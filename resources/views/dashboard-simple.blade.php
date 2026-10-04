<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Money Transfer System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/">
                Money Transfer System
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Dashboard</h1>

        @if(isset($error))
            <div class="alert alert-danger">
                <strong>Error:</strong> {{ $error }}
            </div>
        @endif

        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Links</h5>
                        <a href="/currencies" class="btn btn-primary btn-block mb-2">Currencies</a>
                        <a href="/accounts" class="btn btn-success btn-block mb-2">Accounts</a>
                        <a href="/transactions" class="btn btn-info btn-block">Transactions</a>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Welcome to Money Transfer System</h5>
                        <p class="card-text">Your financial management solution is being set up.</p>

                        @if(!isset($error))
                        <div class="alert alert-info">
                            System is loading. Please check if you have:
                            <ul>
                                <li>Created database tables (migrations)</li>
                                <li>Added some currencies</li>
                                <li>Created accounts</li>
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
