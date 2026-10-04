<?php

use App\Models\Account;
use App\Models\User;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$wallet = Account::where('account_number', 'COMPANY-WALLET')->with('user')->first();

$data = [];

if ($wallet) {
    $data['wallet'] = [
        'account_number' => $wallet->account_number,
        'wallet_type' => $wallet->wallet_type,
        'owner_name' => $wallet->user->name,
        'owner_email' => $wallet->user->email,
        'owner_role' => $wallet->user->role,
    ];
} else {
    $data['wallet'] = 'NOT FOUND';
}

$admin = User::where('role', 'super_admin')->first();
if ($admin) {
    $data['super_admin'] = [
        'name' => $admin->name,
        'email' => $admin->email
    ];
}

echo json_encode($data, JSON_PRETTY_PRINT);
