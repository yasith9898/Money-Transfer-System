<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home and Dashboard Routes
Route::get('/', function () {
    return redirect()->route('admin.login');
})->name('home');

// Main dashboard redirects to admin dashboard
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth', 'admin'])->name('dashboard');


// Protected Routes - Admin Only
Route::middleware(['auth', 'admin'])->group(function () {
    // Account Routes
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/search', [AccountController::class, 'search'])->name('accounts.search');
        Route::get('/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('/negative-balances', [AccountController::class, 'negativeBalances'])->name('accounts.negative-balances');
        Route::get('/{id}', [AccountController::class, 'show'])->name('accounts.show');
        Route::get('/{id}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('/{id}', [AccountController::class, 'update'])->name('accounts.update');
        Route::post('/{id}/deactivate', [AccountController::class, 'deactivate'])->name('accounts.deactivate');
        Route::post('/{id}/activate', [AccountController::class, 'activate'])->name('accounts.activate');
        Route::delete('/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy');
        Route::post('/{id}/adjust-balance', [AccountController::class, 'adjustBalance'])->name('accounts.adjust-balance');
        Route::get('/{id}/statement', [AccountController::class, 'statement'])->name('accounts.statement');
        Route::get('/{id}/balances', [AccountController::class, 'getBalances'])->name('accounts.balances');
    });

    // Transaction Routes
    Route::prefix('transactions')->group(function () {
        // Main transactions page
        Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');

        // Transfer routes
        Route::get('/transfer', [TransactionController::class, 'createTransfer'])->name('transactions.transfer.create');
        Route::post('/transfer', [TransactionController::class, 'storeTransfer'])->name('transactions.transfer.store');

        // Adjustment routes
        Route::get('/adjustment', [TransactionController::class, 'createAdjustment'])->name('transactions.adjustment.create');
        Route::post('/adjustment', [TransactionController::class, 'adjustBalance'])->name('transactions.adjustment.store');

        // Transaction management
        Route::get('/{id}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::post('/{id}/void', [TransactionController::class, 'voidTransaction'])->name('transactions.void');

        // Report routes
        Route::get('/reports/daily', [TransactionController::class, 'dailyReport'])->name('transactions.daily-report');
        Route::get('/reports/daily/pdf', [TransactionController::class, 'downloadDailyReportPDF'])->name('transactions.daily-report.pdf');
        Route::get('/reports/daily-summary', [TransactionController::class, 'dailySummaryReport'])->name('transactions.daily-summary');
        Route::get('/reports/daily-summary/pdf', [TransactionController::class, 'downloadDailySummaryPDF'])->name('transactions.daily-summary.pdf');
        Route::get('/reports/all-transactions', [TransactionController::class, 'allTransactionsReport'])->name('transactions.all-transactions-report');
        Route::get('/reports/all-transactions/pdf', [TransactionController::class, 'allTransactionsReportPDF'])->name('transactions.all-transactions-report.pdf');
        Route::get('/reports/commissions', [TransactionController::class, 'commissionReport'])->name('transactions.commission-report');
        Route::get('/reports/commissions/pdf', [TransactionController::class, 'downloadCommissionReportPDF'])->name('transactions.commission-report.pdf');
        Route::get('/reports/account-balances', [TransactionController::class, 'accountBalancesReport'])->name('transactions.account-balances-report');
        Route::get('/reports/account-balances/pdf', [TransactionController::class, 'accountBalancesReportPDF'])->name('transactions.account-balances-report.pdf');
        Route::get('/reports/company-transactions', [TransactionController::class, 'companyReport'])->name('transactions.company-report');
        Route::get('/reports/company-transactions/pdf', [TransactionController::class, 'companyReportPDF'])->name('transactions.company-report.pdf');
        Route::get('/account/{accountId}/statement', [TransactionController::class, 'accountStatement'])->name('transactions.account-statement');

        // API routes for AJAX calls
        Route::get('/api/account-balance', [TransactionController::class, 'getAccountBalance'])->name('transactions.get-account-balance');
    });

    // Currency Routes
    Route::prefix('currencies')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('currencies.index');
        Route::get('/create', [CurrencyController::class, 'create'])->name('currencies.create');
        Route::post('/', [CurrencyController::class, 'store'])->name('currencies.store');
        Route::get('/{id}', [CurrencyController::class, 'show'])->name('currencies.show');
        Route::get('/{id}/edit', [CurrencyController::class, 'edit'])->name('currencies.edit');
        Route::put('/{id}', [CurrencyController::class, 'update'])->name('currencies.update');
        Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('currencies.destroy');
        Route::post('/{id}/toggle-status', [CurrencyController::class, 'toggleStatus'])->name('currencies.toggle-status');
    });
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/login', [UserController::class, 'adminLogin'])->name('admin.login');
    Route::post('/login', [UserController::class, 'adminLoginPost'])->name('admin.login.post');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', [UserController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/users/search', [UserController::class, 'search'])->name('admin.users.search');
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.users.show');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    });
});

// Admin Profile Routes (for logged-in admins)
Route::middleware(['auth', 'admin'])->prefix('admin/profile')->group(function () {
    Route::get('/', [UserController::class, 'profile'])->name('admin.profile');
    Route::put('/', [UserController::class, 'updateProfile'])->name('admin.profile.update');
    Route::get('/change-password', [UserController::class, 'changePassword'])->name('admin.change-password');
    Route::post('/change-password', [UserController::class, 'updatePassword'])->name('admin.update-password');
});

// Logout Route
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('admin.login')->with('success', 'Logged out successfully');
})->name('logout');

// Fallback route - redirect to admin login
Route::fallback(function () {
    return redirect()->route('admin.login');
});
