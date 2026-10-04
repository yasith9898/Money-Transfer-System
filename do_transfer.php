<?php

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\TransactionController;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mainWallet = Account::where('wallet_type', 'main_company')->first();
$yasith = Account::whereHas('user', function($q) { $q->where('name', 'like', '%yasith%'); })->first();
$john = Account::whereHas('user', function($q) { $q->where('name', 'like', '%John Doe%'); })->first();
$usd = Currency::where('code', 'USD')->first();

if (!$john) {
    // Create John if needed, but presumably he exists or we can use another
     $john = Account::where('id', '!=', $yasith->id)->where('wallet_type', null)->first();
}

echo "BEFORE:\n";
echo "Main: " . $mainWallet->getBalance($usd->id) . "\n";
echo "Yasith: " . $yasith->getBalance($usd->id) . "\n";
echo "John: " . $john->getBalance($usd->id) . "\n";

// Execute Transfer Logic manually to simulate controller
Illuminate\Support\Facades\DB::transaction(function () use ($yasith, $john, $usd, $mainWallet) {
    $amount = 50;
    $commission = 5;
    $total = $amount + $commission;

    // Create Transaction
    Transaction::create([
        'from_account_id' => $yasith->id,
        'to_account_id' => $john->id,
        'currency_id' => $usd->id,
        'amount' => $amount,
        'office_commission' => 0,
        'company_commission' => $commission,
        'type' => 'transfer',
        'status' => 'completed',
        'notes' => 'Test Tool Transfer',
        'transaction_date' => now(),
    ]);

    // Update Balances
    $yasith->updateBalance($usd->id, -$total);
    $john->updateBalance($usd->id, $amount);
    
    if ($commission > 0) {
        $mainWallet->updateBalance($usd->id, $commission);
    }
});

echo "\nAFTER:\n";
echo "Main: " . $mainWallet->getBalance($usd->id) . "\n";
echo "Yasith: " . $yasith->getBalance($usd->id) . "\n";
echo "John: " . $john->getBalance($usd->id) . "\n";
