<x-app-layout>
    <x-slot name="header">{{ __('payments.payment_details') }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
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
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Payment Info') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Payment Date') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $payment->payment_date->format('d M Y') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Payment Method') }}</dt>
                                <dd class="font-medium text-gray-900">{{ ucfirst($payment->payment_method) }}</dd>
                            </div>
                            @if($payment->transaction_id)
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Transaction ID') }}</dt>
                                <dd class="font-medium text-gray-900 font-mono">{{ $payment->transaction_id }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Collected By') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $payment->collector?->name ?? '-' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('Invoice Info') }}</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Invoice') }}</dt>
                                <dd>
                                    <a href="{{ route('market-owner.invoices.show', $payment->invoice) }}"
                                       class="font-medium text-indigo-600 hover:text-indigo-800">
                                        {{ $payment->invoice->invoice_number }}
                                    </a>
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Shop') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $payment->shop->shop_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Shop Owner') }}</dt>
                                <dd class="font-medium text-gray-900">{{ $payment->shop->shopOwner?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-600">{{ __('Billing Month') }}</dt>
                                <dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($payment->invoice->billing_month)->format('F Y') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                @if($payment->transaction_reference)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('payments.transaction_reference') }}</h3>
                    <p class="text-gray-700">{{ $payment->transaction_reference }}</p>
                </div>
                @endif

                @if($payment->notes)
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">{{ __('Notes') }}</h3>
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
            <a href="{{ route('market-owner.payments.index') }}" class="text-indigo-600 hover:text-indigo-800">
                ← {{ __('Back to Payments') }}
            </a>
            <a href="{{ route('market-owner.payments.receipt', $payment) }}"
               class="px-4 py-2 btn-primary" target="_blank">
                {{ __('Print Receipt') }}
            </a>
        </div>
    </div>
</x-app-layout>
