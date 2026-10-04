@extends('layouts.app')

@section('title', 'Daily Transaction Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-day mr-2 text-primary"></i>Daily Transaction Report
        </h1>
        <div>
            <a href="{{ route('transactions.daily-report.pdf', ['date' => $date]) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card shadow mb-4 no-print">
        <div class="card-body">
            <form action="{{ route('transactions.daily-report') }}" method="GET" class="form-inline">
                <div class="form-group mr-3">
                    <label for="date" class="mr-2 font-weight-bold">Select Date:</label>
                    <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" max="{{ date('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search mr-2"></i>Generate Report
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
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
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Company Commission</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCompanyCommission, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Turkey Commission</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalTurkeyCommission, 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe-europe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Breakdown -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-coins mr-2"></i>Currency Breakdown ({{ \Carbon\Carbon::parse($date)->format('M j, Y') }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Currency</th>
                            <th class="text-center">Count</th>
                            <th class="text-right">Total Amount</th>
                            <th class="text-right">Company Comm</th>
                            <th class="text-right">Company Comm (%)</th>
                            <th class="text-right">Turkey Comm</th>
                            <th class="text-right">Turkey Comm (%)</th>
                            <th class="text-right">First Total</th>
                            <th class="text-right">Second Total</th>

                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($currencyBreakdown as $breakdown)
                            <tr>
                                <td><strong>{{ $breakdown['currency']->code }}</strong> <span class="text-muted small">({{ $breakdown['currency']->name }})</span></td>
                                <td class="text-center">{{ $breakdown['count'] }}</td>
                                <td class="text-right font-weight-bold text-primary">{{ number_format($breakdown['total_amount'], 2) }}</td>
                                <td class="text-right text-info">{{ number_format($breakdown['company_commission'], 2) }}</td>
                                <td class="text-right text-secondary small">
                                    {{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%
                                </td>
                                <td class="text-right text-success">{{ number_format($breakdown['turkey_commission'], 2) }}</td>
                                <td class="text-right text-secondary small">
                                    {{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%
                                </td>
                                <td class="text-right font-weight-bold">{{ number_format($breakdown['total_amount'] + $breakdown['company_commission'], 2) }}</td>
                                <td class="text-right font-weight-bold text-success">{{ number_format($breakdown['total_amount'] + $breakdown['turkey_commission'], 2) }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Transactions -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Transactions - {{ \Carbon\Carbon::parse($date)->format('M j, Y') }}</h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3 no-print" id="reportTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="transfers-tab" data-bs-toggle="tab" data-bs-target="#transfers" type="button" role="tab">Transfers</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="adjustments-tab" data-bs-toggle="tab" data-bs-target="#adjustments" type="button" role="tab">Adjustments</button>
                </li>
            </ul>

            <div class="tab-content" id="reportTabsContent">
                <!-- All Transactions -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    @include('transactions.partials.report-table', ['reportTransactions' => $transactions])
                </div>

                <!-- Transfers -->
                <div class="tab-pane fade" id="transfers" role="tabpanel">
                    @include('transactions.partials.report-table', ['reportTransactions' => $transferTransactions])
                </div>

                <!-- Adjustments -->
                <div class="tab-pane fade" id="adjustments" role="tabpanel">
                    @include('transactions.partials.report-table', ['reportTransactions' => $adjustmentTransactions])
                </div>
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
    }
</style>
@endpush
