<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\app\Http\Controllers\HomeController.php';
$content = file_get_contents($file);

$search = <<<'EOD'
        $efficiencyData = MasterAset::query()
            ->select('master_asets.unit_code as id_aset', 'telemetry.total_kerja', 'fuel.total_solar')
            ->joinSub($telemetrySub, 'telemetry', 'master_asets.unit_code', '=', 'telemetry.id_aset')
            ->joinSub($fuelSub, 'fuel', 'master_asets.unit_code', '=', 'fuel.unit_code')
            ->get()
            ->map(function ($row) {
                $row->total_kerja = (float) ($row->total_kerja ?? 0);
                $row->total_solar = (float) ($row->total_solar ?? 0);
                $row->efficiency = $row->total_kerja > 0 ? ($row->total_solar / $row->total_kerja) : null;
                return $row;
            })
            ->filter(function($row) {
                return $row->total_kerja > 0 && $row->total_solar > 0;
            });

        // Top 5 Efficient (Lowest Ratio L/Jam)
        $topEfficient = $efficiencyData->sortBy('efficiency')->take(5)->values();
        
        // Bottom 5 Efficient (Highest Ratio L/Jam)
        $bottomEfficient = $efficiencyData->sortByDesc('efficiency')->take(5)->values();

        return view('home', compact('availableMonths', 'availableYears', 'bulan', 'tahun', 
            'totalAset', 'avgIdle', 'totalFuel', 'totalKerja',
            'topEfficient', 'bottomEfficient', 'bulan', 'tahun'
        ));
EOD;

$replace = <<<'EOD'
        $efficiencyData = MasterAset::query()
            ->select('master_asets.unit_code as id_aset', 'master_asets.group_internal_order', 'telemetry.total_kerja', 'fuel.total_solar')
            ->joinSub($telemetrySub, 'telemetry', 'master_asets.unit_code', '=', 'telemetry.id_aset')
            ->joinSub($fuelSub, 'fuel', 'master_asets.unit_code', '=', 'fuel.unit_code')
            ->get()
            ->map(function ($row) {
                $row->total_kerja = (float) ($row->total_kerja ?? 0);
                $row->total_solar = (float) ($row->total_solar ?? 0);
                
                $isKendaraan = in_array($row->group_internal_order, ['KRD', 'KRF', 'KRK', 'KRL', 'KRT', 'KRS']);
                $row->is_kendaraan = $isKendaraan;
                
                if ($isKendaraan) {
                    $row->efficiency = $row->total_solar > 0 ? ($row->total_kerja / $row->total_solar) : null;
                } else {
                    $row->efficiency = $row->total_kerja > 0 ? ($row->total_solar / $row->total_kerja) : null;
                }
                return $row;
            })
            ->filter(function($row) {
                return $row->total_kerja > 0 && $row->total_solar > 0 && !is_null($row->efficiency);
            });

        $alatBeratData = $efficiencyData->where('is_kendaraan', false);
        $kendaraanData = $efficiencyData->where('is_kendaraan', true);

        // Alat Berat: Lowest L/Jam is best
        $topAB = $alatBeratData->sortBy('efficiency')->take(5)->values();
        $bottomAB = $alatBeratData->sortByDesc('efficiency')->take(5)->values();

        // Kendaraan: Highest KM/L is best
        $topKen = $kendaraanData->sortByDesc('efficiency')->take(5)->values();
        $bottomKen = $kendaraanData->sortBy('efficiency')->take(5)->values();

        return view('home', compact('availableMonths', 'availableYears', 'bulan', 'tahun', 
            'totalAset', 'avgIdle', 'totalFuel', 'totalKerja',
            'topAB', 'bottomAB', 'topKen', 'bottomKen'
        ));
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "HomeController updated.";
