@extends('layouts.app')

@section('title', 'Laporan Utama Monitoring')

@section('content')
<div class="space-y-6">

    {{-- ====== HEADER ====== --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-xl p-5 text-white shadow-md">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-tpaOrange-500/20 border border-tpaOrange-500/30 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-file-invoice-dollar text-xl text-tpaOrange-400"></i>
                </div>
                <div>
                    <p class="text-xs text-tpaOrange-300 font-semibold uppercase tracking-wider">{{ __('Laporan Konsumsi BBM/Solar') }}</p>
                    <h2 class="text-2xl font-extrabold tracking-wide">{{ __('Laporan Konsumsi Solar') }}</h2>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-xs text-tpaOrange-300">{{ __('Periode') }}</p>
                <p class="text-md font-bold">
                    {{ $bulan_dari == 'ALL' ? 'Jan' : substr($bulan_dari, 0, 3) }} –
                    {{ $bulan_sampai == 'ALL' ? 'Dec' : substr($bulan_sampai, 0, 3) }}
                    {{ $tahun == 'ALL' ? __('Semua Tahun') : $tahun }}
                </p>
            </div>
        </div>
    </div>

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        {{-- Total Assets --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaGreen-500 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Unit Aset') }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->total_aset, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">{{ __('Unit') }}</span>
            </p>
        </div>

        {{-- Total Fuel (Budget Aktual) --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaGreen-600 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Budget Aktual') }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->actual_fuel, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L</span>
            </p>
        </div>

        {{-- Total Budget Solar --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-blue-500 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Budget Solar') }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->solar_budget ?? 0, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L</span>
            </p>
        </div>

        {{-- Avg Fuel --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaOrange-600 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Rata-Rata Solar / Unit') }}</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->avg_fuel, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L</span>
            </p>
        </div>

        {{-- Max Fuel --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaOrange-500 p-4 shadow-sm flex flex-col justify-between transition-colors duration-200">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Konsumsi Tertinggi') }}</p>
                <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                    {{ number_format($stats->max_fuel_val, 0) }}
                    <span class="text-xs font-normal text-slate-400 ml-1">L</span>
                </p>
            </div>
            @if($stats->max_fuel_aset !== '-')
            <div class="text-[10px] text-slate-500 font-semibold mt-1">
                {{ __('Unit') }}: <span class="text-amber-600 dark:text-amber-400 font-bold font-mono">{{ $stats->max_fuel_aset }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ====== CHART SECTION ====== --}}
    @if($reports->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart (Kiri - 2/3 width) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-bar text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> {{ __('Perbandingan Konsumsi Solar per Aset') }}
            </h3>
            <div class="relative h-72 sm:h-96">
                <canvas id="fuelReportChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart (Kanan - 1/3 width) -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm flex flex-col transition-colors duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center">
                    <i class="fas fa-chart-pie text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> {{ __('Distribusi Konsumsi Solar') }}
                </h3>
                <div class="flex bg-slate-100 dark:bg-slate-900/50 rounded-lg p-0.5 border border-slate-200 dark:border-white/5 no-print text-[10px] font-bold">
                    <button type="button" id="toggleDoughnutGroup" class="px-2 py-1 rounded-md bg-white dark:bg-[#0B1120] text-slate-800 dark:text-slate-100 shadow-sm border border-slate-250 dark:border-white/5 transition-all focus:outline-none">{{ __('Grup') }}</button>
                    <button type="button" id="toggleDoughnutArea" class="px-2 py-1 rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-all focus:outline-none ml-0.5">{{ __('Area') }}</button>
                </div>
            </div>
            <div class="relative h-72 sm:h-96 flex-1 flex items-center justify-center">
                <canvas id="fuelDistributionChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== DUA TREND CHARTS (ATAS & BAWAH) ====== --}}
    @if($reports->isNotEmpty())
    <div class="space-y-6 mt-6">
        <!-- Chart 1: Tren Konsumsi Solar (Atas) -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center">
                    <i class="fas fa-gas-pump text-emerald-600 dark:text-emerald-400 mr-2"></i> {{ __('Tren Konsumsi Solar') }}
                </h3>
                <span class="text-[11px] font-semibold text-slate-400">{{ __('Solar Aktual vs Budget Solar') }}</span>
            </div>
            <div class="relative h-72 sm:h-96 w-full">
                <canvas id="trendSolarChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Tren Output Kerja (Bawah) -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center">
                    <i class="fas fa-tachometer-alt text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> {{ __('Tren Output Kerja') }}
                </h3>
                <span class="text-[11px] font-semibold text-slate-400">{{ __('Output Aktual vs Budget Output') }}</span>
            </div>
            <div class="relative h-72 sm:h-96 w-full">
                <canvas id="trendOutputChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== FILTER BAR ====== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 shadow-sm no-print transition-colors duration-200">
        <form action="{{ route('monitoring.fuel') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
                {{-- Tahun --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Tahun') }}</label>
                    <select name="tahun" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">

                    <option value="ALL" {{ $tahun == 'ALL' ? 'selected' : '' }}>{{ __('Semua Tahun') }}</option>
                        @for($i = 2023; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                {{-- Bulan Dari --}}
                @php $months = ['January','February','March','April','May','June','July','August','September','October','November','December']; @endphp
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Bulan Mulai') }}</label>
                    <select name="bulan_dari" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $bulan_dari == 'ALL' ? 'selected' : '' }}>{{ __('Semua Bulan') }}</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $bulan_dari == $m ? 'selected' : '' }}>{{ __($m) }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Bulan Sampai --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Bulan Akhir') }}</label>
                    <select name="bulan_sampai" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $bulan_sampai == 'ALL' ? 'selected' : '' }}>{{ __('Semua Bulan') }}</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $bulan_sampai == $m ? 'selected' : '' }}>{{ __($m) }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Aset --}}
                {{-- Grup --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Group Aset') }}</label>
                    <select name="group_aset" id="filter_group_aset" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_aset) || $group_aset == 'ALL') ? 'selected' : '' }}>{{ __('Semua Grup') }}</option>
                        @foreach($filterGroups as $group)
                            <option value="{{ $group }}" {{ (isset($group_aset) && $group_aset == $group) ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Area --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Area') }}</label>
                    <select name="area" id="filter_area" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($area) || $area == 'ALL') ? 'selected' : '' }}>{{ __('Semua Area') }}</option>
                        @foreach($filterAreas as $a)
                            <option value="{{ $a }}" {{ (isset($area) && $area == $a) ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- PT --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('PT') }}</label>
                    <select name="pt" id="filter_pt" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($pt) || $pt == 'ALL') ? 'selected' : '' }}>{{ __('Semua PT') }}</option>
                        @foreach($filterPts as $p)
                            <option value="{{ $p }}" {{ (isset($pt) && $pt == $p) ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Aset (Unit)') }}</label>
                    <select name="id_aset" id="filter_id_aset" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($id_aset) || $id_aset == 'ALL') ? 'selected' : '' }}>{{ __('Semua Aset') }}</option>
                        @foreach($filterUnits as $unit)
                            <option value="{{ $unit }}" {{ (isset($id_aset) && $id_aset == $unit) ? 'selected' : '' }}>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Group Desc --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Group Desc') }}</label>
                    <select name="group_desc" id="filter_group_desc" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_desc) || $group_desc == 'ALL') ? 'selected' : '' }}>{{ __('Semua Group Desc') }}</option>
                        @foreach($filterGroupDescs as $gd)
                            <option value="{{ $gd }}" {{ (isset($group_desc) && $group_desc == $gd) ? 'selected' : '' }}>{{ $gd }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- IO Group --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('IO Group') }}</label>
                    <select name="group_internal_order" id="filter_group_internal_order" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_internal_order) || $group_internal_order == 'ALL') ? 'selected' : '' }}>{{ __('Semua IO Group') }}</option>
                        @foreach($filterIoGroups as $ig)
                            <option value="{{ $ig }}" {{ (isset($group_internal_order) && $group_internal_order == $ig) ? 'selected' : '' }}>{{ $ig }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Internal Order --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Internal Order') }}</label>
                    <select name="internal_order" id="filter_internal_order" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($internal_order) || $internal_order == 'ALL') ? 'selected' : '' }}>{{ __('Semua Internal Order') }}</option>
                        @foreach($filterInternalOrders as $io)
                            <option value="{{ $io }}" {{ (isset($internal_order) && $internal_order == $io) ? 'selected' : '' }}>{{ $io }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-slate-100">
                <a href="{{ route('monitoring.fuel', ['bulan_dari' => 'ALL', 'bulan_sampai' => 'ALL', 'tahun' => 'ALL']) }}" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-600 font-semibold rounded-lg text-sm transition">
                    {{ __('Reset Filter') }}
                </a>
                <button type="submit" class="bg-gradient-to-r from-tpaGreen-600 to-tpaGreen-700 hover:from-tpaGreen-700 hover:to-tpaGreen-800 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm">
                    <i class="fas fa-filter mr-2"></i> {{ __('Terapkan Filter') }}
                </button>
                <a href="{{ route('monitoring.export', array_merge(request()->all(), ['type' => 'fuel'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm ml-2">
                    <i class="fas fa-file-excel mr-2"></i> {{ __('Unduh Excel') }}
                </a>
                <a href="{{ route('monitoring.export_pdf', array_merge(request()->all(), ['type' => 'fuel'])) }}" target="_blank" class="bg-gradient-to-r from-tpaOrange-500 to-tpaOrange-600 hover:from-tpaOrange-600 hover:to-tpaOrange-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm ml-2">
                    <i class="fas fa-file-pdf mr-2"></i> {{ __('Cetak PDF') }}
                </a>
            </div>
        </form>
    </div>




    {{-- ====== DATA TABLE ====== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
        <div class="border-b border-slate-100 dark:border-white/5 pb-3 mb-4 flex flex-wrap justify-between items-center gap-2">
            <div>
                <h3 class="text-md font-bold text-slate-800 dark:text-slate-200 flex items-center">
                    <i class="fas fa-list-check text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> {{ __('Rincian Konsumsi Solar Aset') }}
                </h3>
            </div>
            <div class="text-right flex items-center justify-end gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" id="assetSearchInput" placeholder="{{ __('Cari data...') }}"
                           class="pl-8 pr-3 py-1.5 w-full sm:w-48 border border-slate-300 dark:border-white/10 rounded-lg text-sm bg-slate-50 dark:bg-[#0B1120] text-slate-800 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 focus:ring-tpaGreen-600 focus:border-tpaGreen-600 focus:outline-none transition-all">
                </div>
                <span class="text-xs bg-slate-100 dark:bg-[#0B1120] text-slate-600 dark:text-slate-300 font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 dark:border-white/10 shadow-sm whitespace-nowrap">
                    {{ number_format($reports->count()) }} {{ __('data') }}
                </span>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[600px] table-scroll">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-white/5 border border-slate-100 dark:border-white/5 text-sm">
                <thead class="bg-slate-50 dark:bg-[#0B1120] sticky top-0 shadow-sm z-10">
                    <tr>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Grup') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Area') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('PT') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Unit') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Bulan') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Tahun') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Internal Order') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('IO Group') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Group Desc') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Satuan') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Output') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Solar Akt (L)') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Rasio') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Standar') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-white/5">
                    @php
                        $targetStandards = [
                            'ABA' => '≤ 5 L/Jam', 'ABC' => '≤ 12 L/Jam', 'ABE' => '≤ 16 L/Jam', 'ABG' => '≤ 12 L/Jam', 'ABT' => '≤ 5 L/Jam',
                            'KRD' => '≥ 3 KM/L', 'KRF' => '≥ 2 KM/L', 'KRK' => '≥ 4 KM/L', 'KRL' => '≥ 4 KM/L', 'KRT' => '≥ 4 KM/L',
                            'KRC' => '≥ 2 KM/L', 'KRS' => '≥ 4 KM/L', 'ABL' => '≤ 5 L/Jam', 'ABD' => '≤ 16 L/Jam'
                        ];
                    @endphp
                    @forelse($reports as $row)
                        @php
                            $kode = $row->group_internal_order;
                            $rasio = $row->rasio;
                            $isWarning = false;
                            
                            if (!is_null($rasio) && $rasio > 0) {
                                if ($kode == 'KRD' && $rasio < 3) $isWarning = true;
                                elseif ($kode == 'KRL' && $rasio < 4) $isWarning = true;
                                elseif ($kode == 'KRF' && $rasio < 2) $isWarning = true;
                                elseif ($kode == 'KRT' && $rasio < 4) $isWarning = true;
                                elseif ($kode == 'KRK' && $rasio < 4) $isWarning = true;
                                elseif ($kode == 'KRC' && $rasio < 2) $isWarning = true;
                                elseif ($kode == 'KRS' && $rasio < 4) $isWarning = true;
                                elseif ($kode == 'ABA' && $rasio > 5) $isWarning = true;
                                elseif ($kode == 'ABC' && $rasio > 12) $isWarning = true;
                                elseif ($kode == 'ABE' && $rasio > 16) $isWarning = true;
                                elseif ($kode == 'ABG' && $rasio > 12) $isWarning = true;
                                elseif ($kode == 'ABT' && $rasio > 5) $isWarning = true;
                                elseif ($kode == 'ABL' && $rasio > 5) $isWarning = true;
                                elseif ($kode == 'ABD' && $rasio > 16) $isWarning = true;
                            }
                            $numColor = $isWarning ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-700 dark:text-slate-300 font-medium';
                        @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition">
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_aset ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->area ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->pt ?? '-' }}</td>
                        <td class="px-3 py-2.5 font-bold text-slate-700 dark:text-slate-300 font-mono">{{ $row->id_aset }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->bulan }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->tahun }}</td>
                        <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300 font-mono text-xs">{{ $row->internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_desc ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs font-semibold">
                            {{ $row->is_kendaraan ? 'KM' : 'HM' }}
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs {{ $numColor }}">{{ $row->total_kerja > 0 ? number_format($row->total_kerja, 1) : '-' }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs {{ $numColor }}">{{ number_format($row->actual_fuel, 0) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold">
                            @if(is_null($rasio) || $rasio == 0)
                                <span class="text-slate-400">-</span>
                            @elseif($isWarning)
                                <span class="bg-[#F07B23] text-white px-2 py-1 rounded shadow-sm inline-flex items-center gap-1">
                                    {{ number_format($rasio, 2) }} 
                                    @if(str_starts_with($kode, 'AB'))
                                        <i class="fas fa-arrow-up text-[10px]"></i>
                                    @else
                                        <i class="fas fa-arrow-down text-[10px]"></i>
                                    @endif
                                </span>
                            @else
                                <span class="text-emerald-600 dark:text-emerald-400">{{ number_format($rasio, 2) }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs text-slate-500 dark:text-slate-400">
                            {{ $targetStandards[$kode] ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="px-4 py-12 text-center text-slate-400">
                            <i class="fas fa-filter-circle-xmark text-3xl block mb-2 text-slate-300"></i>
                            <span class="text-xs">Tidak ada data operasional/transaksi solar yang cocok dengan filter aktif.</span>
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
    const selects = document.querySelectorAll('.searchable-select');
    selects.forEach(select => {
        // Create custom UI wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'relative w-full';
        
        // Selected text element
        const btn = document.createElement('div');
        btn.className = 'w-full flex items-center justify-between rounded-lg border border-slate-350 dark:border-white/10 bg-white dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus-within:border-tpaGreen-600 focus-within:ring-1 focus-within:ring-tpaGreen-600 focus:outline-none cursor-pointer select-none transition-colors duration-200';
        
        // Label/Value inside button
        const btnText = document.createElement('span');
        btnText.className = 'truncate';
        
        // Chevron/Clear icons
        const iconContainer = document.createElement('div');
        iconContainer.className = 'flex items-center space-x-1.5 ml-2 text-slate-400';
        
        const clearBtn = document.createElement('i');
        clearBtn.className = 'fas fa-times hover:text-slate-655 text-[10px] hidden cursor-pointer';
        
        const caret = document.createElement('i');
        caret.className = 'fas fa-chevron-down text-[10px] transition-transform duration-200';
        
        // iconContainer.appendChild(clearBtn);
        iconContainer.appendChild(caret);
        btn.appendChild(btnText);
        btn.appendChild(iconContainer);
        wrapper.appendChild(btn);
        
        // Dropdown Menu Container
        const menu = document.createElement('div');
        menu.className = 'absolute left-0 right-0 mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/5 rounded-lg shadow-xl z-50 flex flex-col hidden transition-colors duration-200';
        menu.style.maxHeight = '280px';
        
        // Search Input
        const searchBox = document.createElement('div');
        searchBox.className = 'p-2 border-b border-slate-100 dark:border-white/5 flex-shrink-0';
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search...';
        searchInput.className = 'w-full rounded-md border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 placeholder-slate-400 dark:placeholder-slate-500 text-xs py-1.5 px-2.5 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200';
        searchBox.appendChild(searchInput);
        menu.appendChild(searchBox);
        
        // Options List Wrapper
        const optionsList = document.createElement('div');
        optionsList.className = 'overflow-y-auto flex-1 max-h-48 py-1';
        menu.appendChild(optionsList);
        wrapper.appendChild(menu);
        
        // Insert wrapper next to original select
        select.parentNode.insertBefore(wrapper, select);
        select.classList.add('hidden'); // hide original select
        
        // Populate options list
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
                    if (opt.value !== '') {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                }
                
                optItem.addEventListener('click', () => {
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change'));
                    
                    // Update display
                    btnText.textContent = opt.text;
                    if (opt.value !== '') {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                    
                    closeDropdown();
                });
                
                optionsList.appendChild(optItem);
            });
        }
        
        populateOptions();
        select.updateCustomUI = populateOptions;
        
        // Dropdown Toggle
        function openDropdown() {
            // Close other dropdowns first
            document.querySelectorAll('.searchable-select-menu').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('.searchable-select-caret').forEach(c => c.classList.remove('rotate-180'));
            
            menu.classList.remove('hidden');
            caret.classList.add('rotate-180');
            searchInput.value = '';
            filterOptions('');
            setTimeout(() => searchInput.focus(), 50);
        }
        
        function closeDropdown() {
            menu.classList.add('hidden');
            caret.classList.remove('rotate-180');
        }
        
                // Add identification classes for closing other dropdowns
        menu.classList.add('searchable-select-menu');
        caret.classList.add('searchable-select-caret');
        
        btn.addEventListener('click', (e) => {
            if (e.target === clearBtn) {
                e.stopPropagation();
                select.value = '';
                select.dispatchEvent(new Event('change'));
                btnText.textContent = select.options[0].text;
                clearBtn.classList.add('hidden');
                populateOptions();
                closeDropdown();
                return;
            }
            if (menu.classList.contains('hidden')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });
        
        // Search filter logic
        function filterOptions(term) {
            const items = optionsList.querySelectorAll('div');
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(term.toLowerCase())) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }
        
        searchInput.addEventListener('input', (e) => {
            filterOptions(e.target.value);
        });
        
        // Close when clicking outside
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                closeDropdown();
            }
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
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
});
</script>

@if($reports->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Bar Chart
    const labels = @json($chartData->pluck('id_aset'));
    const fuelData = @json($chartData->pluck('actual_fuel'));
    
    const barCtx = document.getElementById('fuelReportChart').getContext('2d');
    const gradient = barCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(240, 123, 35, 0.85)'); // TPA Orange 500
    gradient.addColorStop(1, 'rgba(251, 222, 200, 0.35)'); // TPA Orange 100

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __('Solar (L)') }}',
                data: fuelData,
                backgroundColor: gradient,
                borderColor: '#F07B23', // TPA Orange 500
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.parsed.y.toLocaleString()} L`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    title: { display: true, text: '{{ __('Volume (L)') }}', font: { weight: 'bold' } }
                }
            }
        }
    });

    // Doughnut Chart
    const groupLabels = @json($groupChartData->pluck('group_aset'));
    const groupData = @json($groupChartData->pluck('actual_fuel'));

    const areaLabels = @json($areaChartData->pluck('area'));
    const areaData = @json($areaChartData->pluck('actual_fuel'));

    const palette = [
        '#1C683E', // TPA Green 700
        '#568D49', // TPA Green 500
        '#AAC6A3', // TPA Green 200
        '#F07B23', // TPA Orange 500
        '#F69E20', // TPA Orange 600
        '#FFC112', // TPA Orange 700
        '#606B71', // TPA Neutral 600
        '#C6C6C6', // TPA Neutral 300
        '#00553A', // TPA Green 800
        '#EF7A22'  // TPA Orange 400
    ];

    const doughnutCtx = document.getElementById('fuelDistributionChart').getContext('2d');
    const distributionChart = new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: groupLabels,
            datasets: [{
                data: groupData,
                backgroundColor: palette,
                borderColor: '#ffffff',
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
                        font: { size: 11 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                            return ` ${val.toLocaleString()} L (${pct}%)`;
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });

    // Toggle Handler
    const groupBtn = document.getElementById('toggleDoughnutGroup');
    const areaBtn = document.getElementById('toggleDoughnutArea');

    if (groupBtn && areaBtn) {
        const setActive = (active, inactive) => {
            active.className = 'px-2 py-1 rounded-md bg-white text-slate-800 shadow-sm border border-slate-250 transition-all focus:outline-none';
            inactive.className = 'px-2 py-1 rounded-md text-slate-500 hover:text-slate-800 transition-all focus:outline-none ml-0.5';
        };

        groupBtn.addEventListener('click', () => {
            setActive(groupBtn, areaBtn);
            distributionChart.data.labels = groupLabels;
            distributionChart.data.datasets[0].data = groupData;
            distributionChart.update();
        });

        areaBtn.addEventListener('click', () => {
            setActive(areaBtn, groupBtn);
            distributionChart.data.labels = areaLabels;
            distributionChart.data.datasets[0].data = areaData;
            distributionChart.update();
        });
    }
});
</script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    const dependentFilters = document.querySelectorAll('.dependent-filter');
    
    dependentFilters.forEach(filter => {
        filter.addEventListener('change', async function(e) {
            let params = new URLSearchParams();
            dependentFilters.forEach(f => {
                if (f.value && f.value !== 'ALL') {
                    params.append(f.name, f.value);
                }
            });
            // Sertakan bulan_dari, bulan_sampai, tahun (bukan dependent-filter)
            ['bulan_dari', 'bulan_sampai', 'tahun'].forEach(name => {
                const el = document.querySelector(`select[name="${name}"]`);
                if (el && el.value && el.value !== 'ALL') params.append(name, el.value);
            });
            
            // Tell the backend we are requesting filter options for the fuel report
            params.append('type', 'fuel');

            try {
                let response = await fetch(`/api/monitoring/filter-options?${params.toString()}`);
                if (!response.ok) throw new Error('Network response was not ok');
                let data = await response.json();
                
                // Conflict resolution: If combination yields 0 units, prioritize the newly changed filter
                if (data.filterUnits && data.filterUnits.length === 0 && e.target.value && e.target.value !== 'ALL') {
                    params = new URLSearchParams();
                    params.append(e.target.name, e.target.value);
                    params.append('type', 'fuel');
                    
                    // Clear other filters visually
                    dependentFilters.forEach(f => {
                        if (f !== e.target && f.name !== 'bulan_dari' && f.name !== 'bulan_sampai' && f.name !== 'tahun') {
                            f.value = 'ALL';
                            if (typeof f.updateCustomUI === 'function') f.updateCustomUI();
                        }
                    });

                    // Keep dates if present
                    const bulanDari   = document.querySelector('select[name="bulan_dari"]');
                    const bulanSampai = document.querySelector('select[name="bulan_sampai"]');
                    const tahun = document.querySelector('select[name="tahun"]');
                    if (bulanDari   && bulanDari.value   && bulanDari.value   !== 'ALL') params.append('bulan_dari',   bulanDari.value);
                    if (bulanSampai && bulanSampai.value && bulanSampai.value !== 'ALL') params.append('bulan_sampai', bulanSampai.value);
                    if (tahun && tahun.value && tahun.value !== 'ALL') params.append('tahun', tahun.value);

                    response = await fetch(`/api/monitoring/filter-options?${params.toString()}`);
                    data = await response.json();
                }
                
                updateFilterOptions('filter_id_aset', data.filterUnits, '{{ __('Semua Aset') }}');
                updateFilterOptions('filter_group_aset', data.filterGroups, '{{ __('Semua Grup') }}');
                updateFilterOptions('filter_area', data.filterAreas, '{{ __('Semua Area') }}');
                updateFilterOptions('filter_group_internal_order', data.filterIoGroups, '{{ __('Semua IO Group') }}');
                updateFilterOptions('filter_internal_order', data.filterInternalOrders, '{{ __('Semua Internal Order') }}');
                updateFilterOptions('filter_group_desc', data.filterGroupDescs, '{{ __('Semua Group Desc') }}');
                updateFilterOptions('filter_pt', data.filterPts, '{{ __('Semua PT') }}');

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
        
        let valueStillExists = false;
        if (currentValue === 'ALL') valueStillExists = true;

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
});

    // Trend Line Charts (Solar Trend & Output Trend)
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.classList.contains('dark');
        const trendLabels = @json($trendChartData->pluck('label'));
        const trendActualFuel = @json($trendChartData->pluck('actual_fuel'));
        const trendBudgetFuel = @json($trendChartData->pluck('solar_budget'));
        const trendActualOutput = @json($trendChartData->pluck('output_actual'));
        const trendBudgetOutput = @json($trendChartData->pluck('output_budget'));

        // Chart 1: Tren Konsumsi Solar (Actual Solar vs Budget Solar)
        const trendSolarCanvas = document.getElementById('trendSolarChart');
        if (trendSolarCanvas) {
            new Chart(trendSolarCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: '{{ __('Solar Aktual (L)') }}',
                            data: trendActualFuel,
                            borderColor: '#16A34A', // Emerald 600
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            borderWidth: 2.5,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: '{{ __('Budget Solar (L)') }}',
                            data: trendBudgetFuel,
                            borderColor: '#2563EB', // Blue 600
                            backgroundColor: 'rgba(37, 99, 235, 0.05)',
                            borderWidth: 2.5,
                            borderDash: [6, 4],
                            tension: 0.3,
                            fill: false,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: isDark ? '#cbd5e1' : '#475569', font: { weight: 'bold' } } },
                        tooltip: { 
                            backgroundColor: isDark ? '#1e293b' : 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.dataset.label}: ${context.parsed.y.toLocaleString()} L`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' },
                            grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : '#e2e8f0', display: false }
                        },
                        y: { 
                            beginAtZero: true,
                            title: { display: true, text: '{{ __('Volume (L)') }}', color: isDark ? '#94a3b8' : '#64748b', font: { weight: 'bold' } },
                            ticks: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                callback: function(val) { return val.toLocaleString() + ' L'; }
                            },
                            grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : '#e2e8f0', borderDash: [4, 4] }
                        }
                    }
                }
            });
        }

        // Chart 2: Tren Output Kerja (Output Actual vs Budget Output)
        const trendOutputCanvas = document.getElementById('trendOutputChart');
        if (trendOutputCanvas) {
            new Chart(trendOutputCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [
                        {
                            label: '{{ __('Output Aktual') }}',
                            data: trendActualOutput,
                            borderColor: '#F07B23', // TPA Orange 500
                            backgroundColor: 'rgba(240, 123, 35, 0.1)',
                            borderWidth: 2.5,
                            tension: 0.3,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: '{{ __('Budget Output') }}',
                            data: trendBudgetOutput,
                            borderColor: '#8B5CF6', // Purple 500
                            backgroundColor: 'rgba(139, 92, 246, 0.05)',
                            borderWidth: 2.5,
                            borderDash: [6, 4],
                            tension: 0.3,
                            fill: false,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { color: isDark ? '#cbd5e1' : '#475569', font: { weight: 'bold' } } },
                        tooltip: { 
                            backgroundColor: isDark ? '#1e293b' : 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.dataset.label}: ${context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            ticks: { color: isDark ? '#94a3b8' : '#64748b' },
                            grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : '#e2e8f0', display: false }
                        },
                        y: { 
                            beginAtZero: true,
                            title: { display: true, text: '{{ __('Output') }} (KM/HM)', color: isDark ? '#94a3b8' : '#64748b', font: { weight: 'bold' } },
                            ticks: { 
                                color: isDark ? '#94a3b8' : '#64748b',
                                callback: function(val) { return val.toLocaleString(); }
                            },
                            grid: { color: isDark ? 'rgba(255, 255, 255, 0.05)' : '#e2e8f0', borderDash: [4, 4] }
                        }
                    }
                }
            });
        }
    });


</script>
@endsection
