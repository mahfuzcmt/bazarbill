<x-app-layout>
    <x-slot name="header">{{ __('Subscription') }}: {{ $market->name }}</x-slot>

    @php
        $statusClasses = [
            'trial' => 'bg-blue-100 text-blue-800',
            'active' => 'bg-green-100 text-green-800',
            'expired' => 'bg-red-100 text-red-800',
            'cancelled' => 'bg-gray-100 text-gray-700',
        ];
    @endphp

    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Current plan') }}</p>
                <p class="text-xl font-bold text-gray-800">{{ $market->plan?->name ?? __('None') }}</p>
                @if($market->subscription_status)
                    <span class="mt-1 inline-block px-2 py-0.5 text-xs rounded-full {{ $statusClasses[$market->subscription_status] ?? '' }}">{{ ucfirst($market->subscription_status) }}</span>
                @endif
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Access until') }}</p>
                @if($market->subscription_ends_at)
                    <p class="text-xl font-bold {{ $market->hasActiveSubscription() ? 'text-gray-800' : 'text-red-600' }}">{{ $market->subscription_ends_at->format('d M Y') }}</p>
                    <p class="text-xs text-gray-500">{{ $market->hasActiveSubscription() ? __(':days days left', ['days' => $market->subscriptionDaysRemaining()]) : __('Locked out') }}</p>
                @else
                    <p class="text-xl font-bold text-gray-500">{{ __('Unlimited (no plan)') }}</p>
                @endif
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Shops used') }}</p>
                <p class="text-xl font-bold text-gray-800">{{ $shopCount }} / {{ $market->shopLimit() ?? '∞' }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('SMS credits') }}</p>
                <p class="text-xl font-bold text-indigo-600">{{ number_format($market->sms_credits) }}</p>
                <a href="{{ route('admin.markets.sms-credits', $market) }}" class="text-xs text-indigo-600 hover:underline">{{ __('Manage') }}</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="space-y-6 lg:col-span-1">
                <!-- Activate / renew -->
                <div class="glass-card">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('Activate or renew') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Record the payment you received offline. Renewing the same plan extends from the current end date.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.markets.subscription.store', $market) }}" class="p-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Plan') }}</label>
                            <select name="plan_id" id="plan_id" class="w-full glass-input" required>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" data-monthly="{{ $plan->monthly_price }}" data-yearly="{{ $plan->yearly_price ?? '' }}"
                                            @selected(old('plan_id', $market->plan_id) == $plan->id)>
                                        {{ $plan->name }} — ৳{{ number_format($plan->monthly_price) }}/{{ __('mo') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Billing cycle') }}</label>
                            <select name="billing_cycle" id="billing_cycle" class="w-full glass-input">
                                <option value="monthly" @selected(old('billing_cycle', 'monthly') === 'monthly')>{{ __('Monthly') }}</option>
                                <option value="yearly" @selected(old('billing_cycle') === 'yearly')>{{ __('Yearly') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Amount received (৳)') }}</label>
                            <input type="number" name="amount_paid" id="amount_paid" min="0" step="1" value="{{ old('amount_paid', $market->plan?->monthly_price ?? 0) }}" required class="w-full glass-input">
                            @error('amount_paid')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Method') }}</label>
                                <select name="payment_method" class="w-full glass-input">
                                    <option value="">—</option>
                                    @foreach(\App\Models\Subscription::PAYMENT_METHODS as $m)
                                        <option value="{{ $m }}" @selected(old('payment_method') === $m)>{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Reference') }}</label>
                                <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" maxlength="100" class="w-full glass-input" placeholder="TrxID">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Note') }}</label>
                            <textarea name="notes" rows="2" maxlength="500" class="w-full glass-input">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 btn-primary transition">{{ __('Activate subscription') }}</button>
                    </form>
                </div>

                @if(!$market->hasActiveSubscription() || $market->subscription_ends_at === null)
                <div class="glass-card p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('Start a free trial instead') }}</h3>
                    <form method="POST" action="{{ route('admin.markets.subscription.trial', $market) }}" class="flex gap-2">
                        @csrf
                        <select name="plan_id" class="flex-1 glass-input">
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->trial_days }} {{ __('days') }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 btn-secondary transition">{{ __('Start trial') }}</button>
                    </form>
                </div>
                @endif

                @if($market->hasActiveSubscription() && $market->subscription_ends_at !== null)
                <div class="glass-card p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">{{ __('Cancel subscription') }}</h3>
                    <p class="text-xs text-gray-500 mb-3">{{ __('The market will be locked out immediately. Use this for non-payment or abuse.') }}</p>
                    <form method="POST" action="{{ route('admin.markets.subscription.cancel', $market) }}" onsubmit="return confirm('{{ __('Cancel this subscription and lock the market out?') }}')" class="flex gap-2">
                        @csrf
                        <input type="text" name="reason" maxlength="500" placeholder="{{ __('Reason') }}" class="flex-1 glass-input">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">{{ __('Cancel') }}</button>
                    </form>
                </div>
                @endif
            </div>

            <!-- History -->
            <div class="glass-card lg:col-span-2">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Subscription history') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="glass-thead">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Period') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Plan') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Paid') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Payment') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('By') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70">
                            @forelse($history as $sub)
                                <tr class="hover:bg-white/50 transition">
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $sub->starts_at->format('d M Y') }} – {{ $sub->ends_at->format('d M Y') }}
                                        <div class="text-xs text-gray-500">{{ ucfirst($sub->billing_cycle) }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800">{{ $sub->plan?->name }}</td>
                                    <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded-full {{ $statusClasses[$sub->status] ?? '' }}">{{ ucfirst($sub->status) }}</span></td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-800">৳ {{ number_format($sub->amount_paid) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ $sub->payment_method ? ucfirst($sub->payment_method) : '—' }}
                                        @if($sub->payment_reference)<div class="text-xs">{{ $sub->payment_reference }}</div>@endif
                                        @if($sub->notes)<div class="text-xs text-gray-500">{{ $sub->notes }}</div>@endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $sub->creator?->name ?? __('Self-service') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">{{ __('No subscription history yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 btn-secondary transition">{{ __('All subscriptions') }}</a>
            <a href="{{ route('admin.markets.show', $market) }}" class="px-4 py-2 btn-secondary transition">{{ __('Market details') }}</a>
        </div>
    </div>

    <script>
        (function () {
            const plan = document.getElementById('plan_id');
            const cycle = document.getElementById('billing_cycle');
            const amount = document.getElementById('amount_paid');
            if (!plan || !cycle || !amount) return;
            function refresh() {
                const opt = plan.options[plan.selectedIndex];
                const price = cycle.value === 'yearly' ? opt.dataset.yearly : opt.dataset.monthly;
                if (price !== '' && price !== undefined) amount.value = Math.round(price);
            }
            plan.addEventListener('change', refresh);
            cycle.addEventListener('change', refresh);
        })();
    </script>
</x-app-layout>
