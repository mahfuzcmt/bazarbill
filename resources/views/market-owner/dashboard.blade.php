<x-app-layout>
    <x-slot name="header">
        {{ __('messages.dashboard') }}
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Shops -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('shops.title') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $shopStats->total ?? 0 }}</p>
                </div>
            </div>
            <div class="mt-4 flex space-x-4 text-sm">
                <span class="text-green-600">{{ $shopStats->active ?? 0 }} {{ __('shops.statuses.active') }}</span>
                <span class="text-yellow-600">{{ $shopStats->vacant ?? 0 }} {{ __('shops.statuses.vacant') }}</span>
            </div>
        </div>

        <!-- Monthly Collection -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('Collection This Month') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ __('messages.currency_symbol') }} {{ number_format($paymentStats->total_collected ?? 0) }}</p>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                {{ $paymentStats->total_transactions ?? 0 }} {{ __('transactions') }}
            </div>
        </div>

        <!-- Total Due -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('Total Due') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ __('messages.currency_symbol') }} {{ number_format($invoiceStats->due_amount ?? 0) }}</p>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="text-red-600">{{ $invoiceStats->overdue_count ?? 0 }} {{ __('invoices.statuses.overdue') }}</span>
            </div>
        </div>

        <!-- Invoices Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('invoices.title') }} ({{ $currentMonth }})</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $invoiceStats->total ?? 0 }}</p>
                </div>
            </div>
            <div class="mt-4 flex space-x-4 text-sm">
                <span class="text-green-600">{{ $invoiceStats->paid_count ?? 0 }} {{ __('invoices.statuses.paid') }}</span>
                <span class="text-yellow-600">{{ $invoiceStats->pending_count ?? 0 }} {{ __('invoices.statuses.pending') }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Payments -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">{{ __('Recent Payments') }}</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentPayments as $payment)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $payment->shop->getDisplayName() }}</p>
                        <p class="text-sm text-gray-500">{{ $payment->receipt_number }} • {{ $payment->collector?->name ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-green-600">{{ __('messages.currency_symbol') }} {{ number_format($payment->amount) }}</p>
                        <p class="text-xs text-gray-500">{{ $payment->payment_date->format('d M Y') }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-4 text-center text-gray-500">
                    {{ __('payments.no_payments') }}
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('market-owner.payments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('View All') }} →
                </a>
            </div>
        </div>

        <!-- Overdue Invoices -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">{{ __('Overdue Invoices') }}</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($overdueInvoices as $invoice)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $invoice->shop->getDisplayName() }}</p>
                        <p class="text-sm text-gray-500">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-red-600">{{ __('messages.currency_symbol') }} {{ number_format($invoice->due_amount) }}</p>
                        <p class="text-xs text-red-500">{{ __('Due') }}: {{ $invoice->due_date->format('d M Y') }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-4 text-center text-gray-500">
                    {{ __('No overdue invoices') }}
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('market-owner.invoices.index', ['status' => 'overdue']) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('View All') }} →
                </a>
            </div>
        </div>

        <!-- Pending Complaints -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">{{ __('Pending Complaints') }}</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($pendingComplaints as $complaint)
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">{{ $complaint->subject }}</p>
                        <span class="px-2 py-1 text-xs rounded-full {{ $complaint->priority === 'high' ? 'bg-red-100 text-red-800' : ($complaint->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ __('complaints.priorities.' . $complaint->priority) }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">{{ $complaint->shop->getDisplayName() }} • {{ $complaint->submitter->name }}</p>
                </div>
                @empty
                <div class="px-6 py-4 text-center text-gray-500">
                    {{ __('complaints.no_complaints') }}
                </div>
                @endforelse
            </div>
            <div class="px-6 py-3 bg-gray-50 border-t">
                <a href="{{ route('market-owner.complaints.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                    {{ __('View All') }} →
                </a>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">{{ __('Quick Actions') }}</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <a href="{{ route('market-owner.invoices.create') }}"
                   class="flex flex-col items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="mt-2 text-sm font-medium text-indigo-900">{{ __('invoices.generate_invoice') }}</span>
                </a>

                <a href="{{ route('market-owner.payments.create') }}"
                   class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span class="mt-2 text-sm font-medium text-green-900">{{ __('payments.collect_payment') }}</span>
                </a>

                <a href="{{ route('market-owner.shops.create') }}"
                   class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="mt-2 text-sm font-medium text-blue-900">{{ __('shops.add_shop') }}</span>
                </a>

                <a href="{{ route('market-owner.notices.create') }}"
                   class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span class="mt-2 text-sm font-medium text-yellow-900">{{ __('notices.create_notice') }}</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
