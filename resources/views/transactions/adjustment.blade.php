@extends('layouts.app')

@section('title', 'Adjust Balance')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-edit mr-2"></i>Adjust Account Balance
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('transactions.adjustment.store') }}">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Account *</label>
                            <select name="account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">
                                        {{ $account->user->name }} - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Currency *</label>
                            <select name="currency_id" class="form-control" required>
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Amount *</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required
                                   placeholder="Positive to add, negative to deduct">
                            <small class="form-text text-muted">Use positive number to increase balance, negative to decrease</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Notes *</label>
                            <textarea name="notes" class="form-control" rows="3" required
                                      placeholder="Reason for adjustment"></textarea>
                        </div>

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save mr-2"></i>Adjust Balance
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
