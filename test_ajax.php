<?php 
require 'vendor/autoload.php'; 
$app = require_once 'bootstrap/app.php'; 
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$req = \Illuminate\Http\Request::create('/api/monitoring/filter-options', 'GET', ['pt' => '3300-TAN', 'area' => 'BERAU2', 'group_aset' => 'SGA', 'id_aset' => 'E036-TPE']); 
$controller = new \App\Http\Controllers\MonitoringController(); 
echo json_encode($controller->getFilterOptions($req)->getData());
