<x-app-layout>
    <x-slot name="header">
        {{ __('Markets Management') }}
    </x-slot>

    <div class="glass-card">
        <div class="p-6 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <h3 class="text-lg font-semibold text-gray-800">{{ __('All Markets') }}</h3>
            <a href="{{ route('admin.markets.create') }}" class="inline-flex items-center px-4 py-2 btn-primary transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                {{ __('Add Market') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mx-6 mt-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="glass-thead">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Market') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Contact') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.nav.shops') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Users') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('SMS Credits') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70">
                    @forelse($markets as $market)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $market->name }}</div>
                                @if($market->name_bn)
                                    <div class="text-sm text-gray-500">{{ $market->name_bn }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $market->phone ?? '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $market->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $market->shops_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $market->users_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if($market->usesPlatformSms())
                                    <a href="{{ route('admin.markets.sms-credits', $market) }}" class="font-semibold {{ $market->hasLowSmsCredits() ? 'text-red-600' : 'text-gray-900' }} hover:underline">
                                        {{ number_format($market->sms_credits) }}
                                    </a>
                                @else
                                    <span class="text-gray-500">{{ __('Own key') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full {{ $market->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($market->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.markets.show', $market) }}" class="text-blue-600 hover:text-blue-900 mr-3">{{ __('View') }}</a>
                                <a href="{{ route('admin.markets.edit', $market) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('Edit') }}</a>
                                <form action="{{ route('admin.markets.destroy', $market) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this market?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                {{ __('No markets found.') }}
                                <a href="{{ route('admin.markets.create') }}" class="text-indigo-600 hover:underline ml-1">{{ __('Create one?') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($markets->hasPages())
            <div class="px-6 py-4 border-t">
                {{ $markets->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
