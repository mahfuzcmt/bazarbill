<x-app-layout>
    <x-slot name="header">{{ __('complaints.my_complaints') }}</x-slot>

    <div class="space-y-6">
        <!-- Actions -->
        <div class="flex justify-end">
            <a href="{{ route('shop-owner.complaints.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('complaints.submit_complaint') }}
            </a>
        </div>

        <!-- Complaints List -->
        <div class="space-y-4">
            @forelse($complaints as $complaint)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $complaint->subject }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $complaint->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            @php
                                $priorityColors = [
                                    'high' => 'bg-red-100 text-red-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'low' => 'bg-green-100 text-green-800',
                                ];
                                $statusColors = [
                                    'open' => 'bg-red-100 text-red-800',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'closed' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $priorityColors[$complaint->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('complaints.priority_' . $complaint->priority) }}
                            </span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$complaint->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('complaints.status_' . $complaint->status) }}
                            </span>
                        </div>
                    </div>

                    <p class="mt-3 text-gray-600">{{ Str::limit($complaint->description, 200) }}</p>

                    @if($complaint->resolution_notes)
                    <div class="mt-4 p-3 bg-green-50 rounded-lg">
                        <p class="text-sm font-medium text-green-800">{{ __('complaints.resolution') }}:</p>
                        <p class="text-sm text-green-700 mt-1">{{ $complaint->resolution_notes }}</p>
                    </div>
                    @endif

                    <div class="mt-4 flex justify-end">
                        <a href="{{ route('shop-owner.complaints.show', $complaint) }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                            {{ __('messages.view_details') }} →
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="mt-2 text-gray-500">{{ __('complaints.no_complaints') }}</p>
                <a href="{{ route('shop-owner.complaints.create') }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                    {{ __('complaints.submit_first_complaint') }}
                </a>
            </div>
            @endforelse
        </div>

        @if($complaints->hasPages())
        <div class="mt-6">
            {{ $complaints->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
