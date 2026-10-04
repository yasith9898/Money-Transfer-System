@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-eye mr-2"></i>Transaction Details
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Transaction ID:</strong> #{{ $transaction->id }}</p>
                            <p><strong>Date & Time:</strong> {{ $transaction->created_at->format('M j, Y H:i:s') }}</p>
                            <p><strong>Type:</strong>
                                @if($transaction->type === 'transfer')
                                    <span class="badge badge-primary" style="background-color: #4e73df;">Transfer</span>
                                @elseif($transaction->type === 'deposit')
                                    <span class="badge badge-success" style="background-color: #1cc88a;">Deposit</span>
                                @elseif($transaction->type === 'withdrawal')
                                    <span class="badge badge-danger" style="background-color: #e74a3b;">Withdrawal</span>
                                @else
                                    <span class="badge badge-warning" style="background-color: #f6c23e; color: #fff;">Adjustment</span>
                                @endif
                            </p>
                            <p><strong>Status:</strong>
                                @if($transaction->status === 'completed')
                                    <span class="badge badge-success" style="background-color: #1cc88a;">Completed</span>
                                @elseif($transaction->status === 'auto')
                                    <span class="badge badge-info" style="background-color: #36b9cc;">Auto</span>
                                @elseif($transaction->status === 'pending')
                                    <span class="badge badge-warning" style="background-color: #f6c23e; color: #fff;">Pending</span>
                                @elseif($transaction->status === 'failed')
                                    <span class="badge badge-danger" style="background-color: #e74a3b;">Failed</span>
                                @else
                                    <span class="badge badge-secondary" style="background-color: #858796;">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Amount:</strong>
                                <span class="font-weight-bold text-primary">
                                    {{ $transaction->currency->formatAmount($transaction->amount) }}
                                </span>
                            </p>
                            <p><strong>Company Commission:</strong> {{ $transaction->currency->formatAmount($transaction->company_commission) }}
                                @if($transaction->amount > 0)
                                    <small class="text-secondary">({{ number_format(($transaction->company_commission / $transaction->amount) * 100, 2) }}%)</small>
                                @endif
                            </p>
                            <p><strong>Turkey Commission:</strong> {{ $transaction->currency->formatAmount($transaction->turkey_commission) }} 
                                @if($transaction->amount > 0)
                                    <small class="text-secondary">({{ number_format(($transaction->turkey_commission / $transaction->amount) * 100, 2) }}%)</small>
                                @endif
                            </p>
                            <hr>
                            <p><strong>First Total:</strong>
                                <span class="font-weight-bold">{{ $transaction->currency->formatAmount($transaction->amount + $transaction->company_commission) }}</span>
                            </p>
                            <hr>
                            <p><strong>Second Total:</strong>
                                <span class="font-weight-bold text-success">{{ $transaction->currency->formatAmount($transaction->amount + $transaction->turkey_commission) }}</span>
                            </p>
                            <hr>
                            @if($transaction->exchange_rate)
                                <p><strong>Exchange Rate:</strong> <span class="text-primary"><i class="fas fa-random mr-1"></i>{{ $transaction->exchange_rate }}</span></p>
                                <p><strong>Exchange Action:</strong> <span class="text-secondary"><i class="fas {{ $transaction->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} mr-1"></i>{{ ucfirst($transaction->exchange_action) }}</span></p>
                                <p><strong>Exchange Result:</strong> <span class="text-success font-weight-bold" style="font-size: 1.1rem;">${{ number_format($transaction->exchange_result, 2) }}</span></p>
                            @else
                                <p><strong>Exchange Result <small class="text-muted">(Auto-converted)</small>:</strong> 
                                    <span class="text-success font-weight-bold" style="font-size: 1.1rem;">${{ number_format($transaction->currency->convertAmount($transaction->amount + $transaction->company_commission, 'USD'), 2) }}</span>
                                </p>
                            @endif

                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0">From Account</h6>
                                </div>
                                <div class="card-body">
                                    <strong>{{ $transaction->fromAccount->user->name }}</strong><br>
                                    @if(!empty($transaction->fromAccount->user->company_name))
                                        <span class="text-info small">{{ $transaction->fromAccount->user->company_name }}</span><br>
                                    @endif
                                    <small class="text-muted"><i class="fas fa-wallet mr-1"></i>{{ $transaction->fromAccount->account_number }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">To Account</h6>
                                </div>
                                <div class="card-body">
                                    <strong>{{ $transaction->toAccount->user->name }}</strong><br>
                                    @if(!empty($transaction->toAccount->user->company_name))
                                        <span class="text-info small">{{ $transaction->toAccount->user->company_name }}</span><br>
                                    @endif
                                    <small class="text-muted"><i class="fas fa-wallet mr-1"></i>{{ $transaction->toAccount->account_number }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($transaction->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Notes</h6>
                                </div>
                                <div class="card-body">
                                    {{ $transaction->notes }}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="form-group mt-4">
                        <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Transactions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
