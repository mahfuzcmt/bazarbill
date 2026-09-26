<x-app-layout>
    <x-slot name="header">{{ __('mymarkets.title') }}</x-slot>

    <div class="max-w-4xl space-y-6">
        <p class="text-sm text-gray-600">{{ __('mymarkets.subtitle') }}</p>

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($markets as $m)
            <div class="glass-card p-5 {{ $m->id === auth()->user()->market_id ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900 truncate">{{ $m->getLocalizedName() }}</h3>
                        <p class="text-xs text-gray-500">{{ $m->shops_count }} {{ __('mymarkets.shops') }} · {{ __('mymarkets.plan') }}: {{ $m->plan?->getLocalizedName() ?? __('mymarkets.no_plan') }}
                            @if($m->subscription_ends_at) · {{ __('mymarkets.valid_until', ['date' => $m->subscription_ends_at->format('d M Y')]) }}@endif
                        </p>
                    </div>
                    @if($m->id === auth()->user()->market_id)
                        <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 whitespace-nowrap">{{ __('mymarkets.current') }}</span>
                    @else
                        <form method="POST" action="{{ route('market.switch', $m) }}">@csrf
                            <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">{{ __('mymarkets.open') }}</button>
                        </form>
                    @endif
                </div>
                @if(!$m->hasActiveSubscription())
                    <p class="mt-2 text-xs text-red-600">{{ __('settings.status_expired') }}</p>
                @endif
            </div>
            @endforeach
        </div>

        @if($plan)
        <form method="POST" action="{{ route('market-owner.markets.store') }}" class="glass-card overflow-hidden">
            @csrf
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('mymarkets.add_title') }}</h3>
                <p class="text-sm text-gray-500">{{ __('mymarkets.add_hint', ['days' => $plan->trial_days]) }}</p>
            </div>
            <div class="px-4 sm:px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full glass-input">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name_bn" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.name_bn') }}</label>
                    <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn') }}" class="mt-1 block w-full glass-input">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.phone') }}</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="mt-1 block w-full glass-input" placeholder="01XXXXXXXXX">
                </div>
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.address') }}</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}" class="mt-1 block w-full glass-input">
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4 bg-white/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">{{ __('mymarkets.create') }}</button>
            </div>
        </form>
        @endif
    </div>
</x-app-layout>
