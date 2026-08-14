<?php
$file = 'e:\TPA\Dashboard\dashboard_monitor\resources\views\monitoring\efficiency.blade.php';
$content = file_get_contents($file);

// 1. Fix KPI cards
$search1 = <<<'EOD'
        {{-- Avg Efficiency --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaOrange-600 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rerata Konsumsi Solar</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->avg_efficiency, 2) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L/Jam</span>
            </p>
        </div>
EOD;

$replace1 = <<<'EOD'
        {{-- Avg Efficiency AB --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-tpaOrange-600 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rasio Alat Berat</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->avg_efficiency_ab ?? 0, 2) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L/Jam</span>
            </p>
        </div>
        
        {{-- Avg Efficiency Ken --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-white/5 border-l-4 border-l-indigo-500 p-4 shadow-sm transition-colors duration-200">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rasio Kendaraan</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-slate-100 mt-1">
                {{ number_format($stats->avg_efficiency_ken ?? 0, 2) }}
                <span class="text-xs font-normal text-slate-400 ml-1">KM/L</span>
            </p>
        </div>
EOD;

// 2. Fix Grid cols
$search2 = '<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">';
$replace2 = '<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">';

// 3. Fix Table Headers
$search3 = '<th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-800/80">EFISIENSI<br>(L/JAM)</th>';
$replace3 = '<th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-800/80">EFISIENSI<br>(RASIO)</th>';

// 4. Fix Table Data & Badges
$search4 = <<<'EOD'
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                            {{ is_null($row->efficiency) ? '-' : number_format($row->efficiency, 2) }}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($row->total_kerja == 0 && $row->total_solar > 0)
                                @php $naCount++; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                                    N/A (Tanpa HM)
                                </span>
                            @elseif(is_null($row->efficiency))
                                @php $naCount++; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    N/A
                                </span>
                            @elseif($row->efficiency > 15)
                                @php $warningCount++; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50">
                                    Boros (>15)
                                </span>
                            @else
                                @php $efficientCount++; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">
                                    Efisien (<=15)
                                </span>
                            @endif
                        </td>
EOD;

$replace4 = <<<'EOD'
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                            {{ is_null($row->efficiency) ? '-' : number_format($row->efficiency, 2) }} <span class="text-[9px] text-slate-400">{{ $row->uom ?? '' }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if($row->total_kerja == 0 && $row->total_solar > 0)
                                @php $naCount++; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">
                                    N/A (Tanpa HM)
                                </span>
                            @elseif(is_null($row->efficiency))
                                @php $naCount++; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    N/A
                                </span>
                            @else
                                @php
                                    $isBoros = false;
                                    if ($row->is_kendaraan ?? false) {
                                        $isBoros = isset($row->target_ratio) ? ($row->efficiency < $row->target_ratio) : false;
                                    } else {
                                        $isBoros = isset($row->target_ratio) ? ($row->efficiency > $row->target_ratio) : ($row->efficiency > 15);
                                    }
                                @endphp
                                @if($isBoros)
                                    @php $warningCount++; @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-800 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50">
                                        Boros {!! isset($row->target_ratio) ? '(<i class="fas fa-bullseye"></i> '.$row->target_ratio.')' : '' !!}
                                    </span>
                                @else
                                    @php $efficientCount++; @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">
                                        Efisien {!! isset($row->target_ratio) ? '(<i class="fas fa-bullseye"></i> '.$row->target_ratio.')' : '' !!}
                                    </span>
                                @endif
                            @endif
                        </td>
EOD;

$content = str_replace($search1, $replace1, $content);
$content = str_replace($search2, $replace2, $content);
$content = str_replace($search3, $replace3, $content);
$content = str_replace($search4, $replace4, $content);

// 5. Fix Chart Labels
$search5 = "labels: ['Efisien (<=15 L/Jam)', 'Boros (>15 L/Jam)', 'N/A / Tanpa HM'],";
$replace5 = "labels: ['Efisien (Sesuai Target)', 'Boros (Meleset)', 'N/A / Tanpa HM'],";
$content = str_replace($search5, $replace5, $content);

// Add tooltip for target in chart data
$search6 = <<<'EOD'
                    label: function(context) {
                        return context.label + ': ' + context.parsed.y + ' L/Jam';
                    }
EOD;
$replace6 = <<<'EOD'
                    label: function(context) {
                        return context.label + ': ' + context.parsed.y + ' ' + (context.raw.uom || 'Rasio');
                    }
EOD;
$content = str_replace($search6, $replace6, $content);

file_put_contents($file, $content);
echo "efficiency.blade.php updated.";
