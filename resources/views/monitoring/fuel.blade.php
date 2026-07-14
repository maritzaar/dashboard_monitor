@extends('layouts.app')

@section('title', 'Laporan Utama Monitoring')

@section('content')
<div class="space-y-6">

    {{-- ====== HEADER ====== --}}
    <div class="bg-gradient-to-r from-slate-900 to-indigo-950 rounded-xl p-5 text-white shadow-md">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-file-invoice-dollar text-xl text-indigo-400"></i>
            </div>
            <div>
                <p class="text-xs text-indigo-300 font-semibold uppercase tracking-wider">Fuel Reports</p>
                <h2 class="text-2xl font-extrabold tracking-wide">Laporan Konsumsi Solar</h2>
            </div>
            <div class="ml-auto text-right hidden sm:block">
                <p class="text-xs text-indigo-300">Periode</p>
                <p class="text-md font-bold">
                    {{ $bulan == 'ALL' ? __('Semua Bulan') : __($bulan) }} 
                    {{ $tahun == 'ALL' ? __('Semua Tahun') : $tahun }}
                </p>
            </div>
        </div>
    </div>

    {{-- ====== STAT CARDS ====== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Assets --}}
        <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-blue-500 p-4 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Unit Aset</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">
                {{ number_format($stats->total_aset, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">Unit</span>
            </p>
        </div>

        {{-- Total Fuel --}}
        <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-emerald-500 p-4 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Solar (Aktual)</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">
                {{ number_format($stats->actual_fuel, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L</span>
            </p>
        </div>

        {{-- Avg Fuel --}}
        <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-indigo-500 p-4 shadow-sm">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Rata-Rata Solar / Unit</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">
                {{ number_format($stats->avg_fuel, 0) }}
                <span class="text-xs font-normal text-slate-400 ml-1">L</span>
            </p>
        </div>

        {{-- Max Fuel --}}
        <div class="bg-white rounded-xl border border-slate-200 border-l-4 border-l-rose-500 p-4 shadow-sm flex flex-col justify-between">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Konsumsi Tertinggi</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">
                    {{ number_format($stats->max_fuel_val, 0) }}
                    <span class="text-xs font-normal text-slate-400 ml-1">L</span>
                </p>
            </div>
            @if($stats->max_fuel_aset !== '-')
            <div class="text-[10px] text-slate-500 font-semibold mt-1">
                Unit: <span class="text-rose-600 font-bold font-mono">{{ $stats->max_fuel_aset }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- ====== CHART SECTION ====== --}}
    @if($reports->isNotEmpty())
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Bar Chart (Kiri - 2/3 width) -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-4 flex items-center">
                <i class="fas fa-chart-bar text-indigo-600 mr-2"></i> Perbandingan Konsumsi Solar per Aset
            </h3>
            <div class="relative h-72 sm:h-96">
                <canvas id="fuelReportChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart (Kanan - 1/3 width) -->
        <div class="lg:col-span-1 bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                    <i class="fas fa-chart-pie text-indigo-600 mr-2"></i> Distribusi Konsumsi Solar
                </h3>
                <div class="flex bg-slate-100 rounded-lg p-0.5 border border-slate-200 no-print text-[10px] font-bold">
                    <button type="button" id="toggleDoughnutGroup" class="px-2 py-1 rounded-md bg-white text-slate-800 shadow-sm border border-slate-250 transition-all focus:outline-none">Grup</button>
                    <button type="button" id="toggleDoughnutArea" class="px-2 py-1 rounded-md text-slate-500 hover:text-slate-800 transition-all focus:outline-none ml-0.5">Area</button>
                </div>
            </div>
            <div class="relative h-72 sm:h-96 flex-1 flex items-center justify-center">
                <canvas id="fuelDistributionChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== FILTER BAR ====== --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm no-print">
        <form action="{{ route('monitoring.fuel') }}" method="GET">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 items-end">
                {{-- Bulan --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Month</label>
                    <select name="bulan" class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ $bulan == 'ALL' ? 'selected' : ' ' }}>{{ __('Semua Bulan') }}</option>
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Tahun --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Year</label>
                    <select name="tahun" class="w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">

                    <option value="ALL" {{ $tahun == 'ALL' ? 'selected' : '' }}>{{ __('Semua Tahun') }}</option>
                        @for($i = 2023; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                {{-- Aset --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Asset (Unit)</label>
                    <select name="id_aset" id="filter_id_aset" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($id_aset) || $id_aset == 'ALL') ? 'selected' : '' }}>{{ __('Semua Aset') }}</option>
                        @foreach($filterUnits as $unit)
                            <option value="{{ $unit }}" {{ (isset($id_aset) && $id_aset == $unit) ? 'selected' : '' }}>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Grup --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Group Aset</label>
                    <select name="group_aset" id="filter_group_aset" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_aset) || $group_aset == 'ALL') ? 'selected' : '' }}>{{ __('Semua Grup') }}</option>
                        @foreach($filterGroups as $group)
                            <option value="{{ $group }}" {{ (isset($group_aset) && $group_aset == $group) ? 'selected' : '' }}>{{ $group }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Area --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Area</label>
                    <select name="area" id="filter_area" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($area) || $area == 'ALL') ? 'selected' : '' }}>{{ __('Semua Area') }}</option>
                        @foreach($filterAreas as $a)
                            <option value="{{ $a }}" {{ (isset($area) && $area == $a) ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- PT --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">PT</label>
                    <select name="pt" id="filter_pt" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($pt) || $pt == 'ALL') ? 'selected' : '' }}>{{ __('Semua PT') }}</option>
                        @foreach($filterPts as $p)
                            <option value="{{ $p }}" {{ (isset($pt) && $pt == $p) ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Group Desc --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Group Desc</label>
                    <select name="group_desc" id="filter_group_desc" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_desc) || $group_desc == 'ALL') ? 'selected' : '' }}>{{ __('Semua Group Desc') }}</option>
                        @foreach($filterGroupDescs as $gd)
                            <option value="{{ $gd }}" {{ (isset($group_desc) && $group_desc == $gd) ? 'selected' : '' }}>{{ $gd }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- IO Group --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">IO Group</label>
                    <select name="group_internal_order" id="filter_group_internal_order" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($group_internal_order) || $group_internal_order == 'ALL') ? 'selected' : '' }}>{{ __('Semua IO Group') }}</option>
                        @foreach($filterIoGroups as $ig)
                            <option value="{{ $ig }}" {{ (isset($group_internal_order) && $group_internal_order == $ig) ? 'selected' : '' }}>{{ $ig }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- Internal Order --}}
                <div>
                    <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Internal Order</label>
                    <select name="internal_order" id="filter_internal_order" class="searchable-select dependent-filter w-full rounded-lg border border-slate-300 bg-slate-50 text-slate-700 text-sm py-2 px-3 focus:border-blue-600 focus:outline-none">
                        <option value="ALL" {{ (!isset($internal_order) || $internal_order == 'ALL') ? 'selected' : '' }}>{{ __('Semua IO') }}</option>
                        @foreach($filterInternalOrders as $io)
                            <option value="{{ $io }}" {{ (isset($internal_order) && $internal_order == $io) ? 'selected' : '' }}>{{ $io }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-4 pt-2 border-t border-slate-100">
                <a href="{{ route('monitoring.fuel', ['bulan' => 'ALL', 'tahun' => 'ALL']) }}" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-600 font-semibold rounded-lg text-sm transition">
                    {{ __('Reset Filter') }}
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm">
                    <i class="fas fa-filter mr-2"></i> Apply Filter
                </button>
                <a href="{{ route('monitoring.export', array_merge(request()->all(), ['type' => 'fuel'])) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2 rounded-lg transition text-sm flex items-center shadow-sm ml-2">
                    <i class="fas fa-file-excel mr-2"></i> Export Excel
                </a>
            </div>
        </form>
    </div>




    {{-- ====== DATA TABLE ====== --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-sm">
        <div class="border-b border-slate-100 pb-3 mb-4 flex flex-wrap justify-between items-center gap-2">
            <div>
                <h3 class="text-md font-bold text-slate-800 flex items-center">
                    <i class="fas fa-list-check text-indigo-600 mr-2"></i> Rincian Konsumsi Solar Aset
                </h3>
                <p class="text-xs text-slate-450 mt-0.5">Data transaksi pengisian solar riil</p>
            </div>
            <div class="text-right flex items-center justify-end gap-3 w-full sm:w-auto mt-2 sm:mt-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fas fa-search text-xs"></i>
                    </div>
                    <input type="text" id="assetSearchInput" placeholder="Cari data..."
                           class="pl-8 pr-3 py-1.5 w-full sm:w-48 border border-slate-300 rounded-lg text-sm bg-slate-50 text-slate-800 placeholder-slate-400 focus:ring-blue-600 focus:border-blue-600 focus:outline-none transition-all">
                </div>
                <span class="text-xs bg-slate-100 text-slate-600 font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 shadow-sm whitespace-nowrap">
                    {{ number_format($reports->count()) }} records
                </span>
            </div>
        </div>

        <div class="overflow-x-auto max-h-[600px] table-scroll">
            <table class="min-w-full divide-y divide-slate-200 border border-slate-100 text-sm">
                <thead class="bg-slate-50 sticky top-0 shadow-sm z-10">
                    <tr>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Group</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Area</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">PT</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Unit</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Bulan</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Tahun</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Internal Order</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">IO Group</th>
                        <th class="px-3 py-3 text-left text-[10px] font-bold text-slate-500 uppercase tracking-wider">Group Desc</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Fuel Akt (L)</th>
                        <th class="px-3 py-3 text-right text-[10px] font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($reports as $row)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->group_aset ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->area ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->pt ?? '-' }}</td>
                        <td class="px-3 py-2.5 font-bold text-slate-700 font-mono">{{ $row->id_aset }}</td>
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->bulan }}</td>
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->tahun }}</td>
                        <td class="px-3 py-2.5 text-slate-700 font-mono text-xs">{{ $row->internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->group_internal_order ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-slate-600 text-xs">{{ $row->group_desc ?? '-' }}</td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold text-emerald-600">{{ number_format($row->actual_fuel, 0) }}</td>
                        <td class="px-3 py-2.5 text-right">
                            <a href="{{ route('monitoring.fuel_detail', $row->id_aset) }}" class="text-blue-600 hover:text-blue-800"><i class="fas fa-eye"></i> Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-4 py-12 text-center text-slate-400">
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
        btn.className = 'w-full flex items-center justify-between rounded-lg border border-slate-350 bg-white text-slate-700 text-sm py-2 px-3 focus-within:border-blue-600 focus-within:ring-1 focus-within:ring-blue-600 focus:outline-none cursor-pointer select-none';
        
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
        
        iconContainer.appendChild(clearBtn);
        iconContainer.appendChild(caret);
        btn.appendChild(btnText);
        btn.appendChild(iconContainer);
        wrapper.appendChild(btn);
        
        // Dropdown Menu Container
        const menu = document.createElement('div');
        menu.className = 'absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-50 flex flex-col hidden';
        menu.style.maxHeight = '280px';
        
        // Search Input
        const searchBox = document.createElement('div');
        searchBox.className = 'p-2 border-b border-slate-100 flex-shrink-0';
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search...';
        searchInput.className = 'w-full rounded-md border border-slate-300 bg-slate-50 text-slate-700 text-xs py-1.5 px-2.5 focus:border-blue-600 focus:outline-none';
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
                optItem.className = 'px-3 py-2 text-xs text-slate-700 hover:bg-blue-600 hover:text-white cursor-pointer transition-colors';
                optItem.textContent = opt.text;
                optItem.dataset.value = opt.value;
                
                if (opt.selected) {
                    optItem.classList.add('bg-blue-50', 'text-blue-800', 'font-semibold');
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
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.85)');
    gradient.addColorStop(1, 'rgba(6, 182, 212, 0.35)');

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Solar (L)',
                data: fuelData,
                backgroundColor: gradient,
                borderColor: '#4f46e5',
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
                    title: { display: true, text: 'Volume (L)', font: { weight: 'bold' } }
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
        '#4f46e5', // indigo
        '#06b6d4', // cyan
        '#10b981', // emerald
        '#f59e0b', // amber
        '#ef4444', // red
        '#ec4899', // pink
        '#8b5cf6', // violet
        '#14b8a6', // teal
        '#f97316', // orange
        '#64748b'  // slate
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

            try {
                let response = await fetch(`/api/monitoring/filter-options?${params.toString()}`);
                if (!response.ok) throw new Error('Network response was not ok');
                let data = await response.json();
                
                // Conflict resolution: If combination yields 0 units, prioritize the newly changed filter
                if (data.filterUnits && data.filterUnits.length === 0 && e.target.value && e.target.value !== 'ALL') {
                    params = new URLSearchParams();
                    params.append(e.target.name, e.target.value);
                    
                    // Clear other filters visually
                    dependentFilters.forEach(f => {
                        if (f !== e.target && f.name !== 'bulan' && f.name !== 'tahun') {
                            f.value = 'ALL';
                            if (typeof f.updateCustomUI === 'function') f.updateCustomUI();
                        }
                    });

                    // Keep dates if present
                    const bulan = document.querySelector('select[name="bulan"]');
                    const tahun = document.querySelector('select[name="tahun"]');
                    if (bulan && bulan.value && bulan.value !== 'ALL') params.append('bulan', bulan.value);
                    if (tahun && tahun.value && tahun.value !== 'ALL') params.append('tahun', tahun.value);

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
</script>

@endsection
