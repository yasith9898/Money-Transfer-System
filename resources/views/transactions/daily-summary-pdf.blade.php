<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Summary Report</title>
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
        .text-success { color: #28a745; }
        .text-warning { color: #ffc107; }
        .badge { padding: 2px 4px; border-radius: 3px; font-size: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Summary Report</h1>
        <p>Date: {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}</p>
        <p style="font-size: 9px;">Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <h2>1. Currency Breakdown</h2>
    <table>
        <thead>
            <tr>
                <th>Currency</th>
                <th>Count</th>
                <th class="text-right">History Amount</th>
                <th class="text-right">Company Commission</th>
                <th class="text-right">Company Comm. (%)</th>
                <th class="text-right">Turkey Commission</th>
                <th class="text-right">Turkey Comm. (%)</th>
                <th class="text-right">First Total</th>
                <th class="text-right text-success">Exchange Result</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($currencyBreakdown as $breakdown)
                <tr>
                    <td><strong>{{ $breakdown['currency']->code }}</strong></td>
                    <td>{{ $breakdown['count'] }}</td>
                    <td class="text-right font-bold text-primary">{{ number_format($breakdown['history_amount'], 2) }}</td>
                    <td class="text-right text-info">{{ number_format($breakdown['company_commission'], 2) }}</td>
                    <td class="text-right" style="font-size: 8px;">{{ $breakdown['history_amount'] != 0 ? number_format((abs($breakdown['company_commission']) / abs($breakdown['history_amount'])) * 100, 2) : '0.00' }}%</td>
                    <td class="text-right text-success">{{ number_format($breakdown['turkey_commission'], 2) }}</td>
                    <td class="text-right" style="font-size: 8px;">{{ $breakdown['history_amount'] != 0 ? number_format((abs($breakdown['turkey_commission']) / abs($breakdown['history_amount'])) * 100, 2) : '0.00' }}%</td>
                    <td class="text-right font-bold">{{ number_format($breakdown['history_amount'] + $breakdown['company_commission'], 2) }}</td>
                    <td class="text-right text-success">${{ number_format($breakdown['currency']->convertAmount($breakdown['history_amount'], 'USD'), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="page-break-before: always;"></div>

    <h2>2. Transfers (Daily)</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>Transaction From</th>
                <th>To</th>
                <th>Currency</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Comp Comm.</th>
                <th class="text-right">Turk Comm.</th>
                <th class="text-right">First Total</th>
                <th class="text-right text-success">Exchange Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($historyTransactions as $index => $t)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $t->transaction_date->format('H:i') }}</td>
                    <td>
                        {{ $t->fromAccount->user->name ?? 'System' }}<br>
                        @if(!empty($t->fromAccount->user->company_name))
                            <span style="font-size: 8px; color: #666;">{{ $t->fromAccount->user->company_name }}</span><br>
                        @endif
                        {{ $t->fromAccount->account_number }}
                        <br><span style="font-size: 8px; color: #666;">({{ ucfirst($t->type) }})</span>
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
                    <td class="text-right text-info">{{ number_format($t->company_commission, 2) }}</td>
                    <td class="text-right text-success">{{ number_format($t->turkey_commission, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format($t->amount + $t->company_commission, 2) }}</td>
                    <td class="text-right text-success">
                        @if($t->exchange_rate)
                            <span style="font-size: 8px; color: #666;">[R:{{ $t->exchange_rate }}]</span><br>
                            ${{ number_format($t->exchange_result, 2) }}
                        @else
                            ${{ number_format($t->currency->convertAmount($t->amount + $t->company_commission, 'USD'), 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No transfers recorded on this day.</td>
                </tr>
            @endforelse
        </tbody>
        @if($historyTransactions->count() > 0)
        <tfoot class="bg-light font-bold">
            @foreach ($currencyBreakdown as $breakdown)
                @if($breakdown['history_amount'] != 0 || $breakdown['company_commission'] != 0 || $breakdown['turkey_commission'] != 0)
                <tr>
                    <td colspan="5" class="text-right">Totals ({{ $breakdown['currency']->code }}):</td>
                    <td class="text-right text-primary">{{ number_format($breakdown['history_amount'], 2) }}</td>
                    <td class="text-right text-info">{{ number_format($breakdown['company_commission'], 2) }}</td>
                    <td class="text-right text-success">{{ number_format($breakdown['turkey_commission'], 2) }}</td>
                    <td class="text-right">{{ number_format($breakdown['history_amount'] + $breakdown['company_commission'], 2) }}</td>
                    <td class="text-right text-success">${{ number_format($breakdown['currency']->convertAmount($breakdown['history_amount'], 'USD'), 2) }}</td>
                </tr>
                @endif
            @endforeach
        </tfoot>
        @endif
    </table>

    <div style="page-break-before: auto;"></div>

    <h2>3. Balance Adjustments</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Time</th>
                <th>Transaction From</th>
                <th>To</th>
                <th>Currency</th>
                <th class="text-right">Amount</th>
                <th>Notes</th>
                <th class="text-right text-success">Exchange Result</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($adjustmentTransactions as $index => $t)
                <tr>
                    <td>{{ $index + 1 }}</td>
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
                    <td class="text-right font-bold text-warning">{{ number_format($t->amount, 2) }}</td>
                    <td style="font-size: 8px;">{{ $t->notes }}</td>
                    <td class="text-right font-bold text-success">
                        @if($t->exchange_rate)
                            <span style="font-size: 8px; color: #666;">[R:{{ $t->exchange_rate }}]</span><br>
                            ${{ number_format($t->exchange_result, 2) }}
                        @else
                            ${{ number_format($t->currency->convertAmount($t->amount, 'USD'), 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No adjustments recorded on this day.</td>
                </tr>
            @endforelse
        </tbody>
        @if($adjustmentTransactions->count() > 0)
        <tfoot class="bg-light font-bold">
            @foreach ($currencyBreakdown as $breakdown)
                @php
                    $adjAmount = $adjustmentTransactions->where('currency_id', $breakdown['currency']->id)->sum('amount');
                @endphp
                @if($adjAmount != 0)
                <tr>
                    <td colspan="5" class="text-right">Totals ({{ $breakdown['currency']->code }}):</td>
                    <td class="text-right text-warning">{{ number_format($adjAmount, 2) }}</td>
                    <td></td>
                    <td class="text-right text-success font-bold">${{ number_format($breakdown['currency']->convertAmount($adjAmount, 'USD'), 2) }}</td>
                </tr>
                @endif
            @endforeach
        </tfoot>
        @endif
    </table>

    <div class="footer">
        <p>Software Powered by BrainTech Projects - Generated on {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
