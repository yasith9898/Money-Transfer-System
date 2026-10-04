<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->latest();

        // Filter by type if provided
        if ($request->has('type') && $request->type !== 'all') {
            if ($request->type === 'history') {
                $query->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
            } else {
                $query->where('type', $request->type);
            }
        }

        // Filter by account if provided
        if ($request->has('account_id') && $request->account_id !== 'all') {
            $query->where(function($q) use ($request) {
                $q->where('from_account_id', $request->account_id)
                  ->orWhere('to_account_id', $request->account_id);
            });
        }

        // Filter by currency if provided
        if ($request->has('currency_id') && $request->currency_id !== 'all') {
            $query->where('currency_id', $request->currency_id);
        }

        // Clone query for statistics before pagination
        $statsQuery = clone $query;
        $currencyStatsQuery = clone $query;

        $transactions = $query->paginate(20);

        // Statistics for the main table footer (filtered by user selection)
        $footerStats = (clone $statsQuery)
            ->selectRaw('
                currency_id, 
                SUM(amount) as total_amount, 
                SUM(company_commission) as total_company_commission, 
                SUM(turkey_commission) as total_turkey_commission
            ')
            ->groupBy('currency_id')
            ->with('currency')
            ->get();

        // Today's specific statistics for the activity section
        $todaysAdjustmentStats = Transaction::whereDate('created_at', today())
            ->where('type', 'adjustment')
            ->selectRaw('
                currency_id, 
                SUM(CASE WHEN to_account_id NOT IN (SELECT id FROM accounts WHERE wallet_type = "main_company") THEN amount ELSE 0 END) as total_increase,
                SUM(CASE WHEN to_account_id IN (SELECT id FROM accounts WHERE wallet_type = "main_company") THEN amount ELSE 0 END) as total_decrease,
                SUM(CASE WHEN to_account_id IN (SELECT id FROM accounts WHERE wallet_type = "main_company") THEN -amount ELSE amount END) as net_amount,
                COUNT(*) as count
            ')
            ->groupBy('currency_id')
            ->with('currency')
            ->get();

        $todaysTransactionStats = Transaction::whereDate('created_at', today())
            ->where('type', '!=', 'adjustment')
            ->selectRaw('
                currency_id, 
                SUM(amount) as total_amount,
                SUM(company_commission) as total_company_commission,
                SUM(turkey_commission) as total_turkey_commission,
                COUNT(*) as count
            ')
            ->groupBy('currency_id')
            ->with('currency')
            ->get();

        // Statistics for cards (backward compatibility/general overview)
        $todayCount = Transaction::whereDate('created_at', today())->count();
        
        // Use the first currency as "total" if it's the only one, or use it for the cards
        // Actually, cards should probably show counts or we can keep the old calculation but it's risky
        $totalAmount = (clone $statsQuery)->sum('amount');
        $totalOfficeCommission = (clone $statsQuery)->sum('office_commission');
        $totalCompanyCommission = (clone $statsQuery)->sum('company_commission');
        $totalTurkeyCommission = (clone $statsQuery)->sum('turkey_commission');
        
        // Total Company Commission is sum of office + company
        $totalCompanyCommission = $totalOfficeCommission + $totalCompanyCommission;
        $totalCommissions = $totalCompanyCommission + $totalTurkeyCommission;

        // Currency-specific statistics (already exists, but we can reuse or refine)
        $currencyStats = (clone $currencyStatsQuery)->with('currency')
            ->selectRaw('
                currency_id,
                SUM(amount) as total_amount,
                SUM(office_commission + company_commission) as total_company_commissions,
                SUM(turkey_commission) as total_turkey_commissions,
                COUNT(*) as transaction_count
            ')
            ->groupBy('currency_id')
            ->get()
            ->map(function($stat) {
                return [
                    'currency' => $stat->currency,
                    'total_amount' => $stat['total_amount'],
                    'total_company_commissions' => $stat['total_company_commissions'],
                    'total_turkey_commissions' => $stat['total_turkey_commissions'],
                    'transaction_count' => $stat['transaction_count'],
                ];
            });

        // Today's specific lists
        $todaysTransactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('created_at', today())
            ->where('type', '!=', 'adjustment')
            ->latest()
            ->get();

        $todaysAdjustments = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('created_at', today())
            ->where('type', 'adjustment')
            ->latest()
            ->get();

        // Get accounts and currencies for filters
        $accounts = Account::with('user')
            ->where(function($query) {
                // Include regular users
                $query->whereHas('user', function($q) {
                    $q->whereNotIn('role', ['admin', 'super_admin']);
                })
                // Include Company Wallet
                ->orWhere('wallet_type', 'main_company');
            })
            // Exclude Office Wallet explicitly
            ->where(function($q) {
                $q->whereNull('wallet_type')
                  ->orWhere('wallet_type', '!=', 'office');
            })
            ->get();
        $currencies = Currency::active()->get();

        return view('transactions.index', compact(
            'transactions',
            'todayCount',
            'totalAmount',
            'totalCommissions',
            'totalCompanyCommission',
            'totalTurkeyCommission',
            'currencyStats',
            'todaysTransactions',
            'todaysAdjustments',
            'todaysTransactionStats',
            'todaysAdjustmentStats',
            'footerStats',
            'accounts',
            'currencies'
        ));
    }

    public function createTransfer()
    {
        $accounts = Account::with('user')
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
            ->get();
        $currencies = Currency::all();
        return view('transactions.transfer', compact('accounts', 'currencies'));
    }

    public function storeTransfer(Request $request)
    {
        $request->validate([
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id' => 'required|exists:accounts,id|different:from_account_id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|min:0.01',

            'company_commission' => 'nullable|numeric',
            'turkey_commission' => 'nullable|numeric',
            'exchange_rate' => 'nullable|numeric|min:0',
            'exchange_action' => 'nullable|string|in:multiply,divide',
            'exchange_result' => 'nullable|numeric',
            'notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Create Transaction
                $transaction = Transaction::create([
                    'from_account_id' => $request->from_account_id,
                    'to_account_id' => $request->to_account_id,
                    'currency_id' => $request->currency_id,
                    'amount' => $request->amount,
                    'office_commission' => 0,
                    'company_commission' => $request->company_commission ?? 0,
                    'turkey_commission' => $request->turkey_commission ?? 0,
                    'exchange_rate' => $request->exchange_rate,
                    'exchange_action' => $request->exchange_action,
                    'exchange_result' => $request->exchange_result,
                    'type' => 'transfer',
                    'status' => 'auto',
                    'notes' => $request->notes,
                    'transaction_date' => now()
                ]);

                $totalAmount = $request->amount + ($request->company_commission ?? 0);

                // Update Balances
                $fromAccount = Account::find($request->from_account_id);
                $toAccount = Account::find($request->to_account_id);
                $mainWallet = Account::getOrCreateMainWallet();

                // Deduct from sender - If Company Wallet is the sender, it doesn't "pay" commission to itself
                $deductionAmount = ($fromAccount->id === $mainWallet->id) ? $request->amount : $totalAmount;
                $fromAccount->updateBalance($request->currency_id, -$deductionAmount);

                // Add to receiver
                $toAccount->updateBalance($request->currency_id, $request->amount);

                // Route commissions to main wallet
                if (($request->company_commission ?? 0) != 0) {
                    $mainWallet->updateBalance($request->currency_id, $request->company_commission);
                }
                // Removed Turkey commission balance update per request

                // Mark completed for reporting
                $transaction->update(['status' => 'completed']);
            });

            return redirect()->route('transactions.index')
                ->with('success', 'Transfer completed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to complete transfer: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function createAdjustment()
    {
        $accounts = Account::with('user')
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
            ->get();
        $currencies = Currency::all();
        return view('transactions.adjustment', compact('accounts', 'currencies'));
    }

    public function adjustBalance(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric',
            'notes' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $account = Account::find($request->account_id);
                $mainWallet = Account::getOrCreateMainWallet();

                // Check if we are adjusting the Main Wallet itself
                if ($account->id === $mainWallet->id) {
                    // Update Main Wallet Balance
                    $account->updateBalance($request->currency_id, $request->amount);

                    Transaction::create([
                        // Self-referencing or just using it as primary
                        'from_account_id' => $account->id,
                        'to_account_id' => $account->id,
                        'currency_id' => $request->currency_id,
                        'amount' => abs($request->amount),
                        'office_commission' => 0,
                        'company_commission' => 0,
                        'type' => $request->amount > 0 ? 'deposit' : 'withdrawal',
                        'status' => 'completed',
                        'transaction_date' => now(),
                        'notes' => $request->notes . ' (System Wallet Update)',
                        'office_commission' => 0
                    ]);
                } else {
                    if ($request->amount > 0) {
                        // Increase: debit main wallet, credit account
                        $mainWallet->updateBalance($request->currency_id, -abs($request->amount));
                        $account->updateBalance($request->currency_id, abs($request->amount));

                        Transaction::create([
                            'from_account_id' => $mainWallet->id,
                            'to_account_id' => $account->id,
                            'currency_id' => $request->currency_id,
                            'amount' => abs($request->amount),
                            'office_commission' => 0,
                            'company_commission' => 0,
                            'type' => 'adjustment',
                            'status' => 'completed',
                            'transaction_date' => now(),
                            'notes' => $request->notes,
                            'office_commission' => 0
                        ]);
                    } else {
                        // Decrease: debit account, credit main wallet
                        $account->updateBalance($request->currency_id, -abs($request->amount));
                        $mainWallet->updateBalance($request->currency_id, abs($request->amount));

                        Transaction::create([
                            'from_account_id' => $account->id,
                            'to_account_id' => $mainWallet->id,
                            'currency_id' => $request->currency_id,
                            'amount' => abs($request->amount),
                            'office_commission' => 0,
                            'company_commission' => 0,
                            'type' => 'adjustment',
                            'status' => 'completed',
                            'transaction_date' => now(),
                            'notes' => $request->notes,
                            'office_commission' => 0
                        ]);
                    }
                }
            });

            return redirect()->route('transactions.index')
                ->with('success', 'Balance adjusted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to adjust balance: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $transaction = Transaction::with([
            'fromAccount.user',
            'toAccount.user',
            'currency'
        ])->findOrFail($id);

        return view('transactions.show', compact('transaction'));
    }

    public function voidTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);

        try {
            DB::transaction(function () use ($transaction) {
                // Get accounts
                $fromAccount = Account::find($transaction->from_account_id);
                $toAccount = Account::find($transaction->to_account_id);
                $mainWallet = Account::getOrCreateMainWallet();

                // Reverse the balance updates
                $totalAmount = $transaction->amount + $transaction->company_commission;

                // Reverse: add back to sender
                $fromAccount->updateBalance($transaction->currency_id, $totalAmount);

                // Reverse: deduct from receiver
                $toAccount->updateBalance($transaction->currency_id, -$transaction->amount);

                // Reverse: deduct commissions from main wallet
                if ($transaction->company_commission != 0) {
                    $mainWallet->updateBalance($transaction->currency_id, -$transaction->company_commission);
                }
                // Turkey commission is not reversed from balance

                // Create reverse transaction record
                $reverseTransaction = Transaction::create([
                    'from_account_id' => $transaction->to_account_id,
                    'to_account_id' => $transaction->from_account_id,
                    'currency_id' => $transaction->currency_id,
                    'amount' => $transaction->amount,
                    'office_commission' => $transaction->office_commission,
                    'company_commission' => $transaction->company_commission,
                    'type' => 'adjustment',
                    'status' => 'manual',
                    'notes' => 'VOID: ' . $transaction->notes,
                    'transaction_date' => now()
                ]);

                // Mark original as failed
                $transaction->update([
                    'status' => 'failed',
                    'notes' => ($transaction->notes ? $transaction->notes . "\n" : '') . 'FAILED/VOIDED on ' . now()
                ]);
            });

            return redirect()->back()
                ->with('success', 'Transaction voided successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to void transaction: ' . $e->getMessage());
        }
    }

    // Daily Report
    public function dailyReport(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));

        $transactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Group transactions by type
        $transferTransactions = $transactions->filter(function($t) {
            return $t->type === 'transfer';
        });

        $adjustmentTransactions = $transactions->filter(function($t) {
            return $t->type === 'adjustment';
        });

        // Calculate totals
        $totalTransactions = $transactions->count();

        // Group by currency for breakdown
        $currencyBreakdown = $transactions->groupBy('currency_id')->map(function($group) {
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'office_commission' => $group->sum('office_commission'),
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
            ];
        });

        $totalCompanyCommission = $transactions->sum('company_commission');
        $totalTurkeyCommission = $transactions->sum('turkey_commission');

        return view('transactions.daily-report', compact(
            'transactions',
            'transferTransactions',
            'adjustmentTransactions',
            'date',
            'totalTransactions',
            'currencyBreakdown',
            'totalCompanyCommission',
            'totalTurkeyCommission'
        ));
    }



    public function dailySummaryReport(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));

        $allTransactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $historyTransactions = $allTransactions->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
        $adjustmentTransactions = $allTransactions->where('type', 'adjustment');

        // Group by currency for breakdown
        $currencyBreakdown = $allTransactions->groupBy('currency_id')->map(function($group) {
            $history = $group->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'history_amount' => $history->sum('amount'),
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
                'total_transactions' => $group->count(),
            ];
        });

        $totalTransactions = $allTransactions->count();
        $totalCompanyCommission = $allTransactions->sum('company_commission');
        $totalTurkeyCommission = $allTransactions->sum('turkey_commission');

        return view('transactions.daily-summary-report', compact(
            'historyTransactions', 
            'adjustmentTransactions', 
            'date', 
            'totalTransactions',
            'totalCompanyCommission', 
            'totalTurkeyCommission',
            'currencyBreakdown'
        ));
    }

    public function downloadDailySummaryPDF(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));

        $allTransactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc')
            ->get();

        $historyTransactions = $allTransactions->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
        $adjustmentTransactions = $allTransactions->where('type', 'adjustment');

        // Group by currency for breakdown
        $currencyBreakdown = $allTransactions->groupBy('currency_id')->map(function($group) {
            $history = $group->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'history_amount' => $history->sum('amount'),
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
                'total_transactions' => $group->count(),
            ];
        });

        $totalTransactions = $allTransactions->count();
        $totalCompanyCommission = $allTransactions->sum('company_commission');
        $totalTurkeyCommission = $allTransactions->sum('turkey_commission');

        $pdf = Pdf::loadView('transactions.daily-summary-pdf', compact(
            'historyTransactions', 
            'adjustmentTransactions', 
            'date', 
            'totalTransactions',
            'totalCompanyCommission', 
            'totalTurkeyCommission',
            'currencyBreakdown'
        ));
        return $pdf->download('daily-summary-' . $date . '.pdf');
    }

    public function downloadDailyReportPDF(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));

        $transactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', $date)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate statistics
        // Group transactions by type
        $transferTransactions = $transactions->filter(function($t) {
            return $t->type === 'transfer';
        });

        $adjustmentTransactions = $transactions->filter(function($t) {
            return $t->type === 'adjustment';
        });

        $totalTransactions = $transactions->count();

        // Group by currency for breakdown
        $currencyBreakdown = $transactions->groupBy('currency_id')->map(function($group) {
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'office_commission' => $group->sum('office_commission'),
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
            ];
        });

        $totalCompanyCommission = $transactions->sum('company_commission');
        $totalTurkeyCommission = $transactions->sum('turkey_commission');

        $pdf = Pdf::loadView('transactions.daily-report-pdf', compact(
            'transactions',
            'transferTransactions',
            'adjustmentTransactions',
            'date',
            'totalTransactions',
            'totalCompanyCommission',
            'totalTurkeyCommission',
            'currencyBreakdown'
        ));

        return $pdf->download('daily-report-' . $date . '.pdf');
    }

    public function commissionReport(Request $request) {
        $startDate = $request->input('start_date', today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));

        $transactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('status', 'completed')
            ->where(function($query) {
                $query->where('office_commission', '!=', 0)
                      ->orWhere('company_commission', '!=', 0)
                      ->orWhere('turkey_commission', '!=', 0);
            })
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate totals
        $totalTransactions = $transactions->count();

        // Group by currency
        $commissionsByCurrency = $transactions->groupBy('currency_id')->map(function($group) {
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'office_commission' => $group->sum('office_commission'),
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
                'total_commission' => $group->sum('office_commission') + $group->sum('company_commission') + $group->sum('turkey_commission'),
            ];
        });

        $totalAmount = $transactions->sum('amount');
        $totalCompanyCommission = $transactions->sum('company_commission');
        $totalTurkeyCommission = $transactions->sum('turkey_commission');

        return view('transactions.commission-report', compact(
            'transactions',
            'startDate',
            'endDate',
            'totalTransactions',
            'totalAmount',
            'commissionsByCurrency',
            'totalCompanyCommission',
            'totalTurkeyCommission'
        ));
    }

    public function downloadCommissionReportPDF(Request $request) {
        $startDate = $request->input('start_date', today()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));

        $transactions = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('status', 'completed')
            ->where(function($query) {
                $query->where('office_commission', '!=', 0)
                      ->orWhere('company_commission', '!=', 0)
                      ->orWhere('turkey_commission', '!=', 0);
            })
            ->orderBy('transaction_date', 'desc')
            ->get();

        // Calculate totals
        $totalTransactions = $transactions->count();

        // Group by currency
        $commissionsByCurrency = $transactions->groupBy('currency_id')->map(function($group) {
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'office_commission' => $group->sum('office_commission'),
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
                'total_commission' => $group->sum('office_commission') + $group->sum('company_commission') + $group->sum('turkey_commission'),
            ];
        });

        $totalAmount = $transactions->sum('amount');
        $totalCompanyCommission = $transactions->sum('company_commission');
        $totalTurkeyCommission = $transactions->sum('turkey_commission');

        $pdf = Pdf::loadView('transactions.commission-report-pdf', compact(
            'transactions',
            'startDate',
            'endDate',
            'totalTransactions',
            'totalAmount',
            'commissionsByCurrency',
            'totalCompanyCommission',
            'totalTurkeyCommission'
        ));
        return $pdf->download('commission-report-' . $startDate . '-to-' . $endDate . '.pdf');

    }

    public function accountBalancesReport(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));
        $filter_type = $request->input('filter_type', 'all');

        $result = $this->getAccountBalancesData($date, $filter_type);
        $reportData = $result['reportData']->sortBy('account_name');
        $currencySummary = $result['currencySummary'];

        return view('transactions.account-balances-report', compact(
            'reportData', 
            'date', 
            'filter_type',
            'currencySummary'
        ));
    }

    public function accountBalancesReportPDF(Request $request) {
        $date = $request->input('date', today()->format('Y-m-d'));
        $filter_type = $request->input('filter_type', 'all');

        $result = $this->getAccountBalancesData($date, $filter_type);
        $reportData = $result['reportData']->sortBy('account_name');
        $currencySummary = $result['currencySummary'];
        
        $pdf = Pdf::loadView('transactions.account-balances-report-pdf', [
            'reportData' => $reportData,
            'date' => $date,
            'filter_type' => $filter_type,
            'currencySummary' => $currencySummary
        ]);

        return $pdf->download("account-balances-report-{$date}.pdf");
    }

    private function getAccountBalancesData($date, $filter_type) {
        $accounts = Account::with(['user', 'balances.currency'])
            ->where('is_active', true)
            ->get();

        $reportData = collect();
        $mainWallet = Account::getOrCreateMainWallet();

        foreach($accounts as $account) {
            if (!$account->user) continue;

            foreach($account->balances as $balance) {
                $currency = $balance->currency;
                
                // Get all transactions for this account & currency after the selected date to now
                $futureTransactions = Transaction::where('currency_id', $currency->id)
                    ->where('status', 'completed')
                    ->where(function($q) use ($account) {
                        $q->where('from_account_id', $account->id)
                          ->orWhere('to_account_id', $account->id);
                    })
                    ->whereDate('transaction_date', '>', $date)
                    ->get();
                
                $futureImpact = 0;
                foreach($futureTransactions as $t) {
                    $isSender = $t->from_account_id == $account->id && $t->to_account_id != $account->id;
                    $isReceiver = $t->to_account_id == $account->id && $t->from_account_id != $account->id;

                    if ($isSender) {
                        $comm = ($account->id === $mainWallet->id) ? 0 : $t->company_commission;
                        $futureImpact -= ($t->amount + $comm);
                    } elseif ($isReceiver) {
                        $futureImpact += $t->amount;
                    } else {
                        if ($t->type === 'withdrawal') $futureImpact -= $t->amount;
                        else $futureImpact += $t->amount;
                    }
                }

                $balanceAtDate = $balance->balance - $futureImpact;
                
                $dailyTransactions = Transaction::where('currency_id', $currency->id)
                    ->where('status', 'completed')
                    ->where(function($q) use ($account) {
                        $q->where('from_account_id', $account->id)
                          ->orWhere('to_account_id', $account->id);
                    })
                    ->whereDate('transaction_date', $date)
                    ->get();
                
                $totalIn = 0;
                $totalOut = 0;
                foreach($dailyTransactions as $t) {
                    $isSender = $t->from_account_id == $account->id && $t->to_account_id != $account->id;
                    $isReceiver = $t->to_account_id == $account->id && $t->from_account_id != $account->id;
                    
                    if ($isSender) {
                        $comm = ($account->id === $mainWallet->id) ? 0 : $t->company_commission;
                        $totalOut += ($t->amount + $comm);
                    } elseif ($isReceiver) {
                        $totalIn += $t->amount;
                    } else {
                        if ($t->type === 'withdrawal') $totalOut += $t->amount;
                        else $totalIn += $t->amount;
                    }
                }

                if ($balanceAtDate == 0 && $totalIn == 0 && $totalOut == 0) continue;
                if ($filter_type === 'positive' && $balanceAtDate < 0) continue;
                if ($filter_type === 'negative' && $balanceAtDate > 0) continue;
                
                $reportData->push([
                    'account_id' => $account->id,
                    'account_name' => $account->user->name,
                    'account_number' => $account->account_number,
                    'company_name' => $account->user->company_name,
                    'currency' => $currency,
                    'balance' => $balanceAtDate,
                    'total_in' => $totalIn,
                    'total_out' => $totalOut,
                    'net_change' => $totalIn - $totalOut
                ]);
            }
        }

        // Calculate currency-wise sums
        $currencySummary = $reportData->groupBy(function($item) {
            return $item['currency']->id;
        })->map(function($group) {
            return [
                'currency' => $group->first()['currency'],
                'count' => $group->count(),
                'positive_balance' => $group->where('balance', '>', 0)->sum('balance'),
                'negative_balance' => $group->where('balance', '<', 0)->sum('balance'),
                'net_balance' => $group->sum('balance'),
                'total_in' => $group->sum('total_in'),
                'total_out' => $group->sum('total_out'),
            ];
        });

        return [
            'reportData' => $reportData,
            'currencySummary' => $currencySummary
        ];
    }

    public function allTransactionsReport(Request $request)
    {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));
        $accountId = $request->input('account_id', 'all');
        $currencyId = $request->input('currency_id', 'all');
        $type = $request->input('type', 'all');
        $balanceType = $request->input('balance_type', 'all');

        $query = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc');

        if ($accountId !== 'all') {
            $query->where(function($q) use ($accountId, $balanceType) {
                if ($balanceType === 'positive') {
                    $q->where('to_account_id', $accountId);
                } elseif ($balanceType === 'negative') {
                    $q->where('from_account_id', $accountId);
                } else {
                    $q->where('from_account_id', $accountId)
                      ->orWhere('to_account_id', $accountId);
                }
            });
        }

        if ($currencyId !== 'all') {
            $query->where('currency_id', $currencyId);
        }

        if ($type !== 'all') {
            if ($type === 'history') {
                $query->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
            } else {
                $query->where('type', $type);
            }
        }

        $transactions = $query->get();

        // Calculate totals by currency
        $currencyBreakdown = $transactions->groupBy('currency_id')->map(function($group) use ($accountId) {
            $totalAmount = 0;
            if ($accountId !== 'all') {
                foreach ($group as $t) {
                    if ($t->from_account_id == $accountId && $t->to_account_id != $accountId) {
                        // Sender: deduction
                        $totalAmount -= $t->amount;
                    } elseif ($t->to_account_id == $accountId && $t->from_account_id != $accountId) {
                        // Receiver: addition
                        $totalAmount += $t->amount;
                    } else {
                        // Same account (e.g. self adjustment)
                        if ($t->type === 'withdrawal') $totalAmount -= $t->amount;
                        else $totalAmount += $t->amount;
                    }
                }
            } else {
                $totalAmount = $group->sum('amount');
            }

            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $totalAmount,
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
            ];
        });

        $totalTransactions = $transactions->count();
        $totalCompanyCommission = $transactions->sum('company_commission');
        $totalTurkeyCommission = $transactions->sum('turkey_commission');

        $accounts = Account::with('user')
            ->where(function($query) {
                $query->whereHas('user', function($q) {
                    $q->whereNotIn('role', ['admin', 'super_admin']);
                })
                ->orWhere('wallet_type', 'main_company');
            })
            ->get();
        $currencies = Currency::active()->get();

        return view('transactions.all-transactions-report', compact(
            'transactions',
            'startDate',
            'endDate',
            'accountId',
            'currencyId',
            'type',
            'balanceType',
            'totalTransactions',
            'currencyBreakdown',
            'totalCompanyCommission',
            'totalTurkeyCommission',
            'accounts',
            'currencies'
        ));
    }

    public function allTransactionsReportPDF(Request $request)
    {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));
        $accountId = $request->input('account_id', 'all');
        $currencyId = $request->input('currency_id', 'all');
        $type = $request->input('type', 'all');
        $balanceType = $request->input('balance_type', 'all');

        $query = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc');

        if ($accountId !== 'all') {
            $query->where(function($q) use ($accountId, $balanceType) {
                if ($balanceType === 'positive') {
                    $q->where('to_account_id', $accountId);
                } elseif ($balanceType === 'negative') {
                    $q->where('from_account_id', $accountId);
                } else {
                    $q->where('from_account_id', $accountId)
                      ->orWhere('to_account_id', $accountId);
                }
            });
        }

        if ($currencyId !== 'all') {
            $query->where('currency_id', $currencyId);
        }

        if ($type !== 'all') {
            if ($type === 'history') {
                $query->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
            } else {
                $query->where('type', $type);
            }
        }

        $transactions = $query->get();

        $currencyBreakdown = $transactions->groupBy('currency_id')->map(function($group) use ($accountId) {
            $totalAmount = 0;
            if ($accountId !== 'all') {
                foreach ($group as $t) {
                    if ($t->from_account_id == $accountId && $t->to_account_id != $accountId) {
                        $totalAmount -= $t->amount;
                    } elseif ($t->to_account_id == $accountId && $t->from_account_id != $accountId) {
                        $totalAmount += $t->amount;
                    } else {
                        if ($t->type === 'withdrawal') {
                            $totalAmount -= $t->amount;
                        } else {
                            $totalAmount += $t->amount;
                        }
                    }
                }
            } else {
                $totalAmount = $group->sum('amount');
            }

            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $totalAmount,
                'company_commission' => $group->sum('company_commission'),
                'turkey_commission' => $group->sum('turkey_commission'),
            ];
        });

        $totalTransactions = $transactions->count();
        $totalAmount = $transactions->sum('amount');
        $totalCompanyCommission = $transactions->sum('company_commission');
        $totalTurkeyCommission = $transactions->sum('turkey_commission');

        $pdf = Pdf::loadView('transactions.all-transactions-report-pdf', compact(
            'transactions',
            'startDate',
            'endDate',
            'totalTransactions',
            'currencyBreakdown',
            'totalAmount',
            'totalCompanyCommission',
            'totalTurkeyCommission',
            'accountId',
            'balanceType'
        ));

        return $pdf->download('all-transactions-report-' . $startDate . '-to-' . $endDate . '.pdf');
    }

    public function accountStatement($accountId) {
        $account = Account::with(['user', 'balances.currency'])->findOrFail($accountId);
        $mainWallet = Account::getOrCreateMainWallet();
        $isMainWallet = ($account->id === $mainWallet->id);

        // All transactions related to this account
        $allAccountTransactions = Transaction::where(function($q) use ($accountId, $isMainWallet) {
                $q->where('from_account_id', $accountId)
                  ->orWhere('to_account_id', $accountId);
                
                if ($isMainWallet) {
                    $q->orWhere('company_commission', '>', 0);
                }
            })
            ->where('status', 'completed')
            ->get();

        // Group by currency for balance calculation table and history totals
        $currencyAggregates = $allAccountTransactions->groupBy('currency_id')->map(function($group) use ($accountId, $mainWallet) {
            $currency = $group->first()->currency;
            
            // History subset for this currency
            $historyGroup = $group->whereIn('type', ['transfer', 'deposit', 'withdrawal']);
            $adjGroup = $group->where('type', 'adjustment');

            // Total In (+): Amount received (excluding commission)
            $totalIn = $group->filter(function($t) use ($accountId) {
                return ($t->to_account_id == $accountId && $t->from_account_id != $accountId) ||
                       ($t->to_account_id == $accountId && $t->from_account_id == $accountId && in_array($t->type, ['deposit', 'adjustment']));
            })->sum('amount');

            // Total Out (-): Amount sent (base amount only)
            $totalOutBase = $group->filter(function($t) use ($accountId) {
                return ($t->from_account_id == $accountId && $t->to_account_id != $accountId) ||
                       ($t->from_account_id == $accountId && $t->to_account_id == $accountId && $t->type === 'withdrawal');
            })->sum('amount');
            
            $commPaid = 0;
            $commEarned = 0;

            if ($accountId == $mainWallet->id) {
                $commEarned = $group->sum('company_commission');
                $commPaid = 0; 
            } else {
                $commPaid = $group->where('from_account_id', $accountId)->sum('company_commission');
            }
            
            // History Table Totals for this currency
            $histTotalAmount = $historyGroup->sum('amount');
            $histTotalCompanyComm = $historyGroup->sum('company_commission');
            $histTotalTurkeyComm = $historyGroup->sum('turkey_commission');

            // Adjustment Totals for this currency
            $adjTotalPlus = $adjGroup->where('to_account_id', $accountId)->sum('amount');
            $adjTotalMinus = $adjGroup->where('from_account_id', $accountId)
                                     ->where('to_account_id', '!=', $accountId)
                                     ->sum('amount');
            
            return [
                'currency' => $currency,
                'total_in' => $totalIn,
                'total_out' => $totalOutBase,
                'company_commission_minus' => $commPaid,
                'company_commission_plus' => $commEarned,
                'net' => $totalIn + $commEarned - ($totalOutBase + $commPaid),
                'hist_total_amount' => $histTotalAmount,
                'hist_total_company_comm' => $histTotalCompanyComm,
                'hist_total_turkey_comm' => $histTotalTurkeyComm,
                'adj_total_amount' => $adjTotalPlus - $adjTotalMinus
            ];
        });

        // History query (Transfers, Deposits, Withdrawals)
        $historyQuery = Transaction::where(function($q) use ($accountId) {
                $q->where('from_account_id', $accountId)
                  ->orWhere('to_account_id', $accountId);
            })
            ->whereIn('type', ['transfer', 'deposit', 'withdrawal'])
            ->with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->latest();

        // Adjustment query
        $adjustmentQuery = Transaction::where(function($q) use ($accountId) {
                $q->where('from_account_id', $accountId)
                  ->orWhere('to_account_id', $accountId);
            })
            ->where('type', 'adjustment')
            ->with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->latest();

        // Global counts for pagination/UI
        $totalTransactions = $allAccountTransactions->whereIn('type', ['transfer', 'deposit', 'withdrawal'])->count();
        $totalAdjustments = $allAccountTransactions->where('type', 'adjustment')->count();
        
        // Paginate both sets independently
        $historyTransactions = $historyQuery->paginate(15, ['*'], 'history_page');
        $adjustmentTransactions = $adjustmentQuery->paginate(15, ['*'], 'adj_page');

        return view('transactions.account-statement', compact(
            'account', 
            'historyTransactions',
            'adjustmentTransactions',
            'currencyAggregates'
        ));
    }

    public function companyReport(Request $request) {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));
        $companyName = $request->input('company_name', 'all');

        $query = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc');

        if ($companyName !== 'all') {
            $query->where(function($q) use ($companyName) {
                $q->whereHas('fromAccount.user', function($u) use ($companyName) {
                    $u->where('company_name', $companyName);
                })->orWhereHas('toAccount.user', function($u) use ($companyName) {
                    $u->where('company_name', $companyName);
                });
            });
        }

        $transactions = $query->get();

        // Calculate "First Total" (Amount + Comp Comm if not main company) for each transaction
        // Since this report is from the perspective of the company, we calculate their total impact
        // However, for "All Companies", displaying their 'First Total' is more like a statement.
        
        $currencyBreakdown = $transactions->groupBy('currency_id')->map(function($group) {
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'total_comp_comm' => $group->sum('company_commission'),
                'total_turkey_comm' => $group->sum('turkey_commission'),
                // Total 1: Amount + Comp Comm
                'total_first' => $group->sum(function($t) {
                    // Similar logic: if sender is NOT main company, they paid comm
                    return $t->amount + ($t->fromAccount->wallet_type !== 'main_company' ? $t->company_commission : 0);
                })
            ];
        });

        // Get unique company names for filter
        $companies = User::whereNotNull('company_name')
            ->whereNotIn('role', ['admin', 'super_admin'])
            ->distinct()
            ->pluck('company_name')
            ->sort();

        return view('transactions.company-report', compact(
            'transactions', 
            'startDate', 
            'endDate', 
            'companyName', 
            'currencyBreakdown',
            'companies'
        ));
    }

    public function companyReportPDF(Request $request) {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));
        $companyName = $request->input('company_name', 'all');

        $query = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency', 'fromAccount', 'toAccount'])
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->where('status', 'completed')
            ->orderBy('transaction_date', 'desc');

        if ($companyName !== 'all') {
            $query->where(function($q) use ($companyName) {
                $q->whereHas('fromAccount.user', function($u) use ($companyName) {
                    $u->where('company_name', $companyName);
                })->orWhereHas('toAccount.user', function($u) use ($companyName) {
                    $u->where('company_name', $companyName);
                });
            });
        }

        $transactions = $query->get();
        
        $currencyBreakdown = $transactions->groupBy('currency_id')->map(function($group) {
            return [
                'currency' => $group->first()->currency,
                'count' => $group->count(),
                'total_amount' => $group->sum('amount'),
                'total_comp_comm' => $group->sum('company_commission'),
                'total_turkey_comm' => $group->sum('turkey_commission'),
                'total_first' => $group->sum(function($t) {
                    return $t->amount + ($t->fromAccount->wallet_type !== 'main_company' ? $t->company_commission : 0);
                })
            ];
        });

        $pdf = Pdf::loadView('transactions.company-report-pdf', compact(
            'transactions', 
            'startDate', 
            'endDate', 
            'companyName', 
            'currencyBreakdown'
        ));

        return $pdf->download("company-report-{$startDate}-to-{$endDate}.pdf");
    }

    public function getAccountBalance(Request $request) {
        $account = Account::find($request->account_id);
        $balance = $account ? $account->getBalance($request->currency_id) : 0;

        return response()->json([
            'balance' => $balance,
            'formatted_balance' => number_format($balance, 2)
        ]);
    }
}
