@extends('layouts.app')

@section('title', 'Account Balances Report')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-file-invoice-dollar mr-2 text-primary"></i>Account Balances Report
            </h1>
            <p class="text-muted small mb-0">Analysis of balances and transaction impacts on a specific date</p>
        </div>
        <div>
            <a href="{{ route('transactions.account-balances-report.pdf', request()->all()) }}" class="btn btn-danger shadow-sm mr-2">
                <i class="fas fa-file-pdf mr-2"></i>Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-primary shadow-sm">
                <i class="fas fa-print mr-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4 no-print border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('transactions.account-balances-report') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label for="date" class="font-weight-bold small text-uppercase text-muted">Select Target Date</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                            </div>
                            <input type="date" name="date" id="date" class="form-control border-left-0" value="{{ $date }}">
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <label for="filter_type" class="font-weight-bold small text-uppercase text-muted">Filter by Balance Type</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-light border-right-0"><i class="fas fa-filter text-info"></i></span>
                            </div>
                            <select name="filter_type" id="filter_type" class="form-control border-left-0">
                                <option value="all" {{ $filter_type == 'all' ? 'selected' : '' }}>All Balances (Excl. Zero)</option>
                                <option value="positive" {{ $filter_type == 'positive' ? 'selected' : '' }}>Just (+) Positive Balances</option>
                                <option value="negative" {{ $filter_type == 'negative' ? 'selected' : '' }}>Just (-) Negative Balances</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button type="submit" class="btn btn-dark w-100 shadow-sm" style="height: calc(1.5em + .75rem + 2px);">
                            <i class="fas fa-sync-alt mr-2"></i>Generate Statement
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        @foreach($currencySummary as $summary)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; background: linear-gradient(135deg, #ffffff 0%, #f9f9ff 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem;">{{ $summary['currency']->code }}</span>
                            <small class="text-muted font-weight-bold text-uppercase">{{ $summary['count'] }} Accounts</small>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted d-block text-uppercase small">Total In (+)</small>
                                <span class="text-success font-weight-bold h6">+{{ number_format($summary['total_in'], 2) }}</span>
                            </div>
                            <div class="col-6 text-right">
                                <small class="text-muted d-block text-uppercase small">Total Out (-)</small>
                                <span class="text-danger font-weight-bold h6">-{{ number_format($summary['total_out'], 2) }}</span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Positive Balances:</span>
                            <span class="small font-weight-bold text-primary">{{ number_format($summary['positive_balance'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted">Negative Balances:</span>
                            <span class="small font-weight-bold text-danger">{{ number_format($summary['negative_balance'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 mt-2 border-top">
                            <span class="font-weight-bold text-dark">Net Balance:</span>
                            <span class="font-weight-bold {{ $summary['net_balance'] >=0 ? 'text-primary' : 'text-danger' }}">
                                {{ number_format($summary['net_balance'], 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Balances Snapshot: <span class="text-dark">{{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</span>
            </h6>
            <span class="badge badge-light border text-muted px-3">{{ $reportData->count() }} records found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small font-weight-bold text-muted">
                            <th class="border-0 px-4 py-3">Account Information</th>
                            <th class="border-0 py-3 text-right text-success">Total In (+)</th>
                            <th class="border-0 py-3 text-right text-danger">Total Out (-)</th>
                            <th class="border-0 py-3 text-right">Day Net</th>
                            <th class="border-0 py-3 text-right bg-white" style="width: 250px;">Balance (on {{ \Carbon\Carbon::parse($date)->format('M d') }})</th>
                            <th class="border-0 px-4 py-3 text-center no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reportData as $row)
                            <tr class="align-middle">
                                <td class="px-4 py-3">
                                    <div class="font-weight-bold text-dark">{{ $row['account_name'] }}</div>
                                    <div class="small text-muted">{{ $row['account_number'] }} | {{ $row['company_name'] }}</div>
                                </td>
                                <td class="text-right py-3 text-success font-weight-bold">
                                    @if($row['total_in'] > 0)
                                        +{{ number_format($row['total_in'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right py-3 text-danger font-weight-bold">
                                    @if($row['total_out'] > 0)
                                        -{{ number_format($row['total_out'], 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-right py-3 small {{ $row['net_change'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                    {{ $row['net_change'] > 0 ? '+' : '' }}{{ number_format($row['net_change'], 2) }}
                                </td>
                                <td class="text-right py-3 bg-light-soft px-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge badge-pill badge-primary-soft text-primary small px-2 mr-2">{{ $row['currency']->code }}</span>
                                        <span class="h5 mb-0 font-weight-bold {{ $row['balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                            {{ $row['balance'] < 0 ? '' : '' }}{{ number_format($row['balance'], 2) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center px-4 py-3 no-print">
                                    <a href="{{ route('transactions.account-statement', $row['account_id']) }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                        <i class="fas fa-file-invoice"></i> Statement
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                        No records found for the selected filters.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-light-soft { background-color: rgba(248, 249, 252, 0.82); }
    .badge-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
        .container-fluid { padding: 0 !important; }
        body { font-size: 10pt; background: white !important; }
        .table { border: 1px solid #eee !important; }
        .table td, .table th { padding: 8px !important; }
        .h5 { font-size: 11pt !important; }
    }
</style>
@endpush
