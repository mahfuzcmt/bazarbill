<x-app-layout>
    <x-slot name="header">{{ __('staff.staff_management') }}</x-slot>

    <div class="space-y-6">
        <!-- Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <h3 class="text-lg font-medium text-gray-900">{{ __('staff.collectors') }}</h3>
                <span class="text-sm text-gray-500">({{ $staff->total() }} {{ __('staff.total') }})</span>
            </div>
            <a href="{{ route('market-owner.staff.create') }}"
               class="inline-flex items-center px-4 py-2 btn-primary transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                {{ __('staff.add_staff') }}
            </a>
        </div>

        <!-- Staff Table -->
        <div class="glass-card">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="glass-thead">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('staff.name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('staff.contact') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('staff.assigned_shops') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('staff.collections_today') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('staff.status') }}
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('messages.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70">
                    @forelse($staff as $member)
                    <tr class="hover:bg-white/50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                        <span class="text-lg font-medium text-indigo-600">{{ substr($member->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $member->getLocalizedName() }}</div>
                                    <div class="text-sm text-gray-500">{{ $member->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $member->phone }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $member->assignedShops->count() }} {{ __('shops.shops') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-green-600">
                                ৳{{ number_format($member->payments()->whereDate('payment_date', today())->sum('amount')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $member->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $member->is_active ? __('staff.active') : __('staff.inactive') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex justify-end space-x-2">
                                <a href="{{ route('market-owner.staff.show', $member) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('messages.view') }}
                                </a>
                                <a href="{{ route('market-owner.staff.edit', $member) }}" class="text-yellow-600 hover:text-yellow-900">
                                    {{ __('messages.edit') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="mt-2">{{ __('staff.no_staff') }}</p>
                            <a href="{{ route('market-owner.staff.create') }}" class="mt-2 inline-flex items-center text-indigo-600 hover:text-indigo-900">
                                {{ __('staff.add_first_staff') }}
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            </div>

            @if($staff->hasPages())
            <div class="px-4 sm:px-6 py-3 border-t border-gray-200">
                {{ $staff->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
