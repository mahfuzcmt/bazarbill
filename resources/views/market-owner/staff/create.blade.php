<x-app-layout>
    <x-slot name="header">{{ __('staff.add_staff') }}</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('market-owner.staff.store') }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        {{ __('staff.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">
                        {{ __('staff.phone') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                           placeholder="01XXXXXXXXX"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        {{ __('staff.email') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        {{ __('staff.password') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password" id="password" required minlength="6"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('staff.password_hint') }}</p>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        {{ __('staff.confirm_password') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Assign Shops -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('staff.assign_shops') }}
                </label>
                <div class="border border-gray-300 rounded-md max-h-48 overflow-y-auto p-3 space-y-2">
                    @foreach($shops as $shop)
                    <label class="flex items-center">
                        <input type="checkbox" name="shop_ids[]" value="{{ $shop->id }}"
                               {{ in_array($shop->id, old('shop_ids', [])) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">
                            {{ $shop->shop_number }} - {{ $shop->shopOwner?->getLocalizedName() ?? __('shops.no_owner') }}
                        </span>
                    </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __('staff.assign_shops_hint') }}</p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.staff.index') }}"
                   class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('staff.create_staff') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
