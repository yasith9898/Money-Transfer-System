<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Transactions Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #4e73df;
            font-size: 20px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .summary-box {
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f8f9fa;
        }
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
        }
        .transactions-table th, .transactions-table td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }
        .transactions-table th {
            background-color: #4e73df;
            color: white;
        }
        .text-primary { color: #4e73df; }
        .text-success { color: #1cc88a; }
        .text-info { color: #36b9cc; }
        .text-danger { color: #e74a3b; }
        .font-weight-bold { font-weight: bold; }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            color: white;
        }
        .badge-primary { background-color: #4e73df; }
        .badge-success { background-color: #1cc88a; }
        .badge-danger { background-color: #e74a3b; }
        .badge-warning { background-color: #f6c23e; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>All Transactions Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}</p>
        <p>Mode: 
            @if($balanceType == 'positive') Just + Transactions (Inflow)
            @elseif($balanceType == 'negative') Just - Transactions (Outflow)
            @else All Transactions (+ and -)
            @endif
        </p>
        <p>Generated on: {{ now()->format('M j, Y H:i') }}</p>
    </div>

    <style>
        .summary-boxes {
            margin-bottom: 20px;
            width: 100%;
        }
        .summary-item {
            display: inline-block;
            width: 32%;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-label {
            display: block;
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-value {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>

    <div class="summary-boxes">
        <div class="summary-item">
            <span class="summary-label">Total Transactions</span>
            <span class="summary-value">{{ $totalTransactions }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Company Comm.</span>
            <span class="summary-value">{{ number_format($totalCompanyCommission, 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Turkey Comm.</span>
            <span class="summary-value">{{ number_format($totalTurkeyCommission, 2) }}</span>
        </div>
    </div>

    <div class="summary-box">
        <h3 style="font-size: 11px; color: #4e73df; margin-bottom: 5px;">Currency Breakdown</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="font-size: 8px;">Currency</th>
                    <th style="font-size: 8px;">Count</th>
                    <th style="font-size: 8px;">Total Amount</th>
                    <th style="font-size: 8px;">Comp. Comm.</th>
                    <th style="font-size: 8px;">Comp. (%)</th>
                    <th style="font-size: 8px;">Turk. Comm.</th>
                    <th style="font-size: 8px;">Turk. (%)</th>
                    <th style="font-size: 8px;">First Total</th>
                    <th style="font-size: 8px;">Second Total</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($currencyBreakdown as $breakdown)
                    <tr>
                        <td><strong>{{ $breakdown['currency']->code }}</strong></td>
                        <td>{{ $breakdown['count'] }}</td>
                        <td class="font-weight-bold {{ isset($accountId) && $accountId !== 'all' && $breakdown['total_amount'] < 0 ? 'text-danger' : 'text-primary' }}">
                            {{ $breakdown['currency']->formatAmount($breakdown['total_amount']) }}
                        </td>
                        <td>{{ $breakdown['currency']->formatAmount($breakdown['company_commission']) }}</td>
                        <td style="font-size: 8px;">
                            ({{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%)
                        </td>
                        <td>{{ $breakdown['currency']->formatAmount($breakdown['turkey_commission']) }}</td>
                        <td style="font-size: 8px;">
                            ({{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%)
                        </td>
                        <td class="font-weight-bold {{ isset($accountId) && $accountId !== 'all' && $breakdown['total_amount'] < 0 ? 'text-danger' : '' }}">
                            {{ $breakdown['currency']->formatAmount($breakdown['total_amount'] + ($breakdown['total_amount'] < 0 ? -$breakdown['company_commission'] : $breakdown['company_commission'])) }}
                        </td>
                        <td class="font-weight-bold text-success">
                            {{ $breakdown['currency']->formatAmount($breakdown['total_amount'] + ($breakdown['total_amount'] < 0 ? -$breakdown['turkey_commission'] : $breakdown['turkey_commission'])) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3>Transaction Details</h3>
    <table class="transactions-table">
        <thead>
            <tr>
                <th>Time & Date</th>
                <th>Transaction From</th>
                <th>To</th>
                <th>Currency</th>
                <th>Amount</th>
                <th>Comp Comm.</th>
                <th>Comp Comm. (%)</th>
                <th>Turk Comm.</th>
                <th>Turk Comm. (%)</th>
                <th>First Total</th>
                <th>Second Total</th>
                <th class="text-success">Exchange Result</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                <td>
                    {{ $transaction->fromAccount->user->name ?? 'System' }}<br>
                    @if(!empty($transaction->fromAccount->user->company_name))
                        <span style="font-size: 8px; color: #666;">{{ $transaction->fromAccount->user->company_name }}</span><br>
                    @endif
                    {{ $transaction->fromAccount->account_number }}
                </td>
                <td>
                    {{ $transaction->toAccount->user->name ?? 'System' }}<br>
                    @if(!empty($transaction->toAccount->user->company_name))
                        <span style="font-size: 8px; color: #666;">{{ $transaction->toAccount->user->company_name }}</span><br>
                    @endif
                    {{ $transaction->toAccount->account_number }}
                </td>
                <td>{{ $transaction->currency->code }}</td>
                <td class="font-weight-bold {{ isset($accountId) && $accountId !== 'all' && $transaction->from_account_id == $accountId ? 'text-danger' : 'text-primary' }}">
                    @php
                        $displayAmount = $transaction->amount;
                        if (isset($accountId) && $accountId !== 'all' && $transaction->from_account_id == $accountId) {
                            $displayAmount = -$transaction->amount;
                        }
                    @endphp
                    {{ $transaction->currency->formatAmount($displayAmount) }}
                </td>
                <td>{{ $transaction->currency->formatAmount($transaction->company_commission) }}</td>
                <td style="font-size: 8px;">
                    ({{ $transaction->amount != 0 ? number_format((abs($transaction->company_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%)
                </td>
                <td>{{ $transaction->currency->formatAmount($transaction->turkey_commission) }}</td>
                <td style="font-size: 8px;">
                    ({{ $transaction->amount != 0 ? number_format((abs($transaction->turkey_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%)
                </td>
                <td class="font-weight-bold {{ isset($accountId) && $accountId !== 'all' && $transaction->from_account_id == $accountId ? 'text-danger' : '' }}">
                    @php
                        $total1 = $transaction->amount + ($transaction->fromAccount->wallet_type !== 'main_company' ? $transaction->company_commission : 0);
                        if (isset($accountId) && $accountId !== 'all' && $transaction->from_account_id == $accountId) {
                            $total1 = -($total1);
                        }
                    @endphp
                    {{ $transaction->currency->formatAmount($total1) }}
                </td>
                <td class="font-weight-bold text-success">
                    @php
                        $total2 = $transaction->amount + ($transaction->fromAccount->wallet_type !== 'main_company' ? $transaction->turkey_commission : 0);
                        if (isset($accountId) && $accountId !== 'all' && $transaction->from_account_id == $accountId) {
                            $total2 = -($total2);
                        }
                    @endphp
                    {{ $transaction->currency->formatAmount($total2) }}
                </td>
                <td class="text-success font-weight-bold">
                    @if($transaction->exchange_rate)
                        <span style="font-size: 8px; color: #666;">[R:{{ $transaction->exchange_rate }}]</span><br>
                        ${{ number_format($transaction->exchange_result, 2) }}
                    @else
                        @php
                            $usdAmount = $transaction->currency->convertAmount($transaction->amount + ($transaction->fromAccount->wallet_type !== 'main_company' ? $transaction->company_commission : 0), 'USD');
                        @endphp
                        ${{ number_format($usdAmount, 2) }}
                    @endif
                </td>
                <td>
                    @if($transaction->type === 'transfer')
                        <span class="badge badge-primary">Transfer</span>
                    @elseif($transaction->type === 'deposit')
                        <span class="badge badge-success">Deposit</span>
                    @elseif($transaction->type === 'withdrawal')
                        <span class="badge badge-danger">Withdrawal</span>
                    @else
                        <span class="badge badge-warning">Adjustment</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @foreach ($currencyBreakdown as $breakdown)
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="4" style="text-align: right;">Totals ({{ $breakdown['currency']->code }}):</td>
                <td class="{{ isset($accountId) && $accountId !== 'all' && $breakdown['total_amount'] < 0 ? 'text-danger' : 'text-primary' }}">{{ $breakdown['currency']->formatAmount($breakdown['total_amount']) }}</td>
                <td class="text-info">{{ $breakdown['currency']->formatAmount($breakdown['company_commission']) }}</td>
                <td style="font-size: 8px;">
                    ({{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%)
                </td>
                <td class="text-success">{{ $breakdown['currency']->formatAmount($breakdown['turkey_commission']) }}</td>
                <td style="font-size: 8px;">
                    ({{ $breakdown['total_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['total_amount'])) * 100, 2) : '0.00' }}%)
                </td>
                <td class="font-weight-bold {{ isset($accountId) && $accountId !== 'all' && $breakdown['total_amount'] < 0 ? 'text-danger' : '' }}">
                    {{ $breakdown['currency']->formatAmount($breakdown['total_amount'] + ($breakdown['total_amount'] < 0 ? -$breakdown['company_commission'] : $breakdown['company_commission'])) }}
                </td>
                <td class="font-weight-bold text-success">
                    {{ $breakdown['currency']->formatAmount($breakdown['total_amount'] + ($breakdown['total_amount'] < 0 ? -$breakdown['turkey_commission'] : $breakdown['turkey_commission'])) }}
                </td>
                <td class="text-success">
                    ${{ number_format($breakdown['currency']->convertAmount($breakdown['total_amount'], 'USD'), 2) }}
                </td>
                <td></td>
            </tr>
            @endforeach
        </tfoot>
    </table>

    <div class="footer">
        <p>Software Powered by BrainTech Projects</p>
    </div>
</body>
</html>
