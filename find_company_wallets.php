<?php
use App\Models\Account;
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$companyWallets = Account::where('wallet_type', 'main_company')
    ->orWhere('account_number', 'COMPANY-WALLET')
    ->orWhere('account_number', 'like', 'WALLETMAIN%')
    ->with('user')
    ->get();

foreach ($companyWallets as $a) {
    echo "ID: $a->id | No: $a->account_number | Type: $a->wallet_type | User: " . ($a->user ? $a->user->name : 'N/A') . "\n";
}
