<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #2563eb; padding-bottom: 10px; position: relative; }
        .logo { position: absolute; left: 0; top: 0; height: 50px; }
        h1 { margin: 0; font-size: 18px; color: #1e40af; text-transform: uppercase; }
        h3 { margin: 5px 0 0 0; font-size: 12px; font-weight: normal; color: #64748b; }
        .filters { margin-bottom: 15px; font-size: 9px; }
        .filters strong { display: inline-block; width: 100px; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f1f5f9; color: #334155; font-weight: bold; text-align: left; padding: 6px; border: 1px solid #cbd5e1; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 6px; border: 1px solid #cbd5e1; color: #334155; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; height: 30px; text-align: right; font-size: 8px; color: #94a3b8; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/logo.png');
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }
    @endphp

    <div class="header">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" class="logo" alt="Logo">
        @endif
        <h1>{{ $title }}</h1>
        <h3>
            Periode: 
            @php
                $bDari = $filters['bulan_dari'] ?? $filters['bulan'] ?? 'ALL';
                $bSampai = $filters['bulan_sampai'] ?? $filters['bulan'] ?? 'ALL';
                $rangeText = ($bDari === 'ALL' && $bSampai === 'ALL') ? 'Semua Bulan' : (($bDari === $bSampai) ? $bDari : ($bDari . ' - ' . $bSampai));
            @endphp
            {{ $rangeText }} {{ $filters['tahun'] ?? 'ALL' }}
        </h3>
    </div>

    <div class="filters">
        <table style="width: 50%; border: none; margin-top: 0;">
            <tr><td style="border: none; padding: 2px;"><strong>PT:</strong> {{ $filters['pt'] ?? 'Semua' }}</td></tr>
            <tr><td style="border: none; padding: 2px;"><strong>Area:</strong> {{ $filters['area'] ?? 'Semua' }}</td></tr>
            <tr><td style="border: none; padding: 2px;"><strong>Group Aset:</strong> {{ $filters['group_aset'] ?? 'Semua' }}</td></tr>
            <tr><td style="border: none; padding: 2px;"><strong>Unit / Asset:</strong> {{ $filters['id_aset'] ?? 'Semua' }}</td></tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tahun</th>
                <th>Bulan</th>
                <th>Unit Code</th>
                <th>Group Aset</th>
                <th>Area</th>
                <th>PT</th>
                <th>Internal Order</th>
                <th>Group IO</th>
                <th>Group Desc</th>
                <th class="text-right">Jam Kerja (Jam)</th>
                <th class="text-right">Solar (L)</th>
                <th class="text-right">Rasio Efisiensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->tahun ?? '-' }}</td>
                <td>{{ $row->bulan ?? '-' }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $row->id_aset }}</td>
                <td>{{ $row->group_aset }}</td>
                <td>{{ $row->area }}</td>
                <td>{{ $row->pt }}</td>
                <td>{{ $row->internal_order }}</td>
                <td>{{ $row->group_internal_order }}</td>
                <td>{{ $row->group_desc }}</td>
                <td class="text-right">{{ number_format($row->total_kerja, 1) }}</td>
                <td class="text-right">{{ number_format($row->total_solar, 0) }}</td>
                <td class="text-right" style="font-weight: bold;">
                    {{ is_null($row->efficiency) ? 'N/A' : number_format($row->efficiency, 2) . ' ' . ($row->uom ?? 'L/Jam') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center">Tidak ada data ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ now()->translatedFormat('d M Y H:i:s') }} | Halaman <span class="page-number"></span>
    </div>
</body>
</html>
