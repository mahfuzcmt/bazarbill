<x-app-layout>
    <x-slot name="header">{{ __('staff.staff_details') }}</x-slot>

    <div class="max-w-4xl space-y-6">
        <!-- Staff Info Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-bold text-indigo-600">{{ strtoupper(substr($staff->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ $staff->name }}</h2>
                            @if($staff->name_bn)
                            <p class="text-gray-600">{{ $staff->name_bn }}</p>
                            @endif
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $staff->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $staff->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('market-owner.staff.edit', $staff) }}"
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            {{ __('messages.edit') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('staff.email') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('staff.phone') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('staff.joined_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->created_at->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('staff.last_login') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $staff->last_login_at ? $staff->last_login_at->diffForHumans() : __('messages.never') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Performance Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('staff.assigned_shops') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $assignedShops->count() }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('staff.total_collections') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($totalCollections) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('staff.this_month') }}</p>
                <p class="text-2xl font-bold text-indigo-600">৳ {{ number_format($thisMonthCollections) }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">{{ __('staff.pending_dues') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($pendingDues) }}</p>
            </div>
        </div>

        <!-- Assigned Shops -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('staff.assigned_shops') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.shop_number') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.owner') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.floor') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.rent_amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.due_amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($assignedShops as $shop)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-gray-900">{{ $shop->shop_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $shop->shopOwner->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $shop->floor }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">
                                ৳ {{ number_format($shop->rent_amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $due = $shop->invoices->whereIn('status', ['pending', 'partial', 'overdue'])->sum('due_amount');
                                @endphp
                                <span class="{{ $due > 0 ? 'text-red-600 font-medium' : 'text-green-600' }}">
                                    ৳ {{ number_format($due) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('market-owner.shops.show', $shop) }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('messages.view') }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                {{ __('staff.no_assigned_shops') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Collections -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('staff.recent_collections') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.receipt_number') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('shops.shop') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.amount') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('payments.date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentPayments as $payment)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">
                                {{ $payment->receipt_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $payment->shop->shop_number ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-green-600">
                                ৳ {{ number_format($payment->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $payment->payment_date->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                {{ __('payments.no_payments') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Back Button -->
        <div class="flex justify-start">
            <a href="{{ route('market-owner.staff.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                ← {{ __('messages.back_to_list') }}
            </a>
        </div>
    </div>
</x-app-layout>
