<?php

namespace App\Exports;

use App\Models\DataAlat;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkingHourMonthlyExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = DataAlat::query()
            ->leftJoin('master_asets', 'data_alat.id_aset', '=', 'master_asets.unit_code');

        $bulan_dari = $this->filters['bulan_dari'] ?? 'ALL';
        $bulan_sampai = $this->filters['bulan_sampai'] ?? 'ALL';
        $tahun = $this->filters['tahun'] ?? 'ALL';

        $monthMap = [
            '1' => 'Jan', '01' => 'Jan', 'JAN' => 'Jan', 'JANUARI' => 'Jan', 'JANUARY' => 'Jan',
            '2' => 'Feb', '02' => 'Feb', 'FEB' => 'Feb', 'FEBRUARI' => 'Feb', 'FEBRUARY' => 'Feb',
            '3' => 'Mar', '03' => 'Mar', 'MAR' => 'Mar', 'MARET' => 'Mar', 'MARCH' => 'Mar',
            '4' => 'Apr', '04' => 'Apr', 'APR' => 'Apr', 'APRIL' => 'Apr',
            '5' => 'May', '05' => 'May', 'MAY' => 'May', 'MEI' => 'May',
            '6' => 'Jun', '06' => 'Jun', 'JUN' => 'Jun', 'JUNI' => 'Jun', 'JUNE' => 'Jun',
            '7' => 'Jul', '07' => 'Jul', 'JUL' => 'Jul', 'JULI' => 'Jul', 'JULY' => 'Jul',
            '8' => 'Aug', '08' => 'Aug', 'AUG' => 'Aug', 'AGUSTUS' => 'Aug', 'AUGUST' => 'Aug',
            '9' => 'Sep', '09' => 'Sep', 'SEP' => 'Sep', 'SEPTEMBER' => 'Sep',
            '10' => 'Oct', 'OCT' => 'Oct', 'OKTOBER' => 'Oct', 'OCTOBER' => 'Oct',
            '11' => 'Nov', 'NOV' => 'Nov', 'NOVEMBER' => 'Nov',
            '12' => 'Dec', 'DEC' => 'Dec', 'DESEMBER' => 'Dec', 'DECEMBER' => 'Dec',
        ];
        $allMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        if ($bulan_dari !== 'ALL' || $bulan_sampai !== 'ALL') {
            $dariKey = $monthMap[strtoupper(trim((string)$bulan_dari))] ?? 'Jan';
            $sampaiKey = $monthMap[strtoupper(trim((string)$bulan_sampai))] ?? 'Dec';
            $startIdx = array_search($dariKey, $allMonths);
            $endIdx = array_search($sampaiKey, $allMonths);
            if ($startIdx !== false && $endIdx !== false) {
                if ($startIdx <= $endIdx) {
                    $bulanList = array_slice($allMonths, $startIdx, $endIdx - $startIdx + 1);
                } else {
                    $bulanList = array_merge(array_slice($allMonths, $startIdx), array_slice($allMonths, 0, $endIdx + 1));
                }
                $query->whereIn('data_alat.bulan', $bulanList);
            }
        }

        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('data_alat.tahun', $tahun);
        }

        if (! empty($this->filters['id_aset']) && $this->filters['id_aset'] !== 'ALL') {
            $id_aset = $this->filters['id_aset'];
            $query->where(function ($q) use ($id_aset) {
                $q->where('master_asets.unit_code', $id_aset)
                  ->orWhere('data_alat.id_aset', $id_aset);
            });
        }
        if (! empty($this->filters['group_aset']) && $this->filters['group_aset'] !== 'ALL') {
            $group_aset = $this->filters['group_aset'];
            $query->where(function ($q) use ($group_aset) {
                $q->where('master_asets.group_aset', $group_aset)
                  ->orWhere('data_alat.group_aset', $group_aset);
            });
        }
        if (! empty($this->filters['area']) && $this->filters['area'] !== 'ALL') {
            $area = $this->filters['area'];
            $query->where(function ($q) use ($area) {
                $q->where('master_asets.area', $area)
                  ->orWhere('data_alat.area', $area);
            });
        }
        if (! empty($this->filters['group_internal_order']) && $this->filters['group_internal_order'] !== 'ALL') {
            $gio = $this->filters['group_internal_order'];
            $query->where(function ($q) use ($gio) {
                $q->where('master_asets.group_internal_order', $gio)
                  ->orWhere('data_alat.group_internal_order', $gio);
            });
        }
        if (! empty($this->filters['internal_order']) && $this->filters['internal_order'] !== 'ALL') {
            $io = $this->filters['internal_order'];
            $query->where(function ($q) use ($io) {
                $q->where('master_asets.internal_order', $io)
                  ->orWhere('data_alat.internal_order', $io);
            });
        }
        if (! empty($this->filters['group_desc']) && $this->filters['group_desc'] !== 'ALL') {
            $gd = $this->filters['group_desc'];
            $query->where(function ($q) use ($gd) {
                $q->where('master_asets.group_desc', $gd)
                  ->orWhere('data_alat.group_desc', $gd);
            });
        }
        if (! empty($this->filters['pt']) && $this->filters['pt'] !== 'ALL') {
            $pt = $this->filters['pt'];
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

        return $query->select(
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
    }

    public function headings(): array
    {
        return [
            'Group Aset',
            'Area',
            'PT',
            'Unit Code / ID Aset',
            'Tahun',
            'Bulan',
            'Internal Order',
            'IO Group',
            'Group Desc',
            'Model',
            'Jam Kerja (Jam)',
            'Jam Operasi (Jam)',
            'Jam Idle (Jam)',
            '% Idle',
            'Status Idle',
        ];
    }

    public function map($row): array
    {
        return [
            $row->group_aset,
            $row->area,
            $row->pt,
            $row->id_aset,
            $row->tahun,
            $row->bulan,
            $row->internal_order,
            $row->group_internal_order,
            $row->group_desc,
            $row->model,
            $row->total_kerja,
            $row->total_operasi,
            $row->total_idle,
            $row->avg_idle . '%',
            ($row->avg_idle <= 10) ? 'Aman' : 'Warning',
        ];
    }
}
