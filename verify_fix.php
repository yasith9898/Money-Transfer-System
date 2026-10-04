<?php

use App\Models\Account;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// New Dashboard Logic
$dashboardCount = Account::where(function($query) {
        $query->whereHas('user', function($q) {
            $q->whereNotIn('role', ['admin', 'super_admin']);
        })
        ->orWhere('wallet_type', 'main_company');
    })
    ->where(function($q) {
        $q->whereNull('wallet_type')
          ->orWhere('wallet_type', '!=', 'office');
    })
    ->count();

// Accounts Page Logic
$accountsPageCount = Account::with(['user'])
    ->where(function($query) {
        $query->whereHas('user', function($q) {
            $q->whereNotIn('role', ['admin', 'super_admin']);
        })
        ->orWhere('wallet_type', 'main_company');
    })
    ->where(function($q) {
        $q->whereNull('wallet_type')
        ->orWhere('wallet_type', '!=', 'office');
    })
    ->count();

echo "New Dashboard Count: " . $dashboardCount . "\n";
echo "Accounts Page Count: " . $accountsPageCount . "\n";

if ($dashboardCount === $accountsPageCount) {
    echo "SUCCESS: Counts match.\n";
} else {
    echo "FAILURE: Counts do not match.\n";
}
