<x-app-layout>
    <x-slot name="header">{{ __('shop_owners.title') }}</x-slot>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-medium text-gray-900">{{ __('shop_owners.title') }}
                    <span class="text-sm text-gray-500 font-normal">({{ $owners->total() }} {{ __('shop_owners.total') }})</span>
                </h3>
                <p class="text-sm text-gray-500">{{ __('shop_owners.subtitle') }}</p>
                @if($unassignedShops > 0)
                    <p class="text-sm text-yellow-700 mt-1">{{ __('shop_owners.unassigned_shops', ['count' => $unassignedShops]) }}</p>
                @endif
            </div>
            <a href="{{ route('market-owner.shop-owners.create') }}" class="inline-flex items-center px-4 py-2 btn-primary transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('shop_owners.add') }}
            </a>
        </div>

        <div class="glass-card">
            <div class="p-4 sm:p-6 border-b">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('shop_owners.search_placeholder') }}" class="w-full glass-input">
                    </div>
                    <select name="status" class="glass-input w-full sm:w-40">
                        <option value="">{{ __('shop_owners.all_statuses') }}</option>
                        <option value="active" @selected(request('status') === 'active')>{{ __('shop_owners.active') }}</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>{{ __('shop_owners.inactive') }}</option>
                    </select>
                    <button type="submit" class="px-4 py-2 btn-primary transition">{{ __('messages.search') }}</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('shop_owners.name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('shop_owners.phone') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('shop_owners.shops') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('shop_owners.status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($owners as $owner)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <span class="text-lg font-medium text-emerald-700">{{ mb_substr($owner->getLocalizedName(), 0, 1) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $owner->getLocalizedName() }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ __('shop_owners.login_id') }}: {{ str_ends_with($owner->email, '@bazarbill.local') ? $owner->phone : $owner->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $owner->phone }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @forelse($owner->ownedShops as $shop)
                                    <a href="{{ route('market-owner.shops.show', $shop) }}" class="inline-block mr-1 mb-1 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-100">{{ $shop->shop_number }}</a>
                                @empty
                                    <span class="text-gray-400">{{ __('shop_owners.no_shops') }}</span>
                                @endforelse
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $owner->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $owner->is_active ? __('shop_owners.active') : __('shop_owners.inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end items-center gap-3">
                                    <a href="{{ route('market-owner.shop-owners.edit', $owner) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('messages.edit') }}</a>
                                    <form action="{{ route('market-owner.shop-owners.toggle-status', $owner) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="{{ $owner->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}">
                                            {{ $owner->is_active ? __('shop_owners.inactive') : __('shop_owners.active') }}
                                        </button>
                                    </form>
                                    <form action="{{ route('market-owner.shop-owners.destroy', $owner) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('shop_owners.delete_confirmation') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('messages.delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <p>{{ __('shop_owners.no_owners') }}</p>
                                <a href="{{ route('market-owner.shop-owners.create') }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">{{ __('shop_owners.add_first') }}</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($owners->hasPages())
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">{{ $owners->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
