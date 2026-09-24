<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('invoices.invoice') }} - {{ $invoice->invoice_number }}</title>
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Hind Siliguri', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            padding: 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4F46E5;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            color: #4F46E5;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
        }

        .invoice-info {
            width: 100%;
            margin-bottom: 30px;
        }

        .invoice-info .left,
        .invoice-info .right {
            width: 50%;
            vertical-align: top;
        }

        .invoice-info .right {
            text-align: right;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }

        .info-block {
            margin-bottom: 20px;
        }

        .info-block h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .info-block p {
            font-size: 12px;
            margin: 2px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-pending { background: #FEF3C7; color: #92400E; }
        .status-partial { background: #DBEAFE; color: #1E40AF; }
        .status-paid { background: #D1FAE5; color: #065F46; }
        .status-overdue { background: #FEE2E2; color: #991B1B; }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.items th {
            background: #F3F4F6;
            padding: 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 2px solid #E5E7EB;
        }

        table.items td {
            padding: 12px;
            border-bottom: 1px solid #E5E7EB;
        }

        table.items .amount {
            text-align: right;
        }

        .totals {
            width: 300px;
            margin-left: auto;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px 12px;
        }

        .totals tr:last-child td {
            border-top: 2px solid #333;
            font-size: 16px;
            font-weight: bold;
        }

        .totals .label {
            text-align: left;
        }

        .totals .value {
            text-align: right;
        }

        .total-due {
            color: #DC2626;
        }

        .total-paid {
            color: #059669;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #666;
            font-size: 10px;
        }

        .note-box {
            background: #F9FAFB;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .note-box h4 {
            font-size: 11px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $invoice->market->getLocalizedName() }}</h1>
            <p>{{ $invoice->market->address }}</p>
            <p>{{ __('messages.phone') }}: {{ $invoice->market->phone }} | {{ __('messages.email') }}: {{ $invoice->market->email }}</p>
        </div>

        <!-- Invoice Info -->
        <table class="invoice-info"><tr>
            <td class="left">
                <div class="invoice-title">{{ __('invoices.invoice') }}</div>
                <div class="info-block">
                    <h3>{{ __('invoices.billed_to') }}</h3>
                    <p><strong>{{ $invoice->shop->shop_number }}</strong></p>
                    @if($invoice->shop->shopOwner)
                    <p>{{ $invoice->shop->shopOwner->getLocalizedName() }}</p>
                    <p>{{ $invoice->shop->shopOwner->phone }}</p>
                    @endif
                    <p>{{ $invoice->shop->floor }}</p>
                </div>
            </td>
            <td class="right">
                <div class="info-block">
                    <h3>{{ __('invoices.invoice_number') }}</h3>
                    <p><strong>{{ $invoice->invoice_number }}</strong></p>
                </div>
                <div class="info-block">
                    <h3>{{ __('invoices.billing_month') }}</h3>
                    <p>{{ $invoice->billing_month_formatted }}</p>
                </div>
                <div class="info-block">
                    <h3>{{ __('invoices.issue_date') }}</h3>
                    <p>{{ $invoice->generated_at->format('d M Y') }}</p>
                </div>
                <div class="info-block">
                    <h3>{{ __('invoices.due_date') }}</h3>
                    <p>{{ $invoice->due_date->format('d M Y') }}</p>
                </div>
                <div class="info-block">
                    <h3>{{ __('invoices.status') }}</h3>
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ __('invoices.status_' . $invoice->status) }}
                    </span>
                </div>
            </td>
        </tr></table>

        <!-- Items Table -->
        <table class="items">
            <thead>
                <tr>
                    <th>{{ __('invoices.description') }}</th>
                    <th class="amount">{{ __('invoices.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('invoices.monthly_rent') }} - {{ $invoice->billing_month_formatted }}</td>
                    <td class="amount">৳{{ number_format($invoice->rent_amount) }}</td>
                </tr>
                @if($invoice->previous_due > 0)
                <tr>
                    <td>{{ __('invoices.previous_due') }}</td>
                    <td class="amount">৳{{ number_format($invoice->previous_due) }}</td>
                </tr>
                @endif
                @if($invoice->late_fee > 0)
                <tr>
                    <td>{{ __('invoices.late_fee') }}</td>
                    <td class="amount">৳{{ number_format($invoice->late_fee) }}</td>
                </tr>
                @endif
                @if($invoice->discount > 0)
                <tr>
                    <td>{{ __('invoices.discount') }}</td>
                    <td class="amount" style="color: #059669;">-৳{{ number_format($invoice->discount) }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <table>
                <tr>
                    <td class="label">{{ __('invoices.subtotal') }}</td>
                    <td class="value">৳{{ number_format($invoice->rent_amount + $invoice->previous_due + $invoice->late_fee) }}</td>
                </tr>
                @if($invoice->discount > 0)
                <tr>
                    <td class="label">{{ __('invoices.discount') }}</td>
                    <td class="value" style="color: #059669;">-৳{{ number_format($invoice->discount) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label"><strong>{{ __('invoices.total_amount') }}</strong></td>
                    <td class="value"><strong>৳{{ number_format($invoice->total_amount) }}</strong></td>
                </tr>
                @if($invoice->paid_amount > 0)
                <tr>
                    <td class="label total-paid">{{ __('invoices.paid_amount') }}</td>
                    <td class="value total-paid">-৳{{ number_format($invoice->paid_amount) }}</td>
                </tr>
                @endif
                <tr>
                    <td class="label {{ $invoice->due_amount > 0 ? 'total-due' : 'total-paid' }}">{{ __('invoices.due_amount') }}</td>
                    <td class="value {{ $invoice->due_amount > 0 ? 'total-due' : 'total-paid' }}">৳{{ number_format($invoice->due_amount) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="note-box">
            <h4>{{ __('invoices.notes') }}</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
        @endif

        <!-- Payment Instructions -->
        <div class="note-box">
            <h4>{{ __('invoices.payment_instructions') }}</h4>
            <p>{{ __('invoices.payment_instructions_text') }}</p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ __('invoices.thank_you') }}</p>
            <p>{{ __('invoices.generated_by') }} DueTap (duetap.com) | {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>
</body>
</html>
