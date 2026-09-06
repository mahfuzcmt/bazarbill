<x-app-layout>
    <x-slot name="header">{{ __('shops.shop_details') }}</x-slot>

    <div class="max-w-4xl space-y-6">
        <!-- Shop Info Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $shop->shop_number }}</h2>
                        <p class="text-gray-500">{{ $shop->floor }}</p>
                    </div>
                    <span class="inline-flex self-start sm:self-auto px-3 py-1 text-sm font-semibold rounded-full
                        {{ $shop->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $shop->status === 'vacant' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $shop->status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ __('shops.status_' . $shop->status) }}
                    </span>
                </div>
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.owner') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($shop->shopOwner)
                                {{ $shop->shopOwner->name }}
                                @if($shop->shopOwner->name_bn)
                                <span class="text-gray-500">({{ $shop->shopOwner->name_bn }})</span>
                                @endif
                            @else
                                <span class="text-gray-400">{{ __('shops.no_owner') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.phone') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($shop->shopOwner && $shop->shopOwner->phone)
                                <a href="tel:{{ $shop->shopOwner->phone }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ $shop->shopOwner->phone }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.rent_amount') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">৳ {{ number_format($shop->rent_amount) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.area') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $shop->area_sqft ? $shop->area_sqft . ' sqft' : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.shop_type') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ __('shops.type_' . ($shop->shop_type ?? 'general')) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('shops.advance_deposit') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">৳ {{ number_format($shop->advance_deposit ?? 0) }}</dd>
                    </div>
                </dl>

                @if($shop->notes)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <dt class="text-sm font-medium text-gray-500">{{ __('shops.notes') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $shop->notes }}</dd>
                </div>
                @endif
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('invoices.total_due') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($totalDue) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('payments.total_paid') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($totalPaid) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('invoices.pending_invoices') }}</p>
                <p class="text-2xl font-bold text-amber-600">{{ $pendingInvoices }}</p>
            </div>
        </div>

        <!-- Quick Action -->
        @if($totalDue > 0)
        <div class="bg-indigo-50 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <p class="text-indigo-800 font-medium">{{ __('collector.collect_payment_prompt') }}</p>
                <p class="text-indigo-600 text-sm">{{ __('collector.total_due') }}: ৳ {{ number_format($totalDue) }}</p>
            </div>
            <a href="{{ route('collector.payments.create', ['shop' => $shop->id]) }}"
               class="w-full sm:w-auto text-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                {{ __('collector.collect_payment') }}
            </a>
        </div>
        @endif

        <!-- Pending Invoices -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('invoices.pending_invoices') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.billing_month') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.invoice_number') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.total_amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.paid_amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.due_amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pendingInvoicesList as $invoice)
                        <tr class="{{ $invoice->status === 'overdue' ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($invoice->billing_month)->format('F Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ৳ {{ number_format($invoice->total_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-green-600">
                                ৳ {{ number_format($invoice->paid_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-red-600">
                                ৳ {{ number_format($invoice->due_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'partial' => 'bg-blue-100 text-blue-800',
                                        'overdue' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ __('invoices.status_' . $invoice->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                {{ __('invoices.no_pending') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('payments.recent_payments') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt_number') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.method') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.collected_by') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $payment->payment_date->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                {{ $payment->receipt_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                ৳ {{ number_format($payment->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ __('payments.method_' . $payment->payment_method) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $payment->collector->name ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                {{ __('payments.no_payments') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('collector.shops.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                ← {{ __('messages.back_to_list') }}
            </a>
        </div>
    </div>
</x-app-layout>
