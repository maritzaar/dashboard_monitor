<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MasterAset;
use App\Models\DataAlat;

$missingPT = DataAlat::whereNull('pt')->orWhere('pt', '')->get();
$count = 0;

foreach ($missingPT as $data) {
    // Look up the master aset either by unit_code matching id_aset, or by serial
    $master = MasterAset::where('unit_code', $data->id_aset)->first();
    
    if ($master && !empty($master->company_code)) {
        $data->pt = $master->company_code;
        $data->save();
        $count++;
    }
}

echo "Fixed PT for $count records in DataAlat!";
