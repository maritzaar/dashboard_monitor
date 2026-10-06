<?php

namespace App\Imports;

use App\Models\FuelBudget;
use App\Models\DataAlat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FuelBudgetImport implements ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    protected int $importLogId;

    protected int $processedRows = 0;

    protected int $validRows = 0;

    protected int $skippedRows = 0;

    protected array $skipReasons = [];

    protected array $periods = [];

    protected array $unitCodes = [];

    protected int $defaultYear;

    protected array $monthMap = [
        'JAN' => 'Jan', 'JANUARI' => 'Jan', 'JANUARY' => 'Jan', '1' => 'Jan', '01' => 'Jan',
        'FEB' => 'Feb', 'FEBRUARI' => 'Feb', 'FEBRUARY' => 'Feb', '2' => 'Feb', '02' => 'Feb',
        'MAR' => 'Mar', 'MARET' => 'Mar', 'MARCH' => 'Mar', '3' => 'Mar', '03' => 'Mar',
        'APR' => 'Apr', 'APRIL' => 'Apr', '4' => 'Apr', '04' => 'Apr',
        'MAY' => 'May', 'MEI' => 'May', '5' => 'May', '05' => 'May',
        'JUN' => 'Jun', 'JUNI' => 'Jun', 'JUNE' => 'Jun', '6' => 'Jun', '06' => 'Jun',
        'JUL' => 'Jul', 'JULI' => 'Jul', 'JULY' => 'Jul', '7' => 'Jul', '07' => 'Jul',
        'AUG' => 'Aug', 'AGUSTUS' => 'Aug', 'AUGUST' => 'Aug', '8' => 'Aug', '08' => 'Aug',
        'SEP' => 'Sep', 'SEPTEMBER' => 'Sep', '9' => 'Sep', '09' => 'Sep',
        'OCT' => 'Oct', 'OKTOBER' => 'Oct', 'OCTOBER' => 'Oct', '10' => 'Oct',
        'NOV' => 'Nov', 'NOVEMBER' => 'Nov', '11' => 'Nov',
        'DEC' => 'Dec', 'DESEMBER' => 'Dec', 'DECEMBER' => 'Dec', '12' => 'Dec',
    ];

    public function __construct(int $importLogId)
    {
        $this->importLogId = $importLogId;
        $this->defaultYear = DataAlat::max('tahun') ?: 2026;
    }

    /**
     * Extract a field from a row using a list of candidate column names.
     * Falls back to fuzzy (alphanumeric-only) matching.
     */
    private function extractField(array $row, array $candidates, $default = null)
    {
        foreach ($candidates as $cand) {
            if (isset($row[$cand]) && $row[$cand] !== null && $row[$cand] !== '') {
                return $row[$cand];
            }
        }

        // Fuzzy match: ignore case, spaces, underscores, dashes, parentheses
        foreach ($row as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            $kClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $k));
            foreach ($candidates as $cand) {
                $candClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $cand));
                if ($kClean === $candClean) {
                    return $v;
                }
            }
        }

        return $default;
    }

    /**
     * Parse a numeric value from various string formats.
     */
    private function parseNum($val): float
    {
        if ($val === null || $val === '') {
            return 0;
        }
        if (is_numeric($val)) {
            return (float) $val;
        }
        $clean = trim(str_replace(' ', '', (string) $val));
        if (strpos($clean, ',') !== false && strpos($clean, '.') !== false) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (strpos($clean, ',') !== false) {
            $clean = str_replace(',', '.', $clean);
        }

        return is_numeric($clean) ? (float) $clean : 0;
    }

    public function model(array $row)
    {
        $this->processedRows++;

        $unit        = $this->extractField($row, ['unit_code', 'unit', 'unitcode', 'id_aset', 'idaset', 'asset_id', 'code_unit', 'codeunit']);
        $io          = $this->extractField($row, ['internal_order', 'internalorder', 'io', 'no_io', 'nomor_io']);
        $groupIo     = $this->extractField($row, ['group_io', 'groupio', 'io_group', 'iogroup', 'group_internal_order']);
        $groupDesc   = $this->extractField($row, ['group_desc', 'groupdesc', 'io_desc', 'iodesc', 'description', 'deskripsi', 'desc']);
        $groupAset   = $this->extractField($row, ['group_aset', 'groupaset', 'group', 'grup']);
        $area        = $this->extractField($row, ['area', 'lokasi', 'site', 'wilayah']);
        $pt          = $this->extractField($row, ['pt', 'company', 'company_code', 'code_company', 'perusahaan']);
        $satuan      = $this->extractField($row, ['satuan', 'uom', 'unit_of_measure']);
        $owner       = $this->extractField($row, ['owner', 'pemilik']);
        $type        = $this->extractField($row, ['type', 'tipe', 'category']);
        $bulanRaw    = $this->extractField($row, ['bulan', 'month', 'month_name', 'monthname']);
        $tahunRaw    = $this->extractField($row, ['tahun', 'year']);
        $kmHmRaw     = $this->extractField($row, ['km_hm', 'kmhm', 'km_or_hm', 'satuan_hm_km', 'km', 'hm']);

        $outputBudget = $this->parseNum($this->extractField($row, ['output_budget', 'outputbudget', 'output_plan', 'budget_output', 'output_budget_hm_km']));
        $outputActual = $this->parseNum($this->extractField($row, ['output_actual', 'outputactual', 'output_aktual', 'outputaktual', 'output', 'actual_output', 'realisasi_output', 'output_realisasi', 'total_kerja']));
        $solarBudget  = $this->parseNum($this->extractField($row, ['solar_budget', 'solarbudget', 'solar_budget_l', 'solar_budget_ltr', 'budget_solar', 'budget_fuel', 'fuel_budget', 'plan_solar']));
        $solarActual  = $this->parseNum($this->extractField($row, ['solar_actual', 'solaractual', 'solar_aktual', 'solaraktual', 'solar_actual_l', 'solar_aktual_l', 'solar', 'fuel_actual', 'actual_fuel', 'fuel_aktual', 'realisasi_solar', 'solar_realisasi', 'pemakaian_solar', 'pemakaian_bbm']));

        // Skip completely empty rows
        if (empty($unit) && empty($io) && empty($bulanRaw)
            && $outputBudget == 0 && $solarBudget == 0
            && $solarActual == 0 && $outputActual == 0) {
            $this->skippedRows++;

            return null;
        }

        // Normalize month
        $bulanNorm = 'Jan';
        if ($bulanRaw) {
            $bUpper    = strtoupper(trim((string) $bulanRaw));
            $bulanNorm = $this->monthMap[$bUpper] ?? ucfirst(strtolower(substr(trim((string) $bulanRaw), 0, 3)));
        }

        $yearVal    = (is_numeric($tahunRaw) && (int) $tahunRaw > 2000) ? (int) $tahunRaw : $this->defaultYear;
        $unitClean  = $unit     ? trim(strtoupper((string) $unit))  : null;
        $ioClean    = $io       ? trim(strtoupper((string) $io))    : null;
        $kmHmClean  = $kmHmRaw  ? trim(strtoupper((string) $kmHmRaw)) : null;

        // Auto-derive group_io if empty and io is formatted like B001MSP001
        if (empty($groupIo) && ! empty($ioClean) && strlen($ioClean) >= 7) {
            $groupIo = substr($ioClean, 4, 3);
        }

        $this->validRows++;
        $this->periods[$bulanNorm . ' ' . $yearVal] = true;
        if ($unitClean) {
            $this->unitCodes[$unitClean] = true;
        }

        return new FuelBudget([
            'import_log_id'        => $this->importLogId,
            'tahun'                => $yearVal,
            'bulan'                => $bulanNorm,
            'group_aset'           => $groupAset,
            'area'                 => $area,
            'pt'                   => $pt,
            'unit_code'            => $unitClean,
            'satuan'               => $satuan,
            'owner'                => $owner,
            'type'                 => $type,
            'internal_order'       => $ioClean,
            'group_internal_order' => $groupIo ?: $groupDesc,
            'km_hm'                => $kmHmClean,
            'output_budget'        => $outputBudget,
            'output_actual'        => $outputActual,
            'solar_budget'         => $solarBudget,
            'solar_actual'         => $solarActual,
        ]);
    }

    public function summary(): array
    {
        return [
            'processed_rows' => $this->processedRows,
            'valid_rows'     => $this->validRows,
            'skipped_rows'   => $this->skippedRows,
            'skip_reasons'   => $this->skipReasons,
            'periods'        => array_keys($this->periods),
            'unique_assets'  => count($this->unitCodes),
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
