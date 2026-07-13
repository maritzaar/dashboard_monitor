@extends('layouts.app')
@section('title', 'Alur Sistem')
@section('content')

<!-- Include Mermaid.js -->
<script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>
<script>
    mermaid.initialize({ startOnLoad: true, theme: 'default' });
</script>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Alur Sistem (System Flow)</h1>
    </div>

    <!-- Tab Navigation -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button id="tab-solar" onclick="switchTab('solar')" class="border-blue-600 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition">
                <i class="fas fa-gas-pump mr-2"></i>Monitoring Pemakaian Solar
            </button>
            <button id="tab-alat" onclick="switchTab('alat')" class="border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                <i class="fas fa-tractor mr-2"></i>Monitoring Penggunaan Alat Berat
            </button>
        </nav>
    </div>

    <!-- Tab Content: Solar -->
    <div id="content-solar" class="bg-white rounded-xl border border-slate-200 p-8 shadow-sm relative opacity-100 visible w-full transition-all duration-300">
        <div class="w-full flex justify-center overflow-x-auto">
            <div class="mermaid">
                graph TD
                classDef default fill:#f8fafc,stroke:#cbd5e1,stroke-width:2px,color:#334155;
                classDef highlight fill:#eff6ff,stroke:#60a5fa,stroke-width:2px,color:#1e3a8a;
                classDef dark fill:#1e293b,stroke:#0f172a,stroke-width:2px,color:#f8fafc;
                classDef table fill:#fff,stroke:#e2e8f0,stroke-width:2px,color:#0f172a,stroke-dasharray: 5 5;
                
                A["Estate LKE"]:::dark
                A --> B["Sistem AGI GPS<br/>GPS tracking and telemetry"]
                B --> C["Vehicle Identifiers<br/>No. Mesin / Rangka / Nopol<br/>Jenis: Dump Truck"]
                C --> D["Data Kilometer"]
                
                E["Nomor Kendaraan TPA<br/>e.g. E031 XX"]:::highlight
                E --> F["Pemakaian Solar<br/>Dispenser readings liters"]
                
                D --> G["Sistem Kontrol<br/>Pemakaian Solar"]:::dark
                F --> G
                
                G --> H["Laporan Kontrol<br/>Pemakaian BBM"]:::highlight
                H --> I["Tabel: Kilometer vs Liter<br/>Efficiency comparison per vehicle"]:::table
            </div>
        </div>
    </div>

    <!-- Tab Content: Alat Berat UT -->
    <div id="content-alat" class="bg-white rounded-xl border border-slate-200 p-8 shadow-sm absolute top-0 left-0 opacity-0 invisible pointer-events-none w-full transition-all duration-300">
        <div class="w-full flex justify-center overflow-x-auto">
            <div class="mermaid">
                graph TD
                classDef default fill:#f8fafc,stroke:#cbd5e1,stroke-width:2px,color:#334155;
                classDef source fill:#fef3c7,stroke:#f59e0b,stroke-width:2px,color:#92400e;
                classDef primary fill:#dbeafe,stroke:#3b82f6,stroke-width:2px,color:#1d4ed8;
                classDef dark fill:#1e293b,stroke:#0f172a,stroke-width:2px,color:#f8fafc;
                classDef extract fill:#f1f5f9,stroke:#94a3b8,stroke-width:1px,color:#475569;
                
                Start(("Mulai")):::dark
                
                Start --> S1["Source 1<br/>CATERPILLAR<br/>https://VL.cat.com/"]:::source
                Start --> S2["Source 2<br/>INTERNAL<br/>Master Data Internal"]:::source
                Start --> S3["Source 3<br/>SAP<br/>Data"]:::source
                
                S1 --> E1["Extract<br/>Caterpillar export file"]:::extract
                S2 --> E2["Extract<br/>Internal system / Excel"]:::extract
                S3 --> E3["Extract<br/>SAP"]:::extract
                
                E1 --> Process["Read - Clean - Standardize format<br/>Unify units and unit codes"]:::dark
                E2 --> Process
                E3 --> Process
                
                Process --> Join["Merge / Join<br/>by Unit ID"]:::primary
                
                Join --> DB[("Database<br/>Monitoring SQLite")]
                DB --> App["PHP - LARAVEL<br/>Operating hours - Fuel"]:::dark
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tabId) {
        // Reset all tabs
        document.getElementById('tab-solar').className = "border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition";
        document.getElementById('tab-alat').className = "border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition";
        
        // Hide all contents (using opacity/absolute positioning instead of display:none)
        const contentSolar = document.getElementById('content-solar');
        const contentAlat = document.getElementById('content-alat');
        
        contentSolar.classList.remove('relative', 'opacity-100', 'visible');
        contentSolar.classList.add('absolute', 'top-0', 'left-0', 'opacity-0', 'invisible', 'pointer-events-none');
        
        contentAlat.classList.remove('relative', 'opacity-100', 'visible');
        contentAlat.classList.add('absolute', 'top-0', 'left-0', 'opacity-0', 'invisible', 'pointer-events-none');
        
        // Active selected tab
        if(tabId === 'solar') {
            document.getElementById('tab-solar').className = "border-blue-600 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition";
            contentSolar.classList.remove('absolute', 'top-0', 'left-0', 'opacity-0', 'invisible', 'pointer-events-none');
            contentSolar.classList.add('relative', 'opacity-100', 'visible');
        } else {
            document.getElementById('tab-alat').className = "border-blue-600 text-blue-600 whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition";
            contentAlat.classList.remove('absolute', 'top-0', 'left-0', 'opacity-0', 'invisible', 'pointer-events-none');
            contentAlat.classList.add('relative', 'opacity-100', 'visible');
        }
    }
</script>
@endsection
