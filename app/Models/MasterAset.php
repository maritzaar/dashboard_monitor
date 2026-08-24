<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAset extends Model
{
    protected $table = 'master_asets';

    protected $fillable = [
        'unit_code',
        'nomor_seri',
        'model',
        'group_aset',
        'area',
        'internal_order',
        'group_internal_order',
        'group_desc',
        'pt',
        'company_code',
    ];

    public static function isKendaraan($ioGroup)
    {
        $kendaraanGroups = [
            'KRD', 'KRF', 'KRK', 'KRL', 'KRT', 'WSW', 
            'SPG', 'SWA', 'RMH', 'OBT', 'CBR', 'KRS', 'PJC', 'NON IO', 'KRC'
        ];
        return in_array($ioGroup, $kendaraanGroups);
    }
}
