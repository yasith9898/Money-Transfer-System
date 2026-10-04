@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-wallet me-2"></i>Total Accounts</h5>
                    <h2 class="mb-0">{{ $totalAccounts ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exchange-alt me-2"></i>Today's Transfers</h5>
                    <h2 class="mb-0">{{ $todayTransactions ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-edit me-2"></i>Today's Adjust</h5>
                    <h2 class="mb-0">{{ $todayAdjustments ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-coins me-2"></i>Active Currencies</h5>
                    <h2 class="mb-0">{{ $activeCurrencies ?? 0 }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- System Wallets Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card border-success shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Company Wallet Balances</h5>
                </div>
                <div class="card-body">
                    @if(isset($companyWalletBalances) && $companyWalletBalances->count() > 0)
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($companyWalletBalances as $balance)
                                <tr>
                                    <td>
                                        <strong>{{ $balance->currency->code }}</strong> 
                                        <span class="text-muted">({{ $balance->currency->name }})</span>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">
                                            {{ $balance->currency->symbol }} {{ number_format($balance->balance, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted mb-0"><i class="fas fa-info-circle me-2"></i>No balances available</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Actions and Reports -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create New Account
                        </a>
                        <a href="{{ route('transactions.transfer.create') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-exchange-alt me-2"></i>Make a Transfer
                        </a>
                        <a href="{{ route('currencies.create') }}" class="btn btn-info btn-lg">
                            <i class="fas fa-coins me-2"></i>Add Currency
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('transactions.daily-report') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-calendar-day me-2"></i>Daily Report
                        </a>
                        <a href="{{ route('transactions.commission-report') }}" class="btn btn-outline-success btn-lg">
                            <i class="fas fa-chart-line me-2"></i>Commission Report
                        </a>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-info btn-lg">
                            <i class="fas fa-list me-2"></i>All Transactions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Management (Super Admin Only) -->
    @if(auth()->check() && auth()->user()->role === 'super_admin')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Admin Management</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-danger btn-lg w-100">
                                <i class="fas fa-users-cog me-2"></i>Manage Admins
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-warning btn-lg w-100">
                                <i class="fas fa-user-plus me-2"></i>Add New Admin
                            </a>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center pt-2">
                                <h6 class="text-muted">Total Admins</h6>
                                <h3 class="text-danger mb-0">
                                    {{ \App\Models\User::whereIn('role', ['admin', 'super_admin'])->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.card {
    border-radius: 10px;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.shadow {
    box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15) !important;
}

.bg-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.bg-success {
    background: linear-gradient(135deg, #1cc88a 0%, #0d8f65 100%) !important;
}

.bg-info {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%) !important;
}

.bg-warning {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%) !important;
}

.bg-danger {
    background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%) !important;
}

.border-success {
    border: 2px solid #1cc88a !important;
}

.border-info {
    border: 2px solid #36b9cc !important;
}

.border-danger {
    border: 2px solid #e74a3b !important;
}

.btn-lg {
    padding: 12px 20px;
    font-size: 16px;
    font-weight: 600;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.025);
}

h1 {
    color: #5a5c69;
    font-weight: 700;
}

.card-title {
    font-size: 14px;
    margin-bottom: 10px;
    opacity: 0.9;
}

.card-body h2 {
    font-size: 32px;
    font-weight: 700;
}

@media (max-width: 768px) {
    .col-md-3, .col-md-6, .col-md-4 {
        margin-bottom: 15px;
    }
}
</style>
@endsection
