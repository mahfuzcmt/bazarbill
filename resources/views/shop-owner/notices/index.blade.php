<x-app-layout>
    <x-slot name="header">{{ __('notices.notices') }}</x-slot>

    <div class="space-y-4">
        @forelse($notices as $notice)
        <div class="bg-white rounded-lg shadow overflow-hidden {{ $notice->is_pinned ? 'ring-2 ring-indigo-500' : '' }}">
            <div class="px-4 sm:px-6 py-4">
                <div class="flex items-start space-x-3">
                    @if($notice->is_pinned)
                    <svg class="w-5 h-5 text-indigo-600 mt-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                        <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    @else
                    <svg class="w-5 h-5 text-gray-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @endif
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-gray-900">{{ $notice->getLocalizedTitle() }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $notice->created_at->format('d M Y, h:i A') }}</p>
                        <div class="mt-3 text-gray-600 prose prose-sm max-w-none">
                            {!! nl2br(e($notice->getLocalizedContent())) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            <p class="mt-2 text-gray-500">{{ __('notices.no_notices') }}</p>
        </div>
        @endforelse

        @if($notices->hasPages())
        <div class="mt-6">
            {{ $notices->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
