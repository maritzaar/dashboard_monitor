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
        // Get available months and years from fuel_budgets & data_alat
        $availableMonthsFB = FuelBudget::select('bulan')->distinct()->pluck('bulan')->toArray();
        $availableMonthsDA = DataAlat::select('bulan')->distinct()->pluck('bulan')->toArray();
        $availableMonths = array_values(array_unique(array_filter(array_merge($availableMonthsFB, $availableMonthsDA))));

        $availableYearsFB = FuelBudget::select('tahun')->distinct()->pluck('tahun')->toArray();
        $availableYearsDA = DataAlat::select('tahun')->distinct()->pluck('tahun')->toArray();
        $availableYears = array_values(array_unique(array_filter(array_merge($availableYearsFB, $availableYearsDA))));
        rsort($availableYears);

        // Default to latest year and month from fuel_budgets
        $latestFB = FuelBudget::orderBy('tahun', 'desc')->orderBy('id', 'desc')->first();
        $reqBulan = $request->get('bulan', $latestFB ? $latestFB->bulan : ($availableMonths[0] ?? 'ALL'));
        $reqTahun = $request->get('tahun', $latestFB ? $latestFB->tahun : ($availableYears[0] ?? date('Y')));
        
        $bulan = $reqBulan;
        $tahun = $reqTahun;

        // Total Assets Monitored
        $totalAset = MasterAset::count();

        // Avg % Idle overall
        $avgIdle = DataAlat::avg('persen_idle') ?? 0;

        // Total Fuel overall (from FuelBudget)
        $totalFuel = FuelBudget::sum('solar_actual') ?? 0;

        // Total Working Hours overall
        $totalKerja = DataAlat::sum('waktu_kerja') ?? 0;

        // --- CALCULATION FOR TOP 5 & BOTTOM 5 EFFICIENCY FROM FUEL_BUDGETS ---
        $fbQuery = FuelBudget::query();
        if ($bulan !== 'ALL') {
            $fbQuery->where(function ($q) use ($bulan) {
                $q->where('bulan', $bulan)->orWhere('bulan', substr($bulan, 0, 3));
            });
        }
        if ($tahun !== 'ALL') {
            $fbQuery->where('tahun', $tahun);
        }

        $fuelBudgetData = $fbQuery->get();

        $processedData = $fuelBudgetData->map(function ($item) {
            $isKendaraan = \App\Models\MasterAset::isKendaraan($item->group_internal_order);
            $kmHmType = strtoupper(trim((string)($item->km_hm ?? '')));
            if (empty($kmHmType)) {
                $kmHmType = $isKendaraan ? 'KM' : 'HM';
            }

            if ($kmHmType === 'KM') {
                $rasio_budget = ($item->solar_budget > 0) ? ($item->output_budget / $item->solar_budget) : 0;
            } else {
                $rasio_budget = ($item->output_budget > 0) ? ($item->solar_budget / $item->output_budget) : 0;
            }

            $efisiensi = (float) $item->solar_actual - ((float) $rasio_budget * (float) $item->output_actual);

            return (object) [
                'id_aset' => $item->unit_code ?: '-',
                'internal_order' => $item->internal_order,
                'group_internal_order' => $item->group_internal_order,
                'km_hm_type' => $kmHmType,
                'is_kendaraan' => ($kmHmType === 'KM'),
                'output_actual' => (float) $item->output_actual,
                'solar_actual' => (float) $item->solar_actual,
                'solar_budget' => (float) $item->solar_budget,
                'rasio_budget' => $rasio_budget,
                'efisiensi' => $efisiensi,
            ];
        })->filter(function ($item) {
            return $item->id_aset !== '-' && $item->id_aset !== '#N/A' && ($item->solar_actual > 0 || $item->solar_budget > 0);
        });

        // Group by unit_code to aggregate monthly records
        $aggregatedUnits = $processedData->groupBy('id_aset')->map(function ($group) {
            $first = $group->first();
            return (object) [
                'id_aset' => $first->id_aset,
                'is_kendaraan' => $first->is_kendaraan,
                'km_hm_type' => $first->km_hm_type,
                'total_solar' => $group->sum('solar_actual'),
                'total_output' => $group->sum('output_actual'),
                'efisiensi' => $group->sum('efisiensi'),
            ];
        });

        $alatBeratData = $aggregatedUnits->where('is_kendaraan', false);
        $kendaraanData = $aggregatedUnits->where('is_kendaraan', true);

        // Alat Berat: Lowest efisiensi (paling negatif) = paling hemat; Highest = paling boros
        $topAB = $alatBeratData->sortBy('efisiensi')->take(5)->values();
        $bottomAB = $alatBeratData->sortByDesc('efisiensi')->take(5)->values();

        // Kendaraan: Lowest efisiensi (paling negatif) = paling hemat; Highest = paling boros
        $topKen = $kendaraanData->sortBy('efisiensi')->take(5)->values();
        $bottomKen = $kendaraanData->sortByDesc('efisiensi')->take(5)->values();

        $totalEfisiensiAB = $alatBeratData->sum('efisiensi');
        $totalEfisiensiKen = $kendaraanData->sum('efisiensi');

        return view('home', compact('availableMonths', 'availableYears', 'bulan', 'tahun', 
            'totalAset', 'avgIdle', 'totalFuel', 'totalKerja',
            'topAB', 'bottomAB', 'topKen', 'bottomKen', 'totalEfisiensiAB', 'totalEfisiensiKen'
        ));
    }
}
