<x-app-layout>
    <x-slot name="header">
        {{ __('Edit Market') }}: {{ $market->name }}
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="glass-card p-6">
            <form action="{{ route('admin.markets.update', $market) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Market Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $market->name) }}" required
                            class="w-full glass-input">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name Bangla -->
                    <div>
                        <label for="name_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Market Name (Bangla)') }}</label>
                        <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $market->name_bn) }}"
                            class="w-full glass-input">
                        @error('name_bn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address') }}</label>
                        <textarea name="address" id="address" rows="2"
                            class="w-full glass-input">{{ old('address', $market->address) }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address Bangla -->
                    <div>
                        <label for="address_bn" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Address (Bangla)') }}</label>
                        <textarea name="address_bn" id="address_bn" rows="2"
                            class="w-full glass-input">{{ old('address_bn', $market->address_bn) }}</textarea>
                        @error('address_bn')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone & Email -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Phone') }}</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $market->phone) }}"
                                class="w-full glass-input">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $market->email) }}"
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
                            <option value="active" {{ old('status', $market->status) === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="inactive" {{ old('status', $market->status) === 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-4">
                    <a href="{{ route('admin.markets.index') }}" class="px-4 py-2 btn-secondary transition">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 btn-primary transition">
                        {{ __('Update Market') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
