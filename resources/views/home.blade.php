@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<style>
    /* Custom Micro-Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .animate-stagger {
        opacity: 0;
        animation: fadeInUp 0.6s ease-out forwards;
    }
    
    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
    .delay-300 { animation-delay: 300ms; }
    .delay-400 { animation-delay: 400ms; }
    .delay-500 { animation-delay: 500ms; }
</style>

<div class="space-y-6 pb-8">

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-2xl p-8 sm:p-10 text-white shadow-lg flex flex-col md:flex-row items-center justify-between relative overflow-hidden">
        <!-- Ambient Glowing Backdrop -->
        <div class="absolute -right-10 -top-10 w-80 h-80 bg-gradient-to-br from-tpaGreen/20 to-tpaOrange/25 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 md:w-2/3 text-center md:text-left mb-6 md:mb-0">
            <h1 class="text-3xl sm:text-4xl font-bold mb-3">Selamat Datang, {{ explode(' ', Auth::user()->name)[0] }}!</h1>
            <p class="text-slate-300 text-base max-w-xl">
                Berikut merupakan ringkasan komprehensif data operasional armada kendaraan serta konsumsi bahan bakar solar untuk seluruh unit alat berat yang terpantau.
            </p>
        </div>
        <div class="relative z-10 md:w-1/3 flex justify-center md:justify-end">
            <div class="bg-white/10 backdrop-blur-sm p-4 rounded-xl border border-white/20 text-center">
                <p class="text-xs text-slate-300 uppercase tracking-wider mb-1">Status Sistem</p>
                <div class="flex items-center space-x-2 text-emerald-400 font-bold">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    <span>Monitoring Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 3 card di home dashboard -->
        <!-- Stat 1 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex items-center space-x-4 transition hover:shadow-md hover:border-tpaGreen/30 dark:hover:border-tpaGreen/50 animate-stagger delay-100 group">
            <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-950/30 text-tpaGreen dark:text-emerald-400 flex items-center justify-center flex-shrink-0 group-hover:bg-tpaGreen group-hover:text-white transition-colors duration-300">
                <i class="fas fa-tractor text-2xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Unit Terpantau</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100"><span id="count_aset">0</span> <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Unit</span></h3>
            </div>
        </div>
        
        <!-- Stat 2 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex items-center space-x-4 transition hover:shadow-md hover:border-rose-300 dark:hover:border-rose-500/50 animate-stagger delay-200 group">
            <div class="w-14 h-14 rounded-full bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                <i class="fas fa-clock text-2xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rata-rata Waktu Idle</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100"><span id="count_idle">0</span>%</h3>
            </div>
        </div>
        
        <!-- Stat 3 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex items-center space-x-4 transition hover:shadow-md hover:border-tpaOrange/30 dark:hover:border-tpaOrange/50 animate-stagger delay-300 group">
            <div class="w-14 h-14 rounded-full bg-orange-50 dark:bg-orange-950/30 text-tpaOrange dark:text-orange-400 flex items-center justify-center flex-shrink-0 group-hover:bg-tpaOrange group-hover:text-white transition-colors duration-300">
                <i class="fas fa-gas-pump text-2xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Akumulasi Konsumsi Solar</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100"><span id="count_fuel">0</span> <span class="text-sm font-medium text-slate-500 dark:text-slate-400">L</span></h3>
            </div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 mt-8 mb-4 flex items-center">
        Akses Cepat
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Jam Kerja Summary Card --> 
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-200">
            <!-- Header Banner -->
            <div class="h-24 bg-transparent text-slate-800 dark:text-slate-100 flex items-center justify-between px-6 border-b border-slate-100 dark:border-white/5 relative overflow-hidden">
                  <div class="z-10">
                      <span class="text-[10px] font-bold uppercase tracking-wider bg-tpaGreen/10 text-tpaGreen px-2 py-0.5 rounded-full">Analitik</span>
                      <h3 class="text-base font-bold mt-1">Rekap Jam Kerja</h3>
                  </div>
                  <div class="w-12 h-12 rounded-full bg-tpaGreen/10 flex items-center justify-center z-10">
                      <i class="fas fa-clock text-2xl text-tpaGreen"></i>
                  </div>
              </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <!-- Metrics -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Waktu Kerja</span>
                        <span class="text-base font-black text-slate-800 dark:text-slate-100">{{ number_format($totalKerja, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">Jam</span></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Rata-rata Idle</span>
                        <span class="text-base font-black text-rose-600 dark:text-rose-400">{{ number_format($avgIdle, 1, ',', '.') }}%</span>
                    </div>
                </div>

                <!-- Progress Bar visualizer -->
                <div class="mb-5">
                    <div class="flex justify-between items-center text-xs mb-1.5 font-semibold">
                        <span class="text-slate-500 flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-tpaGreen mr-1.5"></span>Kerja: {{ number_format(100 - $avgIdle, 1, ',', '.') }}%</span>
                        <span class="text-slate-500 flex items-center"><span class="w-2.5 h-2.5 rounded-full bg-rose-500 mr-1.5"></span>Idle: {{ number_format($avgIdle, 1, ',', '.') }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 flex overflow-hidden shadow-inner">
                        <div class="bg-tpaGreen h-full rounded-l-full" style="width: {{ 100 - $avgIdle }}%"></div>
                        <div class="bg-rose-500 h-full rounded-r-full" style="width: {{ $avgIdle }}%"></div>
                    </div>
                </div>

                <!-- Card Button -->
                <a href="{{ route('monitoring.working_hour') }}" class="mt-auto w-full bg-tpaGreen hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition shadow-sm text-sm active:scale-95 flex items-center justify-center gap-1.5">
                    <span>Laporan Lengkap</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        
        <!-- Konsumsi Solar Summary Card -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-300">
            <!-- Header Banner -->
            <div class="h-24 bg-transparent text-slate-800 dark:text-slate-100 flex items-center justify-between px-6 border-b border-slate-100 dark:border-white/5 relative overflow-hidden">
                  <div class="z-10">
                      <span class="text-[10px] font-bold uppercase tracking-wider bg-tpaOrange/10 text-tpaOrange px-2 py-0.5 rounded-full">Analitik</span>
                      <h3 class="text-base font-bold mt-1">Rekap Konsumsi Solar</h3>
                  </div>
                  <div class="w-12 h-12 rounded-full bg-tpaOrange/10 flex items-center justify-center z-10">
                      <i class="fas fa-gas-pump text-2xl text-tpaOrange"></i>
                  </div>
              </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <!-- Metrics -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Pemakaian</span>
                        <span class="text-base font-black text-slate-800 dark:text-slate-100">{{ number_format($totalFuel, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">L</span></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Rerata / Unit</span>
                        <span class="text-base font-black text-tpaOrange dark:text-orange-400">{{ number_format($totalFuel / max($totalAset, 1), 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">L</span></span>
                    </div>
                </div>

                <!-- Descriptive list summary -->
                <div class="space-y-2 mb-5">
                    <div class="flex items-center justify-between text-xs border-b border-slate-100 dark:border-white/5 pb-1.5">
                        <span class="text-slate-500 dark:text-slate-400">Total Unit Dipantau</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $totalAset }} Unit</span>
                    </div>
                    <div class="flex items-center justify-between text-xs pb-0.5">
                        <span class="text-slate-500 dark:text-slate-400">Status Data Dispenser</span>
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-md">Terintegrasi</span>
                    </div>
                </div>

                <!-- Card Button -->
                <a href="{{ route('monitoring.fuel') }}" class="mt-auto w-full bg-tpaOrange hover:bg-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition shadow-sm text-sm active:scale-95 flex items-center justify-center gap-1.5">
                    <span>Laporan Lengkap</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        
        <!-- Efisiensi BBM Summary Card -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-400">
            <!-- Header Banner -->
            <div class="h-24 bg-transparent text-slate-800 dark:text-slate-100 flex items-center justify-between px-6 border-b border-slate-100 dark:border-white/5 relative overflow-hidden">
                  <div class="z-10">
                      <span class="text-[10px] font-bold uppercase tracking-wider bg-tpaGreen/10 text-tpaGreen px-2 py-0.5 rounded-full">Analitik</span>
                      <h3 class="text-base font-bold mt-1">Efisiensi Bahan Bakar</h3>
                  </div>
                  <div class="w-12 h-12 rounded-full bg-tpaGreen/10 flex items-center justify-center z-10">
                      <i class="fas fa-tachometer-alt text-2xl text-tpaGreen"></i>
                  </div>
              </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <!-- Metrics -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block" title="Alat Berat">Rasio AB</span>
                        <span class="text-base font-black text-slate-800 dark:text-slate-100">{{ number_format($avgEffAB ?? 0, 2, ',', '.') }} <span class="text-xs font-semibold text-slate-500">L/Jam</span></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block" title="Kendaraan">Rasio Kendaraan</span>
                        <span class="text-base font-black text-indigo-600 dark:text-indigo-400">{{ number_format($avgEffKen ?? 0, 2, ',', '.') }} <span class="text-xs font-semibold text-slate-500">KM/L</span></span>
                    </div>
                </div>

                <!-- Descriptive summary -->
                <div class="space-y-2 mb-5">
                    <div class="flex items-center justify-between text-xs border-b border-slate-100 dark:border-white/5 pb-1.5">
                        <span class="text-slate-500 dark:text-slate-400">Total Jam Kerja</span>
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ number_format($totalKerja, 0, ',', '.') }} Jam</span>
                    </div>
                    <div class="flex items-center justify-between text-xs pb-0.5">
                        <span class="text-slate-500 dark:text-slate-400">Rasio Produktivitas</span>
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-tpaGreen dark:text-emerald-400 rounded-md">Analisis Terintegrasi</span>
                    </div>
                </div>

                <!-- Card Button -->
                <a href="{{ route('monitoring.efficiency') }}" class="mt-auto w-full bg-tpaGreen hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition shadow-sm text-sm active:scale-95 flex items-center justify-center gap-1.5">
                    <span>Analisis Efisiensi</span>
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
        
        <!-- Alur Sistem Card (Links style) -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-400">
            <!-- Header Banner -->
            <div class="h-24 bg-transparent text-slate-800 dark:text-slate-100 flex items-center justify-between px-6 border-b border-slate-100 dark:border-white/5 relative overflow-hidden">
                  <div class="z-10">
                      <span class="text-[10px] font-bold uppercase tracking-wider bg-tpaOrange/10 text-tpaOrange px-2 py-0.5 rounded-full">Integrasi</span>
                      <h3 class="text-base font-bold mt-1">Status Alur Data</h3>
                  </div>
                  <div class="w-12 h-12 rounded-full bg-tpaOrange/10 flex items-center justify-center z-10">
                      <i class="fas fa-project-diagram text-2xl text-tpaOrange"></i>
                  </div>
              </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <!-- Status List -->
                <div class="space-y-2 mb-5 flex-grow">
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-white/5">
                        <span class="text-slate-600 dark:text-slate-400 font-semibold flex items-center"><i class="fas fa-satellite-dish text-tpaGreen-500 mr-2"></i>GPS AGI Ingestion</span>
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-md">Online</span>
                    </div>
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-white/5">
                        <span class="text-slate-600 dark:text-slate-400 font-semibold flex items-center"><i class="fas fa-globe text-amber-500 mr-2"></i>Caterpillar Telemetry</span>
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-md">Online</span>
                    </div>
                    <div class="flex items-center justify-between text-xs p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-white/5">
                        <span class="text-slate-600 dark:text-slate-400 font-semibold flex items-center"><i class="fas fa-network-wired text-tpaOrange-500 mr-2"></i>SAP Integration</span>
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-md">Connected</span>
                    </div>
                </div>

                <!-- Card Links -->
                <div class="pt-3 border-t border-slate-100 dark:border-white/5 mt-auto">
                    <a href="{{ route('monitoring.flow') }}" class="text-tpaGreen dark:text-emerald-400 hover:text-emerald-700 hover:underline text-sm font-semibold transition flex items-center justify-center gap-1.5">
                        <span>Lihat Aliran Integrasi</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Insight Kinerja Widget Filter & Tabs -->
        <div class="lg:col-span-4 flex flex-col sm:flex-row justify-between items-start sm:items-center mt-4 mb-3 animate-stagger delay-500 gap-3">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <h2 class="text-xl font-bold text-slate-800 dark:text-white">Peringkat Efisiensi</h2>
                
                <!-- Tabs -->
                <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-lg">
                    <button type="button" id="tab-btn-ab" onclick="switchDashboardTab('ab')" class="px-4 py-1.5 text-sm font-bold rounded-md bg-white dark:bg-slate-700 text-tpaGreen dark:text-emerald-400 shadow-sm transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-tractor"></i> Alat Berat
                    </button>
                    <button type="button" id="tab-btn-ken" onclick="switchDashboardTab('ken')" class="px-4 py-1.5 text-sm font-semibold rounded-md text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 flex items-center gap-2">
                        <i class="fas fa-truck-pickup"></i> Kendaraan
                    </button>
                </div>
            </div>
            
            <form method="GET" action="{{ route('home') }}" class="flex items-center space-x-2">
                <select name="bulan" class="text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-lg px-3 py-1.5 focus:ring-tpaGreen focus:border-tpaGreen dark:text-slate-200" onchange="this.form.submit()">
                    <option value="ALL" {{ $bulan == 'ALL' ? 'selected' : '' }}>Seluruh Bulan</option>
                    @foreach($availableMonths as $m)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <select name="tahun" class="text-xs font-semibold bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 rounded-lg px-3 py-1.5 focus:ring-tpaGreen focus:border-tpaGreen dark:text-slate-200" onchange="this.form.submit()">
                    <option value="ALL" {{ $tahun == 'ALL' ? 'selected' : '' }}>Seluruh Tahun</option>
                    @foreach($availableYears as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        
        <!-- Insight Kinerja Widget Alat Berat -->
        <div id="tab-content-ab" class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-5 mb-2 animate-stagger delay-500 block">
            <!-- Top 5 Alat Berat Paling Efisien -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-tpaGreen/30 dark:border-emerald-500/20 shadow-sm overflow-hidden flex flex-col">
                <div class="h-12 bg-tpaGreen/5 dark:bg-emerald-900/30 flex items-center justify-between px-5 border-b border-tpaGreen/10 dark:border-emerald-500/10">
                    <h3 class="font-bold text-tpaGreen dark:text-emerald-400 flex items-center">
                        <i class="fas fa-trophy mr-2 text-tpaGreen dark:text-emerald-400"></i> Top 5 Alat Berat Paling Efisien
                    </h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-2 font-bold">Unit</th>
                                <th class="px-4 py-2 font-bold text-right">Rasio (L/Jam)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($topAB as $index => $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-2.5 flex items-center">
                                    <span class="w-5 h-5 rounded-full bg-tpaGreen/10 dark:bg-emerald-900/50 text-tpaGreen dark:text-emerald-400 text-[10px] flex items-center justify-center font-bold mr-2">{{ $index + 1 }}</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $item->id_aset }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-tpaGreen dark:text-emerald-400">
                                    {{ number_format($item->efficiency, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-4 text-center text-xs text-slate-400">Data tidak tersedia bulan ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top 5 Alat Berat Paling Boros -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-tpaOrange/30 dark:border-rose-500/20 shadow-sm overflow-hidden flex flex-col">
                <div class="h-12 bg-tpaOrange/5 dark:bg-rose-900/30 flex items-center justify-between px-5 border-b border-tpaOrange/10 dark:border-rose-500/10">
                    <h3 class="font-bold text-tpaOrange dark:text-rose-400 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-tpaOrange dark:text-rose-400"></i> Top 5 Alat Berat Paling Boros
                    </h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-2 font-bold">Unit</th>
                                <th class="px-4 py-2 font-bold text-right">Rasio (L/Jam)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($bottomAB as $index => $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-2.5 flex items-center">
                                    <span class="w-5 h-5 rounded-full bg-tpaOrange/10 dark:bg-rose-900/50 text-tpaOrange dark:text-rose-400 text-[10px] flex items-center justify-center font-bold mr-2">{{ $index + 1 }}</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $item->id_aset }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-tpaOrange dark:text-rose-400">
                                    {{ number_format($item->efficiency, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-4 text-center text-xs text-slate-400">Data tidak tersedia bulan ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Insight Kinerja Widget Kendaraan -->
        <div id="tab-content-ken" class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-5 mb-2 animate-stagger delay-500 hidden">
            <!-- Top 5 Kendaraan Paling Efisien -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-indigo-500/30 shadow-sm overflow-hidden flex flex-col">
                <div class="h-12 bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-between px-5 border-b border-indigo-100 dark:border-indigo-500/10">
                    <h3 class="font-bold text-indigo-600 dark:text-indigo-400 flex items-center">
                        <i class="fas fa-trophy mr-2 text-indigo-600 dark:text-indigo-400"></i> Top 5 Kendaraan Paling Efisien
                    </h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-2 font-bold">Unit</th>
                                <th class="px-4 py-2 font-bold text-right">Rasio (KM/L)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($topKen as $index => $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-2.5 flex items-center">
                                    <span class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 text-[10px] flex items-center justify-center font-bold mr-2">{{ $index + 1 }}</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $item->id_aset }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($item->efficiency, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-4 text-center text-xs text-slate-400">Data tidak tersedia bulan ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top 5 Kendaraan Paling Boros -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-rose-500/30 shadow-sm overflow-hidden flex flex-col">
                <div class="h-12 bg-rose-50 dark:bg-rose-900/30 flex items-center justify-between px-5 border-b border-rose-100 dark:border-rose-500/10">
                    <h3 class="font-bold text-rose-600 dark:text-rose-400 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-rose-600 dark:text-rose-400"></i> Top 5 Kendaraan Paling Boros
                    </h3>
                </div>
                <div class="p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-2 font-bold">Unit</th>
                                <th class="px-4 py-2 font-bold text-right">Rasio (KM/L)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-white/5">
                            @forelse($bottomKen as $index => $item)
                            <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-2.5 flex items-center">
                                    <span class="w-5 h-5 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 text-[10px] flex items-center justify-center font-bold mr-2">{{ $index + 1 }}</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $item->id_aset }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-bold text-rose-600 dark:text-rose-400">
                                    {{ number_format($item->efficiency, 2, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="px-4 py-4 text-center text-xs text-slate-400">Data tidak tersedia bulan ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Admin Card -->
        @if(Auth::user()->role === 'admin')
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-800/20 transition-all duration-300 animate-stagger delay-500 lg:col-span-4">
            <div class="p-5 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-slate-300 flex items-center justify-center text-xl flex-shrink-0 shadow">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Panel Administrasi Sistem</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Unggah data telemetry mentah atau kelola izin akses akun pengguna.</p>
                    </div>
                </div>
                <div class="flex gap-2.5 w-full md:w-auto">
                    <a href="{{ route('import.index') }}" class="flex-1 md:flex-initial bg-white hover:bg-slate-200 text-slate-900 font-bold py-2 px-4 rounded-lg text-center transition text-xs shadow-sm active:scale-95">
                        <i class="fas fa-upload mr-1.5"></i>Impor Data
                    </a>
                    <a href="{{ route('users.index') }}" class="flex-1 md:flex-initial bg-slate-700 hover:bg-slate-600 text-white border border-slate-600 font-semibold py-2 px-4 rounded-lg text-center transition text-xs shadow-sm active:scale-95">
                        <i class="fas fa-users-cog mr-1.5"></i>Kelola Pengguna
                    </a>
                </div>
            </div>
        </div>
        @endif
        
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Number Counter Animation Function
        const animateNumbers = (element, finalValue, duration, isPercentage = false) => {
            let startTime = null;
            const step = (timestamp) => {
                if (!startTime) startTime = timestamp;
                const progress = Math.min((timestamp - startTime) / duration, 1);
                
                // Use easeOutQuart for a smoother slow-down effect
                const easeProgress = 1 - Math.pow(1 - progress, 4);
                
                const currentValue = easeProgress * finalValue;
                
                if (isPercentage) {
                    // Format with 1 decimal for percentage
                    element.innerText = currentValue.toFixed(1).replace('.', ',');
                } else {
                    // Format with dot separators for whole numbers
                    element.innerText = Math.round(currentValue).toLocaleString('id-ID');
                }
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        };

        // Triger animations for each start card
        const totalAsetElem = document.getElementById('count_aset');
        const avgIdleElem = document.getElementById('count_idle');
        const totalFuelElem = document.getElementById('count_fuel');

        if (totalAsetElem) animateNumbers(totalAsetElem, {{ $totalAset }}, 1500);
        if (avgIdleElem) animateNumbers(avgIdleElem, {{ $avgIdle }}, 1500, true);
        if (totalFuelElem) animateNumbers(totalFuelElem, {{ $totalFuel }}, 2000);
    });
        window.switchDashboardTab = function(tabName) {
            const btnAB = document.getElementById('tab-btn-ab');
            const btnKen = document.getElementById('tab-btn-ken');
            const contentAB = document.getElementById('tab-content-ab');
            const contentKen = document.getElementById('tab-content-ken');
            
            if (tabName === 'ab') {
                // Update buttons
                btnAB.className = "px-4 py-1.5 text-sm font-bold rounded-md bg-white dark:bg-slate-700 text-tpaGreen dark:text-emerald-400 shadow-sm transition-all duration-200 flex items-center gap-2";
                btnKen.className = "px-4 py-1.5 text-sm font-semibold rounded-md text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-200 flex items-center gap-2";
                
                // Show/Hide content
                contentAB.classList.remove('hidden');
                contentAB.classList.add('block');
                contentKen.classList.add('hidden');
                contentKen.classList.remove('block');
            } else {
                // Update buttons
                btnKen.className = "px-4 py-1.5 text-sm font-bold rounded-md bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm transition-all duration-200 flex items-center gap-2";
                btnAB.className = "px-4 py-1.5 text-sm font-semibold rounded-md text-slate-500 dark:text-slate-400 hover:text-tpaGreen dark:hover:text-emerald-400 transition-all duration-200 flex items-center gap-2";
                
                // Show/Hide content
                contentKen.classList.remove('hidden');
                contentKen.classList.add('block');
                contentAB.classList.add('hidden');
                contentAB.classList.remove('block');
            }
        };
    </script>
@endsection 
 