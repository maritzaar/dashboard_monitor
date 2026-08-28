<?php

namespace App\Http\Controllers;

use App\Exports\DataAlatExport;
use App\Models\DataAlat;
use App\Models\FuelTransaction;
use App\Models\MasterAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    private function getFilters(Request $request, $type = null) { $req = clone $request; if ($type) { $req->merge(['type' => $type]); } return $this->getFilterOptions($req)->getData(true); }

    /**
     * Kembalikan array nama bulan (full + singkatan 3 huruf) dalam rentang bulan_dari s.d. bulan_sampai.
     * Jika salah satu ALL / kosong: dari = January, sampai = December.
     */
    private function getBulanRange(?string $dari, ?string $sampai): array
    {
        $all = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $fromIdx = ($dari && $dari !== 'ALL') ? array_search($dari, $all) : 0;
        $toIdx   = ($sampai && $sampai !== 'ALL') ? array_search($sampai, $all) : 11;
        if ($fromIdx === false) $fromIdx = 0;
        if ($toIdx   === false) $toIdx   = 11;
        if ($fromIdx > $toIdx) [$fromIdx, $toIdx] = [$toIdx, $fromIdx];
        $months = array_slice($all, $fromIdx, $toIdx - $fromIdx + 1);
        return array_unique(array_merge($months, array_map(fn($m) => substr($m, 0, 3), $months)));
    }

    public function workingHour(Request $request)
    {
        ini_set('memory_limit', '512M');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if (! $start_date || ! $end_date) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            if ($latestData) {
                $latestDate = \Carbon\Carbon::parse($latestData->tanggal);
                $start_date = $latestDate->copy()->startOfMonth()->format('Y-m-d');
                $end_date = $latestDate->copy()->endOfMonth()->format('Y-m-d');
            } else {
                $start_date = now()->startOfMonth()->format('Y-m-d');
                $end_date = now()->endOfMonth()->format('Y-m-d');
            }
            $request->merge(['start_date' => $start_date, 'end_date' => $end_date]);
        }

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');
        $group_desc = $request->get('group_desc');
        $pt = $request->get('pt');

        $query = DataAlat::query()
            ->leftJoin('master_asets', 'data_alat.id_aset', '=', 'master_asets.unit_code');

        $query_end_date = \Carbon\Carbon::parse($end_date)->endOfDay()->format('Y-m-d H:i:s');
        $query->whereBetween('data_alat.tanggal', [$start_date, $query_end_date]);

        if (! empty($id_aset) && $id_aset !== 'ALL') {
            $query->where(function ($q) use ($id_aset) {
                $q->where('master_asets.unit_code', $id_aset)
                    ->orWhere('data_alat.id_aset', $id_aset);
            });
        }
        if (! empty($group_aset) && $group_aset !== 'ALL') {
            $query->where(function ($q) use ($group_aset) {
                $q->where('master_asets.group_aset', $group_aset)
                    ->orWhere('data_alat.group_aset', $group_aset);
            });
        }
        if (! empty($area) && $area !== 'ALL') {
            $query->where(function ($q) use ($area) {
                $q->where('master_asets.area', $area)
                    ->orWhere('data_alat.area', $area);
            });
        }
        if (! empty($group_internal_order) && $group_internal_order !== 'ALL') {
            $query->where(function ($q) use ($group_internal_order) {
                $q->where('master_asets.group_internal_order', $group_internal_order)
                    ->orWhere('data_alat.group_internal_order', $group_internal_order);
            });
        }
        if (! empty($internal_order) && $internal_order !== 'ALL') {
            $query->where(function ($q) use ($internal_order) {
                $q->where('master_asets.internal_order', $internal_order)
                    ->orWhere('data_alat.internal_order', $internal_order);
            });
        }
        if (! empty($group_desc) && $group_desc !== 'ALL') {
            $query->where(function ($q) use ($group_desc) {
                $q->where('master_asets.group_desc', $group_desc)
                    ->orWhere('data_alat.group_desc', $group_desc);
            });
        }
        if (! empty($pt) && $pt !== 'ALL') {
            $query->where(function ($q) use ($pt) {
                $q->where('master_asets.pt', $pt)
                    ->orWhere('data_alat.pt', $pt);
            });
        }

        $reports = $query->select(
            'data_alat.id as id',
            DB::raw('COALESCE(data_alat.id_aset, master_asets.unit_code) as id_aset'),
            'data_alat.tanggal as tanggal',
            DB::raw('COALESCE(data_alat.internal_order, master_asets.internal_order) as internal_order'),
            'data_alat.model as model',
            DB::raw('COALESCE(data_alat.group_aset, master_asets.group_aset) as group_aset'),
            DB::raw('COALESCE(data_alat.area, master_asets.area) as area'),
            DB::raw('COALESCE(data_alat.group_internal_order, master_asets.group_internal_order) as group_internal_order'),
            DB::raw('COALESCE(data_alat.pt, master_asets.pt) as pt'),
            DB::raw('COALESCE(data_alat.group_desc, master_asets.group_desc) as group_desc'),
            'data_alat.waktu_kerja as total_kerja',
            'data_alat.waktu_operasi as total_operasi',
            'data_alat.waktu_idle as total_idle',
            'data_alat.persen_idle as avg_idle'
        )
            ->orderBy('data_alat.tanggal', 'desc',
            'wh.total_waktu_operasi as total_kerja'
        )
            ->get()
            ->map(function ($item) {
                $item->rasio = $item->total_kerja > 0 ? $item->actual_fuel / $item->total_kerja : 0;
                return $item;
            });

        $stats = (object) [
            'total_aset' => $reports->pluck('id_aset')->unique()->count(),
            'total_kerja' => $reports->sum('total_kerja'),
            'total_operasi' => $reports->sum('total_operasi'),
            'total_idle' => $reports->sum('total_idle'),
            'avg_idle' => $reports->count() > 0 ? $reports->avg('avg_idle') : 0,
        ];

        $chartData = $reports->groupBy('id_aset')->map(function ($group) {
            return (object) [
                'id_aset' => $group->first()->id_aset,
                'total_kerja' => $group->sum('total_kerja'),
                'total_idle' => $group->sum('total_idle'),
            ];
        })->values();

        // Calculate trend aggregated by date
        $trendChartData = $reports->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
        })->map(function ($group, $date) {
            return (object) [
                'tanggal' => $date,
                'total_kerja' => $group->sum('total_kerja'),
                'total_idle' => $group->sum('total_idle'),
            ];
        })->sortBy('tanggal')->values();

        $filters = $this->getFilters($request, $request->route()->getName() == 'monitoring.fuel' ? 'fuel' : ($request->route()->getName() == 'monitoring.efficiency' ? 'efficiency' : null));

        return view('monitoring.working_hour', array_merge(compact(
            'reports', 'stats', 'chartData', 'trendChartData', 'start_date', 'end_date',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc', 'pt'
        ), $filters));
    }

    public function workingHourMonthly(Request $request)
    {
        ini_set('memory_limit', '512M');
        $bulan_dari  = $request->get('bulan_dari');
        $bulan_sampai = $request->get('bulan_sampai');
        $tahun = $request->get('tahun');

        if (! $tahun) {
            $latestData = DataAlat::orderBy('tahun', 'desc')->first();
            $tahun = $latestData ? $latestData->tahun : date('Y');
        }

        if (! $bulan_dari)  $bulan_dari  = 'ALL';
        if (! $bulan_sampai) $bulan_sampai = 'ALL';

        $request->merge(['bulan_dari' => $bulan_dari, 'bulan_sampai' => $bulan_sampai, 'tahun' => $tahun]);

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');
        $group_desc = $request->get('group_desc');
        $pt = $request->get('pt');

        $query = DataAlat::query()
            ->leftJoin('master_asets', 'data_alat.id_aset', '=', 'master_asets.unit_code');

        $hasBulanFilter = ($bulan_dari !== 'ALL') || ($bulan_sampai !== 'ALL');
        if ($hasBulanFilter) {
            $bulanList = $this->getBulanRange($bulan_dari, $bulan_sampai);
            $query->whereIn('data_alat.bulan', $bulanList);
        }

        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('data_alat.tahun', $tahun);
        }

        if (! empty($id_aset) && $id_aset !== 'ALL') {
            $query->where(function ($q) use ($id_aset) {
                $q->where('master_asets.unit_code', $id_aset)
                    ->orWhere('data_alat.id_aset', $id_aset);
            });
        }
        if (! empty($group_aset) && $group_aset !== 'ALL') {
            $query->where(function ($q) use ($group_aset) {
                $q->where('master_asets.group_aset', $group_aset)
                    ->orWhere('data_alat.group_aset', $group_aset);
            });
        }
        if (! empty($area) && $area !== 'ALL') {
            $query->where(function ($q) use ($area) {
                $q->where('master_asets.area', $area)
                    ->orWhere('data_alat.area', $area);
            });
        }
        if (! empty($group_internal_order) && $group_internal_order !== 'ALL') {
            $query->where(function ($q) use ($group_internal_order) {
                $q->where('master_asets.group_internal_order', $group_internal_order)
                    ->orWhere('data_alat.group_internal_order', $group_internal_order);
            });
        }
        if (! empty($internal_order) && $internal_order !== 'ALL') {
            $query->where(function ($q) use ($internal_order) {
                $q->where('master_asets.internal_order', $internal_order)
                    ->orWhere('data_alat.internal_order', $internal_order);
            });
        }
        if (! empty($group_desc) && $group_desc !== 'ALL') {
            $query->where(function ($q) use ($group_desc) {
                $q->where('master_asets.group_desc', $group_desc)
                    ->orWhere('data_alat.group_desc', $group_desc);
            });
        }
        if (! empty($pt) && $pt !== 'ALL') {
            $query->where(function ($q) use ($pt) {
                $q->where('master_asets.pt', $pt)
                    ->orWhere('data_alat.pt', $pt);
            });
        }

        $monthOrder = [
            'January' => 1, 'Jan' => 1, 'Januari' => 1, '1' => 1, '01' => 1,
            'February' => 2, 'Feb' => 2, 'Februari' => 2, '2' => 2, '02' => 2,
            'March' => 3, 'Mar' => 3, 'Maret' => 3, '3' => 3, '03' => 3,
            'April' => 4, 'Apr' => 4, '4' => 4, '04' => 4,
            'May' => 5, 'Mei' => 5, '5' => 5, '05' => 5,
            'June' => 6, 'Jun' => 6, 'Juni' => 6, '6' => 6, '06' => 6,
            'July' => 7, 'Jul' => 7, 'Juli' => 7, '7' => 7, '07' => 7,
            'August' => 8, 'Aug' => 8, 'Agustus' => 8, '8' => 8, '08' => 8,
            'September' => 9, 'Sep' => 9, '9' => 9, '09' => 9,
            'October' => 10, 'Oct' => 10, 'Oktober' => 10, '10' => 10,
            'November' => 11, 'Nov' => 11, '11' => 11,
            'December' => 12, 'Dec' => 12, 'Desember' => 12, '12' => 12,
        ];

        // Group by per unit per bulan
        $reports = $query->select(
            DB::raw('COALESCE(data_alat.id_aset, master_asets.unit_code) as id_aset'),
            'data_alat.tahun',
            'data_alat.bulan',
            DB::raw('MAX(COALESCE(data_alat.internal_order, master_asets.internal_order)) as internal_order'),
            DB::raw('MAX(COALESCE(data_alat.model, master_asets.model)) as model'),
            DB::raw('MAX(COALESCE(data_alat.group_aset, master_asets.group_aset)) as group_aset'),
            DB::raw('MAX(COALESCE(data_alat.area, master_asets.area)) as area'),
            DB::raw('MAX(COALESCE(data_alat.group_internal_order, master_asets.group_internal_order)) as group_internal_order'),
            DB::raw('MAX(COALESCE(data_alat.pt, master_asets.pt)) as pt'),
            DB::raw('MAX(COALESCE(data_alat.group_desc, master_asets.group_desc)) as group_desc'),
            DB::raw('SUM(data_alat.waktu_kerja) as total_kerja'),
            DB::raw('SUM(data_alat.waktu_operasi) as total_operasi'),
            DB::raw('SUM(data_alat.waktu_idle) as total_idle')
        )
        ->groupBy(
            DB::raw('COALESCE(data_alat.id_aset, master_asets.unit_code)'),
            'data_alat.tahun',
            'data_alat.bulan'
        )
        ->orderBy('data_alat.tahun', 'desc')
        ->get()
        ->map(function ($item) use ($monthOrder) {
            $item->month_num = $monthOrder[$item->bulan] ?? 0;
            $item->avg_idle = $item->total_operasi > 0
                ? round(($item->total_idle / $item->total_operasi) * 100, 2)
                : 0;
            return $item;
        })
        ->sortBy([
            ['tahun', 'asc'],
            ['month_num', 'asc'],
            ['id_aset', 'asc'],
        ])
        ->values();

        $stats = (object) [
            'total_aset' => $reports->pluck('id_aset')->unique()->count(),
            'total_kerja' => $reports->sum('total_kerja'),
            'total_operasi' => $reports->sum('total_operasi'),
            'total_idle' => $reports->sum('total_idle'),
            'avg_idle' => $reports->sum('total_operasi') > 0
                ? round(($reports->sum('total_idle') / $reports->sum('total_operasi')) * 100, 2)
                : 0,
        ];

        // Chart data: Komparasi per Aset (total jam kerja dan idle sepanjang rentang bulan terpilih)
        $chartData = $reports->groupBy('id_aset')->map(function ($group) {
            return (object) [
                'id_aset' => $group->first()->id_aset,
                'total_kerja' => $group->sum('total_kerja'),
                'total_idle' => $group->sum('total_idle'),
            ];
        })->values();

        // Chart data: Tren Akumulasi Bulanan
        $trendChartData = $reports->groupBy('bulan')->map(function ($group, $bulan) use ($monthOrder) {
            return (object) [
                'bulan' => $bulan,
                'order' => $monthOrder[$bulan] ?? 0,
                'total_kerja' => $group->sum('total_kerja'),
                'total_idle' => $group->sum('total_idle'),
            ];
        })->sortBy('order')->values();

        $filters = $this->getFilters($request, 'working_hour_monthly');

        return view('monitoring.working_hour_monthly', array_merge(compact(
            'reports', 'stats', 'chartData', 'trendChartData', 'bulan_dari', 'bulan_sampai', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc', 'pt'
        ), $filters));
    }

    public function fuel(Request $request)
    {
        ini_set('memory_limit', '512M');
        $bulan_dari  = $request->get('bulan_dari');
        $bulan_sampai = $request->get('bulan_sampai');
        $tahun = $request->get('tahun');

        if (! $bulan_dari)  $bulan_dari  = 'ALL';
        if (! $bulan_sampai) $bulan_sampai = 'ALL';
        if (! $tahun) {
            $tahun = date('Y');
        }
        $request->merge(['bulan_dari' => $bulan_dari, 'bulan_sampai' => $bulan_sampai, 'tahun' => $tahun]);

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');
        $group_desc = $request->get('group_desc');
        $pt = $request->get('pt');

                $query = FuelTransaction::query()
            ->leftJoin('master_asets', 'fuel_transactions.unit_code', '=', 'master_asets.unit_code');

        // Filter rentang bulan: 
        $hasBulanFilter = ($bulan_dari !== 'ALL') || ($bulan_sampai !== 'ALL');
        if ($hasBulanFilter) {
            $bulanList = $this->getBulanRange($bulan_dari, $bulan_sampai);
            $query->whereIn('fuel_transactions.bulan', $bulanList);
        }

        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('fuel_transactions.tahun', $tahun);
        }
        if (! empty($id_aset) && $id_aset !== 'ALL') {
            $query->where('fuel_transactions.unit_code', $id_aset);
        }
        if (! empty($group_aset) && $group_aset !== 'ALL') {
            $query->where(function ($q) use ($group_aset) {
                $q->where('master_asets.group_aset', $group_aset)
                    ->orWhere('fuel_transactions.group_aset', $group_aset);
            });
        }
        if (! empty($area) && $area !== 'ALL') {
            $query->where(function ($q) use ($area) {
                $q->where('master_asets.area', $area)
                    ->orWhere('fuel_transactions.area', $area);
            });
        }
        if (! empty($group_internal_order) && $group_internal_order !== 'ALL') {
            $query->where(function ($q) use ($group_internal_order) {
                $q->where('master_asets.group_internal_order', $group_internal_order)
                    ->orWhere('fuel_transactions.io_group', $group_internal_order);
            });
        }
        if (! empty($internal_order) && $internal_order !== 'ALL') {
            $query->where(function ($q) use ($internal_order) {
                $q->where('master_asets.internal_order', $internal_order)
                    ->orWhere('fuel_transactions.internal_order', $internal_order);
            });
        }
        if (! empty($group_desc) && $group_desc !== 'ALL') {
            $query->where(function ($q) use ($group_desc) {
                $q->where('master_asets.group_desc', $group_desc)
                    ->orWhere('fuel_transactions.io_desc', $group_desc);
            });
        }
        if (! empty($pt) && $pt !== 'ALL') {
            $query->where('master_asets.pt', $pt);
        }

        $reports = $query->select(
            'fuel_transactions.id as id',
            'fuel_transactions.unit_code as id_aset',
            DB::raw('COALESCE(fuel_transactions.internal_order, master_asets.internal_order) as internal_order'),
            DB::raw('COALESCE(fuel_transactions.group_aset, master_asets.group_aset) as group_aset'),
            DB::raw('COALESCE(fuel_transactions.area, master_asets.area) as area'),
            'fuel_transactions.solar as actual_fuel',
            'fuel_transactions.km_hm as total_kerja',
            'fuel_transactions.bulan',
            'fuel_transactions.tahun',
            'master_asets.pt as pt',
            DB::raw('COALESCE(fuel_transactions.io_desc, master_asets.group_desc) as group_desc'),
            DB::raw('COALESCE(fuel_transactions.io_group, master_asets.group_internal_order) as group_internal_order')
        )
            ->get()
            ->map(function ($item) {
                $isKendaraan = \App\Models\MasterAset::isKendaraan($item->group_internal_order);
                if ($isKendaraan) {
                    $item->rasio = $item->actual_fuel > 0 ? $item->total_kerja / $item->actual_fuel : 0;
                } else {
                    $item->rasio = $item->total_kerja > 0 ? $item->actual_fuel / $item->total_kerja : 0;
                }
                $item->is_kendaraan = $isKendaraan;
                return $item;
            })
            ->sortBy(function($item) {
                try {
                    return \Carbon\Carbon::parse("1 " . $item->bulan . " " . $item->tahun)->format('Y-m');
                } catch (\Exception $e) {
                    return $item->tahun . '-' . $item->bulan;
                }
            })
            ->values();

        // Calculate aggregated fuel per asset (ordered descending by fuel usage)
        $chartData = $reports->groupBy('id_aset')->map(function ($group) {
            return (object) [
                'id_aset' => $group->first()->id_aset,
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortByDesc('actual_fuel')->values();

        // Calculate aggregated fuel per asset group
        $groupChartData = $reports->groupBy('group_aset')->map(function ($group) {
            return (object) [
                'group_aset' => $group->first()->group_aset ?? 'Lain-lain',
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortByDesc('actual_fuel')->values();

        // Calculate aggregated fuel per area
        $areaChartData = $reports->groupBy('area')->map(function ($group) {
            return (object) [
                'area' => $group->first()->area ?? 'Lain-lain',
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortByDesc('actual_fuel')->values();

        // Calculate trend aggregated by year-month
        $trendChartData = $reports->groupBy(function($item) {
            try {
                return \Carbon\Carbon::parse("1 " . $item->bulan . " " . $item->tahun)->format('Y-m');
            } catch (\Exception $e) {
                return $item->tahun . '-' . $item->bulan;
            }
        })->map(function ($group, $ym) {
            return (object) [
                'periode' => $ym,
                'label' => $group->first()->bulan . ' ' . $group->first()->tahun,
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortBy('periode')->values();

        $totalAsetCount = $reports->pluck('id_aset')->unique()->count();
        $stats = (object) [
            'total_aset' => $totalAsetCount,
            'actual_fuel' => $reports->sum('actual_fuel'),
            'avg_fuel' => $totalAsetCount > 0 ? $reports->sum('actual_fuel') / $totalAsetCount : 0,
            'max_fuel_val' => $chartData->first() ? $chartData->first()->actual_fuel : 0,
            'max_fuel_aset' => $chartData->first() ? $chartData->first()->id_aset : '-',
        ];

        $filters = $this->getFilters($request, $request->route()->getName() == 'monitoring.fuel' ? 'fuel' : ($request->route()->getName() == 'monitoring.efficiency' ? 'efficiency' : null));

        return view('monitoring.fuel', array_merge(compact(
            'reports', 'stats', 'chartData', 'groupChartData', 'areaChartData', 'trendChartData', 'bulan_dari', 'bulan_sampai', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc', 'pt'
        ), $filters));
    }



    public function export(Request $request)
    {
        ini_set('memory_limit', '512M');
        $filters = $request->only([
            'start_date', 'end_date', 'bulan', 'bulan_dari', 'bulan_sampai', 'tahun', 'group_aset', 'area', 'id_aset',
            'group_desc', 'group_internal_order', 'internal_order', 'pt',
        ]);

        $type = $request->get('type', 'working_hour');

        // Build a nice filename
        $nameParts = ['Laporan_Monitoring_Alat'];
        if ($type === 'efficiency') {
            $nameParts = ['Laporan_Efisiensi_Alat'];
        } elseif ($type === 'fuel') {
            $nameParts = ['Laporan_Konsumsi_Solar'];
        }

        if (! empty($filters['group_aset']) && $filters['group_aset'] !== 'ALL') {
            $nameParts[] = $filters['group_aset'];
        }
        if (! empty($filters['area']) && $filters['area'] !== 'ALL') {
            $nameParts[] = $filters['area'];
        }

        if ($type === 'working_hour' && ! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $nameParts[] = $filters['start_date'].'_to_'.$filters['end_date'];
        } else {
            $bDari = $filters['bulan_dari'] ?? $filters['bulan'] ?? 'all';
            $bSampai = $filters['bulan_sampai'] ?? $filters['bulan'] ?? 'all';
            $nameParts[] = strtolower($bDari === $bSampai ? $bDari : $bDari . '_to_' . $bSampai);
            $nameParts[] = $filters['tahun'] ?? 'all';
        }

        $fileName = implode('_', $nameParts).'.xlsx';

        if ($type === 'fuel') {
            return Excel::download(new \App\Exports\FuelExport($filters), $fileName);
        }
        if ($type === 'efficiency') {
            return Excel::download(new \App\Exports\EfficiencyExport($filters), $fileName);
        }

        return Excel::download(new DataAlatExport($filters), $fileName);
    }

    public function exportPdf(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '1024M');

        $filters = $request->only([
            'start_date', 'end_date', 'bulan', 'bulan_dari', 'bulan_sampai', 'tahun', 'group_aset', 'area', 'id_aset',
            'group_desc', 'group_internal_order', 'internal_order', 'pt',
        ]);

        $type = $request->get('type', 'working_hour');

        if ($type === 'fuel') {
            $export = new \App\Exports\FuelExport($filters);
            $data = $export->query()->get();
            $title = 'Laporan Konsumsi Solar';
            $view = 'exports.fuel_pdf';
        } elseif ($type === 'efficiency') {
            $export = new \App\Exports\EfficiencyExport($filters);
            $data = $export->collection();
            $title = 'Laporan Efisiensi Bahan Bakar';
            $view = 'exports.efficiency_pdf';
        } else {
            $export = new \App\Exports\DataAlatExport($filters);
            $data = $export->query()->get();
            $title = 'Laporan Konsolidasi Jam Kerja';
            $view = 'exports.working_hour_pdf';
        }

        $redirectUrl = route('monitoring.working_hour');
        if ($type === 'fuel') {
            $redirectUrl = route('monitoring.fuel');
        } elseif ($type === 'efficiency') {
            $redirectUrl = route('monitoring.efficiency');
        }

        if ($data->count() > 1500) {
            return redirect($redirectUrl)
                ->with('error', 'Ukuran data terlalu besar untuk diekspor ke PDF (' . number_format($data->count()) . ' baris). Batas maksimum ekspor PDF adalah 1.500 baris. Silakan gunakan ekspor Excel (tidak dibatasi) atau gunakan filter tanggal/bulan lebih spesifik.');
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, compact('data', 'filters', 'title'))
                ->setPaper('a4', 'landscape');

            // Build a nice filename
            $nameParts = ['laporan_monitoring_alat'];
            if ($type === 'efficiency') {
                $nameParts = ['laporan_efisiensi_alat'];
            }
            if (! empty($filters['group_aset']) && $filters['group_aset'] !== 'ALL') {
                $nameParts[] = $filters['group_aset'];
            }
            if (! empty($filters['area']) && $filters['area'] !== 'ALL') {
                $nameParts[] = $filters['area'];
            }
            if ($type !== 'efficiency' && ! empty($filters['start_date']) && ! empty($filters['end_date'])) {
                $nameParts[] = $filters['start_date'].'_to_'.$filters['end_date'];
            } else {
                $nameParts[] = strtolower($filters['bulan'] ?? 'all');
                $nameParts[] = $filters['tahun'] ?? 'all';
            }

            $fileName = implode('_', $nameParts).'.pdf';

            return $pdf->download($fileName);
        } catch (\Exception $e) {
            $errorMessage = 'Gagal mengekspor PDF. Terjadi kesalahan sistem.';
            if (str_contains($e->getMessage(), 'GD extension is required')) {
                $errorMessage = 'Gagal mengekspor PDF: Ekstensi PHP "GD" (pengolah gambar) belum aktif di server Anda.';
            }
            return redirect($redirectUrl)->with('error', $errorMessage);
        }
    }

    public function efficiency(Request $request)
    {
        ini_set('memory_limit', '512M');
        $bulan_dari  = $request->get('bulan_dari', 'ALL');
        $bulan_sampai = $request->get('bulan_sampai', 'ALL');
        $tahun = $request->get('tahun', date('Y'));
        
        $request->merge(['bulan_dari' => $bulan_dari, 'bulan_sampai' => $bulan_sampai, 'tahun' => $tahun]);

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');
        $group_desc = $request->get('group_desc');
        $pt = $request->get('pt');

        $telemetrySub = DB::table('data_alat');
        $hasBulanFilter = ($bulan_dari !== 'ALL') || ($bulan_sampai !== 'ALL');
        if ($hasBulanFilter) {
            $bulanList = $this->getBulanRange($bulan_dari, $bulan_sampai);
            $telemetrySub->whereIn('bulan', $bulanList);
        }
        if (! empty($tahun) && $tahun !== 'ALL') {
            $telemetrySub->where('tahun', $tahun);
        }
        $telemetrySub = $telemetrySub->select(
            'id_aset',
            'bulan',
            'tahun',
            DB::raw('SUM(COALESCE(waktu_kerja, waktu_operasi, 0)) as total_kerja'),
            DB::raw('SUM(waktu_operasi) as total_operasi'),
            DB::raw('SUM(waktu_idle) as total_idle')
        )
        ->groupBy('id_aset', 'bulan', 'tahun');

        $fuelSub = DB::table('fuel_transactions');
        if ($hasBulanFilter) {
            $bulanList = $this->getBulanRange($bulan_dari, $bulan_sampai);
            $fuelSub->whereIn('bulan', $bulanList);
        }
        if (! empty($tahun) && $tahun !== 'ALL') {
            $fuelSub->where('tahun', $tahun);
        }
        $fuelSub = $fuelSub->select(
            'unit_code',
            'bulan',
            'tahun',
            DB::raw('COALESCE(io_group, "") as io_group'),
            DB::raw('COALESCE(io_desc, "") as io_desc'),
            DB::raw('COALESCE(internal_order, "") as internal_order'),
            DB::raw('SUM(solar) as total_solar'),
            DB::raw('SUM(km_hm) as fuel_km_hm')
        )
        ->groupBy('unit_code', 'bulan', 'tahun', 'io_group', 'io_desc', 'internal_order');

        // Gabungkan transaksi bulanan dari kedua tabel
        $query = DB::table('master_asets')
            ->crossJoin(DB::raw('(SELECT DISTINCT bulan, tahun FROM fuel_transactions UNION SELECT DISTINCT bulan, tahun FROM data_alat) as periods'))
            ->leftJoinSub($telemetrySub, 'telemetry', function($join) {
                $join->on('master_asets.unit_code', '=', 'telemetry.id_aset')
                     ->on('periods.bulan', '=', 'telemetry.bulan')
                     ->on('periods.tahun', '=', 'telemetry.tahun');
            })
            ->leftJoinSub($fuelSub, 'fuel', function($join) {
                $join->on('master_asets.unit_code', '=', 'fuel.unit_code')
                     ->on('periods.bulan', '=', 'fuel.bulan')
                     ->on('periods.tahun', '=', 'fuel.tahun');
            })
            ->select(
                'master_asets.unit_code as id_aset',
                'master_asets.group_aset',
                'master_asets.area',
                'master_asets.pt',
                'periods.bulan',
                'periods.tahun',
                DB::raw('COALESCE(NULLIF(fuel.internal_order, ""), master_asets.internal_order) as internal_order'),
                DB::raw('COALESCE(NULLIF(fuel.io_group, ""), master_asets.group_internal_order) as group_internal_order'),
                DB::raw('COALESCE(NULLIF(fuel.io_desc, ""), master_asets.group_desc) as group_desc'),
                'telemetry.total_kerja',
                'telemetry.total_operasi',
                'telemetry.total_idle',
                'fuel.total_solar',
                'fuel.fuel_km_hm'
            )
            ->where(function ($q) {
                $q->whereNotNull('telemetry.total_kerja')
                  ->orWhereNotNull('fuel.total_solar');
            });

        if ($hasBulanFilter) {
            $bulanList = $this->getBulanRange($bulan_dari, $bulan_sampai);
            $query->whereIn('periods.bulan', $bulanList);
        }
        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('periods.tahun', $tahun);
        }

        if (! empty($id_aset) && $id_aset !== 'ALL') {
            $query->where('master_asets.unit_code', $id_aset);
        }
        if (! empty($group_aset) && $group_aset !== 'ALL') {
            $query->where('master_asets.group_aset', $group_aset);
        }
        if (! empty($area) && $area !== 'ALL') {
            $query->where('master_asets.area', $area);
        }
        if (! empty($group_internal_order) && $group_internal_order !== 'ALL') {
            $query->where('master_asets.group_internal_order', $group_internal_order);
        }
        if (! empty($internal_order) && $internal_order !== 'ALL') {
            $query->where('master_asets.internal_order', $internal_order);
        }
        if (! empty($group_desc) && $group_desc !== 'ALL') {
            $query->where(function ($q) use ($group_desc) {
                $q->where('master_asets.group_desc', $group_desc)
                    ->orWhere('fuel_transactions.io_desc', $group_desc);
            });
        }
        if (! empty($pt) && $pt !== 'ALL') {
            $query->where('master_asets.pt', $pt);
        }

        $reports = $query->get()->map(function ($row) {
            $isKendaraan = \App\Models\MasterAset::isKendaraan($row->group_internal_order);
            $row->is_kendaraan = $isKendaraan;
            $row->uom = $isKendaraan ? 'KM/L' : 'L/JAM';

            if ($isKendaraan) {
                // For Kendaraan, use KM from Fuel Excel
                $row->total_kerja = (float) ($row->fuel_km_hm ?? 0);
            } else {
                // For Alat Berat, use Waktu Kerja from Telemetry Excel
                $row->total_kerja = (float) ($row->total_kerja ?? 0);
            }

            $row->total_operasi = (float) ($row->total_operasi ?? 0);
            $row->total_idle = (float) ($row->total_idle ?? 0);
            $row->total_solar = (float) ($row->total_solar ?? 0);
            $row->avg_idle = $row->total_operasi > 0 ? ($row->total_idle / $row->total_operasi) * 100 : 0;
            
            // Target standards
            $targets = [
                'ABA' => 5, 'ABC' => 12, 'ABE' => 16, 'ABG' => 12, 'ABT' => 5,
                'KRD' => 3, 'KRF' => 2, 'KRK' => 4, 'KRL' => 4, 'KRT' => 4,
                'KRC' => 2, 'KRS' => 4,
                'ABL' => 5, 'ABD' => 16
            ];
            $row->target_ratio = $targets[$row->group_internal_order] ?? null;
            
            if ($isKendaraan) {
                $row->efficiency = $row->total_solar > 0 ? ($row->total_kerja / $row->total_solar) : null;
            } else {
                $row->efficiency = $row->total_kerja > 0 ? ($row->total_solar / $row->total_kerja) : null;
            }
            
            return $row;
        })->sortBy(function ($item) {
            $monthOrder = [
                'january' => 1, 'jan' => 1,
                'february' => 2, 'feb' => 2,
                'march' => 3, 'mar' => 3,
                'april' => 4, 'apr' => 4,
                'may' => 5,
                'june' => 6, 'jun' => 6,
                'july' => 7, 'jul' => 7,
                'august' => 8, 'aug' => 8,
                'september' => 9, 'sep' => 9,
                'october' => 10, 'oct' => 10,
                'november' => 11, 'nov' => 11,
                'december' => 12, 'dec' => 12,
            ];
            $m = strtolower($item->bulan ?? '');
            $monthNum = $monthOrder[$m] ?? 99;
            $yearNum = (int) ($item->tahun ?? 0);

            // Urutan: Tahun ASC -> Bulan ASC (Jan s.d. Des) -> Unit Code ASC
            return [
                $yearNum,
                $monthNum,
                $item->id_aset ?? ''
            ];
        })->values();

        $abReports = $reports->where('is_kendaraan', false);
        $kenReports = $reports->where('is_kendaraan', true);

        $stats = (object) [
            'total_aset' => $reports->count(),
            'total_solar' => $reports->sum('total_solar'),
            'total_kerja' => $reports->sum('total_kerja'),
            'avg_efficiency_ab' => $abReports->sum('total_kerja') > 0 ? ($abReports->sum('total_solar') / $abReports->sum('total_kerja')) : 0,
            'avg_efficiency_ken' => $kenReports->sum('total_solar') > 0 ? ($kenReports->sum('total_kerja') / $kenReports->sum('total_solar')) : 0,
        ];

        $chartData = $reports->filter(function ($item) {
            return !is_null($item->efficiency) && $item->total_kerja > 0;
        })->map(function ($item) {
            return (object) [
                'id_aset' => $item->id_aset,
                'efficiency' => round($item->efficiency, 2),
                'uom' => $item->uom
            ];
        })->values();

        $filters = $this->getFilters($request, $request->route()->getName() == 'monitoring.fuel' ? 'fuel' : ($request->route()->getName() == 'monitoring.efficiency' ? 'efficiency' : null));

        return view('monitoring.efficiency', array_merge(compact(
            'reports', 'stats', 'chartData', 'bulan_dari', 'bulan_sampai', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc', 'pt'
        ), $filters));
    }

        public function getFilterOptions(Request $request)
    {
        $type = $request->get('type', 'working_hour'); // Default to working_hour if not specified

        $getMergedOptions = function($column, $masterColumn = null) use ($request, $type) {
            $masterCol = $masterColumn ?? $column;
            
            // 1. Query Master Asets (must still join transactional table to apply date filters)
            $masterQuery = DB::table('master_asets')
                ->where(function($masterWhere) use ($request, $type) {
                    if ($type === 'efficiency') {
                        $masterWhere->whereExists(function($q) use ($request) {
                            $q->select(DB::raw(1))->from('fuel_transactions')->whereColumn('fuel_transactions.unit_code', 'master_asets.unit_code');
                            if ($request->filled('tahun') && $request->tahun !== 'ALL') $q->where('fuel_transactions.tahun', $request->tahun);
                            $hasBulan = ($request->bulan_dari && $request->bulan_dari !== 'ALL') || ($request->bulan_sampai && $request->bulan_sampai !== 'ALL');
                            if ($hasBulan) {
                                $bulanList = $this->getBulanRange($request->bulan_dari, $request->bulan_sampai);
                                $q->whereIn('fuel_transactions.bulan', $bulanList);
                            }
                        })->orWhereExists(function($q) use ($request) {
                            $q->select(DB::raw(1))->from('data_alat')->whereColumn('data_alat.id_aset', 'master_asets.unit_code');
                            if ($request->filled('tahun') && $request->tahun !== 'ALL') $q->where('data_alat.tahun', $request->tahun);
                            $hasBulan = ($request->bulan_dari && $request->bulan_dari !== 'ALL') || ($request->bulan_sampai && $request->bulan_sampai !== 'ALL');
                            if ($hasBulan) {
                                $bulanList = $this->getBulanRange($request->bulan_dari, $request->bulan_sampai);
                                $q->whereIn('data_alat.bulan', $bulanList);
                            }
                        });
                    } else if ($type === 'fuel') {
                        $masterWhere->whereExists(function($q) use ($request) {
                            $q->select(DB::raw(1))->from('fuel_transactions')->whereColumn('fuel_transactions.unit_code', 'master_asets.unit_code');
                            if ($request->filled('tahun') && $request->tahun !== 'ALL') $q->where('fuel_transactions.tahun', $request->tahun);
                            $hasBulan = ($request->bulan_dari && $request->bulan_dari !== 'ALL') || ($request->bulan_sampai && $request->bulan_sampai !== 'ALL');
                            if ($hasBulan) {
                                $bulanList = $this->getBulanRange($request->bulan_dari, $request->bulan_sampai);
                                $q->whereIn('fuel_transactions.bulan', $bulanList);
                            }
                        });
                    } else if ($type === 'working_hour_monthly') {
                        $masterWhere->whereExists(function($q) use ($request) {
                            $q->select(DB::raw(1))->from('data_alat')->whereColumn('data_alat.id_aset', 'master_asets.unit_code');
                            if ($request->filled('tahun') && $request->tahun !== 'ALL') $q->where('data_alat.tahun', $request->tahun);
                            $hasBulan = ($request->bulan_dari && $request->bulan_dari !== 'ALL') || ($request->bulan_sampai && $request->bulan_sampai !== 'ALL');
                            if ($hasBulan) {
                                $bulanList = $this->getBulanRange($request->bulan_dari, $request->bulan_sampai);
                                $q->whereIn('data_alat.bulan', $bulanList);
                            }
                        });
                    } else { // working_hour
                        $masterWhere->whereExists(function($q) use ($request) {
                            $q->select(DB::raw(1))->from('data_alat')->whereColumn('data_alat.id_aset', 'master_asets.unit_code');
                            if ($request->filled('start_date') && $request->filled('end_date')) {
                                $query_end_date = \Carbon\Carbon::parse($request->end_date)->endOfDay()->format('Y-m-d H:i:s');
                                $q->whereBetween('data_alat.tanggal', [$request->start_date, $query_end_date]);
                            } else {
                                if ($request->filled('tahun') && $request->tahun !== 'ALL') $q->where('data_alat.tahun', $request->tahun);
                                if ($request->filled('bulan') && $request->bulan !== 'ALL') $q->where('data_alat.bulan', $request->bulan);
                            }
                        });
                    }
                });

            // 2. Query Transactional Table (data_alat or fuel_transactions)
            if ($type === 'fuel') {
                $histQuery = DB::table('fuel_transactions');
                if ($request->filled('tahun') && $request->tahun !== 'ALL') {
                    $histQuery->where('fuel_transactions.tahun', $request->tahun);
                }
                $hasBulanFilter = ($request->bulan_dari && $request->bulan_dari !== 'ALL') || ($request->bulan_sampai && $request->bulan_sampai !== 'ALL');
                if ($hasBulanFilter) {
                    $bulanList = $this->getBulanRange($request->bulan_dari, $request->bulan_sampai);
                    $histQuery->whereIn('fuel_transactions.bulan', $bulanList);
                }
                $transTable = 'fuel_transactions';
                $transIdCol = 'unit_code';
            } else if ($type === 'working_hour_monthly') {
                $histQuery = DB::table('data_alat');
                if ($request->filled('tahun') && $request->tahun !== 'ALL') {
                    $histQuery->where('data_alat.tahun', $request->tahun);
                }
                $hasBulanFilter = ($request->bulan_dari && $request->bulan_dari !== 'ALL') || ($request->bulan_sampai && $request->bulan_sampai !== 'ALL');
                if ($hasBulanFilter) {
                    $bulanList = $this->getBulanRange($request->bulan_dari, $request->bulan_sampai);
                    $histQuery->whereIn('data_alat.bulan', $bulanList);
                }
                $transTable = 'data_alat';
                $transIdCol = 'id_aset';
            } else { // working_hour
                $histQuery = DB::table('data_alat');
                if ($request->filled('start_date') && $request->filled('end_date')) {
                    $query_end_date = \Carbon\Carbon::parse($request->end_date)->endOfDay()->format('Y-m-d H:i:s');
                    $histQuery->whereBetween('data_alat.tanggal', [$request->start_date, $query_end_date]);
                } else {
                    if ($request->filled('tahun') && $request->tahun !== 'ALL') {
                        $histQuery->where('data_alat.tahun', $request->tahun);
                    }
                    if ($request->filled('bulan') && $request->bulan !== 'ALL') {
                        $histQuery->where('data_alat.bulan', $request->bulan);
                    }
                }
                $transTable = 'data_alat';
                $transIdCol = 'id_aset';
            }

            // Define hierarchy (from top to bottom) according to user request
            $hierarchy = [
                'group_aset' => 1,
                'area' => 2,
                'pt' => 3,
                'id_aset' => 4,
                'group_desc' => 5,
                'group_internal_order' => 6,
                'internal_order' => 7,
            ];
            $currentLevel = $hierarchy[$column] ?? 99;

            // Apply Field Filters STRICTLY to each query based on hierarchy
            $applyFilters = function($query, $tablePrefix, $idCol, $isTransTable = false) use ($request, $currentLevel) {
                // Beberapa kolom tidak ada langsung di fuel_transactions, tapi punya kolom mapping:
                //   group_desc           → io_desc    (ditangani di else-if di bawah)
                //   group_internal_order → io_group   (ditangani di else-if di bawah)
                //   pt                   → tidak ada ekuivalen, di-skip sepenuhnya
                $skipIfNotExists = function($col) use ($isTransTable, $tablePrefix) {
                    if ($isTransTable && $tablePrefix === 'fuel_transactions') {
                        // Return true = kolom tidak ada langsung → masuk ke else-if branch yang pakai nama kolom mapping
                        return in_array($col, ['pt', 'group_desc', 'group_internal_order']);
                    }
                    return false;
                };

                if ($currentLevel > 1 && $request->filled('group_aset') && $request->group_aset !== 'ALL') {
                    if (!$skipIfNotExists('group_aset')) $query->where($tablePrefix.'.group_aset', $request->group_aset);
                }
                if ($currentLevel > 2 && $request->filled('area') && $request->area !== 'ALL') {
                    if (!$skipIfNotExists('area')) $query->where($tablePrefix.'.area', $request->area);
                }
                if ($currentLevel > 3 && $request->filled('pt') && $request->pt !== 'ALL') {
                    if (!$skipIfNotExists('pt')) $query->where($tablePrefix.'.pt', $request->pt);
                }
                if ($currentLevel > 4 && $request->filled('id_aset') && $request->id_aset !== 'ALL') {
                    $query->where($tablePrefix.'.'.$idCol, $request->id_aset);
                }
                if ($currentLevel > 5 && $request->filled('group_desc') && $request->group_desc !== 'ALL') {
                    if (!$skipIfNotExists('group_desc')) {
                        $query->where($tablePrefix.'.group_desc', $request->group_desc);
                    } else if ($isTransTable && $tablePrefix === 'fuel_transactions') {
                        $query->where('fuel_transactions.io_desc', $request->group_desc);
                    }
                }
                if ($currentLevel > 6 && $request->filled('group_internal_order') && $request->group_internal_order !== 'ALL') {
                    if (!$skipIfNotExists('group_internal_order')) {
                        $query->where($tablePrefix.'.group_internal_order', $request->group_internal_order);
                    } else if ($isTransTable && $tablePrefix === 'fuel_transactions') {
                        $query->where('fuel_transactions.io_group', $request->group_internal_order);
                    }
                }
                if ($currentLevel > 7 && $request->filled('internal_order') && $request->internal_order !== 'ALL') {
                    if (!$skipIfNotExists('internal_order')) $query->where($tablePrefix.'.internal_order', $request->internal_order);
                }
            };

            $applyFilters($masterQuery, 'master_asets', 'unit_code', false);
            $applyFilters($histQuery, $transTable, $transIdCol, true);

            $masterValues = $masterQuery->whereNotNull('master_asets.'.$masterCol)->distinct()->pluck('master_asets.'.$masterCol)->toArray();
            
            $historicalValues = [];
            $transCol = $column === 'id_aset' ? $transIdCol : $column;
            
            // If the column doesn't exist in fuel_transactions, skip fetching from it
            $skipTransCol = false;
            if ($transTable === 'fuel_transactions' && in_array($transCol, ['pt'])) {
                $skipTransCol = true;
            }

            if (!$skipTransCol) {
                if ($transTable === 'fuel_transactions' && $transCol === 'group_internal_order') {
                    // Mapping: group_internal_order → io_group di fuel_transactions
                    $historicalValues = $histQuery->whereNotNull('fuel_transactions.io_group')->distinct()->pluck('fuel_transactions.io_group')->toArray();
                } else if ($transTable === 'fuel_transactions' && $transCol === 'group_desc') {
                    // Mapping: group_desc → io_desc di fuel_transactions
                    $historicalValues = $histQuery->whereNotNull('fuel_transactions.io_desc')->distinct()->pluck('fuel_transactions.io_desc')->toArray();
                } else {
                    $historicalValues = $histQuery->whereNotNull($transTable.'.'.$transCol)->distinct()->pluck($transTable.'.'.$transCol)->toArray();
                }
            }
            
            $merged = array_unique(array_merge($masterValues, $historicalValues));
            $merged = array_filter($merged, function($value) { return $value !== '' && $value !== '-'; });
            sort($merged);
            return array_values($merged);
        };

        return response()->json([
            'filterUnits' => $getMergedOptions('id_aset', 'unit_code'),
            'filterGroups' => $getMergedOptions('group_aset'),
            'filterAreas' => $getMergedOptions('area'),
            'filterIoGroups' => $getMergedOptions('group_internal_order'),
            'filterInternalOrders' => $getMergedOptions('internal_order'),
            'filterGroupDescs' => $getMergedOptions('group_desc'),
            'filterPts' => $getMergedOptions('pt'),
        ]);
    }
}
