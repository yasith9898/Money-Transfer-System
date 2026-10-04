<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'decimal_places'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'is_active' => true,
        'decimal_places' => 2,
    ];

    /**
     * Relationships
     */

    // Currency has many account balances
    public function accountBalances(): HasMany
    {
        return $this->hasMany(AccountBalance::class);
    }

    // Currency has many transactions
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scopes
     */

    // Scope for active currencies
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for specific currency codes
    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }

    /**
     * Methods
     */

    // Get formatted amount with currency symbol
    public function formatAmount($amount): string
    {
        $formattedAmount = number_format(
            $amount,
            $this->decimal_places,
            '.',
            ','
        );

        // Different currency symbol positions based on currency
        if (in_array($this->code, ['USD', 'GBP', 'IQD'])) {
            return $this->symbol . $formattedAmount;
        } else {
            return $formattedAmount . ' ' . $this->symbol;
        }
    }

    // Check if currency is base currency (USD)
    public function isBaseCurrency(): bool
    {
        return $this->code === 'USD';
    }

    // Get exchange rate (you would integrate with actual exchange rate API)
    public function getExchangeRate($toCurrencyCode): float
    {
        // Static exchange rates for demonstration
        // In real application, fetch from database or API
        $rates = [
            'USD' => [
                'EUR' => 0.85,
                'IQD' => 1310,
                'GBP' => 0.75,
                'USD' => 1
            ],
            'EUR' => [
                'USD' => 1.18,
                'IQD' => 1541,
                'GBP' => 0.88,
                'EUR' => 1
            ],
            'IQD' => [
                'USD' => 0.00076,
                'EUR' => 0.00065,
                'GBP' => 0.00057,
                'IQD' => 1
            ],
            'GBP' => [
                'USD' => 1.33,
                'EUR' => 1.14,
                'IQD' => 1746,
                'GBP' => 1
            ]
        ];

        return $rates[$this->code][$toCurrencyCode] ?? 1;
    }

    // Convert amount to another currency
    public function convertAmount($amount, $toCurrencyCode): float
    {
        if ($this->code === $toCurrencyCode) {
            return $amount;
        }

        $exchangeRate = $this->getExchangeRate($toCurrencyCode);
        return $amount * $exchangeRate;
    }

    // Get total balance in this currency across all accounts
    public function getTotalBalance(): float
    {
        return $this->accountBalances()->sum('balance');
    }

    // Get active accounts count for this currency
    public function getActiveAccountsCount(): int
    {
        return $this->accountBalances()
            ->whereHas('account', function($query) {
                $query->where('is_active', true);
            })
            ->distinct('account_id')
            ->count('account_id');
    }

    // Deactivate currency
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    // Activate currency
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    // Check if currency can be deleted (no transactions or balances)
    public function canBeDeleted(): bool
    {
        return $this->accountBalances()->count() === 0 &&
               $this->transactions()->count() === 0;
    }

    /**
     * Static methods
     */

    // Get base currency (USD)
    public static function getBaseCurrency(): ?self
    {
        return static::where('code', 'USD')->first();
    }

    // Get currency by code
    public static function getByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->first();
    }

    // Get all active currency codes
    public static function getActiveCurrencyCodes(): array
    {
        return static::active()->pluck('code')->toArray();
    }

    // Get currency options for dropdown
    public static function getDropdownOptions(): array
    {
        return static::active()
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-uppercase currency code
        static::saving(function ($currency) {
            $currency->code = strtoupper($currency->code);
        });
    }
}
