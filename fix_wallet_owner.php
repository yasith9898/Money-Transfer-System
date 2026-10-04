<?php

use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Correcting System Wallet Ownership...\n";

// 1. Get or Create the System User
$systemUser = User::firstOrCreate(
    ['email' => 'system@moneytransfer.com'],
    [
        'name' => 'Company Wallet',
        'company_name' => 'System',
        'password' => Hash::make(Str::random(32)),
        'mobile' => '+0000000000',
        'role' => 'user', 
        'is_active' => true
    ]
);

echo "System User ID: " . $systemUser->id . "\n";

// 2. Find System Wallets (Company and Office) and update owner
$wallets = Account::whereIn('wallet_type', ['main_company', 'office'])->get();

if ($wallets->isEmpty()) {
    // Fallback: search by account number if type not yet set/migrated
    $wallets = Account::whereIn('account_number', ['COMPANY-WALLET', 'OFFICE-WALLET'])->get();
}

foreach ($wallets as $wallet) {
    echo "Updating Wallet: " . $wallet->account_number . " (" . $wallet->wallet_type . ")\n";
    $wallet->user_id = $systemUser->id;
    $wallet->save();
}

echo "Done.\n";
