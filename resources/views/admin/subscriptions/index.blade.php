<x-app-layout>
    <x-slot name="header">{{ __('Subscriptions') }}</x-slot>

    @php
        $statusClasses = [
            'trial' => 'bg-blue-100 text-blue-800',
            'active' => 'bg-green-100 text-green-800',
            'expired' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-700',
        ];
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <a href="{{ route('admin.subscriptions.index', ['status' => 'active']) }}" class="glass-card p-4 hover:bg-white/60 transition">
                <p class="text-sm text-gray-500">{{ __('Paying') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ $counts['active'] }}</p>
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'trial']) }}" class="glass-card p-4 hover:bg-white/60 transition">
                <p class="text-sm text-gray-500">{{ __('On trial') }}</p>
                <p class="text-2xl font-bold text-blue-600">{{ $counts['trial'] }}</p>
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'expiring']) }}" class="glass-card p-4 hover:bg-white/60 transition">
                <p class="text-sm text-gray-500">{{ __('Expiring in 7 days') }}</p>
                <p class="text-2xl font-bold {{ $counts['expiring'] ? 'text-yellow-600' : 'text-gray-800' }}">{{ $counts['expiring'] }}</p>
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}" class="glass-card p-4 hover:bg-white/60 transition">
                <p class="text-sm text-gray-500">{{ __('Expired / cancelled') }}</p>
                <p class="text-2xl font-bold {{ $counts['expired'] ? 'text-red-600' : 'text-gray-800' }}">{{ $counts['expired'] }}</p>
            </a>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Monthly recurring revenue') }}</p>
                <p class="text-2xl font-bold text-gray-800">৳ {{ number_format($counts['mrr']) }}</p>
            </div>
        </div>

        <div class="glass-card">
            <div class="p-6 border-b">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Search market') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="w-full glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Status') }}</label>
                        <select name="status" class="glass-input w-full sm:w-44">
                            <option value="">{{ __('All') }}</option>
                            @foreach(['trial' => __('Trial'), 'active' => __('Active'), 'expiring' => __('Expiring soon'), 'expired' => __('Expired'), 'cancelled' => __('Cancelled'), 'none' => __('No plan')] as $v => $l)
                                <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Plan') }}</label>
                        <select name="plan_id" class="glass-input w-full sm:w-44">
                            <option value="">{{ __('All') }}</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(request('plan_id') == $plan->id)>{{ $plan->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 btn-primary transition">{{ __('Filter') }}</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Market') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Plan') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Ends') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($markets as $market)
                            <tr class="hover:bg-white/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $market->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $market->phone ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-800">{{ $market->plan?->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if($market->subscription_status)
                                        <span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$market->subscription_status] ?? 'bg-gray-100 text-gray-700' }}">{{ ucfirst($market->subscription_status) }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">{{ __('No plan') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @if($market->subscription_ends_at)
                                        @php $days = $market->subscriptionDaysRemaining(); @endphp
                                        <div class="{{ !$market->hasActiveSubscription() ? 'text-red-600' : ($days <= 7 ? 'text-yellow-700' : 'text-gray-800') }}">
                                            {{ $market->subscription_ends_at->format('d M Y') }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $market->hasActiveSubscription() ? __(':days days left', ['days' => $days]) : __('Locked out') }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <a href="{{ route('admin.markets.subscription', $market) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('Manage') }}</a>
                                    <a href="{{ route('admin.markets.show', $market) }}" class="text-blue-600 hover:text-blue-900">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">{{ __('No markets found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($markets->hasPages())
                <div class="px-6 py-4 border-t">{{ $markets->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
