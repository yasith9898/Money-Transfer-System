<!-- Today's Activity Section -->
<div class="row">
    <!-- Today's Transactions (Transfers, Deposits, Withdrawals) -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #1cc88a !important;">
            <a href="#collapseTodayTrans" class="d-block card-header py-3 bg-white border-bottom-0" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseTodayTrans" style="border-radius: 12px 12px 0 0;">
                <h6 class="m-0 font-weight-bold text-success d-flex align-items-center">
                    <i class="fas fa-calendar-day mr-2"></i>Today's Transactions 
                    <span class="badge badge-success ml-2 px-2 py-1 shadow-sm">{{ $todaysTransactions->count() }}</span>
                </h6>
            </a>
            <div class="collapse show" id="collapseTodayTrans">
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                            <thead class="bg-gray-100 x-small text-uppercase font-weight-bold text-muted">
                                <tr>
                                    <th class="ps-3 py-3 rounded-left">Time</th>
                                    <th class="py-3">From</th>
                                    <th class="py-3">To</th>
                                    <th class="py-3">Amount</th>
                                    <th class="text-right py-3">Company Comm</th>
                                    <th class="text-right py-3">Comp (%)</th>
                                    <th class="text-right py-3">Turkey Comm</th>
                                    <th class="text-right py-3">Turk (%)</th>
                                    <th class="text-right py-3">First Total</th>
                                    <th class="text-right py-3">Second Total</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3 text-center">Status</th>
                                    <th class="pe-3 py-3 text-right rounded-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($todaysTransactions as $transaction)
                                <tr class="border-bottom">
                                    <td class="ps-3 py-3 font-weight-bold text-dark">{{ $transaction->created_at->format('H:i') }}</td>
                                    <td>
                                        @if($transaction->fromAccount)
                                            <div class="small font-weight-bold text-dark">{{ $transaction->fromAccount->user->name }}</div>
                                            @if(!empty($transaction->fromAccount->user->company_name))
                                                <div class="x-small text-info">{{ $transaction->fromAccount->user->company_name }}</div>
                                            @endif
                                            <div class="x-small text-muted mt-1"><i class="fas fa-wallet mr-1 text-primary"></i>{{ $transaction->fromAccount->account_number }}</div>
                                        @else
                                            <div class="small text-muted font-italic">System</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($transaction->toAccount)
                                            <div class="small font-weight-bold text-dark">{{ $transaction->toAccount->user->name }}</div>
                                            @if(!empty($transaction->toAccount->user->company_name))
                                                <div class="x-small text-info">{{ $transaction->toAccount->user->company_name }}</div>
                                            @endif
                                            <div class="x-small text-muted mt-1"><i class="fas fa-wallet mr-1 text-success"></i>{{ $transaction->toAccount->account_number }}</div>
                                        @else
                                            <div class="small text-muted font-italic">System</div>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold text-primary">
                                        {{ $transaction->currency->formatAmount($transaction->amount) }}
                                    </td>
                                    <td class="text-right text-info font-weight-bold">
                                        {{ $transaction->currency->formatAmount($transaction->company_commission) }}
                                    </td>
                                    <td class="text-right">
                                        <div class="x-small text-secondary bg-light px-2 py-1 rounded d-inline-block">
                                            {{ $transaction->amount != 0 ? number_format((abs($transaction->company_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%
                                        </div>
                                    </td>
                                    <td class="text-right text-success font-weight-bold">
                                        {{ $transaction->currency->formatAmount($transaction->turkey_commission) }}
                                    </td>
                                    <td class="text-right">
                                        <div class="x-small text-secondary bg-light px-2 py-1 rounded d-inline-block">
                                            {{ $transaction->amount != 0 ? number_format((abs($transaction->turkey_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%
                                        </div>
                                    </td>
                                    <td class="text-right font-weight-bold text-dark">
                                        {{ $transaction->currency->formatAmount($transaction->amount + $transaction->company_commission) }}
                                    </td>
                                    <td class="text-right font-weight-bold text-success">
                                        {{ $transaction->currency->formatAmount($transaction->amount + $transaction->turkey_commission) }}
                                    </td>
                                    <td>
                                        @if($transaction->type === 'transfer')
                                            <span class="badge badge-primary px-2 py-1 shadow-sm" style="background-color: #4e73df;">Transfer</span>
                                        @elseif($transaction->type === 'deposit')
                                            <span class="badge badge-success px-2 py-1 shadow-sm" style="background-color: #1cc88a;">Deposit</span>
                                        @elseif($transaction->type === 'withdrawal')
                                            <span class="badge badge-danger px-2 py-1 shadow-sm" style="background-color: #e74a3b;">Withdrawal</span>
                                        @else
                                            <span class="badge badge-warning text-white px-2 py-1 shadow-sm" style="background-color: #f6c23e;">{{ ucfirst($transaction->type) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($transaction->status == 'completed')
                                            <span class="badge bg-success-soft text-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Completed</span>
                                        @else
                                            <span class="badge bg-warning-soft text-warning px-2 py-1"><i class="fas fa-clock mr-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-right">
                                        <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-sm btn-primary rounded shadow-sm py-1 px-2">
                                            <i class="fas fa-eye small"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center py-5">
                                        <div class="text-muted mb-2"><i class="fas fa-calendar-day fa-2x text-gray-300"></i></div>
                                        <div class="small font-weight-bold text-gray-500">No transactions today</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($todaysTransactionStats->count() > 0)
                            <tfoot>
                                @foreach ($todaysTransactionStats as $stat)
                                <tr class="bg-gray-100 font-weight-bold text-dark x-small border-top-0">
                                    <td colspan="3" class="text-right py-3 pe-4 text-muted text-uppercase">Today's Totals ({{ $stat->currency->code }}):</td>
                                    <td class="text-primary py-3">{{ $stat->currency->formatAmount($stat->total_amount) }}</td>
                                    <td class="text-info text-right py-3">{{ $stat->currency->formatAmount($stat->total_company_commission) }}</td>
                                    <td class="text-secondary text-right py-3">
                                        ({{ $stat->total_amount != 0 ? number_format((abs($stat->total_company_commission) / abs($stat->total_amount)) * 100, 2) : '0.00' }}%)
                                    </td>
                                    <td class="text-success text-right py-3">{{ $stat->currency->formatAmount($stat->total_turkey_commission) }}</td>
                                    <td class="text-secondary text-right py-3">
                                        ({{ $stat->total_amount != 0 ? number_format((abs($stat->total_turkey_commission) / abs($stat->total_amount)) * 100, 2) : '0.00' }}%)
                                    </td>
                                    <td class="text-right py-3 text-white bg-secondary rounded">{{ $stat->currency->formatAmount($stat->total_amount + $stat->total_company_commission) }}</td>
                                    <td class="text-right py-3 text-white bg-secondary rounded">{{ $stat->currency->formatAmount($stat->total_amount + $stat->total_turkey_commission) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                                @endforeach
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Adjustments -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 12px; border-left: 4px solid #f6c23e !important;">
            <a href="#collapseTodayAdj" class="d-block card-header py-3 bg-white border-bottom-0" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseTodayAdj" style="border-radius: 12px 12px 0 0;">
                <h6 class="m-0 font-weight-bold text-warning d-flex align-items-center">
                    <i class="fas fa-edit mr-2"></i>Today's Adjustments 
                    <span class="badge badge-warning text-white ml-2 px-2 py-1 shadow-sm">{{ $todaysAdjustments->count() }}</span>
                </h6>
            </a>
            <div class="collapse show" id="collapseTodayAdj">
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" width="100%" cellspacing="0">
                            <thead class="bg-gray-100 x-small text-uppercase font-weight-bold text-muted">
                                <tr>
                                    <th class="ps-3 py-3 rounded-left">Time</th>
                                    <th class="py-3">From (Sender)</th>
                                    <th class="py-3">To (Receiver)</th>
                                    <th class="py-3">Currency</th>
                                    <th class="text-right py-3">Amount</th>
                                    <th class="py-3 text-center">Type</th>
                                    <th class="py-3">Notes</th>
                                    <th class="text-center py-3">Status</th>
                                    <th class="pe-3 py-3 text-right rounded-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($todaysAdjustments as $adj)
                                <tr class="border-bottom">
                                    <td class="ps-3 py-3 font-weight-bold text-dark">{{ $adj->created_at->format('H:i') }}</td>
                                    <td>
                                        @if($adj->fromAccount)
                                            <div class="small font-weight-bold text-dark">{{ $adj->fromAccount->user->name ?? 'System' }}</div>
                                            @if(!empty($adj->fromAccount->user->company_name))
                                                <div class="x-small text-info">{{ $adj->fromAccount->user->company_name }}</div>
                                            @endif
                                            <div class="x-small text-muted mt-1"><i class="fas fa-wallet mr-1 text-primary"></i>{{ $adj->fromAccount->account_number }}</div>
                                        @else
                                            <div class="small text-muted font-italic">System</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($adj->toAccount)
                                            <div class="small font-weight-bold text-dark">{{ $adj->toAccount->user->name ?? 'System' }}</div>
                                            @if(!empty($adj->toAccount->user->company_name))
                                                <div class="x-small text-info">{{ $adj->toAccount->user->company_name }}</div>
                                            @endif
                                            <div class="x-small text-muted mt-1"><i class="fas fa-wallet mr-1 text-success"></i>{{ $adj->toAccount->account_number }}</div>
                                        @else
                                            <div class="small text-muted font-italic">System</div>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary-soft text-secondary px-2 py-1">{{ $adj->currency->code }}</span></td>
                                    <td class="font-weight-bold text-right {{ $adj->toAccount && $adj->toAccount->wallet_type == 'main_company' ? 'text-danger' : 'text-success' }}">
                                        {{ $adj->toAccount && $adj->toAccount->wallet_type == 'main_company' ? '-' : '+' }}{{ $adj->currency->formatAmount($adj->amount) }}
                                    </td>
                                    <td class="text-center">
                                        @if($adj->toAccount && $adj->toAccount->wallet_type == 'main_company')
                                            <span class="badge badge-danger px-2 py-1 shadow-sm" style="background-color: #e74a3b;"><i class="fas fa-arrow-down mr-1"></i>Decrease (-)</span>
                                        @else
                                            <span class="badge badge-success px-2 py-1 shadow-sm" style="background-color: #1cc88a;"><i class="fas fa-arrow-up mr-1"></i>Increase (+)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-muted" title="{{ $adj->notes }}">{{ \Illuminate\Support\Str::limit($adj->notes, 40) }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-soft text-success px-2 py-1"><i class="fas fa-check-circle mr-1"></i>Completed</span>
                                    </td>
                                    <td class="pe-3 text-right">
                                        <a href="{{ route('transactions.show', $adj->id) }}" class="btn btn-sm btn-primary rounded shadow-sm py-1 px-2">
                                            <i class="fas fa-eye small"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted mb-2"><i class="fas fa-edit fa-2x text-gray-300"></i></div>
                                        <div class="small font-weight-bold text-gray-500">No adjustments today</div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($todaysAdjustmentStats->count() > 0)
                            <tfoot>
                                @foreach ($todaysAdjustmentStats as $stat)
                                <tr class="bg-gray-100 font-weight-bold text-dark x-small border-top-0">
                                    <td colspan="4" class="text-right py-3 pe-4 text-muted text-uppercase">
                                        Cumulative Adjustments ({{ $stat->currency->code }})
                                        <i class="fas fa-info-circle ml-1 text-primary" title="Net sum of all manual balance increases and decreases for users today."></i>
                                    </td>
                                    <td class="text-right py-3 bg-white border border-gray-200 rounded">
                                        <div class="d-flex flex-column align-items-end">
                                            <span class="text-success small" title="Total Increase (+) to users">+{{ $stat->currency->formatAmount($stat->total_increase) }}</span>
                                            <span class="text-danger small" title="Total Decrease (-) from users">-{{ $stat->currency->formatAmount($stat->total_decrease) }}</span>
                                            <div class="border-top w-50 my-1"></div>
                                            <strong class="{{ $stat->net_amount >= 0 ? 'text-success' : 'text-danger' }} h6 mb-0 font-weight-bold" style="font-size: 0.95rem;">
                                                {{ $stat->net_amount >= 0 ? '+' : '' }}{{ $stat->currency->formatAmount($stat->net_amount) }}
                                            </strong>
                                        </div>
                                    </td>
                                    <td colspan="4" class="align-middle py-3">
                                        <span class="badge bg-secondary-soft text-secondary px-2 py-1">{{ $stat->count }} adjustments</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
