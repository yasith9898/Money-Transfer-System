<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Account Balances Report - {{ $date }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4e73df; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #4e73df; font-size: 18px; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #e3e6f0; padding: 8px; text-align: left; }
        th { background-color: #f8f9fc; font-weight: bold; color: #4e73df; text-transform: uppercase; font-size: 9px; }
        .text-right { text-align: right; }
        .text-success { color: #1cc88a; }
        .text-danger { color: #e74a3b; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; }
        .summary-row { background-color: #f8f9fc; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Account Balances Report</h1>
        <p>Snapshot Date: {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
        <p>Filter: {{ ucfirst($filter_type) }} Balances</p>
    </div>

    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 13px; color: #4e73df; margin-bottom: 5px;">Currency Breakdown</h3>
        <table style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>Currency</th>
                    <th class="text-right">Total In (+)</th>
                    <th class="text-right">Total Out (-)</th>
                    <th class="text-right">Pos. Balances</th>
                    <th class="text-right">Neg. Balances</th>
                    <th class="text-right">Net Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($currencySummary as $summary)
                    <tr>
                        <td><strong>{{ $summary['currency']->code }}</strong></td>
                        <td class="text-right text-success">+{{ number_format($summary['total_in'], 2) }}</td>
                        <td class="text-right text-danger">-{{ number_format($summary['total_out'], 2) }}</td>
                        <td class="text-right text-success">{{ number_format($summary['positive_balance'], 2) }}</td>
                        <td class="text-right text-danger">{{ number_format($summary['negative_balance'], 2) }}</td>
                        <td class="text-right font-weight-bold {{ $summary['net_balance'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            {{ number_format($summary['net_balance'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h3 style="font-size: 13px; color: #4e73df; margin-bottom: 5px;">Account Details</h3>

    <table>
        <thead>
            <tr>
                <th>Account Name / Number</th>
                <th>Company</th>
                <th class="text-right">Total In (+)</th>
                <th class="text-right">Total Out (-)</th>
                <th class="text-right">Net Change</th>
                <th>Currency</th>
                <th class="text-right">Balance (at Date)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($reportData as $row)
                <tr>
                    <td>
                        <strong>{{ $row['account_name'] }}</strong><br>
                        <small>{{ $row['account_number'] }}</small>
                    </td>
                    <td>{{ $row['company_name'] }}</td>
                    <td class="text-right text-success">{{ $row['total_in'] > 0 ? number_format($row['total_in'], 2) : '-' }}</td>
                    <td class="text-right text-danger">{{ $row['total_out'] > 0 ? number_format($row['total_out'], 2) : '-' }}</td>
                    <td class="text-right {{ $row['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $row['net_change'] > 0 ? '+' : '' }}{{ number_format($row['net_change'], 2) }}
                    </td>
                    <td>{{ $row['currency']->code }}</td>
                    <td class="text-right font-weight-bold {{ $row['balance'] >= 0 ? '' : 'text-danger' }}">
                        {{ number_format($row['balance'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
