<x-app-layout>
    <x-slot name="header">{{ __('reports.collection_report') }} - {{ $periodLabel }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Card -->
        <div class="glass-card p-4">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Total Collection') }}</p>
                    <p class="text-3xl font-bold text-green-600">৳ {{ number_format($grandTotal) }}</p>
                </div>
                <form action="{{ route('market-owner.reports.collection') }}" method="GET" class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs text-gray-500">{{ __('reports.from_date') }}</label>
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="mt-1 glass-input text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500">{{ __('reports.to_date') }}</label>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="mt-1 glass-input text-sm">
                    </div>
                    <button type="submit" class="btn-primary px-4 py-2 text-sm">{{ __('messages.filter') }}</button>
                </form>
                <div class="flex gap-2">
                    <a href="{{ route('market-owner.reports.collection', array_merge(['from_date' => $fromDate, 'to_date' => $toDate], ['format' => 'pdf'])) }}"
                       class="btn-danger px-3 py-2 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ __('Download PDF') }}
                    </a>
                    <a href="{{ route('market-owner.reports.collection', array_merge(['from_date' => $fromDate, 'to_date' => $toDate], ['format' => 'excel'])) }}"
                       class="btn-secondary px-3 py-2 text-sm text-emerald-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ __('Download Excel') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Collector Summary -->
        @if($totals->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">{{ __('Collection by Collector') }}</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                @foreach($totals as $data)
                <div class="bg-white/40 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                            <span class="text-indigo-600 font-semibold">{{ substr($data['collector']->name ?? '?', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $data['collector']->name ?? __('Unknown') }}</p>
                            <p class="text-sm text-gray-500">{{ $data['count'] }} {{ __('transactions') }}</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xl font-bold text-green-600">৳ {{ number_format($data['total']) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payments Table -->
        <div class="glass-card overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">{{ __('All Payments') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Receipt') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shop') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Invoice') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Amount') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('staff.roles.collector') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $payment->payment_date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                <span class="font-mono text-indigo-600">{{ $payment->receipt_number }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $payment->shop->shop_number }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $payment->invoice->invoice_number }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-green-600">
                                ৳ {{ number_format($payment->amount) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $payment->collector->name ?? '-' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">{{ __('No payments found for this period') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-white/50 font-medium">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm text-gray-700">{{ __('Total') }} ({{ $payments->count() }} {{ __('messages.nav.payments') }})</td>
                            <td class="px-4 py-3 text-sm text-right text-green-600">৳ {{ number_format($grandTotal) }}</td>
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
