@extends('layouts.app')

@section('title', 'Company Transaction Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building mr-2 text-primary"></i>Company Transaction Report
        </h1>
        <div>
            <a href="{{ route('transactions.company-report.pdf', ['start_date' => $startDate, 'end_date' => $endDate, 'company_name' => $companyName]) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('transactions.company-report') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="start_date" class="mr-2 font-weight-bold">From:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
                </div>
                <div class="form-group mr-3">
                    <label for="end_date" class="mr-2 font-weight-bold">To:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
                </div>
                <div class="form-group mr-3">
                    <label for="company_name" class="mr-2 font-weight-bold">Company:</label>
                    <select name="company_name" id="company_name" class="form-control">
                        <option value="all" {{ $companyName == 'all' ? 'selected' : '' }}>All Companies (Consolidated)</option>
                        @foreach($companies as $name)
                            <option value="{{ $name }}" {{ $companyName == $name ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>Generate Report
                </button>
            </form>
        </div>
    </div>

    <!-- Currency Summary Cards -->
    <div class="row mb-4">
        @foreach($currencyBreakdown as $breakdown)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Summary ({{ $breakdown['currency']->code }})
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Total First: {{ number_format($breakdown['total_first'], 2) }}
                            </div>
                            <div class="text-xs text-muted mt-1">
                                Principal: {{ number_format($breakdown['total_amount'], 2) }} | 
                                Comm: {{ number_format($breakdown['total_comp_comm'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                {{ $companyName === 'all' ? 'All Companies' : $companyName }} - Transaction Details
            </h6>
            <span class="badge badge-info">{{ $transactions->count() }} Records</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="reportTable">
                    <thead class="thead-light">
                        <tr>
                            <th class="ps-3 py-3">Date</th>
                            <th class="py-3">Transaction From</th>
                            <th class="py-3">To</th>
                            <th class="py-3">Currency</th>
                            <th class="text-right py-3">Amount</th>
                            <th class="text-right py-3">Commission</th>
                            <th class="text-right py-3 bg-light font-weight-bold">First Total</th>
                            <th class="text-right py-3 text-success">Exchange Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $t)
                            @php
                                // Calculate First Total for this row
                                $comm = ($t->fromAccount->wallet_type !== 'main_company') ? $t->company_commission : 0;
                                $firstTotal = $t->amount + $comm;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="font-weight-bold text-dark">{{ $t->transaction_date->format('Y-m-d') }}</div>
                                    <div class="small text-muted">{{ $t->transaction_date->format('H:i') }}</div>
                                </td>
                                <td>
                                    <strong>{{ $t->fromAccount->user->name ?? 'System' }}</strong><br>
                                    @if(!empty($t->fromAccount->user->company_name))
                                        <span class="text-info small">{{ $t->fromAccount->user->company_name }}</span><br>
                                    @endif
                                    <span class="text-muted small"><i class="fas fa-wallet mr-1"></i>{{ $t->fromAccount->account_number ?? '-' }}</span><br>
                                    <div class="badge badge-primary mt-1">{{ ucfirst($t->type) }}</div>
                                </td>
                                <td>
                                    <strong>{{ $t->toAccount->user->name ?? 'System' }}</strong><br>
                                    @if(!empty($t->toAccount->user->company_name))
                                        <span class="text-info small">{{ $t->toAccount->user->company_name }}</span><br>
                                    @endif
                                    <span class="text-muted small"><i class="fas fa-wallet mr-1"></i>{{ $t->toAccount->account_number ?? '-' }}</span>
                                </td>
                                <td><strong>{{ $t->currency->code }}</strong></td>
                                <td class="text-right text-primary font-weight-bold">{{ number_format($t->amount, 2) }}</td>
                                <td class="text-right text-danger">
                                    {{ $comm > 0 ? '+' . number_format($comm, 2) : '-' }}
                                </td>
                                <td class="text-right font-weight-bold bg-light">
                                    {{ number_format($firstTotal, 2) }}
                                </td>
                                <td class="text-right font-weight-bold">
                                    @if($t->exchange_rate)
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;" title="Rate">
                                            <i class="fas fa-random text-primary mr-1"></i>Rate: {{ $t->exchange_rate }}<br>
                                            <i class="fas {{ $t->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} text-secondary mr-1"></i>{{ ucfirst($t->exchange_action) }}
                                        </div>
                                        <span class="text-success">${{ number_format($t->exchange_result, 2) }}</span>
                                    @else
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;">Auto-converted</div>
                                        <span class="text-success">${{ number_format($t->currency->convertAmount($firstTotal, 'USD'), 2) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted italic">No transactions found for the selected criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                    <tfoot class="bg-light font-weight-bold">
                        @foreach ($currencyBreakdown as $breakdown)
                            <tr>
                                <td colspan="4" class="text-right py-3 text-muted text-uppercase">Totals ({{ $breakdown['currency']->code }}):</td>
                                <td class="text-right py-3 text-primary">{{ number_format($breakdown['total_amount'], 2) }}</td>
                                <td class="text-right py-3 text-danger">{{ number_format($breakdown['total_comp_comm'], 2) }}</td>
                                <td class="text-right py-3 bg-secondary text-white">{{ number_format($breakdown['total_first'], 2) }}</td>
                                <td class="text-right py-3 text-success font-weight-bold">${{ number_format($breakdown['currency']->convertAmount($breakdown['total_first'], 'USD'), 2) }}</td>
                            </tr>
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
    .bg-light { background-color: #f8f9fc !important; }
</style>
@endpush
