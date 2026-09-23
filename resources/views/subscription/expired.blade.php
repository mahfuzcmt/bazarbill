<x-guest-layout>
    <div class="text-center">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 00-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
            </svg>
        </div>
        <h1 class="text-xl font-semibold text-gray-900">
            {{ $market->subscription_status === 'trial' || $lastSubscription?->billing_cycle === 'trial'
                ? __('messages.subscription.trial_ended_title')
                : __('messages.subscription.expired_title') }}
        </h1>
        <p class="mt-2 text-sm text-gray-600">{{ __('messages.subscription.expired_body', ['market' => $market->getLocalizedName()]) }}</p>
    </div>

    <div class="mt-6 rounded-lg bg-slate-50 p-4 text-sm text-gray-700 space-y-1">
        @php $shownPlan = $lastSubscription?->plan ?? $market->plan; @endphp
        @if($shownPlan)
            <div class="flex justify-between"><span>{{ __('messages.subscription.plan') }}</span><span class="font-medium">{{ $shownPlan->getLocalizedName() }}</span></div>
        @endif
        @if($lastSubscription?->ends_at ?? $market->subscription_ends_at)
            <div class="flex justify-between"><span>{{ __('messages.subscription.ended_on') }}</span><span class="font-medium">{{ ($lastSubscription?->ends_at ?? $market->subscription_ends_at)->format('d M Y') }}</span></div>
        @endif
        @if($market->plan)
            <div class="flex justify-between"><span>{{ __('messages.subscription.monthly_price') }}</span><span class="font-medium">৳ {{ number_format($market->plan->monthly_price) }}</span></div>
        @endif
    </div>

    <p class="mt-6 text-sm text-gray-600 text-center">{{ __('messages.subscription.how_to_renew') }}</p>
    @if(config('services.support.phone'))
        <p class="mt-1 text-center text-lg font-semibold text-indigo-700">{{ config('services.support.phone') }}</p>
    @endif

    <div class="mt-6 flex items-center justify-between">
        <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('messages.subscription.my_profile') }}</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-primary-button>{{ __('messages.logout') }}</x-primary-button>
        </form>
    </div>
</x-guest-layout>
