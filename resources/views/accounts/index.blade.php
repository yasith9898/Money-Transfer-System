@extends('layouts.app')

@section('title', 'All Accounts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-users mr-2"></i>All Accounts
        </h1>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Create New Account
        </a>
    </div>

    <!-- Search Form -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('accounts.search') }}">
                <div class="input-group">
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by name, company, email, or account number...">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Account Details</th>
                            <th>Balances</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                        <tr>
                            <td>
                                <strong>{{ $account->user->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $account->account_number }}</small>
                                <br>
                                <br>
                                <small class="text-muted">{{ $account->user->company_name }}</small>
                            </td>
                            <td>
                                @foreach($account->balances as $balance)
                                    <div class="mb-1">
                                        <span class="font-weight-bold {{ $balance->balance < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $balance->currency->code }}: {{ $balance->currency->formatAmount($balance->balance) }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($account->balances->count() === 0)
                                    <span class="text-muted">No balances</span>
                                @endif
                            </td>
                            <td>
                                @if($account->is_active)
                                    <span class="badge badge-success" style="background-color: #1cc88a; padding: 0.5em 0.75em;">Active</span>
                                @else
                                    <span class="badge badge-danger" style="background-color: #e74a3b; padding: 0.5em 0.75em;">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <!-- Actions restored as per user request -->
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('accounts.show', $account->id) }}"
                                       class="btn btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if(!in_array($account->wallet_type, ['main_company', 'office']) || auth()->user()->role === 'super_admin')
                                    <a href="{{ route('accounts.edit', $account->id) }}"
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endif

                                    <a href="{{ route('accounts.statement', $account->id) }}"
                                       class="btn btn-secondary" title="Statement">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>

                                    @if(!in_array($account->wallet_type, ['main_company', 'office']))
                                    <form action="{{ $account->is_active ? route('accounts.deactivate', $account->id) : route('accounts.activate', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $account->is_active ? 'deactivate' : 'activate' }} this account?')">
                                        @csrf
                                        <button type="submit" class="btn btn-{{ $account->is_active ? 'secondary' : 'success' }}" title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $account->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('accounts.destroy', $account->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to DELETE this account? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <p>No accounts found. <a href="{{ route('accounts.create') }}">Create the first account</a></p>
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
