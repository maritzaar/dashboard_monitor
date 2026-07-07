<?php

namespace App\Imports;

use App\Models\DataAlat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Carbon\Carbon;

class DataAlatImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    protected $sumber;
    protected $importLogId;

    public function __construct($sumber = 'CATERPILLAR', $importLogId = null)
    {
        $this->sumber = $sumber;
        $this->importLogId = $importLogId;
    }

    public function model(array $row)
    {
        // Debug logging for the first row to trace keys and values
        static $logged = false;
        if (!$logged) {
            \Illuminate\Support\Facades\Log::info('Excel Row Keys: ' . json_encode(array_keys($row)));
            \Illuminate\Support\Facades\Log::info('Excel Row Sample: ' . json_encode($row));
            $logged = true;
        }

        // Ambil ID Aset dengan fallback ke English dan custom/SAP headers
        $idAset = $row['id_aset'] ?? $row['id_asset'] ?? $row['idasset'] ?? $row['id_asset_id'] ?? $row['equipment_id'] ?? $row['asset_id'] ?? $row['code_unit'] ?? $row['internal_order'] ?? $row['serial_number'] ?? $row['nomor_seri'] ?? null;
        if (empty($idAset)) {
            return null;
        }

        // Parse tanggal dengan fallback ke year/month
        try {
            $tanggalRaw = $row['tanggal'] ?? $row['date'] ?? $row['time'] ?? $row['timestamp'] ?? $row['tgl'] ?? null;
            if (empty($tanggalRaw) && (!empty($row['tahun']) || !empty($row['year'])) && (!empty($row['bulan']) || !empty($row['month']))) {
                $monthStr = $row['bulan'] ?? $row['month'];
                $yearVal = $row['tahun'] ?? $row['year'];
                $tanggal = Carbon::parse("1 $monthStr $yearVal");
            } else {
                $tanggal = $this->parseDate($tanggalRaw);
            }
            if (!$tanggal) {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        $bulan = $tanggal->format('F');
        $tahun = $tanggal->year;

        $zonaWaktu = $row['offset_zona_waktu'] ?? $row['zona_waktu'] ?? $row['time_zone'] ?? null;
        $namaZona = $row['nama_tampilan_zona_waktu'] ?? $row['nama_zona'] ?? null;

        if ($zonaWaktu && !is_numeric($zonaWaktu) && str_contains($zonaWaktu, '/')) {
            try {
                $tz = new \DateTimeZone($zonaWaktu);
                $namaZona = $zonaWaktu;
                $dateTime = new \DateTime('now', $tz);
                $zonaWaktu = $dateTime->format('P');
            } catch (\Exception $e) {
                // fallback if timezone parsing fails
            }
        }

        if ($zonaWaktu && strlen($zonaWaktu) > 10) {
            if (!$namaZona) {
                $namaZona = $zonaWaktu;
            }
            $zonaWaktu = substr($zonaWaktu, 0, 10);
        }

        if ($namaZona && strlen($namaZona) > 50) {
            $namaZona = substr($namaZona, 0, 50);
        }

        if ($idAset && strlen($idAset) > 50) {
            $idAset = substr($idAset, 0, 50);
        }

        $nomorSeri = $row['nomor_seri_aset'] ?? $row['nomor_seri'] ?? $row['serial_number'] ?? $row['asset_serial_number'] ?? $idAset;
        if ($nomorSeri && strlen($nomorSeri) > 50) {
            $nomorSeri = substr($nomorSeri, 0, 50);
        }

        $buatan = $row['buatan'] ?? $row['make'] ?? $row['manufacturer'] ?? 'CAT';
        if ($buatan && strlen($buatan) > 50) {
            $buatan = substr($buatan, 0, 50);
        }

        $model = $row['model'] ?? $row['equipment_model'] ?? $row['group_d'] ?? $row['group_desc'] ?? $row['nama_alat'] ?? $row['namaalat'] ?? 'UNKNOWN';
        if ($model && strlen($model) > 50) {
            $model = substr($model, 0, 50);
        }

        $groupAset = $row['group'] ?? $row['group_aset'] ?? null;
        if ($groupAset && strlen($groupAset) > 50) {
            $groupAset = substr($groupAset, 0, 50);
        }

        $area = $row['area'] ?? null;
        if ($area && strlen($area) > 50) {
            $area = substr($area, 0, 50);
        }

        $pt = $row['pt'] ?? $row['comp_name'] ?? $row['compname'] ?? $row['comp_cod'] ?? $row['compcod'] ?? $row['companycode'] ?? $row['company_code'] ?? null;
        if ($pt && strlen($pt) > 50) {
            $pt = substr($pt, 0, 50);
        }

        $internalOrder = $row['internal_order'] ?? $row['internal_ord'] ?? $row['internalord'] ?? null;
        if ($internalOrder && strlen($internalOrder) > 50) {
            $internalOrder = substr($internalOrder, 0, 50);
        }

        $groupInternalOrder = $row['group_internal_order'] ?? $row['io_group'] ?? $row['iogroup'] ?? null;
        if ($groupInternalOrder && strlen($groupInternalOrder) > 50) {
            $groupInternalOrder = substr($groupInternalOrder, 0, 50);
        }

        $groupDesc = $row['group_desc'] ?? $row['io_desc'] ?? $row['iodesc'] ?? null;
        if ($groupDesc && strlen($groupDesc) > 100) {
            $groupDesc = substr($groupDesc, 0, 100);
        }

        $waktuOperasi = $this->parseNumeric($row['waktu_operasi_jam'] ?? $row['waktu_operasi'] ?? $row['operating_hours'] ?? $row['operating_time'] ?? $row['operating_time_hours'] ?? $row['quantity_operasi'] ?? $row['opr'] ?? null);
        $waktuIdle = $this->parseNumeric($row['waktu_idle_jam'] ?? $row['waktu_idle'] ?? $row['idle_hours'] ?? $row['idle_time'] ?? $row['idle_time_hours'] ?? $row['quantity_idle'] ?? $row['idle'] ?? null);
        $waktuKerja = $this->parseNumeric($row['waktu_kerja_jam'] ?? $row['waktu_kerja'] ?? $row['working_hours'] ?? $row['working_time'] ?? $row['working_time_hours'] ?? $row['quantity_kerja'] ?? $row['work_hrs'] ?? $row['workhrs'] ?? null);

        $persenIdle = $this->parseNumeric($row['_idle'] ?? $row['persen_idle'] ?? $row['idle_percent'] ?? $row['idle_percentage'] ?? $row['rasio'] ?? $row['ratio'] ?? null);
        if (is_null($persenIdle) && $waktuOperasi > 0) {
            $persenIdle = ($waktuIdle / $waktuOperasi) * 100;
        }

        $totalBahanBakar = $this->parseNumeric($row['total_bahan_bakar_yang_terbakar_l'] ?? $row['total_bahan_bakar'] ?? $row['total_fuel_burned'] ?? $row['total_fuel_burned_l'] ?? $row['fuel_burned'] ?? $row['actul'] ?? $row['actual'] ?? $row['total_fuel'] ?? $row['totalfuel'] ?? $row['fueling'] ?? null);
        $lajuBakar = $this->parseNumeric($row['laju_total_pembakaran_bahan_bakar_l_jam'] ?? $row['laju_bakar'] ?? $row['average_fuel_rate'] ?? $row['average_fuel_rate_l_hr'] ?? $row['fuel_rate'] ?? null);
        if (is_null($lajuBakar) && $totalBahanBakar && $waktuOperasi > 0) {
            $lajuBakar = $totalBahanBakar / $waktuOperasi;
        }

        return new DataAlat([
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
            'keterangan' => $row['keterangan'] ?? null,
            'id_aset' => $idAset,
            'nomor_seri' => $nomorSeri,
            'buatan' => $buatan,
            'model' => $model,
            'group_aset' => $groupAset,
            'area' => $area,
            'pt' => $pt,
            'internal_order' => $internalOrder,
            'group_internal_order' => $groupInternalOrder,
            'group_desc' => $groupDesc,
            'meteran_jam' => $this->parseNumeric($row['meteran_jam_jam'] ?? $row['meteran_jam'] ?? $row['hour_meter'] ?? $row['hour_meter_hours'] ?? $row['output'] ?? null),
            'waktu_terakhir' => $this->parseDateTime($row['waktu_terakhir_dilaporkan_meteran_jam'] ?? $row['waktu_terakhir'] ?? $row['last_reported_time'] ?? $row['last_reported'] ?? null),
            'laporan_pemanfaatan' => $this->parseDateTime($row['laporan_pemanfaatan_terakhir'] ?? $row['laporan_pemanfaatan'] ?? null),
            'zona_waktu' => $zonaWaktu,
            'nama_zona' => $namaZona,
            'waktu_operasi' => $waktuOperasi,
            'waktu_idle' => $waktuIdle,
            'waktu_kerja' => $waktuKerja,
            'persen_idle' => $persenIdle,
            'total_bahan_bakar' => $totalBahanBakar,
            'laju_bakar' => $lajuBakar,
            'daya_dihasilkan' => $this->parseNumeric($row['daya_dihasilkan_kwh'] ?? $row['daya_dihasilkan'] ?? null),
            'beban_harian' => $this->parseNumeric($row['beban_harian_rata_rata'] ?? $row['beban_harian'] ?? null),
            'daya_per_unit' => $this->parseNumeric($row['daya_per_unit_bahan_bakar_kwh_l'] ?? $row['daya_per_unit'] ?? null),
            'sumber_data' => $this->sumber,
            'import_log_id' => $this->importLogId,
        ]);
    }

    private function parseNumeric($value)
    {
        if (is_null($value) || $value === '' || $value === ' ') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
            $value = str_replace(' ', '', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseDateTime($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($value - 2);
            }

            $formats = ['d/m/Y H:i:s', 'm/d/Y H:i:s', 'Y-m-d H:i:s', 'd/m/Y', 'm/d/Y', 'Y-m-d'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::createFromFormat('Y-m-d', '1900-01-01')->addDays($value - 2);
            }

            $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd/m/Y H:i:s', 'm/d/Y H:i:s', 'Y-m-d H:i:s'];
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $value);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}