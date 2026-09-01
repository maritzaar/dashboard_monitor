@extends('layouts.app')

@section('title', 'Impor Data')

@section('content')
<div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm space-y-6">
    <h2 class="text-xl sm:text-2xl font-bold text-slate-800 flex items-center">
        <i class="fas fa-upload text-tpaGreen-600 mr-2"></i> {{ __('Impor Data') }}
    </h2>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm space-y-1">
            <p class="font-bold">{{ __('Gagal Validasi:') }}</p>
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Import -->
    <div class="bg-tpaGreen-50/50 p-4 sm:p-6 rounded-xl border border-tpaGreen-100">
        <form action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-655 mb-1.5">{{ __('Sumber Data') }}</label>
                    <select name="sumber" class="w-full rounded-lg border border-slate-300 bg-white text-slate-700 text-sm p-2.5 focus:border-tpaGreen-600 focus:ring-tpaGreen-600 focus:outline-none">
                        <option value="INTERNAL">{{ __('JAM KERJA') }}</option>
                        <option value="BUDGET">{{ __('BUDGET & AKTUAL SOLAR (EXCEL SAP/COST CONTROL)') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-655 mb-1.5">{{ __('Berkas Excel / CSV') }}</label>
                    <div class="relative flex items-center">
                        <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" required class="hidden">
                        <button type="button" onclick="document.getElementById('fileInput').click()" 
                                class="bg-gradient-to-r from-tpaGreen-600 to-tpaGreen-700 hover:from-tpaGreen-700 hover:to-tpaGreen-800 text-white px-4 py-2 rounded-lg text-xs font-bold transition shadow-sm select-none mr-3">
                            {{ __('Pilih Berkas') }}
                        </button>
                        <span id="fileNameDisplay" class="text-xs text-slate-500 truncate">{{ __('Belum ada berkas dipilih') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">{{ __('Format: .xlsx, .xls, .csv') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 pt-2">
                <button type="submit" class="bg-gradient-to-r from-tpaGreen-600 to-tpaGreen-700 hover:from-tpaGreen-700 hover:to-tpaGreen-800 text-white px-5 py-2.5 rounded-lg transition text-sm font-semibold shadow-sm inline-flex items-center">
                    <i class="fas fa-upload mr-2"></i> {{ __('Impor Data') }}
                </button>
                <a href="{{ route('import.clear') }}" class="bg-rose-600 text-white px-5 py-2.5 rounded-lg hover:bg-rose-700 transition text-sm font-semibold shadow-sm inline-flex items-center"
                   onclick="return confirm('{{ __('Apakah Anda yakin ingin menghapus semua data?') }}')">
                    <i class="fas fa-trash mr-2"></i> {{ __('Hapus Semua Data') }}
                </a>
            </div>
        </form>
    </div>

    @if(session('import_summary'))
        @php($summary = session('import_summary'))
        <div class="border border-emerald-100 bg-emerald-50/60 rounded-xl p-4 sm:p-5">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider">{{ __('Ringkasan Impor Terakhir') }}</p>
                    <h3 class="text-lg font-bold text-slate-800 mt-1">{{ $summary['filename'] ?? '-' }}</h3>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span class="px-2.5 py-1 rounded-full bg-white border border-emerald-100 text-emerald-800 text-xs font-bold">{{ $summary['sumber'] ?? '-' }}</span>
                        @foreach(($summary['periods'] ?? []) as $period)
                            <span class="px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700 text-xs font-semibold">{{ $period }}</span>
                        @endforeach
                        @if(!empty($summary['detected_format']))
                            <span class="px-2.5 py-1 rounded-full bg-tpaOrange-50 border border-tpaOrange-200 text-tpaOrange-700 text-xs font-semibold">
                                {{ __('Format:') }} {{ $summary['detected_format'] }}
                            </span>
                        @endif
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 min-w-full lg:min-w-[520px]">
                    <div class="bg-white border border-slate-100 rounded-lg p-3">
                        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">{{ __('Baris Dibaca') }}</p>
                        <p class="text-xl font-bold text-slate-800">{{ number_format($summary['processed_rows'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-lg p-3">
                        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">{{ __('Valid') }}</p>
                        <p class="text-xl font-bold text-emerald-700">{{ number_format($summary['valid_rows'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-lg p-3">
                        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">{{ __('Dilewati') }}</p>
                        <p class="text-xl font-bold text-amber-700">{{ number_format($summary['skipped_rows'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white border border-slate-100 rounded-lg p-3">
                        <p class="text-[10px] uppercase tracking-wider font-bold text-slate-400">{{ __('Variabel Unik') }}</p>
                        <p class="text-xl font-bold text-tpaGreen-700">{{ number_format($summary['unique_assets'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
            @if(!empty($summary['skip_reasons']))
                <div class="mt-4 bg-white border border-amber-100 rounded-lg p-3">
                    <p class="text-xs font-bold text-amber-800 mb-2">{{ __('Alasan Baris Dilewati') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($summary['skip_reasons'] as $reason => $count)
                            <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-800 border border-amber-100 text-xs font-semibold">
                                {{ $reason }}: {{ number_format($count) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Riwayat Unggah Berkas -->
    <div class="pt-2">
        <h3 class="text-md font-bold mb-4 text-slate-700 flex items-center">
            <i class="fas fa-history text-tpaGreen-600 mr-2"></i> {{ __('Riwayat Unggah Berkas') }}
        </h3>
        <div class="overflow-x-auto -mx-4 sm:mx-0 table-scroll">
            <table class="min-w-full divide-y divide-slate-200 border border-slate-100 rounded-lg overflow-hidden">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-550 uppercase tracking-wider whitespace-nowrap">{{ __('Nama Berkas') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-550 uppercase tracking-wider whitespace-nowrap">{{ __('Sumber') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-550 uppercase tracking-wider whitespace-nowrap">{{ __('Baris Ditambahkan') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-550 uppercase tracking-wider whitespace-nowrap">{{ __('Tanggal Unggah') }}</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-slate-550 uppercase tracking-wider whitespace-nowrap">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100 text-sm">
                    @forelse($history as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                            <i class="far fa-file-excel text-emerald-600 mr-2"></i>{{ $log->filename }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-xs rounded-full font-semibold border
                                @if($log->sumber == 'CATERPILLAR') bg-tpaGreen-50 text-tpaGreen-700 border-tpaGreen-100
                                @elseif($log->sumber == 'INTERNAL') bg-emerald-50 text-emerald-800 border-emerald-100
                                @elseif($log->sumber == 'FUEL') bg-amber-50 text-amber-800 border-amber-100
                                @else bg-slate-105 text-slate-600 border-slate-200 @endif">
                                {{ $log->sumber }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-650 font-bold whitespace-nowrap">{{ number_format($log->rows_count) }}</td>
                        <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $log->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <form action="{{ route('import.delete-log', $log->id) }}" method="POST" 
                                  onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data dari berkas ini?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 transition inline-flex items-center text-xs font-bold">
                                    <i class="fas fa-trash-can mr-1.5"></i> {{ __('Hapus Data') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">{{ __('Belum ada riwayat unggahan berkas.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="pt-4">
        <h3 class="text-md font-bold mb-4 text-slate-700 flex items-center">
            <i class="fas fa-database text-tpaGreen-600 mr-2"></i> {{ __('Data Tersimpan') }}
        </h3>
        <div class="overflow-x-auto -mx-4 sm:mx-0 table-scroll">
            <table class="min-w-full divide-y divide-slate-200 border border-slate-100 rounded-lg overflow-hidden">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('Tanggal') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('ID Aset') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('Model') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('Sumber') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('Waktu Kerja') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('Waktu Operasi') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">{{ __('% Idle') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($data as $item)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ $item->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-bold text-slate-800 whitespace-nowrap">{{ $item->id_aset }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ $item->model }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 text-xs rounded-full font-semibold border
                                @if($item->sumber_data == 'CATERPILLAR') bg-tpaGreen-50 text-tpaGreen-700 border-tpaGreen-100
                                @elseif($item->sumber_data == 'INTERNAL') bg-emerald-50 text-emerald-800 border-emerald-100
                                @else bg-slate-100 text-slate-700 border-slate-200 @endif">
                                {{ $item->sumber_data }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-slate-700 font-semibold whitespace-nowrap">{{ number_format($item->waktu_kerja, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 font-semibold whitespace-nowrap">{{ number_format($item->waktu_operasi, 2) }}</td>
                        <td class="px-4 py-3 text-sm text-slate-700 font-semibold whitespace-nowrap">{{ number_format($item->persen_idle, 2) }}%</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-450">
                            <i class="fas fa-inbox text-3xl block mb-2 text-slate-350"></i>
                            {{ __('Belum ada data. Silakan lakukan impor data.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $data->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('fileInput');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
                fileNameDisplay.classList.remove('text-slate-500');
                fileNameDisplay.classList.add('text-slate-800', 'font-semibold');
            } else {
                fileNameDisplay.textContent = 'Belum ada berkas dipilih';
                fileNameDisplay.classList.remove('text-slate-800', 'font-semibold');
                fileNameDisplay.classList.add('text-slate-500');
            }
        });
    }
});
</script>
@endsection
