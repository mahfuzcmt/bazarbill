<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('Receipt') }} - {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }
        .receipt {
            max-width: 400px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            color: #666;
        }
        .receipt-number {
            text-align: center;
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .receipt-number span {
            font-size: 16px;
            font-weight: bold;
            font-family: monospace;
        }
        .details {
            margin-bottom: 15px;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .details-row:last-child {
            border-bottom: none;
        }
        .details-label {
            color: #666;
        }
        .details-value {
            font-weight: 500;
            text-align: right;
        }
        .amount-section {
            background: #4f46e5;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 15px 0;
        }
        .amount-section .label {
            font-size: 11px;
            opacity: 0.9;
        }
        .amount-section .amount {
            font-size: 28px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            border-top: 2px dashed #333;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 10px;
            color: #666;
        }
        .footer p {
            margin: 3px 0;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
                max-width: 100%;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer;">
            {{ __('Print Receipt') }}
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">
            {{ __('Close') }}
        </button>
    </div>

    <div class="receipt">
        <div class="header">
            <h1>{{ $payment->shop->market?->name ?? 'DueTap' }}</h1>
            <p>{{ __('Payment Receipt') }}</p>
        </div>

        <div class="receipt-number">
            <p>{{ __('Receipt No') }}</p>
            <span>{{ $payment->receipt_number }}</span>
        </div>

        <div class="details">
            <div class="details-row">
                <span class="details-label">{{ __('Date') }}</span>
                <span class="details-value">{{ $payment->payment_date->format('d M Y') }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Shop') }}</span>
                <span class="details-value">{{ $payment->shop->shop_number }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Shop Owner') }}</span>
                <span class="details-value">{{ $payment->shop->shopOwner?->name ?? '-' }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Invoice') }}</span>
                <span class="details-value">{{ $payment->invoice->invoice_number }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Billing Month') }}</span>
                <span class="details-value">{{ \Carbon\Carbon::parse($payment->invoice->billing_month)->format('F Y') }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Payment Method') }}</span>
                <span class="details-value">{{ ucfirst($payment->payment_method) }}</span>
            </div>
            @if($payment->transaction_id)
            <div class="details-row">
                <span class="details-label">{{ __('Transaction ID') }}</span>
                <span class="details-value" style="font-family: monospace;">{{ $payment->transaction_id }}</span>
            </div>
            @endif
        </div>

        <div class="amount-section">
            <p class="label">{{ __('Amount Paid') }}</p>
            <p class="amount">৳ {{ number_format($payment->amount) }}</p>
        </div>

        <div class="details">
            <div class="details-row">
                <span class="details-label">{{ __('Invoice Total') }}</span>
                <span class="details-value">৳ {{ number_format($payment->invoice->total_amount) }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Total Paid') }}</span>
                <span class="details-value">৳ {{ number_format($payment->invoice->paid_amount) }}</span>
            </div>
            <div class="details-row">
                <span class="details-label">{{ __('Remaining Due') }}</span>
                <span class="details-value" style="color: {{ $payment->invoice->due_amount > 0 ? '#dc2626' : '#059669' }};">
                    ৳ {{ number_format($payment->invoice->due_amount) }}
                </span>
            </div>
        </div>

        <div class="footer">
            <p>{{ __('Collected by') }}: {{ $payment->collector?->name ?? '-' }}</p>
            <p>{{ __('Printed on') }}: {{ now()->format('d M Y, h:i A') }}</p>
            <p style="margin-top: 10px;">{{ __('Thank you for your payment!') }}</p>
        </div>
    </div>
</body>
</html>
