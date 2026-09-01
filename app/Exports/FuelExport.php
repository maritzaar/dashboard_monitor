<?php

namespace App\Exports;

use App\Models\FuelBudget;
use Illuminate\Support\Facades\DB;
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
        $query = FuelBudget::query()
            ->leftJoin('master_asets', 'fuel_budgets.unit_code', '=', 'master_asets.unit_code');

        if (! empty($this->filters['tahun']) && $this->filters['tahun'] !== 'ALL') {
            $query->where('fuel_budgets.tahun', $this->filters['tahun']);
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
            $shortBulanList = array_map(function($m) { return ucfirst(strtolower(substr(trim($m), 0, 3))); }, $months);
            $bulanList = array_unique(array_merge($months, $shortBulanList));
            $query->whereIn('fuel_budgets.bulan', $bulanList);
        }
        if (! empty($this->filters['group_aset']) && $this->filters['group_aset'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.group_aset', $this->filters['group_aset'])
                  ->orWhere('fuel_budgets.group_aset', $this->filters['group_aset']);
            });
        }
        if (! empty($this->filters['area']) && $this->filters['area'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.area', $this->filters['area'])
                  ->orWhere('fuel_budgets.area', $this->filters['area']);
            });
        }
        if (! empty($this->filters['id_aset']) && $this->filters['id_aset'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('fuel_budgets.unit_code', $this->filters['id_aset'])
                  ->orWhere('master_asets.unit_code', $this->filters['id_aset']);
            });
        }
        if (! empty($this->filters['group_desc']) && $this->filters['group_desc'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('fuel_budgets.group_internal_order', $this->filters['group_desc'])
                  ->orWhere('master_asets.group_desc', $this->filters['group_desc']);
            });
        }
        if (! empty($this->filters['group_internal_order']) && $this->filters['group_internal_order'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.group_internal_order', $this->filters['group_internal_order'])
                  ->orWhere(DB::raw("SUBSTRING(fuel_budgets.internal_order, 5, 3)"), $this->filters['group_internal_order']);
            });
        }
        if (! empty($this->filters['internal_order']) && $this->filters['internal_order'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.internal_order', $this->filters['internal_order'])
                  ->orWhere('fuel_budgets.internal_order', $this->filters['internal_order']);
            });
        }
        if (! empty($this->filters['pt']) && $this->filters['pt'] !== 'ALL') {
            $query->where(function ($q) {
                $q->where('master_asets.pt', $this->filters['pt'])
                  ->orWhere('fuel_budgets.pt', $this->filters['pt']);
            });
        }

        $query->select(
            DB::raw("COALESCE(fuel_budgets.group_aset, master_asets.group_aset) as group_aset"),
            DB::raw("COALESCE(fuel_budgets.area, master_asets.area) as area"),
            DB::raw("COALESCE(fuel_budgets.pt, master_asets.pt) as pt"),
            'fuel_budgets.unit_code as unit_code',
            'fuel_budgets.bulan',
            'fuel_budgets.tahun',
            'fuel_budgets.internal_order as internal_order',
            DB::raw("COALESCE(NULLIF(SUBSTRING(fuel_budgets.internal_order, 5, 3), ''), master_asets.group_internal_order) as group_internal_order"),
            DB::raw("COALESCE(NULLIF(fuel_budgets.group_internal_order, '#N/A'), master_asets.group_desc) as group_desc"),
            'fuel_budgets.satuan as satuan',
            'fuel_budgets.output_budget as output_budget',
            'fuel_budgets.output_actual as output_actual',
            'fuel_budgets.solar_budget as solar_budget',
            'fuel_budgets.solar_actual as solar_actual'
        );

        return $query->orderBy('fuel_budgets.tahun', 'desc')->orderBy('fuel_budgets.bulan', 'desc');
    }

    public function headings(): array
    {
        return [
            'Group Aset',
            'Area',
            'PT',
            'Unit Code',
            'Bulan',
            'Tahun',
            'Internal Order',
            'IO Group',
            'Group Desc',
            'Satuan',
            'Output Budget',
            'Output Aktual',
            'Solar Budget (L)',
            'Solar Aktual (L)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->group_aset,
            $row->area,
            $row->pt,
            $row->unit_code,
            $row->bulan,
            $row->tahun,
            $row->internal_order,
            $row->group_internal_order,
            $row->group_desc,
            $row->satuan,
            $row->output_budget,
            $row->output_actual,
            $row->solar_budget,
            $row->solar_actual,
        ];
    }
}
