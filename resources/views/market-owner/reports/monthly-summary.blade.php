<x-app-layout>
    <x-slot name="header">{{ __('reports.monthly_summary') }} - {{ \Carbon\Carbon::parse($month)->format('F Y') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_invoices') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_invoices'] }}</p>
                <div class="mt-2 text-xs space-x-1">
                    <span class="text-green-600">{{ $summary['paid_count'] }} {{ __('Paid') }}</span>
                    <span class="text-yellow-600">{{ $summary['partial_count'] }} {{ __('Partial') }}</span>
                </div>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_billed') }}</p>
                <p class="text-2xl font-bold text-indigo-600">৳ {{ number_format($summary['total_billed']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_collected') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($summary['total_collected']) }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $summary['collection_rate'] }}% {{ __('Collection Rate') }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_due') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($summary['total_due']) }}</p>
                <p class="mt-1 text-xs text-red-500">{{ $summary['overdue_count'] }} {{ __('Overdue') }}</p>
            </div>
        </div>

        <!-- Filter & Actions -->
        <div class="glass-card p-4 flex flex-wrap items-center justify-between gap-4">
            <form action="{{ route('market-owner.reports.monthly') }}" method="GET" class="flex items-center gap-4">
                <input type="month" name="month" value="{{ $month }}"
                       class="glass-input text-sm" onchange="this.form.submit()">
            </form>
            <div class="flex gap-2">
                <a href="{{ route('market-owner.reports.monthly', ['month' => $month, 'format' => 'pdf']) }}"
                   class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('market-owner.reports.monthly', ['month' => $month, 'format' => 'excel']) }}"
                   class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    {{ __('Download Excel') }}
                </a>
            </div>
        </div>

        <!-- Breakdown Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card p-4 border-l-4 border-indigo-500">
                <p class="text-sm text-gray-500">{{ __('Rent Amount') }}</p>
                <p class="text-xl font-bold text-gray-900">৳ {{ number_format($summary['total_rent']) }}</p>
            </div>
            <div class="glass-card p-4 border-l-4 border-orange-500">
                <p class="text-sm text-gray-500">{{ __('Previous Due') }}</p>
                <p class="text-xl font-bold text-gray-900">৳ {{ number_format($summary['total_previous_due']) }}</p>
            </div>
            <div class="glass-card p-4 border-l-4 border-red-500">
                <p class="text-sm text-gray-500">{{ __('Late Fees') }}</p>
                <p class="text-xl font-bold text-gray-900">৳ {{ number_format($summary['total_late_fee']) }}</p>
            </div>
            <div class="glass-card p-4 border-l-4 border-green-500">
                <p class="text-sm text-gray-500">{{ __('Discounts') }}</p>
                <p class="text-xl font-bold text-gray-900">৳ {{ number_format($summary['total_discount']) }}</p>
            </div>
        </div>

        <!-- Daily Collection Table -->
        <div class="glass-card overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">{{ __('Daily Collection Breakdown') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Transactions') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($dailyCollection as $day)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $day['date']->format('d M Y (l)') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                {{ $day['count'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 font-medium">
                                ৳ {{ number_format($day['amount']) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-gray-500">{{ __('No collections this month') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-white/50 font-medium">
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ __('Total') }}</td>
                            <td class="px-4 py-3 text-sm text-right">{{ $dailyCollection->sum('count') }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-600">৳ {{ number_format($summary['total_collected']) }}</td>
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
