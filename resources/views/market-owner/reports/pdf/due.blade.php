<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.due_report') }}</title>
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
        <h1>{{ __('reports.due_report') }}</h1>
        <p>{{ __(['all' => 'reports.all_dues', 'overdue' => 'reports.overdue_only', 'pending' => 'reports.pending_only'][$filter] ?? 'reports.all_dues') }}</p>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="summary-grid"><tr>
        <td class="summary-box">
            <div class="label">{{ __('Total Outstanding Due') }}</div>
            <div class="value red">৳ {{ number_format($grandTotal) }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Shops with Due') }}</div>
            <div class="value">{{ $shops->count() }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Unpaid Invoices') }}</div>
            <div class="value indigo">{{ $shops->sum(fn ($shop) => $shop->invoices->count()) }}</div>
        </td>
    </tr></table>

    <table>
        <thead>
            <tr>
                <th>{{ __('Shop') }}</th>
                <th>{{ __('Owner') }}</th>
                <th>{{ __('messages.phone') }}</th>
                <th class="center">{{ __('Unpaid Invoices') }}</th>
                <th>{{ __('Oldest Due Date') }}</th>
                <th class="right">{{ __('Total Due') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shops as $shop)
            <tr>
                <td><strong>{{ $shop->shop_number }}</strong><br><span style="color: #6b7280;">{{ $shop->floor ?? '-' }}</span></td>
                <td>{{ $shop->shopOwner?->getLocalizedName() ?? '-' }}</td>
                <td>{{ $shop->shopOwner?->phone ?? '-' }}</td>
                <td class="center">{{ $shop->invoices->count() }}</td>
                <td>{{ optional($shop->invoices->min('due_date'))->format('d M Y') }}</td>
                <td class="right" style="color: #dc2626;">৳ {{ number_format($shop->total_due) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="center">{{ __('No shops with outstanding dues') }}</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"><strong>{{ __('Total') }}</strong></td>
                <td class="right" style="color: #dc2626;">৳ {{ number_format($grandTotal) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>BazarBill - Market Rent Collection System</p>
    </div>
</body>
</html>
