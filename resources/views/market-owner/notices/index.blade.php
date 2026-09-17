<x-app-layout>
    <x-slot name="header">{{ __('notices.notices') }}</x-slot>

    <div class="space-y-6">
        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900">{{ __('notices.notice_board') }}</h3>
            </div>
            <a href="{{ route('market-owner.notices.create') }}"
               class="inline-flex items-center px-4 py-2 btn-primary transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('notices.create_notice') }}
            </a>
        </div>

        <!-- Notices List -->
        <div class="space-y-4">
            @forelse($notices as $notice)
            <div class="glass-card overflow-hidden {{ $notice->is_pinned ? 'ring-2 ring-indigo-500' : '' }}">
                <div class="px-4 sm:px-6 py-4">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="flex items-start space-x-3">
                            @if($notice->is_pinned)
                            <svg class="w-5 h-5 text-indigo-600 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                            </svg>
                            @endif
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ $notice->getLocalizedTitle() }}</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ __('notices.by') }} {{ $notice->creator->getLocalizedName() }} •
                                    {{ $notice->created_at->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $notice->target_role === 'all' ? 'bg-blue-100 text-blue-800' : ($notice->target_role === 'shop_owner' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ __('notices.target_' . $notice->target_role) }}
                            </span>
                            @if($notice->expires_at && $notice->expires_at < now())
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                {{ __('notices.expired') }}
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 text-gray-600">
                        {{ Str::limit($notice->getLocalizedContent(), 200) }}
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('market-owner.notices.edit', $notice) }}"
                           class="text-sm text-yellow-600 hover:text-yellow-900">
                            {{ __('messages.edit') }}
                        </a>
                        <form action="{{ route('market-owner.notices.destroy', $notice) }}" method="POST" class="inline"
                              onsubmit="return confirm('{{ __('notices.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-900">
                                {{ __('messages.delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="glass-card p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="mt-2 text-gray-500">{{ __('notices.no_notices') }}</p>
                <a href="{{ route('market-owner.notices.create') }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                    {{ __('notices.create_first') }}
                </a>
            </div>
            @endforelse
        </div>

        @if($notices->hasPages())
        <div class="mt-6">
            {{ $notices->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
