<?php

namespace App\Exports;

use App\Models\DataAlat;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DataAlatExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = DataAlat::query();

        if (! empty($this->filters['start_date']) && ! empty($this->filters['end_date'])) {
            $query_end_date = \Carbon\Carbon::parse($this->filters['end_date'])->endOfDay()->format('Y-m-d H:i:s');
            $query->whereBetween('tanggal', [$this->filters['start_date'], $query_end_date]);
        } else {
            if (! empty($this->filters['tahun']) && $this->filters['tahun'] !== 'ALL') {
                $query->where('tahun', $this->filters['tahun']);
            }
            if (! empty($this->filters['bulan']) && $this->filters['bulan'] !== 'ALL') {
                $query->where('bulan', $this->filters['bulan']);
            }
        }
        if (! empty($this->filters['group_aset']) && $this->filters['group_aset'] !== 'ALL') {
            $query->where('group_aset', $this->filters['group_aset']);
        }
        if (! empty($this->filters['area']) && $this->filters['area'] !== 'ALL') {
            $query->where('area', $this->filters['area']);
        }
        if (! empty($this->filters['id_aset']) && $this->filters['id_aset'] !== 'ALL') {
            $query->where('id_aset', $this->filters['id_aset']);
        }
        if (! empty($this->filters['group_desc']) && $this->filters['group_desc'] !== 'ALL') {
            $query->where('group_desc', $this->filters['group_desc']);
        }
        if (! empty($this->filters['group_internal_order']) && $this->filters['group_internal_order'] !== 'ALL') {
            $query->where('group_internal_order', $this->filters['group_internal_order']);
        }
        if (! empty($this->filters['internal_order']) && $this->filters['internal_order'] !== 'ALL') {
            $query->where('internal_order', $this->filters['internal_order']);
        }

        return $query->orderBy('tanggal', 'asc');
    }

    public function headings(): array
    {
        return [
            'Tahun',
            'Bulan',
            'Tanggal',
            'Keterangan',
            'ID Aset',
            'Nomor Seri',
            'Buatan',
            'Model',
            'Group Aset',
            'Area',
            'PT',
            'Internal Order',
            'Group Internal Order',
            'Group Desc',
            'Meteran Jam (HM)',
            'Waktu Terakhir Dilaporkan',
            'Laporan Pemanfaatan Terakhir',
            'Zona Waktu',
            'Nama Zona',
            'Waktu Operasi (Jam)',
            'Waktu Idle (Jam)',
            'Waktu Kerja (Jam)',
            '% Idle',
            'Total Bahan Bakar (L)',
            'Laju Bakar (L/Jam)',
            'Daya Dihasilkan (kWh)',
            'Beban Harian Rata-rata',
            'Daya per Unit Bahan Bakar (kWh/L)',
            'Sumber Data',
        ];
    }

    public function map($row): array
    {
        return [
            $row->tahun,
            $row->bulan,
            $row->tanggal ? $row->tanggal->format('Y-m-d') : '',
            $row->keterangan,
            $row->id_aset,
            $row->nomor_seri,
            $row->buatan,
            $row->model,
            $row->group_aset,
            $row->area,
            $row->pt,
            $row->internal_order,
            $row->group_internal_order,
            $row->group_desc,
            $row->meteran_jam,
            $row->waktu_terakhir ? $row->waktu_terakhir->format('Y-m-d H:i:s') : '',
            $row->laporan_pemanfaatan ? $row->laporan_pemanfaatan->format('Y-m-d H:i:s') : '',
            $row->zona_waktu,
            $row->nama_zona,
            $row->waktu_operasi,
            $row->waktu_idle,
            $row->waktu_kerja,
            $row->persen_idle,
            $row->total_bahan_bakar,
            $row->laju_bakar,
            $row->daya_dihasilkan,
            $row->beban_harian,
            $row->daya_per_unit,
            $row->sumber_data,
        ];
    }
}
