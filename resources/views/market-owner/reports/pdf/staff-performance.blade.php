<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('reports.staff_performance') }}</title>
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
            width: 33.33%;
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
        .indigo { color: #4f46e5; }
        .green { color: #059669; }
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
        tr.top-performer {
            background-color: #fef9c3 !important;
        }
        .rank {
            font-weight: bold;
            font-size: 14px;
        }
        .rank-1 { color: #ca8a04; }
        .rank-2 { color: #6b7280; }
        .rank-3 { color: #b45309; }
        .medal {
            font-size: 16px;
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
        <h1>{{ __('reports.staff_performance') }}</h1>
        <h2>{{ \Carbon\Carbon::parse($summary['from_date'])->format('d M') }} - {{ \Carbon\Carbon::parse($summary['to_date'])->format('d M Y') }}</h2>
        <p>{{ __('Generated on') }}: {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="summary-grid"><tr>
        <td class="summary-box">
            <div class="label">{{ __('Total Collectors') }}</div>
            <div class="value">{{ $summary['total_collectors'] }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Collections') }}</div>
            <div class="value indigo">{{ $summary['total_collections'] }}</div>
        </td>
        <td class="summary-box">
            <div class="label">{{ __('Total Amount') }}</div>
            <div class="value green">৳ {{ number_format($summary['total_amount']) }}</div>
        </td>
    </tr></table>

    <table>
        <thead>
            <tr>
                <th class="center">{{ __('Rank') }}</th>
                <th>{{ __('staff.roles.collector') }}</th>
                <th class="right">{{ __('Assigned Shops') }}</th>
                <th class="right">{{ __('Collections') }}</th>
                <th class="right">{{ __('Total Amount') }}</th>
                <th class="right">{{ __('Avg/Collection') }}</th>
                <th class="right">{{ __('Daily Avg') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $rank = 1; @endphp
            @forelse($performance as $p)
            <tr class="{{ $rank <= 3 ? 'top-performer' : '' }}">
                <td class="center">
                    @if($rank === 1)
                        <span class="medal">🥇</span>
                    @elseif($rank === 2)
                        <span class="medal">🥈</span>
                    @elseif($rank === 3)
                        <span class="medal">🥉</span>
                    @else
                        <span class="rank">#{{ $rank }}</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $p['collector']->name }}</strong>
                    @if($p['collector']->phone)
                        <br><span style="color: #6b7280; font-size: 10px;">{{ $p['collector']->phone }}</span>
                    @endif
                </td>
                <td class="right">{{ $p['assigned_shops'] }}</td>
                <td class="right">{{ $p['total_collections'] }}</td>
                <td class="right green">৳ {{ number_format($p['total_amount']) }}</td>
                <td class="right">৳ {{ number_format($p['avg_per_collection']) }}</td>
                <td class="right">৳ {{ number_format($p['daily_avg']) }}</td>
            </tr>
            @php $rank++; @endphp
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #6b7280;">{{ __('No collectors found') }}</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">{{ __('Total') }}</td>
                <td class="right">{{ $summary['total_collections'] }}</td>
                <td class="right green">৳ {{ number_format($summary['total_amount']) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>DueTap - Market Rent Collection System | duetap.com</p>
    </div>
</body>
</html>
