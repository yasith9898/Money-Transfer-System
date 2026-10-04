<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-money-bill-transfer"></i> Money Transfer System
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="{{ route('accounts.index') }}">Accounts</a>
                <a class="nav-link" href="{{ route('transactions.index') }}">Transactions</a>
                <a class="nav-link" href="{{ route('currencies.index') }}">Currencies</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Dashboard</h1>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif


        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Accounts</h5>
                        <h2>{{ $totalAccounts ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Today's Transactions</h5>
                        <h2>{{ $todayTransactions ?? 0 }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Company Commission</h5>
                        <h2>${{ number_format($totalCompanyCommission ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Total Balance (USD)</h5>
                        <h2>${{ number_format($totalBalanceUSD ?? 0, 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>


        <!-- System Wallets Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-building"></i> Company Wallet Balances</h5>
                    </div>
                    <div class="card-body">
                        @if($companyWalletBalances->count() > 0)
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Currency</th>
                                        <th class="text-end">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($companyWalletBalances as $balance)
                                    <tr>
                                        <td><strong>{{ $balance->currency->code }}</strong> ({{ $balance->currency->name }})</td>
                                        <td class="text-end">
                                            <strong>{{ $balance->currency->symbol }} {{ number_format($balance->balance, 2) }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted">No balances available</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('accounts.create') }}" class="btn btn-primary btn-lg btn-block mb-2">
                            <i class="fas fa-user-plus"></i> Create New Account
                        </a>
                        <a href="{{ route('transactions.transfer.create') }}" class="btn btn-success btn-lg btn-block mb-2">
                            <i class="fas fa-exchange-alt"></i> Make a Transfer
                        </a>
                        <a href="{{ route('currencies.create') }}" class="btn btn-info btn-lg btn-block">
                            <i class="fas fa-coins"></i> Add Currency
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Reports</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('transactions.daily-report') }}" class="btn btn-outline-primary btn-lg btn-block mb-2">
                            <i class="fas fa-calendar-day"></i> Daily Report
                        </a>
                        <a href="{{ route('transactions.commission-report') }}" class="btn btn-outline-success btn-lg btn-block mb-2">
                            <i class="fas fa-chart-line"></i> Commission Report
                        </a>
                        <a href="{{ route('transactions.index') }}" class="btn btn-outline-info btn-lg btn-block">
                            <i class="fas fa-list"></i> All Transactions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
