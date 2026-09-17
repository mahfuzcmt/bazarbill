<x-app-layout>
    <x-slot name="header">{{ __('payments.payment_details') }}</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="glass-card overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-500 to-green-600">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="text-white">
                        <p class="text-sm opacity-80">{{ __('Receipt Number') }}</p>
                        <p class="text-2xl font-bold font-mono">{{ $payment->receipt_number }}</p>
                    </div>
                    <div class="text-right text-white">
                        <p class="text-sm opacity-80">{{ __('Amount Paid') }}</p>
                        <p class="text-3xl font-bold">৳ {{ number_format($payment->amount) }}</p>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="px-6 py-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Payment Date') }}</p>
                        <p class="font-medium text-gray-900">{{ $payment->payment_date->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Payment Method') }}</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($payment->payment_method) }}</p>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Shop') }}</p>
                            <p class="font-medium text-gray-900">{{ $payment->shop->shop_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Shop Owner') }}</p>
                            <p class="font-medium text-gray-900">{{ $payment->shop->shopOwner?->name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Invoice') }}</p>
                            <p class="font-medium text-indigo-600">{{ $payment->invoice->invoice_number }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">{{ __('Billing Month') }}</p>
                            <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($payment->invoice->billing_month)->format('F Y') }}</p>
                        </div>
                    </div>
                </div>

                @if($payment->transaction_id)
                <div class="pt-4 border-t">
                    <p class="text-sm text-gray-500">{{ __('Transaction ID') }}</p>
                    <p class="font-medium text-gray-900 font-mono">{{ $payment->transaction_id }}</p>
                </div>
                @endif

                @if($payment->notes)
                <div class="pt-4 border-t">
                    <p class="text-sm text-gray-500">{{ __('Notes') }}</p>
                    <p class="text-gray-700">{{ $payment->notes }}</p>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <p class="text-sm text-gray-500">
                    {{ __('Recorded on') }} {{ $payment->created_at->format('d M Y, h:i A') }}
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('collector.payments.index') }}" class="text-indigo-600 hover:text-indigo-800">
                ← {{ __('Back to Payments') }}
            </a>
            <a href="{{ route('collector.payments.receipt', $payment) }}"
               class="px-4 py-2 btn-primary" target="_blank">
                {{ __('Print Receipt') }}
            </a>
        </div>
    </div>
</x-app-layout>
