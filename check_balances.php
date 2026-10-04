<?php

use App\Models\Account;
use App\Models\Transaction;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mainWallet = Account::where('wallet_type', 'main_company')->first();
$yasith = Account::whereHas('user', function($q) { $q->where('name', 'like', '%yasith%'); })->first();

echo "\n--- BALANCES ---\n";
echo "Main Wallet Balance: " . number_format($mainWallet?->getBalanceByCurrencyCode('USD') ?? 0, 2) . "\n";
echo "Yasith Balance: " . number_format($yasith?->getBalanceByCurrencyCode('USD') ?? 0, 2) . "\n";
echo "----------------\n";

$tx = Transaction::latest()->first();
echo "Last Tx: " . $tx->type . " " . $tx->amount . " Comm: " . $tx->company_commission . "\n";
