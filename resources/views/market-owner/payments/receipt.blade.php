<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('payments.receipt') }} - {{ $payment->receipt_number }}</title>
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
            background: #f9fafb;
        }

        .receipt {
            max-width: 400px;
            margin: 20px auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            opacity: 0.9;
        }

        .receipt-title {
            text-align: center;
            padding: 15px;
            border-bottom: 2px dashed #E5E7EB;
        }

        .receipt-title h2 {
            font-size: 16px;
            color: #4F46E5;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .receipt-title .receipt-number {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .content {
            padding: 20px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .row:last-child {
            border-bottom: none;
        }

        .row .label {
            color: #666;
            font-size: 11px;
        }

        .row .value {
            font-weight: 600;
            color: #333;
        }

        .amount-section {
            background: #F0FDF4;
            margin: 15px -20px;
            padding: 20px;
            text-align: center;
            border-top: 2px dashed #E5E7EB;
            border-bottom: 2px dashed #E5E7EB;
        }

        .amount-section .label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .amount-section .amount {
            font-size: 32px;
            font-weight: bold;
            color: #059669;
            margin-top: 5px;
        }

        .footer {
            padding: 20px;
            text-align: center;
            background: #F9FAFB;
            border-top: 1px solid #E5E7EB;
        }

        .footer p {
            font-size: 10px;
            color: #666;
            margin: 3px 0;
        }

        .thank-you {
            font-size: 14px;
            color: #4F46E5;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .qr-placeholder {
            width: 80px;
            height: 80px;
            background: #E5E7EB;
            margin: 10px auto;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: #666;
        }

        @media print {
            body {
                background: white;
            }
            .receipt {
                box-shadow: none;
                max-width: none;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="header">
            @if($payment->market->logo && file_exists(storage_path('app/public/' . $payment->market->logo)))
            <img src="{{ storage_path('app/public/' . $payment->market->logo) }}" alt="" style="height:56px;max-width:160px;margin-bottom:6px">
            @endif
            <h1>{{ $payment->market->getLocalizedName() }}</h1>
            <p>{{ $payment->market->address }}</p>
            <p>{{ __('messages.phone') }}: {{ $payment->market->phone }}</p>
        </div>

        <!-- Receipt Title -->
        <div class="receipt-title">
            <h2>{{ __('payments.payment_receipt') }}</h2>
            <div class="receipt-number">{{ $payment->receipt_number }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="row">
                <span class="label">{{ __('payments.date') }}</span>
                <span class="value">{{ $payment->payment_date->format('d M Y') }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('payments.time') }}</span>
                <span class="value">{{ $payment->created_at->format('h:i A') }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('shops.shop') }}</span>
                <span class="value">{{ $payment->shop->shop_number }}</span>
            </div>
            @if($payment->shop->shopOwner)
            <div class="row">
                <span class="label">{{ __('shops.owner') }}</span>
                <span class="value">{{ $payment->shop->shopOwner->getLocalizedName() }}</span>
            </div>
            @endif
            <div class="row">
                <span class="label">{{ __('invoices.invoice') }}</span>
                <span class="value">{{ $payment->invoice->invoice_number }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('invoices.billing_month') }}</span>
                <span class="value">{{ $payment->invoice->billing_month_formatted }}</span>
            </div>
            <div class="row">
                <span class="label">{{ __('payments.method') }}</span>
                <span class="value">{{ __('payments.method_' . $payment->payment_method) }}</span>
            </div>
            @if($payment->collector)
            <div class="row">
                <span class="label">{{ __('payments.collected_by') }}</span>
                <span class="value">{{ $payment->collector->getLocalizedName() }}</span>
            </div>
            @endif

            <!-- Amount -->
            <div class="amount-section">
                <div class="label">{{ __('payments.amount_paid') }}</div>
                <div class="amount">৳{{ number_format($payment->amount) }}</div>
            </div>

            @if($payment->notes)
            <div class="row">
                <span class="label">{{ __('payments.notes') }}</span>
                <span class="value">{{ $payment->notes }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="thank-you">{{ __('payments.thank_you') }}</p>
            <p>{{ __('payments.receipt_generated') }}: {{ now()->format('d M Y, h:i A') }}</p>
            <p>{{ __('payments.keep_receipt') }}</p>
        </div>
    </div>

    <!-- Print Button -->
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; background: #4F46E5; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px;">
            {{ __('messages.print') }}
        </button>
        <button onclick="window.close()" style="padding: 10px 30px; background: #6B7280; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-left: 10px;">
            {{ __('messages.close') }}
        </button>
    </div>
</body>
</html>
