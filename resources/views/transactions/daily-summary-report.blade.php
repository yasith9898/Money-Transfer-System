@extends('layouts.app')

@section('title', 'Daily Summary Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice mr-2 text-info"></i>Daily Summary Report
        </h1>
        <div>
            <a href="{{ route('transactions.daily-summary.pdf', ['date' => $date]) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print mr-2"></i>Print Summary
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('transactions.daily-summary') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="date" class="mr-2 font-weight-bold">Select Date:</label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" max="{{ date('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-search mr-2"></i>View Summary
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Transactions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTransactions }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @foreach($currencyBreakdown as $breakdown)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Commission ({{ $breakdown['currency']->code }})</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($breakdown['company_commission'] + $breakdown['turkey_commission'], 2) }}</div>
                            <div class="text-xs text-muted mt-1">Company: {{ number_format($breakdown['company_commission'], 2) }} | Turkey: {{ number_format($breakdown['turkey_commission'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Currency Breakdown -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Currency Breakdown</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Currency</th>
                            <th>Count</th>
                            <th>History Amount</th>
                            <th>Company Commission</th>
                            <th>Company Comm. (%)</th>
                            <th>Turkey Commission</th>
                            <th>Turkey Comm. (%)</th>
                            <th>Total 1 (Amt + Comp)</th>
                            <th>Total 2 (Amt + Turk)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($currencyBreakdown as $breakdown)
                            <tr>
                                <td><strong>{{ $breakdown['currency']->code }}</strong> ({{ $breakdown['currency']->name }})</td>
                                <td>{{ $breakdown['count'] }}</td>
                                <td class="font-weight-bold text-primary">
                                    {{ $breakdown['currency']->formatAmount($breakdown['history_amount']) }}
                                </td>
                                <td class="text-info">{{ $breakdown['currency']->formatAmount($breakdown['company_commission']) }}</td>
                                <td class="text-secondary small">
                                    ({{ $breakdown['history_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['history_amount'])) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-success">{{ $breakdown['currency']->formatAmount($breakdown['turkey_commission']) }}</td>
                                <td class="text-secondary small">
                                    ({{ $breakdown['history_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['history_amount'])) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="font-weight-bold">
                                    {{ $breakdown['currency']->formatAmount($breakdown['history_amount'] + $breakdown['company_commission']) }}
                                </td>
                                <td class="font-weight-bold">
                                    {{ $breakdown['currency']->formatAmount($breakdown['history_amount'] + $breakdown['turkey_commission']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No data available for this date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Transfers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-exchange-alt mr-2"></i>Daily Transfers (Transfers, Deposits, Withdrawals)</h6>
            <span class="badge badge-light text-primary">{{ $historyTransactions->count() }} transactions</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Time</th>
                            <th>Transaction From</th>
                            <th>To</th>
                            <th>Currency</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Company Commission</th>
                            <th class="text-right">Company Comm. (%)</th>
                            <th class="text-right">Turkey Commission</th>
                            <th class="text-right">Turkey Comm. (%)</th>
                            <th class="text-right">First Total</th>
                            <th class="text-right">Total 2 (Amt + Turk)</th>
                            <th class="text-right text-success">Exchange Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($historyTransactions as $index => $t)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $t->transaction_date->format('H:i') }}</td>
                                <td>
                                    <strong>{{ $t->fromAccount->user->name ?? 'System' }}</strong><br>
                                    @if(!empty($t->fromAccount->user->company_name))
                                        <span class="text-info small">{{ $t->fromAccount->user->company_name }}</span><br>
                                    @endif
                                    <span class="text-muted small">{{ $t->fromAccount->account_number }}</span>
                                    <br>
                                    @if($t->type === 'transfer')
                                        <span class="badge badge-primary" style="background-color: #4e73df; font-size: 75%;">Transfer</span>
                                    @elseif($t->type === 'deposit')
                                        <span class="badge badge-success" style="background-color: #1cc88a; font-size: 75%;">Deposit</span>
                                    @elseif($t->type === 'withdrawal')
                                        <span class="badge badge-danger" style="background-color: #e74a3b; font-size: 75%;">Withdrawal</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $t->toAccount->user->name ?? 'System' }}</strong><br>
                                    @if(!empty($t->toAccount->user->company_name))
                                        <span class="text-info small">{{ $t->toAccount->user->company_name }}</span><br>
                                    @endif
                                    <span class="text-muted small">{{ $t->toAccount->account_number }}</span>
                                </td>
                                <td><strong>{{ $t->currency->code }}</strong></td>
                                <td class="text-right font-weight-bold text-primary">{{ number_format($t->amount, 2) }}</td>
                                <td class="text-right text-info">{{ number_format($t->company_commission, 2) }}</td>
                                <td class="text-right text-secondary small">
                                    ({{ $t->amount != 0 ? number_format((abs($t->company_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-right text-success">{{ number_format($t->turkey_commission, 2) }}</td>
                                <td class="text-right text-secondary small">
                                    ({{ $t->amount != 0 ? number_format((abs($t->turkey_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-right font-weight-bold">{{ number_format($t->amount + $t->company_commission, 2) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($t->amount + $t->turkey_commission, 2) }}</td>
                                <td class="text-right font-weight-bold">
                                    @if($t->exchange_rate)
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;" title="Rate">
                                            <i class="fas fa-random text-primary mr-1"></i>Rate: {{ $t->exchange_rate }}<br>
                                            <i class="fas {{ $t->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} text-secondary mr-1"></i>{{ ucfirst($t->exchange_action) }}
                                        </div>
                                        <span class="text-success">${{ number_format($t->exchange_result, 2) }}</span>
                                    @else
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;">Auto-converted</div>
                                        <span class="text-success">${{ number_format($t->currency->convertAmount($t->amount + $t->company_commission, 'USD'), 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center py-4 text-muted italic">No transfers were recorded on this day.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($historyTransactions->count() > 0)
                    <tfoot class="bg-light font-weight-bold">
                        @foreach ($currencyBreakdown as $breakdown)
                            @if($breakdown['history_amount'] != 0 || $breakdown['company_commission'] != 0 || $breakdown['turkey_commission'] != 0)
                            <tr>
                                <td colspan="5" class="text-right">Totals ({{ $breakdown['currency']->code }}):</td>
                                <td class="text-right text-primary">{{ number_format($breakdown['history_amount'], 2) }}</td>
                                <td class="text-right text-info">{{ number_format($breakdown['company_commission'], 2) }}</td>
                                <td class="text-right text-secondary small">
                                    ({{ $breakdown['history_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['history_amount'])) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-right text-success">{{ number_format($breakdown['turkey_commission'], 2) }}</td>
                                <td class="text-right text-secondary small">
                                    ({{ $breakdown['history_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['history_amount'])) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-right">{{ number_format($breakdown['history_amount'] + $breakdown['company_commission'], 2) }}</td>
                                <td class="text-right">{{ number_format($breakdown['history_amount'] + $breakdown['turkey_commission'], 2) }}</td>
                                <td class="text-right text-success">
                                    ${{ number_format($breakdown['currency']->convertAmount($breakdown['history_amount'], 'USD'), 2) }}
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

    <!-- Adjustments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning text-dark d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Daily Balance Adjustments</h6>
            <span class="badge badge-dark">{{ $adjustmentTransactions->count() }} adjustments</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Time</th>
                            <th>Transaction From</th>
                            <th>To</th>
                            <th>Currency</th>
                            <th class="text-right">Amount</th>
                            <th>Notes</th>
                            <th class="text-right text-success">Exchange Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($adjustmentTransactions as $index => $t)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $t->transaction_date->format('H:i') }}</td>
                                <td>
                                    <strong>{{ $t->fromAccount->user->name ?? 'System' }}</strong><br>
                                    @if(!empty($t->fromAccount->user->company_name))
                                        <span class="text-info small">{{ $t->fromAccount->user->company_name }}</span><br>
                                    @endif
                                    <span class="text-muted small">{{ $t->fromAccount->account_number }}</span>
                                </td>
                                <td>
                                    <strong>{{ $t->toAccount->user->name ?? 'System' }}</strong><br>
                                    @if(!empty($t->toAccount->user->company_name))
                                        <span class="text-info small">{{ $t->toAccount->user->company_name }}</span><br>
                                    @endif
                                    <span class="text-muted small">{{ $t->toAccount->account_number }}</span>
                                </td>
                                <td><strong>{{ $t->currency->code }}</strong></td>
                                <td class="text-right font-weight-bold text-warning">{{ number_format($t->amount, 2) }}</td>
                                <td><small>{{ $t->notes }}</small></td>
                                <td class="text-right font-weight-bold">
                                    @if($t->exchange_rate)
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;" title="Rate">
                                            <i class="fas fa-random text-primary mr-1"></i>Rate: {{ $t->exchange_rate }}<br>
                                            <i class="fas {{ $t->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} text-secondary mr-1"></i>{{ ucfirst($t->exchange_action) }}
                                        </div>
                                        <span class="text-success">${{ number_format($t->exchange_result, 2) }}</span>
                                    @else
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;">Auto-converted</div>
                                        <span class="text-success">${{ number_format($t->currency->convertAmount($t->amount, 'USD'), 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted italic">No balance adjustments were recorded on this day.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($adjustmentTransactions->count() > 0)
                    <tfoot class="bg-light font-weight-bold">
                        @foreach ($currencyBreakdown as $breakdown)
                            @php
                                $adjAmount = $adjustmentTransactions->where('currency_id', $breakdown['currency']->id)->sum('amount');
                            @endphp
                            @if($adjAmount != 0)
                            <tr>
                                <td colspan="5" class="text-right">Totals ({{ $breakdown['currency']->code }}):</td>
                                <td class="text-right text-warning">{{ number_format($adjAmount, 2) }}</td>
                                <td></td>
                                <td class="text-right text-success font-weight-bold">
                                    ${{ number_format($breakdown['currency']->convertAmount($adjAmount, 'USD'), 2) }}
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
@endsection

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .container-fluid { padding: 0 !important; }
        body { font-size: 10pt; }
        .table td, .table th { padding: 0.3rem !important; }
    }
</style>
@endpush
