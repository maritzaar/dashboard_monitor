<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\app\Http\Controllers\MonitoringController.php';
$content = file_get_contents($file);

$search = <<<'EOD'
        $reports = $query->get()->map(function ($row) {
            $row->total_kerja = (float) ($row->total_kerja ?? 0);
            $row->total_operasi = (float) ($row->total_operasi ?? 0);
            $row->total_idle = (float) ($row->total_idle ?? 0);
            $row->total_solar = (float) ($row->total_solar ?? 0);
            $row->avg_idle = $row->total_operasi > 0 ? ($row->total_idle / $row->total_operasi) * 100 : 0;
            $row->efficiency = $row->total_kerja > 0 ? ($row->total_solar / $row->total_kerja) : null;
            return $row;
        })->sortByDesc(function ($item) {
            return $item->efficiency ?? -1;
        })->values();

        $stats = (object) [
            'total_aset' => $reports->count(),
            'total_solar' => $reports->sum('total_solar'),
            'total_kerja' => $reports->sum('total_kerja'),
            'avg_efficiency' => $reports->sum('total_kerja') > 0 ? ($reports->sum('total_solar') / $reports->sum('total_kerja')) : 0,
        ];

        $chartData = $reports->filter(function ($item) {
            return !is_null($item->efficiency) && $item->total_kerja > 0;
        })->map(function ($item) {
            return (object) [
                'id_aset' => $item->id_aset,
                'efficiency' => round($item->efficiency, 2),
            ];
        })->values();
EOD;

$replace = <<<'EOD'
        $reports = $query->get()->map(function ($row) {
            $row->total_kerja = (float) ($row->total_kerja ?? 0);
            $row->total_operasi = (float) ($row->total_operasi ?? 0);
            $row->total_idle = (float) ($row->total_idle ?? 0);
            $row->total_solar = (float) ($row->total_solar ?? 0);
            $row->avg_idle = $row->total_operasi > 0 ? ($row->total_idle / $row->total_operasi) * 100 : 0;
            
            $isKendaraan = in_array($row->group_internal_order, ['KRD', 'KRF', 'KRK', 'KRL', 'KRT', 'KRS']);
            $row->is_kendaraan = $isKendaraan;
            $row->uom = $isKendaraan ? 'KM/L' : 'L/JAM';
            
            if ($isKendaraan) {
                $row->efficiency = $row->total_solar > 0 ? ($row->total_kerja / $row->total_solar) : null;
            } else {
                $row->efficiency = $row->total_kerja > 0 ? ($row->total_solar / $row->total_kerja) : null;
            }
            
            return $row;
        })->sortBy(function ($item) {
            // Sort Alat Berat first, then Kendaraan. 
            // Alat Berat (L/JAM) -> lower is better. We sort them by efficiency ASC.
            // Kendaraan (KM/L) -> higher is better. We sort them by efficiency DESC.
            if (!$item->is_kendaraan) {
                return [0, $item->efficiency ?? 999999]; // 0 ensures Alat Berat is top. ASC efficiency.
            } else {
                return [1, -($item->efficiency ?? -999999)]; // 1 ensures Kendaraan is bottom. DESC efficiency.
            }
        })->values();

        $abReports = $reports->where('is_kendaraan', false);
        $kenReports = $reports->where('is_kendaraan', true);

        $stats = (object) [
            'total_aset' => $reports->count(),
            'total_solar' => $reports->sum('total_solar'),
            'total_kerja' => $reports->sum('total_kerja'),
            'avg_efficiency_ab' => $abReports->sum('total_kerja') > 0 ? ($abReports->sum('total_solar') / $abReports->sum('total_kerja')) : 0,
            'avg_efficiency_ken' => $kenReports->sum('total_solar') > 0 ? ($kenReports->sum('total_kerja') / $kenReports->sum('total_solar')) : 0,
        ];

        $chartData = $reports->filter(function ($item) {
            return !is_null($item->efficiency) && $item->total_kerja > 0;
        })->map(function ($item) {
            return (object) [
                'id_aset' => $item->id_aset,
                'efficiency' => round($item->efficiency, 2),
                'uom' => $item->uom
            ];
        })->values();
EOD;

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "MonitoringController updated.";
