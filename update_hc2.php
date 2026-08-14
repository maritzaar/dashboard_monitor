<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\app\Http\Controllers\HomeController.php';
$content = file_get_contents($file);

$search = <<<'EOD'
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

$replace = <<<'EOD'
        // Alat Berat: Lowest L/Jam is best
        $topAB = $alatBeratData->sortBy('efficiency')->take(5)->values();
        $bottomAB = $alatBeratData->sortByDesc('efficiency')->take(5)->values();

        // Kendaraan: Highest KM/L is best
        $topKen = $kendaraanData->sortByDesc('efficiency')->take(5)->values();
        $bottomKen = $kendaraanData->sortBy('efficiency')->take(5)->values();
        
        $avgEffAB = $alatBeratData->sum('total_kerja') > 0 ? ($alatBeratData->sum('total_solar') / $alatBeratData->sum('total_kerja')) : 0;
        $avgEffKen = $kendaraanData->sum('total_solar') > 0 ? ($kendaraanData->sum('total_kerja') / $kendaraanData->sum('total_solar')) : 0;

        return view('home', compact('availableMonths', 'availableYears', 'bulan', 'tahun', 
            'totalAset', 'avgIdle', 'totalFuel', 'totalKerja',
            'topAB', 'bottomAB', 'topKen', 'bottomKen', 'avgEffAB', 'avgEffKen'
        ));
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "HomeController updated with avg eff.";
