<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MasterAset;
use App\Models\DataAlat;

// Fix bad values we added manually
$updates = [
    'E017-BB2' => 'ABE',
    'E014-MB1' => 'MSP',
    'E001-TS1' => 'ABG',
    'E018-TH1' => 'ABE'
];

foreach ($updates as $bad => $good) {
    MasterAset::where('group_internal_order', $bad)->update(['group_internal_order' => $good]);
    DataAlat::where('group_internal_order', $bad)->update(['group_internal_order' => $good]);
    // Also fix group_desc which we wrongly set to ABE, MSP, etc. It should be EXCAVATOR for BBE, MBE, TSE, THE (since they are 320, 307.5, etc.)
    DataAlat::where('group_desc', $good)->update(['group_desc' => 'EXCAVATOR']);
}

// Delete the specific formula row
MasterAset::where('group_internal_order', 'like', '=%')->delete();
DataAlat::where('group_internal_order', 'like', '=%')->delete();

echo "Done fixing DB!";
