<?php

namespace App\Exports;

use App\Models\FuelTransaction;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FuelExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = FuelTransaction::query()
            ->leftJoin('master_asets', 'fuel_transactions.unit_code', '=', 'master_asets.unit_code');

        if (! empty($this->filters['tahun']) && $this->filters['tahun'] !== 'ALL') {
            $query->where('fuel_transactions.tahun', $this->filters['tahun']);
        }

        $dari = $this->filters['bulan_dari'] ?? $this->filters['bulan'] ?? null;
        $sampai = $this->filters['bulan_sampai'] ?? $this->filters['bulan'] ?? null;
        $hasBulan = ($dari && $dari !== 'ALL') || ($sampai && $sampai !== 'ALL');
        if ($hasBulan) {
            $all = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $fromIdx = ($dari && $dari !== 'ALL') ? array_search($dari, $all) : 0;
            $toIdx   = ($sampai && $sampai !== 'ALL') ? array_search($sampai, $all) : 11;
            if ($fromIdx === false) $fromIdx = 0;
            if ($toIdx   === false) $toIdx   = 11;
            if ($fromIdx > $toIdx) [$fromIdx, $toIdx] = [$toIdx, $fromIdx];
            $months = array_slice($all, $fromIdx, $toIdx - $fromIdx + 1);
            $bulanList = array_unique(array_merge($months, array_map(fn($m) => substr($m, 0, 3), $months)));
            $query->whereIn('fuel_transactions.bulan', $bulanList);
        }
        if (! empty($this->filters['group_aset']) && $this->filters['group_aset'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.group_aset', $this->filters['group_aset'])
                  ->orWhere('fuel_transactions.group_aset', $this->filters['group_aset']);
            });
        }
        if (! empty($this->filters['area']) && $this->filters['area'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.area', $this->filters['area'])
                  ->orWhere('fuel_transactions.area', $this->filters['area']);
            });
        }
        if (! empty($this->filters['id_aset']) && $this->filters['id_aset'] !== 'ALL') {
            $query->where('fuel_transactions.unit_code', $this->filters['id_aset']);
        }
        if (! empty($this->filters['group_desc']) && $this->filters['group_desc'] !== 'ALL') {
            $query->where('master_asets.group_desc', $this->filters['group_desc']);
        }
        if (! empty($this->filters['group_internal_order']) && $this->filters['group_internal_order'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.group_internal_order', $this->filters['group_internal_order'])
                  ->orWhereRaw('SUBSTR(fuel_transactions.internal_order, 5, 3) = ?', [$this->filters['group_internal_order']]);
            });
        }
        if (! empty($this->filters['internal_order']) && $this->filters['internal_order'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.internal_order', $this->filters['internal_order'])
                  ->orWhere('fuel_transactions.internal_order', $this->filters['internal_order']);
            });
        }
        if (! empty($this->filters['pt']) && $this->filters['pt'] !== 'ALL') {
            $query->where('master_asets.pt', $this->filters['pt']);
        }

        $query->select(
            'fuel_transactions.*',
            'master_asets.pt as pt',
            'master_asets.group_desc as group_desc',
            \Illuminate\Support\Facades\DB::raw('COALESCE(fuel_transactions.internal_order, master_asets.internal_order) as internal_order'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(fuel_transactions.group_aset, master_asets.group_aset) as group_aset'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(fuel_transactions.area, master_asets.area) as area'),
            \Illuminate\Support\Facades\DB::raw('COALESCE(SUBSTR(fuel_transactions.internal_order, 5, 3), master_asets.group_internal_order) as group_internal_order')
        );

        return $query->orderBy('fuel_transactions.created_at', 'asc');
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Bulan',
            'Unit Code',
            'Group Aset',
            'Area',
            'PT',
            'Group Desc',
            'Internal Order',
            'Group IO',
            'Total Kerja (KM/HM)',
            'Total Quantity (L)',
            'Solar (L)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->tahun,
            $row->bulan,
            $row->unit_code,
            $row->group_aset,
            $row->area,
            $row->pt,
            $row->group_desc,
            $row->internal_order,
            $row->group_internal_order,
            $row->km_hm,
            $row->total_quantity,
            $row->solar,
        ];
    }
}
