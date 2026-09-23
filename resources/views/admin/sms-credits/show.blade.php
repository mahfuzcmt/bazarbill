<x-app-layout>
    <x-slot name="header">
        {{ __('SMS Credits') }}: {{ $market->name }}
    </x-slot>

    @php
        $typeLabels = [
            'subscription' => __('Subscription'),
            'recharge' => __('Offline recharge'),
            'adjustment' => __('Adjustment'),
            'usage' => __('SMS usage'),
            'refund' => __('Refund'),
        ];
        $typeClasses = [
            'subscription' => 'bg-indigo-100 text-indigo-800',
            'recharge' => 'bg-green-100 text-green-800',
            'adjustment' => 'bg-yellow-100 text-yellow-800',
            'usage' => 'bg-gray-100 text-gray-700',
            'refund' => 'bg-blue-100 text-blue-800',
        ];
    @endphp

    <div class="space-y-6">

        <!-- Balance + summary -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Current balance') }}</p>
                <p class="text-3xl font-bold {{ $market->hasLowSmsCredits() ? 'text-red-600' : 'text-gray-800' }}">{{ number_format($market->sms_credits) }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    @if($market->usesPlatformSms())
                        {{ __('Sends through platform gateway') }}
                    @else
                        {{ __('Uses own API key; credits are not consumed') }}
                    @endif
                </p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total allocated') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ number_format($summary['allocated']) }}</p>
                @if($summary['deducted'])
                    <p class="text-xs text-red-600">-{{ number_format($summary['deducted']) }} {{ __('deducted manually') }}</p>
                @endif
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total used (net of refunds)') }}</p>
                <p class="text-2xl font-bold text-indigo-600">{{ number_format($summary['used']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('SMS sent this month') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($summary['sent_this_month']) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Adjust form -->
            <div class="glass-card lg:col-span-1">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Add or deduct credits') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Use this for plan allocations and offline recharges (bKash, Nagad, cash).') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.markets.sms-credits.store', $market) }}" class="p-6 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Operation') }}</label>
                        <div class="flex gap-4">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="operation" value="add" @checked(old('operation', 'add') === 'add') class="border-gray-300">
                                {{ __('Add credits') }}
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="radio" name="operation" value="deduct" @checked(old('operation') === 'deduct') class="border-gray-300">
                                {{ __('Deduct credits') }}
                            </label>
                        </div>
                        @error('operation')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reason') }}</label>
                        <select name="type" id="type" class="w-full glass-input">
                            <option value="subscription" @selected(old('type') === 'subscription')>{{ __('Subscription plan allocation') }}</option>
                            <option value="recharge" @selected(old('type', 'recharge') === 'recharge')>{{ __('Offline recharge (bKash / Nagad / cash)') }}</option>
                            <option value="adjustment" @selected(old('type') === 'adjustment')>{{ __('Manual adjustment / correction') }}</option>
                        </select>
                        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Number of SMS credits') }}</label>
                        <input type="number" name="amount" id="amount" min="1" step="1" value="{{ old('amount') }}" required
                               class="w-full glass-input" placeholder="500">
                        @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="reference" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reference') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                        <input type="text" name="reference" id="reference" value="{{ old('reference') }}" maxlength="100"
                               class="w-full glass-input" placeholder="{{ __('bKash TrxID, receipt no, plan name') }}">
                        @error('reference')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Note') }} <span class="text-gray-400 font-normal">({{ __('optional') }})</span></label>
                        <textarea name="note" id="note" rows="2" maxlength="500" class="w-full glass-input">{{ old('note') }}</textarea>
                        @error('note')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="w-full px-4 py-2 btn-primary transition">{{ __('Apply') }}</button>
                </form>
            </div>

            <!-- Ledger -->
            <div class="glass-card lg:col-span-2">
                <div class="p-6 border-b flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Credit history') }}</h3>
                    <form method="GET" class="flex gap-2">
                        <select name="type" class="glass-input text-sm" onchange="this.form.submit()">
                            <option value="">{{ __('All types') }}</option>
                            @foreach($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="glass-thead">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Balance') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reference / Note') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('By') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70">
                            @forelse($transactions as $tx)
                                <tr class="hover:bg-white/50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $tx->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $typeClasses[$tx->type] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ $typeLabels[$tx->type] ?? ucfirst($tx->type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold {{ $tx->isCredit() ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $tx->isCredit() ? '+' : '' }}{{ number_format($tx->amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-800">{{ number_format($tx->balance_after) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        @if($tx->reference)<div class="font-medium">{{ $tx->reference }}</div>@endif
                                        @if($tx->note)<div class="text-xs text-gray-500">{{ $tx->note }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $tx->creator?->name ?? __('System') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">{{ __('No credit transactions yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                    <div class="px-6 py-4 border-t">{{ $transactions->links() }}</div>
                @endif
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.sms-credits.index') }}" class="px-4 py-2 btn-secondary transition">{{ __('All markets') }}</a>
            <a href="{{ route('admin.markets.show', $market) }}" class="px-4 py-2 btn-secondary transition">{{ __('Market details') }}</a>
        </div>
    </div>
</x-app-layout>
