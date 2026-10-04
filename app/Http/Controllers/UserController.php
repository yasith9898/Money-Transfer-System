<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display user profile
     */
    public function profile()
    {
        $user = auth()->user();
        $accounts = $user->accounts()->with('balances.currency')->get();

        $balances = [];
        foreach ($accounts as $account) {
            foreach ($account->balances as $balance) {
                $currencyCode = $balance->currency->code;
                if (!isset($balances[$currencyCode])) {
                    $balances[$currencyCode] = 0;
                }
                $balances[$currencyCode] += $balance->balance;
            }
        }

        return view('admin.profile', compact('user', 'accounts', 'balances'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ]
        ]);

        try {
            $user->update([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'mobile' => $request->mobile,
                'email' => $request->email
            ]);

            return redirect()->route('admin.profile')
                ->with('success', 'Profile updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update profile: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show change password form
     */
    public function changePassword()
    {
        return view('admin.change-password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required'
        ]);

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->with('error', 'Current password is incorrect!')
                ->withInput();
        }

        try {
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return redirect()->route('admin.profile')
                ->with('success', 'Password changed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to change password: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display all users (for admin)
     */
    public function index()
    {
        // Only show admin and super_admin users (no regular users)
        $users = User::with(['accounts'])
            ->whereIn('role', ['admin', 'super_admin'])
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details
     */
    public function show($id)
    {
        $user = User::with(['accounts.balances.currency'])->findOrFail($id);

        $totalBalances = [];
        foreach ($user->accounts as $account) {
            foreach ($account->balances as $balance) {
                $currencyCode = $balance->currency->code;
                if (!isset($totalBalances[$currencyCode])) {
                    $totalBalances[$currencyCode] = 0;
                }
                $totalBalances[$currencyCode] += $balance->balance;
            }
        }

        return view('admin.users.show', compact('user', 'totalBalances'));
    }

    /**
     * Create new user (admin function)
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store new user (admin function)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:admin,super_admin'
        ]);

        try {
            // Create User (Admin only - no regular users)
            $user = User::create([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => true
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'Admin user created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Edit user (admin function)
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update user (admin function)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentUser = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'role' => 'required|in:user,admin,super_admin'
        ]);

        // Check if current user can update this user's role
        if ($request->role === 'super_admin' && $currentUser->role !== 'super_admin') {
            return redirect()->back()
                ->with('error', 'Only super admins can assign super admin role.')
                ->withInput();
        }
        if ($request->role === 'admin' && !in_array($currentUser->role, ['admin', 'super_admin'])) {
            return redirect()->back()
                ->with('error', 'Insufficient permissions to assign admin role.')
                ->withInput();
        }
        // Prevent users from changing their own role to lower level
        if ($user->id === $currentUser->id && $request->role !== $currentUser->role) {
            return redirect()->back()
                ->with('error', 'You cannot change your own role.')
                ->withInput();
        }

        try {
            $user->update([
                'name' => $request->name,
                'company_name' => $request->company_name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'role' => $request->role
            ]);

            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'User updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle user status (admin function)
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        // Prevent users from deactivating themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        try {
            // Deactivate all user's accounts when deactivating user (if they have any)
            if ($user->is_active && $user->accounts()->exists()) {
                $user->accounts()->update(['is_active' => false]);
            }

            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "User {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update user status: ' . $e->getMessage());
        }
    }

    /**
     * Get user statistics (API)
     */
    public function getUserStats($id)
    {
        $user = User::with(['accounts'])->findOrFail($id);

        $stats = [
            'total_accounts' => $user->accounts->count(),
            'active_accounts' => $user->accounts->where('is_active', true)->count(),
            'total_balance_usd' => $user->getTotalBalanceInUSD(),
            'joined_date' => $user->created_at->format('M j, Y')
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Search users
     */
    public function search(Request $request)
    {
        $search = $request->get('search');

        // Only search admin and super_admin users
        $users = User::where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->whereIn('role', ['admin', 'super_admin'])
            ->with(['accounts'])
            ->latest()
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show admin login form
     */
    public function adminLogin()
    {
        if (auth()->check() && in_array(auth()->user()->role, ['admin', 'super_admin'])) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function adminLoginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Check if account is active
            if (!$user->is_active) {
                auth()->logout();
                return redirect()->back()
                    ->with('error', 'Your account has been deactivated. Please contact the administrator.')
                    ->withInput($request->only('email'));
            }

            if (in_array($user->role, ['admin', 'super_admin'])) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                auth()->logout();
                return redirect()->back()
                    ->with('error', 'Access denied. Admin privileges required.')
                    ->withInput($request->only('email'));
            }
        }

        return redirect()->back()
            ->with('error', 'Invalid credentials or insufficient privileges.')
            ->withInput($request->only('email'));
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        try {
            // Get active accounts
            // Get active accounts (excluding admins)
            // Get total accounts (matching Accounts page logic)
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
            
            // Today's transfers (excluding adjustments)
            $todayTransactions = \App\Models\Transaction::whereDate('created_at', today())
                ->whereIn('type', ['transfer', 'deposit', 'withdrawal'])
                ->count();
            
            // Today's adjustments
            $todayAdjustments = \App\Models\Transaction::whereDate('created_at', today())
                ->where('type', 'adjustment')
                ->count();
            
            // Active currencies
            $activeCurrencies = \App\Models\Currency::where('is_active', true)->count();
            
            // Get system wallets
            $companyWallet = Account::getOrCreateMainWallet();
            
            // Get wallet balances with currency details
            $companyWalletBalances = $companyWallet->balances()->with('currency')->get();
            
            return view('admin.dashboard', compact(
                'totalAccounts',
                'todayTransactions',
                'todayAdjustments',
                'activeCurrencies',
                'companyWalletBalances'
            ));
            
        } catch (\Exception $e) {
            return view('admin.dashboard', [
                'totalAccounts' => 0,
                'todayTransactions' => 0,
                'activeCurrencies' => 0,
                'totalBalanceUSD' => 0,
                'companyWalletBalances' => collect([]),
                'officeWalletBalances' => collect([]),
                'error' => $e->getMessage()
            ]);
        }
    }
}
