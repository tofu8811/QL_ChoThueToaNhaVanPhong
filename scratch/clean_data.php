<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenants = App\Models\Tenant::where('company_name', 'like', '%abc%')
    ->orWhere('tax_code', '1234124')
    ->get();

echo "Found junk tenants: " . $tenants->count() . "\n";
foreach ($tenants as $t) {
    echo "- ID: {$t->id}, Name: {$t->company_name}, Tax: {$t->tax_code}\n";
    // Check if any contracts exist
    $hasContracts = App\Models\Contract::where('tenant_id', $t->id)->count();
    echo "  Contracts: $hasContracts\n";
    if ($hasContracts === 0) {
        $t->delete();
        echo "  Deleted successfully.\n";
    }
}
