<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commission Report - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .report-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .stat-card {
            border-left: 4px solid #f5576c;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .commission-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary no-print">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-money-bill-transfer"></i> Money Transfer System
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('accounts.index') }}">Accounts</a>
                <a class="nav-link" href="{{ route('transactions.index') }}">Transactions</a>
                <a class="nav-link active" href="{{ route('transactions.commission-report') }}">Reports</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="report-header">
            <h1><i class="fas fa-chart-line"></i> Commission Report</h1>
            <p class="mb-0">Detailed analysis of commissions earned over time</p>
        </div>

        <!-- Date Range Filter -->
        <div class="card mb-4 no-print">
            <div class="card-body">
                <form method="GET" action="{{ route('transactions.commission-report') }}" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                        <a href="{{ route('transactions.commission-report.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger me-2">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                        <button type="button" class="btn btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small font-weight-bold">Total Transactions</h6>
                        <h3 class="mb-0 text-primary">{{ $totalTransactions }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm border-left-info">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small font-weight-bold">Total Company Commission</h6>
                        <h3 class="mb-0 text-info">{{ number_format($totalCompanyCommission, 2) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm border-left-warning">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase small font-weight-bold">Total Turkey Commission</h6>
                        <h3 class="mb-0 text-warning">{{ number_format($totalTurkeyCommission, 2) }}</h3>
                    </div>
                </div>
            </div>

        </div>

        <!-- Commission Breakdown by Currency -->
        @if($commissionsByCurrency->count() > 0)
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-primary fw-bold"><i class="fas fa-coins me-2"></i>Commission Breakdown by Currency</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="bg-light text-dark">
                            <tr>
                                <th>Currency</th>
                                <th class="text-center">Transactions</th>
                                <th class="text-right">Total Amount</th>
                                <th class="text-right">Company Commission</th>
                                <th class="text-right">Company Comm. (%)</th>
                                <th class="text-right">Turkey Commission</th>
                                <th class="text-right">Turkey Comm. (%)</th>

                                <th class="text-right">Total 1 (Amt + Comp)</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($commissionsByCurrency as $commission)
                            <tr>
                                <td>
                                    <strong>{{ $commission['currency']->code }}</strong>
                                    <small class="text-muted d-block">({{ $commission['currency']->name }})</small>
                                </td>
                                <td class="text-center">{{ $commission['count'] }}</td>
                                <td class="text-right font-weight-bold text-primary">{{ number_format($commission['total_amount'], 2) }}</td>
                                <td class="text-right text-info">
                                    {{ number_format($commission['company_commission'], 2) }}
                                </td>
                                <td class="text-right text-secondary small">
                                    {{ $commission['total_amount'] != 0 ? number_format((abs($commission['company_commission']) / abs($commission['total_amount'])) * 100, 2) : '0.00' }}%
                                </td>
                                <td class="text-right text-warning">
                                    {{ number_format($commission['turkey_commission'], 2) }}
                                </td>
                                <td class="text-right text-secondary small">
                                    {{ $commission['total_amount'] != 0 ? number_format((abs($commission['turkey_commission']) / abs($commission['total_amount'])) * 100, 2) : '0.00' }}%
                                </td>

                                <td class="text-right font-weight-bold">
                                    {{ number_format($commission['total_amount'] + $commission['company_commission'], 2) }}
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Detailed Transactions with Commissions -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-list"></i> Transaction Details</h5>
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>

                                 <th>Company Commission</th>
                                <th>Company Comm. (%)</th>
                                <th>Turkey Commission</th>
                                <th>Turkey Comm. (%)</th>

                                <th>Total Commission</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                            <tr>
                                <td><a href="{{ route('transactions.show', $transaction->id) }}">#{{ $transaction->id }}</a></td>
                                <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                                <td>
                                    <small>{{ $transaction->fromAccount->user->name }}</small><br>
                                    <small class="text-muted">{{ $transaction->fromAccount->account_number }}</small>
                                </td>
                                <td>
                                    <small>{{ $transaction->toAccount->user->name }}</small><br>
                                    <small class="text-muted">{{ $transaction->toAccount->account_number }}</small>
                                </td>
                                <td>
                                    <strong>{{ $transaction->currency->symbol }} {{ number_format($transaction->amount, 2) }}</strong>
                                </td>

                                <td class="text-info">
                                    {{ $transaction->currency->symbol }} {{ number_format($transaction->company_commission, 2) }}
                                </td>
                                <td class="text-secondary small">
                                    ({{ $transaction->amount != 0 ? number_format((abs($transaction->company_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-warning">
                                    {{ $transaction->currency->symbol }} {{ number_format($transaction->turkey_commission, 2) }}
                                </td>
                                <td class="text-secondary small">
                                    ({{ $transaction->amount != 0 ? number_format((abs($transaction->turkey_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%)
                                </td>

                                <td class="text-dark">
                                    <strong>{{ $transaction->currency->symbol }} {{ number_format($transaction->getTotalCommission(), 2) }}</strong>
                                </td>
                                <td><span class="badge bg-secondary">{{ ucfirst($transaction->type) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold bg-light">
                                <td colspan="4" class="text-end">Total Balance:</td>
                                <td class="text-primary">{{ number_format($totalAmount, 2) }}</td>
                                <td class="text-info">{{ number_format($totalCompanyCommission, 2) }}</td>
                                <td class="text-secondary small">
                                    ({{ $totalAmount != 0 ? number_format((abs($totalCompanyCommission) / abs($totalAmount)) * 100, 2) : '0.00' }}%)
                                </td>
                                <td class="text-warning">{{ number_format($totalTurkeyCommission, 2) }}</td>
                                <td class="text-secondary small">
                                    ({{ $totalAmount != 0 ? number_format((abs($totalTurkeyCommission) / abs($totalAmount)) * 100, 2) : '0.00' }}%)
                                </td>

                                <td class="text-dark">{{ number_format($totalCompanyCommission + $totalTurkeyCommission, 2) }}</td>
                                <td colspan="1"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No commission transactions found for the selected date range.
                </div>
                @endif
            </div>
        </div>

        <div class="mt-4 mb-4 no-print">
            <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Transactions
            </a>
            <a href="{{ route('transactions.daily-report') }}" class="btn btn-primary">
                <i class="fas fa-calendar-day"></i> Daily Report
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
