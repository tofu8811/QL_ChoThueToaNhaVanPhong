<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pms = App\Models\PropertyManager::all();
echo "=== PROPERTY MANAGERS ===\n";
foreach ($pms as $p) {
    echo "PM: {$p->id} | {$p->name} | {$p->email}\n";
    $blds = $p->buildings()->pluck('name')->join(', ');
    echo "  Assigned buildings: $blds\n";
}
