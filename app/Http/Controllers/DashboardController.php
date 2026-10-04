<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Get active accounts with relationships
            $accounts = Account::with(['user', 'balances.currency'])
                ->where('is_active', true) // Use direct where instead of scope
                ->get();

            // Get active currencies
            $currencies = Currency::where('is_active', true)->get();

            // Statistics with error handling - use same filtering as accounts page
            $totalAccounts = Account::where(function($query) {
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

            $todayTransactions = Transaction::whereDate('created_at', today())->count();

            $activeCurrencies = Currency::where('is_active', true)->count();

            // Get system wallets
            $companyWallet = Account::getOrCreateMainWallet();

            // Get wallet balances with currency details
            $companyWalletBalances = $companyWallet->balances()->with('currency')->get();

            // Calculate total balance in USD
            $totalBalanceUSD = 0;

            foreach ($accounts as $account) {
                // Simple calculation if method doesn't exist
                if (method_exists($account, 'getTotalBalanceInUSD')) {
                    $totalBalanceUSD += $account->getTotalBalanceInUSD();
                } else {
                    // Fallback calculation
                    foreach ($account->balances as $balance) {
                        if ($balance->currency->code === 'USD') {
                            $totalBalanceUSD += $balance->balance;
                        }
                    }
                }
            }


            // Recent transactions
            $recentTransactions = Transaction::with([
                'fromAccount.user',
                'toAccount.user',
                'currency'
            ])
            ->latest()
            ->limit(10)
            ->get();

            // Calculate Total Company Commission (Revenue)
            $totalCompanyCommission = Transaction::where('status', 'completed')->sum('company_commission');

            return view('dashboard', compact(
                'accounts',
                'currencies',
                'totalAccounts',
                'todayTransactions',
                'activeCurrencies',
                'totalBalanceUSD',
                'recentTransactions',
                'companyWalletBalances',
                'totalCompanyCommission'
            ));


        } catch (\Exception $e) {
            // Return a simple view if there's an error
            return view('dashboard-simple', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
