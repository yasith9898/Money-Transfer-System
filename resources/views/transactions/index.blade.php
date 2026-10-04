@extends('layouts.app')

@section('title', 'All Transactions')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800 font-weight-bold">
            <i class="fas fa-exchange-alt mr-2 text-primary"></i>Transaction Management
        </h1>
        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
            <a href="{{ route('transactions.transfer.create') }}" class="btn btn-primary d-flex align-items-center">
                <i class="fas fa-paper-plane mr-2"></i>New Transfer
            </a>
            <a href="{{ route('transactions.adjustment.create') }}" class="btn btn-warning text-dark font-weight-bold d-flex align-items-center">
                <i class="fas fa-edit mr-2"></i>Adjust Balance
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Overall Statistics -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-radius: 12px; border-left: 4px solid #4e73df !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1 tracking-wider">
                                Total Transactions</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $transactions->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-primary-soft">
                                <i class="fas fa-list fa-fw text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 py-2" style="border-radius: 12px; border-left: 4px solid #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1 tracking-wider">
                                Today's Transactions</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">{{ $todayCount }}</div>
                        </div>
                        <div class="col-auto">
                            <div class="icon-circle bg-success-soft">
                                <i class="fas fa-calendar-day fa-fw text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency-specific Statistics -->
    @if($currencyStats->count() > 0)
    <div class="row mb-5">
        <div class="col-12 mb-3">
             <h6 class="font-weight-bold text-gray-800 text-uppercase small tracking-widest pl-2">
                <i class="fas fa-chart-line mr-2 text-primary"></i>Currency Overviews
            </h6>
        </div>
        @foreach ($currencyStats as $stat)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-secondary-soft text-secondary px-3 py-2" style="font-size: 0.8rem;">
                            {{ $stat['currency']->name }} ({{ $stat['currency']->code }})
                        </span>
                    </div>
                    <div class="h5 mb-1 font-weight-bold text-gray-900">
                        {{ $stat['currency']->formatAmount($stat['total_amount']) }}
                    </div>
                    <div class="small font-weight-bold text-info mb-3">
                        Total + Comm: {{ $stat['currency']->formatAmount($stat['total_amount'] + $stat['total_company_commissions']) }}
                    </div>
                    <div class="text-xs text-muted mb-0 font-weight-bold text-uppercase">
                        <i class="fas fa-exchange-alt mr-1"></i> {{ $stat['transaction_count'] }} transactions
                    </div>
                </div>
                <div class="bg-secondary py-1 opacity-75"></div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Today's Activity -->
    @include('transactions.partials.todays_tables')

    <!-- Filters and Transactions Table -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header py-3 bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>All Transactions Explorer
            </h6>
            <a href="{{ route('transactions.all-transactions-report') }}" class="btn btn-sm btn-outline-primary shadow-sm" style="border-radius: 8px;">
                <i class="fas fa-file-invoice mr-1"></i>View Full Report
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="bg-gray-100 p-3 rounded mb-4">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-muted text-uppercase mb-1">Filter by Account</label>
                        <select class="form-control form-control-sm select-filter" id="accountFilter" onchange="applyFilters()">
                            <option value="all">All Accounts</option>
                            @foreach ($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->user->name }} ({{ $account->account_number }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-muted text-uppercase mb-1">Filter by Currency</label>
                        <select class="form-control form-control-sm select-filter" id="currencyFilter" onchange="applyFilters()">
                            <option value="all">All Currencies</option>
                            @foreach ($currencies as $currency)
                            <option value="{{ $currency->id }}" {{ request('currency_id') == $currency->id ? 'selected' : '' }}>
                                {{ $currency->name }} ({{ $currency->code }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label class="small font-weight-bold text-muted text-uppercase mb-1">Filter by Type</label>
                        <select class="form-control form-control-sm select-filter" id="typeFilter" onchange="applyFilters()">
                            <option value="all" {{ request('type') == 'all' || !request('type') ? 'selected' : '' }}>All Types</option>
                            <option value="history" {{ request('type') == 'history' ? 'selected' : '' }}>Transaction History</option>
                            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustments</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="transactionsTable" width="100%" cellspacing="0">
                    <thead class="bg-light x-small text-uppercase font-weight-bold text-muted">
                        <tr>
                            <th class="ps-3 py-3 rounded-left">Date & Time</th>
                            <th class="py-3">Transaction From</th>
                            <th class="py-3">To</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Company Comm</th>
                            <th class="py-3">Comp (%)</th>
                            <th class="py-3">Turkey Comm</th>
                            <th class="py-3">Turk (%)</th>
                            <th class="py-3">First Total</th>
                            <th class="py-3">Second Total</th>
                            <th class="py-3 text-success">Exchange Details</th>
                            <th class="py-3">Type</th>
                            <th class="py-3">Status</th>
                            <th class="pe-3 py-3 text-right rounded-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                        <tr class="border-bottom">
                            <td class="ps-3 py-3">
                                <div class="font-weight-bold text-dark mb-0">{{ $transaction->created_at->format('M j, Y') }}</div>
                                <div class="x-small text-muted">{{ $transaction->created_at->format('H:i') }}</div>
                            </td>
                            <td>
                                @if($transaction->fromAccount)
                                    <div class="small font-weight-bold text-dark">{{ $transaction->fromAccount->user->name }}</div>
                                    @if(!empty($transaction->fromAccount->user->company_name))
                                        <div class="x-small text-info">{{ $transaction->fromAccount->user->company_name }}</div>
                                    @endif
                                    <div class="x-small text-muted d-flex align-items-center mt-1">
                                        <i class="fas fa-wallet mr-1 text-primary"></i>
                                        {{ $transaction->fromAccount->account_number }}
                                    </div>
                                @else
                                    <div class="small text-muted font-italic">System</div>
                                @endif
                            </td>
                            <td>
                                @if($transaction->toAccount)
                                    <div class="small font-weight-bold text-dark">{{ $transaction->toAccount->user->name }}</div>
                                    @if(!empty($transaction->toAccount->user->company_name))
                                        <div class="x-small text-info">{{ $transaction->toAccount->user->company_name }}</div>
                                    @endif
                                    <div class="x-small text-muted d-flex align-items-center mt-1">
                                        <i class="fas fa-wallet mr-1 text-success"></i>
                                        {{ $transaction->toAccount->account_number }}
                                    </div>
                                @else
                                    <div class="small text-muted font-italic">System</div>
                                @endif
                            </td>
                            <td class="font-weight-bold text-primary">
                                {{ $transaction->currency->formatAmount($transaction->amount) }}
                                <div class="x-small text-muted mt-1 bg-light d-inline-block px-2 rounded">{{ $transaction->currency->code }}</div>
                            </td>
                            <td>
                                <div class="text-info font-weight-bold">
                                    {{ $transaction->currency->formatAmount($transaction->company_commission) }}
                                </div>
                            </td>
                            <td>
                                <div class="x-small text-secondary bg-light px-2 py-1 rounded d-inline-block">
                                    {{ $transaction->amount != 0 ? number_format((abs($transaction->company_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%
                                </div>
                            </td>
                            <td>
                                <div class="text-success font-weight-bold">
                                    {{ $transaction->currency->formatAmount($transaction->turkey_commission) }}
                                </div>
                            </td>
                            <td>
                                <div class="x-small text-secondary bg-light px-2 py-1 rounded d-inline-block">
                                    {{ $transaction->amount != 0 ? number_format((abs($transaction->turkey_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%
                                </div>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $transaction->currency->formatAmount($transaction->amount + $transaction->company_commission) }}</strong>
                            </td>
                            <td>
                                <strong class="text-success">{{ $transaction->currency->formatAmount($transaction->amount + $transaction->turkey_commission) }}</strong>
                            </td>
                            <td>
                                @if($transaction->exchange_rate)
                                    <div class="x-small text-muted font-weight-bold mb-1" title="Rate">
                                        <i class="fas fa-random text-primary mr-1"></i>
                                        Rate: {{ $transaction->exchange_rate }}
                                    </div>
                                    <div class="x-small text-muted font-weight-bold mb-1" title="Action">
                                        <i class="fas {{ $transaction->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} text-secondary mr-1"></i>
                                        {{ ucfirst($transaction->exchange_action) }}
                                    </div>
                                    <span class="badge bg-success-soft text-success px-2 py-1" style="font-size: 0.85rem;" title="Stored Result">
                                        ${{ number_format($transaction->exchange_result, 2) }}
                                    </span>
                                @else
                                    <div class="x-small text-muted font-italic mb-1">Auto-converted</div>
                                    <span class="badge bg-secondary-soft text-secondary px-2 py-1" style="font-size: 0.85rem;">
                                        ${{ number_format($transaction->currency->convertAmount($transaction->amount + $transaction->company_commission, 'USD'), 2) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if($transaction->type === 'transfer')
                                    <span class="badge badge-primary px-2 py-1 shadow-sm" style="background-color: #4e73df;">Transfer</span>
                                @elseif($transaction->type === 'deposit')
                                    <span class="badge badge-success px-2 py-1 shadow-sm" style="background-color: #1cc88a;">Deposit</span>
                                @elseif($transaction->type === 'withdrawal')
                                    <span class="badge badge-danger px-2 py-1 shadow-sm" style="background-color: #e74a3b;">Withdrawal</span>
                                @else
                                    <span class="badge badge-warning text-white px-2 py-1 shadow-sm" style="background-color: #f6c23e;">Adjustment</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->status === 'completed')
                                    <span class="badge badge-success px-2 py-1 shadow-sm" style="background-color: #1cc88a;">Completed</span>
                                @elseif($transaction->status === 'auto')
                                    <span class="badge badge-info px-2 py-1 shadow-sm" style="background-color: #36b9cc;">Auto</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge badge-warning text-white px-2 py-1 shadow-sm" style="background-color: #f6c23e;">Pending</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge badge-danger px-2 py-1 shadow-sm" style="background-color: #e74a3b;">Failed</span>
                                @else
                                    <span class="badge badge-secondary px-2 py-1 shadow-sm" style="background-color: #858796;">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td class="pe-3 text-right">
                                <div class="btn-group">
                                    <a href="{{ route('transactions.show', $transaction->id) }}"
                                       class="btn btn-primary btn-sm rounded shadow-sm mr-1" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($transaction->status === 'completed')
                                    <form action="{{ route('transactions.void', $transaction->id) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to void this transaction?')">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm rounded shadow-sm" title="Void Transaction">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center py-5">
                                <div class="text-muted mb-3">
                                    <i class="fas fa-exchange-alt fa-3x text-gray-300"></i>
                                </div>
                                <h6 class="font-weight-bold text-gray-600">No transactions found</h6>
                                <p class="text-gray-500 mb-0">
                                    <a href="{{ route('transactions.transfer.create') }}" class="text-primary font-weight-bold text-decoration-none">Click here to create the first transaction</a>
                                </p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        @forelse ($footerStats as $stat)
                        <tr class="bg-gray-100 font-weight-bold text-dark x-small border-top-0">
                            <td colspan="3" class="text-right py-3 pe-4 text-muted text-uppercase">Totals ({{ $stat->currency->code }}):</td>
                            <td class="text-primary py-3">{{ $stat->currency->formatAmount($stat->total_amount) }}</td>
                            <td class="text-info py-3">{{ $stat->currency->formatAmount($stat->total_company_commission) }}</td>
                            <td class="text-secondary py-3">
                                ({{ $stat->total_amount != 0 ? number_format((abs($stat->total_company_commission) / abs($stat->total_amount)) * 100, 2) : '0.00' }}%)
                            </td>
                            <td class="text-success py-3">{{ $stat->currency->formatAmount($stat->total_turkey_commission) }}</td>
                            <td class="text-secondary py-3">
                                ({{ $stat->total_amount != 0 ? number_format((abs($stat->total_turkey_commission) / abs($stat->total_amount)) * 100, 2) : '0.00' }}%)
                            </td>
                            <td class="py-3 bg-secondary text-white rounded">{{ $stat->currency->formatAmount($stat->total_amount + $stat->total_company_commission) }}</td>
                            <td class="py-3 bg-secondary text-white rounded">{{ $stat->currency->formatAmount($stat->total_amount + $stat->total_turkey_commission) }}</td>
                            <td class="text-success py-3">${{ number_format($stat->currency->convertAmount($stat->total_amount + $stat->total_company_commission, 'USD'), 2) }}</td>
                            <td colspan="3"></td>
                        </tr>
                        @empty
                        <tr class="bg-gray-100 font-weight-bold text-dark x-small">
                            <td colspan="3" class="text-right py-3 text-muted text-uppercase">Totals:</td>
                            <td colspan="11" class="py-3">No data available</td>
                        </tr>
                        @endforelse
                    </tfoot>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
            <div class="d-flex justify-content-center mt-4 pt-3 border-top">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1) !important; color: #4e73df; }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1) !important; color: #1cc88a; }
    .bg-danger-soft { background-color: rgba(231, 74, 59, 0.1) !important; color: #e74a3b; }
    .bg-warning-soft { background-color: rgba(246, 194, 62, 0.1) !important; color: #f6c23e; }
    .bg-info-soft { background-color: rgba(54, 185, 204, 0.1) !important; color: #36b9cc; }
    .bg-secondary-soft { background-color: rgba(133, 135, 150, 0.1) !important; color: #858796; }
    
    .icon-circle {
        height: 3rem;
        width: 3rem;
        border-radius: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-widest { letter-spacing: 0.1em; }
    .x-small { font-size: 0.75rem; }
    .bg-gray-100 { background-color: #f8f9fc !important; }
    
    .select-filter {
        border-radius: 6px;
        border: 1px solid #d1d3e2;
        padding: 0.375rem 0.75rem;
    }
    .select-filter:focus {
        border-color: #bac8f3;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .table-hover tbody tr.transaction-row:hover {
        background-color: rgba(78, 115, 223, 0.03);
        cursor: pointer;
    }
</style>
@endsection

@push('scripts')
<script>
// Apply filters function
function applyFilters() {
    const accountId = document.getElementById('accountFilter').value;
    const currencyId = document.getElementById('currencyFilter').value;
    const type = document.getElementById('typeFilter').value;

    const params = new URLSearchParams(window.location.search);

    if (accountId !== 'all') {
        params.set('account_id', accountId);
    } else {
        params.delete('account_id');
    }

    if (currencyId !== 'all') {
        params.set('currency_id', currencyId);
    } else {
        params.delete('currency_id');
    }

    if (type !== 'all') {
        params.set('type', type);
    } else {
        params.delete('type');
    }

    // Remove page parameter to reset pagination
    params.delete('page');

    // Show loading state manually if needed, or simply let it load
    document.getElementById('transactionsTable').style.opacity = '0.5';

    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = newUrl;
}

// Make rows clickable
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#transactionsTable tbody tr');
    rows.forEach(row => {
        // Add hover effect class
        row.classList.add('transaction-row');
        
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons
            if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('.badge')) {
                const actionBtn = this.querySelector('a.btn-primary[title="View Details"]');
                if (actionBtn) {
                    const transactionUrl = actionBtn.getAttribute('href');
                    if (transactionUrl) {
                        window.location.href = transactionUrl;
                    }
                }
            }
        });
    });
});
</script>
@endpush
