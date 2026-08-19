<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelTransaction extends Model
{
    use HasFactory;

    protected $table = 'fuel_transactions';

    protected $fillable = [
        'import_log_id',
        'tahun',
        'bulan',
        'company_code',
        'unit_code',
        'internal_order',
        'material_number',
        'material_description',
        'total_quantity',
        'uom',
        'group_aset',
        'area',
        'code_company',
        'code_unit',
        'km_hm',
        'solar',
    ];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class, 'import_log_id');
    }
}
