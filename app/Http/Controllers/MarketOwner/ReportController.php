<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
}
