<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'account_number',
        'is_active',
        'is_main_wallet',
        'wallet_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function balances()
    {
        return $this->hasMany(AccountBalance::class);
    }

    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'from_account_id');
    }

    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    /**
     * Get balance for specific currency
     */
    public function getBalance($currencyId)
    {
        $balance = $this->balances()->where('currency_id', $currencyId)->first();
        return $balance ? $balance->balance : 0;
    }

    /**
     * Get balance by currency code
     */
    public function getBalanceByCurrencyCode($currencyCode)
    {
        $currency = Currency::where('code', $currencyCode)->first();
        if (!$currency) return 0;

        return $this->getBalance($currency->id);
    }

    /**
     * Update account balance
     */

    public function updateBalance($currencyId, $amount)
    {
        // Use atomic operations to ensure concurrency safety
        $balance = $this->balances()->firstOrCreate(
            ['currency_id' => $currencyId],
            ['balance' => 0]
        );

        if ($amount != 0) {
            // Increment (or decrement) atomically in the database
            AccountBalance::where('id', $balance->id)->increment('balance', $amount);

            // Refresh model to return updated instance
            $balance->refresh();
        }

        return $balance;
    }


    /**
     * Get all balances with currency information
     */
    public function getAllBalances()
    {
        return $this->balances()->with('currency')->get();
    }

    /**
     * Check if account has sufficient balance
     */
    public function hasSufficientBalance($currencyId, $amount)
    {
        return $this->getBalance($currencyId) >= $amount;
    }

    /**
     * Get total balance across all currencies in base currency (USD)
     */
    public function getTotalBalanceInUSD()
    {
        $total = 0;
        $balances = $this->getAllBalances();

        // You would need exchange rates table for accurate conversion
        foreach ($balances as $balance) {
            $exchangeRate = $this->getExchangeRate($balance->currency->code, 'USD');
            $total += $balance->balance * $exchangeRate;
        }

        return $total;
    }

    /**
     * Get account summary
     */
    public function getAccountSummary()
    {
        return [
            'account_number' => $this->account_number,
            'user_name' => $this->user->name,
            'company_name' => $this->user->company_name,
            'balances' => $this->getAllBalances()->map(function($balance) {
                return [
                    'currency' => $balance->currency->code,
                    'balance' => $balance->balance,
                    'symbol' => $balance->currency->symbol
                ];
            }),
            'total_transactions' => $this->sentTransactions->count() + $this->receivedTransactions->count(),
            'is_active' => $this->is_active
        ];
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for main wallet accounts
     */
    public function scopeMainWallet($query)
    {
        return $query->where('is_main_wallet', true);
    }

    /**
     * Scope for wallet type
     */
    public function scopeWalletType($query, $type)
    {
        return $query->where('wallet_type', $type);
    }

    /**
     * Scope for accounts with minimum balance
     */
    public function scopeWithMinBalance($query, $currencyId, $minAmount)
    {
        return $query->whereHas('balances', function($q) use ($currencyId, $minAmount) {
            $q->where('currency_id', $currencyId)
              ->where('balance', '>=', $minAmount);
        });
    }

    /**
     * Helper method for exchange rates (you need to implement this properly)
     */
    private function getExchangeRate($fromCurrency, $toCurrency)
    {
        // This is a simplified version - you should create an exchange_rates table
        $rates = [
            'USD' => 1,
            'EUR' => 0.85,
            'IQD' => 1310,
            'GBP' => 0.75
        ];

        if ($fromCurrency === $toCurrency) return 1;

        return isset($rates[$fromCurrency]) && isset($rates[$toCurrency])
            ? $rates[$toCurrency] / $rates[$fromCurrency]
            : 1;
    }

    /**
     * Get recent transactions
     */
    public function getRecentTransactions($limit = 10)
    {
        $sent = $this->sentTransactions()
            ->with(['toAccount.user', 'currency'])
            ->latest()
            ->limit($limit)
            ->get();

        $received = $this->receivedTransactions()
            ->with(['fromAccount.user', 'currency'])
            ->latest()
            ->limit($limit)
            ->get();

        return $sent->merge($received)
            ->sortByDesc('created_at')
            ->take($limit);
    }

    public static function getOrCreateMainWallet()
    {
        // Try to find by wallet_type first (preferred)
        $account = static::where('wallet_type', 'main_company')->first();
        if ($account) {
            // Ensure is_main_wallet is true for consistency if it wasn't set
            if (!$account->is_main_wallet) {
                $account->is_main_wallet = true;
                $account->save();
            }
            return $account;
        }

        // Fallback to searching by account number (legacy/seeder)
        $account = static::where('account_number', 'COMPANY-WALLET')->first();
        if ($account) {
            $account->wallet_type = 'main_company'; // Ensure type is set
            $account->is_main_wallet = true;
            $account->save();
            return $account;
        }

        // Fallback to searching by is_main_wallet flag
        $account = static::where('is_main_wallet', true)->first();
        if ($account) {
            // Ensure wallet_type is set for consistency
            if (!$account->wallet_type) {
                $account->wallet_type = 'main_company';
                $account->save();
            }
            return $account;
        }

        $user = User::firstOrCreate(
            ['email' => 'main-wallet@system.local'],
            [
                'name' => 'Main Company Wallet',
                'company_name' => 'System',
                'password' => bcrypt('secret'),
                'mobile' => null,
                'is_active' => true,
            ]
        );

        return static::create([
            'user_id' => $user->id,
            'account_number' => 'WALLETMAIN-' . uniqid(),
            'is_active' => true,
            'is_main_wallet' => true,
            'wallet_type' => 'main_company',
        ]);
    }



    /**
     * Deactivate account
     */
    public function deactivate()
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Activate account
     */
    public function activate()
    {
        $this->is_active = true;
        return $this->save();
    }
}
