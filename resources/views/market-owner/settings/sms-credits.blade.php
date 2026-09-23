<x-app-layout>
    <x-slot name="header">{{ __('settings.sms_credit_history') }}</x-slot>

    <div class="max-w-5xl space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('settings.sms_credit_balance') }}</p>
                <p class="text-3xl font-bold {{ $market->hasLowSmsCredits() ? 'text-red-600' : 'text-indigo-600' }}">{{ number_format($market->sms_credits) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('settings.used_this_month') }}</p>
                <p class="text-3xl font-bold text-gray-800">{{ number_format($usedThisMonth) }}</p>
            </div>
            <div class="glass-card p-4 col-span-2 sm:col-span-1">
                <p class="text-xs text-gray-600">{{ __('settings.sms_credits_hint') }}</p>
            </div>
        </div>

        @if($market->hasLowSmsCredits())
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                {{ __('settings.sms_credits_low') }}
            </div>
        @endif

        <div class="glass-card overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('settings.sms_credit_history') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('settings.sms_credit_history_desc') }}</p>
                </div>
                <form method="GET">
                    <select name="type" class="glass-input text-sm" onchange="this.form.submit()">
                        <option value="">{{ __('messages.all') }}</option>
                        @foreach(__('settings.credit_types') as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('settings.credit_type') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('settings.sms_credit_balance') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-white/50 transition">
                                <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $tx->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800">{{ __('settings.credit_types.' . $tx->type) }}</td>
                                <td class="px-4 py-3 text-right font-semibold {{ $tx->isCredit() ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $tx->isCredit() ? '+' : '' }}{{ number_format($tx->amount) }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-800">{{ number_format($tx->balance_after) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    @if($tx->reference)<div class="font-medium text-gray-800">{{ $tx->reference }}</div>@endif
                                    @if($tx->note)<div class="text-xs">{{ $tx->note }}</div>@endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-gray-500">{{ __('messages.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t">{{ $transactions->links() }}</div>
            @endif
        </div>

        <a href="{{ route('market-owner.settings.index') }}" class="inline-block px-4 py-2 btn-secondary transition">{{ __('messages.back') }}</a>
    </div>
</x-app-layout>
