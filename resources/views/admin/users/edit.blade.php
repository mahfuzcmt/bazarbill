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
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }} <span class="text-gray-400 text-xs font-normal">({{ __('optional if mobile number is given') }})</span></label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->hasPlaceholderEmail() ? '' : $user->email) }}"
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
                    <!-- Markets (market owners only) -->
                    <div id="market-picker" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Markets this owner can manage') }}</label>
                        <div class="border border-gray-300 rounded-md max-h-48 overflow-y-auto p-3 space-y-2 text-sm">
                            @foreach($markets as $m)
                                <label class="flex items-center">
                                    <input type="checkbox" name="market_ids[]" value="{{ $m->id }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                           @checked(in_array($m->id, old('market_ids', $memberMarketIds ?? [])))>
                                    <span class="ml-2 text-gray-700">{{ $m->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('The market selected above is always included. The owner switches between markets from their sidebar.') }}</p>
                    </div>

                    <!-- Shops (shop owners only) -->
                    <div id="shop-picker" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Shops owned') }}</label>
                        <div id="shop-picker-list" class="border border-gray-300 rounded-md max-h-48 overflow-y-auto p-3 space-y-2 text-sm"></div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Only shops without an owner in the selected market are listed. Pick the market first.') }}</p>
                        @error('shop_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
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
            <script>
                (function () {
                    const shopsByMarket = @json($shopsByMarket);
                    const selected = new Set(@json(array_map('intval', old('shop_ids', $ownedShopIds ?? []))));
                    const role = document.getElementById('role');
                    const market = document.getElementById('market_id');
                    const picker = document.getElementById('shop-picker');
                    const list = document.getElementById('shop-picker-list');
                    if (!role || !market || !picker || !list) return;

                    function render() {
                        const mp = document.getElementById('market-picker');
                        if (mp) mp.classList.toggle('hidden', role.value !== 'market_owner');
                        const isShopOwner = role.value === 'shop_owner';
                        picker.classList.toggle('hidden', !isShopOwner);
                        if (!isShopOwner) return;
                        list.querySelectorAll('input').forEach(i => { if (i.checked) selected.add(parseInt(i.value)); else selected.delete(parseInt(i.value)); });
                        const shops = shopsByMarket[market.value] || [];
                        list.innerHTML = shops.length ? '' : '<p class="text-gray-500">{{ __('No unowned shops in this market. Create the shop first.') }}</p>';
                        shops.forEach(s => {
                            const wrap = document.createElement('label');
                            wrap.className = 'flex items-center';
                            wrap.innerHTML = '<input type="checkbox" name="shop_ids[]" value="' + s.id + '" class="h-4 w-4 text-indigo-600 border-gray-300 rounded"' + (selected.has(s.id) || s.mine ? ' checked' : '') + '><span class="ml-2 text-gray-700"></span>';
                            wrap.querySelector('span').textContent = s.label;
                            list.appendChild(wrap);
                        });
                    }
                    role.addEventListener('change', render);
                    market.addEventListener('change', render);
                    render();
                })();
            </script>
        </div>
    </div>
</x-app-layout>
