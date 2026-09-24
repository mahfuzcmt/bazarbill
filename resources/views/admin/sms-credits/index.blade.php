<x-app-layout>
    <x-slot name="header">
        {{ __('SMS Credits') }}
    </x-slot>

    <div class="space-y-6">

        <!-- Totals -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Credits outstanding (all markets)') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totals['credits_outstanding']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Credits used this month') }}</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($totals['used_this_month']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Markets running low') }}</p>
                <p class="text-2xl font-bold {{ $totals['low_markets'] ? 'text-red-600' : 'text-gray-800' }}">{{ $totals['low_markets'] }}</p>
                <p class="text-xs text-gray-500">{{ __('at or below :n credits', ['n' => \App\Models\Setting::smsLowCreditThreshold()]) }}</p>
            </div>
        </div>

        <div class="glass-card">
            <div class="p-6 border-b">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Search market') }}</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Market name') }}"
                               class="w-full glass-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Gateway') }}</label>
                        <select name="gateway" class="glass-input w-full sm:w-44">
                            <option value="">{{ __('All') }}</option>
                            <option value="platform" @selected(request('gateway') === 'platform')>{{ __('Platform (credits)') }}</option>
                            <option value="own" @selected(request('gateway') === 'own')>{{ __('Own API key') }}</option>
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 pb-2">
                        <input type="checkbox" name="low_only" value="1" @checked(request()->boolean('low_only')) class="rounded border-gray-300">
                        {{ __('Low balance only') }}
                    </label>
                    <button type="submit" class="px-4 py-2 btn-primary transition">{{ __('Filter') }}</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Market') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Gateway') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Balance') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Sent this month') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Credits used this month') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($markets as $market)
                            <tr class="hover:bg-white/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $market->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $market->status === 'active' ? __('Active') : __('Inactive') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($market->usesPlatformSms())
                                        <span class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ __('Platform') }}</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">{{ __('Own key') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold {{ $market->hasLowSmsCredits() ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ number_format($market->sms_credits) }}
                                </td>
                                <td class="px-6 py-4 text-right text-gray-700">{{ number_format($market->sms_sent_this_month) }}</td>
                                <td class="px-6 py-4 text-right text-gray-700">{{ number_format($market->credits_used_this_month ?? 0) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                    <a href="{{ route('admin.markets.sms-credits', $market) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('Manage credits') }}</a>
                                    <a href="{{ route('admin.markets.show', $market) }}" class="text-blue-600 hover:text-blue-900">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">{{ __('No markets found.') }}</td>
                            </tr>
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
