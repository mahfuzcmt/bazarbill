<x-app-layout>
    <x-slot name="header">{{ __('notices.notice_details') }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="glass-card overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $notice->title }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ __('Posted on') }} {{ $notice->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 text-sm rounded-full
                            {{ $notice->type === 'urgent' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $notice->type === 'info' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $notice->type === 'warning' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $notice->type === 'general' ? 'bg-gray-100 text-gray-800' : '' }}">
                            {{ ucfirst($notice->type) }}
                        </span>
                        <span class="px-3 py-1 text-sm rounded-full {{ $notice->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $notice->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-6">
                <div class="prose max-w-none">
                    {!! nl2br(e($notice->content)) !!}
                </div>
            </div>

            <!-- Meta -->
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">{{ __('Target Audience') }}</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($notice->target_audience ?? 'All') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Start Date') }}</p>
                        <p class="font-medium text-gray-900">{{ $notice->start_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('End Date') }}</p>
                        <p class="font-medium text-gray-900">{{ $notice->end_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">{{ __('Created By') }}</p>
                        <p class="font-medium text-gray-900">{{ $notice->creator?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('market-owner.notices.index') }}" class="text-indigo-600 hover:text-indigo-800">
                ← {{ __('Back to Notices') }}
            </a>
            <div class="flex gap-2">
                <a href="{{ route('market-owner.notices.edit', $notice) }}"
                   class="px-4 py-2 btn-primary">
                    {{ __('Edit Notice') }}
                </a>
                <form action="{{ route('market-owner.notices.destroy', $notice) }}" method="POST"
                      onsubmit="return confirm('{{ __('Are you sure you want to delete this notice?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
