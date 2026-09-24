<x-app-layout>
    <x-slot name="header">{{ __('reports.reports') }}</x-slot>

    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_shops') }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalShops }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_billed') }}</p>
                <p class="text-2xl font-bold text-indigo-600">৳ {{ number_format($totalBilled) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_collected') }}</p>
                <p class="text-2xl font-bold text-green-600">৳ {{ number_format($totalCollected) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('reports.total_due') }}</p>
                <p class="text-2xl font-bold text-red-600">৳ {{ number_format($totalDue) }}</p>
            </div>
        </div>

        <!-- Report Types -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Collection Report -->
            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.collection_report') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('reports.collection_report_desc') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('market-owner.reports.collection') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500">{{ __('reports.from_date') }}</label>
                                <input type="date" name="from_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                       class="mt-1 block w-full glass-input text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">{{ __('reports.to_date') }}</label>
                                <input type="date" name="to_date" value="{{ now()->format('Y-m-d') }}"
                                       class="mt-1 block w-full glass-input text-sm">
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="format" value="pdf"
                                    class="flex-1 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-medium hover:bg-rose-100 text-sm transition">
                                PDF
                            </button>
                            <button type="submit" name="format" value="excel"
                                    class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-medium hover:bg-emerald-100 text-sm transition">
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Due Report -->
            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.due_report') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('reports.due_report_desc') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('market-owner.reports.due') }}" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-xs text-gray-500">{{ __('reports.filter_by') }}</label>
                            <select name="filter" class="mt-1 block w-full glass-input text-sm">
                                <option value="all">{{ __('reports.all_dues') }}</option>
                                <option value="overdue">{{ __('reports.overdue_only') }}</option>
                                <option value="pending">{{ __('reports.pending_only') }}</option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="format" value="pdf"
                                    class="flex-1 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-medium hover:bg-rose-100 text-sm transition">
                                PDF
                            </button>
                            <button type="submit" name="format" value="excel"
                                    class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-medium hover:bg-emerald-100 text-sm transition">
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Shop Report -->
            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.shop_report') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('reports.shop_report_desc') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('market-owner.reports.shops') }}" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-xs text-gray-500">{{ __('reports.shop_status') }}</label>
                            <select name="status" class="mt-1 block w-full glass-input text-sm">
                                <option value="all">{{ __('messages.all') }}</option>
                                <option value="active">{{ __('shops.status_active') }}</option>
                                <option value="vacant">{{ __('shops.status_vacant') }}</option>
                                <option value="suspended">{{ __('shops.status_suspended') }}</option>
                            </select>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="format" value="pdf"
                                    class="flex-1 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-medium hover:bg-rose-100 text-sm transition">
                                PDF
                            </button>
                            <button type="submit" name="format" value="excel"
                                    class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-medium hover:bg-emerald-100 text-sm transition">
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Monthly Summary -->
            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.monthly_summary') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('reports.monthly_summary_desc') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('market-owner.reports.monthly') }}" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-xs text-gray-500">{{ __('reports.select_month') }}</label>
                            <input type="month" name="month" value="{{ now()->format('Y-m') }}"
                                   class="mt-1 block w-full glass-input text-sm">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="format" value="pdf"
                                    class="flex-1 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-medium hover:bg-rose-100 text-sm transition">
                                PDF
                            </button>
                            <button type="submit" name="format" value="excel"
                                    class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-medium hover:bg-emerald-100 text-sm transition">
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Staff Performance -->
            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.staff_performance') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('reports.staff_performance_desc') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('market-owner.reports.staff') }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500">{{ __('reports.from_date') }}</label>
                                <input type="date" name="from_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                       class="mt-1 block w-full glass-input text-sm">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500">{{ __('reports.to_date') }}</label>
                                <input type="date" name="to_date" value="{{ now()->format('Y-m-d') }}"
                                       class="mt-1 block w-full glass-input text-sm">
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="format" value="pdf"
                                    class="flex-1 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-medium hover:bg-rose-100 text-sm transition">
                                PDF
                            </button>
                            <button type="submit" name="format" value="excel"
                                    class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-medium hover:bg-emerald-100 text-sm transition">
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoice Report -->
            <div class="glass-card overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.invoice_report') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('reports.invoice_report_desc') }}</p>
                        </div>
                    </div>
                    <form action="{{ route('market-owner.reports.invoices') }}" method="GET" class="space-y-4">
                        <div>
                            <label class="block text-xs text-gray-500">{{ __('reports.billing_month') }}</label>
                            <input type="month" name="billing_month" value="{{ now()->format('Y-m') }}"
                                   class="mt-1 block w-full glass-input text-sm">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="format" value="pdf"
                                    class="flex-1 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-700 font-medium hover:bg-rose-100 text-sm transition">
                                PDF
                            </button>
                            <button type="submit" name="format" value="excel"
                                    class="flex-1 px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 font-medium hover:bg-emerald-100 text-sm transition">
                                Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Recent Generated Reports -->
        @if($recentReports && $recentReports->count() > 0)
        <div class="glass-card overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('reports.recent_reports') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="glass-thead">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('reports.report_type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('reports.generated_at') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('reports.generated_by') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @foreach($recentReports as $report)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->type }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $report->user->name ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ $report->download_url }}" class="text-indigo-600 hover:text-indigo-900">
                                    {{ __('messages.download') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
