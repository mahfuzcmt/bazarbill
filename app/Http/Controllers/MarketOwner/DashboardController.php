<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $marketId = $user->market_id;

        // Shop statistics
        $shopStats = Shop::selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'vacant' THEN 1 ELSE 0 END) as vacant,
            SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) as suspended
        ")->first();

        // Invoice statistics for current month
        $currentMonth = now()->format('Y-m');
        $invoiceStats = Invoice::selectRaw("
            COUNT(*) as total,
            SUM(total_amount) as total_amount,
            SUM(paid_amount) as paid_amount,
            SUM(due_amount) as due_amount,
            SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_count
        ")->where('billing_month', $currentMonth)->first();

        // Payment statistics for current month
        $paymentStats = Payment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->selectRaw("
                COUNT(*) as total_transactions,
                SUM(amount) as total_collected
            ")->first();

        // Recent payments
        $recentPayments = Payment::with(['shop', 'collector', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending complaints
        $pendingComplaints = Complaint::with(['shop', 'submitter'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Overdue invoices
        $overdueInvoices = Invoice::with('shop')
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Monthly collection trend (last 6 months)
        // Grouped in PHP so the query stays portable between SQLite and MySQL.
        $collectionTrend = Payment::where('payment_date', '>=', now()->subMonths(6)->startOfMonth())
            ->get(['payment_date', 'amount'])
            ->groupBy(fn ($payment) => $payment->payment_date->format('Y-m'))
            ->map(fn ($group, $month) => (object) ['month' => $month, 'total' => $group->sum('amount')])
            ->sortKeys()
            ->values();

        return view('market-owner.dashboard', compact(
            'shopStats',
            'invoiceStats',
            'paymentStats',
            'recentPayments',
            'pendingComplaints',
            'overdueInvoices',
            'collectionTrend',
            'currentMonth'
        ));
    }
}
