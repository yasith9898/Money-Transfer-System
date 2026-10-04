<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'company_name', // අලුතින් add කරන්න
        'mobile',       // අලුතින් add කරන්න
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationships
     */

    // User has many accounts
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    // Get user's balance for specific currency
    public function getBalanceByCurrency($currencyCode)
    {
        $currency = Currency::where('code', $currencyCode)->first();
        if (!$currency) return 0;

        $totalBalance = 0;
        foreach ($this->accounts as $account) {
            $balance = $account->balances()->where('currency_id', $currency->id)->first();
            if ($balance) {
                $totalBalance += $balance->balance;
            }
        }

        return $totalBalance;
    }

    // Get all balances for user
    public function getAllBalances()
    {
        $balances = [];
        $currencies = Currency::all();

        foreach ($currencies as $currency) {
            $balances[$currency->code] = $this->getBalanceByCurrency($currency->code);
        }

        return $balances;
    }

    // Get user's main account (first account)
    public function getMainAccount()
    {
        return $this->accounts()->first();
    }
}
