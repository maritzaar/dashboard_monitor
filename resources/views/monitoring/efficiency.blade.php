@extends('layouts.app')

@section('title', 'Laporan Efisiensi Bahan Bakar')

@section('content')
<div class="space-y-6">

    {{-- ====== HEADER ====== --}}
    <div class="rounded-xl p-5 text-white shadow-md" style="background-color: #1C683D; border: 1.5px solid #0f172a; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-tpaOrange-500/20 border border-tpaOrange-500/30 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-gas-pump text-xl text-tpaOrange-400"></i>
                </div>
                <div>
                    <p class="text-xs text-tpaOrange-300 font-semibold uppercase tracking-wider">{{ __('Analisis Produktivitas & BBM') }}</p>
                    <h2 class="text-2xl font-extrabold tracking-wide">{{ __('Laporan Efisiensi Bahan Bakar') }}</h2>
                </div>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-xs text-tpaOrange-300">{{ __('Periode Laporan') }}</p>
                <p class="text-md font-bold">
                    {{ $bulan_dari == 'ALL' ? 'Jan' : substr($bulan_dari, 0, 3) }} –
                    {{ $bulan_sampai == 'ALL' ? 'Dec' : substr($bulan_sampai, 0, 3) }}
                    {{ $tahun == 'ALL' ? __('Semua Tahun') : $tahun }}
                </p>
            </div>
        </div>
    </div>

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total IO (Internal Order) --}}
        <div class="rounded-xl p-4 text-white transition-all duration-200 hover:-translate-y-0.5" style="background-color: #1C683D; border: 1.5px solid #0f172a; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);">
            <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">{{ __('Internal Order Terpantau') }}</p>
            <p class="text-2xl font-bold text-white mt-1">
                {{ number_format($stats->total_io ?? $stats->total_aset, 0) }}
                <span class="text-xs font-normal text-white/70 ml-1">{{ __('Internal Order') }}</span>
            </p>
            <p class="text-[11px] text-white/60 mt-1">{{ number_format($reports->count(), 0) }} Data ditemukan ({{ $stats->total_aset }} Unit)</p>
        </div>

        {{-- Total Solar (Actual vs Budget) --}}
        <div class="rounded-xl p-4 text-white transition-all duration-200 hover:-translate-y-0.5" style="background-color: #1C683D; border: 1.5px solid #0f172a; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);">
            <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">{{ __('Total Konsumsi Solar') }}</p>
            <p class="text-2xl font-bold text-white mt-1">
                {{ number_format($stats->total_solar_actual, 0) }}
                <span class="text-xs font-normal text-white/70 ml-1">L (Act)</span>
            </p>
            <p class="text-[11px] text-white/60 mt-1">Budget: {{ number_format($stats->total_solar_budget, 0) }} L</p>
        </div>

        {{-- Total Efisiensi BBM --}}
        <div class="rounded-xl p-4 text-white transition-all duration-200 hover:-translate-y-0.5" style="background-color: #1C683D; border: 1.5px solid #0f172a; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);">
            <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">{{ __('Total Efisiensi Solar') }}</p>
            <p class="text-2xl font-bold {{ $stats->total_efisiensi <= 0 ? 'text-cyan-300' : 'text-rose-300' }} mt-1">
                {{ ($stats->total_efisiensi > 0 ? '+' : '') . number_format($stats->total_efisiensi, 1) }}
                <span class="text-xs font-normal text-white/80 ml-1">L</span>
            </p>
            <p class="text-[11px] text-white/80 mt-1">
                @if($stats->total_efisiensi < 0)
                    <span class="text-cyan-300 font-semibold"><i class="fas fa-arrow-down mr-1"></i>Hemat {{ number_format(abs($stats->total_efisiensi), 1) }} L</span>
                @elseif($stats->total_efisiensi > 0)
                    <span class="text-rose-300 font-semibold"><i class="fas fa-arrow-up mr-1"></i>Over {{ number_format($stats->total_efisiensi, 1) }} L</span>
                @else
                    <span class="text-white/80 font-semibold">Tepat Budget</span>
                @endif
            </p>
        </div>

        {{-- Ratio Unit Status --}}
        <div class="rounded-xl p-4 text-white transition-all duration-200 hover:-translate-y-0.5" style="background-color: #1C683D; border: 1.5px solid #0f172a; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);">
            <p class="text-[10px] font-bold text-white/70 uppercase tracking-wider">{{ __('Status Efisiensi Unit') }}</p>
            <div class="flex items-center gap-3 mt-1.5">
                <div>
                    <span class="text-lg font-bold text-cyan-300">{{ $stats->count_efisien }}</span>
                    <span class="text-[10px] text-white/70 block">{{ __('Efisien') }}</span>
                </div>
                <div class="h-6 w-px bg-white/20"></div>
                <div>
                    <span class="text-lg font-bold text-rose-300">{{ $stats->count_warning }}</span>
                    <span class="text-[10px] text-white/70 block">{{ __('Boros') }}</span>
                </div>
                <div class="h-6 w-px bg-white/20"></div>
                <div>
                    <span class="text-lg font-bold text-white">{{ $stats->count_neutral }}</span>
                    <span class="text-[10px] text-white/70 block">{{ __('Sesuai Budget') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ====== CHART SECTION ====== --}}
    @if($reports->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart (Kiri - 2/3 width) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center justify-between">
                <span>
                    <i class="fas fa-chart-bar text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> 
                    @if($isSingleUnit)
                        {{ __('Efisiensi per Bulan - ') }} <span class="text-tpaGreen-600 dark:text-tpaGreen-400 font-bold font-mono">{{ $singleUnitName }}</span>
                    @else
                        {{ __('Efisiensi Solar per Unit (Liter)') }}
                    @endif
                </span>
                <span class="text-[10px] font-normal text-slate-400 lowercase">(hijau = hemat, merah = boros)</span>
            </h3>
            <div class="overflow-x-auto pb-3 custom-chart-scroll" style="scrollbar-width: thin; scrollbar-color: #F07B23 #f1f5f9;">
                <div id="barChartWrapper" class="relative h-72 sm:h-96 min-w-full">
                    <canvas id="efficiencyReportChart"></canvas>
                </div>
            </div>
            <style>
                .custom-chart-scroll::-webkit-scrollbar {
                    height: 8px;
                }
                .custom-chart-scroll::-webkit-scrollbar-track {
                    background: #f1f5f9;
                    border-radius: 4px;
                }
                .custom-chart-scroll::-webkit-scrollbar-thumb {
                    background: #F07B23;
                    border-radius: 4px;
                }
                .custom-chart-scroll::-webkit-scrollbar-thumb:hover {
                    background: #d9630e;
                }
            </style>
        </div>

        <!-- Doughnut Chart (Kanan - 1/3 width) -->
        <div class="lg:col-span-1 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm flex flex-col transition-colors duration-200">
            <h3 class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-pie text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> {{ __('Proporsi Status Efisiensi') }}
            </h3>
            <div class="relative h-72 sm:h-96 flex-1 flex items-center justify-center">
                <canvas id="efficiencyDistributionChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== FUEL TANK PER UNIT VISUALIZATION ====== --}}
    @if($reports->isNotEmpty() && $chartData->isNotEmpty())
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 sm:p-5 shadow-sm transition-colors duration-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-2 border-b border-slate-100 dark:border-white/5">
            <div>
                <h3 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center flex-wrap gap-2">
                    <span class="flex items-center">
                        <i class="fas fa-gas-pump text-tpaGreen-600 dark:text-tpaGreen-400 mr-2"></i>
                        {{ $chartTitle ?? __('Utilisasi Anggaran Solar (Cylinder Tank Bar)') }}
                    </span>
                    @if(isset($chartMode))
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold normal-case
                            @if($chartMode === 'month') bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                            @elseif($chartMode === 'io') bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300
                            @elseif($chartMode === 'category') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                            @else bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 @endif">
                            {{ $chartMode === 'month' ? 'Level: Tren Bulanan' : ($chartMode === 'io' ? 'Level: Per Nomor IO' : ($chartMode === 'category' ? 'Level: Jenis Kendaraan' : 'Level: Unit Aset')) }}
                        </span>
                    @endif
                </h3>
            </div>

            <!-- Legend Status -->
            <div class="flex items-center gap-3 text-[11px]">
                <span class="flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#1C683D]"></span>
                    <span>&le; 85% (Hemat)</span>
                </span>
                <span class="flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#D97706]"></span>
                    <span>86 - 100% (Waspada)</span>
                </span>
                <span class="flex items-center gap-1.5 font-medium text-slate-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#E11D48]"></span>
                    <span>&gt; 100% (Over)</span>
                </span>
            </div>
        </div>

        <div class="overflow-x-auto pb-4 pt-2 custom-chart-scroll" style="scrollbar-width: thin; scrollbar-color: #1C683D #f1f5f9;">
            <div class="flex items-end gap-5 sm:gap-6 min-w-full px-2" style="height: 240px; width: max-content;">
                
                {{-- Ruler Ticks --}}
                <div class="flex flex-col justify-between text-[9px] font-bold text-slate-400 select-none text-right pr-2 sticky left-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm z-20" style="height: 160px; margin-bottom: 42px;">
                    <span>100% -</span>
                    <span>75% -</span>
                    <span>50% -</span>
                    <span>25% -</span>
                    <span>0% -</span>
                </div>

                {{-- Cylinder Bars per Item --}}
                @foreach($chartData as $item)
                @php
                    $pct = $item->percent ?? 0;
                    $clampedHeight = min(100, max(0, $pct));
                    $isOver = $pct > 100;
                    $isWarning = $pct > 85 && $pct <= 100;
                    
                    if ($isOver) {
                        $grad = 'linear-gradient(180deg, #FB7185 0%, #E11D48 100%)';
                        $textCol = 'text-rose-600 dark:text-rose-400';
                        $borderCol = 'border-rose-300 dark:border-rose-800';
                    } elseif ($isWarning) {
                        $grad = 'linear-gradient(180deg, #FBBF24 0%, #D97706 100%)';
                        $textCol = 'text-amber-600 dark:text-amber-400';
                        $borderCol = 'border-amber-300 dark:border-amber-800';
                    } else {
                        $grad = 'linear-gradient(180deg, #34D399 0%, #1C683D 100%)';
                        $textCol = 'text-emerald-600 dark:text-emerald-400';
                        $borderCol = 'border-slate-300 dark:border-slate-700';
                    }
                @endphp
                <div class="flex flex-col items-center flex-shrink-0 group cursor-pointer" 
                     title="{{ __('Item') }}: {{ $item->label }}{{ !empty($item->sub_label) ? ' (' . $item->sub_label . ')' : '' }}&#10;{{ __('Aktual') }}: {{ number_format($item->actual_fuel, 1) }} L&#10;{{ __('Budget') }}: {{ number_format($item->solar_budget, 1) }} L&#10;{{ __('Utilisasi') }}: {{ number_format($pct, 1) }}%"
                     style="width: 82px;">
                    
                    {{-- Percentage Tag on top --}}
                    <span class="text-[11px] font-bold {{ $textCol }} mb-1.5 group-hover:scale-110 transition-transform font-mono">
                        {{ number_format($pct, 1) }}%
                    </span>

                    {{-- 3D Cylinder Tank Body --}}
                    <div class="relative w-12 h-40 rounded-2xl border-2 {{ $borderCol }} bg-slate-100 dark:bg-slate-800/80 shadow-inner overflow-hidden flex flex-col justify-end p-0.5 backdrop-blur-sm group-hover:shadow-md transition-shadow">
                        {{-- Glass shine reflection --}}
                        <div class="absolute inset-y-0 left-0.5 w-1 bg-white/30 rounded-full z-20 pointer-events-none"></div>
                        <div class="absolute inset-y-0 right-0.5 w-0.5 bg-black/10 rounded-full z-20 pointer-events-none"></div>

                        {{-- Liquid Fill Level --}}
                        <div class="w-full rounded-xl transition-all duration-700 ease-out relative z-10 shadow-sm flex items-center justify-center overflow-hidden" 
                             style="height: {{ $clampedHeight }}%; background: {{ $grad }};">
                            <div class="absolute top-0 inset-x-0 h-1 bg-white/40 rounded-full"></div>
                        </div>
                    </div>

                    {{-- Labels below tank --}}
                    <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 mt-2 truncate w-full text-center group-hover:text-tpaGreen-600 transition-colors" title="{{ $item->label }}">
                        {{ $item->label }}
                    </span>
                    @if(!empty($item->sub_label))
                    <span class="text-[10px] text-slate-400 truncate w-full text-center" title="{{ $item->sub_label }}">
                        {{ $item->sub_label }}
                    </span>
                    @endif
                </div>
                @endforeach

            </div>
        </div>
    </div>
    @endif

    {{-- ====== FILTER BAR ====== --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 p-4 shadow-sm no-print transition-colors duration-200">
        <form action="{{ route('monitoring.efficiency') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
                {{-- Tahun --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Tahun') }}</label>
                    <select name="tahun" class="searchable-select w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
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
                    <select name="bulan_dari" class="searchable-select w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $bulan_dari == 'ALL' ? 'selected' : '' }}>{{ __('Semua Bulan') }}</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $bulan_dari == $m ? 'selected' : '' }}>{{ __($m) }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Bulan Sampai --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">{{ __('Bulan Akhir') }}</label>
                    <select name="bulan_sampai" class="searchable-select w-full rounded-lg border border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-[#0B1120] text-slate-700 dark:text-slate-200 text-sm py-2 px-3 focus:border-tpaGreen-600 focus:outline-none transition-colors duration-200">
                        <option value="ALL" {{ $bulan_sampai == 'ALL' ? 'selected' : '' }}>{{ __('Semua Bulan') }}</option>
                        @foreach($months as $m)
                            <option value="{{ $m }}" {{ $bulan_sampai == $m ? 'selected' : '' }}>{{ __($m) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Group Aset --}}
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
                {{-- Aset --}}
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
            <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-slate-100 dark:border-white/5">
                <a href="{{ route('monitoring.efficiency') }}" class="px-4 py-2 border border-slate-300 dark:border-white/10 hover:bg-slate-50 dark:hover:bg-white/5 text-slate-600 dark:text-slate-400 font-semibold rounded-lg text-sm transition">
                    {{ __('Reset Filter') }}
                </a>
                <button type="submit" class="bg-gradient-to-r from-tpaGreen-600 to-tpaGreen-700 hover:from-tpaGreen-700 hover:to-tpaGreen-800 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm">
                    <i class="fas fa-filter mr-2"></i> {{ __('Terapkan Filter') }}
                </button>
                <a href="{{ route('monitoring.export', array_merge(request()->all(), ['type' => 'efficiency'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm ml-2">
                    <i class="fas fa-file-excel mr-2"></i> {{ __('Unduh Excel') }}
                </a>
                <a href="{{ route('monitoring.export_pdf', array_merge(request()->all(), ['type' => 'efficiency'])) }}" target="_blank" class="bg-gradient-to-r from-tpaOrange-500 to-tpaOrange-600 hover:from-tpaOrange-600 hover:to-tpaOrange-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm ml-2">
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
                    <i class="fas fa-list-check text-tpaOrange-600 dark:text-tpaOrange-400 mr-2"></i> {{ __('Analisis Rasio & Efisiensi Bahan Bakar') }}
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
                    {{ number_format($reports->count()) }} {{ __('baris data') }}
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
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Group IO') }}</th>
                        <th class="px-3 py-3 text-center text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('KM/HM') }}</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Satuan') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Output Budget') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Output Actual') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Solar Budget (L)') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Solar Actual (L)') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Rasio Budget') }}</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">{{ __('Efisiensi (L)') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-white/5">
                    @forelse($reports as $row)
                        @php
                            $efisiensi = $row->efisiensi;
                            $isNegative = $efisiensi < 0; // Efisien (Hijau)
                            $isPositive = $efisiensi > 0; // Boros (Merah)
                        @endphp
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition">
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_aset ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->area ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->pt ?? '-' }}</td>
                        <td class="px-3 py-2.5 font-bold text-slate-700 dark:text-slate-300 font-mono text-xs">{{ $row->id_aset }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->bulan ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->tahun ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-700 dark:text-slate-300 font-mono text-xs">{{ $row->internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->group_desc ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-center text-xs">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $row->km_hm_type === 'KM' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                {{ $row->km_hm_type }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 text-slate-600 dark:text-slate-400 text-xs">{{ $row->satuan ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300">{{ number_format($row->output_budget, 1) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300">{{ number_format($row->total_kerja, 1) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300">{{ number_format($row->solar_budget, 1) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs text-slate-700 dark:text-slate-300 font-bold">{{ number_format($row->actual_fuel, 1) }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                            {{ number_format($row->rasio_budget, 1) }}
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold">
                            @if($isNegative)
                                <span class="text-emerald-600 dark:text-emerald-400 inline-flex items-center gap-0.5">
                                    <i class="fas fa-arrow-trend-down text-[10px] mr-1"></i>
                                    {{ number_format($efisiensi, 1) }}
                                </span>
                            @elseif($isPositive)
                                <span class="text-rose-600 dark:text-rose-400 inline-flex items-center gap-0.5">
                                    <i class="fas fa-arrow-trend-up text-[10px] mr-1"></i>
                                    +{{ number_format($efisiensi, 1) }}
                                </span>
                            @else
                                <span class="text-slate-500">0.0</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="16" class="px-4 py-12 text-center text-slate-400">
                            <i class="fas fa-filter-circle-xmark text-3xl block mb-2 text-slate-300"></i>
                            <span class="text-xs">{{ __('Tidak ada data operasional/transaksi solar yang cocok dengan filter aktif.') }}</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if($reports->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Bar Chart (Dinamis: Trend per Bulan jika 1 unit, atau per Unit jika Semua Aset)
    const labels = @json($chartData->pluck('label'));
    const efficiencyData = @json($chartData->pluck('efisiensi'));
    const backgroundColors = efficiencyData.map(val => val < 0 ? 'rgba(16, 185, 129, 0.85)' : 'rgba(244, 63, 94, 0.85)');
    const borderColors = efficiencyData.map(val => val < 0 ? '#059669' : '#e11d48');
    
    // Set dynamic width for horizontal scrolling if many items
    const wrapper = document.getElementById('barChartWrapper');
    if (wrapper && labels.length > 10) {
        // Alokasikan minimal 50px per bar agar label & batangnya jelas dan tidak berdempetan
        const calculatedWidth = Math.max(wrapper.parentElement.clientWidth, labels.length * 52);
        wrapper.style.width = calculatedWidth + 'px';
    }

    const barCtx = document.getElementById('efficiencyReportChart').getContext('2d');

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __('Efisiensi (L)') }}',
                data: efficiencyData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 4,
                maxBarThickness: 50
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
                            const val = context.parsed.y;
                            return ` ${val > 0 ? '+' : ''}${val.toLocaleString()} Liter (${val < 0 ? 'Hemat' : (val > 0 ? 'Boros' : 'Tepat')})`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        autoSkip: false,
                        maxRotation: 45,
                        minRotation: 45,
                        font: { size: 11 }
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    title: { display: true, text: '{{ __('Deviasi Solar (Liter)') }}', font: { weight: 'bold' } },
                    ticks: {
                        callback: function(val) {
                            return (val > 0 ? '+' : '') + val.toLocaleString() + ' L';
                        }
                    },
                    grid: {
                        color: function(context) {
                            if (context.tick && context.tick.value === 0) {
                                return '#64748b'; // highlight zero line
                            }
                            return 'rgba(0, 0, 0, 0.06)';
                        },
                        lineWidth: function(context) {
                            return context.tick && context.tick.value === 0 ? 2 : 1;
                        }
                    }
                }
            }
        }
    });

    // Doughnut Chart
    const doughnutCtx = document.getElementById('efficiencyDistributionChart').getContext('2d');
    new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: ['{{ __('Efisien / Hemat') }}', '{{ __('Boros / Over') }}', '{{ __('Sesuai Budget') }}'],
            datasets: [{
                data: [{{ $stats->count_efisien }}, {{ $stats->count_warning }}, {{ $stats->count_neutral }}],
                backgroundColor: ['#10b981', '#f43f5e', '#94a3b8'],
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
                }
            },
            cutout: '65%'
        }
    });
});
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
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
        
        const clearBtn = document.createElement('i');
        clearBtn.className = 'fas fa-times hover:text-slate-655 text-[10px] hidden cursor-pointer';
        
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
                    optItem.classList.add('bg-tpaGreen-50', 'dark:bg-[#0B1120]', 'text-tpaGreen-800', 'dark:text-tpaGreen-300', 'font-semibold');
                    btnText.textContent = opt.text;
                    if (opt.value !== '' && opt.value !== 'ALL') {
                        clearBtn.classList.remove('hidden');
                    } else {
                        clearBtn.classList.add('hidden');
                    }
                }
                
                optItem.addEventListener('click', () => {
                    select.value = opt.value;
                    select.dispatchEvent(new Event('change'));
                    
                    btnText.textContent = opt.text;
                    if (opt.value !== '' && opt.value !== 'ALL') {
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
        
        function openDropdown() {
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
            // Sertakan tahun, bulan_dari, bulan_sampai
            ['tahun', 'bulan_dari', 'bulan_sampai'].forEach(name => {
                const el = document.querySelector(`select[name="${name}"]`);
                if (el && el.value && el.value !== 'ALL') params.append(name, el.value);
            });
            params.append('type', 'efficiency');

            try {
                let response = await fetch(`/api/monitoring/filter-options?${params.toString()}`);
                if (!response.ok) throw new Error('Network response was not ok');
                let data = await response.json();
                
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

        // User preference: Do NOT automatically reset filters to "ALL" if they are no longer in the options list.
        // Instead, we force add their old selection back into the DOM so it remains selected,
        // allowing them to change their mind without losing their previous choices.
        if (!valueStillExists && currentValue) {
            const option = document.createElement('option');
            option.value = currentValue;
            option.textContent = currentValue; // Can append " (No Data)" if desired, but user just wanted it kept.
            option.selected = true;
            select.appendChild(option);
        }

        if (typeof select.updateCustomUI === 'function') {
            select.updateCustomUI();
        }
    }
});
</script>
@endsection
