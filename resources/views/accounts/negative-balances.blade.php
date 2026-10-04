@extends('layouts.app')

@section('title', 'Negative Balance Accounts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1 class="h3 mb-0 text-gray-800 text-danger">
            <i class="fas fa-exclamation-circle mr-2"></i>Negative Balance Accounts
        </h1>
        <div class="btn-group">
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to All Accounts
            </a>
            <button onclick="window.print()" class="btn btn-info">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Filters & Reports Stats -->
    <div class="row align-items-stretch no-print mb-4">
        <!-- Filter Form -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow border-0" style="border-radius: 12px; background: #fff;">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter mr-2"></i>Filters (Period Transactions Detailed View Only)
                    </h6>
                    <small class="text-muted">Filtering {{ $accounts->count() }} accounts</small>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('accounts.negative-balances') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="start_date" class="small font-weight-bold text-uppercase text-muted">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" max="{{ $endDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="end_date" class="small font-weight-bold text-uppercase text-muted">End Date</label>
                                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="balance_type" class="small font-weight-bold text-uppercase text-muted">History View Mode</label>
                                    <select name="balance_type" id="balance_type" class="form-control">
                                        <option value="all" {{ $balanceType == 'all' ? 'selected' : '' }}>Complete History</option>
                                        <option value="positive" {{ $balanceType == 'positive' ? 'selected' : '' }}>Inflows Only (+)</option>
                                        <option value="negative" {{ $balanceType == 'negative' ? 'selected' : '' }}>Outflows Only (-)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-sync-alt mr-2"></i>Refresh Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards (Global Debt) -->
    <div class="row mb-5">
        <div class="col-12 mb-3">
            <h6 class="font-weight-bold text-gray-800 text-uppercase small tracking-widest pl-2">
                <i class="fas fa-chart-pie mr-2 text-danger"></i>Report Summary - Total Net Debt Portfolio (All Accounts)
            </h6>
        </div>
        @foreach($globalSummary as $summary)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #fffefe 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem;">{{ $summary['currency']->code }}</span>
                            <small class="text-danger font-weight-bold text-uppercase">Total Debt Outstanding</small>
                        </div>
                        <div class="row mb-3">
                            <div class="col-12">
                                <h3 class="font-weight-bold text-danger mb-0">
                                    {{ number_format($summary['total_negative'], 2) }}
                                </h3>
                                <small class="text-muted text-uppercase x-small d-block">SUM OF ALL NEGATIVE BALANCES</small>
                            </div>
                        </div>
                        <div class="border-top pt-3 mt-1">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="small text-muted">Accounts with Neg. Bal:</span>
                                <span class="small font-weight-bold text-dark">{{ $summary['count'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small text-muted">Net Debt Position:</span>
                                <span class="small font-weight-bold text-primary">{{ number_format($summary['net_debt'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between bg-light p-2 mt-2" style="border-radius: 6px;">
                                <span class="x-small text-muted">Total Inflow (Period):</span>
                                <span class="x-small font-weight-bold text-success">+{{ number_format($summary['period_total_in'], 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row no-print mb-4">
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm" style="border-radius: 10px; background-color: #fdf5e6;">
                <i class="fas fa-info-circle mr-2"></i>This report only lists accounts that have a <strong>negative balance</strong> in at least one currency.
            </div>
        </div>
    </div>

    @forelse ($accounts as $account)
    <div class="card shadow-lg mb-5 border-0 overflow-hidden" style="border-radius: 15px;">
        <div class="card-header border-0 bg-gradient-danger py-4" style="background: linear-gradient(135deg, #e74a3b 0%, #be2617 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="m-0 font-weight-bold text-white">{{ $account->user->name }}</h4>
                    <div class="text-white-50 small mt-1">
                        <i class="fas fa-hashtag mr-1"></i>{{ $account->account_number }} 
                        <span class="mx-2">|</span> 
                        <i class="fas fa-building mr-1"></i>{{ $account->user->company_name }}
                    </div>
                </div>
                <div class="no-print">
                    <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-sm btn-light border-0 px-3 mr-2 shadow-sm" style="border-radius: 8px; color: #e74a3b;">
                        <i class="fas fa-user mr-1"></i>Profile
                    </a>
                    <a href="{{ route('accounts.statement', $account->id) }}" class="btn btn-sm btn-dark border-0 px-3 shadow-sm" style="border-radius: 8px;">
                        <i class="fas fa-list mr-1"></i>Full Statement
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <!-- Summary Row -->
            <div class="row mb-5">
                @foreach ($account->currency_summaries as $summary)
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-{{ $summary['current_balance'] < 0 ? 'danger' : 'primary' }} shadow-sm h-100 py-2 bg-light border-0" style="border-radius: 10px;">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-{{ $summary['current_balance'] < 0 ? 'danger' : 'primary' }} text-uppercase mb-1">
                                            Current {{ $summary['currency']->code }} Balance
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                                            {{ $summary['currency']->formatAmount($summary['current_balance']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-wallet fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Detailed Balance Logic Table -->
            <div class="mb-4">
                <h6 class="font-weight-bold text-gray-800 mb-3 ml-1 text-uppercase small tracking-wider">
                    <i class="fas fa-calculator mr-2 text-info"></i>Balance Statement for Period
                </h6>
                <div class="table-responsive shadow-sm" style="border-radius: 12px; border: 1px solid #e3e6f0;">
                    <table class="table table-hover mb-0">
                        <thead class="bg-gray-100 text-gray-800 x-small text-uppercase font-weight-bold">
                            <tr>
                                <th class="py-3 px-4">Currency</th>
                                <th class="text-right py-3 text-success">Total In (+)</th>
                                <th class="text-right py-3 text-danger">Total Out (-)</th>
                                <th class="text-right py-3 text-muted">Fees/Comm</th>
                                <th class="text-right py-3 bg-light font-weight-bolder" style="width: 200px;">Closing Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($account->currency_summaries as $summary)
                            <tr>
                                <td class="font-weight-bold align-middle px-4 py-3">{{ $summary['currency']->code }}</td>
                                <td class="text-right align-middle py-3 text-success font-weight-bold">
                                    +{{ $summary['currency']->formatAmount($summary['total_in']) }}
                                </td>
                                <td class="text-right align-middle py-3 text-danger font-weight-bold">
                                    -{{ $summary['currency']->formatAmount($summary['total_out'] - $summary['total_commissions']) }}
                                </td>
                                <td class="text-right align-middle py-3 text-muted small">
                                    {{ $summary['currency']->formatAmount($summary['total_commissions']) }}
                                </td>
                                <td class="text-right align-middle py-3 bg-gray-100 font-weight-bolder {{ $summary['closing_balance'] < 0 ? 'text-danger' : 'text-primary' }}">
                                    {{ $summary['currency']->formatAmount($summary['closing_balance']) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detailed Transactions -->
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold text-gray-800 mb-0">
                        <i class="fas fa-exchange-alt mr-2 text-primary"></i>Transaction History ({{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }})
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover" style="border-radius: 10px; overflow: hidden;">
                        <thead class="bg-dark text-white" style="font-size: 0.75rem;">
                            <tr>
                                <th class="py-3 px-3">DATE/TIME</th>
                                <th class="py-3">TYPE</th>
                                <th class="py-3">DESCRIPTION</th>
                                <th class="py-3 text-right">PRINCIPAL</th>
                                <th class="py-3 text-right">COMM</th>
                                <th class="py-3 text-right px-3">NET IMPACT</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.85rem;">
                            @forelse ($account->filtered_transactions as $transaction)
                            <tr class="border-bottom">
                                <td class="align-middle px-3 text-muted">
                                    {{ $transaction->transaction_date->format('Y-m-d') }}
                                    <div class="x-small">{{ $transaction->transaction_date->format('H:i') }}</div>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-pill font-weight-normal px-2
                                        {{ $transaction->type == 'transfer' ? 'bg-primary-soft text-primary' : 
                                           ($transaction->type == 'deposit' ? 'bg-success-soft text-success' : 
                                           ($transaction->type == 'withdrawal' ? 'bg-danger-soft text-danger' : 'bg-warning-soft text-warning')) }}" style="font-size: 0.65rem; border: 1px solid currentColor;">
                                        {{ strtoupper($transaction->type) }}
                                    </span>
                                </td>
                                <td class="align-middle">
                                    @if($transaction->from_account_id == $account->id)
                                        <div class="font-weight-bold text-danger">Payment TO</div>
                                        <div class="small">{{ $transaction->toAccount->user->name }}</div>
                                    @else
                                        <div class="font-weight-bold text-success">Incoming FROM</div>
                                        <div class="small">{{ $transaction->fromAccount->user->name }}</div>
                                    @endif
                                    @if($transaction->notes)
                                        <div class="text-muted italic x-small mt-1">{{ Str::limit($transaction->notes, 50) }}</div>
                                    @endif
                                </td>
                                <td class="text-right align-middle text-gray-800">
                                    {{ $transaction->currency->formatAmount($transaction->amount) }}
                                </td>
                                <td class="text-right align-middle text-muted small">
                                    @if($transaction->from_account_id == $account->id && $transaction->company_commission > 0)
                                        <span class="text-danger">+{{ $transaction->currency->formatAmount($transaction->company_commission) }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right align-middle font-weight-bold px-3 {{ $transaction->from_account_id == $account->id ? 'text-danger' : 'text-primary' }}">
                                    @php
                                        $mainWallet = \App\Models\Account::getOrCreateMainWallet();
                                        $isSender = $transaction->from_account_id == $account->id && $transaction->to_account_id != $account->id;
                                        $isReceiver = $transaction->to_account_id == $account->id && $transaction->from_account_id != $account->id;
                                        
                                        if ($isSender) {
                                            $deduction = ($account->id === $mainWallet->id) ? $transaction->amount : ($transaction->amount + $transaction->company_commission);
                                            $imp = -$deduction;
                                        } elseif ($isReceiver) {
                                            $imp = $transaction->amount;
                                        } else {
                                            $imp = ($transaction->type === 'withdrawal') ? -$transaction->amount : $transaction->amount;
                                        }
                                    @endphp
                                    {{ $imp > 0 ? '+' : '' }}{{ $transaction->currency->formatAmount($imp) }}
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-5 text-muted">No matching transactions found.</td></tr>
                            @endforelse
                        </tbody>
                        @if($account->filtered_transactions->count() > 0)
                        <tfoot class="bg-light font-weight-bold" style="font-size: 0.85rem;">
                            @foreach ($account->currency_summaries as $summary)
                            @if($summary['transaction_count'] > 0)
                            <tr class="border-top-2 border-dark">
                                <td colspan="3" class="text-right py-3 px-4">TOTALS ({{ $summary['currency']->code }}) :</td>
                                <td class="text-right py-3 text-gray-900 border-left">
                                    {{ $summary['currency']->formatAmount($summary['total_in'] + ($summary['total_out'] - $summary['total_commissions'])) }}
                                </td>
                                <td class="text-right py-3 text-danger">
                                    {{ $summary['currency']->formatAmount($summary['total_commissions']) }}
                                </td>
                                <td class="text-right py-3 px-3 border-right {{ $summary['period_net_impact'] < 0 ? 'text-danger' : 'text-primary' }}">
                                    {{ $summary['period_net_impact'] > 0 ? '+' : '' }}{{ $summary['currency']->formatAmount($summary['period_net_impact']) }}
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card shadow py-5 text-center">
        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
        <h4>Great News!</h4>
        <p class="text-muted">No accounts currently have negative balances.</p>
    </div>
    @endforelse
</div>

<style>
    .bg-error-light { background-color: rgba(231, 74, 59, 0.05); }
    .bg-danger-soft { background-color: rgba(231, 74, 59, 0.08); }
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.08); }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.08); }
    .bg-warning-soft { background-color: rgba(246, 194, 62, 0.08); }
    .bg-info-soft { background-color: rgba(54, 185, 204, 0.08); }
    
    .x-small { font-size: 0.7rem; }
    .italic { font-style: italic; }
    
    .table th { border-top: none; }
    .card-header.bg-gradient-danger {
        border-bottom: none;
    }
    
    @media print {
        .no-print { display: none !important; }
        .card { border: 1px solid #ddd !important; margin-bottom: 2rem !important; page-break-inside: avoid; }
        .card-header { background: #f8f9fc !important; color: #333 !important; }
        .card-header h4, .card-header .text-white-50 { color: #333 !important; }
        body { font-size: 10pt; }
    }
</style>
@endsection
