@extends('layouts.app')

@section('title', 'Account Details - ' . $account->user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle mr-2"></i>Account Details
        </h1>
        <div class="btn-group">
            @if(!in_array($account->wallet_type, ['main_company', 'office']) || auth()->user()->role === 'super_admin')
            <a href="{{ route('accounts.edit', $account->id) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-2"></i>Edit Account
            </a>
            @endif
            <a href="{{ route('accounts.statement', $account->id) }}" class="btn btn-info">
                <i class="fas fa-file-invoice mr-2"></i>View Statement
            </a>
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Accounts
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Account Information -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Account Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Account Number:</strong></p>
                            <p><strong>Account Holder:</strong></p>
                            <p><strong>Company Name:</strong></p>
                            <p><strong>Mobile:</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p class="font-weight-bold text-primary">{{ $account->account_number }}</p>
                            <p>{{ $account->user->name }}</p>
                            <p>{{ $account->user->company_name }}</p>
                            <p>{{ $account->user->mobile ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Status:</strong></p>
                            <p><strong>Created Date:</strong></p>
                        </div>
                        <div class="col-md-6">
                            <p>
                                @if($account->is_active)
                                    <span class="badge badge-success" style="background-color: #1cc88a; padding: 0.5em 0.75em;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="background-color: #e74a3b; padding: 0.5em 0.75em;">Inactive</span>
                                @endif
                            </p>
                            <p>{{ $account->created_at->format('M j, Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Status Toggle -->
                    <div class="mt-4">
                        @if($account->is_active)
                            <form action="{{ route('accounts.deactivate', $account->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning"
                                        onclick="return confirm('Are you sure you want to deactivate this account?')">
                                    <i class="fas fa-toggle-off mr-2"></i>Deactivate Account
                                </button>
                            </form>
                        @else
                            <form action="{{ route('accounts.activate', $account->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-toggle-on mr-2"></i>Activate Account
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Balance Adjustment Form -->
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>Adjust Balance
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('accounts.adjust-balance', $account->id) }}">
                        @csrf

                        <div class="form-group">
                            <label>Currency</label>
                            <select name="currency_id" class="form-control" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required
                                   placeholder="Positive to add, negative to deduct">
                        </div>

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="Reason for adjustment"></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-2"></i>Adjust Balance
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Balances -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet mr-2"></i>Account Balances
                    </h5>
                </div>
                <div class="card-body">
                    @if($account->balances->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Currency</th>
                                        <th>Balance</th>
                                        <th>Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($account->balances as $balance)
                                    <tr>
                                        <td>
                                            <strong>{{ $balance->currency->code }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $balance->currency->name }}</small>
                                        </td>
                                        <td class="font-weight-bold {{ $balance->balance < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $balance->currency->formatAmount($balance->balance) }}
                                        </td>
                                        <td>
                                            <small>{{ $balance->updated_at->format('M j, Y H:i') }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No balances found for this account.</p>
                    @endif
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt mr-2"></i>Recent Transactions
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentTransactions as $transaction)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <small class="font-weight-bold">
                                            @if($transaction->from_account_id == $account->id)
                                                <span class="text-danger">Sent to {{ $transaction->toAccount->user->name }}</span>
                                            @else
                                                <span class="text-success">Received from {{ $transaction->fromAccount->user->name }}</span>
                                            @endif
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $transaction->currency->formatAmount($transaction->amount) }}
                                            • {{ $transaction->created_at->format('M j, H:i') }}
                                        </small>
                                    </div>
                                    @if($transaction->status === 'completed')
                                        <span class="badge badge-success" style="background-color: #1cc88a; padding: 0.4em 0.6em;">Completed</span>
                                    @elseif($transaction->status === 'auto')
                                        <span class="badge badge-info" style="background-color: #36b9cc; padding: 0.4em 0.6em;">Auto</span>
                                    @elseif($transaction->status === 'pending')
                                        <span class="badge badge-warning" style="background-color: #f6c23e; color: #fff; padding: 0.4em 0.6em;">Pending</span>
                                    @elseif($transaction->status === 'failed')
                                        <span class="badge badge-danger" style="background-color: #e74a3b; padding: 0.4em 0.6em;">Failed</span>
                                    @else
                                        <span class="badge badge-secondary" style="background-color: #858796; padding: 0.4em 0.6em;">{{ ucfirst($transaction->status) }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No recent transactions.</p>
                    @endif

                    @if($recentTransactions->count() > 0)
                        <div class="text-center mt-3">
                            <a href="{{ route('accounts.statement', $account->id) }}" class="btn btn-sm btn-outline-info">
                                View All Transactions
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
