<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commission Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-success {
            color: #198754;
        }
        .text-info {
            color: #0dcaf0;
        }
        .text-primary {
            color: #0d6efd;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-item {
            display: inline-block;
            width: 23%;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-label {
            display: block;
            font-size: 10px;
            color: #666;
        }
        .summary-value {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Commission Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
    </div>

    <!-- Statistics Summary -->
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

    <!-- Commission Breakdown by Currency -->
    @if($commissionsByCurrency->count() > 0)
    <div class="section-title">Commission Breakdown by Currency</div>
    <table>
        <thead>
            <tr>
                <th>Currency</th>
                <th class="text-center">Transactions</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Company Commission</th>
                <th class="text-right">Company Comm. (%)</th>
                <th class="text-right">Turkey Commission</th>
                <th class="text-right">Turkey Comm. (%)</th>
                <th class="text-right">Total 1 (Amt + Comp)</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($commissionsByCurrency as $commission)
            <tr>
                <td>
                    <strong>{{ $commission['currency']->code }}</strong>
                    <small>({{ $commission['currency']->name }})</small>
                </td>
                <td class="text-center">{{ $commission['count'] }}</td>
                <td class="text-right">{{ $commission['currency']->symbol }} {{ number_format($commission['total_amount'], 2) }}</td>
                <td class="text-right text-info">
                    <strong>{{ $commission['currency']->symbol }} {{ number_format($commission['company_commission'], 2) }}</strong>
                </td>
                <td class="text-right" style="font-size: 9px;">
                    {{ $commission['total_amount'] != 0 ? number_format((abs($commission['company_commission']) / abs($commission['total_amount'])) * 100, 2) : '0.00' }}%
                </td>
                <td class="text-right text-warning">
                    <strong>{{ $commission['currency']->symbol }} {{ number_format($commission['turkey_commission'], 2) }}</strong>
                </td>
                <td class="text-right" style="font-size: 9px;">
                    {{ $commission['total_amount'] != 0 ? number_format((abs($commission['turkey_commission']) / abs($commission['total_amount'])) * 100, 2) : '0.00' }}%
                </td>
                <td class="text-right">
                    <strong>{{ $commission['currency']->symbol }} {{ number_format($commission['total_amount'] + $commission['company_commission'], 2) }}</strong>
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Detailed Transactions -->
    <div class="section-title">Transaction Details</div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>From</th>
                <th>To</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Company Commission</th>
                <th class="text-right">Company Comm. (%)</th>
                <th class="text-right">Turkey Commission</th>
                <th class="text-right">Turkey Comm. (%)</th>
                <th class="text-right">Total Commission</th>
                <th>Type</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transactions as $transaction)
            <tr>
                <td>#{{ $transaction->id }}</td>
                <td>{{ $transaction->transaction_date->format('Y-m-d H:i') }}</td>
                <td>
                    {{ $transaction->fromAccount->user->name }}<br>
                    <small style="color: #666;">{{ $transaction->fromAccount->account_number }}</small>
                </td>
                <td>
                    {{ $transaction->toAccount->user->name }}<br>
                    <small style="color: #666;">{{ $transaction->toAccount->account_number }}</small>
                </td>
                <td class="text-right">
                    <strong>{{ $transaction->currency->symbol }} {{ number_format($transaction->amount, 2) }}</strong>
                </td>
                <td class="text-right text-info">
                    {{ $transaction->currency->symbol }} {{ number_format($transaction->company_commission, 2) }}
                </td>
                <td class="text-right" style="font-size: 9px;">
                    {{ $transaction->amount != 0 ? number_format((abs($transaction->company_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%
                </td>
                <td class="text-right text-warning">
                    {{ $transaction->currency->symbol }} {{ number_format($transaction->turkey_commission, 2) }}
                </td>
                <td class="text-right" style="font-size: 9px;">
                    {{ $transaction->amount != 0 ? number_format((abs($transaction->turkey_commission) / abs($transaction->amount)) * 100, 2) : '0.00' }}%
                </td>
                <td class="text-right">
                    {{ $transaction->currency->symbol }} {{ number_format($transaction->getTotalCommission(), 2) }}
                </td>
                <td>{{ ucfirst($transaction->type) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>


    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
