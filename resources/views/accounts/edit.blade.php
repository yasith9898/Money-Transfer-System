@extends('layouts.app')

@section('title', 'Edit Account - ' . $account->user->name)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>Edit Account
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('accounts.update', $account->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Account Number</label>
                                    <input type="text" class="form-control bg-light"
                                           value="{{ $account->account_number }}" readonly>
                                    <small class="form-text text-muted">Account number cannot be changed</small>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">Full Name *</label>
                                    <input type="text" name="name" class="form-control"
                                           value="{{ old('name', $account->user->name) }}" required>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">Company Name *</label>
                                    <input type="text" name="company_name" class="form-control"
                                           value="{{ old('company_name', $account->user->company_name) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Email field removed -->

                                <div class="form-group">
                                    <label class="font-weight-bold">Mobile Number</label>
                                    <input type="text" name="mobile" class="form-control"
                                           value="{{ old('mobile', $account->user->mobile) }}">
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">Account Status</label>
                                    <div>
                                        @if($account->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save mr-2"></i>Update Account
                            </button>
                            <a href="{{ route('accounts.show', $account->id) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Account Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle mr-2"></i>Account Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Created:</strong> {{ $account->created_at->format('M j, Y H:i') }}</p>
                            <p><strong>Last Updated:</strong> {{ $account->updated_at->format('M j, Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Balances:</strong></p>
                            <ul>
                                @foreach($account->balances as $balance)
                                    <li>
                                        {{ $balance->currency->code }}:
                                        <span class="{{ $balance->balance < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $balance->currency->formatAmount($balance->balance) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
