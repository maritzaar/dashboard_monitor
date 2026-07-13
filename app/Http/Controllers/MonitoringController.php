<?php

namespace App\Http\Controllers;

use App\Models\DataAlat;
use App\Models\FuelTransaction;
use App\Models\MasterAset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
    private function getFilters()
    {
        return [
            'filterUnits' => MasterAset::select('unit_code')->where('unit_code', 'like', '%-%')->distinct()->orderBy('unit_code')->pluck('unit_code'),
            'filterGroups' => MasterAset::select('group_aset')->whereNotNull('group_aset')->distinct()->orderBy('group_aset')->pluck('group_aset'),
            'filterAreas' => MasterAset::select('area')->whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
            'filterIoGroups' => MasterAset::select('group_internal_order')->whereNotNull('group_internal_order')->distinct()->orderBy('group_internal_order')->pluck('group_internal_order'),
            'filterInternalOrders' => MasterAset::select('internal_order')->whereNotNull('internal_order')->distinct()->orderBy('internal_order')->pluck('internal_order'),
            'filterGroupDescs' => \App\Models\DataAlat::select('group_desc')->whereNotNull('group_desc')->distinct()->orderBy('group_desc')->pluck('group_desc'),
        ];
    }

    public function workingHour(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (!$bulan || !$tahun) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            $bulan = $latestData ? $latestData->bulan : now()->format('F');
            $tahun = $latestData ? $latestData->tahun : now()->year;
        }

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');
        $group_desc = $request->get('group_desc');

        $query = DataAlat::query()
            ->leftJoin('master_asets', 'data_alat.id_aset', '=', 'master_asets.unit_code');
        
        if (!empty($bulan) && $bulan !== 'ALL') $query->where('data_alat.bulan', $bulan);
        if (!empty($tahun) && $tahun !== 'ALL') $query->where('data_alat.tahun', $tahun);
        if (!empty($id_aset) && $id_aset !== 'ALL') {
            $query->where(function($q) use ($id_aset) {
                $q->where('master_asets.unit_code', $id_aset)
                  ->orWhere('data_alat.id_aset', $id_aset);
            });
        }
        if (!empty($group_aset) && $group_aset !== 'ALL') $query->where('data_alat.group_aset', $group_aset);
        if (!empty($area) && $area !== 'ALL') $query->where('data_alat.area', $area);
        if (!empty($group_internal_order) && $group_internal_order !== 'ALL') $query->where('data_alat.group_internal_order', $group_internal_order);
        if (!empty($internal_order) && $internal_order !== 'ALL') $query->where('data_alat.internal_order', $internal_order);
        if (!empty($group_desc) && $group_desc !== 'ALL') $query->where('data_alat.group_desc', $group_desc);

        $reports = $query->select(
                'data_alat.id as id',
                DB::raw('COALESCE(master_asets.unit_code, data_alat.id_aset) as id_aset'),
                'data_alat.tanggal as tanggal',
                'data_alat.internal_order as internal_order',
                'data_alat.model as model',
                'data_alat.group_aset as group_aset',
                'data_alat.area as area',
                'data_alat.group_internal_order as group_internal_order',
                'data_alat.pt as pt',
                'data_alat.group_desc as group_desc',
                'data_alat.waktu_kerja as total_kerja',
                'data_alat.waktu_operasi as total_operasi',
                'data_alat.waktu_idle as total_idle',
                'data_alat.persen_idle as avg_idle'
            )
            ->orderBy('data_alat.tanggal', 'desc')
            ->get();

        $stats = (object)[
            'total_aset' => $reports->pluck('id_aset')->unique()->count(),
            'total_kerja' => $reports->sum('total_kerja'),
            'total_operasi' => $reports->sum('total_operasi'),
            'total_idle' => $reports->sum('total_idle'),
            'avg_idle' => $reports->count() > 0 ? $reports->avg('avg_idle') : 0,
        ];

        $chartData = $reports->groupBy('id_aset')->map(function($group) {
            return (object)[
                'id_aset' => $group->first()->id_aset,
                'total_kerja' => $group->sum('total_kerja'),
                'total_idle' => $group->sum('total_idle'),
            ];
        })->values();

        $filters = $this->getFilters();

        return view('monitoring.working_hour', array_merge(compact(
            'reports', 'stats', 'chartData', 'bulan', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc'
        ), $filters));
    }

    public function workingHourDetail(Request $request, $idAset)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (!$bulan || !$tahun) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            $bulan = $latestData ? $latestData->bulan : now()->format('F');
            $tahun = $latestData ? $latestData->tahun : now()->year;
        }

        $alat = DataAlat::where('id_aset', $idAset)->first();

        $data = DataAlat::where('id_aset', $idAset)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('monitoring.working_hour_detail', compact('alat', 'data', 'bulan', 'tahun', 'idAset'));
    }

    public function fuel(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        
        if (!$bulan || !$tahun) {
            $latest = FuelTransaction::orderBy('created_at', 'desc')->first();
            $bulan = $latest ? $latest->bulan : now()->format('F');
            $tahun = $latest ? $latest->tahun : now()->year;
        }

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');

        $query = FuelTransaction::query();
        
        if (!empty($bulan) && $bulan !== 'ALL') {
            $query->where(function($q) use ($bulan) {
                $q->where('bulan', $bulan)->orWhere('bulan', substr($bulan, 0, 3));
            });
        }
        if (!empty($tahun) && $tahun !== 'ALL') $query->where('tahun', $tahun);
        if (!empty($id_aset) && $id_aset !== 'ALL') $query->where('unit_code', $id_aset);
        if (!empty($group_aset) && $group_aset !== 'ALL') $query->where('group_aset', $group_aset);
        if (!empty($area) && $area !== 'ALL') $query->where('area', $area);
        if (!empty($group_internal_order) && $group_internal_order !== 'ALL') $query->where('internal_order', 'like', '%' . $group_internal_order . '%');
        if (!empty($internal_order) && $internal_order !== 'ALL') $query->where('internal_order', $internal_order);

        $reports = $query->select(
                'unit_code as id_aset', 'internal_order', 'group_aset', 'area', 'total_quantity as actual_fuel', 'bulan', 'tahun'
            )
            ->get();

        // Calculate aggregated fuel per asset (ordered descending by fuel usage)
        $chartData = $reports->groupBy('id_aset')->map(function($group) {
            return (object)[
                'id_aset' => $group->first()->id_aset,
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortByDesc('actual_fuel')->values();

        // Calculate aggregated fuel per asset group
        $groupChartData = $reports->groupBy('group_aset')->map(function($group) {
            return (object)[
                'group_aset' => $group->first()->group_aset ?? 'Lain-lain',
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortByDesc('actual_fuel')->values();

        // Calculate aggregated fuel per area
        $areaChartData = $reports->groupBy('area')->map(function($group) {
            return (object)[
                'area' => $group->first()->area ?? 'Lain-lain',
                'actual_fuel' => $group->sum('actual_fuel'),
            ];
        })->sortByDesc('actual_fuel')->values();

        $totalAsetCount = $reports->pluck('id_aset')->unique()->count();
        $stats = (object)[
            'total_aset' => $totalAsetCount,
            'actual_fuel' => $reports->sum('actual_fuel'),
            'avg_fuel' => $totalAsetCount > 0 ? $reports->sum('actual_fuel') / $totalAsetCount : 0,
            'max_fuel_val' => $chartData->first() ? $chartData->first()->actual_fuel : 0,
            'max_fuel_aset' => $chartData->first() ? $chartData->first()->id_aset : '-',
        ];

        $filters = $this->getFilters();

        return view('monitoring.fuel', array_merge(compact(
            'reports', 'stats', 'chartData', 'groupChartData', 'areaChartData', 'bulan', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order'
        ), $filters));
    }

    public function fuelDetail(Request $request, $idAset)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (!$bulan || !$tahun) {
            $latest = FuelTransaction::orderBy('created_at', 'desc')->first();
            $bulan = $latest ? $latest->bulan : now()->format('F');
            $tahun = $latest ? $latest->tahun : now()->year;
        }

        $alat = FuelTransaction::where('unit_code', $idAset)->first();
        
        $data = FuelTransaction::where('unit_code', $idAset)
            ->where(function($q) use ($bulan) {
                if ($bulan !== 'ALL') {
                    $q->where('bulan', $bulan)->orWhere('bulan', substr($bulan, 0, 3));
                }
            });
            
        if ($tahun !== 'ALL') {
            $data = $data->where('tahun', $tahun);
        }
            
        $data = $data->orderBy('created_at', 'asc')->get();

        return view('monitoring.fuel_detail', compact('alat', 'data', 'bulan', 'tahun', 'idAset'));
    }

    public function export(Request $request)
    {
        $filters = $request->only([
            'tahun', 'bulan', 'group_aset', 'area', 'id_aset', 
            'group_desc', 'group_internal_order', 'internal_order'
        ]);

        if (empty($filters['bulan']) || empty($filters['tahun'])) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            if ($latestData) {
                $filters['bulan'] = $latestData->bulan;
                $filters['tahun'] = $latestData->tahun;
            } else {
                $filters['bulan'] = now()->format('F');
                $filters['tahun'] = now()->year;
            }
        }

        // Build a nice filename
        $nameParts = ['laporan_monitoring_alat'];
        if (!empty($filters['group_aset']) && $filters['group_aset'] !== 'ALL') $nameParts[] = $filters['group_aset'];
        if (!empty($filters['area']) && $filters['area'] !== 'ALL') $nameParts[] = $filters['area'];
        $nameParts[] = strtolower($filters['bulan']);
        $nameParts[] = $filters['tahun'];
        
        $fileName = implode('_', $nameParts) . '.xlsx';

        return Excel::download(new \App\Exports\DataAlatExport($filters), $fileName);
    }
}