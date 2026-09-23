<x-app-layout>
    <x-slot name="header">{{ __('Subscription Plans') }}</x-slot>

    <div class="glass-card">
        <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Plans') }}</h3>
                <p class="text-sm text-gray-500">{{ __('The default plan is the one new self-service signups start their trial on.') }}</p>
            </div>
            <a href="{{ route('admin.plans.create') }}" class="inline-flex items-center px-4 py-2 btn-primary transition">{{ __('Add Plan') }}</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="glass-thead">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Plan') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Monthly') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Yearly') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Shop limit') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('SMS / month') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Trial') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Markets') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $plan->name }}
                                    @if($plan->is_default)<span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-800">{{ __('Default') }}</span>@endif
                                </div>
                                @if($plan->name_bn)<div class="text-sm text-gray-500">{{ $plan->name_bn }}</div>@endif
                                @if($plan->features)
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($plan->features as $feature => $enabled)
                                            @if($enabled)<span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">{{ \App\Models\Plan::FEATURES[$feature] ?? $feature }}</span>@endif
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">৳ {{ number_format($plan->monthly_price) }}</td>
                            <td class="px-6 py-4 text-right">{{ $plan->yearly_price !== null ? '৳ ' . number_format($plan->yearly_price) : '—' }}</td>
                            <td class="px-6 py-4 text-right">{{ $plan->shop_limit ?? __('Unlimited') }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($plan->sms_credits_per_month) }}</td>
                            <td class="px-6 py-4 text-right text-sm text-gray-600">{{ $plan->trial_days }} {{ __('days') }} / {{ $plan->trial_sms_credits }} SMS</td>
                            <td class="px-6 py-4 text-right">{{ $plan->active_markets_count }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs rounded-full {{ $plan->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">{{ $plan->is_active ? __('Active') : __('Inactive') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                <a href="{{ route('admin.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('Edit') }}</a>
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Delete this plan?') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-12 text-center text-gray-500">{{ __('No plans yet.') }} <a href="{{ route('admin.plans.create') }}" class="text-indigo-600 hover:underline">{{ __('Create one?') }}</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
