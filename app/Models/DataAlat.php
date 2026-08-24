<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataAlat extends Model
{
    protected $table = 'data_alat';

    protected $fillable = [
        'tahun', 'bulan', 'tanggal', 'keterangan', 'id_aset', 'nomor_seri',
        'buatan', 'model', 'group_aset', 'area', 'pt', 'internal_order',
        'group_internal_order', 'group_desc', 'meteran_jam', 'waktu_terakhir',
        'laporan_pemanfaatan', 'zona_waktu', 'nama_zona', 'waktu_operasi',
        'waktu_idle', 'waktu_kerja', 'persen_idle', 'total_bahan_bakar',
        'laju_bakar', 'daya_dihasilkan', 'beban_harian', 'daya_per_unit',
        'sumber_data', 'import_log_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_terakhir' => 'datetime',
        'laporan_pemanfaatan' => 'datetime',
    ];
}

