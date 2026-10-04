<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use App\Models\Currency;
use App\Models\AccountBalance;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index()
    {
        try {
            $accounts = Account::with(['user', 'balances.currency'])
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
                ->latest()
                ->get();
            return view('accounts.index', compact('accounts'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', 'Error loading accounts: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $currencies = Currency::all();
            return view('accounts.create', compact('currencies'));
        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        // Basic validation
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            // Email and password removed from validation
        ]);

        try {
            DB::beginTransaction();

            // Generate unique account number first to use in dummy email
            $accountNumber = 'ACC' . date('YmdHis') . rand(100, 999);

            // Create User with dummy email and password
            $user = User::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'mobile' => $request->mobile,
                'email' => $accountNumber . '@system.local', // Dummy email
                'password' => Hash::make('password123'), // Dummy password
                'is_active' => true
            ]);

            // Account number already generated above

            // Create Account
            $account = Account::create([
                'user_id' => $user->id,
                'account_number' => $accountNumber,
                'is_active' => true,
                'wallet_type' => null 
            ]);

            DB::commit();

            return redirect()->route('accounts.index')
                ->with('success', 'Account created successfully! Account Number: ' . $accountNumber);

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('error', 'Failed to create account: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $account = Account::with(['user', 'balances.currency'])->findOrFail($id);
            $currencies = Currency::all();
            $recentTransactions = $account->getRecentTransactions(10);
            return view('accounts.show', compact('account', 'currencies', 'recentTransactions'));
        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', 'Error loading account: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $account = Account::with('user')->findOrFail($id);
            // Protect against editing admin/system accounts if needed
            // Allow Super Admin to edit system wallets, but block others
            if(in_array($account->wallet_type, ['main_company', 'office']) && auth()->user()->role !== 'super_admin') {
                 return redirect()->route('accounts.index')->with('error', 'Cannot edit system wallets.');
            }
            $currencies = Currency::all();
            return view('accounts.edit', compact('account', 'currencies'));
        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', 'Error loading edit form: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            DB::beginTransaction();

            $account = Account::with('user')->findOrFail($id);

            // Double check protection
            if(in_array($account->wallet_type, ['main_company', 'office']) && auth()->user()->role !== 'super_admin') {
                 return redirect()->route('accounts.index')->with('error', 'Cannot edit system wallets.');
            }

            $account->user->update([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'mobile' => $request->mobile,
            ]);

            $account->update([
                'is_active' => (bool) ($request->is_active ?? $account->is_active),
            ]);

            DB::commit();

            return redirect()->route('accounts.index') // Redirect to index as per requested flow usually or show
                ->with('success', 'Account updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update account: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function adjustBalance(Request $request, $id)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric',
            'notes' => 'required|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $account = Account::findOrFail($id);
                $mainWallet = Account::getOrCreateMainWallet();

                // Check if we are adjusting the Main Wallet itself (or an office wallet)
                // This prevents "self-transfer" which cancels out
                if ($account->id === $mainWallet->id || $account->wallet_type === 'office') {
                     $account->updateBalance($request->currency_id, $request->amount);

                     Transaction::create([
                        'from_account_id' => $account->id,
                        'to_account_id' => $account->id,
                        'currency_id' => $request->currency_id,
                        'amount' => abs($request->amount),
                        'office_commission' => 0,
                        'company_commission' => 0,
                        'type' => 'adjustment',
                        'status' => 'completed',
                        'transaction_date' => now(),
                        'notes' => $request->notes . ' (Direct ' . ($request->amount > 0 ? 'Deposit' : 'Withdrawal') . ')',
                    ]);
                } else {
                    if ($request->amount > 0) {
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
                            'notes' => $request->notes
                        ]);
                    } else {
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
                            'notes' => $request->notes
                        ]);
                    }
                }
            });

            return redirect()->route('accounts.index')
                ->with('success', 'Balance adjusted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to adjust balance: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function statement($id)
    {
        return redirect()->route('transactions.account-statement', ['accountId' => $id]);
    }

    public function activate($id)
    {
        try {
            $account = Account::findOrFail($id);
            if(in_array($account->wallet_type, ['main_company', 'office'])) {
                 return redirect()->back()->with('error', 'Cannot modify system wallets.');
            }
            $account->update(['is_active' => true]);
            return redirect()->back()->with('success', 'Account activated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error activating account: ' . $e->getMessage());
        }
    }

    public function deactivate($id)
    {
        try {
            $account = Account::findOrFail($id);
            if(in_array($account->wallet_type, ['main_company', 'office'])) {
                 return redirect()->back()->with('error', 'Cannot modify system wallets.');
            }
            $account->update(['is_active' => false]);
            return redirect()->back()->with('success', 'Account deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deactivating account: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $account = Account::findOrFail($id);
                
                if(in_array($account->wallet_type, ['main_company', 'office'])) {
                     throw new \Exception('Cannot delete system wallets.');
                }

                // Detach or delete related data if necessary, or let foreign keys handle it or soft deletes
                // For now, assuming hard delete is requested or just deleting the account record and user
                
                // Delete transactions? Or keep them? Usually frameworks prevent deletion if there are FKs.
                // If user wants "delete", we might need to delete user too.
                $user = $account->user;
                
                // Delete account first
                $account->delete();
                
                // Delete user if they don't have other accounts? 
                // Checks if user has other accounts
                if ($user->accounts()->count() == 0) {
                     $user->delete();
                }
            });

            return redirect()->route('accounts.index')->with('success', 'Account deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting account: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        try {
            $query = $request->get('search');
            $accounts = Account::with(['user', 'balances.currency'])
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
                ->where(function($q) use ($query) {
                    $q->where('account_number', 'like', "%{$query}%")
                      ->orWhereHas('user', function($subQ) use ($query) {
                          $subQ->where('name', 'like', "%{$query}%");
                      });
                })
                ->get();

            return view('accounts.index', compact('accounts'));
        } catch (\Exception $e) {
            return redirect()->route('accounts.index')
                ->with('error', 'Error searching accounts: ' . $e->getMessage());
        }
    }

    public function getBalances($id)
    {
        try {
            $account = Account::with('balances.currency')->findOrFail($id);
            return response()->json($account->balances);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
        public function negativeBalances(Request $request)
    {
        $startDate = $request->input('start_date', today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', today()->format('Y-m-d'));
        $balanceType = $request->input('balance_type', 'all');

        $accounts = Account::with(['user', 'balances.currency'])
            ->whereHas('balances', function($query) {
                $query->where('balance', '<', 0);
            })
            ->get();

        foreach ($accounts as $account) {
            $mainWallet = Account::getOrCreateMainWallet();
            
            // Get all transactions for this account from startDate onwards (to calculate opening balance)
            // But for the "Transaction Details" table, we only want [startDate, endDate]
            $allTransactionsFromStart = Transaction::with(['fromAccount.user', 'toAccount.user', 'currency'])
                ->where('status', 'completed')
                ->where(function($q) use ($account) {
                    $q->where('from_account_id', $account->id)
                      ->orWhere('to_account_id', $account->id);
                })
                ->whereDate('transaction_date', '>=', $startDate)
                ->orderBy('transaction_date', 'desc')
                ->get();

            // Filter for the specific period requested
            $account->filtered_transactions = $allTransactionsFromStart->filter(function($t) use ($endDate) {
                return $t->transaction_date->format('Y-m-d') <= $endDate;
            });

            // If balanceType is positive or negative, further filter
            if ($balanceType === 'positive') {
                $account->filtered_transactions = $account->filtered_transactions->filter(function($t) use ($account) {
                    return $t->to_account_id == $account->id && $t->from_account_id != $account->id;
                });
            } elseif ($balanceType === 'negative') {
                $account->filtered_transactions = $account->filtered_transactions->filter(function($t) use ($account) {
                    return $t->from_account_id == $account->id && $t->to_account_id != $account->id;
                });
            }

            // Calculate totals and impacts for each currency the account has
            $account->currency_summaries = $account->balances->map(function($balance) use ($account, $allTransactionsFromStart, $startDate, $endDate, $mainWallet) {
                $currency = $balance->currency;
                
                // Transactions for this specific currency
                $currencyTransactions = $allTransactionsFromStart->where('currency_id', $currency->id);
                
                // 1. Transactions in the [startDate, endDate] period
                $periodTransactions = $currencyTransactions->filter(function($t) use ($endDate) {
                    return $t->transaction_date->format('Y-m-d') <= $endDate;
                });

                // 2. Calculate Net Impact for the period (with In/Out breakdown)
                $periodNetImpact = 0;
                $totalIn = 0;
                $totalOut = 0;
                $totalCommissions = 0;

                foreach ($periodTransactions as $t) {
                    $isSender = $t->from_account_id == $account->id && $t->to_account_id != $account->id;
                    $isReceiver = $t->to_account_id == $account->id && $t->from_account_id != $account->id;

                    if ($isSender) {
                        $comm = ($account->id === $mainWallet->id) ? 0 : $t->company_commission;
                        $deduction = $t->amount + $comm;
                        $totalOut += $deduction;
                        $totalCommissions += $comm;
                        $periodNetImpact -= $deduction;
                    } elseif ($isReceiver) {
                        $totalIn += $t->amount;
                        $periodNetImpact += $t->amount;
                    } else {
                        // Adjustment/Self
                        if ($t->type === 'withdrawal') {
                            $totalOut += $t->amount;
                            $periodNetImpact -= $t->amount;
                        } else {
                            $totalIn += $t->amount;
                            $periodNetImpact += $t->amount;
                        }
                    }
                }

                // 3. Calculate Impact from endDate to NOW (to derive Opening/Closing balances)
                // Opening Balance (at startDate) = Current Balance - Net Impact from startDate to NOW
                $totalImpactFromStart = 0;
                foreach ($currencyTransactions as $t) {
                    if ($t->from_account_id == $account->id && $t->to_account_id != $account->id) {
                        $deduction = ($account->id === $mainWallet->id) ? $t->amount : ($t->amount + $t->company_commission);
                        $totalImpactFromStart -= $deduction;
                    } elseif ($t->to_account_id == $account->id && $t->from_account_id != $account->id) {
                        $totalImpactFromStart += $t->amount;
                    } else {
                        if ($t->type === 'withdrawal') $totalImpactFromStart -= $t->amount;
                        else $totalImpactFromStart += $t->amount;
                    }
                }

                $openingBalance = $balance->balance - $totalImpactFromStart;
                $closingBalance = $openingBalance + $periodNetImpact;

                return [
                    'currency' => $currency,
                    'current_balance' => $balance->balance,
                    'opening_balance' => $openingBalance,
                    'total_in' => $totalIn,
                    'total_out' => $totalOut,
                    'total_commissions' => $totalCommissions,
                    'period_net_impact' => $periodNetImpact,
                    'closing_balance' => $closingBalance,
                    'transaction_count' => $periodTransactions->count(),
                    'turkey_commission' => $periodTransactions->sum('turkey_commission'),
                ];
            });
        }

        // Calculate global summary for the report
        $allBalances = collect();
        foreach($accounts as $account) {
            foreach($account->currency_summaries as $summary) {
                $allBalances->push($summary);
            }
        }

        $globalSummary = $allBalances->groupBy(function($item) {
            return $item['currency']->id;
        })->map(function($group) {
            return [
                'currency' => $group->first()['currency'],
                'count' => $group->count(),
                'total_negative' => $group->where('current_balance', '<', 0)->sum('current_balance'),
                'total_positive' => $group->where('current_balance', '>', 0)->sum('current_balance'),
                'net_debt' => $group->sum('current_balance'),
                'period_total_in' => $group->sum('total_in'),
                'period_total_out' => $group->sum('total_out'),
            ];
        });

        return view('accounts.negative-balances', compact('accounts', 'startDate', 'endDate', 'balanceType', 'globalSummary'));
    }
}
