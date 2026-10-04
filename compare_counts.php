<?php

use App\Models\Account;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Dashboard Logic
$dashboardCount = Account::where('is_active', true)
    ->whereHas('user', function($q) {
        $q->whereNotIn('role', ['admin', 'super_admin']);
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

echo "Dashboard Count (Active Users Only, No Main Wallet): " . $dashboardCount . "\n";
echo "Accounts Page Count (Including Inactive & Main Wallet): " . $accountsPageCount . "\n";

// Detailed breakdown
$activeUsers = Account::where('is_active', true)
    ->whereHas('user', function($q) {
        $q->whereNotIn('role', ['admin', 'super_admin']);
    })->count();

$inactiveUsers = Account::where('is_active', false)
    ->whereHas('user', function($q) {
        $q->whereNotIn('role', ['admin', 'super_admin']);
    })->count();

$mainWalletCount = Account::where('wallet_type', 'main_company')->count();

echo "\n--- Breakdown ---\n";
echo "Active User Accounts: $activeUsers\n";
echo "Inactive User Accounts: $inactiveUsers\n";
echo "Main Company Wallet: $mainWalletCount\n";
