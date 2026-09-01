<?php

namespace App\Http\Controllers;

use App\Models\DataAlat;
use App\Models\FuelBudget;
use App\Models\MasterAset;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        // Get available months and years for the dropdown
        $availableMonths = DataAlat::select('bulan')->distinct()->pluck('bulan')->toArray();
        $availableYears = DataAlat::select('tahun')->distinct()->pluck('tahun')->toArray();

        // Get the latest month available in the working hours data as default if not provided
        $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
        
        $reqBulan = $request->get('bulan', $latestData ? $latestData->bulan : now()->format('F'));
        $reqTahun = $request->get('tahun', $latestData ? $latestData->tahun : now()->year);
        
        $bulan = $reqBulan;
        $tahun = $reqTahun;

        // Total Assets Monitored
        $totalAset = MasterAset::count();

        // Avg % Idle overall
        $avgIdle = DataAlat::avg('persen_idle') ?? 0;

        // Total Fuel overall (from Budget & Actual SAP data)
        $totalFuel = FuelBudget::sum('solar_actual') ?? 0;

        // Total Working Hours overall
        $totalKerja = DataAlat::sum('waktu_kerja') ?? 0;

        // --- CALCULATION FOR TOP 5 & BOTTOM 5 EFFICIENCY (LATEST MONTH) ---
        $telemetrySub = DB::table('data_alat')
            ->select('id_aset', DB::raw('SUM(COALESCE(waktu_kerja, waktu_operasi, 0)) as total_kerja'))
            ->groupBy('id_aset');
            
        if ($bulan !== 'ALL') {
            $telemetrySub->where('bulan', $bulan);
        }
        if ($tahun !== 'ALL') {
            $telemetrySub->where('tahun', $tahun);
        }

        $fuelSub = DB::table('fuel_budgets')
            ->select(
                'unit_code',
                DB::raw('SUM(solar_actual) as total_solar'),
                DB::raw('SUM(output_actual) as km_hm'),
                DB::raw('MAX(internal_order) as internal_order')
            )
            ->groupBy('unit_code');
            
        if ($bulan !== 'ALL') {
            $fuelSub->where(function ($q) use ($bulan) {
                $q->where('bulan', $bulan)->orWhere('bulan', substr($bulan, 0, 3));
            });
        }
        if ($tahun !== 'ALL') {
            $fuelSub->where('tahun', $tahun);
        }

        $telemetryData = $telemetrySub->get()->keyBy('id_aset');
        $fuelData = $fuelSub->get()->keyBy('unit_code');
        
        $allIds = collect($telemetryData->keys())->merge($fuelData->keys())->unique();
        $masterAsets = MasterAset::whereIn('unit_code', $allIds)->get()->keyBy('unit_code');
        
        $efficiencyData = collect();
        foreach ($allIds as $id) {
            $t = $telemetryData->get($id);
            $f = $fuelData->get($id);
            $m = $masterAsets->get($id);
            
            $total_solar = (float) ($f->total_solar ?? 0);
            $gio = $m ? $m->group_internal_order : null;
            if (empty($gio) && $f && !empty($f->internal_order)) {
                $gio = substr($f->internal_order, 4, 3);
            }
            $isKendaraan = \App\Models\MasterAset::isKendaraan($gio);
            if ($isKendaraan) {
                $total_kerja = (float) ($f->km_hm ?? 0);
            } else {
                $total_kerja = (float) ($t->total_kerja ?? 0);
            }
            
            if ($total_kerja <= 0 || $total_solar <= 0) continue;
            

            $efficiency = null;
            if ($isKendaraan) {
                $efficiency = $total_solar > 0 ? ($total_kerja / $total_solar) : null;
            } else {
                $efficiency = $total_kerja > 0 ? ($total_solar / $total_kerja) : null;
            }
            
            if (!is_null($efficiency)) {
                $efficiencyData->push((object)[
                    'id_aset' => $id,
                    'group_internal_order' => $gio,
                    'total_kerja' => $total_kerja,
                    'total_solar' => $total_solar,
                    'is_kendaraan' => $isKendaraan,
                    'efficiency' => $efficiency
                ]);
            }
        }

        $alatBeratData = $efficiencyData->where('is_kendaraan', false);
        $kendaraanData = $efficiencyData->where('is_kendaraan', true);

        // Alat Berat: Lowest L/Jam is best
        $topAB = $alatBeratData->sortBy('efficiency')->take(5)->values();
        $bottomAB = $alatBeratData->sortByDesc('efficiency')->take(5)->values();

        // Kendaraan: Highest KM/L is best
        $topKen = $kendaraanData->sortByDesc('efficiency')->take(5)->values();
        $bottomKen = $kendaraanData->sortBy('efficiency')->take(5)->values();
        
        $avgEffAB = $alatBeratData->sum('total_kerja') > 0 ? ($alatBeratData->sum('total_solar') / $alatBeratData->sum('total_kerja')) : 0;
        $avgEffKen = $kendaraanData->sum('total_solar') > 0 ? ($kendaraanData->sum('total_kerja') / $kendaraanData->sum('total_solar')) : 0;

        return view('home', compact('availableMonths', 'availableYears', 'bulan', 'tahun', 
            'totalAset', 'avgIdle', 'totalFuel', 'totalKerja',
            'topAB', 'bottomAB', 'topKen', 'bottomKen', 'avgEffAB', 'avgEffKen'
        ));
    }
}
