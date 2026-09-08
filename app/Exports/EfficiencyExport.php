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
        $tahun = $this->filters['tahun'] ?? null;

        $query = \App\Models\FuelBudget::query()
            ->leftJoin('master_asets', 'fuel_budgets.unit_code', '=', 'master_asets.unit_code');

        $hasBulan = ($bulan_dari !== 'ALL') || ($bulan_sampai !== 'ALL');
        if ($hasBulan) {
            $all = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            $fromIdx = ($bulan_dari && $bulan_dari !== 'ALL') ? array_search($bulan_dari, $all) : 0;
            $toIdx   = ($bulan_sampai && $bulan_sampai !== 'ALL') ? array_search($bulan_sampai, $all) : 11;
            if ($fromIdx === false) $fromIdx = 0;
            if ($toIdx   === false) $toIdx   = 11;
            if ($fromIdx > $toIdx) [$fromIdx, $toIdx] = [$toIdx, $fromIdx];
            $months = array_slice($all, $fromIdx, $toIdx - $fromIdx + 1);
            $shortBulanList = array_map(fn($m) => ucfirst(strtolower(substr(trim($m), 0, 3))), $months);
            $bulanList = array_unique(array_merge($months, $shortBulanList));
            $query->whereIn('fuel_budgets.bulan', $bulanList);
        }

        if (! empty($tahun) && $tahun !== 'ALL') {
            $query->where('fuel_budgets.tahun', $tahun);
        }

        if (! empty($this->filters['id_aset']) && $this->filters['id_aset'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(fuel_budgets.unit_code, '#N/A'), master_asets.unit_code)"), $this->filters['id_aset']);
        }
        if (! empty($this->filters['group_aset']) && $this->filters['group_aset'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(fuel_budgets.group_aset, '#N/A'), master_asets.group_aset)"), $this->filters['group_aset']);
        }
        if (! empty($this->filters['area']) && $this->filters['area'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(fuel_budgets.area, '#N/A'), master_asets.area)"), $this->filters['area']);
        }
        if (! empty($this->filters['group_desc']) && $this->filters['group_desc'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(fuel_budgets.group_internal_order, '#N/A'), master_asets.group_desc)"), $this->filters['group_desc']);
        }
        if (! empty($this->filters['group_internal_order']) && $this->filters['group_internal_order'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(SUBSTRING(fuel_budgets.internal_order, 5, 3), ''), master_asets.group_internal_order)"), $this->filters['group_internal_order']);
        }
        if (! empty($this->filters['internal_order']) && $this->filters['internal_order'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(fuel_budgets.internal_order, '#N/A'), master_asets.internal_order)"), $this->filters['internal_order']);
        }
        if (! empty($this->filters['pt']) && $this->filters['pt'] !== 'ALL') {
            $query->where(DB::raw("COALESCE(NULLIF(fuel_budgets.pt, '#N/A'), master_asets.pt)"), $this->filters['pt']);
        }

        return $query->select(
            'fuel_budgets.id as id',
            DB::raw("CASE WHEN fuel_budgets.unit_code = '#N/A' OR fuel_budgets.unit_code IS NULL OR fuel_budgets.unit_code = '' THEN COALESCE(master_asets.unit_code, '-') ELSE fuel_budgets.unit_code END as id_aset"),
            DB::raw("CASE WHEN fuel_budgets.group_aset = '#N/A' OR fuel_budgets.group_aset IS NULL OR fuel_budgets.group_aset = '' THEN COALESCE(master_asets.group_aset, '-') ELSE fuel_budgets.group_aset END as group_aset"),
            DB::raw("CASE WHEN fuel_budgets.area = '#N/A' OR fuel_budgets.area IS NULL OR fuel_budgets.area = '' THEN COALESCE(master_asets.area, '-') ELSE fuel_budgets.area END as area"),
            DB::raw("CASE WHEN fuel_budgets.pt = '#N/A' OR fuel_budgets.pt IS NULL OR fuel_budgets.pt = '' THEN COALESCE(master_asets.pt, '-') ELSE fuel_budgets.pt END as pt"),
            'fuel_budgets.internal_order as internal_order',
            DB::raw("CASE WHEN fuel_budgets.group_internal_order = '#N/A' OR fuel_budgets.group_internal_order IS NULL OR fuel_budgets.group_internal_order = '' THEN COALESCE(master_asets.group_desc, '-') ELSE fuel_budgets.group_internal_order END as group_desc"),
            DB::raw("COALESCE(NULLIF(SUBSTRING(fuel_budgets.internal_order, 5, 3), ''), NULLIF(NULLIF(master_asets.group_internal_order, '#N/A'), ''), '-') as group_internal_order"),
            'fuel_budgets.km_hm',
            'fuel_budgets.satuan',
            'fuel_budgets.owner',
            'fuel_budgets.type',
            'fuel_budgets.solar_actual as actual_fuel',
            'fuel_budgets.solar_budget as solar_budget',
            'fuel_budgets.output_actual as total_kerja',
            'fuel_budgets.output_budget as output_budget',
            'fuel_budgets.bulan',
            'fuel_budgets.tahun'
        )->get()->map(function ($item) {
            $isKendaraan = \App\Models\MasterAset::isKendaraan($item->group_internal_order);
            $kmHmType = strtoupper(trim((string)($item->km_hm ?? '')));
            if (empty($kmHmType)) {
                $kmHmType = $isKendaraan ? 'KM' : 'HM';
            }
            $item->km_hm_type = $kmHmType;

            if ($kmHmType === 'KM') {
                $item->rasio_budget = ($item->solar_budget > 0) ? ($item->output_budget / $item->solar_budget) : 0;
            } else {
                $item->rasio_budget = ($item->output_budget > 0) ? ($item->solar_budget / $item->output_budget) : 0;
            }

            $item->efisiensi = (float) $item->actual_fuel - ((float) $item->rasio_budget * (float) $item->total_kerja);
            return $item;
        })->sortBy(function ($item) {
            $monthOrder = [
                'january' => 1, 'jan' => 1,
                'february' => 2, 'feb' => 2,
                'march' => 3, 'mar' => 3,
                'april' => 4, 'apr' => 4,
                'may' => 5, 'may' => 5,
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
            'Grup',
            'Area',
            'PT',
            'Unit',
            'Bulan',
            'Tahun',
            'Internal Order',
            'Group IO',
            'KM/HM',
            'Satuan',
            'Output Budget',
            'Output Actual',
            'Solar Budget (L)',
            'Solar Actual (L)',
            'Rasio Budget',
            'Efisiensi (L)',
            'Status'
        ];
    }

    public function map($row): array
    {
        $status = 'Sesuai Budget';
        if ($row->efisiensi < 0) {
            $status = 'Efisien (Hemat)';
        } elseif ($row->efisiensi > 0) {
            $status = 'Boros (Over)';
        }

        return [
            $row->group_aset ?? '-',
            $row->area ?? '-',
            $row->pt ?? '-',
            $row->id_aset ?? '-',
            $row->bulan ?? '-',
            $row->tahun ?? '-',
            $row->internal_order ?? '-',
            $row->group_desc ?? '-',
            $row->km_hm_type ?? '-',
            $row->satuan ?? '-',
            round($row->output_budget, 1),
            round($row->total_kerja, 1),
            round($row->solar_budget, 1),
            round($row->actual_fuel, 1),
            round($row->rasio_budget, 1),
            round($row->efisiensi, 1),
            $status
        ];
    }
}
