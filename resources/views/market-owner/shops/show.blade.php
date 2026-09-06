<x-app-layout>
    <x-slot name="header">{{ __('shops.shop_details') }}: {{ $shop->shop_number }}</x-slot>

    <div class="space-y-6">
        <!-- Shop Info Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">{{ __('shops.shop_information') }}</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('market-owner.shops.edit', $shop) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        {{ __('messages.edit') }}
                    </a>
                </div>
            </div>

            <div class="p-6">
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.shop_number') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $shop->shop_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.floor') }}</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ $shop->floor ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.shop_type') }}</dt>
                        <dd class="mt-1 text-lg text-gray-900">{{ __('shops.type_' . $shop->shop_type) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.rent_amount') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-indigo-600">৳{{ number_format($shop->rent_amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.advance_deposit') }}</dt>
                        <dd class="mt-1 text-lg text-gray-900">৳{{ number_format($shop->advance_deposit ?? 0) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.status') }}</dt>
                        <dd class="mt-1">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'vacant' => 'bg-yellow-100 text-yellow-800',
                                    'suspended' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$shop->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('shops.status_' . $shop->status) }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Owner & Collector Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Owner Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('shops.owner_information') }}</h3>
                @if($shop->shopOwner)
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-xl font-medium text-indigo-600">{{ substr($shop->shopOwner->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-lg font-medium text-gray-900">{{ $shop->shopOwner->getLocalizedName() }}</p>
                        <p class="text-sm text-gray-500">{{ $shop->shopOwner->phone }}</p>
                        <p class="text-sm text-gray-500">{{ $shop->shopOwner->email }}</p>
                    </div>
                </div>
                @else
                <p class="text-gray-500">{{ __('shops.no_owner') }}</p>
                @endif
            </div>

            <!-- Collector Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('shops.assigned_collector') }}</h3>
                @if($shop->collector)
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-xl font-medium text-green-600">{{ substr($shop->collector->name, 0, 1) }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-lg font-medium text-gray-900">{{ $shop->collector->getLocalizedName() }}</p>
                        <p class="text-sm text-gray-500">{{ $shop->collector->phone }}</p>
                    </div>
                </div>
                @else
                <p class="text-gray-500">{{ __('shops.no_collector') }}</p>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('shops.financial_summary') }}</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-gray-900">৳{{ number_format($shop->invoices->sum('total_amount')) }}</p>
                    <p class="text-sm text-gray-500">{{ __('invoices.total_billed') }}</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">৳{{ number_format($shop->invoices->sum('paid_amount')) }}</p>
                    <p class="text-sm text-gray-500">{{ __('invoices.total_paid') }}</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="text-2xl font-bold text-red-600">৳{{ number_format($shop->invoices->whereIn('status', ['pending', 'partial', 'overdue'])->sum('due_amount')) }}</p>
                    <p class="text-sm text-gray-500">{{ __('invoices.total_due') }}</p>
                </div>
                <div class="text-center p-4 bg-indigo-50 rounded-lg">
                    <p class="text-2xl font-bold text-indigo-600">{{ $shop->invoices->count() }}</p>
                    <p class="text-sm text-gray-500">{{ __('invoices.total_invoices') }}</p>
                </div>
            </div>
        </div>

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-lg font-medium text-gray-900">{{ __('invoices.recent_invoices') }}</h3>
                <a href="{{ route('market-owner.invoices.index', ['shop' => $shop->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('messages.view_all') }} →
                </a>
            </div>

            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.invoice_number') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.billing_month') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.due_amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.status') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($shop->invoices->take(5) as $invoice)
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                            ৳{{ number_format($invoice->due_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $invoiceStatusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'partial' => 'bg-blue-100 text-blue-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'overdue' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $invoiceStatusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('invoices.status_' . $invoice->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            {{ __('invoices.no_invoices') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <h3 class="text-lg font-medium text-gray-900">{{ __('payments.recent_payments') }}</h3>
                <a href="{{ route('market-owner.payments.index', ['shop' => $shop->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('messages.view_all') }} →
                </a>
            </div>

            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt_number') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.collected_by') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($shop->payments->take(5) as $payment)
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
                            {{ $payment->collector?->getLocalizedName() ?? '-' }}
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
    </div>
</x-app-layout>
