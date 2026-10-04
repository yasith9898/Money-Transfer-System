@extends('layouts.app')

@section('title', 'All Transactions Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history mr-2 text-primary"></i>All Transactions Report
        </h1>
        <div class="btn-group">
            <a href="{{ route('transactions.all-transactions-report.pdf', request()->all()) }}" class="btn btn-danger shadow-sm">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary shadow-sm">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 no-print border-0" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-filter mr-2"></i>Advanced Filters</h6>
        </div>
        <div class="card-body pt-0">
            <form action="{{ route('transactions.all-transactions-report') }}" method="GET">
                <div class="row">
                    <div class="col-md-2 mb-3">
                        <label for="start_date" class="font-weight-bold small text-muted">START DATE</label>
                        <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="end_date" class="font-weight-bold small text-muted">END DATE</label>
                        <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="account_id" class="font-weight-bold small text-muted">ACCOUNT</label>
                        <select name="account_id" id="account_id" class="form-control form-control-sm select2">
                            <option value="all" {{ $accountId == 'all' ? 'selected' : '' }}>All Accounts</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ $accountId == $account->id ? 'selected' : '' }}>
                                    {{ $account->user->name }} ({{ $account->account_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="currency_id" class="font-weight-bold small text-muted">CURRENCY</label>
                        <select name="currency_id" id="currency_id" class="form-control form-control-sm">
                            <option value="all" {{ $currencyId == 'all' ? 'selected' : '' }}>All Currencies</option>
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ $currencyId == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="type" class="font-weight-bold small text-muted">TYPE</label>
                        <select name="type" id="type" class="form-control form-control-sm">
                            <option value="all" {{ $type == 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="transfer" {{ $type == 'transfer' ? 'selected' : '' }}>Transfers Only</option>
                            <option value="adjustment" {{ $type == 'adjustment' ? 'selected' : '' }}>Adjustments Only</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm btn-block shadow-sm">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats Layout -->
    <div class="row mb-5">
        <div class="col-12 mb-3">
             <h6 class="font-weight-bold text-gray-800 text-uppercase small tracking-widest pl-2">
                <i class="fas fa-chart-line mr-2 text-primary"></i>Executive Summary Breakdowns
            </h6>
        </div>
        @foreach($currencyBreakdown as $breakdown)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card shadow-sm border-0 h-100 overflow-hidden" style="border-radius: 12px; background: #fff;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary-soft text-primary px-3 py-2" style="font-size: 0.8rem;">{{ $breakdown['currency']->code }}</span>
                            <div class="text-right">
                                <small class="text-muted d-block text-uppercase x-small font-weight-bold">Volume</small>
                                <span class="font-weight-bold text-dark">{{ $breakdown['count'] }} Txns</span>
                            </div>
                        </div>
                        <h4 class="font-weight-bold text-gray-900 mb-1">
                            {{ $breakdown['currency']->formatAmount(abs($breakdown['total_amount'])) }}
                        </h4>
                        <div class="small text-muted mb-3">Total Processed (Principal)</div>
                        
                        <div class="border-top pt-3 mt-3">
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted font-weight-bold">COMMISSION:</span>
                                <span class="text-info font-weight-bold">{{ $breakdown['currency']->formatAmount($breakdown['company_commission']) }}</span>
                            </div>
                            <div class="d-flex justify-content-between x-small bg-light p-2 rounded">
                                <span class="text-muted">Avg Comm Rate:</span>
                                <span class="text-dark font-weight-bold">
                                    {{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-primary py-1 opacity-75"></div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Transaction Details -->
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list mr-2"></i>Transaction Ledger
            </h6>
            <span class="badge badge-pill badge-light px-3">{{ $transactions->count() }} records found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-gray-100 x-small text-uppercase font-weight-bold text-muted">
                            <th class="ps-4 py-3">Timestamp</th>
                            <th class="py-3">Transaction From</th>
                            <th class="py-3">To</th>
                            <th class="py-3">Currency</th>
                            <th class="text-right py-3">Amount</th>
                            <th class="text-right py-3">Company Comm</th>
                            <th class="text-right py-3">Comp (%)</th>
                            <th class="text-right py-3">Turkey Comm</th>
                            <th class="text-right py-3">Turk (%)</th>
                            <th class="text-right py-3">First Total</th>
                            <th class="text-right py-3">Second Total</th>
                            <th class="py-3 text-right text-success">Exchange Result</th>
                            <th class="py-3">Type</th>
                            <th class="pe-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $t)
                        <tr class="border-bottom">
                            <td class="ps-4">
                                <div class="font-weight-bold text-dark mb-0">{{ $t->transaction_date->format('M j, Y') }}</div>
                                <div class="x-small text-muted">{{ $t->transaction_date->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="small font-weight-bold text-dark">{{ $t->fromAccount->user->name ?? 'System' }}</div>
                                @if(!empty($t->fromAccount->user->company_name))
                                    <div class="x-small text-info">{{ $t->fromAccount->user->company_name }}</div>
                                @endif
                                <div class="x-small text-muted">{{ $t->fromAccount->account_number }}</div>
                            </td>
                            <td>
                                <div class="small font-weight-bold text-dark">{{ $t->toAccount->user->name ?? 'System' }}</div>
                                @if(!empty($t->toAccount->user->company_name))
                                    <div class="x-small text-info">{{ $t->toAccount->user->company_name }}</div>
                                @endif
                                <div class="x-small text-muted">{{ $t->toAccount->account_number }}</div>
                            </td>
                            <td>
                                <div class="small font-weight-bold text-dark">{{ $t->currency->code }}</div>
                            </td>
                            <td class="text-right">
                                @php
                                    $isNeg = $accountId !== 'all' && $t->from_account_id == $accountId;
                                @endphp
                                <div class="font-weight-bold {{ $isNeg ? 'text-danger' : 'text-primary' }}">
                                    {{ $t->currency->formatAmount($isNeg ? -$t->amount : $t->amount) }}
                                </div>
                            </td>
                            <td class="text-right text-info font-weight-bold">
                                {{ $t->company_commission > 0 ? $t->currency->formatAmount($t->company_commission) : '-' }}
                            </td>
                            <td class="text-right text-secondary small">
                                ({{ $t->amount != 0 ? number_format((abs($t->company_commission) / abs($t->amount)) * 100, 2) : '0' }}%)
                            </td>
                            <td class="text-right text-success font-weight-bold">
                                {{ $t->turkey_commission > 0 ? $t->currency->formatAmount($t->turkey_commission) : '-' }}
                            </td>
                            <td class="text-right text-secondary small">
                                ({{ $t->amount != 0 ? number_format((abs($t->turkey_commission) / abs($t->amount)) * 100, 2) : '0' }}%)
                            </td>
                            <td class="text-right font-weight-bolder py-3 text-dark">
                                @php
                                    $total1 = $t->amount + ($t->fromAccount->wallet_type !== 'main_company' ? $t->company_commission : 0);
                                    if ($isNeg) $total1 = -$total1;
                                @endphp
                                <span class="{{ $total1 < 0 ? 'text-danger' : 'text-dark' }}">
                                    {{ $t->currency->formatAmount($total1) }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bolder py-3 text-success">
                                @php
                                    $total2 = $t->amount + ($t->fromAccount->wallet_type !== 'main_company' ? $t->turkey_commission : 0);
                                    if ($isNeg) $total2 = -$total2;
                                @endphp
                                <span class="{{ $total2 < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $t->currency->formatAmount($total2) }}
                                </span>
                            </td>
                            <td class="text-right py-3 font-weight-bold">
                                @if($t->exchange_rate)
                                    <div class="x-small text-muted mb-1" style="font-weight: normal;" title="Rate">
                                        <i class="fas fa-random text-primary mr-1"></i>Rate: {{ $t->exchange_rate }}<br>
                                        <i class="fas {{ $t->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} text-secondary mr-1"></i>{{ ucfirst($t->exchange_action) }}
                                    </div>
                                    <span class="text-success">${{ $isNeg ? '-' : '' }}{{ number_format($t->exchange_result, 2) }}</span>
                                @else
                                    <div class="x-small text-muted mb-1" style="font-weight: normal;">Auto-converted</div>
                                    @php
                                        $usdAmount = $t->currency->convertAmount($t->amount + ($t->fromAccount->wallet_type !== 'main_company' ? $t->company_commission : 0), 'USD');
                                    @endphp
                                    <span class="text-success">${{ $isNeg ? '-' : '' }}{{ number_format($usdAmount, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($t->type === 'transfer')
                                    <span class="badge badge-primary" style="background-color: #4e73df;">Transfer</span>
                                @elseif($t->type === 'deposit')
                                    <span class="badge badge-success" style="background-color: #1cc88a;">Deposit</span>
                                @elseif($t->type === 'withdrawal')
                                    <span class="badge badge-danger" style="background-color: #e74a3b;">Withdrawal</span>
                                @else
                                    <span class="badge badge-warning" style="background-color: #f6c23e; color: #fff;">Adjustment</span>
                                @endif
                            </td>
                            <td class="pe-4 text-center">
                                <a href="{{ route('transactions.show', $t->id) }}" class="btn btn-outline-primary btn-sm rounded-circle py-1" style="width: 32px; height: 32px;">
                                    <i class="fas fa-eye small"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="text-center py-5">
                                <div class="text-muted"><i class="fas fa-info-circle mb-2 fa-2x"></i><br>No matching records found.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                    <tfoot class="bg-gray-100 font-weight-bold text-dark x-small">
                        @foreach ($currencyBreakdown as $breakdown)
                        <tr>
                            <td colspan="4" class="text-right py-3 pe-4 text-muted text-uppercase">Totals ({{ $breakdown['currency']->code }})</td>
                            <td class="text-right py-3 {{ $accountId !== 'all' && $breakdown['total_amount'] < 0 ? 'text-danger' : 'text-primary' }}">
                                {{ $breakdown['currency']->formatAmount($breakdown['total_amount']) }}
                            </td>
                            <td class="text-right py-3 text-info">
                                {{ $breakdown['currency']->formatAmount($breakdown['company_commission']) }}
                            </td>
                            <td class="text-right py-3 text-muted">
                                ({{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%)
                            </td>
                            <td class="text-right py-3 text-success">
                                {{ $breakdown['currency']->formatAmount($breakdown['turkey_commission']) }}
                            </td>
                            <td class="text-right py-3 text-muted">
                                ({{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%)
                            </td>
                            <td class="text-right py-3 bg-secondary text-white" style="font-size: 0.85rem;">
                                @php
                                    $gTotal = $breakdown['total_amount'] + ($breakdown['total_amount'] < 0 ? -$breakdown['company_commission'] : $breakdown['company_commission']);
                                @endphp
                                {{ $breakdown['currency']->formatAmount($gTotal) }}
                            </td>
                            <td class="text-right py-3 bg-secondary text-white" style="font-size: 0.85rem;">
                                @php
                                    $gTotal2 = $breakdown['total_amount'] + ($breakdown['total_amount'] < 0 ? -$breakdown['turkey_commission'] : $breakdown['turkey_commission']);
                                @endphp
                                {{ $breakdown['currency']->formatAmount($gTotal2) }}
                            </td>
                            <td class="text-right py-3 text-success">
                                ${{ number_format($breakdown['currency']->convertAmount($breakdown['total_amount'], 'USD'), 2) }}
                            </td>
                            <td colspan="2" class="bg-secondary rounded-end"></td>
                        </tr>
                        @endforeach
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-soft { background-color: rgba(28, 200, 138, 0.1); }
    .bg-danger-soft { background-color: rgba(231, 74, 59, 0.1); }
    .bg-warning-soft { background-color: rgba(246, 194, 62, 0.1); }
    .x-small { font-size: 0.72rem; }
    .bg-gray-100 { background-color: #f8f9fc; }
    
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.02);
    }
    
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.5rem + 2px) !important;
    }

    @media print {
        .no-print { display: none !important; }
        .card { border: 1px solid #eee !important; box-shadow: none !important; margin-bottom: 2rem !important; }
        .container-fluid { padding: 0 !important; }
        body { font-size: 9pt; background: #fff !important; }
        .table td, .table th { padding: 0.4rem !important; }
        .bg-light, .bg-secondary, .bg-gray-100 { background-color: #f8f9fc !important; color: #333 !important; }
    }
</style>
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .container-fluid { padding: 0 !important; }
        body { font-size: 9pt; }
        .table td, .table th { padding: 0.3rem !important; }
        .badge { border: 1px solid #ddd !important; color: black !important; background: transparent !important; }
    }
</style>
@endpush
