@extends('layouts.app')

@section('title', 'Currency Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Currency Details: {{ $currency->name }} ({{ $currency->code }})</h1>
            <p class="text-muted mb-0">View currency information and statistics</p>
        </div>
        <div>
            <a href="{{ route('currencies.edit', $currency->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit Currency
            </a>
            <a href="{{ route('currencies.index') }}" class="btn btn-secondary ms-2">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Currency Information Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Currency Info</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $currency->name }}</div>
                            <div class="mt-2">
                                <span class="badge bg-primary">{{ $currency->code }}</span>
                                <span class="badge bg-info">{{ $currency->symbol }}</span>
                                @if($currency->is_active)
                                    <span class="badge badge-success" style="background-color: #1cc88a; padding: 0.5em 0.75em;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="background-color: #e74a3b; padding: 0.5em 0.75em;">Inactive</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Balance Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total System Balance</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currency->symbol }} {{ number_format($totalBalance, $currency->decimal_places) }}
                            </div>
                            <p class="text-muted text-sm mt-2 mb-0">Held across {{ $activeAccountsCount }} accounts</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-wallet fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Volume Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Transaction Volume</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currency->symbol }} {{ number_format($stats->total_volume ?? 0, $currency->decimal_places) }}
                            </div>
                            <p class="text-muted text-sm mt-2 mb-0">{{ $stats->count ?? 0 }} completed transactions</p>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exchange-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commissions Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Commissions Earned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $currency->symbol }} {{ number_format(($stats->total_company_comm ?? 0) + ($stats->total_turkey_comm ?? 0), $currency->decimal_places) }}
                            </div>
                            <div class="text-xs mt-2">
                                <span class="text-primary">Company: {{ number_format($stats->total_company_comm ?? 0, 2) }}</span> | 
                                <span class="text-success">Turkey: {{ number_format($stats->total_turkey_comm ?? 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Accounts Table -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Accounts by Balance ({{ $currency->code }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Account</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topAccounts as $balance)
                                    <tr>
                                        <td>
                                            <a href="{{ route('accounts.show', $balance->account->id) }}">
                                                {{ $balance->account->user->name ?? $balance->account->account_number }}
                                            </a>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            {{ $currency->symbol }} {{ number_format($balance->balance, $currency->decimal_places) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">No balances found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Transactions ({{ $currency->code }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>From/To</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $transaction)
                                    <tr>
                                        <td><small>{{ $transaction->created_at->format('M d, H:i') }}</small></td>
                                        <td>
                                            <span class="badge badge-sm bg-{{ $transaction->type === 'transfer' ? 'primary' : ($transaction->type === 'adjustment' ? 'warning' : 'info') }}" style="font-size: 0.7rem;">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($transaction->fromAccount)
                                                <small class="text-danger">{{ $transaction->fromAccount->account_number }}</small>
                                            @else
                                                <small class="text-muted">System</small>
                                            @endif
                                            <i class="fas fa-long-arrow-alt-right mx-1 text-muted"></i>
                                            @if($transaction->toAccount)
                                                <small class="text-success">{{ $transaction->toAccount->account_number }}</small>
                                            @else
                                                <small class="text-muted">System</small>
                                            @endif
                                        </td>
                                        <td class="font-weight-bold">
                                            {{ $currency->symbol }}{{ number_format($transaction->amount, $currency->decimal_places) }}
                                        </td>
                                        <td>
                                            @if($transaction->status == 'completed')
                                                <i class="fas fa-check-circle text-success" title="Completed"></i>
                                            @elseif($transaction->status == 'pending')
                                                <i class="fas fa-clock text-warning" title="Pending"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger" title="Failed"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No recent transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $recentTransactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
