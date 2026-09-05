<x-app-layout>
    <x-slot name="header">{{ __('messages.dashboard') }}</x-slot>

    <div class="space-y-6">
        @if(!$shop)
        <!-- No Shop Assigned -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-yellow-800">{{ __('shops.no_shop_assigned') }}</h3>
            <p class="mt-1 text-sm text-yellow-600">{{ __('shops.contact_market_owner') }}</p>
        </div>
        @else

        <!-- Shop Info Banner -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">{{ $shop->shop_number }}</h2>
                    <p class="text-indigo-100">{{ $shop->floor ?? '' }} • {{ __('shops.type_' . $shop->shop_type) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-indigo-100">{{ __('shops.monthly_rent') }}</p>
                    <p class="text-3xl font-bold">৳{{ number_format($shop->rent_amount) }}</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">{{ __('invoices.total_due') }}</p>
                        <p class="text-2xl font-bold {{ $totalDue > 0 ? 'text-red-600' : 'text-green-600' }}">৳{{ number_format($totalDue) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">{{ __('invoices.total_paid') }}</p>
                        <p class="text-2xl font-bold text-green-600">৳{{ number_format($totalPaid) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm text-gray-500">{{ __('invoices.total_invoices') }}</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ $totalInvoices }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Invoice -->
        @if($currentInvoice)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-medium text-gray-900">{{ __('invoices.current_invoice') }}</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ $currentInvoice->invoice_number }}</p>
                        <p class="text-lg font-medium text-gray-900">{{ $currentInvoice->billing_month_formatted }}</p>
                    </div>
                    <div class="text-right">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'partial' => 'bg-blue-100 text-blue-800',
                                'paid' => 'bg-green-100 text-green-800',
                                'overdue' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statusColors[$currentInvoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ __('invoices.status_' . $currentInvoice->status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-sm text-gray-500">{{ __('invoices.total') }}</p>
                        <p class="text-lg font-semibold text-gray-900">৳{{ number_format($currentInvoice->total_amount) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <p class="text-sm text-gray-500">{{ __('invoices.paid') }}</p>
                        <p class="text-lg font-semibold text-green-600">৳{{ number_format($currentInvoice->paid_amount) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-sm text-gray-500">{{ __('invoices.due') }}</p>
                        <p class="text-lg font-semibold text-red-600">৳{{ number_format($currentInvoice->due_amount) }}</p>
                    </div>
                </div>

                <div class="mt-4 flex justify-between items-center">
                    <p class="text-sm text-gray-500">
                        {{ __('invoices.due_date') }}: <span class="{{ $currentInvoice->due_date < now() && $currentInvoice->due_amount > 0 ? 'text-red-600 font-medium' : '' }}">{{ $currentInvoice->due_date->format('d M Y') }}</span>
                    </p>
                    <a href="{{ route('shop-owner.invoices.show', $currentInvoice) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                        {{ __('messages.view_details') }} →
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Invoices -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">{{ __('invoices.recent_invoices') }}</h3>
                <a href="{{ route('shop-owner.invoices.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('messages.view_all') }} →
                </a>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.month') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.amount') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('invoices.status') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentInvoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->billing_month_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ৳{{ number_format($invoice->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('invoices.status_' . $invoice->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            {{ __('invoices.no_invoices') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">{{ __('payments.recent_payments') }}</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentPayments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->receipt_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            ৳{{ number_format($payment->amount) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            {{ __('payments.no_payments') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Notices -->
        @if($notices->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">{{ __('notices.notices') }}</h3>
                <a href="{{ route('shop-owner.notices') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('messages.view_all') }} →
                </a>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($notices as $notice)
                <div class="p-4 {{ $notice->is_pinned ? 'bg-indigo-50' : '' }}">
                    <div class="flex items-start space-x-3">
                        @if($notice->is_pinned)
                        <svg class="w-5 h-5 text-indigo-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                        </svg>
                        @endif
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $notice->getLocalizedTitle() }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ Str::limit($notice->getLocalizedContent(), 100) }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $notice->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @endif
    </div>
</x-app-layout>
