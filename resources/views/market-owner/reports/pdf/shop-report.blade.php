<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.shop_report') }}</title>
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
        <h1>{{ __('reports.shop_report') }}</h1>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
        @if($status !== 'all')
            <p>{{ __('Filter') }}: {{ ucfirst($status) }} {{ __('shops only') }}</p>
        @endif
    </div>

    <table class="summary-grid"><tr>
        <td class="summary-box">
            <div class="label">{{ __('Total Shops') }}</div>
            <div class="value">{{ $summary['total_shops'] }}</div>
            <div style="font-size: 10px; color: #059669;">{{ $summary['active_shops'] }} Active</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Rent') }}</div>
            <div class="value indigo">৳ {{ number_format($summary['total_rent']) }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Collected') }}</div>
            <div class="value green">৳ {{ number_format($summary['total_collected']) }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Due') }}</div>
            <div class="value red">৳ {{ number_format($summary['total_due']) }}</div>
        </td>
    </tr></table>

    <table>
        <thead>
            <tr>
                <th>{{ __('Shop') }}</th>
                <th>{{ __('Owner') }}</th>
                <th>{{ __('staff.roles.collector') }}</th>
                <th class="right">{{ __('Rent') }}</th>
                <th class="right">{{ __('Billed') }}</th>
                <th class="right">{{ __('Paid') }}</th>
                <th class="right">{{ __('Due') }}</th>
                <th class="center">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($shops as $shop)
            <tr>
                <td>
                    <strong>{{ $shop->shop_number }}</strong>
                    @if($shop->floor)<br><span style="color: #6b7280;">{{ $shop->floor }}</span>@endif
                </td>
                <td>{{ $shop->shopOwner?->name ?? '-' }}</td>
                <td>{{ $shop->collector?->name ?? '-' }}</td>
                <td class="right">৳ {{ number_format($shop->rent_amount) }}</td>
                <td class="right">৳ {{ number_format($shop->invoices_sum_total_amount ?? 0) }}</td>
                <td class="right" style="color: #059669;">৳ {{ number_format($shop->invoices_sum_paid_amount ?? 0) }}</td>
                <td class="right" style="color: #dc2626;">৳ {{ number_format($shop->invoices_sum_due_amount ?? 0) }}</td>
                <td class="center">
                    <span class="status status-{{ $shop->status }}">{{ ucfirst($shop->status) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>{{ __('Total') }}</strong></td>
                <td class="right">৳ {{ number_format($summary['total_rent']) }}</td>
                <td class="right">৳ {{ number_format($summary['total_billed']) }}</td>
                <td class="right" style="color: #059669;">৳ {{ number_format($summary['total_collected']) }}</td>
                <td class="right" style="color: #dc2626;">৳ {{ number_format($summary['total_due']) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>BazarBill - Market Rent Collection System</p>
    </div>
</body>
</html>
