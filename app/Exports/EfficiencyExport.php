<?php

namespace App\Exports;

use App\Models\MasterAset;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EfficiencyExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $bulan_dari = $this->filters['bulan_dari'] ?? $this->filters['bulan'] ?? 'ALL';
        $bulan_sampai = $this->filters['bulan_sampai'] ?? $this->filters['bulan'] ?? 'ALL';
        $tahun = $this->filters['tahun'] ?? date('Y');

        $telemetrySub = DB::table('data_alat');
        $hasBulan = ($bulan_dari !== 'ALL') || ($bulan_sampai !== 'ALL');
        if ($hasBulan) {
            $all = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $fromIdx = ($bulan_dari && $bulan_dari !== 'ALL') ? array_search($bulan_dari, $all) : 0;
            $toIdx   = ($bulan_sampai && $bulan_sampai !== 'ALL') ? array_search($bulan_sampai, $all) : 11;
            if ($fromIdx === false) $fromIdx = 0;
            if ($toIdx   === false) $toIdx   = 11;
            if ($fromIdx > $toIdx) [$fromIdx, $toIdx] = [$toIdx, $fromIdx];
            $months = array_slice($all, $fromIdx, $toIdx - $fromIdx + 1);
            $bulanList = array_unique(array_merge($months, array_map(fn($m) => substr($m, 0, 3), $months)));
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
        if ($hasBulan) {
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

        if ($hasBulan) {
            $query->whereIn('periods.bulan', $bulanList);
        }
        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('periods.tahun', $tahun);
        }

        if (! empty($this->filters['id_aset']) && $this->filters['id_aset'] !== 'ALL') {
            $query->where('master_asets.unit_code', $this->filters['id_aset']);
        }
        if (! empty($this->filters['group_aset']) && $this->filters['group_aset'] !== 'ALL') {
            $query->where('master_asets.group_aset', $this->filters['group_aset']);
        }
        if (! empty($this->filters['area']) && $this->filters['area'] !== 'ALL') {
            $query->where('master_asets.area', $this->filters['area']);
        }
        if (! empty($this->filters['group_internal_order']) && $this->filters['group_internal_order'] !== 'ALL') {
            $query->where('master_asets.group_internal_order', $this->filters['group_internal_order']);
        }
        if (! empty($this->filters['internal_order']) && $this->filters['internal_order'] !== 'ALL') {
            $query->where('master_asets.internal_order', $this->filters['internal_order']);
        }
        if (! empty($this->filters['group_desc']) && $this->filters['group_desc'] !== 'ALL') {
            $query->where('master_asets.group_desc', $this->filters['group_desc']);
        }
        if (! empty($this->filters['pt']) && $this->filters['pt'] !== 'ALL') {
            $query->where('master_asets.pt', $this->filters['pt']);
        }

        return $query->get()->map(function ($row) {
            $isKendaraan = \App\Models\MasterAset::isKendaraan($row->group_internal_order);
            $row->is_kendaraan = $isKendaraan;
            $row->uom = $isKendaraan ? 'KM/L' : 'L/JAM';

            if ($isKendaraan) {
                $row->total_kerja = (float) ($row->fuel_km_hm ?? 0);
            } else {
                $row->total_kerja = (float) ($row->total_kerja ?? 0);
            }

            $row->total_operasi = (float) ($row->total_operasi ?? 0);
            $row->total_idle = (float) ($row->total_idle ?? 0);
            $row->total_solar = (float) ($row->total_solar ?? 0);
            $row->avg_idle = $row->total_operasi > 0 ? ($row->total_idle / $row->total_operasi) * 100 : 0;
            
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

            return [
                $yearNum,
                $monthNum,
                $item->id_aset ?? ''
            ];
        })->values();
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Tahun',
            'Unit Code',
            'Group Aset',
            'Area',
            'PT',
            'Internal Order',
            'Group IO',
            'Group Desc',
            'Total Jam Kerja (Jam)',
            'Total Waktu Operasi (Jam)',
            'Total Waktu Idle (Jam)',
            'Rata-rata Idle (%)',
            'Total Solar (L)',
            'Efisiensi Solar (L/Jam)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->bulan,
            $row->tahun,
            $row->id_aset,
            $row->group_aset,
            $row->area,
            $row->pt,
            $row->internal_order,
            $row->group_internal_order,
            $row->group_desc,
            $row->total_kerja,
            $row->total_operasi,
            $row->total_idle,
            round($row->avg_idle, 2) . '%',
            $row->total_solar,
            is_null($row->efficiency) ? 'N/A' : round($row->efficiency, 2)
        ];
    }
}
