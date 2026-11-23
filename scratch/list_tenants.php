<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sales = App\Models\Sale::all();
echo "=== SALES LIST ===\n";
foreach ($sales as $s) {
    echo "Sale: {$s->id} | {$s->name} | {$s->email}\n";
    $blds = $s->buildings()->pluck('name')->join(', ');
    echo "  Assigned buildings: $blds\n";
}

echo "\n=== TENANTS LIST ===\n";
$tenants = App\Models\Tenant::with('createdBySales')->get();
foreach ($tenants as $t) {
    $creator = $t->createdBySales ? $t->createdBySales->email : 'None';
    echo "- {$t->company_name} ({$t->tax_code}) | Created by: $creator | Contracts: {$t->contracts()->count()}\n";
}
