<x-guest-layout>
    <div class="mb-6 text-center">
        <h1 class="text-xl font-semibold text-gray-900">{{ __('messages.auth.register_title') }}</h1>
        @if($plan)
            <p class="mt-1 text-sm text-gray-600">
                {{ __('messages.auth.register_trial_hint', ['days' => $plan->trial_days, 'plan' => $plan->getLocalizedName()]) }}
            </p>
        @endif
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="market_name" :value="__('messages.auth.market_name')" />
            <x-text-input id="market_name" class="block mt-1 w-full" type="text" name="market_name" :value="old('market_name')" required autofocus />
            <x-input-error :messages="$errors->get('market_name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="market_name_bn" :value="__('messages.auth.market_name_bn')" />
            <x-text-input id="market_name_bn" class="block mt-1 w-full" type="text" name="market_name_bn" :value="old('market_name_bn')" />
            <x-input-error :messages="$errors->get('market_name_bn')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="name" :value="__('messages.auth.owner_name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('messages.auth.phone')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" required placeholder="01XXXXXXXXX" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('messages.auth.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.auth.password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('messages.auth.confirm_password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('messages.auth.already_registered') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('messages.auth.start_trial') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
