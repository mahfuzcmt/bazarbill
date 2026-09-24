<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.collection_report') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Hind Siliguri', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4f46e5;
        }
        .header h1 {
            font-size: 20px;
            color: #4f46e5;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .summary-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .summary-box {
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .summary-box .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
        }
        .summary-box .value {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }
        .summary-box .value.indigo { color: #4f46e5; }
        .summary-box .value.green { color: #059669; }
        .summary-box .value.red { color: #dc2626; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #f3f4f6;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        th.right, td.right {
            text-align: right;
        }
        th.center, td.center {
            text-align: center;
        }
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: 500;
        }
        .status-active { background-color: #d1fae5; color: #065f46; }
        .status-vacant { background-color: #fef3c7; color: #92400e; }
        .status-suspended { background-color: #fee2e2; color: #991b1b; }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
        tfoot td {
            background-color: #f3f4f6;
            font-weight: bold;
            border-top: 2px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('reports.collection_report') }}</h1>
        <p>{{ $periodLabel }}</p>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="summary-grid"><tr>
        <td class="summary-box">
            <div class="label">{{ __('Total Collection') }}</div>
            <div class="value green">৳ {{ number_format($grandTotal) }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('messages.nav.payments') }}</div>
            <div class="value">{{ $payments->count() }}</div>
        </td>
        @foreach($totals->take(2) as $data)
        <td class="summary-box">
            <div class="label">{{ $data['collector']->name ?? '-' }}</div>
            <div class="value indigo">৳ {{ number_format($data['total']) }}</div>
            <div style="font-size: 10px; color: #6b7280;">{{ $data['count'] }} {{ __('messages.nav.payments') }}</div>
        </td>
        @endforeach
    </tr></table>

    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Receipt') }}</th>
                <th>{{ __('Shop') }}</th>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('payments.method') }}</th>
                <th>{{ __('staff.roles.collector') }}</th>
                <th class="right">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                <td>{{ $payment->receipt_number }}</td>
                <td>{{ $payment->shop?->shop_number }}</td>
                <td>{{ $payment->invoice?->invoice_number }}</td>
                <td>{{ __('payments.method_' . $payment->payment_method) }}</td>
                <td>{{ $payment->collector?->name ?? '-' }}</td>
                <td class="right" style="color: #059669;">৳ {{ number_format($payment->amount) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="center">{{ __('No payments found for this period') }}</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"><strong>{{ __('Total') }}</strong></td>
                <td class="right" style="color: #059669;">৳ {{ number_format($grandTotal) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>DueTap - Market Rent Collection System | duetap.com</p>
    </div>
</body>
</html>
