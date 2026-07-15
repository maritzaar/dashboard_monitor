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
        <div class="absolute right-0 top-0 opacity-10 pointer-events-none">
            <i class="fas fa-chart-line text-[15rem] -mr-10 -mt-10"></i>
        </div>
        
        <div class="relative z-10 md:w-2/3 text-center md:text-left mb-6 md:mb-0">
            <h1 class="text-3xl sm:text-4xl font-bold font-serif mb-3">Selamat Datang, {{ explode(' ', Auth::user()->name)[0] }}!</h1>
            <p class="text-slate-300 text-base max-w-xl">
                Ini adalah ringkasan data operasional dan penggunaan solar keseluruhan alat berat dari seluruh periode yang terekam. 
                Pilih menu akses cepat di bawah untuk melihat laporan secara detail.
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
        <!-- Stat 1 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex items-center space-x-4 transition hover:shadow-md hover:border-blue-200 dark:hover:border-blue-500/50 animate-stagger delay-100 group">
            <div class="w-14 h-14 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300">
                <i class="fas fa-tractor text-2xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Aset</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100"><span id="count_aset">0</span> <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Unit</span></h3>
            </div>
        </div>
        
        <!-- Stat 2 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex items-center space-x-4 transition hover:shadow-md hover:border-rose-200 dark:hover:border-rose-500/50 animate-stagger delay-200 group">
            <div class="w-14 h-14 rounded-full bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors duration-300">
                <i class="fas fa-clock text-2xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rata-rata Idle</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100"><span id="count_idle">0</span>%</h3>
            </div>
        </div>
        
        <!-- Stat 3 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl p-6 border border-slate-200 dark:border-white/5 shadow-sm flex items-center space-x-4 transition hover:shadow-md hover:border-amber-200 dark:hover:border-amber-500/50 animate-stagger delay-300 group">
            <div class="w-14 h-14 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300">
                <i class="fas fa-gas-pump text-2xl group-hover:scale-110 transition-transform"></i>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Konsumsi Solar</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-slate-100"><span id="count_fuel">0</span> <span class="text-sm font-medium text-slate-500 dark:text-slate-400">L</span></h3>
            </div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <h2 class="text-lg font-bold text-slate-800 dark:text-slate-200 mt-8 mb-4 flex items-center">
        Akses Cepat
    </h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        
        <!-- Bootstrap Inspired Card 1 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-200">
            <!-- Image Cap Replacement (Icon/Color Banner) -->
            <div class="h-28 bg-gradient-to-br from-blue-50 to-slate-100 dark:from-slate-800 dark:to-[#0B1120] flex items-center justify-center text-blue-200 dark:text-slate-700 border-b border-slate-100 dark:border-white/5 group relative overflow-hidden">
                <div class="absolute inset-0 bg-blue-600/5 dark:bg-blue-400/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <i class="fas fa-clock text-5xl drop-shadow-sm group-hover:scale-110 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-all duration-300"></i>
            </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <h3 class="text-lg font-bold font-serif text-slate-800 dark:text-slate-100 mb-1">Jam Kerja</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 flex-grow line-clamp-3">Laporan rincian waktu kerja, waktu operasi, dan idle alat berat sesuai dengan lokasi dan unit group.</p>
                <!-- Card Button -->
                <a href="{{ route('monitoring.working_hour') }}" class="w-full bg-forest dark:bg-emerald-600 hover:bg-green-700 dark:hover:bg-emerald-500 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition shadow-sm text-sm active:scale-95">
                    Buka Laporan
                </a>
            </div>
        </div>
        
        <!-- Bootstrap Inspired Card 2 -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-300">
            <!-- Image Cap Replacement -->
            <div class="h-28 bg-gradient-to-br from-emerald-50 to-slate-100 dark:from-slate-800 dark:to-[#0B1120] flex items-center justify-center text-emerald-200 dark:text-slate-700 border-b border-slate-100 dark:border-white/5 group relative overflow-hidden">
                <div class="absolute inset-0 bg-emerald-600/5 dark:bg-emerald-400/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <i class="fas fa-gas-pump text-5xl drop-shadow-sm group-hover:scale-110 group-hover:text-emerald-500 dark:group-hover:text-emerald-400 transition-all duration-300"></i>
            </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <h3 class="text-lg font-bold font-serif text-slate-800 dark:text-slate-100 mb-1">Konsumsi Solar</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-5 flex-grow line-clamp-3">Pantau pengeluaran solar bulanan dan analisis tingkat efisiensi pembakaran setiap alat berat.</p>
                <!-- Card Button -->
                <a href="{{ route('monitoring.fuel') }}" class="w-full bg-forest dark:bg-emerald-600 hover:bg-green-700 dark:hover:bg-emerald-500 text-white font-semibold py-2.5 px-4 rounded-lg text-center transition shadow-sm text-sm active:scale-95">
                    Buka Laporan
                </a>
            </div>
        </div>
        
        <!-- Bootstrap Inspired Card 3 (Links style) -->
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg transition-all duration-300 animate-stagger delay-400">
            <!-- Image Cap Replacement -->
            <div class="h-28 bg-gradient-to-br from-indigo-50 to-slate-100 dark:from-slate-800 dark:to-[#0B1120] flex items-center justify-center text-indigo-200 dark:text-slate-700 border-b border-slate-100 dark:border-white/5 group relative overflow-hidden">
                <div class="absolute inset-0 bg-indigo-600/5 dark:bg-indigo-400/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <i class="fas fa-project-diagram text-5xl drop-shadow-sm group-hover:scale-110 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-all duration-300"></i>
            </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <h3 class="text-lg font-bold font-serif text-slate-800 dark:text-slate-100 mb-1">Alur Sistem</h3>
                <p class="text-[11px] font-bold text-forest dark:text-emerald-400 uppercase tracking-wider mb-2">Dokumentasi</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-4 flex-grow line-clamp-3">Rancangan sumber data dari Caterpillar, SAP, dan Master Data.</p>
                <!-- Card Links -->
                <div class="flex gap-4 pt-3 border-t border-slate-100 dark:border-white/5">
                    <a href="{{ route('monitoring.flow') }}" class="text-forest dark:text-emerald-400 hover:text-green-800 dark:hover:text-emerald-300 hover:underline text-sm font-semibold transition">
                        Lihat Diagram <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bootstrap Inspired Card 4 (Admin Dark Theme) -->
        @if(Auth::user()->role === 'admin')
        <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-sm overflow-hidden flex flex-col hover:-translate-y-1 hover:shadow-lg hover:shadow-slate-800/20 transition-all duration-300 animate-stagger delay-500">
            <!-- Image Cap Replacement -->
            <div class="h-32 bg-slate-900 flex items-center justify-center text-slate-600 border-b border-slate-700 group">
                <i class="fas fa-file-upload text-6xl drop-shadow-sm group-hover:scale-110 transition-transform duration-300"></i>
            </div>
            <!-- Card Body -->
            <div class="p-5 flex flex-col flex-grow">
                <h3 class="text-lg font-bold font-serif text-white mb-1">Impor Data</h3>
                <p class="text-sm text-slate-400 mb-4 flex-grow line-clamp-3">Unggah file untuk memperbarui basis data ke periode bulan terbaru.</p>
                <!-- Card Buttons -->
                <div class="flex gap-2">
                    <a href="{{ route('import.index') }}" class="flex-1 bg-white hover:bg-slate-200 text-slate-900 font-bold py-2.5 px-4 rounded-lg text-center transition text-sm shadow-sm active:scale-95">
                        Impor
                    </a>
                    <a href="{{ route('users.index') }}" class="flex-1 bg-slate-700 hover:bg-slate-600 text-white border border-slate-600 font-semibold py-2.5 px-4 rounded-lg text-center transition text-sm shadow-sm active:scale-95">
                        Pengguna
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

        // Trigger animations
        const totalAsetElem = document.getElementById('count_aset');
        const avgIdleElem = document.getElementById('count_idle');
        const totalFuelElem = document.getElementById('count_fuel');

        if (totalAsetElem) animateNumbers(totalAsetElem, {{ $totalAset }}, 1500);
        if (avgIdleElem) animateNumbers(avgIdleElem, {{ $avgIdle }}, 1500, true);
        if (totalFuelElem) animateNumbers(totalFuelElem, {{ $totalFuel }}, 2000);
    });
</script>
@endsection
