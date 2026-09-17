<x-app-layout>
    <x-slot name="header">{{ __('reports.invoice_report') }} - {{ \Carbon\Carbon::parse($billingMonth)->format('F Y') }}</x-slot>

    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Invoices') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $summary['total_invoices'] }}</p>
                <div class="mt-2 flex flex-wrap gap-1 text-xs">
                    <span class="px-1.5 py-0.5 bg-green-100 text-green-800 rounded">{{ $summary['paid_count'] }} Paid</span>
                    <span class="px-1.5 py-0.5 bg-yellow-100 text-yellow-800 rounded">{{ $summary['partial_count'] }} Partial</span>
                    <span class="px-1.5 py-0.5 bg-gray-100 text-gray-800 rounded">{{ $summary['pending_count'] }} Pending</span>
                    <span class="px-1.5 py-0.5 bg-red-100 text-red-800 rounded">{{ $summary['overdue_count'] }} Overdue</span>
                </div>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Amount') }}</p>
                <p class="text-2xl font-bold text-indigo-600">৳ {{ number_format($summary['total_amount']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Paid') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($summary['total_paid']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Total Due') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($summary['total_due']) }}</p>
            </div>
        </div>

        <!-- Filter & Actions -->
        <div class="glass-card p-4 flex flex-wrap items-center justify-between gap-4">
            <form action="{{ route('market-owner.reports.invoices') }}" method="GET" class="flex items-center gap-4">
                <input type="month" name="billing_month" value="{{ $billingMonth }}"
                       class="glass-input text-sm" onchange="this.form.submit()">
            </form>
            <div class="flex gap-2">
                <a href="{{ route('market-owner.reports.invoices', ['billing_month' => $billingMonth, 'format' => 'pdf']) }}"
                   class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">
                    {{ __('Download PDF') }}
                </a>
                <a href="{{ route('market-owner.reports.invoices', ['billing_month' => $billingMonth, 'format' => 'excel']) }}"
                   class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    {{ __('Download Excel') }}
                </a>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Invoice') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Shop') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Owner') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Paid') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Due') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Due Date') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @forelse($invoices as $invoice)
                        <tr class="hover:bg-white/50 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <a href="{{ route('market-owner.invoices.show', $invoice) }}" class="text-indigo-600 hover:underline font-medium">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $invoice->shop->shop_number }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ $invoice->shop->shopOwner?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900">
                                ৳ {{ number_format($invoice->total_amount) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600">
                                ৳ {{ number_format($invoice->paid_amount) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">
                                ৳ {{ number_format($invoice->due_amount) }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $invoice->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $invoice->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600">
                                {{ $invoice->due_date->format('d M Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">{{ __('No invoices found for this month') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-white/50 font-medium">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-gray-700">{{ __('Total') }} ({{ $summary['total_invoices'] }} {{ __('messages.nav.invoices') }})</td>
                            <td class="px-4 py-3 text-sm text-right">৳ {{ number_format($summary['total_amount']) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-green-600">৳ {{ number_format($summary['total_paid']) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-red-600">৳ {{ number_format($summary['total_due']) }}</td>
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
