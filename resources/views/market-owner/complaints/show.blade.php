<x-app-layout>
    <x-slot name="header">{{ __('complaints.complaint_details') }}</x-slot>

    <div class="space-y-6 max-w-4xl">
        <!-- Complaint Info -->
        <div class="glass-card overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">{{ $complaint->subject }}</h3>
                    <p class="text-sm text-gray-500">{{ __('complaints.submitted_on') }}: {{ $complaint->created_at->format('d M Y, h:i A') }}</p>
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

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('complaints.submitted_by') }}</h4>
                        <p class="text-gray-900">{{ $complaint->submitter->getLocalizedName() }}</p>
                        <p class="text-sm text-gray-500">{{ $complaint->submitter->phone }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 mb-1">{{ __('shops.shop') }}</h4>
                        <p class="text-gray-900">{{ $complaint->shop->shop_number }}</p>
                        <p class="text-sm text-gray-500">{{ $complaint->shop->floor }}</p>
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('complaints.description') }}</h4>
                    <div class="bg-white/40 rounded-xl p-4">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $complaint->description }}</p>
                    </div>
                </div>

                @if($complaint->resolution_notes)
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-500 mb-2">{{ __('complaints.resolution_notes') }}</h4>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $complaint->resolution_notes }}</p>
                        @if($complaint->resolved_at)
                        <p class="text-sm text-gray-500 mt-2">{{ __('complaints.resolved_at') }}: {{ $complaint->resolved_at->format('d M Y, h:i A') }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Update Status Form -->
        @if($complaint->status !== 'closed')
        <div class="glass-card p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('complaints.update_status') }}</h3>

            <form action="{{ route('market-owner.complaints.update', $complaint) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('complaints.status') }}</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="open" {{ $complaint->status === 'open' ? 'selected' : '' }}>{{ __('complaints.status_open') }}</option>
                            <option value="in_progress" {{ $complaint->status === 'in_progress' ? 'selected' : '' }}>{{ __('complaints.status_in_progress') }}</option>
                            <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>{{ __('complaints.status_resolved') }}</option>
                            <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : '' }}>{{ __('complaints.status_closed') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700">{{ __('complaints.assign_to') }}</label>
                        <select name="assigned_to" id="assigned_to"
                                class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">{{ __('complaints.unassigned') }}</option>
                            @foreach($staff as $member)
                            <option value="{{ $member->id }}" {{ $complaint->assigned_to == $member->id ? 'selected' : '' }}>
                                {{ $member->getLocalizedName() }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="resolution_notes" class="block text-sm font-medium text-gray-700">{{ __('complaints.resolution_notes') }}</label>
                    <textarea name="resolution_notes" id="resolution_notes" rows="3"
                              class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('resolution_notes', $complaint->resolution_notes) }}</textarea>
                </div>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <a href="{{ route('market-owner.complaints.index') }}"
                       class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                        {{ __('messages.back') }}
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                        {{ __('messages.update') }}
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
