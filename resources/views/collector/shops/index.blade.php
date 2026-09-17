<x-app-layout>
    <x-slot name="header">{{ __('shops.my_shops') }}</x-slot>

    <div class="space-y-6">
        <!-- Search -->
        <div class="glass-card p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('shops.search_placeholder') }}"
                           class="w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('messages.search') }}
                </button>
            </form>
        </div>

        <!-- Shops Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($shops as $shop)
            <div class="glass-card overflow-hidden hover:shadow-lg transition">
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $shop->shop_number }}</h3>
                            <p class="text-sm text-gray-500">{{ $shop->floor ?? '-' }}</p>
                        </div>
                        @if($shop->total_due > 0)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                            {{ __('invoices.due') }}: ৳{{ number_format($shop->total_due) }}
                        </span>
                        @else
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            {{ __('invoices.paid_up') }}
                        </span>
                        @endif
                    </div>

                    @if($shop->shopOwner)
                    <div class="mt-3 flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $shop->shopOwner->getLocalizedName() }}
                    </div>
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $shop->shopOwner->phone }}
                    </div>
                    @endif

                    <div class="mt-3 text-sm">
                        <span class="text-gray-500">{{ __('shops.rent') }}:</span>
                        <span class="font-medium text-gray-900">৳{{ number_format($shop->rent_amount) }}</span>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 border-t flex justify-between">
                    <a href="{{ route('collector.shops.show', $shop) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                        {{ __('messages.view_details') }}
                    </a>
                    @if($shop->total_due > 0)
                    <a href="{{ route('collector.payments.create', ['shop' => $shop->id]) }}"
                       class="text-sm text-green-600 hover:text-green-900 font-medium">
                        {{ __('payments.collect') }} →
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full glass-card p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="mt-2 text-gray-500">{{ __('shops.no_assigned_shops') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
