<x-app-layout>
    <x-slot name="header">{{ __('reports.shop_report') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_shops') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_shops'] }}</p>
                <div class="mt-2 text-xs space-x-2">
                    <span class="text-green-600">{{ $summary['active_shops'] }} {{ __('Active') }}</span>
                    <span class="text-yellow-600">{{ $summary['vacant_shops'] }} {{ __('Vacant') }}</span>
                </div>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_rent') }}</p>
                <p class="text-2xl font-bold text-indigo-600">৳ {{ number_format($summary['total_rent']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_collected') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($summary['total_collected']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_due') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($summary['total_due']) }}</p>
            </div>
        </div>

        <!-- Filter & Actions -->
        <div class="glass-card p-4 flex flex-wrap items-center justify-between gap-4">
            <form action="{{ route('market-owner.reports.shops') }}" method="GET" class="flex items-center gap-4">
                <select name="status" class="glass-input text-sm" onchange="this.form.submit()">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>{{ __('All Shops') }}</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                    <option value="vacant" {{ $status === 'vacant' ? 'selected' : '' }}>{{ __('Vacant') }}</option>
                    <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>{{ __('Suspended') }}</option>
                </select>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('market-owner.reports.shops', ['status' => $status, 'format' => 'pdf']) }}"
                   class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('market-owner.reports.shops', ['status' => $status, 'format' => 'excel']) }}"
                   class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    {{ __('Download Excel') }}
                </a>
            </div>
        </div>

        <!-- Shop Table -->
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shop') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Owner') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('staff.roles.collector') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Rent') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Billed') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Paid') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Due') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($shops as $shop)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $shop->shop_number }}</div>
                                <div class="text-sm text-gray-500">{{ $shop->floor ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $shop->shopOwner?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $shop->collector?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                ৳ {{ number_format($shop->rent_amount) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                ৳ {{ number_format($shop->invoices_sum_total_amount ?? 0) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600">
                                ৳ {{ number_format($shop->invoices_sum_paid_amount ?? 0) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                ৳ {{ number_format($shop->invoices_sum_due_amount ?? 0) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $shop->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $shop->status === 'vacant' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $shop->status === 'suspended' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($shop->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">{{ __('No shops found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-white/50 font-medium">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-700">{{ __('Total') }}</td>
                            <td class="px-4 py-3 text-sm text-right">৳ {{ number_format($summary['total_rent']) }}</td>
                            <td class="px-4 py-3 text-sm text-right">৳ {{ number_format($summary['total_billed']) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-600">৳ {{ number_format($summary['total_collected']) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-600">৳ {{ number_format($summary['total_due']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('market-owner.reports.index') }}" class="text-indigo-600 hover:text-indigo-800">
                ← {{ __('Back to Reports') }}
            </a>
        </div>
    </div>
</x-app-layout>
