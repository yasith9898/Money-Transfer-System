<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the currencies.
     */
    public function index()
    {
        $currencies = Currency::latest()->paginate(10);
        return view('currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new currency.
     */
    public function create()
    {
        return view('currencies.create');
    }

    /**
     * Store a newly created currency in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:3|unique:currencies',
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4'
        ]);

        try {
            Currency::create([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'symbol' => $request->symbol,
                'decimal_places' => $request->decimal_places,
                'is_active' => true
            ]);

            return redirect()->route('currencies.index')
                ->with('success', 'Currency created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create currency: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $currency = Currency::with(['accountBalances.account.user'])
            ->findOrFail($id);

        $totalBalance = $currency->getTotalBalance();
        $activeAccountsCount = $currency->getActiveAccountsCount();

        // Transaction Statistics
        $stats = $currency->transactions()
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as count,
                SUM(amount) as total_volume,
                SUM(company_commission) as total_company_comm,
                SUM(turkey_commission) as total_turkey_comm
            ')
            ->first();

        // Top Accounts by Balance (excluding system wallets)
        $topAccounts = $currency->accountBalances()
            ->whereHas('account', function($q) {
                $q->whereNull('wallet_type')
                  ->orWhereNotIn('wallet_type', ['office']);
            })
            ->with('account.user')
            ->orderByDesc('balance')
            ->take(10)
            ->get();

        // Recent transactions for the table (paginated)
        $recentTransactions = $currency->transactions()
            ->with(['fromAccount.user', 'toAccount.user', 'currency'])
            ->latest()
            ->paginate(15);

        return view('currencies.show', compact(
            'currency', 
            'totalBalance', 
            'activeAccountsCount',
            'stats',
            'topAccounts',
            'recentTransactions'
        ));
    }

    /**
     * Show the form for editing the specified currency.
     */
    public function edit($id)
    {
        $currency = Currency::findOrFail($id);
        return view('currencies.edit', compact('currency'));
    }

    /**
     * Update the specified currency in storage.
     */
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code,' . $currency->id,
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:10',
            'decimal_places' => 'required|integer|min:0|max:4'
        ]);

        try {
            $currency->update([
                'code' => strtoupper($request->code),
                'name' => $request->name,
                'symbol' => $request->symbol,
                'decimal_places' => $request->decimal_places
            ]);

            return redirect()->route('currencies.index')
                ->with('success', 'Currency updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update currency: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle currency status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $currency = Currency::findOrFail($id);

        try {
            $currency->update(['is_active' => !$currency->is_active]);

            $status = $currency->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Currency {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update currency status: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified currency from storage.
     */
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);

        // Check if currency can be deleted
        if (!$currency->canBeDeleted()) {
            return redirect()->back()
                ->with('error', 'Cannot delete currency. It has associated transactions or account balances.');
        }

        try {
            $currency->delete();

            return redirect()->route('currencies.index')
                ->with('success', 'Currency deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete currency: ' . $e->getMessage());
        }
    }

    /**
     * Get currencies for dropdown (API)
     */
    public function getCurrencies()
    {
        $currencies = Currency::active()
            ->select('id', 'code', 'name', 'symbol')
            ->get();

        return response()->json([
            'success' => true,
            'currencies' => $currencies
        ]);
    }

    /**
     * Get exchange rates (API)
     */
    public function getExchangeRates()
    {
        $baseCurrency = Currency::getBaseCurrency();
        $currencies = Currency::active()->where('id', '!=', $baseCurrency->id)->get();

        $rates = [];
        foreach ($currencies as $currency) {
            $rates[$currency->code] = $baseCurrency->getExchangeRate($currency->code);
        }

        return response()->json([
            'success' => true,
            'base_currency' => $baseCurrency->code,
            'rates' => $rates,
            'last_updated' => now()->toISOString()
        ]);
    }
}
