<x-app-layout>
    <x-slot name="header">
        {{ __('Users Management') }}
    </x-slot>

    <div class="glass-card">
        <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ __('All Users') }}</h3>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 btn-primary transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                {{ __('Add User') }}
            </a>
        </div>

        <!-- Filters -->
        <div class="p-4 border-b bg-gray-50">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, email, phone...') }}"
                        class="w-full glass-input">
                </div>
                <div>
                    <select name="role" class="glass-input">
                        <option value="">{{ __('All Roles') }}</option>
                        <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>{{ __('Super Admin') }}</option>
                        <option value="market_owner" {{ request('role') === 'market_owner' ? 'selected' : '' }}>{{ __('Market Owner') }}</option>
                        <option value="collector" {{ request('role') === 'collector' ? 'selected' : '' }}>{{ __('staff.roles.collector') }}</option>
                        <option value="shop_owner" {{ request('role') === 'shop_owner' ? 'selected' : '' }}>{{ __('Shop Owner') }}</option>
                    </select>
                </div>
                <div>
                    <select name="market_id" class="glass-input">
                        <option value="">{{ __('All Markets') }}</option>
                        @foreach($markets as $market)
                            <option value="{{ $market->id }}" {{ request('market_id') == $market->id ? 'selected' : '' }}>{{ $market->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-primary px-4 py-2">{{ __('Filter') }}</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary px-4 py-2">{{ __('Clear') }}</a>
            </form>
        </div>

        @if(session('success'))
            <div class="mx-6 mt-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="glass-thead">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('User') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Contact') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Role') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Market') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70">
                    @forelse($users as $user)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                @if($user->name_bn)
                                    <div class="text-sm text-gray-500">{{ $user->name_bn }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                <div class="text-sm text-gray-500">{{ $user->phone ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $user->role === 'market_owner' ? 'bg-indigo-100 text-indigo-800' : '' }}
                                    {{ $user->role === 'collector' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $user->role === 'shop_owner' ? 'bg-green-100 text-green-800' : '' }}">
                                    {{ str_replace('_', ' ', ucfirst($user->role)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->market->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->is_active ? __('Active') : __('Inactive') }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($user->role === 'market_owner' && $user->market_id)
                                    <a href="{{ route('admin.markets.sms-credits', $user->market_id) }}" class="text-emerald-600 hover:text-emerald-900 mr-3">{{ __('SMS Credits') }}</a>
                                @endif
                                <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('View') }}</a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('Edit') }}</a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                {{ __('No users found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
