<x-app-layout>
    <x-slot name="header">
        {{ __('Super Admin Dashboard') }}
    </x-slot>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <!-- Total Markets -->
        <div class="glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('Total Markets') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_markets'] }}</p>
                </div>
            </div>
            <div class="mt-4 text-sm">
                <span class="text-green-600">{{ $stats['active_markets'] }} {{ __('Active') }}</span>
            </div>
        </div>

        <!-- Total Users -->
        <div class="glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('Total Users') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
            </div>
        </div>

        <!-- Total Shops -->
        <div class="glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('Total Shops') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_shops'] }}</p>
                </div>
            </div>
        </div>

        <!-- Total Collection -->
        <div class="glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">{{ __('Total Collection') }}</p>
                    <p class="text-2xl font-semibold text-gray-900">৳ {{ number_format($stats['total_collection']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription overview -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}" class="glass-card p-4 hover:bg-white/60 transition">
            <p class="text-sm text-gray-500">{{ __('Paying markets') }}</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['paying_markets'] }}</p>
        </a>
        <a href="{{ route('admin.subscriptions.index', ['status' => 'trial']) }}" class="glass-card p-4 hover:bg-white/60 transition">
            <p class="text-sm text-gray-500">{{ __('On trial') }}</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['trial_markets'] }}</p>
        </a>
        <a href="{{ route('admin.subscriptions.index', ['status' => 'expiring']) }}" class="glass-card p-4 hover:bg-white/60 transition">
            <p class="text-sm text-gray-500">{{ __('Expiring in 7 days') }}</p>
            <p class="text-2xl font-bold {{ $stats['expiring_markets'] ? 'text-yellow-600' : 'text-gray-800' }}">{{ $stats['expiring_markets'] }}</p>
        </a>
        <div class="glass-card p-4">
            <p class="text-sm text-gray-500">{{ __('Monthly recurring revenue') }}</p>
            <p class="text-2xl font-bold text-gray-800">৳ {{ number_format($stats['mrr']) }}</p>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Quick Actions') }}</h3>
            <div class="grid grid-cols-2 gap-4">
                <a href="{{ route('admin.markets.create') }}" class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="ml-3 text-indigo-700 font-medium">{{ __('Add Market') }}</span>
                </a>
                <a href="{{ route('admin.users.create') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    <span class="ml-3 text-blue-700 font-medium">{{ __('Add User') }}</span>
                </a>
                <a href="{{ route('admin.markets.index') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span class="ml-3 text-green-700 font-medium">{{ __('All Markets') }}</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="ml-3 text-yellow-700 font-medium">{{ __('All Users') }}</span>
                </a>
            </div>
        </div>

        <!-- System Info -->
        <div class="glass-card p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('System Information') }}</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center py-2 border-b">
                    <span class="text-gray-600">{{ __('Laravel Version') }}</span>
                    <span class="font-medium text-gray-800">{{ app()->version() }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b">
                    <span class="text-gray-600">{{ __('PHP Version') }}</span>
                    <span class="font-medium text-gray-800">{{ PHP_VERSION }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b">
                    <span class="text-gray-600">{{ __('Total Invoices') }}</span>
                    <span class="font-medium text-gray-800">{{ number_format($stats['total_invoices']) }}</span>
                </div>
                <div class="flex justify-between items-center py-2">
                    <span class="text-gray-600">{{ __('Total Due') }}</span>
                    <span class="font-medium text-red-600">৳ {{ number_format($stats['total_due']) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Data -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Markets -->
        <div class="glass-card">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Markets') }}</h3>
            </div>
            <div class="divide-y">
                @forelse($recentMarkets as $market)
                    <div class="p-4 flex items-center justify-between hover:bg-white/50 transition">
                        <div>
                            <p class="font-medium text-gray-800">{{ $market->name }}</p>
                            <p class="text-sm text-gray-500">{{ $market->phone ?? 'No phone' }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $market->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ucfirst($market->status) }}
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">{{ __('No markets yet') }}</div>
                @endforelse
            </div>
            <div class="p-4 border-t">
                <a href="{{ route('admin.markets.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">{{ __('View all markets') }} →</a>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="glass-card">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent Users') }}</h3>
            </div>
            <div class="divide-y">
                @forelse($recentUsers as $user)
                    <div class="p-4 flex items-center justify-between hover:bg-white/50 transition">
                        <div>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                            {{ str_replace('_', ' ', ucfirst($user->role)) }}
                        </span>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">{{ __('No users yet') }}</div>
                @endforelse
            </div>
            <div class="p-4 border-t">
                <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">{{ __('View all users') }} →</a>
            </div>
        </div>
    </div>
</x-app-layout>
