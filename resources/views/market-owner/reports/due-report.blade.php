<x-app-layout>
    <x-slot name="header">{{ __('reports.due_report') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Card -->
        <div class="glass-card p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Total Outstanding Due') }}</p>
                    <p class="text-3xl font-bold text-red-600">৳ {{ number_format($grandTotal) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">{{ __('Shops with Due') }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $shops->count() }}</p>
                </div>
                <form action="{{ route('market-owner.reports.due') }}" method="GET">
                    <label class="block text-xs text-gray-500">{{ __('reports.filter_by') }}</label>
                    <select name="filter" class="mt-1 glass-input text-sm pr-9" onchange="this.form.submit()">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>{{ __('reports.all_dues') }}</option>
                        <option value="overdue" {{ $filter === 'overdue' ? 'selected' : '' }}>{{ __('reports.overdue_only') }}</option>
                        <option value="pending" {{ $filter === 'pending' ? 'selected' : '' }}>{{ __('reports.pending_only') }}</option>
                    </select>
                </form>
                <div class="flex gap-2">
                    <a href="{{ route('market-owner.reports.due', array_merge(['filter' => $filter], ['format' => 'pdf'])) }}"
                       class="btn-danger px-3 py-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ __('Download PDF') }}
                    </a>
                    <a href="{{ route('market-owner.reports.due', array_merge(['filter' => $filter], ['format' => 'excel'])) }}"
                       class="btn-secondary px-3 py-2 text-sm text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ __('Download Excel') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Due Table -->
        <div class="glass-card overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">{{ __('Shops with Outstanding Dues') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shop') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Owner') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Pending Invoices') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total Due') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($shops as $shop)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $shop->shop_number }}</div>
                                <div class="text-sm text-gray-500">{{ $shop->floor ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $shop->shopOwner?->name ?? '-' }}</div>
                                <div class="text-sm text-gray-500">{{ $shop->shopOwner?->phone ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                    {{ $shop->invoices->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <span class="text-lg font-bold text-red-600">৳ {{ number_format($shop->total_due) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <a href="{{ route('market-owner.shops.show', $shop) }}"
                                   class="text-indigo-600 hover:text-indigo-900 text-sm">
                                    {{ __('View Details') }}
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                {{ __('No shops with outstanding dues') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-white/50 font-medium">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-700">{{ __('Total') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-600">৳ {{ number_format($grandTotal) }}</td>
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
