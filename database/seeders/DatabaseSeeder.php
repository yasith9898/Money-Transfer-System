<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Account;
use App\Models\Currency;
use App\Models\AccountBalance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed currencies first
        $this->call(CurrencySeeder::class);

        // Create super admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'company_name' => 'System Administrator',
                'password' => Hash::make('admin123'),
                'mobile' => '+1234567890',
                'role' => 'super_admin',
                'is_active' => true
            ]
        );

        // Create a regular admin user for testing
        $adminUser = User::firstOrCreate(
            ['email' => 'admin1@admin.com'],
            [
                'name' => 'Admin User',
                'company_name' => 'Admin Company',
                'password' => Hash::make('admin123'),
                'mobile' => '+1234567891',
                'role' => 'admin',
                'is_active' => true
            ]
        );


        // Create dedicated system user for wallets
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

        // Create system wallets for each currency attached to System User
        $this->createSystemWallets($systemUser->id);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('📧 Super Admin: admin@admin.com / admin123');
        $this->command->info('📧 Regular Admin: admin1@admin.com / admin123');
    }

    /**
     * Create system wallets (Company and Office) for all currencies
     */
    private function createSystemWallets(int $userId): void
    {
        // Get or create company wallet
        $companyWallet = Account::firstOrCreate(
            ['account_number' => 'COMPANY-WALLET'],
            [
                'user_id' => $userId,
                'account_number' => 'COMPANY-WALLET',
                'is_active' => true,
                'wallet_type' => 'main_company'
            ]
        );

        // Get or create office wallet
        $officeWallet = Account::firstOrCreate(
            ['account_number' => 'OFFICE-WALLET'],
            [
                'user_id' => $userId,
                'account_number' => 'OFFICE-WALLET',
                'is_active' => true,
                'wallet_type' => 'office'
            ]
        );

        // Create balances for each currency
        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            // Company wallet balance
            AccountBalance::firstOrCreate(
                [
                    'account_id' => $companyWallet->id,
                    'currency_id' => $currency->id
                ],
                [
                    'balance' => 0
                ]
            );

            // Office wallet balance
            AccountBalance::firstOrCreate(
                [
                    'account_id' => $officeWallet->id,
                    'currency_id' => $currency->id
                ],
                [
                    'balance' => 0
                ]
            );
        }

        $this->command->info('💼 System wallets created for all currencies');
    }
}
