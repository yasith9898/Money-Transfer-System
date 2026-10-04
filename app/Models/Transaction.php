<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'currency_id',
        'amount',
        'office_commission',
        'company_commission',
        'turkey_commission',
        'exchange_rate',
        'exchange_action',
        'exchange_result',
        'type',
        'status',
        'notes',
        'transaction_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'office_commission' => 'decimal:2',
        'company_commission' => 'decimal:2',
        'turkey_commission' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Transaction types
     */
    const TYPE_TRANSFER = 'transfer';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';

    /**
     * Transaction statuses
     */
    const STATUS_AUTO = 'auto';
    const STATUS_MANUAL = 'manual';
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * Relationships
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Scopes
     */

    // Scope for transfers
    public function scopeTransfers($query)
    {
        return $query->where('type', self::TYPE_TRANSFER);
    }

    // Scope for adjustments
    public function scopeAdjustments($query)
    {
        return $query->where('type', self::TYPE_ADJUSTMENT);
    }

    // Scope for completed transactions
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    // Scope for pending transactions
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Scope for auto transactions
    public function scopeAuto($query)
    {
        return $query->where('status', self::STATUS_AUTO);
    }

    // Scope for manual transactions
    public function scopeManual($query)
    {
        return $query->where('status', self::STATUS_MANUAL);
    }

    // Scope for transactions on specific date
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('transaction_date', $date);
    }

    // Scope for transactions between dates
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Scope for transactions for specific account
    public function scopeForAccount($query, $accountId)
    {
        return $query->where(function($q) use ($accountId) {
            $q->where('from_account_id', $accountId)
              ->orWhere('to_account_id', $accountId);
        });
    }

    // Scope for transactions in specific currency
    public function scopeInCurrency($query, $currencyId)
    {
        return $query->where('currency_id', $currencyId);
    }

    /**
     * Methods
     */

    // Get total amount (amount + commissions)
    public function getTotalAmount(): float
    {
        return $this->amount + $this->office_commission + $this->company_commission;
    }

    // Get net amount received by recipient
    public function getNetAmount(): float
    {
        return $this->amount;
    }

    // Get total commissions
    public function getTotalCommission(): float
    {
        return (float) ($this->office_commission + $this->company_commission + $this->turkey_commission);
    }

    // Check if transaction is transfer
    public function isTransfer(): bool
    {
        return $this->type === self::TYPE_TRANSFER;
    }

    // Check if transaction is adjustment
    public function isAdjustment(): bool
    {
        return $this->type === self::TYPE_ADJUSTMENT;
    }

    // Check if transaction is completed
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    // Check if transaction is automatic
    public function isAuto(): bool
    {
        return $this->status === self::STATUS_AUTO;
    }

    // Get transaction description
    public function getDescription(): string
    {
        if ($this->isTransfer()) {
            return "Transfer from {$this->fromAccount->account_number} to {$this->toAccount->account_number}";
        } elseif ($this->isAdjustment()) {
            return "Balance adjustment for {$this->toAccount->account_number}";
        }

        return ucfirst($this->type) . " transaction";
    }

    // Get formatted amount with currency
    public function getFormattedAmount(): string
    {
        return $this->currency->formatAmount($this->amount);
    }

    // Get formatted total amount with currency
    public function getFormattedTotalAmount(): string
    {
        return $this->currency->formatAmount($this->getTotalAmount());
    }

    // Mark transaction as completed
    public function markAsCompleted(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }

    // Mark transaction as failed
    public function markAsFailed(string $reason = null): bool
    {
        $this->status = self::STATUS_FAILED;
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . "Failed: " . $reason;
        }
        return $this->save();
    }

    // Check if account is involved in transaction
    public function involvesAccount($accountId): bool
    {
        return $this->from_account_id == $accountId || $this->to_account_id == $accountId;
    }

    // Get the other account in transaction
    public function getOtherAccount($accountId): ?Account
    {
        if ($this->from_account_id == $accountId) {
            return $this->toAccount;
        } elseif ($this->to_account_id == $accountId) {
            return $this->fromAccount;
        }

        return null;
    }

    /**
     * Static methods
     */

    // Get daily transaction summary
    public static function getDailySummary($date = null)
    {
        $date = $date ?: now()->format('Y-m-d');

        return self::onDate($date)
            ->completed()
            ->select([
                DB::raw('COUNT(*) as total_transactions'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('SUM(office_commission) as total_office_commission'),
                DB::raw('SUM(company_commission) as total_company_commission'),
                DB::raw('SUM(turkey_commission) as total_turkey_commission'),
                'currency_id'
            ])
            ->with('currency')
            ->groupBy('currency_id')
            ->get();
    }

    // Get total commissions for period
    public static function getCommissionsSummary($startDate, $endDate)
    {
        return self::betweenDates($startDate, $endDate)
            ->completed()
            ->select([
                DB::raw('SUM(office_commission) as total_office_commission'),
                DB::raw('SUM(company_commission) as total_company_commission'),
                DB::raw('SUM(turkey_commission) as total_turkey_commission'),
                DB::raw('SUM(office_commission + company_commission + turkey_commission) as total_commissions'),
                'currency_id'
            ])
            ->with('currency')
            ->groupBy('currency_id')
            ->get();
    }

    // Create a new transfer transaction
    public static function createTransfer(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $transaction = self::create(array_merge($data, [
                'type' => self::TYPE_TRANSFER,
                'status' => self::STATUS_AUTO,
                'transaction_date' => now(),
            ]));

            // Update account balances
            $fromAccount = Account::find($data['from_account_id']);
            $toAccount = Account::find($data['to_account_id']);

            $totalAmount = $data['amount'] + ($data['office_commission'] ?? 0) + ($data['company_commission'] ?? 0);

            // Deduct from sender
            $fromAccount->updateBalance($data['currency_id'], -$totalAmount);

            // Add to receiver
            $toAccount->updateBalance($data['currency_id'], $data['amount']);

            return $transaction;
        });
    }

    // Create adjustment transaction
    public static function createAdjustment(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $transaction = self::create(array_merge($data, [
                'type' => self::TYPE_ADJUSTMENT,
                'status' => self::STATUS_MANUAL,
                'transaction_date' => now(),
            ]));

            // Update account balance
            $account = Account::find($data['to_account_id']);
            $account->updateBalance($data['currency_id'], $data['amount']);

            return $transaction;
        });
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Set default transaction date if not provided
        static::creating(function ($transaction) {
            if (!$transaction->transaction_date) {
                $transaction->transaction_date = now();
            }
        });
    }
}
