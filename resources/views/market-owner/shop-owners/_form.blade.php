@php $editing = isset($shopOwner) && $shopOwner->exists; @endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">{{ __('shop_owners.name') }} <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="name" value="{{ old('name', $shopOwner->name ?? '') }}" required
               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name_bn" class="block text-sm font-medium text-gray-700">{{ __('shop_owners.name_bn') }}</label>
        <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $shopOwner->name_bn ?? '') }}"
               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
        @error('name_bn')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('shop_owners.phone') }} <span class="text-red-500">*</span></label>
        <input type="tel" name="phone" id="phone" value="{{ old('phone', $shopOwner->phone ?? '') }}" required placeholder="01XXXXXXXXX"
               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-500">{{ __('shop_owners.phone_hint') }}</p>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">{{ __('shop_owners.email') }}</label>
        <input type="email" name="email" id="email"
               value="{{ old('email', ($editing && !str_ends_with($shopOwner->email, '@bazarbill.local')) ? $shopOwner->email : '') }}"
               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-500">{{ __('shop_owners.email_hint') }}</p>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">
            {{ $editing ? __('shop_owners.new_password') : __('shop_owners.password') }}
            @unless($editing)<span class="text-red-500">*</span>@endunless
        </label>
        <input type="password" name="password" id="password" minlength="6" {{ $editing ? '' : 'required' }} autocomplete="new-password"
               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-gray-500">{{ $editing ? __('shop_owners.leave_blank_password') : __('shop_owners.password_hint') }}</p>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
            {{ __('shop_owners.confirm_password') }} @unless($editing)<span class="text-red-500">*</span>@endunless
        </label>
        <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" {{ $editing ? '' : 'required' }} autocomplete="new-password"
               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div>
        <label for="language_preference" class="block text-sm font-medium text-gray-700">{{ __('shop_owners.language') }}</label>
        <select name="language_preference" id="language_preference" class="mt-1 block w-full glass-input">
            <option value="bn" @selected(old('language_preference', $shopOwner->language_preference ?? 'bn') === 'bn')>বাংলা</option>
            <option value="en" @selected(old('language_preference', $shopOwner->language_preference ?? 'bn') === 'en')>English</option>
        </select>
    </div>

    @if($editing)
    <div class="flex items-start pt-6">
        <label class="flex items-center">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $shopOwner->is_active)) class="h-4 w-4 text-indigo-600 border-gray-300 rounded">
            <span class="ml-2 text-sm text-gray-700">{{ __('shop_owners.active_status') }}</span>
        </label>
    </div>
    @endif
</div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('shop_owners.assign_shops') }}</label>
    @if($shops->isEmpty())
        <p class="text-sm text-gray-500 p-3 border border-dashed border-gray-300 rounded-md">{{ __('shop_owners.no_free_shops') }}</p>
    @else
        <div class="border border-gray-300 rounded-md max-h-48 overflow-y-auto p-3 space-y-2">
            @foreach($shops as $shop)
            <label class="flex items-center">
                <input type="checkbox" name="shop_ids[]" value="{{ $shop->id }}"
                       @checked(in_array($shop->id, old('shop_ids', $assignedShopIds ?? [])))
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <span class="ml-2 text-sm text-gray-700">{{ $shop->shop_number }} @if($shop->floor)· {{ $shop->floor }}@endif · ৳{{ number_format($shop->rent_amount) }}</span>
            </label>
            @endforeach
        </div>
    @endif
    <p class="mt-1 text-xs text-gray-500">{{ __('shop_owners.assign_shops_hint') }}</p>
    @error('shop_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
