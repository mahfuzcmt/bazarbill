<x-app-layout>
    <x-slot name="header">
        {{ __('User Details') }}: {{ $user->name }}
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass-card p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h2>
                    @if($user->name_bn)
                        <p class="text-lg text-gray-600">{{ $user->name_bn }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 text-sm rounded-full
                        {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $user->role === 'market_owner' ? 'bg-indigo-100 text-indigo-800' : '' }}
                        {{ $user->role === 'collector' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $user->role === 'shop_owner' ? 'bg-green-100 text-green-800' : '' }}">
                        {{ str_replace('_', ' ', ucfirst($user->role)) }}
                    </span>
                    <span class="px-3 py-1 text-sm rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->is_active ? __('Active') : __('Inactive') }}
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ __('Email') }}</h4>
                        <p class="text-gray-800">{{ $user->email }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ __('Phone') }}</h4>
                        <p class="text-gray-800">{{ $user->phone ?? '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ __('Market') }}</h4>
                        @if($user->market)
                            <p class="text-gray-800">
                                <a href="{{ route('admin.markets.show', $user->market) }}" class="text-indigo-600 hover:underline">{{ $user->market->name }}</a>
                            </p>
                        @else
                            <p class="text-gray-800">{{ __('No Market Assigned') }}</p>
                        @endif
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ __('Language') }}</h4>
                        <p class="text-gray-800">{{ $user->language_preference === 'bn' ? 'বাংলা' : 'English' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ __('Created') }}</h4>
                        <p class="text-gray-800">{{ $user->created_at->format('d M Y, h:i A') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">{{ __('Last Updated') }}</h4>
                        <p class="text-gray-800">{{ $user->updated_at->format('d M Y, h:i A') }}</p>
                    </div>
                </div>
            </div>

            @if($user->market && $user->role === 'market_owner')
                <div class="mt-6 pt-6 border-t">
                    <h3 class="text-sm font-medium text-gray-500 mb-3">{{ __('Market account') }}: {{ $user->market->name }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="p-4 rounded-lg bg-indigo-50">
                            <p class="text-xs text-indigo-700">{{ __('SMS credit balance') }}</p>
                            <p class="text-2xl font-bold text-indigo-800">{{ number_format($user->market->sms_credits) }}</p>
                            <a href="{{ route('admin.markets.sms-credits', $user->market) }}" class="text-sm font-medium text-indigo-700 hover:underline">{{ __('Add / deduct credits') }} &rarr;</a>
                        </div>
                        <div class="p-4 rounded-lg bg-green-50">
                            <p class="text-xs text-green-700">{{ __('Plan') }}</p>
                            <p class="text-2xl font-bold text-green-800">{{ $user->market->plan?->name ?? __('None') }}</p>
                            <a href="{{ route('admin.markets.subscription', $user->market) }}" class="text-sm font-medium text-green-700 hover:underline">{{ __('Manage subscription') }} &rarr;</a>
                        </div>
                        <div class="p-4 rounded-lg bg-gray-50">
                            <p class="text-xs text-gray-600">{{ __('Access until') }}</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $user->market->subscription_ends_at?->format('d M Y') ?? '—' }}</p>
                            <span class="text-sm text-gray-600">{{ $user->market->subscription_status ? ucfirst($user->market->subscription_status) : __('No plan') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-6 pt-6 border-t flex items-center gap-4">
                <a href="{{ route('admin.users.edit', $user) }}" class="px-4 py-2 btn-primary transition">
                    {{ __('Edit User') }}
                </a>
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 {{ $user->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} text-white rounded-lg transition">
                        {{ $user->is_active ? __('Deactivate') : __('Activate') }}
                    </button>
                </form>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 btn-secondary transition">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
