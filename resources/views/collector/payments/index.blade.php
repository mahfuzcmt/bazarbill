<x-app-layout>
    <x-slot name="header">{{ __('payments.my_collections') }}</x-slot>

    <div class="space-y-6">
        <!-- Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('payments.today') }}</p>
                <p class="text-2xl font-bold text-green-600">৳{{ number_format($todayTotal) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('payments.this_week') }}</p>
                <p class="text-2xl font-bold text-blue-600">৳{{ number_format($weekTotal) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('payments.this_month') }}</p>
                <p class="text-2xl font-bold text-indigo-600">৳{{ number_format($monthTotal) }}</p>
            </div>
        </div>

        <!-- Quick Action -->
        <div class="flex justify-end">
            <a href="{{ route('collector.payments.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('payments.collect_payment') }}
            </a>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.shop') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.invoice') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $payment->receipt_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $payment->shop->shop_number }}</div>
                            <div class="text-xs text-gray-500">{{ $payment->shop->shopOwner?->getLocalizedName() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            ৳{{ number_format($payment->amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('collector.payments.receipt', $payment) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm" target="_blank">
                                {{ __('payments.print_receipt') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="mt-2">{{ __('payments.no_payments') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            </div>

            @if($payments->hasPages())
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                {{ $payments->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
