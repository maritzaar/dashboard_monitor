<?php

namespace App\Http\Controllers;

use App\Models\DataAlat;
use App\Models\MonitoringSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MonitoringController extends Controller
{
   public function index(Request $request)
   {
       $bulan = $request->get('bulan');
       $tahun = $request->get('tahun');

       if (!$bulan || !$tahun) {
           $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
           if ($latestData) {
               $bulan = $latestData->bulan;
               $tahun = $latestData->tahun;
           } else {
               $bulan = now()->format('F');
               $tahun = now()->year;
           }
       }

       // 1. Build Telemetry Query
       // Fleet Utilization harus dihitung per id_aset penuh. Prefix 4 karakter
       // hanya disimpan sebagai info bantu, bukan identitas utama unit.
       $telemetryRaw = DataAlat::where('bulan', $bulan)
           ->where('tahun', $tahun)
           ->select(
               DB::raw('SUBSTRING(id_aset, 1, 4) as asset_code'),
               'id_aset',
               'internal_order', 'model', 'group_aset', 'area',
               DB::raw('SUM(waktu_kerja) as total_kerja'),
               DB::raw('SUM(waktu_operasi) as total_operasi'),
               DB::raw('SUM(waktu_idle) as total_idle'),
               DB::raw('AVG(persen_idle) as avg_idle'),
               DB::raw('SUM(total_bahan_bakar) as telemetry_fuel')
           )->groupBy('id_aset', DB::raw('SUBSTRING(id_aset, 1, 4)'), 'internal_order', 'model', 'group_aset', 'area')
            ->get();

       // 2. Build Actual Fuel Query from transactions
       // Fuel menyimpan bulan dalam format full name (January, February, dst)
       // Coba match dengan bulan penuh, lalu fallback ke 3 karakter pertama
       $fuelRaw = \App\Models\FuelTransaction::where(function($q) use ($bulan) {
               $q->where('bulan', $bulan)
                 ->orWhere('bulan', substr($bulan, 0, 3));
           })
           ->where('tahun', $tahun)
           ->select(
               DB::raw('SUBSTRING(unit_code, 1, 4) as asset_code'),
               'internal_order', 'group_aset', 'area',
               DB::raw('SUM(total_quantity) as actual_fuel')
           )->groupBy(DB::raw('SUBSTRING(unit_code, 1, 4)'), 'internal_order', 'group_aset', 'area')
            ->get();

       // 3. Merge Telemetry & Fuel Data (Full Outer Join in-memory)
       // Telemetry menggunakan id_aset penuh agar unit dengan prefix sama tidak tergabung.
       $telemetryMap = [];
       foreach ($telemetryRaw as $t) {
           $key = $t->id_aset . '|' . strtoupper(trim($t->internal_order ?? ''));
           $t->id_aset_full = $t->id_aset;
           $telemetryMap[$key] = $t;
       }

       $fuelMap = [];
       foreach ($fuelRaw as $f) {
           $key = $f->asset_code . '|' . strtoupper(trim($f->internal_order ?? ''));
           $fuelMap[$key] = $f;
       }

       $allKeys = array_unique(array_merge(array_keys($telemetryMap), array_keys($fuelMap)));
       sort($allKeys);

       $perAset = collect();
       foreach ($allKeys as $key) {
           $parts = explode('|', $key, 2);
           $code = $parts[0];
           $io   = $parts[1] ?? '';
           $t = $telemetryMap[$key] ?? null;
           $f = $fuelMap[$key] ?? null;

           $perAset->push((object)[
               // Gunakan full id_aset dari telemetri agar link detail valid
               'id_aset'       => $t?->id_aset_full ?? $code,
               'asset_code'    => $code,
               'internal_order'=> $io !== '' ? $io : '-',
               'model'         => $t?->model ?? '-',
               'group_aset'    => $t?->group_aset ?? $f?->group_aset ?? '-',
               'area'          => $t?->area ?? $f?->area ?? '-',
               'total_kerja'   => $t?->total_kerja ?? 0,
               'total_operasi' => $t?->total_operasi ?? 0,
               'total_idle'    => $t?->total_idle ?? 0,
               'avg_idle'      => $t?->avg_idle ?? 0,
               'total_bakar'   => $f?->actual_fuel ?? 0,
               'telemetry_fuel'=> $t?->telemetry_fuel ?? 0,
           ]);
       }

       // 4. Calculate Stats Card
       $stats = (object)[
           'total_aset' => $perAset->count(),
           'total_waktu_kerja' => $perAset->sum('total_kerja'),
           'total_waktu_operasi' => $perAset->sum('total_operasi'),
           'total_waktu_idle' => $perAset->sum('total_idle'),
           'avg_idle' => $perAset->count() > 0 ? $perAset->avg('avg_idle') : 0,
           'total_bahan_bakar' => $perAset->sum('total_bakar'), // Actual Fuel total
       ];

       return view('monitoring.index', compact('stats', 'perAset', 'bulan', 'tahun'));
   }

    public function chart(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (!$bulan || !$tahun) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            if ($latestData) {
                $bulan = $latestData->bulan;
                $tahun = $latestData->tahun;
            } else {
                $bulan = now()->format('F');
                $tahun = now()->year;
            }
        }

        $chartData = DataAlat::where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->select(
                'tanggal',
                DB::raw('SUM(waktu_kerja) as total_kerja'),
                DB::raw('SUM(waktu_operasi) as total_operasi'),
                DB::raw('SUM(waktu_idle) as total_idle')
            )
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        return response()->json($chartData);
    }

    public function detail(Request $request, $idAset)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (!$bulan || !$tahun) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            if ($latestData) {
                $bulan = $latestData->bulan;
                $tahun = $latestData->tahun;
            } else {
                $bulan = now()->format('F');
                $tahun = now()->year;
            }
        }

        // Cari aset dengan exact match dulu, jika tidak ada gunakan LIKE prefix
        // Ini agar URL yang hanya berisi 4 karakter asset_code tetap bisa menemukan data
        $alat = DataAlat::where('id_aset', $idAset)->first();
        if (!$alat) {
            $alat = DataAlat::where('id_aset', 'like', $idAset . '%')->first();
        }

        // Query data detail: exact match dulu, jika tidak ada pakai LIKE prefix
        $data = DataAlat::where(function($q) use ($idAset) {
                $q->where('id_aset', $idAset)
                  ->orWhere('id_aset', 'like', $idAset . '%');
            })
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->orderBy('tanggal', 'asc')
            ->get();

        return view('monitoring.detail', compact('alat', 'data', 'bulan', 'tahun'));
    }

    public function export(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');

        if (!$bulan || !$tahun) {
            $latestData = DataAlat::orderBy('tanggal', 'desc')->first();
            if ($latestData) {
                $bulan = $latestData->bulan;
                $tahun = $latestData->tahun;
            } else {
                $bulan = now()->format('F');
                $tahun = now()->year;
            }
        }

        $fileName = sprintf('laporan_monitoring_alat_%s_%s.xlsx', strtolower($bulan), $tahun);

        return Excel::download(new \App\Exports\DataAlatExport($bulan, $tahun), $fileName);
    }

    // ─── Laporan Dashboard ────────────────────────────────────────────────────

    public function laporan(Request $request)
    {
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun');
        
        if (!$bulan || !$tahun) {
            $latest = DataAlat::orderBy('tanggal', 'desc')->first();
            $bulan  = $latest?->bulan ?? now()->format('F');
            $tahun  = $latest?->tahun ?? now()->year;
        }

        $id_aset = $request->get('id_aset');
        $group_aset = $request->get('group_aset');
        $area = $request->get('area');
        $group_internal_order = $request->get('group_internal_order');
        $internal_order = $request->get('internal_order'); // New filter

        // 1. Build Telemetry Query
        $telemetryQuery = DataAlat::query();
        if (!empty($bulan) && $bulan !== 'ALL') {
            $telemetryQuery->where('bulan', $bulan);
        }
        if (!empty($tahun) && $tahun !== 'ALL') {
            $telemetryQuery->where('tahun', $tahun);
        }
        if (!empty($id_aset)) {
            $telemetryQuery->where('id_aset', $id_aset);
        }
        if (!empty($group_aset)) {
            $telemetryQuery->where('group_aset', $group_aset);
        }
        if (!empty($area)) {
            $telemetryQuery->where('area', $area);
        }
        if (!empty($group_internal_order)) {
            $telemetryQuery->where('group_internal_order', $group_internal_order);
        }
        if (!empty($internal_order)) {
            $telemetryQuery->where('internal_order', $internal_order);
        }

        // Group by full id_aset so units with the same 4-character prefix stay separate.
        $telemetryRaw = $telemetryQuery->select(
            DB::raw('SUBSTRING(id_aset, 1, 4) as asset_code'),
            'id_aset',
            'internal_order', 'model', 'group_aset', 'area', 'group_internal_order',
            DB::raw('SUM(waktu_kerja) as total_kerja'),
            DB::raw('SUM(waktu_operasi) as total_operasi'),
            DB::raw('SUM(waktu_idle) as total_idle'),
            DB::raw('AVG(persen_idle) as avg_idle'),
            DB::raw('SUM(total_bahan_bakar) as telemetry_fuel')
        )->groupBy('id_aset', DB::raw('SUBSTRING(id_aset, 1, 4)'), 'internal_order', 'model', 'group_aset', 'area', 'group_internal_order')
         ->get();

        // 2. Build Actual Fuel Query from transactions
        // Fix: match bulan penuh (January) atau 3 karakter (Jan) karena format bisa bervariasi
        $fuelQuery = \App\Models\FuelTransaction::query();
        if (!empty($bulan) && $bulan !== 'ALL') {
            $fuelQuery->where(function($q) use ($bulan) {
                $q->where('bulan', $bulan)
                  ->orWhere('bulan', substr($bulan, 0, 3));
            });
        }
        if (!empty($tahun) && $tahun !== 'ALL') {
            $fuelQuery->where('tahun', $tahun);
        }
        if (!empty($id_aset)) {
            $fuelQuery->where('unit_code', $id_aset);
        }
        if (!empty($group_aset)) {
            $fuelQuery->where('group_aset', $group_aset);
        }
        if (!empty($area)) {
            $fuelQuery->where('area', $area);
        }
        if (!empty($group_internal_order)) {
            $fuelQuery->where('internal_order', 'like', '%' . $group_internal_order . '%');
        }
        if (!empty($internal_order)) {
            $fuelQuery->where('internal_order', $internal_order);
        }

        // Group by 4-char prefix of unit_code and internal_order
        $fuelRaw = $fuelQuery->select(
            DB::raw('SUBSTRING(unit_code, 1, 4) as asset_code'),
            'internal_order', 'group_aset', 'area',
            DB::raw('SUM(total_quantity) as actual_fuel')
        )->groupBy(DB::raw('SUBSTRING(unit_code, 1, 4)'), 'internal_order', 'group_aset', 'area')
         ->get();

        // 3. Merge Telemetry & Fuel Data (Full Outer Join in-memory)
        $telemetryMap = [];
        foreach ($telemetryRaw as $t) {
            $key = $t->id_aset . '|' . strtoupper(trim($t->internal_order ?? ''));
            $t->id_aset_full = $t->id_aset;
            $telemetryMap[$key] = $t;
        }

        $fuelMap = [];
        foreach ($fuelRaw as $f) {
            $key = $f->asset_code . '|' . strtoupper(trim($f->internal_order ?? ''));
            $fuelMap[$key] = $f;
        }

        $allKeys = array_unique(array_merge(array_keys($telemetryMap), array_keys($fuelMap)));
        sort($allKeys);

        $reports = collect();
        foreach ($allKeys as $key) {
            $parts = explode('|', $key, 2);
            $code = $parts[0];
            $io   = $parts[1] ?? '';
            $t = $telemetryMap[$key] ?? null;
            $f = $fuelMap[$key] ?? null;

            $reports->push((object)[
                'id_aset'             => $t?->id_aset_full ?? $code,
                'asset_code'          => $code,
                'internal_order'      => $io !== '' ? $io : '-',
                'model'               => $t?->model ?? '-',
                'group_aset'          => $t?->group_aset ?? $f?->group_aset ?? '-',
                'area'                => $t?->area ?? $f?->area ?? '-',
                'group_internal_order'=> $t?->group_internal_order ?? '-',
                'total_kerja'         => $t?->total_kerja ?? 0,
                'total_operasi'       => $t?->total_operasi ?? 0,
                'total_idle'          => $t?->total_idle ?? 0,
                'avg_idle'            => $t?->avg_idle ?? 0,
                'telemetry_fuel'      => $t?->telemetry_fuel ?? 0,
                'actual_fuel'         => $f?->actual_fuel ?? 0,
            ]);
        }

        // 4. Calculate Stats Card
        $stats = (object)[
            'total_aset' => $reports->count(),
            'total_kerja' => $reports->sum('total_kerja'),
            'total_operasi' => $reports->sum('total_operasi'),
            'total_idle' => $reports->sum('total_idle'),
            'avg_idle' => $reports->count() > 0 ? $reports->avg('avg_idle') : 0,
            'telemetry_fuel' => $reports->sum('telemetry_fuel'),
            'actual_fuel' => $reports->sum('actual_fuel'),
        ];

        // 5. Dropdown lists from all data (unfiltered for options)
        $telemetryUnits = DataAlat::select('id_aset')->distinct()->pluck('id_aset')->toArray();
        $fuelUnits = \App\Models\FuelTransaction::select('unit_code')->distinct()->pluck('unit_code')->toArray();
        $filterUnits = array_unique(array_merge($telemetryUnits, $fuelUnits));
        sort($filterUnits);

        $telemetryGroups = DataAlat::select('group_aset')->whereNotNull('group_aset')->distinct()->pluck('group_aset')->toArray();
        $fuelGroups = \App\Models\FuelTransaction::select('group_aset')->whereNotNull('group_aset')->distinct()->pluck('group_aset')->toArray();
        $filterGroups = array_unique(array_merge($telemetryGroups, $fuelGroups));
        sort($filterGroups);

        $telemetryAreas = DataAlat::select('area')->whereNotNull('area')->distinct()->pluck('area')->toArray();
        $fuelAreas = \App\Models\FuelTransaction::select('area')->whereNotNull('area')->distinct()->pluck('area')->toArray();
        $filterAreas = array_unique(array_merge($telemetryAreas, $fuelAreas));
        sort($filterAreas);

        $filterIoGroups = DataAlat::select('group_internal_order')
            ->whereNotNull('group_internal_order')
            ->distinct()
            ->orderBy('group_internal_order')
            ->pluck('group_internal_order');

        // Fetch unique internal orders for the filter
        $telemetryIos = DataAlat::select('internal_order')->whereNotNull('internal_order')->distinct()->pluck('internal_order')->toArray();
        $fuelIos = \App\Models\FuelTransaction::select('internal_order')->whereNotNull('internal_order')->distinct()->pluck('internal_order')->toArray();
        $filterInternalOrders = array_unique(array_merge($telemetryIos, $fuelIos));
        sort($filterInternalOrders);

        return view('monitoring.laporan', compact(
            'reports', 'stats', 'bulan', 'tahun',
            'id_aset', 'group_aset', 'area', 'group_internal_order', 'internal_order',
            'filterUnits', 'filterGroups', 'filterAreas', 'filterIoGroups', 'filterInternalOrders'
        ));
    }
}
