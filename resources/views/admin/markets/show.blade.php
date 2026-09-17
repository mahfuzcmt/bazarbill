<x-app-layout>
    <x-slot name="header">
        {{ __('Market Details') }}: {{ $market->name }}
    </x-slot>

    <div class="space-y-6">
        <!-- Market Info -->
        <div class="glass-card p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $market->name }}</h2>
                    @if($market->name_bn)
                        <p class="text-lg text-gray-600">{{ $market->name_bn }}</p>
                    @endif
                </div>
                <span class="px-3 py-1 text-sm rounded-full {{ $market->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($market->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('Address') }}</h4>
                    <p class="text-gray-800">{{ $market->address ?? '-' }}</p>
                    @if($market->address_bn)
                        <p class="text-gray-600 text-sm">{{ $market->address_bn }}</p>
                    @endif
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('Contact') }}</h4>
                    <p class="text-gray-800">{{ $market->phone ?? '-' }}</p>
                    <p class="text-gray-600 text-sm">{{ $market->email ?? '-' }}</p>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <a href="{{ route('admin.markets.edit', $market) }}" class="px-4 py-2 btn-primary transition">
                    {{ __('Edit Market') }}
                </a>
                <a href="{{ route('admin.markets.index') }}" class="px-4 py-2 btn-secondary transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Shops') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_shops'] }}</p>
                <p class="text-sm text-green-600">{{ $stats['active_shops'] }} {{ __('active') }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Users') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Collection') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($stats['total_collection']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Due') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($stats['total_due']) }}</p>
            </div>
        </div>

        <!-- Shops List -->
        <div class="glass-card">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Shops in this Market') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shop No') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Floor') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Rent') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($market->shops as $shop)
                            <tr class="hover:bg-white/50 transition">
                                <td class="px-6 py-4 font-medium">{{ $shop->shop_number }}</td>
                                <td class="px-6 py-4">{{ $shop->floor ?? '-' }}</td>
                                <td class="px-6 py-4">৳ {{ number_format($shop->rent_amount) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $shop->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $shop->status === 'vacant' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $shop->status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($shop->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('No shops in this market yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users List -->
        <div class="glass-card">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Users in this Market') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Role') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($market->users as $user)
                            <tr class="hover:bg-white/50 transition">
                                <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? __('Active') : __('Inactive') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">{{ __('No users in this market yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
