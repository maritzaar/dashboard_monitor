<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\resources\views\home.blade.php';
$content = file_get_contents($file);

// 1. Replace the efficiency card metrics
$search1 = <<<'EOD'
                <!-- Metrics -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Solar</span>
                        <span class="text-base font-black text-slate-800 dark:text-slate-100">{{ number_format($totalFuel, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">L</span></span>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-950 p-3 rounded-lg border border-slate-100 dark:border-white/5">
                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Efisiensi</span>
                        <span class="text-base font-black text-tpaGreen dark:text-emerald-400">{{ number_format($totalKerja > 0 ? $totalFuel / $totalKerja : 0, 2, ',', '.') }} <span class="text-xs font-semibold text-slate-500">L/Jam</span></span>
                    </div>
                </div>
EOD;
$replace1 = <<<'EOD'
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
EOD;
$content = str_replace($search1, $replace1, $content);

// 2. Replace the whole Leaderboard Section
$search2 = <<<'EOD'
        <!-- Insight Kinerja Widget -->
        <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-5 mb-2 animate-stagger delay-500">
            <!-- Top 5 Paling Efisien -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-tpaGreen/30 dark:border-emerald-500/20 shadow-sm overflow-hidden flex flex-col">
                <div class="h-12 bg-tpaGreen/5 dark:bg-emerald-900/30 flex items-center justify-between px-5 border-b border-tpaGreen/10 dark:border-emerald-500/10">
                    <h3 class="font-bold text-tpaGreen dark:text-emerald-400 flex items-center">
                        <i class="fas fa-trophy mr-2 text-tpaGreen dark:text-emerald-400"></i> Top 5 Paling Efisien
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
                            @forelse($topEfficient as $index => $item)
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

            <!-- Top 5 Paling Boros -->
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-tpaOrange/30 dark:border-rose-500/20 shadow-sm overflow-hidden flex flex-col">
                <div class="h-12 bg-tpaOrange/5 dark:bg-rose-900/30 flex items-center justify-between px-5 border-b border-tpaOrange/10 dark:border-rose-500/10">
                    <h3 class="font-bold text-tpaOrange dark:text-rose-400 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2 text-tpaOrange dark:text-rose-400"></i> Top 5 Paling Boros
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
                            @forelse($bottomEfficient as $index => $item)
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
EOD;

$replace2 = <<<'EOD'
        <!-- Insight Kinerja Widget Alat Berat -->
        <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-5 mb-2 animate-stagger delay-500">
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
        <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-2 gap-5 mb-2 animate-stagger delay-500">
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
EOD;

$content = str_replace($search2, $replace2, $content);
file_put_contents($file, $content);
echo "home.blade.php updated.";
