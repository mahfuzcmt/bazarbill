<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.monthly_summary') }} - {{ \Carbon\Carbon::parse($summary['month'])->format('F Y') }}</title>
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
            font-size: 10px;
            margin-top: 3px;
        }
        .indigo { color: #4f46e5; }
        .green { color: #059669; }
        .red { color: #dc2626; }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .breakdown-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .breakdown-box {
            width: 25%;
            padding: 8px;
            border-left: 3px solid;
            background-color: #f9fafb;
            margin-right: 10px;
        }
        .breakdown-box.indigo-border { border-color: #4f46e5; }
        .breakdown-box.orange-border { border-color: #f97316; }
        .breakdown-box.red-border { border-color: #dc2626; }
        .breakdown-box.green-border { border-color: #059669; }
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
        td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
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
        <h1>{{ __('reports.monthly_summary') }}</h1>
        <h2>{{ \Carbon\Carbon::parse($summary['month'])->format('F Y') }}</h2>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="summary-grid"><tr>
            <td class="summary-box">
                <div class="label">{{ __('Total Invoices') }}</div>
                <div class="value">{{ $summary['total_invoices'] }}</div>
                <div class="sub green">{{ $summary['paid_count'] }} {{ __('Paid') }} · {{ $summary['partial_count'] }} {{ __('Partial') }}</div>
            </td>
            <td class="summary-box">
                <div class="label">{{ __('Total Billed') }}</div>
                <div class="value indigo">৳ {{ number_format($summary['total_billed']) }}</div>
            </td>
            <td class="summary-box">
                <div class="label">{{ __('Total Collected') }}</div>
                <div class="value green">৳ {{ number_format($summary['total_collected']) }}</div>
                <div class="sub">{{ $summary['collection_rate'] }}% {{ __('Collection Rate') }}</div>
            </td>
            <td class="summary-box">
                <div class="label">{{ __('Total Due') }}</div>
                <div class="value red">৳ {{ number_format($summary['total_due']) }}</div>
                <div class="sub red">{{ $summary['overdue_count'] }} {{ __('Overdue') }}</div>
            </td>
        
    </tr></table>

    <div class="section-title">{{ __('Amount Breakdown') }}</div>
    <table class="breakdown-grid"><tr>
        <td class="breakdown-box indigo-border">
            <div class="label">{{ __('Rent Amount') }}</div>
            <div class="value">৳ {{ number_format($summary['total_rent']) }}</div>
        </td>
        <td class="breakdown-box orange-border">
            <div class="label">{{ __('Previous Due') }}</div>
            <div class="value">৳ {{ number_format($summary['total_previous_due']) }}</div>
        </td>
        <td class="breakdown-box red-border">
            <div class="label">{{ __('Late Fees') }}</div>
            <div class="value">৳ {{ number_format($summary['total_late_fee']) }}</div>
        </td>
        <td class="breakdown-box green-border">
            <div class="label">{{ __('Discounts') }}</div>
            <div class="value">৳ {{ number_format($summary['total_discount']) }}</div>
        </td>
    </tr></table>

    <div class="section-title">{{ __('Daily Collection Breakdown') }}</div>
    <table>
        <thead>
            <tr>
                <th>{{ __('Date') }}</th>
                <th class="right">{{ __('Transactions') }}</th>
                <th class="right">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyCollection as $day)
            <tr>
                <td>{{ $day['date']->format('d M Y (l)') }}</td>
                <td class="right">{{ $day['count'] }}</td>
                <td class="right green">৳ {{ number_format($day['amount']) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center; color: #6b7280;">{{ __('No collections this month') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>{{ __('Total') }}</td>
                <td class="right">{{ $dailyCollection->sum('count') }}</td>
                <td class="right green">৳ {{ number_format($summary['total_collected']) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>BazarBill - Market Rent Collection System</p>
    </div>
</body>
</html>
