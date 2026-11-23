<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$nexus = App\Models\Building::where('name', 'LIKE', '%Nexus%')->first();
$unit = App\Models\Unit::where('unit_code', 'NEX-0501')->first();

echo "Building: " . $nexus->name . " | Code: " . $nexus->building_code . "\n";
echo "default_service_fee_per_sqm: " . ($nexus->default_service_fee_per_sqm ?? 'NULL') . "\n";
echo "service_fee_sqm: " . ($nexus->service_fee_sqm ?? 'NULL') . "\n";
echo "Unit: " . $unit->name . " | Code: " . $unit->unit_code . " | Area: " . $unit->area_sqm . " | Base Price: " . $unit->base_rental_price . "\n";
