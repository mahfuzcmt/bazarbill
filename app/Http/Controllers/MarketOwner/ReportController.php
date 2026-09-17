<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PdfService;

class ReportController extends Controller
{
    public function index()
    {
        $marketId = auth()->user()->market_id;

        // Quick stats
        $totalShops = Shop::where('market_id', $marketId)->count();

        $totalBilled = Invoice::whereHas('shop', function ($q) use ($marketId) {
            $q->where('market_id', $marketId);
        })->sum('total_amount');

        $totalCollected = Payment::whereHas('shop', function ($q) use ($marketId) {
            $q->where('market_id', $marketId);
        })->sum('amount');

        $totalDue = Invoice::whereHas('shop', function ($q) use ($marketId) {
            $q->where('market_id', $marketId);
        })->whereIn('status', ['pending', 'partial', 'overdue'])->sum('due_amount');

        // Recent generated reports (if you have a reports table)
        $recentReports = collect(); // Placeholder - can be implemented with a GeneratedReport model

        return view('market-owner.reports.index', compact(
            'totalShops',
            'totalBilled',
            'totalCollected',
            'totalDue',
            'recentReports'
        ));
    }

    public function monthlyCollection(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $payments = Payment::with(['shop', 'collector', 'invoice'])
            ->whereRaw("strftime('%Y-%m', payment_date) = ?", [$month])
            ->orderBy('payment_date')
            ->get();

        $totals = $payments->groupBy('collected_by')->map(function ($group) {
            return [
                'collector' => $group->first()->collector,
                'total' => $group->sum('amount'),
                'count' => $group->count(),
            ];
        });

        $grandTotal = $payments->sum('amount');

        return view('market-owner.reports.monthly-collection', compact('payments', 'totals', 'grandTotal', 'month'));
    }

    public function dueReport(Request $request)
    {
        $shops = Shop::with(['shopOwner', 'invoices' => function ($q) {
            $q->whereIn('status', ['pending', 'partial', 'overdue']);
        }])
        ->whereHas('invoices', function ($q) {
            $q->whereIn('status', ['pending', 'partial', 'overdue']);
        })
        ->get()
        ->map(function ($shop) {
            $shop->total_due = $shop->invoices->sum('due_amount');
            return $shop;
        })
        ->sortByDesc('total_due');

        $grandTotal = $shops->sum('total_due');

        return view('market-owner.reports.due-report', compact('shops', 'grandTotal'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'payments');
        $format = $request->get('format', 'xlsx');
        $month = $request->get('month', now()->format('Y-m'));

        // Basic export - can be enhanced with proper Excel exports
        if ($type === 'payments') {
            $data = Payment::with(['shop', 'collector', 'invoice'])
                ->whereRaw("strftime('%Y-%m', payment_date) = ?", [$month])
                ->orderBy('payment_date')
                ->get();

            $filename = "payments-{$month}.csv";
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename={$filename}",
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Date', 'Shop', 'Invoice', 'Amount', 'Collector', 'Receipt']);

                foreach ($data as $payment) {
                    fputcsv($file, [
                        $payment->payment_date->format('Y-m-d'),
                        $payment->shop->shop_number,
                        $payment->invoice->invoice_number,
                        $payment->amount,
                        $payment->collector?->name ?? '-',
                        $payment->receipt_number,
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return back()->with('error', 'Invalid export type');
    }

    public function shopReport(Request $request)
    {
        $marketId = auth()->user()->market_id;
        $status = $request->get('status', 'all');
        $format = $request->get('format', 'view');

        $query = Shop::with(['shopOwner', 'collector'])
            ->where('market_id', $marketId)
            ->withCount('invoices')
            ->withSum('invoices', 'total_amount')
            ->withSum('invoices', 'paid_amount')
            ->withSum('invoices', 'due_amount');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $shops = $query->orderBy('shop_number')->get();

        $summary = [
            'total_shops' => $shops->count(),
            'active_shops' => $shops->where('status', 'active')->count(),
            'vacant_shops' => $shops->where('status', 'vacant')->count(),
            'suspended_shops' => $shops->where('status', 'suspended')->count(),
            'total_rent' => $shops->sum('rent_amount'),
            'total_billed' => $shops->sum('invoices_sum_total_amount') ?? 0,
            'total_collected' => $shops->sum('invoices_sum_paid_amount') ?? 0,
            'total_due' => $shops->sum('invoices_sum_due_amount') ?? 0,
        ];

        if ($format === 'pdf') {
            $pdf = PdfService::fromView('market-owner.reports.pdf.shop-report', compact('shops', 'summary', 'status'));
            return $pdf->download('shop-report-' . now()->format('Y-m-d') . '.pdf');
        }

        if ($format === 'excel') {
            return $this->exportShopReport($shops, $summary);
        }

        return view('market-owner.reports.shop-report', compact('shops', 'summary', 'status'));
    }

    public function monthlySummary(Request $request)
    {
        $marketId = auth()->user()->market_id;
        $month = $request->get('month', now()->format('Y-m'));
        $format = $request->get('format', 'view');

        // Invoices for the month
        $invoices = Invoice::where('market_id', $marketId)
            ->where('billing_month', $month)
            ->get();

        // Payments for the month
        $payments = Payment::where('market_id', $marketId)
            ->whereRaw("strftime('%Y-%m', payment_date) = ?", [$month])
            ->get();

        $summary = [
            'month' => $month,
            'total_invoices' => $invoices->count(),
            'total_billed' => $invoices->sum('total_amount'),
            'total_rent' => $invoices->sum('rent_amount'),
            'total_previous_due' => $invoices->sum('previous_due'),
            'total_late_fee' => $invoices->sum('late_fee'),
            'total_discount' => $invoices->sum('discount'),
            'total_collected' => $payments->sum('amount'),
            'total_due' => $invoices->sum('due_amount'),
            'paid_count' => $invoices->where('status', 'paid')->count(),
            'partial_count' => $invoices->where('status', 'partial')->count(),
            'pending_count' => $invoices->where('status', 'pending')->count(),
            'overdue_count' => $invoices->where('status', 'overdue')->count(),
            'collection_rate' => $invoices->sum('total_amount') > 0
                ? round(($payments->sum('amount') / $invoices->sum('total_amount')) * 100, 1)
                : 0,
        ];

        // Daily collection breakdown
        $dailyCollection = $payments->groupBy(function ($payment) {
            return $payment->payment_date->format('Y-m-d');
        })->map(function ($group) {
            return [
                'date' => $group->first()->payment_date,
                'count' => $group->count(),
                'amount' => $group->sum('amount'),
            ];
        })->sortKeys();

        if ($format === 'pdf') {
            $pdf = PdfService::fromView('market-owner.reports.pdf.monthly-summary', compact('summary', 'dailyCollection'));
            return $pdf->download('monthly-summary-' . $month . '.pdf');
        }

        if ($format === 'excel') {
            return $this->exportMonthlySummary($summary, $dailyCollection, $month);
        }

        return view('market-owner.reports.monthly-summary', compact('summary', 'dailyCollection', 'month'));
    }

    public function staffPerformance(Request $request)
    {
        $marketId = auth()->user()->market_id;
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $format = $request->get('format', 'view');

        // Get all collectors for this market
        $collectors = User::where('market_id', $marketId)
            ->where('role', 'collector')
            ->with(['assignedShops'])
            ->get();

        $performance = $collectors->map(function ($collector) use ($fromDate, $toDate) {
            $payments = Payment::where('collected_by', $collector->id)
                ->whereBetween('payment_date', [$fromDate, $toDate])
                ->get();

            $assignedShops = $collector->assignedShops->count();

            return [
                'collector' => $collector,
                'assigned_shops' => $assignedShops,
                'total_collections' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'avg_per_collection' => $payments->count() > 0 ? round($payments->sum('amount') / $payments->count(), 2) : 0,
                'daily_avg' => $this->calculateDailyAvg($payments, $fromDate, $toDate),
            ];
        })->sortByDesc('total_amount');

        $summary = [
            'total_collectors' => $collectors->count(),
            'total_collections' => $performance->sum('total_collections'),
            'total_amount' => $performance->sum('total_amount'),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];

        if ($format === 'pdf') {
            $pdf = PdfService::fromView('market-owner.reports.pdf.staff-performance', compact('performance', 'summary'));
            return $pdf->download('staff-performance-' . $fromDate . '-to-' . $toDate . '.pdf');
        }

        if ($format === 'excel') {
            return $this->exportStaffPerformance($performance, $summary);
        }

        return view('market-owner.reports.staff-performance', compact('performance', 'summary', 'fromDate', 'toDate'));
    }

    public function invoiceReport(Request $request)
    {
        $marketId = auth()->user()->market_id;
        $billingMonth = $request->get('billing_month', now()->format('Y-m'));
        $format = $request->get('format', 'view');

        $invoices = Invoice::with(['shop', 'shop.shopOwner'])
            ->where('market_id', $marketId)
            ->where('billing_month', $billingMonth)
            ->orderBy('invoice_number')
            ->get();

        $summary = [
            'billing_month' => $billingMonth,
            'total_invoices' => $invoices->count(),
            'total_amount' => $invoices->sum('total_amount'),
            'total_paid' => $invoices->sum('paid_amount'),
            'total_due' => $invoices->sum('due_amount'),
            'paid_count' => $invoices->where('status', 'paid')->count(),
            'partial_count' => $invoices->where('status', 'partial')->count(),
            'pending_count' => $invoices->where('status', 'pending')->count(),
            'overdue_count' => $invoices->where('status', 'overdue')->count(),
        ];

        if ($format === 'pdf') {
            $pdf = PdfService::fromView('market-owner.reports.pdf.invoice-report', compact('invoices', 'summary'));
            return $pdf->download('invoice-report-' . $billingMonth . '.pdf');
        }

        if ($format === 'excel') {
            return $this->exportInvoiceReport($invoices, $summary, $billingMonth);
        }

        return view('market-owner.reports.invoice-report', compact('invoices', 'summary', 'billingMonth'));
    }

    private function calculateDailyAvg($payments, $fromDate, $toDate)
    {
        $days = max(1, now()->parse($fromDate)->diffInDays(now()->parse($toDate)) + 1);
        return round($payments->sum('amount') / $days, 2);
    }

    private function exportShopReport($shops, $summary)
    {
        $filename = "shop-report-" . now()->format('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($shops) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Shop No', 'Floor', 'Owner', 'Collector', 'Rent', 'Total Billed', 'Paid', 'Due', 'Status']);

            foreach ($shops as $shop) {
                fputcsv($file, [
                    $shop->shop_number,
                    $shop->floor ?? '-',
                    $shop->shopOwner?->name ?? '-',
                    $shop->collector?->name ?? '-',
                    $shop->rent_amount,
                    $shop->invoices_sum_total_amount ?? 0,
                    $shop->invoices_sum_paid_amount ?? 0,
                    $shop->invoices_sum_due_amount ?? 0,
                    $shop->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportMonthlySummary($summary, $dailyCollection, $month)
    {
        $filename = "monthly-summary-{$month}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($summary, $dailyCollection) {
            $file = fopen('php://output', 'w');

            // Summary section
            fputcsv($file, ['Monthly Summary']);
            fputcsv($file, ['Total Invoices', $summary['total_invoices']]);
            fputcsv($file, ['Total Billed', $summary['total_billed']]);
            fputcsv($file, ['Total Collected', $summary['total_collected']]);
            fputcsv($file, ['Total Due', $summary['total_due']]);
            fputcsv($file, ['Collection Rate', $summary['collection_rate'] . '%']);
            fputcsv($file, []);

            // Daily breakdown
            fputcsv($file, ['Daily Collection']);
            fputcsv($file, ['Date', 'Transactions', 'Amount']);
            foreach ($dailyCollection as $day) {
                fputcsv($file, [
                    $day['date']->format('Y-m-d'),
                    $day['count'],
                    $day['amount'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportStaffPerformance($performance, $summary)
    {
        $filename = "staff-performance-" . now()->format('Y-m-d') . ".csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($performance) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Collector', 'Assigned Shops', 'Collections', 'Total Amount', 'Avg Per Collection', 'Daily Avg']);

            foreach ($performance as $p) {
                fputcsv($file, [
                    $p['collector']->name,
                    $p['assigned_shops'],
                    $p['total_collections'],
                    $p['total_amount'],
                    $p['avg_per_collection'],
                    $p['daily_avg'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportInvoiceReport($invoices, $summary, $month)
    {
        $filename = "invoice-report-{$month}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Invoice No', 'Shop', 'Owner', 'Total', 'Paid', 'Due', 'Status', 'Due Date']);

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->shop->shop_number,
                    $invoice->shop->shopOwner?->name ?? '-',
                    $invoice->total_amount,
                    $invoice->paid_amount,
                    $invoice->due_amount,
                    $invoice->status,
                    $invoice->due_date->format('Y-m-d'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
