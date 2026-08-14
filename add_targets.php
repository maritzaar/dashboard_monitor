<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\app\Http\Controllers\MonitoringController.php';
$content = file_get_contents($file);

$search = <<<'EOD'
            $row->uom = $isKendaraan ? 'KM/L' : 'L/JAM';
EOD;

$replace = <<<'EOD'
            $row->uom = $isKendaraan ? 'KM/L' : 'L/JAM';
            
            // Hardcode targets based on user's image
            $targets = [
                'ABA' => 4, 'ABC' => 10, 'ABE' => 16, 'ABG' => 10, 'ABT' => 4,
                'KRD' => 4, 'KRF' => 4, 'KRK' => 4.5, 'KRL' => 8, 'KRT' => 4.5,
                'ABL' => 5.3, 'ABD' => 16
            ];
            $row->target_ratio = $targets[$row->group_internal_order] ?? null;
EOD;
$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Added targets to MonitoringController.";
