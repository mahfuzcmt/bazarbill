<x-app-layout>
    <x-slot name="header">{{ __('mymarkets.managers_title') }}</x-slot>

    <div class="max-w-4xl space-y-6">
        <p class="text-sm text-gray-600">{{ __('mymarkets.managers_subtitle') }}</p>

        <div class="glass-card">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('mymarkets.manager_name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('mymarkets.manager_phone') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shop_owners.login_id') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($managers as $manager)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $manager->getLocalizedName() }}
                                @if($manager->id === auth()->id())<span class="ml-1 text-xs text-indigo-600">({{ __('mymarkets.you') }})</span>@endif
                                @php $others = $manager->markets()->where('markets.id', '!=', $market->id)->count(); @endphp
                                @if($others)<div class="text-xs text-gray-500">{{ __('mymarkets.also_manages', ['count' => $others]) }}</div>@endif
                                @unless($manager->is_active)<div class="text-xs text-red-600">{{ __('shop_owners.inactive') }}</div>@endunless
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $manager->phone }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $manager->loginIdentifier() }}</td>
                            <td class="px-6 py-4 text-right text-sm">
                                @if($manager->id !== auth()->id())
                                <form method="POST" action="{{ route('market-owner.managers.destroy', $manager) }}" onsubmit="return confirm('{{ __('mymarkets.remove_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">{{ __('mymarkets.remove') }}</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">{{ __('mymarkets.no_managers') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <form method="POST" action="{{ route('market-owner.managers.store') }}" class="glass-card overflow-hidden">
            @csrf
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('mymarkets.add_manager') }}</h3>
                <p class="text-sm text-gray-500">{{ __('mymarkets.manager_hint') }}</p>
            </div>
            <div class="px-4 sm:px-6 py-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.manager_name') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full glass-input">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.manager_phone') }} <span class="text-red-500">*</span></label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="01XXXXXXXXX" class="mt-1 block w-full glass-input">
                    @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.manager_email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full glass-input">
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div></div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.manager_password') }}</label>
                    <input type="password" name="password" id="password" minlength="6" autocomplete="new-password" class="mt-1 block w-full glass-input">
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('mymarkets.manager_password_confirm') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" autocomplete="new-password" class="mt-1 block w-full glass-input">
                </div>
            </div>
            <div class="px-4 sm:px-6 py-4 bg-white/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">{{ __('mymarkets.add_manager') }}</button>
            </div>
        </form>
    </div>
</x-app-layout>
