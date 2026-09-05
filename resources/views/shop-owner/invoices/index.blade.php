<x-app-layout>
    <x-slot name="header">{{ __('invoices.my_invoices') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('invoices.total_billed') }}</p>
                <p class="text-2xl font-bold text-gray-900">৳{{ number_format($totalBilled) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('invoices.total_paid') }}</p>
                <p class="text-2xl font-bold text-green-600">৳{{ number_format($totalPaid) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('invoices.total_due') }}</p>
                <p class="text-2xl font-bold {{ $totalDue > 0 ? 'text-red-600' : 'text-green-600' }}">৳{{ number_format($totalDue) }}</p>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.invoice_number') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.billing_month') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.total') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.paid') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.due') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $invoice->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->billing_month_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ৳{{ number_format($invoice->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                            ৳{{ number_format($invoice->paid_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                            ৳{{ number_format($invoice->due_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'partial' => 'bg-blue-100 text-blue-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'overdue' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('invoices.status_' . $invoice->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('shop-owner.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('messages.view') }}
                                </a>
                                <a href="{{ route('shop-owner.invoices.pdf', $invoice) }}" class="text-gray-600 hover:text-gray-900">
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2">{{ __('invoices.no_invoices') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if($invoices->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
