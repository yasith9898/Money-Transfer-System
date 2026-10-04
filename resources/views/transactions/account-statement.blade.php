@extends('layouts.app')

@section('title', 'Account Statement')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h3>Account Statement</h3>
            <p class="text-muted mb-1">Account: {{ $account->user->name ?? $account->account_number }} ({{ $account->account_number }})</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow border-left-info">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-calculator mr-2"></i>Account Balances Calculation Table</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th>Currency</th>
                                    <th class="text-right">Total In (+)</th>
                                    <th class="text-right">Total Out (-)</th>
                                    <th class="text-right">Company Comm (-)</th>
                                    <th class="text-right">Company Comm (+)</th>
                                    <th class="text-right">Net Balance</th>
                                    <th class="text-right">Current Ledger Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($currencyAggregates as $agg)
                                    <tr>
                                        <td><strong>{{ $agg['currency']->code }}</strong> - {{ $agg['currency']->name }}</td>
                                        <td class="text-right text-success font-weight-bold">
                                            {{ $agg['currency']->formatAmount($agg['total_in']) }}
                                        </td>
                                        <td class="text-right text-danger font-weight-bold">
                                            {{ $agg['currency']->formatAmount($agg['total_out']) }}
                                        </td>
                                        <td class="text-right text-info font-weight-bold">
                                            {{ $agg['currency']->formatAmount($agg['company_commission_minus']) }}
                                        </td>
                                        <td class="text-right text-success font-weight-bold">
                                            {{ $agg['currency']->formatAmount($agg['company_commission_plus']) }}
                                        </td>
                                        <td class="text-right font-weight-bold {{ $agg['net'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ $agg['currency']->formatAmount($agg['net']) }}
                                        </td>
                                        <td class="text-right bg-light font-weight-bold h5 mb-0">
                                            {{ $agg['currency']->formatAmount($account->getBalance($agg['currency']->id)) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-3 text-muted">No transaction data available for this account.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction History Table -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-history mr-2"></i>Transaction History
                    </h6>
                    <div class="small">
                        Showing {{ $historyTransactions->firstItem() ?? 0 }} to {{ $historyTransactions->lastItem() ?? 0 }} of {{ $historyTransactions->total() }} records
                    </div>
                </div>
                <div class="card-body">
                    @if($historyTransactions->isEmpty())
                        <div class="alert alert-info mb-0 text-center">
                            <i class="fas fa-info-circle mr-2"></i>No transfer history found for this account.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Currency</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-right">Company Commission</th>
                                    <th class="text-right">Company Comm. (%)</th>
                                    <th class="text-right">Turkey Commission</th>
                                    <th class="text-right">Turkey Comm. (%)</th>
                                    <th class="text-right">Total 1 (Amt + Comp)</th>

                                    <th>From</th>
                                    <th>To</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($historyTransactions as $t)
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                {{ $t->transaction_date?->format('Y-m-d') ?? $t->created_at->format('Y-m-d') }}<br>
                                                {{ $t->transaction_date?->format('H:i') ?? $t->created_at->format('H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($t->type === 'transfer')
                                                <span class="badge badge-primary" style="background-color: #4e73df;">Transfer</span>
                                            @elseif($t->type === 'deposit')
                                                <span class="badge badge-success" style="background-color: #1cc88a;">Deposit</span>
                                            @elseif($t->type === 'withdrawal')
                                                <span class="badge badge-danger" style="background-color: #e74a3b;">Withdrawal</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($t->type) }}</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $t->currency->code ?? '-' }}</strong></td>
                                        <td class="text-right font-weight-bold text-primary">
                                            {{ $t->currency->formatAmount($t->amount) }}
                                        </td>
                                        <td class="text-right text-info">
                                            {{ $t->currency->formatAmount($t->company_commission) }}
                                        </td>
                                        <td class="text-right text-secondary small">
                                            ({{ $t->amount != 0 ? number_format((abs($t->company_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%)
                                        </td>
                                        <td class="text-right text-success">
                                            {{ $t->currency->formatAmount($t->turkey_commission) }}
                                        </td>
                                        <td class="text-right text-secondary small">
                                            ({{ $t->amount != 0 ? number_format((abs($t->turkey_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%)
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            @php
                                                $total1 = $t->amount;
                                                // Only add company commission to total if the sender is NOT the main company wallet
                                                if ($t->fromAccount->wallet_type !== 'main_company') {
                                                    $total1 += $t->company_commission;
                                                }
                                            @endphp
                                            {{ $t->currency->formatAmount($total1) }}
                                        </td>

                                        <td>
                                            <small>{{ $t->fromAccount->user->name ?? $t->fromAccount->account_number }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $t->toAccount->user->name ?? $t->toAccount->account_number }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                     @foreach ($currencyAggregates as $agg)
                                     <tr style="border-top: 2px solid #dee2e6;">
                                         <td colspan="3" class="text-right">History Totals ({{ $agg['currency']->code }}):</td>
                                         <td class="text-right text-primary">{{ number_format($agg['hist_total_amount'], 2) }}</td>
                                         <td class="text-right text-info">{{ number_format($agg['hist_total_company_comm'], 2) }}</td>
                                         <td class="text-right text-secondary small">
                                             ({{ $agg['hist_total_amount'] != 0 ? number_format((abs($agg['hist_total_company_comm']) / abs($agg['hist_total_amount'])) * 100, 2) : '0.00' }}%)
                                         </td>
                                         <td class="text-right text-success">{{ number_format($agg['hist_total_turkey_comm'], 2) }}</td>
                                         <td class="text-right text-secondary small">
                                             ({{ $agg['hist_total_amount'] != 0 ? number_format((abs($agg['hist_total_turkey_comm']) / abs($agg['hist_total_amount'])) * 100, 2) : '0.00' }}%)
                                         </td>
                                         <td class="text-right">
                                             @php
                                                 // Same logic as individual rows for Total 1
                                                 // This is a sum of (amount + company_commission if NOT main company)
                                                 // Since we already have the aggregates in $agg, we use those if possible or just show the simple sum for now as per previous logic.
                                                 // Previous logic did a loop which is expensive but accurate.
                                                 // Let's just show amount + company_comm for now if account is NOT main company.
                                                 $total1 = $agg['hist_total_amount'];
                                                 if ($account->wallet_type !== 'main_company') {
                                                     $total1 += $agg['hist_total_company_comm'];
                                                 }
                                             @endphp
                                             {{ number_format($total1, 2) }}
                                         </td>
                                         <td colspan="2"></td>
                                     </tr>
                                     @endforeach
                                 </tfoot>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-center">
                            {{ $historyTransactions->appends(['adj_page' => $adjustmentTransactions->currentPage()])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Adjustments Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit mr-2"></i>Balance Adjustments
                    </h6>
                    <div class="small">
                        Showing {{ $adjustmentTransactions->firstItem() ?? 0 }} to {{ $adjustmentTransactions->lastItem() ?? 0 }} of {{ $adjustmentTransactions->total() }} records
                    </div>
                </div>
                <div class="card-body">
                    @if($adjustmentTransactions->isEmpty())
                        <div class="alert alert-light border mb-0 text-center">
                            <i class="fas fa-info-circle mr-2"></i>No balance adjustments recorded for this account.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Currency</th>
                                    <th class="text-right">Adjustment Amount</th>
                                    <th>Notes</th>
                                    <th>From</th>
                                    <th>To</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($adjustmentTransactions as $t)
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                {{ $t->transaction_date?->format('Y-m-d') ?? $t->created_at->format('Y-m-d') }}<br>
                                                {{ $t->transaction_date?->format('H:i') ?? $t->created_at->format('H:i') }}
                                            </small>
                                        </td>
                                        <td><strong>{{ $t->currency->code ?? '-' }}</strong></td>
                                        <td class="text-right font-weight-bold {{ $t->to_account_id == $account->id ? 'text-success' : 'text-danger' }}">
                                            {{ $t->to_account_id == $account->id ? '+' : '-' }}
                                            {{ $t->currency->formatAmount($t->amount) }}
                                        </td>
                                        <td>
                                            <small>{{ $t->notes }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $t->fromAccount->user->name ?? $t->fromAccount->account_number }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $t->toAccount->user->name ?? $t->toAccount->account_number }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot class="bg-light font-weight-bold">
                                    @foreach ($currencyAggregates as $agg)
                                    <tr>
                                        <td colspan="2" class="text-right">Adjustments Total ({{ $agg['currency']->code }}):</td>
                                        <td class="text-right {{ $agg['adj_total_amount'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $agg['adj_total_amount'] >= 0 ? '+' : '' }}{{ number_format($agg['adj_total_amount'], 2) }}
                                        </td>
                                        <td colspan="3"></td>
                                    </tr>
                                    @endforeach
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-center">
                            {{ $adjustmentTransactions->appends(['history_page' => $historyTransactions->currentPage()])->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
