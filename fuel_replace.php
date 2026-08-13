<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\app\Http\Controllers\MonitoringController.php';
$content = file_get_contents($file);

// Find the block where $query is declared in fuel()
$query_pattern = '/(\$query = FuelTransaction::query\(\)\s*->leftJoin\(\'master_asets\', \'fuel_transactions\.unit_code\', \'=\', \'master_asets\.unit_code\'\);)/s';

$query_replacement = <<< 'EOT'
        $workingHoursSub = DB::table('data_alat')
            ->select(
                'id_aset',
                DB::raw('CAST(tahun AS TEXT) as tahun'),
                DB::raw('CAST(bulan AS TEXT) as bulan'),
                DB::raw('SUM(waktu_operasi) as total_waktu_operasi')
            )
            ->groupBy('id_aset', 'tahun', 'bulan');

        $query = FuelTransaction::query()
            ->leftJoin('master_asets', 'fuel_transactions.unit_code', '=', 'master_asets.unit_code')
            ->leftJoinSub($workingHoursSub, 'wh', function ($join) {
                $join->on('fuel_transactions.unit_code', '=', 'wh.id_aset')
                     ->on('fuel_transactions.bulan', '=', 'wh.bulan')
                     ->on('fuel_transactions.tahun', '=', 'wh.tahun');
            });
EOT;

$content = preg_replace($query_pattern, $query_replacement, $content);

// Find the select block
$select_pattern = '/(\$reports = \$query->select\(.*?)(\)\s*->get\(\))/s';
$select_replacement = <<< 'EOT'
$1,
            'wh.total_waktu_operasi as total_kerja'
        )
            ->get()
            ->map(function ($item) {
                $item->rasio = $item->total_kerja > 0 ? $item->actual_fuel / $item->total_kerja : 0;
                return $item;
            })
EOT;

$content = preg_replace($select_pattern, $select_replacement, $content);

file_put_contents($file, $content);
echo "Replaced successfully.";
