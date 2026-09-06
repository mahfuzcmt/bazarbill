<x-app-layout>
    <x-slot name="header">{{ __('invoices.invoice_details') }}: {{ $invoice->invoice_number }}</x-slot>

    <div class="space-y-6">
        <!-- Invoice Header -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $invoice->invoice_number }}</h3>
                    <p class="text-sm text-gray-500">{{ __('invoices.generated_at') }}: {{ $invoice->generated_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2">
                    <a href="{{ route('market-owner.invoices.pdf', $invoice) }}"
                       class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('invoices.download_pdf') }}
                    </a>
                    @if($invoice->due_amount > 0)
                    <a href="{{ route('market-owner.payments.create', ['invoice' => $invoice->id]) }}"
                       class="inline-flex items-center justify-center px-3 py-1.5 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ __('payments.record_payment') }}
                    </a>
                    @endif
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Shop Info -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('shops.shop_information') }}</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-lg font-semibold text-gray-900">{{ $invoice->shop->shop_number }}</p>
                        @if($invoice->shop->shopOwner)
                        <p class="text-sm text-gray-700 mt-1">{{ $invoice->shop->shopOwner->getLocalizedName() }}</p>
                        <p class="text-sm text-gray-500">{{ $invoice->shop->shopOwner->phone }}</p>
                        @endif
                        <p class="text-sm text-gray-500 mt-2">{{ $invoice->shop->floor ?? '-' }}</p>
                    </div>
                </div>

                <!-- Invoice Status -->
                <div>
                    <h4 class="text-sm font-medium text-gray-500 uppercase mb-3">{{ __('invoices.status') }}</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'partial' => 'bg-blue-100 text-blue-800 border-blue-300',
                                'paid' => 'bg-green-100 text-green-800 border-green-300',
                                'overdue' => 'bg-red-100 text-red-800 border-red-300',
                            ];
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full border {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ __('invoices.status_' . $invoice->status) }}
                        </span>
                        <div class="mt-3 space-y-1">
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">{{ __('invoices.billing_month') }}:</span> {{ $invoice->billing_month_formatted }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-medium">{{ __('invoices.due_date') }}:</span>
                                <span class="{{ $invoice->due_date < now() && $invoice->due_amount > 0 ? 'text-red-600' : '' }}">
                                    {{ $invoice->due_date->format('d M Y') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Breakdown -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('invoices.amount_breakdown') }}</h3>
            </div>
            <div class="p-6">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3 text-sm text-gray-600">{{ __('invoices.rent_amount') }}</td>
                            <td class="py-3 text-sm text-gray-900 text-right">৳{{ number_format($invoice->rent_amount) }}</td>
                        </tr>
                        @if($invoice->previous_due > 0)
                        <tr>
                            <td class="py-3 text-sm text-gray-600">{{ __('invoices.previous_due') }}</td>
                            <td class="py-3 text-sm text-red-600 text-right">+৳{{ number_format($invoice->previous_due) }}</td>
                        </tr>
                        @endif
                        @if($invoice->late_fee > 0)
                        <tr>
                            <td class="py-3 text-sm text-gray-600">{{ __('invoices.late_fee') }}</td>
                            <td class="py-3 text-sm text-red-600 text-right">+৳{{ number_format($invoice->late_fee) }}</td>
                        </tr>
                        @endif
                        @if($invoice->discount > 0)
                        <tr>
                            <td class="py-3 text-sm text-gray-600">{{ __('invoices.discount') }}</td>
                            <td class="py-3 text-sm text-green-600 text-right">-৳{{ number_format($invoice->discount) }}</td>
                        </tr>
                        @endif
                        <tr class="border-t-2 border-gray-200">
                            <td class="py-3 text-base font-semibold text-gray-900">{{ __('invoices.total_amount') }}</td>
                            <td class="py-3 text-base font-semibold text-gray-900 text-right">৳{{ number_format($invoice->total_amount) }}</td>
                        </tr>
                        <tr>
                            <td class="py-3 text-sm text-gray-600">{{ __('invoices.paid_amount') }}</td>
                            <td class="py-3 text-sm text-green-600 text-right">-৳{{ number_format($invoice->paid_amount) }}</td>
                        </tr>
                        <tr class="border-t-2 border-gray-200">
                            <td class="py-3 text-lg font-bold {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ __('invoices.due_amount') }}
                            </td>
                            <td class="py-3 text-lg font-bold {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }} text-right">
                                ৳{{ number_format($invoice->due_amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment History -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('payments.payment_history') }}</h3>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt_number') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.method') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.collected_by') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoice->payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $payment->receipt_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            ৳{{ number_format($payment->amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ __('payments.method_' . $payment->payment_method) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->collector?->getLocalizedName() ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            {{ __('payments.no_payments') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->notes)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('invoices.notes') }}</h3>
            <p class="text-gray-600">{{ $invoice->notes }}</p>
        </div>
        @endif
    </div>
</x-app-layout>
