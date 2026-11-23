<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CONTRACTS TABLE COLUMNS ===\n";
foreach (Illuminate\Support\Facades\Schema::getColumnListing('contracts') as $col) {
    echo $col . "\n";
}

echo "\n=== CONTRACT_UNITS TABLE COLUMNS ===\n";
foreach (Illuminate\Support\Facades\Schema::getColumnListing('contract_units') as $col) {
    echo $col . "\n";
}

echo "\n=== SAMPLE RECENT CONTRACT ===\n";
$sample = App\Models\Contract::with('contractUnits')->latest()->first();
if ($sample) {
    echo json_encode($sample->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
}
