@extends('layouts.app')

@section('title', 'Laporan Jam Kerja Bulanan')

@section('content')
<div class="space-y-6">

    {{-- ====== HEADER ====== --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-xl p-5 text-white shadow-md">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-tpaOrange-500/20 border border-tpaOrange-500/30 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-calendar-alt text-xl text-tpaOrange-400"></i>
                </div>
                <div>
                    <p class="text-xs text-tpaOrange-300 font-semibold uppercase tracking-wider">Laporan Operasional Bulanan</p>
                    <h2 class="text-2xl font-extrabold tracking-wide">Laporan Konsolidasi Jam Kerja (Bulanan)</h2>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-xs text-tpaOrange-300">Periode</p>
                <p class="text-md font-bold">
                    {{ $bulan_dari == 'ALL' ? 'Jan' : substr($bulan_dari, 0, 3) }} –
                    {{ $bulan_sampai == 'ALL' ? 'Dec' : substr($bulan_sampai, 0, 3) }}
                    {{ $tahun == 'ALL' ? __('Semua Tahun') : $tahun }}
                </p>
            </div>
        </div>
    </div>

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Total Assets --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaGreen-500 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Unit Aset</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->total_aset, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">Unit</span>
            </p>
        </div>

        {{-- Total Kerja --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaOrange-600 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Jam Kerja</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->total_kerja, 1) }}
                <span class="text-xs font-normal text-slate-400 ml-1">Jam</span>
            </p>
        </div>

        {{-- Avg Idle --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 {{ ($stats->avg_idle ?? 0) <= 10 ? 'border-l-emerald-500' : 'border-l-amber-500' }} p-4 shadow-sm transition-colors duration-200">
            <div class="flex items-center justify-between">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-Rata Idle</p>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ ($stats->avg_idle ?? 0) <= 10 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400' }}">
                    {{ ($stats->avg_idle ?? 0) <= 10 ? 'Aman' : 'Warning' }}
                </span>
            </div>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->avg_idle, 1) }}
                <span class="text-xs font-normal text-slate-400 ml-1">%</span>
            </p>
        </div>
    </div>

    {{-- ====== CHART SECTION ====== --}}
    @if($reports->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart (Kiri - 2/3 width) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-bar text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> Perbandingan Jam Kerja & Jam Idle per Aset
            </h3>
            <div class="relative h-72 sm:h-96">
                <canvas id="consolidatedReportChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart (Kanan - 1/3 width) -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-pie text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> Rasio Total Jam Kerja vs Jam Idle
            </h3>
            <div class="relative h-72 sm:h-96 flex items-center justify-center">
                <canvas id="workingHourPieChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== TREND LINE CHART ====== --}}
    @if($reports->isNotEmpty())
    <div class="mt-6 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
        <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center">
            <i class="fas fa-chart-line text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> Tren Jam Kerja & Idle Bulanan
        </h3>
        <div class="relative h-72 sm:h-96 w-full">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
    @endif

    {{-- ====== FILTER BAR ====== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 shadow-sm no-print transition-colors duration-200">
        <form action="{{ route('monitoring.working_hour_monthly') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
                {{-- Tahun --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Tahun</label>
                    <select name="tahun" id="filter_tahun" class="dependent-filter w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $tahun == 'ALL' ? 'selected' : '' }}>{{ __('Semua Tahun') }}</option>
                        @for($i = 2023; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                {{-- Bulan Dari --}}
                @php $months = ['January','February','March','April','May','June','July','August','September','October','November','December']; @endphp
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Bulan Mulai</label>
                    <select name="bulan_dari" id="filter_bulan_dari" class="dependent-filter w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $bulan_dari == 'ALL' ? 'selected' : '' }}>Semua</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $bulan_dari == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Bulan Sampai --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Bulan Akhir</label>
                    <select name="bulan_sampai" id="filter_bulan_sampai" class="dependent-filter w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $bulan_sampai == 'ALL' ? 'selected' : '' }}>Semua</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $bulan_sampai == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Grup --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Group Aset</label>
                    <select name="group_aset" id="filter_group_aset" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_aset) || $group_aset == 'ALL') ? 'selected' : '' }}>{{ __('Semua Grup') }}</option>
                        @foreach($filterGroups as $group)
                            <option value="{{ $group }}" {{ (isset($group_aset) && $group_aset == $group) ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Area --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Area</label>
                    <select name="area" id="filter_area" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($area) || $area == 'ALL') ? 'selected' : '' }}>{{ __('Semua Area') }}</option>
                        @foreach($filterAreas as $a)
                            <option value="{{ $a }}" {{ (isset($area) && $area == $a) ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- PT --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">PT</label>
                    <select name="pt" id="filter_pt" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($pt) || $pt == 'ALL') ? 'selected' : '' }}>{{ __('Semua PT') }}</option>
                        @foreach($filterPts as $p)
                            <option value="{{ $p }}" {{ (isset($pt) && $pt == $p) ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Aset --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Aset (Unit)</label>
                    <select name="id_aset" id="filter_id_aset" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($id_aset) || $id_aset == 'ALL') ? 'selected' : '' }}>{{ __('Semua Aset') }}</option>
                        @foreach($filterUnits as $unit)
                            <option value="{{ $unit }}" {{ (isset($id_aset) && $id_aset == $unit) ? 'selected' : '' }}>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Group Desc --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Group Desc</label>
                    <select name="group_desc" id="filter_group_desc" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_desc) || $group_desc == 'ALL') ? 'selected' : '' }}>{{ __('Semua Group Desc') }}</option>
                        @foreach($filterGroupDescs as $gd)
                            <option value="{{ $gd }}" {{ (isset($group_desc) && $group_desc == $gd) ? 'selected' : '' }}>{{ $gd }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- IO Group --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">IO Group</label>
                    <select name="group_internal_order" id="filter_group_internal_order" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_internal_order) || $group_internal_order == 'ALL') ? 'selected' : '' }}>{{ __('Semua IO Group') }}</option>
                        @foreach($filterIoGroups as $ig)
                            <option value="{{ $ig }}" {{ (isset($group_internal_order) && $group_internal_order == $ig) ? 'selected' : '' }}>{{ $ig }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Internal Order --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Internal Order</label>
                    <select name="internal_order" id="filter_internal_order" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($internal_order) || $internal_order == 'ALL') ? 'selected' : '' }}>{{ __('Semua Internal Order') }}</option>
                        @foreach($filterInternalOrders as $io)
                            <option value="{{ $io }}" {{ (isset($internal_order) && $internal_order == $io) ? 'selected' : '' }}>{{ $io }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-slate-100 dark:border-white/5">
                <a href="{{ route('monitoring.working_hour_monthly') }}" class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5 transition-colors">
                    <i class="fas fa-undo mr-1.5"></i> Reset Filter
                </a>
                <button type="submit" class="bg-tpaGreen-600 hover:bg-tpaGreen-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm">
                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- ====== DATA TABLE ====== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
        <div class="border-b border-slate-100 dark:border-white/5 pb-3 mb-4 flex flex-wrap justify-between items-center gap-2">
            <div>
                <h3 class="text-md font-bold text-slate-800 dark:text-slate-200 flex items-center">
                    <i class="fas fa-list-check text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> Rincian Kinerja Operasional Aset Bulanan
                </h3>
            </div>
            <div class="text-right flex items-center justify-end gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" id="assetSearchInput" placeholder="Cari data..."
                           class="pl-8 pr-3 py-1.5 w-full sm:w-48 border border-slate-300 dark:border-white/10 rounded-lg text-sm bg-slate-50 dark:bg-[#0B1120] text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-tpaGreen-600 focus:border-tpaGreen-600 focus:outline-none transition-all">
                </div>
                <span class="text-xs bg-slate-100 dark:bg-[#0B1120] text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm whitespace-nowrap">
                    {{ number_format($reports->count()) }} data
                </span>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[600px] table-scroll">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5 border border-slate-100 dark:border-white/5 text-sm">
                <thead class="bg-slate-50 dark:bg-[#0B1120] sticky top-0 shadow-sm z-10">
                    <tr>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Group</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Area</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">PT</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Unit</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tahun</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bulan</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Model</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Internal Order</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">IO Group</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Group Desc</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Kerja (Jam)</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Op (Jam)</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Idle (Jam)</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">% Idle</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-white/5" id="assetTableBody">
                    @forelse($reports as $row)
                    @php
                        $isWarning = ($row->avg_idle ?? 0) > 10;
                        $numColor = $isWarning ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-700 dark:text-slate-300 font-medium';
                    @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition asset-row">
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_aset ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->area ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->pt ?? '-' }}</td>
                        <td class="px-3 py-2.5 font-bold text-slate-800 dark:text-slate-200 text-xs font-mono">{{ $row->id_aset }}</td>
                        <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300 text-xs font-semibold">{{ $row->tahun }}</td>
                        <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300 text-xs font-medium">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                {{ $row->bulan }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->model ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs font-mono">{{ $row->internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_desc ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs {{ $numColor }}">{{ number_format($row->total_kerja, 1) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs {{ $numColor }}">{{ number_format($row->total_operasi, 1) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs {{ $numColor }}">{{ number_format($row->total_idle, 1) }}</td>
                        <td class="px-3 py-2.5 text-right text-xs">
                            <span class="font-bold font-mono {{ !$isWarning ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                {{ number_format($row->avg_idle, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="px-6 py-8 text-center text-slate-400 text-sm">
                            <i class="fas fa-inbox text-3xl mb-2 block"></i>
                            Tidak ada data untuk periode dan filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- ====== SCRIPTS ====== --}}
@if($reports->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    
    // Bar chart
    const assetLabels = @json($chartData->pluck('id_aset'));
    const workingHours = @json($chartData->pluck('total_kerja'));
    const idleHours = @json($chartData->pluck('total_idle'));

    new Chart(document.getElementById('consolidatedReportChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: assetLabels,
            datasets: [
                {
                    label: 'Jam Kerja (Jam)',
                    data: workingHours,
                    backgroundColor: 'rgba(240, 123, 35, 0.85)', // TPA Orange
                    borderColor: '#F07B23',
                    borderWidth: 1,
                    borderRadius: 3
                },
                {
                    label: 'Jam Idle (Jam)',
                    data: idleHours,
                    backgroundColor: 'rgba(86, 141, 73, 0.75)', // TPA Green
                    borderColor: '#568D49',
                    borderWidth: 1,
                    borderRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: { color: isDark ? '#cbd5e1' : '#475569' }
                }
            },
            scales: {
                x: {
                    ticks: { color: isDark ? '#94a3b8' : '#64748b', maxRotation: 45, minRotation: 0 },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { color: isDark ? '#94a3b8' : '#64748b' },
                    grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : '#e2e8f0' },
                    title: { display: true, text: 'Jam', font: { weight: 'bold' }, color: isDark ? '#cbd5e1' : '#475569' }
                }
            }
        }
    });

    // Doughnut chart (Jam Kerja vs Jam Idle)
    new Chart(document.getElementById('workingHourPieChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Jam Kerja (Jam)', 'Jam Idle (Jam)'],
            datasets: [{
                data: [{{ $stats->total_kerja }}, {{ $stats->total_idle }}],
                backgroundColor: ['#F07B23', '#568D49'],
                borderColor: [isDark ? '#0f172a' : '#ffffff', isDark ? '#0f172a' : '#ffffff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        font: { size: 11 },
                        color: isDark ? '#cbd5e1' : '#475569'
                    }
                }
            },
            cutout: '65%'
        }
    });

    // Trend Line Chart (Monthly Trend)
    const trendLabels = @json($trendChartData->pluck('bulan'));
    const trendKerja = @json($trendChartData->pluck('total_kerja'));
    const trendIdle = @json($trendChartData->pluck('total_idle'));

    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Total Waktu Kerja (Jam)',
                    data: trendKerja,
                    borderColor: '#F07B23',
                    backgroundColor: 'rgba(240, 123, 35, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Total Waktu Idle (Jam)',
                    data: trendIdle,
                    borderColor: '#568D49',
                    backgroundColor: 'rgba(86, 141, 73, 0.1)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top', labels: { color: isDark ? '#cbd5e1' : '#475569' } },
                tooltip: { 
                    backgroundColor: isDark ? '#1e293b' : 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    mode: 'index',
                    intersect: false
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            },
            scales: {
                x: { 
                    ticks: { color: isDark ? '#94a3b8' : '#64748b' },
                    grid: { display: false }
                },
                y: { 
                    beginAtZero: true,
                    ticks: { color: isDark ? '#94a3b8' : '#64748b' },
                    grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : '#e2e8f0', borderDash: [4, 4] }
                }
            }
        }
    });
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Custom Searchable Dropdown UI
    const selects = document.querySelectorAll('.searchable-select');
    selects.forEach(select => {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative w-full';
        
        const btn = document.createElement('div');
        btn.className = 'w-full flex items-center justify-between rounded-lg border border-slate-350 dark:border-white/10 bg-white dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus-within:border-tpaGreen-600 focus-within:ring-1 focus-within:ring-tpaGreen-600 focus:outline-none cursor-pointer select-none transition-colors duration-200';
        
        const btnText = document.createElement('span');
        btnText.className = 'truncate';
        
        const iconContainer = document.createElement('div');
        iconContainer.className = 'flex items-center space-x-1.5 ml-2 text-slate-400';
        
        const caret = document.createElement('i');
        caret.className = 'fas fa-chevron-down text-[10px] transition-transform duration-200';
        
        iconContainer.appendChild(caret);
        btn.appendChild(btnText);
        btn.appendChild(iconContainer);
        wrapper.appendChild(btn);
        
        const menu = document.createElement('div');
        menu.className = 'absolute left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-lg shadow-xl z-50 flex flex-col hidden transition-colors duration-200';
        menu.style.maxHeight = '280px';
        
        const searchBox = document.createElement('div');
        searchBox.className = 'p-2 border-b border-slate-100 dark:border-white/5 flex-shrink-0';
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search...';
        searchInput.className = 'w-full rounded-md border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 text-xs py-1.5 px-2.5 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200';
        searchBox.appendChild(searchInput);
        menu.appendChild(searchBox);
        
        const optionsList = document.createElement('div');
        optionsList.className = 'overflow-y-auto flex-1 max-h-48 py-1';
        menu.appendChild(optionsList);
        wrapper.appendChild(menu);
        
        select.parentNode.insertBefore(wrapper, select);
        select.classList.add('hidden');
        
        function populateOptions() {
            optionsList.innerHTML = '';
            const options = Array.from(select.options);
            options.forEach(opt => {
                const optItem = document.createElement('div');
                optItem.className = 'px-3 py-2 text-xs text-slate-700 dark:text-slate-300 hover:bg-tpaGreen-600 dark:hover:bg-white/5 hover:text-white dark:hover:text-slate-200 cursor-pointer transition-colors';
                optItem.textContent = opt.text;
                optItem.dataset.value = opt.value;
                
                if (opt.selected) {
                    optItem.classList.add('bg-tpaGreen-50', 'dark:bg-[#0B1120]', 'text-tpaGreen-800', 'dark:text-blue-300', 'font-semibold');
                    btnText.textContent = opt.text;
                }
                
                optItem.addEventListener('click', () => {
                    select.value = opt.value;
                    btnText.textContent = opt.text;
                    menu.classList.add('hidden');
                    caret.classList.remove('rotate-180');
                    select.dispatchEvent(new Event('change'));
                });
                
                optionsList.appendChild(optItem);
            });
            
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt) {
                btnText.textContent = selectedOpt.text;
            }
        }
        
        populateOptions();
        
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.searchable-select + .relative > div:last-child').forEach(otherMenu => {
                if (otherMenu !== menu) otherMenu.classList.add('hidden');
            });
            const isHidden = menu.classList.toggle('hidden');
            if (!isHidden) {
                searchInput.value = '';
                Array.from(optionsList.children).forEach(child => child.classList.remove('hidden'));
                setTimeout(() => searchInput.focus(), 50);
                caret.classList.add('rotate-180');
            } else {
                caret.classList.remove('rotate-180');
            }
        });
        
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase();
            Array.from(optionsList.children).forEach(item => {
                const match = item.textContent.toLowerCase().includes(query);
                item.classList.toggle('hidden', !match);
            });
        });
        
        select.updateCustomUI = function() {
            populateOptions();
        };
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.searchable-select + .relative > div:last-child').forEach(menu => {
            menu.classList.add('hidden');
        });
        document.querySelectorAll('.searchable-select + .relative i.fa-chevron-down').forEach(caret => {
            caret.classList.remove('rotate-180');
        });
    });

    // Dependent Filter AJAX
    const dependentFilters = document.querySelectorAll('.dependent-filter');
    dependentFilters.forEach(filter => {
        filter.addEventListener('change', async function(e) {
            let params = new URLSearchParams();
            params.append('type', 'working_hour_monthly');
            dependentFilters.forEach(f => {
                if (f.value && f.value !== 'ALL') {
                    params.append(f.name, f.value);
                }
            });

            try {
                let response = await fetch(`/api/monitoring/filter-options?${params.toString()}`);
                if (!response.ok) throw new Error('Network response was not ok');
                let data = await response.json();
                
                // Conflict resolution
                if (data.filterUnits && data.filterUnits.length === 0 && e.target.value && e.target.value !== 'ALL') {
                    params = new URLSearchParams();
                    params.append('type', 'working_hour_monthly');
                    params.append(e.target.name, e.target.value);
                    
                    dependentFilters.forEach(f => {
                        if (f !== e.target && f.name !== 'bulan_dari' && f.name !== 'bulan_sampai' && f.name !== 'tahun') {
                            f.value = 'ALL';
                            if (typeof f.updateCustomUI === 'function') f.updateCustomUI();
                        }
                    });

                    const filterTahun = document.getElementById('filter_tahun');
                    const filterBulanDari = document.getElementById('filter_bulan_dari');
                    const filterBulanSampai = document.getElementById('filter_bulan_sampai');
                    if (filterTahun && filterTahun.value) params.append('tahun', filterTahun.value);
                    if (filterBulanDari && filterBulanDari.value) params.append('bulan_dari', filterBulanDari.value);
                    if (filterBulanSampai && filterBulanSampai.value) params.append('bulan_sampai', filterBulanSampai.value);

                    response = await fetch(`/api/monitoring/filter-options?${params.toString()}`);
                    data = await response.json();
                }
                
                updateFilterOptions('filter_id_aset', data.filterUnits, 'Semua Aset');
                updateFilterOptions('filter_group_aset', data.filterGroups, 'Semua Grup');
                updateFilterOptions('filter_area', data.filterAreas, 'Semua Area');
                updateFilterOptions('filter_group_internal_order', data.filterIoGroups, 'Semua IO Group');
                updateFilterOptions('filter_internal_order', data.filterInternalOrders, 'Semua Internal Order');
                updateFilterOptions('filter_group_desc', data.filterGroupDescs, 'Semua Group Desc');
                updateFilterOptions('filter_pt', data.filterPts, 'Semua PT');

            } catch (error) {
                console.error('Error fetching filter options:', error);
            }
        });
    });

    function updateFilterOptions(selectId, newOptions, defaultLabel) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const currentValue = select.value;
        select.innerHTML = `<option value="ALL">${defaultLabel}</option>`;
        
        let valueStillExists = (currentValue === 'ALL');

        newOptions.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.textContent = opt;
            if (opt === currentValue) {
                option.selected = true;
                valueStillExists = true;
            }
            select.appendChild(option);
        });

        if (!valueStillExists) {
            select.value = 'ALL';
        }

        if (typeof select.updateCustomUI === 'function') {
            select.updateCustomUI();
        }
    }

    // Client-side quick search for table rows
    const searchInput = document.getElementById('assetSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#assetTableBody .asset-row');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});
</script>
@endsection
