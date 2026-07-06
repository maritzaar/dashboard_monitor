@extends('layouts.app')

@section('title', __('Dashboard Monitoring'))

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- Print styles --}}
    <style>
        @media print {
            nav, footer, .no-print, #assetSearchInput, th:last-child, td:last-child { display: none !important; }
            main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        }
    </style>

    {{-- ====== FILTER BAR ====== --}}
    <div class="bg-white rounded-xl border border-stone-200 p-3 sm:p-4 no-print">
        <form action="{{ route('monitoring.index') }}" method="GET">
            {{-- Row 1: selects + filter button --}}
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[120px]">
                    <label class="text-xs font-bold text-stone-400 uppercase tracking-wider block mb-1">{{ __('Bulan') }}</label>
                    <select name="bulan"
                            class="w-full rounded-lg border border-stone-300 bg-stone-50 text-stone-700 text-sm py-2 px-3 focus:border-[#FFC107] focus:ring-[#FFC107] focus:outline-none">
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ __($m) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[90px]">
                    <label class="text-xs font-bold text-stone-400 uppercase tracking-wider block mb-1">{{ __('Tahun') }}</label>
                    <select name="tahun"
                            class="w-full rounded-lg border border-stone-300 bg-stone-50 text-stone-700 text-sm py-2 px-3 focus:border-[#FFC107] focus:ring-[#FFC107] focus:outline-none">
                        @for($i = 2023; $i <= date('Y'); $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <button type="submit"
                        class="bg-[#FFC107] text-stone-900 font-bold px-4 py-2 rounded-lg hover:bg-[#e0a800] transition text-sm flex items-center shadow-sm whitespace-nowrap">
                    <i class="fas fa-filter mr-1.5"></i> {{ __('Filter') }}
                </button>
            </div>

            {{-- Row 2: export buttons (stack on mobile, row on sm+) --}}
            <div class="flex flex-wrap gap-2 mt-3">
                <a href="{{ route('monitoring.export') }}?bulan={{ $bulan }}&tahun={{ $tahun }}"
                   class="flex-1 sm:flex-none bg-emerald-700 text-white font-semibold px-4 py-2 rounded-lg hover:bg-emerald-800 transition inline-flex items-center justify-center text-sm shadow-sm whitespace-nowrap">
                    <i class="fas fa-file-excel mr-1.5"></i> {{ __('Unduh Excel') }}
                </a>
                <button type="button" onclick="window.print()"
                        class="flex-1 sm:flex-none bg-stone-700 text-white font-semibold px-4 py-2 rounded-lg hover:bg-stone-800 transition inline-flex items-center justify-center text-sm shadow-sm whitespace-nowrap">
                    <i class="fas fa-print mr-1.5"></i> {{ __('Cetak PDF') }}
                </button>
            </div>
        </form>
    </div>

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Card 1 --}}
        <div class="bg-white rounded-xl border border-stone-200 border-l-4 border-l-[#FFC107] p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider leading-tight">{{ __('Total Aset') }}</p>
                <p class="text-xl sm:text-2xl font-bold text-stone-800 mt-0.5">{{ $stats->total_aset ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-amber-50 flex items-center justify-center text-[#8E6E4F] flex-shrink-0">
                <i class="fas fa-tools text-base sm:text-lg"></i>
            </div>
        </div>
        {{-- Card 2 --}}
        <div class="bg-white rounded-xl border border-stone-200 border-l-4 border-l-emerald-500 p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider leading-tight">{{ __('Total Waktu Kerja') }}</p>
                <p class="text-xl sm:text-2xl font-bold text-stone-800 mt-0.5">
                    {{ number_format($stats->total_waktu_kerja ?? 0, 1) }}
                    <span class="text-xs font-normal text-stone-500">{{ app()->getLocale() == 'en' ? 'hrs' : 'jam' }}</span>
                </p>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-700 flex-shrink-0">
                <i class="fas fa-clock text-base sm:text-lg"></i>
            </div>
        </div>
        {{-- Card 3 --}}
        <div class="bg-white rounded-xl border border-stone-200 border-l-4 border-l-amber-500 p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider leading-tight">{{ __('Rata-rata Idle') }}</p>
                <p class="text-xl sm:text-2xl font-bold text-stone-800 mt-0.5">{{ number_format($stats->avg_idle ?? 0, 1) }}%</p>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 flex-shrink-0">
                <i class="fas fa-hourglass-half text-base sm:text-lg"></i>
            </div>
        </div>
        {{-- Card 4 --}}
        <div class="bg-white rounded-xl border border-stone-200 border-l-4 border-l-rose-500 p-4 sm:p-5 flex items-center justify-between">
            <div>
                <p class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider leading-tight">{{ __('Total Bahan Bakar') }}</p>
                <p class="text-xl sm:text-2xl font-bold text-stone-800 mt-0.5">
                    {{ number_format($stats->total_bahan_bakar ?? 0, 0) }}
                    <span class="text-xs font-normal text-stone-500">L</span>
                </p>
            </div>
            <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 flex-shrink-0">
                <i class="fas fa-gas-pump text-base sm:text-lg"></i>
            </div>
        </div>
    </div>

    {{-- ====== CHARTS ====== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        {{-- Daily bar chart (2/3 width on desktop) --}}
        <div class="bg-white rounded-xl border border-stone-200 p-4 sm:p-6 lg:col-span-2">
            <h3 class="text-xs font-bold text-stone-500 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-bar text-[#FFC107] mr-2"></i> {{ __('Grafik Harian Jam Kerja') }}
            </h3>
            {{-- Fixed height; canvas scales inside --}}
            <div class="relative h-56 sm:h-72">
                <canvas id="monitoringChart"></canvas>
            </div>
        </div>

        {{-- Doughnut + Pie (1/3 width) --}}
        <div class="bg-white rounded-xl border border-stone-200 p-4 sm:p-6">
            <h3 class="text-xs font-bold text-stone-500 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-pie text-[#FFC107] mr-2"></i> {{ __('Analisis Utilisasi & Area') }}
            </h3>
            {{-- On mobile these sit side-by-side; on lg they stack --}}
            <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                <div class="flex flex-col items-center">
                    <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 text-center">{{ __('Utilisasi Jam Kerja') }}</p>
                    <div class="w-full max-w-[140px] h-24 sm:h-28 relative">
                        <canvas id="utilizationChart"></canvas>
                    </div>
                </div>
                <div class="flex flex-col items-center">
                    <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider mb-2 text-center">{{ __('Beban per Area') }}</p>
                    <div class="w-full max-w-[140px] h-24 sm:h-28 relative">
                        <canvas id="areaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $highIdleAssets = $perAset->filter(fn($i) => $i->avg_idle > 40);
        $areaData = [];
        foreach ($perAset as $item) {
            if ($item->area) $areaData[$item->area] = ($areaData[$item->area] ?? 0) + $item->total_operasi;
        }
    @endphp

    {{-- ====== HIGH IDLE ALERT ====== --}}
    @if($highIdleAssets->count() > 0)
    <div class="bg-rose-50 border border-rose-100 border-l-4 border-l-rose-500 p-3 sm:p-4 rounded-xl shadow-sm no-print">
        <div class="flex items-start space-x-3">
            <i class="fas fa-exclamation-triangle text-rose-600 text-lg animate-pulse flex-shrink-0 mt-0.5"></i>
            <div class="min-w-0">
                <h4 class="text-sm font-bold text-rose-800">{{ __('Peringatan: Idle Tinggi (>40%)') }}</h4>
                <p class="text-xs text-rose-700 mt-1">
                    {!! __('Ditemukan <strong>:count unit</strong> alat dengan idle tinggi:', ['count' => $highIdleAssets->count()]) !!}
                </p>
                <div class="flex flex-wrap gap-2 mt-2">
                    @foreach($highIdleAssets as $asset)
                    <a href="{{ route('monitoring.detail', $asset->id_aset) }}?bulan={{ $bulan }}&tahun={{ $tahun }}"
                       class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 hover:bg-rose-200 transition border border-rose-200">
                        <i class="fas fa-tools mr-1 text-rose-500"></i>
                        {{ $asset->id_aset }} ({{ number_format($asset->avg_idle, 1) }}%)
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== ASSET TABLE ====== --}}
    <div class="bg-white rounded-xl border border-stone-200 p-3 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <h3 class="text-xs font-bold text-stone-500 uppercase tracking-wider flex items-center">
                <i class="fas fa-table text-[#FFC107] mr-2"></i>
                {{ __('Ringkasan Per Aset') }}
                <span class="text-[10px] font-normal text-stone-400 normal-case ml-2">({{ __($bulan) }} {{ $tahun }})</span>
            </h3>
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-400">
                    <i class="fas fa-search text-xs"></i>
                </div>
                <input type="text" id="assetSearchInput" placeholder="{{ __('Cari Aset...') }}"
                       class="pl-8 pr-4 py-2 w-full border border-stone-300 rounded-lg text-sm bg-stone-50 text-stone-800 placeholder-stone-400 focus:ring-[#FFC107] focus:border-[#FFC107] focus:outline-none">
            </div>
        </div>

        {{-- Horizontally scrollable table on small screens --}}
        <div class="overflow-x-auto -mx-3 sm:mx-0 table-scroll">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap">{{ __('Aset') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap hidden sm:table-cell">{{ __('Grup') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap hidden md:table-cell">{{ __('Area') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap">{{ __('Kerja') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap hidden sm:table-cell">{{ __('Operasi') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap hidden sm:table-cell">{{ __('Idle') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap">{{ __('% Idle') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-left text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap hidden md:table-cell">{{ __('BBM (L)') }}</th>
                        <th class="px-3 sm:px-4 py-3 text-center text-[10px] sm:text-xs font-bold text-stone-500 uppercase tracking-wider whitespace-nowrap">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-stone-100">
                    @forelse($perAset as $item)
                    <tr class="hover:bg-stone-50/60 transition">
                        <td class="px-3 sm:px-4 py-3">
                            <div class="flex items-center space-x-2 sm:space-x-3">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-stone-100 flex items-center justify-center text-[#8E6E4F] border border-stone-200 flex-shrink-0">
                                    @php
                                        $ml = strtolower($item->model);
                                        if (str_contains($ml,'compactor')||str_contains($ml,'cs')) echo '<i class="fas fa-trailer text-xs"></i>';
                                        elseif (str_contains($ml,'truck')||str_contains($ml,'dump')) echo '<i class="fas fa-truck text-xs"></i>';
                                        else echo '<i class="fas fa-truck-monster text-xs"></i>';
                                    @endphp
                                </div>
                                <div class="min-w-0">
                                    <span class="block font-bold text-stone-800 text-xs sm:text-sm leading-tight truncate max-w-[80px] sm:max-w-none">{{ $item->id_aset }}</span>
                                    <span class="block text-[9px] sm:text-[10px] text-stone-400 font-mono truncate max-w-[80px] sm:max-w-none">{{ $item->model }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-stone-600 hidden sm:table-cell">{{ $item->group_aset ?? '-' }}</td>
                        <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-stone-600 hidden md:table-cell">{{ $item->area ?? '-' }}</td>
                        <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm font-semibold text-stone-700 whitespace-nowrap">{{ number_format($item->total_kerja, 1) }}</td>
                        <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm font-semibold text-stone-700 whitespace-nowrap hidden sm:table-cell">{{ number_format($item->total_operasi, 1) }}</td>
                        <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm font-semibold text-stone-700 whitespace-nowrap hidden sm:table-cell">{{ number_format($item->total_idle, 1) }}</td>
                        <td class="px-3 sm:px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold border whitespace-nowrap
                                @if(($item->avg_idle ?? 0) < 30) bg-emerald-50 text-emerald-800 border-emerald-100
                                @elseif(($item->avg_idle ?? 0) < 50) bg-amber-50 text-amber-800 border-amber-100
                                @else bg-rose-50 text-rose-800 border-rose-100 @endif">
                                {{ number_format($item->avg_idle ?? 0, 1) }}%
                            </span>
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-xs sm:text-sm text-stone-700 whitespace-nowrap hidden md:table-cell">
                            {{ number_format($item->total_bakar ?? 0, 0) }}
                        </td>
                        <td class="px-3 sm:px-4 py-3 text-center">
                            <a href="{{ route('monitoring.detail', $item->id_aset) }}?bulan={{ $bulan }}&tahun={{ $tahun }}"
                               class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-stone-200 bg-stone-50 text-stone-600 hover:bg-stone-100 text-xs font-bold transition whitespace-nowrap">
                                <i class="fas fa-eye mr-1 text-stone-400"></i> {{ __('Detail') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-stone-400">
                            <i class="fas fa-inbox text-3xl block mb-2 text-stone-300"></i>
                            <span class="text-sm">{{ __('Belum ada data untuk :bulan :tahun. Silakan import data terlebih dahulu.', ['bulan' => __($bulan), 'tahun' => $tahun]) }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Search filter
    const searchInput = document.getElementById('assetSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('table tbody tr').forEach(row => {
                if (row.cells.length < 2) return;
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(q) ? '' : 'none';
            });
        });
    }

    // Daily bar chart
    fetch('{{ route("monitoring.chart") }}?bulan={{ $bulan }}&tahun={{ $tahun }}')
        .then(r => r.json())
        .then(data => {
            new Chart(document.getElementById('monitoringChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: data.map(item => {
                        const d = new Date(item.tanggal);
                        return d.getDate() + '/' + (d.getMonth()+1);
                    }),
                    datasets: [
                        { label: '{{ __("Waktu Kerja") }}',   data: data.map(i=>i.total_kerja),   backgroundColor:'rgba(16,185,129,.7)',  borderColor:'rgba(16,185,129,1)',  borderWidth:1 },
                        { label: '{{ __("Waktu Operasi") }}', data: data.map(i=>i.total_operasi), backgroundColor:'rgba(142,110,79,.7)',  borderColor:'rgba(142,110,79,1)',  borderWidth:1 },
                        { label: '{{ __("Waktu Idle") }}',    data: data.map(i=>i.total_idle),    backgroundColor:'rgba(217,160,89,.7)', borderColor:'rgba(217,160,89,1)', borderWidth:1 },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position:'top', labels: { boxWidth:12, font:{ size:10 } } } },
                    scales: {
                        y: { beginAtZero:true, title:{ display:true, text:'{{ __("Jam") }}', font:{size:10} } },
                        x: { title:{ display:true, text:'{{ __("Tanggal") }}', font:{size:10} } }
                    }
                }
            });
        });

    // Utilization doughnut
    new Chart(document.getElementById('utilizationChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['{{ __("Kerja") }}','{{ __("Idle") }}'],
            datasets: [{ data:[{{ $stats->total_waktu_kerja ?? 0 }},{{ $stats->total_waktu_idle ?? 0 }}], backgroundColor:['#10b981','#d9a059'], hoverOffset:4 }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:9} } } } }
    });

    // Area pie
    new Chart(document.getElementById('areaChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: @json(array_keys($areaData)),
            datasets: [{ data: @json(array_values($areaData)), backgroundColor:['#a7825e','#10b981','#d9a059','#ef4444','#b08b63','#ec4899','#8b7a6c'], hoverOffset:4 }]
        },
        options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:10, font:{size:9} } } } }
    });
});
</script>
@endsection