<x-app-layout>
    <x-slot name="header">{{ __('invoices.invoice_details') }}</x-slot>

    <div class="max-w-4xl space-y-6">
        <!-- Invoice Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $invoice->invoice_number }}</h3>
                    <p class="text-sm text-gray-500">{{ $invoice->billing_month_formatted }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
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
                    <a href="{{ route('shop-owner.invoices.pdf', $invoice) }}"
                       class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('invoices.download_pdf') }}
                    </a>
                </div>
            </div>

            <div class="p-6">
                <!-- Amount Breakdown -->
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

                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-600">
                        <span class="font-medium">{{ __('invoices.due_date') }}:</span>
                        <span class="{{ $invoice->due_date < now() && $invoice->due_amount > 0 ? 'text-red-600' : '' }}">
                            {{ $invoice->due_date->format('d M Y') }}
                        </span>
                    </p>
                </div>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.method') }}</th>
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
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            {{ __('payments.no_payments') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="flex justify-start">
            <a href="{{ route('shop-owner.invoices.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                ← {{ __('messages.back_to_list') }}
            </a>
        </div>
    </div>
</x-app-layout>
