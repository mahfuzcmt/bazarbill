<x-app-layout>
    <x-slot name="header">{{ __('invoices.invoices') }}</x-slot>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.billing_month') }}</label>
                    <input type="month" name="month" value="{{ request('month') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('invoices.status') }}</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('invoices.all_status') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('invoices.status_pending') }}</option>
                        <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>{{ __('invoices.status_partial') }}</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('invoices.status_paid') }}</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>{{ __('invoices.status_overdue') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('shops.shop') }}</label>
                    <select name="shop" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('invoices.all_shops') }}</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        {{ __('messages.filter') }}
                    </button>
                    <a href="{{ route('market-owner.invoices.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                        {{ __('messages.reset') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900">{{ __('invoices.invoice_list') }}</h3>
                <span class="text-sm text-gray-500">({{ $invoices->total() }} {{ __('invoices.total') }})</span>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('market-owner.invoices.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('invoices.generate_invoice') }}
                </a>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.invoice_number') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.shop') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.billing_month') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.total_amount') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.due_amount') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.due_date') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('invoices.status') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($invoices as $invoice)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('market-owner.invoices.show', $invoice) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                {{ $invoice->invoice_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $invoice->shop->shop_number }}</div>
                            <div class="text-xs text-gray-500">{{ $invoice->shop->shopOwner?->getLocalizedName() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $invoice->billing_month_formatted }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ৳{{ number_format($invoice->total_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $invoice->due_amount > 0 ? 'text-red-600' : 'text-green-600' }}">
                            ৳{{ number_format($invoice->due_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $invoice->due_date < now() && $invoice->due_amount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $invoice->due_date->format('d M Y') }}
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
                                <a href="{{ route('market-owner.invoices.show', $invoice) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('messages.view') }}
                                </a>
                                <a href="{{ route('market-owner.invoices.pdf', $invoice) }}" class="text-gray-600 hover:text-gray-900">
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
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
                {{ $invoices->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
