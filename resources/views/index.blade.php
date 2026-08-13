@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-2xl font-bold mb-6">Import Data Excel</h2>

    <!-- Form Import -->
    <div class="bg-tpaGreen-50 p-6 rounded-lg mb-8">
        <form action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Sumber Data</label>
                <select name="sumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-tpaGreen-500 focus:ring-tpaGreen-500">
                    <option value="CATERPILLAR">CATERPILLAR</option>
                    <option value="INTERNAL">INTERNAL</option>
                    <option value="SAP">SAP</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">File Excel</label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                       class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-tpaGreen-50 file:text-tpaGreen-700 hover:file:bg-tpaGreen-100">
                <p class="text-xs text-gray-500 mt-1">Format: .xlsx, .xls, .csv</p>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-tpaGreen-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    <i class="fas fa-upload mr-2"></i> Import Data
                </button>
                <a href="{{ route('import.clear') }}" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700"
                   onclick="return confirm('Yakin ingin menghapus semua data?')">
                    <i class="fas fa-trash mr-2"></i> Hapus Semua Data
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-x-auto">
        <h3 class="text-lg font-semibold mb-4">Data Tersimpan</h3>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Aset</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sumber</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Kerja</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Operasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% Idle</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $item)
                <tr>
                    <td class="px-6 py-4">{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 font-medium">{{ $item->id_aset }}</td>
                    <td class="px-6 py-4">{{ $item->model }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full
                            @if($item->sumber_data == 'CATERPILLAR') bg-tpaGreen-100 text-tpaGreen-800
                            @elseif($item->sumber_data == 'INTERNAL') bg-green-100 text-green-800
                            @else bg-tpaOrange-100 text-tpaOrange-800 @endif">
                            {{ $item->sumber_data }}
                        </span>
                    </td>
                    <td class="px-6 py-4">{{ number_format($item->waktu_kerja, 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($item->waktu_operasi, 2) }}</td>
                    <td class="px-6 py-4">{{ number_format($item->persen_idle, 2) }}%</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada data. Silakan import Data</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $data->links() }}
        </div>
    </div>
</div>
@endsection