<x-app-layout>
    <x-slot name="header">
        {{ __('Edit User') }}: {{ $user->name }}
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass-card p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                class="w-full glass-input">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name (Bangla)') }}</label>
                            <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $user->name_bn) }}"
                                class="w-full glass-input">
                            @error('name_bn')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Email & Phone -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }} <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                class="w-full glass-input">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                                class="w-full glass-input">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New Password') }}</label>
                            <input type="password" name="password" id="password"
                                class="w-full glass-input"
                                placeholder="{{ __('Leave blank to keep current') }}">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full glass-input">
                        </div>
                    </div>

                    <!-- Role & Market -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Role') }} <span class="text-red-500">*</span></label>
                            <select name="role" id="role" required
                                class="w-full glass-input">
                                <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
                                <option value="market_owner" {{ old('role', $user->role) === 'market_owner' ? 'selected' : '' }}>{{ __('Market Owner') }}</option>
                                <option value="collector" {{ old('role', $user->role) === 'collector' ? 'selected' : '' }}>{{ __('staff.roles.collector') }}</option>
                                <option value="shop_owner" {{ old('role', $user->role) === 'shop_owner' ? 'selected' : '' }}>{{ __('Shop Owner') }}</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="market_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Market') }}</label>
                            <select name="market_id" id="market_id"
                                class="w-full glass-input">
                                <option value="">{{ __('No Market (Super Admin)') }}</option>
                                @foreach($markets as $market)
                                    <option value="{{ $market->id }}" {{ old('market_id', $user->market_id) == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                                @endforeach
                            </select>
                            @error('market_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ __('Active') }}</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 btn-secondary transition">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 btn-primary transition">
                        {{ __('Update User') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
