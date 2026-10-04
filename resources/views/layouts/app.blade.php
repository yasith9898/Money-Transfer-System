<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Money Transfer System')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.25rem;
        }

        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .dropdown-menu {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15);
            border: none;
        }

        .dropdown-header {
            padding: 10px 20px;
            background: #f8f9fa;
            border-radius: 10px 10px 0 0;
        }

        .dropdown-item {
            padding: 10px 20px;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
            padding-left: 25px;
        }

        .dropdown-item.text-danger:hover {
            background: #fee;
        }

        .btn-light {
            background: white;
            border: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
        }

        .btn-light:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
        }
    </style>

    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-money-bill-transfer"></i> Money Transfer System
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('accounts.index') }}">
                            <i class="fas fa-users"></i> Accounts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('transactions.index') }}">
                            <i class="fas fa-exchange-alt"></i> Transactions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('currencies.index') }}">
                            <i class="fas fa-coins"></i> Currencies
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('transactions.daily-report') }}">Daily Report</a></li>
                            <li><a class="dropdown-item" href="{{ route('transactions.daily-summary') }}">Daily Summary Report</a></li>
                             <li><a class="dropdown-item" href="{{ route('transactions.commission-report') }}">Commission Report</a></li>
                             <li><a class="dropdown-item" href="{{ route('transactions.all-transactions-report') }}">All Transactions Report</a></li>
                             <li><a class="dropdown-item" href="{{ route('transactions.company-report') }}">Company Transaction Report</a></li>
                             <li><a class="dropdown-item" href="{{ route('transactions.account-balances-report') }}">Account Balances Report</a></li>
                             <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="{{ route('accounts.negative-balances') }}">Negative Balance Accounts</a></li>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    @auth
                        <!-- Admin Management (Super Admin Only) -->
                        @if(auth()->user()->role === 'super_admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-user-shield"></i> Admins
                            </a>
                        </li>
                        @endif

                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-header">
                                        <strong>{{ auth()->user()->name }}</strong><br>
                                        <small class="text-muted">{{ auth()->user()->email }}</small><br>
                                        <span class="badge bg-{{ auth()->user()->role === 'super_admin' ? 'danger' : 'warning' }} mt-1">
                                            {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
                                        </span>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.profile') ?? route('admin.dashboard') }}">
                                        <i class="fas fa-user me-2"></i>My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.change-password') ?? route('admin.dashboard') }}">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>
</html>
