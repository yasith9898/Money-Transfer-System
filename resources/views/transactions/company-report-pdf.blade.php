<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Company Transaction Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #efefef; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #333; font-size: 18px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        h2 { font-size: 13px; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 20px; background-color: #f8f9fa; padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 8px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .bg-light { background-color: #f8f9fa; }
        .text-primary { color: #007bff; }
        .text-info { color: #17a2b8; }
        .text-danger { color: #e74a3b; }
        .text-success { color: #28a745; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 8px; font-weight: bold; background: #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Company Transaction Report</h1>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</p>
        <p>Company: {{ $companyName === 'all' ? 'All Companies' : $companyName }}</p>
    </div>

    <h2>1. Currency Breakdown (Totals)</h2>
    <table>
        <thead>
            <tr>
                <th>Currency</th>
                <th>Record Count</th>
                <th class="text-right">Total Amount</th>
                <th class="text-right">Total Commission</th>
                <th class="text-right">First Total (Sum)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($currencyBreakdown as $breakdown)
                <tr>
                    <td><strong>{{ $breakdown['currency']->code }}</strong> ({{ $breakdown['currency']->name }})</td>
                    <td>{{ $breakdown['count'] }}</td>
                    <td class="text-right">{{ number_format($breakdown['total_amount'], 2) }}</td>
                    <td class="text-right">{{ number_format($breakdown['total_comp_comm'], 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($breakdown['total_first'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-before: auto;"></div>

    <h2>2. Transaction Details</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Transaction From</th>
                <th>To</th>
                <th>Currency</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Comm</th>
                <th class="text-right">First Total</th>
                <th class="text-right text-success">Exchange Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
                @php
                    $comm = ($t->fromAccount->wallet_type !== 'main_company') ? $t->company_commission : 0;
                    $firstTotal = $t->amount + $comm;
                @endphp
                <tr>
                    <td>{{ $t->transaction_date->format('Y-m-d H:i') }}</td>
                    <td>
                        {{ $t->fromAccount->user->name ?? 'System' }}<br>
                        @if(!empty($t->fromAccount->user->company_name))
                            <span style="font-size: 8px; color: #666;">{{ $t->fromAccount->user->company_name }}</span><br>
                        @endif
                        {{ $t->fromAccount->account_number ?? '-' }}
                        <br><span style="font-size: 8px; color: #666;">({{ ucfirst($t->type) }})</span>
                    </td>
                    <td>
                        {{ $t->toAccount->user->name ?? 'System' }}<br>
                        @if(!empty($t->toAccount->user->company_name))
                            <span style="font-size: 8px; color: #666;">{{ $t->toAccount->user->company_name }}</span><br>
                        @endif
                        {{ $t->toAccount->account_number ?? '-' }}
                    </td>
                    <td>{{ $t->currency->code }}</td>
                    <td class="text-right">{{ number_format($t->amount, 2) }}</td>
                    <td class="text-right text-danger">{{ $comm > 0 ? '+' . number_format($comm, 2) : '-' }}</td>
                    <td class="text-right font-bold">{{ number_format($firstTotal, 2) }}</td>
                    <td class="text-right text-success">
                        @if($t->exchange_rate)
                            <span style="font-size: 8px; color: #666;">[R:{{ $t->exchange_rate }}]</span><br>
                            ${{ number_format($t->exchange_result, 2) }}
                        @else
                            ${{ number_format($t->currency->convertAmount($firstTotal, 'USD'), 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No transactions recorded.</td>
                </tr>
            @endforelse
        </tbody>
        @if($transactions->count() > 0)
        <tfoot class="bg-light font-bold">
            @foreach ($currencyBreakdown as $breakdown)
                <tr>
                    <td colspan="4" class="text-right">Totals ({{ $breakdown['currency']->code }}):</td>
                    <td class="text-right text-primary">{{ number_format($breakdown['total_amount'], 2) }}</td>
                    <td class="text-right text-danger">{{ number_format($breakdown['total_comp_comm'], 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($breakdown['total_first'], 2) }}</td>
                    <td class="text-right font-bold text-success">${{ number_format($breakdown['currency']->convertAmount($breakdown['total_first'], 'USD'), 2) }}</td>
                </tr>
            @endforeach
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Software Powered by BrainTech Projects - Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
