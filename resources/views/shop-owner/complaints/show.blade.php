<x-app-layout>
    <x-slot name="header">{{ __('complaints.complaint_details') }}</x-slot>

    <div class="max-w-3xl space-y-6">
        <!-- Complaint Card -->
        <div class="glass-card overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $complaint->subject }}</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ __('complaints.submitted_on') }}: {{ $complaint->created_at->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
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
            </div>

            <div class="px-4 sm:px-6 py-4">
                <h3 class="text-sm font-medium text-gray-500 mb-2">{{ __('complaints.description') }}</h3>
                <div class="prose prose-sm max-w-none text-gray-900">
                    {!! nl2br(e($complaint->description)) !!}
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50">
                <h3 class="text-sm font-medium text-gray-700 mb-4">{{ __('complaints.status_timeline') }}</h3>
                <div class="relative">
                    <div class="absolute left-2 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                    <div class="space-y-4">
                        <!-- Submitted -->
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                            <div class="ml-8">
                                <p class="text-sm font-medium text-gray-900">{{ __('complaints.status_submitted') }}</p>
                                <p class="text-xs text-gray-500">{{ $complaint->created_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>

                        <!-- In Progress (if applicable) -->
                        @if(in_array($complaint->status, ['in_progress', 'resolved', 'closed']))
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-4 h-4 bg-yellow-500 rounded-full border-2 border-white"></div>
                            <div class="ml-8">
                                <p class="text-sm font-medium text-gray-900">{{ __('complaints.status_in_progress') }}</p>
                                @if($complaint->assigned_to)
                                <p class="text-xs text-gray-500">
                                    {{ __('complaints.assigned_to') }}: {{ $complaint->assignee->name ?? '-' }}
                                </p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Resolved (if applicable) -->
                        @if(in_array($complaint->status, ['resolved', 'closed']))
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-4 h-4 bg-green-500 rounded-full border-2 border-white"></div>
                            <div class="ml-8">
                                <p class="text-sm font-medium text-gray-900">{{ __('complaints.status_resolved') }}</p>
                                @if($complaint->resolved_at)
                                <p class="text-xs text-gray-500">{{ $complaint->resolved_at->format('d M Y, h:i A') }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Current status indicator if still open -->
                        @if($complaint->status === 'open')
                        <div class="relative flex items-start">
                            <div class="absolute left-0 w-4 h-4 bg-gray-300 rounded-full border-2 border-white animate-pulse"></div>
                            <div class="ml-8">
                                <p class="text-sm font-medium text-gray-500">{{ __('complaints.awaiting_response') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Resolution (if resolved) -->
        @if($complaint->resolution_notes)
        <div class="bg-green-50 rounded-lg shadow overflow-hidden border border-green-200">
            <div class="px-4 sm:px-6 py-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-green-800">{{ __('complaints.resolution') }}</h3>
                        <div class="mt-2 text-green-700">
                            {!! nl2br(e($complaint->resolution_notes)) !!}
                        </div>
                        @if($complaint->resolved_at)
                        <p class="mt-2 text-sm text-green-600">
                            {{ __('complaints.resolved_on') }}: {{ $complaint->resolved_at->format('d M Y, h:i A') }}
                        </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Feedback Section (if resolved) -->
        @if($complaint->status === 'resolved' && !$complaint->feedback_given)
        <div class="glass-card overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('complaints.give_feedback') }}</h3>
                <p class="text-sm text-gray-500">{{ __('complaints.feedback_desc') }}</p>
            </div>
            <form action="{{ route('shop-owner.complaints.feedback', $complaint) }}" method="POST" class="px-4 sm:px-6 py-4 space-y-4">
                @csrf

                <!-- Satisfaction Rating -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('complaints.satisfaction') }}</label>
                    <div class="flex space-x-2">
                        @foreach(['very_satisfied' => '😊', 'satisfied' => '🙂', 'neutral' => '😐', 'unsatisfied' => '😕', 'very_unsatisfied' => '😞'] as $value => $emoji)
                        <label class="cursor-pointer">
                            <input type="radio" name="satisfaction" value="{{ $value }}" class="sr-only peer">
                            <span class="text-3xl peer-checked:ring-2 peer-checked:ring-indigo-500 peer-checked:ring-offset-2 p-2 rounded-lg inline-block hover:bg-gray-100 transition">
                                {{ $emoji }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Feedback Comment -->
                <div>
                    <label for="feedback" class="block text-sm font-medium text-gray-700">{{ __('complaints.feedback_comment') }}</label>
                    <textarea name="feedback" id="feedback" rows="3"
                              placeholder="{{ __('complaints.feedback_placeholder') }}"
                              class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                        {{ __('complaints.submit_feedback') }}
                    </button>
                </div>
            </form>
        </div>
        @endif

        <!-- Contact Info -->
        @if($complaint->status === 'open' || $complaint->status === 'in_progress')
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-800">{{ __('complaints.need_immediate_help') }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        {{ __('complaints.contact_market') }}:
                        @if($complaint->market && $complaint->market->contact_phone)
                        <a href="tel:{{ $complaint->market->contact_phone }}" class="font-medium hover:underline">
                            {{ $complaint->market->contact_phone }}
                        </a>
                        @else
                        {{ __('messages.not_available') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('shop-owner.complaints.index') }}"
               class="px-4 py-2 btn-secondary transition">
                ← {{ __('messages.back_to_list') }}
            </a>
        </div>
    </div>
</x-app-layout>
