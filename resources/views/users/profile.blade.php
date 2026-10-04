@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-circle mr-2"></i>My Profile
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Full Name *</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name', $user->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Company Name *</label>
                                    <input type="text" name="company_name" class="form-control"
                                           value="{{ old('company_name', $user->company_name) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Email Address *</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control"
                                           value="{{ old('mobile', $user->mobile) }}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Update Profile
                            </button>
                            <a href="{{ route('users.change-password') }}" class="btn btn-warning">
                                <i class="fas fa-key mr-2"></i>Change Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Accounts Summary -->
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet mr-2"></i>My Accounts Summary
                    </h5>
                </div>
                <div class="card-body">
                    @if($accounts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Account Number</th>
                                        <th>Status</th>
                                        <th>Balances</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($accounts as $account)
                                    <tr>
                                        <td>
                                            <strong>{{ $account->account_number }}</strong>
                                        </td>
                                        <td>
                                            @if($account->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($account->balances as $balance)
                                                <div class="mb-1">
                                                    <span class="font-weight-bold {{ $balance->balance < 0 ? 'text-danger' : 'text-success' }}">
                                                        {{ $balance->currency->formatAmount($balance->balance) }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ route('accounts.show', $account->id) }}"
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('accounts.statement', $account->id) }}"
                                               class="btn btn-sm btn-secondary">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No accounts found.</p>
                    @endif
                </div>
            </div>

            <!-- Total Balances -->
            @if(!empty($balances))
            <div class="card shadow mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie mr-2"></i>Total Balances
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($balances as $currencyCode => $balance)
                        <div class="col-md-3 col-6 mb-3">
                            <div class="text-center p-3 border rounded">
                                <div class="h5 font-weight-bold {{ $balance < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $currencyCode }}
                                </div>
                                <div class="h6 {{ $balance < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($balance, 2) }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
