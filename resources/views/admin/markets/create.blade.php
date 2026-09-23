<x-app-layout>
    <x-slot name="header">
        {{ __('Create Market') }}
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass-card p-6">
            <form action="{{ route('admin.markets.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Market Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full glass-input">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name Bangla -->
                    <div>
                        <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Market Name (Bangla)') }}</label>
                        <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn') }}"
                            class="w-full glass-input">
                        @error('name_bn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                        <textarea name="address" id="address" rows="2"
                            class="w-full glass-input">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address Bangla -->
                    <div>
                        <label for="address_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address (Bangla)') }}</label>
                        <textarea name="address_bn" id="address_bn" rows="2"
                            class="w-full glass-input">{{ old('address_bn') }}</textarea>
                        @error('address_bn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                                class="w-full glass-input">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}"
                                class="w-full glass-input">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Status') }} <span class="text-red-500">*</span></label>
                        <select name="status" id="status" required
                            class="w-full glass-input">
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Plan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Subscription plan') }}</label>
                            <select name="plan_id" id="plan_id" class="w-full glass-input">
                                <option value="">{{ __('No plan (unrestricted, legacy)') }}</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('plan_id', $plan->is_default ? $plan->id : null) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} — ৳{{ number_format($plan->monthly_price) }}/{{ __('mo') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="subscription_start" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Start with') }}</label>
                            <select name="subscription_start" id="subscription_start" class="w-full glass-input">
                                <option value="trial" {{ old('subscription_start', 'trial') === 'trial' ? 'selected' : '' }}>{{ __('Free trial') }}</option>
                                <option value="none" {{ old('subscription_start') === 'none' ? 'selected' : '' }}>{{ __('Plan only, record payment next') }}</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('Paid activations are recorded on the market\'s Subscription page.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.markets.index') }}" class="px-4 py-2 btn-secondary transition">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 btn-primary transition">
                        {{ __('Create Market') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
