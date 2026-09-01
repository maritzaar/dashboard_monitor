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
            'KRD', 'KRF', 'KRK', 'KRL', 'KRT', 'KRS'
        ];
        return in_array($ioGroup, $kendaraanGroups);
    }

    public static function getIoGroupDescMap(): array
    {
        return [
            'MSP' => 'POWER SUPPLY',
            'ABA' => 'DUMP CRAWLER',
            'ABC' => 'COMPACTOR ROLLER',
            'ABE' => 'EXCAVATOR',
            'ABG' => 'ROAD GRADER',
            'ABT' => 'TRACTOR',
            'KRD' => 'DUMP TRUCK',
            'KRF' => 'DUMP TRUCK FUSO',
            'KRK' => 'TRUCK',
            'KRL' => 'KENDARAAN RINGAN',
            'KRT' => 'TRUCK TANGKI',
            'MSW' => 'WATER SUPPLY',
            'WSW' => 'BENGKEL',
            'ABL' => 'LOADER',
            'ABD' => 'BULDOZER',
            'SPG' => 'SPESIAL PROJECT',
            'SWA' => 'SWAKELOLA',
            'RMH' => 'PERUMAHAN',
            'OBT' => 'PENGOBATAN',
            'CBR' => 'CATUBERAS',
            'KRS' => 'TRUCK SEKOLAH',
            'PJC' => 'PENAMPUNG GI',
            'NON IO' => 'NN',
            'KRC' => 'SCISSOR LIFT',
        ];
    }
}
