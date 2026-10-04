<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead class="thead-light">
            <tr>
                <th>Time</th>
                <th>Transaction From</th>
                <th>To</th>
                <th>Currency</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Company Commission</th>
                <th class="text-right">Company Comm. (%)</th>
                <th class="text-right">Turkey Commission</th>
                <th class="text-right">Turkey Comm. (%)</th>
                <th class="text-right">First Total</th>
                <th class="text-right">Second Total</th>
                <th class="text-right text-success">Exchange Result</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportTransactions as $t)
                <tr>
                    <td>{{ $t->transaction_date->format('H:i') }}</td>
                    <td>
                        <small><strong>{{ $t->fromAccount->user->name ?? 'System' }}</strong></small><br>
                        @if(!empty($t->fromAccount->user->company_name))
                            <small class="text-info">{{ $t->fromAccount->user->company_name }}</small><br>
                        @endif
                        <small class="text-muted">{{ $t->fromAccount->account_number }}</small>
                    </td>
                    <td>
                        <small><strong>{{ $t->toAccount->user->name ?? 'System' }}</strong></small><br>
                        @if(!empty($t->toAccount->user->company_name))
                            <small class="text-info">{{ $t->toAccount->user->company_name }}</small><br>
                        @endif
                        <small class="text-muted">{{ $t->toAccount->account_number }}</small>
                    </td>
                    <td>{{ $t->currency->code }}</td>
                    <td class="text-right font-weight-bold text-primary">{{ number_format($t->amount, 2) }}</td>
                    <td class="text-right text-info">{{ number_format($t->company_commission, 2) }}</td>
                    <td class="text-right text-secondary small">
                        ({{ $t->amount != 0 ? number_format((abs($t->company_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%)
                    </td>
                    <td class="text-right text-success">{{ number_format($t->turkey_commission, 2) }}</td>
                    <td class="text-right text-secondary small">
                        ({{ $t->amount != 0 ? number_format((abs($t->turkey_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%)
                    </td>
                    <td class="text-right font-weight-bold">{{ number_format($t->amount + $t->company_commission, 2) }}</td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($t->amount + $t->turkey_commission, 2) }}</td>
                    <td class="text-right font-weight-bold">
                        @if($t->exchange_rate)
                            <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;" title="Rate">
                                <i class="fas fa-random text-primary mr-1"></i>Rate: {{ $t->exchange_rate }}<br>
                                <i class="fas {{ $t->exchange_action === 'multiply' ? 'fa-times' : 'fa-divide' }} text-secondary mr-1"></i>{{ ucfirst($t->exchange_action) }}
                            </div>
                            <span class="text-success">${{ number_format($t->exchange_result, 2) }}</span>
                        @else
                            <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: normal; text-align: right;">Auto-converted</div>
                            <span class="text-success">${{ number_format($t->currency->convertAmount($t->amount + $t->company_commission, 'USD'), 2) }}</span>
                        @endif
                    </td>
                    <td>
                        @if($t->type === 'transfer')
                            <span class="badge badge-primary" style="background-color: #4e73df;">Transfer</span>
                        @elseif($t->type === 'deposit')
                            <span class="badge badge-success" style="background-color: #1cc88a;">Plus (+)</span>
                        @elseif($t->type === 'withdrawal')
                            <span class="badge badge-danger" style="background-color: #e74a3b;">Minus (-)</span>
                        @else
                            <span class="badge badge-warning" style="background-color: #f6c23e; color: #fff;">Adjustment</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted">No transactions found for this category.</td>
                </tr>
            @endforelse
        </tbody>
        @if($reportTransactions->count() > 0)
            <tfoot class="bg-light font-weight-bold">
                <tr>
                    <td colspan="4" class="text-right">Category Totals:</td>
                    <td class="text-right text-primary">
                        {{ number_format($reportTransactions->sum('amount'), 2) }}
                    </td>
                    <td class="text-right text-info">
                        {{ number_format($reportTransactions->sum('company_commission'), 2) }}
                    </td>
                    <td class="text-right text-secondary small">
                        @php
                            $totalAmount = $reportTransactions->sum('amount');
                            $totalCompanyComm = $reportTransactions->sum('company_commission');
                            $totalTurkeyComm = $reportTransactions->sum('turkey_commission');
                        @endphp
                        ({{ $totalAmount != 0 ? number_format((abs($totalCompanyComm) / abs($totalAmount)) * 100, 2) : '0.00' }}%)
                    </td>
                    <td class="text-right text-success">
                        {{ number_format($reportTransactions->sum('turkey_commission'), 2) }}
                    </td>
                    <td class="text-right text-secondary small">
                        ({{ $totalAmount != 0 ? number_format((abs($totalTurkeyComm) / abs($totalAmount)) * 100, 2) : '0.00' }}%)
                    </td>
                    <td class="text-right">
                        {{ number_format($reportTransactions->sum('amount') + $reportTransactions->sum('company_commission'), 2) }}
                    </td>
                    <td class="text-right text-success">
                        {{ number_format($reportTransactions->sum('amount') + $reportTransactions->sum('turkey_commission'), 2) }}
                    </td>
                    <td class="text-right text-success">
                        @php
                            $totalUsd = $reportTransactions->map(function($t) {
                                if($t->exchange_rate) {
                                    return $t->exchange_result;
                                }
                                return $t->currency->convertAmount($t->amount + $t->company_commission, 'USD');
                            })->sum();
                        @endphp
                        ${{ number_format($totalUsd, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
