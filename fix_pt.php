<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

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

$masterFixed = 0;
$alatFixed = 0;
$fuelFixed = 0;

// 1. Fix master_asets
$masterRows = DB::table('master_asets')->get();
foreach ($masterRows as $row) {
    $companyCode = $row->company_code;
    $currentPt = $row->pt;
    $newCompanyCode = $companyCode;
    $newPt = $currentPt;

    // Determine code from company_code or current pt
    $detectedCode = null;
    if ($companyCode && isset($knownMaps[$companyCode])) {
        $detectedCode = $companyCode;
    } else {
        // Try to extract from pt (e.g. "1200-SBA" -> "1200")
        if ($currentPt) {
            $parts = explode('-', $currentPt);
            $ptCode = trim($parts[0]);
            if (isset($knownMaps[$ptCode])) {
                $detectedCode = $ptCode;
            }
        }
    }

    if ($detectedCode) {
        $newCompanyCode = $detectedCode;
        $newPt = $knownMaps[$detectedCode];
    }

    if ($newCompanyCode !== $companyCode || $newPt !== $currentPt) {
        DB::table('master_asets')
            ->where('id', $row->id)
            ->update([
                'company_code' => $newCompanyCode,
                'pt' => $newPt
            ]);
        $masterFixed++;
    }
}

// Reload master_asets for lookup
$masterAsets = DB::table('master_asets')->get()->keyBy('unit_code');

// 2. Fix data_alat
$dataAlatRows = DB::table('data_alat')->get();
foreach ($dataAlatRows as $row) {
    $currentPt = $row->pt;
    $newPt = $currentPt;

    // Try to find matching asset in master_asets
    $matchingAsset = $masterAsets->get($row->id_aset);
    if ($matchingAsset && !empty($matchingAsset->pt)) {
        $newPt = $matchingAsset->pt;
    } else {
        // Fallback: parse from current pt
        if ($currentPt) {
            $parts = explode('-', $currentPt);
            $ptCode = trim($parts[0]);
            if (isset($knownMaps[$ptCode])) {
                $newPt = $knownMaps[$ptCode];
            }
        }
    }

    if ($newPt !== $currentPt) {
        DB::table('data_alat')
            ->where('id', $row->id)
            ->update(['pt' => $newPt]);
        $alatFixed++;
    }
}

// 3. Fix fuel_transactions
$fuelRows = DB::table('fuel_transactions')->get();
foreach ($fuelRows as $row) {
    $companyCode = $row->company_code;
    $codeCompany = $row->code_company;
    $newCompanyCode = $companyCode;
    $newCodeCompany = $codeCompany;

    $detectedCode = null;
    if ($companyCode && isset($knownMaps[$companyCode])) {
        $detectedCode = $companyCode;
    } else {
        if ($codeCompany) {
            $parts = explode('-', $codeCompany);
            $ptCode = trim($parts[0]);
            if (isset($knownMaps[$ptCode])) {
                $detectedCode = $ptCode;
            }
        }
    }

    if ($detectedCode) {
        $newCompanyCode = $detectedCode;
        $newCodeCompany = $knownMaps[$detectedCode];
    }

    if ($newCompanyCode !== $companyCode || $newCodeCompany !== $codeCompany) {
        DB::table('fuel_transactions')
            ->where('id', $row->id)
            ->update([
                'company_code' => $newCompanyCode,
                'code_company' => $newCodeCompany
            ]);
        $fuelFixed++;
    }
}

echo "Fixed master_asets: $masterFixed, data_alat: $alatFixed, fuel_transactions: $fuelFixed\n";
