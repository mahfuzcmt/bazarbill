<x-app-layout>
    <x-slot name="header">{{ __('reports.staff_performance') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Collectors') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_collectors'] }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Collections') }}</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $summary['total_collections'] }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Amount') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($summary['total_amount']) }}</p>
            </div>
        </div>

        <!-- Filter & Actions -->
        <div class="glass-card p-4 flex flex-wrap items-center justify-between gap-4">
            <form action="{{ route('market-owner.reports.staff') }}" method="GET" class="flex flex-wrap items-center gap-4">
                <div>
                    <label class="block text-xs text-gray-500">{{ __('From') }}</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}"
                           class="glass-input text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500">{{ __('To') }}</label>
                    <input type="date" name="to_date" value="{{ $toDate }}"
                           class="glass-input text-sm">
                </div>
                <button type="submit" class="px-4 py-2 btn-primary text-sm mt-4">
                    {{ __('Filter') }}
                </button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('market-owner.reports.staff', ['from_date' => $fromDate, 'to_date' => $toDate, 'format' => 'pdf']) }}"
                   class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('market-owner.reports.staff', ['from_date' => $fromDate, 'to_date' => $toDate, 'format' => 'excel']) }}"
                   class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    {{ __('Download Excel') }}
                </a>
            </div>
        </div>

        <!-- Performance Table -->
        <div class="glass-card overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">{{ __('Collector Performance') }} ({{ \Carbon\Carbon::parse($fromDate)->format('d M') }} - {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Rank') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('staff.roles.collector') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Assigned Shops') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Collections') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total Amount') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Avg/Collection') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Daily Avg') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @php $rank = 1; @endphp
                        @forelse($performance as $p)
                        <tr class="hover:bg-gray-50 {{ $rank <= 3 ? 'bg-yellow-50' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($rank === 1)
                                    <span class="text-2xl">🥇</span>
                                @elseif($rank === 2)
                                    <span class="text-2xl">🥈</span>
                                @elseif($rank === 3)
                                    <span class="text-2xl">🥉</span>
                                @else
                                    <span class="text-gray-500 font-medium">#{{ $rank }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $p['collector']->name }}</div>
                                <div class="text-sm text-gray-500">{{ $p['collector']->phone ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                {{ $p['assigned_shops'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                {{ $p['total_collections'] }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                ৳ {{ number_format($p['total_amount']) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                ৳ {{ number_format($p['avg_per_collection']) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                ৳ {{ number_format($p['daily_avg']) }}
                            </td>
                        </tr>
                        @php $rank++; @endphp
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">{{ __('No collectors found') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-white/50 font-medium">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-700">{{ __('Total') }}</td>
                            <td class="px-4 py-3 text-sm text-right">{{ $summary['total_collections'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-600">৳ {{ number_format($summary['total_amount']) }}</td>
                            <td colspan="2"></td>
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
