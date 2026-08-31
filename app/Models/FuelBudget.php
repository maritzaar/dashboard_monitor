<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelBudget extends Model
{
    protected $table = 'fuel_budgets';

    protected $fillable = [
        'import_log_id',
        'tahun',
        'bulan',
        'group_aset',
        'area',
        'pt',
        'unit_code',
        'satuan',
        'owner',
        'type',
        'internal_order',
        'group_internal_order',
        'output_budget',
        'output_actual',
        'solar_budget',
        'solar_actual',
    ];

    public function importLog()
    {
        return $this->belongsTo(ImportLog::class);
    }
}
