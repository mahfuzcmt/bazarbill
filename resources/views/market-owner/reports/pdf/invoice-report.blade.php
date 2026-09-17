<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.invoice_report') }} - {{ \Carbon\Carbon::parse($summary['billing_month'])->format('F Y') }}</title>
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
        .header h2 {
            font-size: 14px;
            color: #6b7280;
            font-weight: normal;
        }
        .header p {
            color: #666;
            font-size: 11px;
            margin-top: 5px;
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
        .summary-box .sub {
            font-size: 9px;
            margin-top: 5px;
        }
        .indigo { color: #4f46e5; }
        .green { color: #059669; }
        .red { color: #dc2626; }
        .status-counts {
            margin-bottom: 15px;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            margin: 0 3px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 500;
        }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-partial { background-color: #fef3c7; color: #92400e; }
        .status-pending { background-color: #f3f4f6; color: #374151; }
        .status-overdue { background-color: #fee2e2; color: #991b1b; }
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
        tfoot td {
            background-color: #f3f4f6;
            font-weight: bold;
            border-top: 2px solid #e5e7eb;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('reports.invoice_report') }}</h1>
        <h2>{{ \Carbon\Carbon::parse($summary['billing_month'])->format('F Y') }}</h2>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="summary-grid"><tr>
        <td class="summary-box">
            <div class="label">{{ __('Total Invoices') }}</div>
            <div class="value">{{ $summary['total_invoices'] }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Amount') }}</div>
            <div class="value indigo">৳ {{ number_format($summary['total_amount']) }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Paid') }}</div>
            <div class="value green">৳ {{ number_format($summary['total_paid']) }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Due') }}</div>
            <div class="value red">৳ {{ number_format($summary['total_due']) }}</div>
        </td>
    </tr></table>

    <div class="status-counts">
        <span class="status-badge status-paid">{{ $summary['paid_count'] }} Paid</span>
        <span class="status-badge status-partial">{{ $summary['partial_count'] }} Partial</span>
        <span class="status-badge status-pending">{{ $summary['pending_count'] }} Pending</span>
        <span class="status-badge status-overdue">{{ $summary['overdue_count'] }} Overdue</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Invoice') }}</th>
                <th>{{ __('Shop') }}</th>
                <th>{{ __('Owner') }}</th>
                <th class="right">{{ __('Total') }}</th>
                <th class="right">{{ __('Paid') }}</th>
                <th class="right">{{ __('Due') }}</th>
                <th class="center">{{ __('Status') }}</th>
                <th class="center">{{ __('Due Date') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
            <tr>
                <td><strong>{{ $invoice->invoice_number }}</strong></td>
                <td>{{ $invoice->shop->shop_number }}</td>
                <td>{{ $invoice->shop->shopOwner?->name ?? '-' }}</td>
                <td class="right">৳ {{ number_format($invoice->total_amount) }}</td>
                <td class="right green">৳ {{ number_format($invoice->paid_amount) }}</td>
                <td class="right red">৳ {{ number_format($invoice->due_amount) }}</td>
                <td class="center">
                    <span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
                </td>
                <td class="center">{{ $invoice->due_date->format('d M Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #6b7280;">{{ __('No invoices found for this month') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ __('Total') }} ({{ $summary['total_invoices'] }} {{ __('messages.nav.invoices') }})</td>
                <td class="right">৳ {{ number_format($summary['total_amount']) }}</td>
                <td class="right green">৳ {{ number_format($summary['total_paid']) }}</td>
                <td class="right red">৳ {{ number_format($summary['total_due']) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>BazarBill - Market Rent Collection System</p>
    </div>
</body>
</html>
