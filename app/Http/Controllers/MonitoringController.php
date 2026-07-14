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
    private function getFilters()
    {
        return [
            'filterUnits' => MasterAset::select('unit_code')->where('unit_code', 'like', '%-%')->distinct()->orderBy('unit_code')->pluck('unit_code'),
            'filterGroups' => MasterAset::select('group_aset')->whereNotNull('group_aset')->distinct()->orderBy('group_aset')->pluck('group_aset'),
            'filterAreas' => MasterAset::select('area')->whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
            'filterIoGroups' => MasterAset::select('group_internal_order')->whereNotNull('group_internal_order')->distinct()->orderBy('group_internal_order')->pluck('group_internal_order'),
            'filterInternalOrders' => MasterAset::select('internal_order')->whereNotNull('internal_order')->distinct()->orderBy('internal_order')->pluck('internal_order'),
            'filterGroupDescs' => MasterAset::select('group_desc')->whereNotNull('group_desc')->distinct()->orderBy('group_desc')->pluck('group_desc'),
            'filterPts' => MasterAset::select('pt')->whereNotNull('pt')->where('pt', '!=', '-')->distinct()->orderBy('pt')->pluck('pt'),
        ];
    }

    public function workingHour(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if (! $start_date) {
            $start_date = now()->startOfMonth()->format('Y-m-d');
        }
        if (! $end_date) {
            $end_date = now()->endOfMonth()->format('Y-m-d');
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
            $query->where('data_alat.group_aset', $group_aset);
        }
        if (! empty($area) && $area !== 'ALL') {
            $query->where('data_alat.area', $area);
        }
        if (! empty($group_internal_order) && $group_internal_order !== 'ALL') {
            $query->where('data_alat.group_internal_order', $group_internal_order);
        }
        if (! empty($internal_order) && $internal_order !== 'ALL') {
            $query->where('data_alat.internal_order', $internal_order);
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

        $filters = $this->getFilters();

        return view('monitoring.working_hour', array_merge(compact(
            'reports', 'stats', 'chartData', 'start_date', 'end_date',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc', 'pt'
        ), $filters));
    }

    public function workingHourDetail(Request $request, $idAset)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        if (! $start_date || ! $end_date) {
            $start_date = now()->startOfMonth()->format('Y-m-d');
            $end_date = now()->endOfMonth()->format('Y-m-d');
        }

        $alat = DataAlat::where('id_aset', $idAset)->first();

        $query_end_date = \Carbon\Carbon::parse($end_date)->endOfDay()->format('Y-m-d H:i:s');

        $data = DataAlat::where('id_aset', $idAset)
            ->whereBetween('tanggal', [$start_date, $query_end_date])
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('monitoring.working_hour_detail', compact('alat', 'data', 'start_date', 'end_date', 'idAset'));
    }

    public function fuel(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (! $bulan) {
            $bulan = 'ALL';
        }
        if (! $tahun) {
            $tahun = 'ALL';
        }

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order');
        $group_desc = $request->get('group_desc');
        $pt = $request->get('pt');

        $query = FuelTransaction::query()
            ->leftJoin('master_asets', 'fuel_transactions.unit_code', '=', 'master_asets.unit_code');

        if (! empty($bulan) && $bulan !== 'ALL') {
            $query->where(function ($q) use ($bulan) {
                $q->where('fuel_transactions.bulan', $bulan)->orWhere('fuel_transactions.bulan', substr($bulan, 0, 3));
            });
        }
        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('fuel_transactions.tahun', $tahun);
        }
        if (! empty($id_aset) && $id_aset !== 'ALL') {
            $query->where('fuel_transactions.unit_code', $id_aset);
        }
        if (! empty($group_aset) && $group_aset !== 'ALL') {
            $query->where('fuel_transactions.group_aset', $group_aset);
        }
        if (! empty($area) && $area !== 'ALL') {
            $query->where('fuel_transactions.area', $area);
        }
        if (! empty($group_internal_order) && $group_internal_order !== 'ALL') {
            $query->where('fuel_transactions.internal_order', 'like', '%'.$group_internal_order.'%');
        }
        if (! empty($internal_order) && $internal_order !== 'ALL') {
            $query->where('fuel_transactions.internal_order', $internal_order);
        }
        if (! empty($group_desc) && $group_desc !== 'ALL') {
            $query->where('master_asets.group_desc', $group_desc);
        }
        if (! empty($pt) && $pt !== 'ALL') {
            $query->where('master_asets.pt', $pt);
        }

        $reports = $query->select(
            'fuel_transactions.id as id',
            'fuel_transactions.unit_code as id_aset',
            'fuel_transactions.internal_order',
            'fuel_transactions.group_aset',
            'fuel_transactions.area',
            'fuel_transactions.total_quantity as actual_fuel',
            'fuel_transactions.bulan',
            'fuel_transactions.tahun',
            'master_asets.pt as pt',
            'master_asets.group_desc as group_desc',
            'master_asets.group_internal_order as group_internal_order'
        )
            ->get();

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

        $totalAsetCount = $reports->pluck('id_aset')->unique()->count();
        $stats = (object) [
            'total_aset' => $totalAsetCount,
            'actual_fuel' => $reports->sum('actual_fuel'),
            'avg_fuel' => $totalAsetCount > 0 ? $reports->sum('actual_fuel') / $totalAsetCount : 0,
            'max_fuel_val' => $chartData->first() ? $chartData->first()->actual_fuel : 0,
            'max_fuel_aset' => $chartData->first() ? $chartData->first()->id_aset : '-',
        ];

        $filters = $this->getFilters();

        return view('monitoring.fuel', array_merge(compact(
            'reports', 'stats', 'chartData', 'groupChartData', 'areaChartData', 'bulan', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order', 'group_desc', 'pt'
        ), $filters));
    }

    public function fuelDetail(Request $request, $idAset)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (! $bulan || ! $tahun) {
            $latest = FuelTransaction::orderBy('created_at', 'desc')->first();
            $bulan = $latest ? $latest->bulan : now()->format('F');
            $tahun = $latest ? $latest->tahun : now()->year;
        }

        $alat = FuelTransaction::where('unit_code', $idAset)->first();

        $data = FuelTransaction::where('unit_code', $idAset)
            ->where(function ($q) use ($bulan) {
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
            'start_date', 'end_date', 'bulan', 'tahun', 'group_aset', 'area', 'id_aset',
            'group_desc', 'group_internal_order', 'internal_order',
        ]);

        if (empty($filters['bulan']) && empty($filters['tahun']) && empty($filters['start_date'])) {
            $filters['bulan'] = now()->format('F');
            $filters['tahun'] = now()->year;
        }

        // Build a nice filename
        $nameParts = ['laporan_monitoring_alat'];
        if (! empty($filters['group_aset']) && $filters['group_aset'] !== 'ALL') {
            $nameParts[] = $filters['group_aset'];
        }
        if (! empty($filters['area']) && $filters['area'] !== 'ALL') {
            $nameParts[] = $filters['area'];
        }
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            $nameParts[] = $filters['start_date'].'_to_'.$filters['end_date'];
        } else {
            $nameParts[] = strtolower($filters['bulan'] ?? 'all');
            $nameParts[] = $filters['tahun'] ?? 'all';
        }

        $fileName = implode('_', $nameParts).'.xlsx';

        return Excel::download(new DataAlatExport($filters), $fileName);
    }

    public function getFilterOptions(Request $request)
    {
        $query = MasterAset::query();

        if ($request->filled('pt') && $request->pt !== 'ALL') {
            $query->where('pt', $request->pt);
        }
        if ($request->filled('area') && $request->area !== 'ALL') {
            $query->where('area', $request->area);
        }
        if ($request->filled('group_aset') && $request->group_aset !== 'ALL') {
            $query->where('group_aset', $request->group_aset);
        }
        if ($request->filled('group_desc') && $request->group_desc !== 'ALL') {
            $query->where('group_desc', $request->group_desc);
        }
        if ($request->filled('group_internal_order') && $request->group_internal_order !== 'ALL') {
            $query->where('group_internal_order', $request->group_internal_order);
        }
        if ($request->filled('internal_order') && $request->internal_order !== 'ALL') {
            $query->where('internal_order', $request->internal_order);
        }
        if ($request->filled('id_aset') && $request->id_aset !== 'ALL') {
            $query->where('unit_code', $request->id_aset);
        }

        $validUnits = (clone $query)->pluck('unit_code')->toArray();

        // Historical Internal Orders
        $historicalIOs = DB::table('data_alat')->whereIn('id_aset', $validUnits)->whereNotNull('internal_order')->distinct()->pluck('internal_order')->toArray();
        $fuelIOs = DB::table('fuel_transactions')->whereIn('unit_code', $validUnits)->whereNotNull('internal_order')->distinct()->pluck('internal_order')->toArray();
        $masterIOs = (clone $query)->whereNotNull('internal_order')->distinct()->pluck('internal_order')->toArray();
        $filterInternalOrders = array_unique(array_merge($masterIOs, $historicalIOs, $fuelIOs));
        sort($filterInternalOrders);

        // Historical Group Descs
        $historicalDescs = DB::table('data_alat')->whereIn('id_aset', $validUnits)->whereNotNull('group_desc')->distinct()->pluck('group_desc')->toArray();
        $masterDescs = (clone $query)->whereNotNull('group_desc')->distinct()->pluck('group_desc')->toArray();
        $filterGroupDescs = array_unique(array_merge($masterDescs, $historicalDescs));
        sort($filterGroupDescs);

        return response()->json([
            'filterUnits' => (clone $query)->where('unit_code', 'like', '%-%')->distinct()->orderBy('unit_code')->pluck('unit_code'),
            'filterGroups' => (clone $query)->whereNotNull('group_aset')->distinct()->orderBy('group_aset')->pluck('group_aset'),
            'filterAreas' => (clone $query)->whereNotNull('area')->distinct()->orderBy('area')->pluck('area'),
            'filterIoGroups' => (clone $query)->whereNotNull('group_internal_order')->distinct()->orderBy('group_internal_order')->pluck('group_internal_order'),
            'filterInternalOrders' => array_values($filterInternalOrders),
            'filterGroupDescs' => array_values($filterGroupDescs),
            'filterPts' => (clone $query)->whereNotNull('pt')->where('pt', '!=', '-')->distinct()->orderBy('pt')->pluck('pt'),
        ]);
    }
}
