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
        <h1>{{ __($title) }}</h1>
        <h3>
            {{ __('Periode') }}: 
            @if(!empty($filters['start_date']) && !empty($filters['end_date']))
                {{ \Carbon\Carbon::parse($filters['start_date'])->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($filters['end_date'])->translatedFormat('d M Y') }}
            @else
                @php
                    $bDari = $filters['bulan_dari'] ?? $filters['bulan'] ?? 'ALL';
                    $bSampai = $filters['bulan_sampai'] ?? $filters['bulan'] ?? 'ALL';
                    $rangeText = ($bDari === 'ALL' && $bSampai === 'ALL') ? __('Semua Bulan') : (($bDari === $bSampai) ? $bDari : ($bDari . ' - ' . $bSampai));
                @endphp
                {{ $rangeText }} {{ $filters['tahun'] ?? 'ALL' }}
            @endif
        </h3>
    </div>

    <div class="filters">
        <table style="width: 50%; border: none; margin-top: 0;">
            <tr><td style="border: none; padding: 2px;"><strong>{{ __('PT') }}:</strong> {{ $filters['pt'] ?? __('Semua') }}</td></tr>
            <tr><td style="border: none; padding: 2px;"><strong>{{ __('Area') }}:</strong> {{ $filters['area'] ?? __('Semua') }}</td></tr>
            <tr><td style="border: none; padding: 2px;"><strong>{{ __('Group Aset') }}:</strong> {{ $filters['group_aset'] ?? __('Semua') }}</td></tr>
            <tr><td style="border: none; padding: 2px;"><strong>{{ __('Aset (Unit)') }}:</strong> {{ $filters['id_aset'] ?? __('Semua') }}</td></tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('No') }}</th>
                <th>{{ __('Tahun') }}</th>
                <th>{{ __('Bulan') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Group Aset') }}</th>
                <th>{{ __('Area') }}</th>
                <th>{{ __('PT') }}</th>
                <th>{{ __('Internal Order') }}</th>
                <th>{{ __('IO Group') }}</th>
                <th>{{ __('Group Desc') }}</th>
                <th class="text-right">{{ __('Solar Akt (L)') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->tahun }}</td>
                <td>{{ $row->bulan }}</td>
                <td>{{ $row->unit_code }}</td>
                <td>{{ $row->group_aset }}</td>
                <td>{{ $row->area }}</td>
                <td>{{ $row->pt }}</td>
                <td>{{ $row->internal_order }}</td>
                <td>{{ $row->group_internal_order }}</td>
                <td>{{ $row->group_desc }}</td>
                <td class="text-right">{{ number_format($row->total_quantity, 1) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">{{ __('Tidak ada data ditemukan') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ __('Dicetak pada:') }} {{ now()->translatedFormat('d F Y H:i') }} | Halaman <span class="page-number"></span>
    </div>
</body>
</html>
