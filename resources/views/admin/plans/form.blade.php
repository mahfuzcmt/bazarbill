<x-app-layout>
    <x-slot name="header">{{ $plan->exists ? __('Edit Plan') : __('Create Plan') }}</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="glass-card p-6">
            <form action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" method="POST">
                @csrf
                @if($plan->exists) @method('PUT') @endif

                <div class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Plan Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $plan->name) }}" required class="w-full glass-input">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Plan Name (Bangla)') }}</label>
                            <input type="text" name="name_bn" value="{{ old('name_bn', $plan->name_bn) }}" class="w-full glass-input">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <textarea name="description" rows="2" class="w-full glass-input">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Monthly price (৳)') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="monthly_price" min="0" step="1" value="{{ old('monthly_price', $plan->monthly_price ?? 0) }}" required class="w-full glass-input">
                            @error('monthly_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Yearly price (৳)') }} <span class="text-gray-400 font-normal">({{ __('leave empty to not offer yearly') }})</span></label>
                            <input type="number" name="yearly_price" min="0" step="1" value="{{ old('yearly_price', $plan->yearly_price) }}" class="w-full glass-input">
                            @error('yearly_price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Shop limit') }} <span class="text-gray-400 font-normal">({{ __('empty = unlimited') }})</span></label>
                            <input type="number" name="shop_limit" min="1" step="1" value="{{ old('shop_limit', $plan->shop_limit) }}" class="w-full glass-input">
                            @error('shop_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('SMS credits per month') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="sms_credits_per_month" min="0" step="1" value="{{ old('sms_credits_per_month', $plan->sms_credits_per_month ?? 0) }}" required class="w-full glass-input">
                            @error('sms_credits_per_month')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Trial days') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="trial_days" min="0" max="365" value="{{ old('trial_days', $plan->trial_days ?? 14) }}" required class="w-full glass-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Trial SMS credits') }} <span class="text-red-500">*</span></label>
                            <input type="number" name="trial_sms_credits" min="0" value="{{ old('trial_sms_credits', $plan->trial_sms_credits ?? 20) }}" required class="w-full glass-input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sort order') }}</label>
                            <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" class="w-full glass-input">
                        </div>
                    </div>

                    <div>
                        <p class="block text-sm font-medium text-gray-700 mb-2">{{ __('Features') }}</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach(\App\Models\Plan::FEATURES as $key => $label)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                    <input type="hidden" name="features[{{ $key }}]" value="0">
                                    <input type="checkbox" name="features[{{ $key }}]" value="1" class="rounded border-gray-300"
                                           @checked(old('features.' . $key, $plan->features[$key] ?? false))>
                                    {{ __($label) }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $plan->is_active ?? true))>
                            {{ __('Active (can be assigned)') }}
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300" @checked(old('is_default', $plan->is_default ?? false))>
                            {{ __('Default plan for new signups') }}
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 btn-secondary transition">{{ __('Cancel') }}</a>
                    <button type="submit" class="px-4 py-2 btn-primary transition">{{ $plan->exists ? __('Save Plan') : __('Create Plan') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
