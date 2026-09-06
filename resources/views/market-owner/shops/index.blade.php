<x-app-layout>
    <x-slot name="header">{{ __('shops.shops') }}</x-slot>

    <div class="space-y-6">
        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <form method="GET" class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('shops.search_placeholder') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            <div class="flex flex-col sm:flex-row gap-2 sm:space-x-3 w-full sm:w-auto">
                <select name="status" onchange="this.form.submit()" form="filter-form"
                        class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500 w-full sm:w-auto">
                    <option value="">{{ __('shops.all_status') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('shops.status_active') }}</option>
                    <option value="vacant" {{ request('status') === 'vacant' ? 'selected' : '' }}>{{ __('shops.status_vacant') }}</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>{{ __('shops.status_suspended') }}</option>
                </select>

                <a href="{{ route('market-owner.shops.create') }}"
                   class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition w-full sm:w-auto">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('shops.add_shop') }}
                </a>
            </div>
        </div>

        <!-- Shops Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.shop_number') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.owner') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.floor') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.rent_amount') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.due_amount') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.status') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($shops as $shop)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $shop->shop_number }}</div>
                            <div class="text-xs text-gray-500">{{ $shop->shop_type }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($shop->shopOwner)
                            <div class="text-sm text-gray-900">{{ $shop->shopOwner->getLocalizedName() }}</div>
                            <div class="text-xs text-gray-500">{{ $shop->shopOwner->phone }}</div>
                            @else
                            <span class="text-sm text-gray-400">{{ __('shops.no_owner') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $shop->floor ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            ৳{{ number_format($shop->rent_amount) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $dueAmount = $shop->invoices->whereIn('status', ['pending', 'partial', 'overdue'])->sum('due_amount');
                            @endphp
                            <span class="text-sm font-medium {{ $dueAmount > 0 ? 'text-red-600' : 'text-green-600' }}">
                                ৳{{ number_format($dueAmount) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'vacant' => 'bg-yellow-100 text-yellow-800',
                                    'suspended' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$shop->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('shops.status_' . $shop->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('market-owner.shops.show', $shop) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('messages.view') }}
                                </a>
                                <a href="{{ route('market-owner.shops.edit', $shop) }}" class="text-yellow-600 hover:text-yellow-900">
                                    {{ __('messages.edit') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="mt-2">{{ __('shops.no_shops') }}</p>
                            <a href="{{ route('market-owner.shops.create') }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                {{ __('shops.add_first_shop') }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            </div>

            @if($shops->hasPages())
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                {{ $shops->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
