<x-app-layout>
    <x-slot name="header">{{ __('shops.add_shop') }}</x-slot>

    <div class="max-w-3xl">
        <form action="{{ route('market-owner.shops.store') }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Shop Number -->
                <div>
                    <label for="shop_number" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.shop_number') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="shop_number" id="shop_number" value="{{ old('shop_number') }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('shop_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Floor -->
                <div>
                    <label for="floor" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.floor') }}
                    </label>
                    <input type="text" name="floor" id="floor" value="{{ old('floor') }}"
                           placeholder="{{ __('shops.floor_placeholder') }}"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('floor')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Shop Type -->
                <div>
                    <label for="shop_type" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.shop_type') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="shop_type" id="shop_type" required
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('shops.select_type') }}</option>
                        <option value="general" {{ old('shop_type') === 'general' ? 'selected' : '' }}>{{ __('shops.type_general') }}</option>
                        <option value="food" {{ old('shop_type') === 'food' ? 'selected' : '' }}>{{ __('shops.type_food') }}</option>
                        <option value="clothing" {{ old('shop_type') === 'clothing' ? 'selected' : '' }}>{{ __('shops.type_clothing') }}</option>
                        <option value="electronics" {{ old('shop_type') === 'electronics' ? 'selected' : '' }}>{{ __('shops.type_electronics') }}</option>
                        <option value="jewelry" {{ old('shop_type') === 'jewelry' ? 'selected' : '' }}>{{ __('shops.type_jewelry') }}</option>
                        <option value="pharmacy" {{ old('shop_type') === 'pharmacy' ? 'selected' : '' }}>{{ __('shops.type_pharmacy') }}</option>
                        <option value="other" {{ old('shop_type') === 'other' ? 'selected' : '' }}>{{ __('shops.type_other') }}</option>
                    </select>
                    @error('shop_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Area -->
                <div>
                    <label for="area_sqft" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.area_sqft') }}
                    </label>
                    <input type="number" name="area_sqft" id="area_sqft" value="{{ old('area_sqft') }}" min="0"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('area_sqft')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rent Amount -->
                <div>
                    <label for="rent_amount" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.rent_amount') }} (৳) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="rent_amount" id="rent_amount" value="{{ old('rent_amount') }}" required min="0" step="0.01"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('rent_amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Advance Deposit -->
                <div>
                    <label for="advance_deposit" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.advance_deposit') }} (৳)
                    </label>
                    <input type="number" name="advance_deposit" id="advance_deposit" value="{{ old('advance_deposit', 0) }}" min="0" step="0.01"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('advance_deposit')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Collector -->
                <div>
                    <label for="collector_id" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.assigned_collector') }}
                    </label>
                    <select name="collector_id" id="collector_id"
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('shops.select_collector') }}</option>
                        @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}" {{ old('collector_id') == $collector->id ? 'selected' : '' }}>
                            {{ $collector->getLocalizedName() }}
                        </option>
                        @endforeach
                    </select>
                    @error('collector_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        {{ __('shops.status') }}
                    </label>
                    <select name="status" id="status"
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('shops.status_active') }}</option>
                        <option value="vacant" {{ old('status') === 'vacant' ? 'selected' : '' }}>{{ __('shops.status_vacant') }}</option>
                    </select>
                    @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Shop Owner Section -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('shops.owner_information') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Owner Name -->
                    <div>
                        <label for="owner_name" class="block text-sm font-medium text-gray-700">
                            {{ __('shops.owner_name') }}
                        </label>
                        <input type="text" name="owner_name" id="owner_name" value="{{ old('owner_name') }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('owner_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Owner Phone -->
                    <div>
                        <label for="owner_phone" class="block text-sm font-medium text-gray-700">
                            {{ __('shops.owner_phone') }}
                        </label>
                        <input type="tel" name="owner_phone" id="owner_phone" value="{{ old('owner_phone') }}"
                               placeholder="01XXXXXXXXX"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('owner_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Owner Email -->
                    <div>
                        <label for="owner_email" class="block text-sm font-medium text-gray-700">
                            {{ __('shops.owner_email') }}
                        </label>
                        <input type="email" name="owner_email" id="owner_email" value="{{ old('owner_email') }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('owner_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <p class="mt-2 text-sm text-gray-500">
                    {{ __('shops.owner_account_note') }}
                </p>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    {{ __('shops.notes') }}
                </label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.shops.index') }}"
                   class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('shops.create_shop') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
