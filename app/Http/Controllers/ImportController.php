<?php

namespace App\Http\Controllers;

use App\Imports\DataAlatImport;
use App\Imports\FuelImportCollection;
use App\Models\DataAlat;
use App\Models\FuelTransaction;
use App\Models\ImportLog;
use App\Models\MonitoringSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function index()
    {
        $data = DataAlat::orderBy('tanggal', 'desc')->paginate(50);
        $history = ImportLog::orderBy('created_at', 'desc')->take(10)->get();

        return view('import.index', compact('data', 'history'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'sumber' => 'required|in:CATERPILLAR,INTERNAL,SAP,FUEL',
        ]);

        try {
            set_time_limit(120); // max upload time 2 minutes
            $filename = $request->file('file')->getClientOriginalName();
            $path = $request->file('file')->storeAs('debug', 'debug_upload.xlsx');

            $importLog = ImportLog::create([
                'filename' => $filename,
                'sumber' => $request->sumber,
                'rows_count' => 0,
            ]);

            $importSummary = [
                'filename' => $filename,
                'sumber' => $request->sumber,
                'processed_rows' => 0,
                'valid_rows' => 0,
                'skipped_rows' => 0,
                'skip_reasons' => [],
                'periods' => [],
                'unique_assets' => 0,
            ];

            if ($request->sumber === 'FUEL') {
                $filePath = Storage::disk('local')->path($path);
                $sheets = Excel::toCollection(new FuelImportCollection, $filePath);

                $transactions = $sheets[0] ?? collect();
                $units = $sheets[1] ?? collect();

                // Build master unit lookup dictionary
                $unitMap = [];
                foreach ($units as $unitRow) {
                    $unitCodeRaw = $unitRow['unitcode'] ?? null;
                    if ($unitCodeRaw) {
                        $unitCodeClean = trim(strtoupper($unitCodeRaw));

                        $unitShortName = isset($unitRow['unitshortname']) ? trim(strtoupper($unitRow['unitshortname'])) : '';
                        $codeUniCalculated = $unitShortName !== '' ? "{$unitCodeClean}-{$unitShortName}" : $unitCodeClean;

                        $companyCode = isset($unitRow['companycode']) ? trim($unitRow['companycode']) : '';
                        $companyShortName = isset($unitRow['companyshortname']) ? trim(strtoupper($unitRow['companyshortname'])) : '';
                        $codeCompanyCalculated = $companyShortName !== '' ? "{$companyCode}-{$companyShortName}" : $companyCode;

                        $unitMap[$unitCodeClean] = [
                            'group' => $unitRow['group'] ?? null,
                            'area' => $unitRow['area'] ?? null,
                            'company_code' => $companyCode,
                            'code_company' => $codeCompanyCalculated,
                            'code_unit' => $codeUniCalculated,
                        ];
                    }
                }

                $rowsImported = 0;
                $rowsSkipped = 0;
                $skipReasons = [];
                $periods = [];
                $unitCodes = [];
                $insertData = [];
                $now = now();

                foreach ($transactions as $row) {
                    $importSummary['processed_rows']++;
                    $unitCodeRaw = $row['unitcode'] ?? null;
                    if (empty($unitCodeRaw) && ! empty($row['internalorder'])) {
                        $unitCodeRaw = substr($row['internalorder'], 0, 4);
                    }

                    if (empty($unitCodeRaw)) {
                        $rowsSkipped++;
                        $skipReasons['Unit code kosong'] = ($skipReasons['Unit code kosong'] ?? 0) + 1;

                        continue;
                    }

                    $unitCodeClean = trim(strtoupper($unitCodeRaw));

                    // Default values
                    $groupAset = null;
                    $area = null;
                    $companyCode = $row['companycode'] ?? null;
                    $codeUnit = $unitCodeClean;
                    $codeCompany = $companyCode;

                    // Match and resolve formulas
                    if (isset($unitMap[$unitCodeClean])) {
                        $ref = $unitMap[$unitCodeClean];
                        $groupAset = $ref['group'] ?? $groupAset;
                        $area = $ref['area'] ?? $area;
                        $companyCode = $ref['company_code'] ?? $companyCode;
                        $codeUnit = $ref['code_unit'] ?? $codeUnit;
                        $codeCompany = $ref['code_company'] ?? $codeCompany;
                    }

                    // Normalize codeCompany / companyCode
                    if ($codeCompany) {
                        $compTrimmed = trim($codeCompany);
                        $knownMaps = [
                            '1100' => '1100-TBP',
                            '1200' => '1200-INK',
                            '1300' => '1300-TLN',
                            '1400' => '1400-SPN',
                            '1500' => '1500-GSA',
                            '1600' => '1600-TPS',
                            '1610' => '1610-MJA',
                            '1700' => '1700-DL',
                            '1800' => '1800-CAP',
                            '1900' => '1900-CDM',
                            '3100' => '3100-SSS',
                            '3200' => '3200-PCS',
                            '3300' => '3300-TAN',
                        ];
                        if (isset($knownMaps[$compTrimmed])) {
                            $codeCompany = $knownMaps[$compTrimmed];
                            $companyCode = $compTrimmed;
                        } else {
                            foreach ($knownMaps as $code => $fullPt) {
                                if (str_starts_with($compTrimmed, $code . '-')) {
                                    $codeCompany = $fullPt;
                                    $companyCode = $code;
                                    break;
                                }
                            }
                        }
                    }

                    // Parse quantity
                    $qtyRaw = $row['sumoftotalquantity'] ?? $row['totalquantity'] ?? $row['total_quantity'] ?? $row['quantity'] ?? $row['qty'] ?? $row['oftotalquantity'] ?? null;
                    if ($qtyRaw === null || $qtyRaw === '' || $qtyRaw === ' ') {
                        $rowsSkipped++;
                        $skipReasons['Quantity kosong'] = ($skipReasons['Quantity kosong'] ?? 0) + 1;

                        continue;
                    }

                    if (is_string($qtyRaw)) {
                        $qtyRaw = str_replace(',', '.', $qtyRaw);
                        $qtyRaw = str_replace(' ', '', $qtyRaw);
                    }
                    $quantity = is_numeric($qtyRaw) ? (float) $qtyRaw : 0;

                    // Parse Year and Month
                    $yearVal = intval($row['year'] ?? now()->year);
                    $monthRaw = trim($row['monthname'] ?? $row['month_name'] ?? $row['month'] ?? now()->format('F'));

                    try {
                        $monthVal = Carbon::parse('1 '.$monthRaw.' '.$yearVal)->format('F');
                    } catch (\Exception $e) {
                        $monthVal = ucfirst(strtolower($monthRaw));
                    }

                    $insertData[] = [
                        'import_log_id' => $importLog->id,
                        'tahun' => $yearVal,
                        'bulan' => $monthVal,
                        'company_code' => $companyCode,
                        'unit_code' => $codeUnit,
                        'internal_order' => $row['internalorder'] ?? $row['internal_order'] ?? null,
                        'material_number' => $row['materialnumber'] ?? $row['material_number'] ?? null,
                        'material_description' => $row['materialdescription'] ?? $row['material_description'] ?? null,
                        'total_quantity' => $quantity,
                        'uom' => $row['uom'] ?? null,
                        'group_aset' => $groupAset,
                        'area' => $area,
                        'code_company' => $codeCompany,
                        'code_unit' => $codeUnit,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $rowsImported++;
                    $periods[$monthVal.' '.$yearVal] = true;
                    $unitCodes[$codeUnit] = true;
                }

                $importSummary['valid_rows'] = $rowsImported;
                $importSummary['skipped_rows'] = $rowsSkipped;
                $importSummary['skip_reasons'] = $skipReasons;
                $importSummary['periods'] = array_keys($periods);
                $importSummary['unique_assets'] = count($unitCodes);

                // Bulk insert in chunks of 500
                if (! empty($insertData)) {
                    foreach (array_chunk($insertData, 500) as $chunk) {
                        FuelTransaction::insert($chunk);
                    }
                }
            } else {
                $countBefore = DataAlat::count();
                $importer = new DataAlatImport($request->sumber, $importLog->id);
                Excel::import($importer, Storage::disk('local')->path($path));

                $countAfter = DataAlat::count();
                $rowsImported = $countAfter - $countBefore;
                $importSummary = array_merge($importSummary, $importer->summary());
                $importSummary['filename'] = $filename;
                $importSummary['sumber'] = $request->sumber;
            }

            $importLog->update([
                'rows_count' => $rowsImported,
            ]);

            $this->updateSummary($importLog->id);

            // Sync master aset
            Artisan::call('app:migrate-master-asets');

            $message = "Data berhasil diimpor! ($rowsImported baris baru ditambahkan)";

            return redirect()->back()
                ->with('success', $message)
                ->with('import_summary', $importSummary);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal impor: '.$e->getMessage());
        }
    }

    public function deleteLog($id)
    {
        try {
            $log = ImportLog::findOrFail($id);

            // Hapus data alat berat yang terkait dengan file ini
            DataAlat::where('import_log_id', $log->id)->delete();

            // Hapus fuel transactions yang terkait
            FuelTransaction::where('import_log_id', $log->id)->delete();

            $filename = $log->filename;
            $log->delete();

            // Hitung ulang summary stats
            MonitoringSummary::truncate();
            $this->updateSummary();

            return redirect()->back()->with('success', "Data dari file '$filename' berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus file: '.$e->getMessage());
        }
    }

    private function updateSummary($importLogId = null)
    {
        $now = now();

        if ($importLogId) {
            $monthsAndYears = DataAlat::where('import_log_id', $importLogId)
                ->select('bulan', 'tahun')
                ->groupBy('bulan', 'tahun')
                ->get();
        } else {
            $monthsAndYears = DataAlat::select('bulan', 'tahun')
                ->groupBy('bulan', 'tahun')
                ->get();
        }

        foreach ($monthsAndYears as $my) {
            // Agregasi per aset per tanggal dalam satu query
            $aggregated = DB::table('data_alat')
                ->where('bulan', $my->bulan)
                ->where('tahun', $my->tahun)
                ->select(
                    'id_aset',
                    'tanggal',
                    DB::raw('MAX(group_aset) as group_aset'),
                    DB::raw('MAX(area) as area'),
                    DB::raw('AVG(waktu_kerja) as avg_waktu_kerja'),
                    DB::raw('AVG(waktu_operasi) as avg_waktu_operasi'),
                    DB::raw('AVG(waktu_idle) as avg_waktu_idle'),
                    DB::raw('AVG(persen_idle) as avg_idle'),
                    DB::raw('SUM(total_bahan_bakar) as sum_bahan_bakar'),
                    DB::raw('AVG(laju_bakar) as avg_bahan_bakar')
                )
                ->groupBy('id_aset', 'tanggal')
                ->get();

            // Siapkan data untuk bulk upsert
            $upsertData = [];
            foreach ($aggregated as $item) {
                if ($item->avg_waktu_kerja !== null || $item->avg_waktu_operasi !== null) {
                    $upsertData[] = [
                        'id_aset' => $item->id_aset,
                        'tanggal' => $item->tanggal,
                        'group_aset' => $item->group_aset,
                        'area' => $item->area,
                        'total_waktu_kerja' => $item->avg_waktu_kerja ?? 0,
                        'total_waktu_operasi' => $item->avg_waktu_operasi ?? 0,
                        'total_waktu_idle' => $item->avg_waktu_idle ?? 0,
                        'rata_idle' => $item->avg_idle ?? 0,
                        'total_bahan_bakar' => $item->sum_bahan_bakar ?? 0,
                        'rata_bahan_bakar' => $item->avg_bahan_bakar ?? 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Bulk upsert dalam chunks — satu query per chunk, bukan per baris
            if (! empty($upsertData)) {
                foreach (array_chunk($upsertData, 500) as $chunk) {
                    MonitoringSummary::upsert(
                        $chunk,
                        ['id_aset', 'tanggal'], // unique key untuk matching
                        ['group_aset', 'area', 'total_waktu_kerja', 'total_waktu_operasi', 'total_waktu_idle', 'rata_idle', 'total_bahan_bakar', 'rata_bahan_bakar', 'updated_at']
                    );
                }
            }
        }
    }

    public function clearData()
    {
        DataAlat::truncate();
        MonitoringSummary::truncate();
        ImportLog::truncate();

        return redirect()->back()->with('success', 'Semua data dan riwayat impor berhasil dihapus!');
    }
}
