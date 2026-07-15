<?php

namespace App\Imports;

use App\Models\DataAlat;
use App\Models\MasterAset;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DataAlatSheetImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    protected $sumber;

    protected $importLogId;

    protected int $processedRows = 0;

    protected int $validRows = 0;

    protected array $skipReasons = [];

    protected array $periods = [];

    protected array $assetIds = [];

    protected array $assetsMap = [];

    protected ?string $detectedFormat = null;

    protected ?array $headerMap = null;

    public function __construct($sumber = 'CATERPILLAR', $importLogId = null)
    {
        $this->sumber = $sumber;
        $this->importLogId = $importLogId;

        try {
            $masters = MasterAset::all();
            foreach ($masters as $m) {
                $mapData = [
                    'unit_code' => $m->unit_code,
                    'group_aset' => $m->group_aset,
                    'area' => $m->area,
                    'internal_order' => $m->internal_order,
                    'group_internal_order' => $m->group_internal_order,
                    'pt' => ! empty($m->pt) ? $m->pt : $m->company_code,
                ];

                $unitCodeUpper = strtoupper($m->unit_code);
                if (! isset($this->assetsMap[$unitCodeUpper]) || (empty($this->assetsMap[$unitCodeUpper]['internal_order']) && ! empty($m->internal_order))) {
                    $this->assetsMap[$unitCodeUpper] = $mapData;
                }

                if (! empty($m->nomor_seri)) {
                    $serialUpper = strtoupper($m->nomor_seri);
                    if (! isset($this->assetsMap[$serialUpper]) || (empty($this->assetsMap[$serialUpper]['internal_order']) && ! empty($m->internal_order))) {
                        $this->assetsMap[$serialUpper] = $mapData;
                    }
                }

                $parts = explode('-', $m->unit_code);
                $short = strtoupper($parts[1] ?? $parts[0] ?? '');
                if (! empty($short)) {
                    if (! isset($this->assetsMap[$short]) || (empty($this->assetsMap[$short]['internal_order']) && ! empty($m->internal_order))) {
                        $this->assetsMap[$short] = $mapData;
                    }
                }
            }
        } catch (\Exception $e) {
            // Silence if table is not migrated or database is not available
        }
    }

    /**
     * Build a mapping from known field purposes to actual header keys.
     * Handles cases where header cells contain Excel formulas
     * (e.g. XLOOKUP) that get mangled by the PHP reader.
     */
    private function buildHeaderMap(array $row): array
    {
        if ($this->headerMap !== null) {
            return $this->headerMap;
        }

        $keys = array_keys($row);
        $map = [];

        $knownMappings = [
            'tahun' => ['tahun', 'year'],
            'bulan' => ['bulan', 'month'],
            'tanggal' => ['tanggal', 'date', 'tgl', 'timestamp'],
            'id_aset' => ['id_aset', 'idaset', 'asset_id', 'keterangan'],
            'nomor_seri' => ['nomor_seri', 'nomor_seri_aset', 'serial_number', 'sn'],
            'internal_order' => ['internal_order', 'internal_os', 'internal_o', 'internalorder'],
            'group' => ['group', 'group_aset', 'groupaset'],
            'area' => ['area'],
            'code_unit' => ['code_unit', 'code_uc', 'codeuc', 'code_uni'],
            'code_cop' => ['code_cop', 'code_company'],
            'io_group' => ['io_group', 'io_grou', 'iogroup', 'group_internal_order'],
            'io_desc' => ['io_desc', 'io_d', 'iodesc', 'group_desc'],
            'buatan' => ['buatan', 'manufacturer', 'mfg'],
            'model' => ['model'],
            'meteran_jam' => ['meteran_jam_jam', 'meteran_jam', 'hour_meter'],
            'waktu_terakhir' => ['waktu_terakhir_dilaporkan_meteran_jam', 'waktu_terakhir', 'last_reported'],
            'laporan_pemanfaatan' => ['laporan_pemanfaatan_terakhir', 'laporan_pemanfaatan', 'last_utilization'],
            'offset_zona_waktu' => ['offset_zona_waktu', 'timezone_offset'],
            'zona_waktu' => ['zona_waktu', 'timezone'],
            'nama_zona' => ['nama_tampilan_zona_waktu', 'timezone_name', 'nama_zona'],
            'waktu_operasi' => ['waktu_operasi_jam', 'waktu_operasi', 'operating_hours'],
            'waktu_idle' => ['waktu_idle_jam', 'waktu_idle', 'idle_hours'],
            'waktu_kerja' => ['waktu_kerja_jam', 'waktu_kerja', 'working_hours'],
            'persen_idle' => ['idle', 'persen_idle', 'idle_percent'],
            'total_bahan_bakar' => ['total_bahan_bakar_yang_terbakar_l', 'total_bahan_bakar', 'total_fuel'],
            'laju_bakar' => ['laju_total_pembakaran_bahan_bakar_ljam', 'laju_bakar', 'fuel_rate'],
            'daya_dihasilkan' => ['daya_dihasilkan_kwh', 'daya_dihasilkan', 'power_generated'],
            'beban_harian' => ['beban_harian_rata_rata', 'beban_harian', 'daily_load'],
            'daya_per_unit' => ['daya_per_unit_bahan_bakar_kwhl', 'daya_per_unit', 'power_per_fuel'],
            'pt' => ['pt', 'comp_name', 'compname', 'company_code', 'companycode'],
            'keterangan_col' => ['keterangan'],
        ];

        // First pass: match known column names
        $mappedKeys = [];
        foreach ($knownMappings as $purpose => $aliases) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $row) && ! in_array($alias, $mappedKeys)) {
                    $map[$purpose] = $alias;
                    $mappedKeys[] = $alias;
                    break;
                }
            }
        }

        // Second pass: detect formula-mangled headers
        // Look for any unmapped key that contains formula-like patterns
        $unmappedKeys = array_diff($keys, $mappedKeys);
        foreach ($unmappedKeys as $key) {
            $keyLower = strtolower($key);
            if (preg_match('/(xlfn|xlookup|iferror|vlookup|hlookup|index|match)/', $keyLower)) {
                $keyIndex = array_search($key, $keys);
                // Position 3 in extended format = ID Aset (unit name from XLOOKUP)
                if ($keyIndex === 3 && ! isset($map['id_aset'])) {
                    $map['id_aset'] = $key;
                    $mappedKeys[] = $key;
                }
            }
        }

        // Fallback: if id_aset still not found, try position 3
        if (! isset($map['id_aset'])) {
            if (isset($keys[3]) && ! in_array($keys[3], $mappedKeys)) {
                $testVal = $row[$keys[3]] ?? '';
                if (! empty($testVal) && ! is_numeric($testVal)) {
                    $map['id_aset'] = $keys[3];
                }
            }
        }

        $this->headerMap = $map;

        return $map;
    }

    /**
     * Get a field value using the header map.
     * Automatically skips unresolved Excel formulas (values starting with '=').
     */
    private function getMappedField(array $row, string $purpose, $default = null)
    {
        $map = $this->headerMap ?? $this->buildHeaderMap($row);

        if (! isset($map[$purpose])) {
            return $default;
        }

        $key = $map[$purpose];
        $value = $row[$key] ?? $default;

        if (is_string($value) && str_starts_with($value, '=')) {
            return $default;
        }

        return $value;
    }

    /**
     * Detect format based on available columns.
     */
    private function detectFormat(array $row): string
    {
        if ($this->detectedFormat !== null) {
            return $this->detectedFormat;
        }

        $map = $this->buildHeaderMap($row);

        $hasCodeUnit = isset($map['code_unit']);
        $hasGroup = isset($map['group']);
        $hasInternalOrder = isset($map['internal_order']);
        $hasIOGroup = isset($map['io_group']);
        $hasKeterangan = isset($map['keterangan_col']);

        if ($hasCodeUnit || ($hasGroup && $hasInternalOrder) || ($hasIOGroup && $hasInternalOrder)) {
            $this->detectedFormat = 'extended';

            return 'extended';
        }

        if ($hasKeterangan) {
            $keteranganVal = $row[$map['keterangan_col']] ?? '';
            if (! empty(trim($keteranganVal))) {
                $kLower = strtolower($keteranganVal);
                $isError = str_contains($kLower, 'insufficient') ||
                           str_contains($kLower, 'invalid') ||
                           str_contains($kLower, 'missing') ||
                           str_contains($kLower, 'value includes') ||
                           str_contains($kLower, 'accumulated');
                if (! $isError) {
                    $this->detectedFormat = 'legacy_shifted';

                    return 'legacy_shifted';
                }
            }
        }

        $this->detectedFormat = 'legacy_unshifted';

        return 'legacy_unshifted';
    }

    public function model(array $row)
    {
        $this->processedRows++;

        if ($this->headerMap === null) {
            $this->buildHeaderMap($row);
        }

        $format = $this->detectFormat($row);

        if ($format === 'extended') {
            $unitCode = $this->getMappedField($row, 'id_aset');
            $serialNumber = $this->getMappedField($row, 'nomor_seri');
            $manufacturer = $this->getMappedField($row, 'buatan', 'CAT');
            $model = $this->getMappedField($row, 'model', 'UNKNOWN');
            $keteranganValue = $this->getMappedField($row, 'keterangan_col');

            $directGroup = $this->getMappedField($row, 'group');
            $directArea = $this->getMappedField($row, 'area');
            $directCodeUnit = $this->getMappedField($row, 'code_unit');
            $directInternalOrder = $this->getMappedField($row, 'internal_order');
            $directIOGroup = $this->getMappedField($row, 'io_group');
            $directIODesc = $this->getMappedField($row, 'io_desc');
            $directPT = $this->getMappedField($row, 'pt') ?? $this->getMappedField($row, 'code_cop') ?? $this->getMappedField($row, 'company_code');

            $meteranJam = $this->getMappedField($row, 'meteran_jam');
            $waktuTerakhir = $this->getMappedField($row, 'waktu_terakhir');
            $laporanPemanfaatan = $this->getMappedField($row, 'laporan_pemanfaatan');
            $offsetZonaWaktu = $this->getMappedField($row, 'offset_zona_waktu');
            $zonaWaktu = $this->getMappedField($row, 'zona_waktu');
            $namaTampilanZona = $this->getMappedField($row, 'nama_zona');
            $waktuOperasi = $this->getMappedField($row, 'waktu_operasi');
            $waktuIdle = $this->getMappedField($row, 'waktu_idle');
            $waktuKerja = $this->getMappedField($row, 'waktu_kerja');
            $persenIdle = $this->getMappedField($row, 'persen_idle');
            $totalBahanBakar = $this->getMappedField($row, 'total_bahan_bakar');
            $lajuBakar = $this->getMappedField($row, 'laju_bakar');
            $dayaDihasilkan = $this->getMappedField($row, 'daya_dihasilkan');
            $bebanHarian = $this->getMappedField($row, 'beban_harian');
            $dayaPerUnit = $this->getMappedField($row, 'daya_per_unit');

        } elseif ($format === 'legacy_shifted') {
            $keteranganRaw = $row['keterangan'] ?? '';
            $keteranganLower = strtolower($keteranganRaw);
            $hasErrorKeyword = str_contains($keteranganLower, 'insufficient') ||
                               str_contains($keteranganLower, 'invalid') ||
                               str_contains($keteranganLower, 'missing') ||
                               str_contains($keteranganLower, 'value includes') ||
                               str_contains($keteranganLower, 'accumulated');

            if ($hasErrorKeyword) {
                $unitCode = $row['id_aset'] ?? null;
                $serialNumber = $row['nomor_seri_aset'] ?? null;
                $manufacturer = $row['buatan'] ?? 'CAT';
                $model = $row['model'] ?? 'UNKNOWN';
                $keteranganValue = $keteranganRaw;
                $meteranJam = $row['meteran_jam_jam'] ?? null;
                $waktuTerakhir = $row['waktu_terakhir_dilaporkan_meteran_jam'] ?? null;
                $laporanPemanfaatan = $row['laporan_pemanfaatan_terakhir'] ?? null;
                $offsetZonaWaktu = $row['offset_zona_waktu'] ?? null;
                $zonaWaktu = $row['zona_waktu'] ?? null;
                $namaTampilanZona = $row['nama_tampilan_zona_waktu'] ?? null;
                $waktuOperasi = $row['waktu_operasi_jam'] ?? null;
                $waktuIdle = $row['waktu_idle_jam'] ?? null;
                $waktuKerja = $row['waktu_kerja_jam'] ?? null;
                $persenIdle = $row['idle'] ?? null;
                $totalBahanBakar = $row['total_bahan_bakar_yang_terbakar_l'] ?? null;
                $lajuBakar = $row['laju_total_pembakaran_bahan_bakar_ljam'] ?? null;
                $dayaDihasilkan = $row['daya_dihasilkan_kwh'] ?? null;
                $bebanHarian = $row['beban_harian_rata_rata'] ?? null;
                $dayaPerUnit = $row['daya_per_unit_bahan_bakar_kwhl'] ?? null;
            } else {
                $unitCode = trim($row['keterangan']);
                $serialNumber = $row['id_aset'] ?? null;
                $manufacturer = $row['nomor_seri_aset'] ?? 'CAT';
                $model = $row['buatan'] ?? 'UNKNOWN';
                $keteranganValue = null;
                $meteranJam = $row['model'] ?? null;
                $waktuTerakhir = $row['meteran_jam_jam'] ?? null;
                $laporanPemanfaatan = $row['waktu_terakhir_dilaporkan_meteran_jam'] ?? null;
                $offsetZonaWaktu = $row['laporan_pemanfaatan_terakhir'] ?? null;
                $zonaWaktu = $row['offset_zona_waktu'] ?? null;
                $namaTampilanZona = $row['zona_waktu'] ?? null;
                $waktuOperasi = $row['nama_tampilan_zona_waktu'] ?? null;
                $waktuIdle = $row['waktu_operasi_jam'] ?? null;
                $waktuKerja = $row['waktu_idle_jam'] ?? null;
                $persenIdle = $row['waktu_kerja_jam'] ?? null;
                $totalBahanBakar = $row['idle'] ?? null;
                $lajuBakar = $row['total_bahan_bakar_yang_terbakar_l'] ?? null;
                $dayaDihasilkan = $row['laju_total_pembakaran_bahan_bakar_ljam'] ?? null;
                $bebanHarian = $row['daya_dihasilkan_kwh'] ?? null;
                $dayaPerUnit = $row['beban_harian_rata_rata'] ?? null;
            }

            $directGroup = null;
            $directArea = null;
            $directCodeUnit = null;
            $directInternalOrder = null;
            $directIOGroup = null;
            $directIODesc = null;
            $directPT = null;

        } else {
            $unitCode = $row['id_aset'] ?? null;
            $serialNumber = $row['nomor_seri_aset'] ?? null;
            $manufacturer = $row['buatan'] ?? 'CAT';
            $model = $row['model'] ?? 'UNKNOWN';
            $keteranganValue = null;
            $meteranJam = $row['meteran_jam_jam'] ?? null;
            $waktuTerakhir = $row['waktu_terakhir_dilaporkan_meteran_jam'] ?? null;
            $laporanPemanfaatan = $row['laporan_pemanfaatan_terakhir'] ?? null;
            $offsetZonaWaktu = $row['offset_zona_waktu'] ?? null;
            $zonaWaktu = $row['zona_waktu'] ?? null;
            $namaTampilanZona = $row['nama_tampilan_zona_waktu'] ?? null;
            $waktuOperasi = $row['waktu_operasi_jam'] ?? null;
            $waktuIdle = $row['waktu_idle_jam'] ?? null;
            $waktuKerja = $row['waktu_kerja_jam'] ?? null;
            $persenIdle = $row['idle'] ?? null;
            $totalBahanBakar = $row['total_bahan_bakar_yang_terbakar_l'] ?? null;
            $lajuBakar = $row['laju_total_pembakaran_bahan_bakar_ljam'] ?? null;
            $dayaDihasilkan = $row['daya_dihasilkan_kwh'] ?? null;
            $bebanHarian = $row['beban_harian_rata_rata'] ?? null;
            $dayaPerUnit = $row['daya_per_unit_bahan_bakar_kwhl'] ?? null;

            $directGroup = null;
            $directArea = null;
            $directCodeUnit = null;
            $directInternalOrder = null;
            $directIOGroup = null;
            $directIODesc = null;
            $directPT = null;
        }

        if (empty($unitCode)) {
            $this->recordSkip('ID aset kosong');

            return null;
        }

        // ===== PARSE DATE =====
        try {
            $tanggalRaw = $this->getMappedField($row, 'tanggal')
                       ?? $this->getMappedField($row, 'waktu_terakhir')
                       ?? $this->getMappedField($row, 'laporan_pemanfaatan');
            if ($format !== 'extended') {
                $tanggalRaw = $tanggalRaw ?? $row['tanggal'] ?? $row['date'] ?? $row['time'] ?? $row['timestamp'] ?? $row['tgl'] ?? null;
                if (empty($tanggalRaw)) {
                    $tanggalRaw = $waktuTerakhir ?? $laporanPemanfaatan ?? null;
                }
            }

            $tahunRaw = $this->getMappedField($row, 'tahun');
            $bulanRaw = $this->getMappedField($row, 'bulan');

            if (empty($tanggalRaw) && ! empty($tahunRaw) && ! empty($bulanRaw)) {
                $tanggal = Carbon::parse("1 $bulanRaw $tahunRaw");
            } else {
                $tanggal = $this->parseDate($tanggalRaw);
            }

            if (! $tanggal && ! empty($tahunRaw) && ! empty($bulanRaw)) {
                try {
                    $tanggal = Carbon::parse("1 $bulanRaw $tahunRaw");
                } catch (\Exception $e) { /* ignore */
                }
            }

            if (! $tanggal) {
                $this->recordSkip('Tanggal tidak valid (raw: '.substr((string) ($tanggalRaw ?? 'null'), 0, 30).')');

                return null;
            }
        } catch (\Exception $e) {
            $this->recordSkip('Tanggal error: '.substr($e->getMessage(), 0, 50));

            return null;
        }

        if (empty($bulanRaw)) {
            $bulan = $tanggal->format('F');
        } else {
            try {
                $bulan = Carbon::parse("1 $bulanRaw 2025")->format('F');
            } catch (\Exception $e) {
                $bulan = $tanggal->format('F');
            }
        }

        $tahun = ! empty($tahunRaw) && is_numeric($tahunRaw) ? (int) $tahunRaw : $tanggal->year;

        // ===== RESOLVE METADATA =====
        $short = '';
        if ($unitCode) {
            $parts = explode(' ', trim($unitCode));
            $short = strtoupper($parts[0] ?? '');
        }

        $mapped = null;
        $serialUpper = $serialNumber ? strtoupper(trim($serialNumber)) : '';
        $unitCodeUpper = $unitCode ? strtoupper(trim($unitCode)) : '';

        if (! empty($serialUpper) && isset($this->assetsMap[$serialUpper])) {
            $mapped = $this->assetsMap[$serialUpper];
        } elseif (! empty($unitCodeUpper) && isset($this->assetsMap[$unitCodeUpper])) {
            $mapped = $this->assetsMap[$unitCodeUpper];
        } elseif (! empty($short) && isset($this->assetsMap[$short])) {
            $mapped = $this->assetsMap[$short];
        }

        if ($mapped) {
            $idAset = $mapped['unit_code'];

            // Force group child units (e.g. AME 01) into their parent unit code (e.g. E005-AME) if available
            $parts = explode(' ', trim($idAset));
            $shortChild = strtoupper($parts[0] ?? '');
            if (! empty($shortChild) && isset($this->assetsMap[$shortChild])) {
                $parentMap = $this->assetsMap[$shortChild];
                if (str_contains($parentMap['unit_code'], '-')) {
                    $idAset = $parentMap['unit_code'];
                }
            }

            // Fallback to Excel value if MasterAset is empty
            $groupAset = ! empty($mapped['group_aset']) ? $mapped['group_aset'] : $directGroup;
            $area = ! empty($mapped['area']) ? $mapped['area'] : $directArea;
            $internalOrder = ! empty($mapped['internal_order']) ? $mapped['internal_order'] : $directInternalOrder;
            $groupInternalOrder = ! empty($mapped['group_internal_order']) ? $mapped['group_internal_order'] : $directIOGroup;
            $pt = ! empty($mapped['pt']) ? $mapped['pt'] : $directPT;
        } else {
            $idAset = $unitCode;
            $groupAset = $directGroup ?? ($row['group'] ?? $row['group_aset'] ?? null);
            $area = $directArea ?? ($row['area'] ?? null);
            $internalOrder = $directInternalOrder ?? ($row['internal_order'] ?? $row['internal_ord'] ?? $row['internalord'] ?? null);
            $groupInternalOrder = $directIOGroup ?? ($row['group_internal_order'] ?? $row['io_group'] ?? $row['iogroup'] ?? null);
            $pt = $directPT ?? ($row['pt'] ?? $row['comp_name'] ?? $row['compname'] ?? $row['comp_cod'] ?? $row['compcod'] ?? $row['companycode'] ?? $row['company_code'] ?? null);
        }

        if ($format === 'extended') {
            if (! empty($directGroup)) {
                $groupAset = $directGroup;
            }
            if (! empty($directArea)) {
                $area = $directArea;
            }
            if (! empty($directInternalOrder)) {
                $internalOrder = $directInternalOrder;
            }
            if (! empty($directIOGroup)) {
                $groupInternalOrder = $directIOGroup;
            }
            if (! empty($directPT)) {
                $pt = $directPT;
            }
        }

        if (! empty($directCodeUnit)) {
            $idAset = $directCodeUnit;
        }

        // ===== NORMALIZE PT / COMPANY CODE =====
        if ($pt) {
            $ptTrimmed = trim($pt);
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
            if (isset($knownMaps[$ptTrimmed])) {
                $pt = $knownMaps[$ptTrimmed];
            } else {
                foreach ($knownMaps as $code => $fullPt) {
                    if (str_starts_with($ptTrimmed, $code . '-')) {
                        $pt = $fullPt;
                        break;
                    }
                }
            }
        }

        // ===== LIMIT FIELD SIZES =====
        $idAset = $idAset ? substr($idAset, 0, 50) : $idAset;
        $serialNumber = $serialNumber ? substr($serialNumber, 0, 50) : $serialNumber;
        $manufacturer = $manufacturer ? substr($manufacturer, 0, 50) : $manufacturer;
        $model = $model ? substr($model, 0, 50) : $model;
        $groupAset = $groupAset ? substr($groupAset, 0, 50) : $groupAset;
        $area = $area ? substr($area, 0, 50) : $area;
        $pt = $pt ? substr($pt, 0, 50) : $pt;
        $internalOrder = $internalOrder ? substr($internalOrder, 0, 50) : $internalOrder;
        $groupInternalOrder = $groupInternalOrder ? substr($groupInternalOrder, 0, 50) : $groupInternalOrder;

        // ===== TIMEZONE =====
        $zonaWaktuVal = $offsetZonaWaktu ?? $zonaWaktu ?? null;
        $namaZonaVal = $namaTampilanZona ?? $zonaWaktu ?? null;

        if ($zonaWaktuVal && ! is_numeric($zonaWaktuVal) && str_contains($zonaWaktuVal, '/')) {
            try {
                $tz = new \DateTimeZone($zonaWaktuVal);
                $namaZonaVal = $zonaWaktuVal;
                $dateTime = new \DateTime('now', $tz);
                $zonaWaktuVal = $dateTime->format('P');
            } catch (\Exception $e) { /* ignore */
            }
        }

        if ($zonaWaktuVal && strlen($zonaWaktuVal) > 10) {
            if (! $namaZonaVal) {
                $namaZonaVal = $zonaWaktuVal;
            }
            $zonaWaktuVal = substr($zonaWaktuVal, 0, 10);
        }
        if ($namaZonaVal && strlen($namaZonaVal) > 50) {
            $namaZonaVal = substr($namaZonaVal, 0, 50);
        }

        $this->validRows++;
        $this->periods[$bulan.' '.$tahun] = true;
        $this->assetIds[$idAset] = true;

        $parsedWaktuOperasi = $this->parseNumeric($waktuOperasi);
        $parsedWaktuIdle = $this->parseNumeric($waktuIdle);
        $parsedWaktuKerja = $this->parseNumeric($waktuKerja);

        $parsedPersenIdle = $this->parseNumeric($persenIdle);
        if (is_null($parsedPersenIdle) && $parsedWaktuOperasi > 0) {
            $parsedPersenIdle = ($parsedWaktuIdle / $parsedWaktuOperasi) * 100;
        }

        $parsedTotalBahanBakar = $this->parseNumeric($totalBahanBakar);
        $parsedLajuBakar = $this->parseNumeric($lajuBakar);
        if (is_null($parsedLajuBakar) && $parsedTotalBahanBakar && $parsedWaktuOperasi > 0) {
            $parsedLajuBakar = $parsedTotalBahanBakar / $parsedWaktuOperasi;
        }

        $groupDesc = $directIODesc ?? ($row['group_desc'] ?? null);

        return new DataAlat([
            'tahun' => $tahun,
            'bulan' => $bulan,
            'tanggal' => $tanggal,
            'keterangan' => $keteranganValue,
            'id_aset' => $idAset,
            'nomor_seri' => $serialNumber,
            'buatan' => $manufacturer,
            'model' => $model,
            'group_aset' => $groupAset,
            'area' => $area,
            'pt' => $pt,
            'internal_order' => $internalOrder,
            'group_internal_order' => $groupInternalOrder,
            'group_desc' => $groupDesc,
            'meteran_jam' => $this->parseNumeric($meteranJam),
            'waktu_terakhir' => $this->parseDateTime($waktuTerakhir),
            'laporan_pemanfaatan' => $this->parseDateTime($laporanPemanfaatan),
            'zona_waktu' => $zonaWaktuVal,
            'nama_zona' => $namaZonaVal,
            'waktu_operasi' => $parsedWaktuOperasi,
            'waktu_idle' => $parsedWaktuIdle,
            'waktu_kerja' => $parsedWaktuKerja,
            'persen_idle' => $parsedPersenIdle,
            'total_bahan_bakar' => $parsedTotalBahanBakar,
            'laju_bakar' => $parsedLajuBakar,
            'daya_dihasilkan' => $this->parseNumeric($dayaDihasilkan),
            'beban_harian' => $this->parseNumeric($bebanHarian),
            'daya_per_unit' => $this->parseNumeric($dayaPerUnit),
            'sumber_data' => $this->sumber,
            'import_log_id' => $this->importLogId,
        ]);
    }

    private function recordSkip(string $reason): void
    {
        $this->skipReasons[$reason] = ($this->skipReasons[$reason] ?? 0) + 1;
    }

    public function summary(): array
    {
        return [
            'processed_rows' => $this->processedRows,
            'valid_rows' => $this->validRows,
            'skipped_rows' => max(0, $this->processedRows - $this->validRows),
            'skip_reasons' => $this->skipReasons,
            'periods' => array_keys($this->periods),
            'unique_assets' => count($this->assetIds),
            'detected_format' => $this->detectedFormat,
        ];
    }

    private function parseNumeric($value)
    {
        if (is_null($value) || $value === '' || $value === ' ') {
            return null;
        }
        if (is_string($value) && str_starts_with($value, '=')) {
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
        if (is_string($value) && str_starts_with($value, '=')) {
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
        if (is_string($value) && str_starts_with($value, '=')) {
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
