<x-app-layout>
    <x-slot name="header">{{ __('complaints.complaints') }}</x-slot>

    <div class="space-y-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('complaints.status') }}</label>
                    <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('complaints.all_status') }}</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>{{ __('complaints.status_open') }}</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>{{ __('complaints.status_in_progress') }}</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>{{ __('complaints.status_resolved') }}</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>{{ __('complaints.status_closed') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('complaints.priority') }}</label>
                    <select name="priority" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('complaints.all_priority') }}</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>{{ __('complaints.priority_high') }}</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>{{ __('complaints.priority_medium') }}</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>{{ __('complaints.priority_low') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('shops.shop') }}</label>
                    <select name="shop" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('complaints.all_shops') }}</option>
                        @foreach($shops as $shop)
                        <option value="{{ $shop->id }}" {{ request('shop') == $shop->id ? 'selected' : '' }}>
                            {{ $shop->shop_number }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        {{ __('messages.filter') }}
                    </button>
                    <a href="{{ route('market-owner.complaints.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition">
                        {{ __('messages.reset') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('complaints.status_open') }}</p>
                <p class="text-2xl font-bold text-red-600">{{ $openCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('complaints.status_in_progress') }}</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $inProgressCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('complaints.status_resolved') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ $resolvedCount }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('complaints.total') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $complaints->total() }}</p>
            </div>
        </div>

        <!-- Complaints Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('complaints.subject') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('shops.shop') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('complaints.submitted_by') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('complaints.priority') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('complaints.status') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('complaints.date') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($complaints as $complaint)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ Str::limit($complaint->subject, 40) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $complaint->shop->shop_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $complaint->submitter->getLocalizedName() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $priorityColors = [
                                    'high' => 'bg-red-100 text-red-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'low' => 'bg-green-100 text-green-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $priorityColors[$complaint->priority] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('complaints.priority_' . $complaint->priority) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'open' => 'bg-red-100 text-red-800',
                                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'closed' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$complaint->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ __('complaints.status_' . $complaint->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $complaint->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('market-owner.complaints.show', $complaint) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ __('messages.view') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                            <p class="mt-2">{{ __('complaints.no_complaints') }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            </div>

            @if($complaints->hasPages())
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                {{ $complaints->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
