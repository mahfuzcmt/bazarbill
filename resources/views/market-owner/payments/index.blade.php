<x-app-layout>
    <x-slot name="header">{{ __('payments.payments') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
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
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('payments.total_collected') }}</p>
                <p class="text-2xl font-bold text-gray-900">৳{{ number_format($allTimeTotal) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('payments.date_from') }}</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('payments.date_to') }}</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('shops.shop') }}</label>
                    <select name="shop" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('payments.all_shops') }}</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('payments.collector') }}</label>
                    <select name="collector" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('payments.all_collectors') }}</option>
                        @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}" {{ request('collector') == $collector->id ? 'selected' : '' }}>
                            {{ $collector->getLocalizedName() }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        {{ __('messages.filter') }}
                    </button>
                    <a href="{{ route('market-owner.payments.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                        {{ __('messages.reset') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900">{{ __('payments.payment_list') }}</h3>
                <span class="text-sm text-gray-500">({{ $payments->total() }} {{ __('payments.records') }})</span>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('market-owner.payments.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('payments.record_payment') }}
                </a>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('payments.receipt_number') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.shop') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.invoice') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('payments.amount') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('payments.method') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('payments.date') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('payments.collected_by') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
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
                            <a href="{{ route('market-owner.invoices.show', $payment->invoice) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $payment->invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                            ৳{{ number_format($payment->amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ __('payments.method_' . $payment->payment_method) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->payment_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $payment->collector?->getLocalizedName() ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('market-owner.payments.receipt', $payment) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ __('payments.receipt') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
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
                {{ $payments->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
