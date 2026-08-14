<?php

namespace App\Http\Controllers;

use App\Models\DataAlat;
use App\Models\FuelTransaction;
use App\Models\MasterAset;

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

        // Total Fuel overall
        $totalFuel = FuelTransaction::sum('total_quantity') ?? 0;

        // Total Working Hours overall (Bonus: adding this since they want overall data)
        $totalKerja = DataAlat::sum('waktu_kerja') ?? 0;

        // --- CALCULATION FOR TOP 5 & BOTTOM 5 EFFICIENCY (LATEST MONTH) ---
        $telemetrySub = \Illuminate\Support\Facades\DB::table('data_alat')
            ->select('id_aset', \Illuminate\Support\Facades\DB::raw('SUM(COALESCE(waktu_kerja, waktu_operasi, 0)) as total_kerja'))
            ->groupBy('id_aset');
            
        if ($bulan !== 'ALL') {
            $telemetrySub->where('bulan', $bulan);
        }
        if ($tahun !== 'ALL') {
            $telemetrySub->where('tahun', $tahun);
        }

        $fuelSub = \Illuminate\Support\Facades\DB::table('fuel_transactions')
            ->select('unit_code', \Illuminate\Support\Facades\DB::raw('SUM(total_quantity) as total_solar'))
            ->groupBy('unit_code');
            
        if ($bulan !== 'ALL') {
            $fuelSub->where(function ($q) use ($bulan) {
                $q->where('bulan', $bulan)->orWhere('bulan', substr($bulan, 0, 3));
            });
        }
        if ($tahun !== 'ALL') {
            $fuelSub->where('tahun', $tahun);
        }

        $efficiencyData = MasterAset::query()
            ->select('master_asets.unit_code as id_aset', 'master_asets.group_internal_order', 'telemetry.total_kerja', 'fuel.total_solar')
            ->joinSub($telemetrySub, 'telemetry', 'master_asets.unit_code', '=', 'telemetry.id_aset')
            ->joinSub($fuelSub, 'fuel', 'master_asets.unit_code', '=', 'fuel.unit_code')
            ->get()
            ->map(function ($row) {
                $row->total_kerja = (float) ($row->total_kerja ?? 0);
                $row->total_solar = (float) ($row->total_solar ?? 0);
                
                $isKendaraan = in_array($row->group_internal_order, ['KRD', 'KRF', 'KRK', 'KRL', 'KRT', 'KRS']);
                $row->is_kendaraan = $isKendaraan;
                
                if ($isKendaraan) {
                    $row->efficiency = $row->total_solar > 0 ? ($row->total_kerja / $row->total_solar) : null;
                } else {
                    $row->efficiency = $row->total_kerja > 0 ? ($row->total_solar / $row->total_kerja) : null;
                }
                return $row;
            })
            ->filter(function($row) {
                return $row->total_kerja > 0 && $row->total_solar > 0 && !is_null($row->efficiency);
            });

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
