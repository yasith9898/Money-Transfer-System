<?php

use App\Models\Account;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$accounts = Account::with('user')
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
    ->get();

echo "Accounts available in Filter:\n";
foreach ($accounts as $acc) {
    if ($acc->wallet_type !== 'main_company') {
        echo "- " . $acc->user->name . " (" . ($acc->wallet_type ?? 'User') . ")\n"; 
    } else {
        echo "- Company Wallet (main_company)\n";
    }
}
