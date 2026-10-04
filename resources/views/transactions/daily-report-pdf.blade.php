<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Transaction Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #333; }
        .summary-box { width: 100%; margin-bottom: 20px; }
        .summary-item { display: inline-block; width: 23%; padding: 10px; background-color: #f8f9fa; border: 1px solid #ddd; text-align: center; }
        .summary-label { display: block; font-size: 9px; color: #666; }
        .summary-value { display: block; font-size: 12px; font-weight: bold; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .section-title { font-size: 13px; font-weight: bold; margin: 15px 0 10px 0; border-bottom: 1px solid #eee; padding-bottom: 3px; color: #2c3e50; }
        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Transaction Report</h1>
        <p>Date: {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">Total Transactions</span>
            <span class="summary-value">{{ $totalTransactions }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Company Comm.</span>
            <span class="summary-value">
                {{ number_format($totalCompanyCommission, 2) }}
            </span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Turkey Comm.</span>
            <span class="summary-value">
                {{ number_format($totalTurkeyCommission, 2) }}
            </span>
        </div>
    </div>

    <div class="section-title">Currency Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Currency</th>
                <th class="text-center">Count</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Comp. Comm.</th>
                <th class="text-right">Comp. Comm. (%)</th>
                <th class="text-right">Turk. Comm.</th>
                <th class="text-right">Turk. Comm. (%)</th>
                <th class="text-right">First Total</th>
                <th class="text-right">Second Total</th>
                <th class="text-right text-success">Exchange Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($currencyBreakdown as $b)
                <tr>
                    <td><strong>{{ $b['currency']->code }}</strong></td>
                    <td class="text-center">{{ $b['count'] }}</td>
                    <td class="text-right">{{ number_format($b['total_amount'], 2) }}</td>
                    <td class="text-right">{{ number_format($b['company_commission'], 2) }}</td>
                    <td class="text-right" style="font-size: 8px;">{{ $b['total_amount'] != 0 ? number_format((abs($b['company_commission']) / abs($b['total_amount'])) * 100, 2) : '0.00' }}%</td>
                    <td class="text-right">{{ number_format($b['turkey_commission'], 2) }}</td>
                    <td class="text-right" style="font-size: 8px;">{{ $b['total_amount'] != 0 ? number_format((abs($b['turkey_commission']) / abs($b['total_amount'])) * 100, 2) : '0.00' }}%</td>
                    <td class="text-right font-bold">{{ number_format($b['total_amount'] + $b['company_commission'], 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($b['total_amount'] + $b['turkey_commission'], 2) }}</td>
                    <td class="text-right font-bold text-success">${{ number_format($b['currency']->convertAmount($b['total_amount'], 'USD'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Transactions List</div>
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Transaction From</th>
                <th>To</th>
                <th>Currency</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Company Commission</th>
                <th class="text-right" style="font-size: 8px;">Comp. Comm. (%)</th>
                <th class="text-right">Turkey Commission</th>
                <th class="text-right" style="font-size: 8px;">Turk. Comm. (%)</th>
                <th class="text-right">First Total</th>
                <th class="text-right">Second Total</th>
                <th class="text-right text-success">Exchange Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $t)
                <tr>
                    <td>{{ $t->transaction_date->format('H:i') }}</td>
                    <td>
                        {{ $t->fromAccount->user->name ?? 'System' }}<br>
                        @if(!empty($t->fromAccount->user->company_name))
                            <span style="font-size: 8px; color: #666;">{{ $t->fromAccount->user->company_name }}</span><br>
                        @endif
                        {{ $t->fromAccount->account_number }}
                    </td>
                    <td>
                        {{ $t->toAccount->user->name ?? 'System' }}<br>
                        @if(!empty($t->toAccount->user->company_name))
                            <span style="font-size: 8px; color: #666;">{{ $t->toAccount->user->company_name }}</span><br>
                        @endif
                        {{ $t->toAccount->account_number }}
                    </td>
                    <td>{{ $t->currency->code }}</td>
                    <td class="text-right">{{ number_format($t->amount, 2) }}</td>
                    <td class="text-right">{{ number_format($t->company_commission, 2) }}</td>
                    <td class="text-right" style="font-size: 9px;">{{ $t->amount != 0 ? number_format((abs($t->company_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%</td>
                    <td class="text-right">{{ number_format($t->turkey_commission, 2) }}</td>
                    <td class="text-right" style="font-size: 9px;">{{ $t->amount != 0 ? number_format((abs($t->turkey_commission) / abs($t->amount)) * 100, 2) : '0.00' }}%</td>
                    <td class="text-right font-bold">{{ number_format($t->amount + $t->company_commission, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($t->amount + $t->turkey_commission, 2) }}</td>
                    <td class="text-right text-success">
                        @if($t->exchange_rate)
                            <span style="font-size: 8px; color: #666;">[R:{{ $t->exchange_rate }}]</span><br>
                            ${{ number_format($t->exchange_result, 2) }}
                        @else
                            ${{ number_format($t->currency->convertAmount($t->amount + $t->company_commission, 'USD'), 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated by Money Transfer System on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
