<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Assigned shops count
        $assignedShops = Shop::where('collector_id', $user->id)->count();

        // Get assigned shop IDs
        $assignedShopIds = Shop::where('collector_id', $user->id)->pluck('id');

        // Shops with dues
        $shopsWithDues = Shop::where('collector_id', $user->id)
            ->with(['shopOwner', 'invoices' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue']);
            }])
            ->get()
            ->map(function ($shop) {
                $shop->total_due = $shop->invoices->sum('due_amount');
                return $shop;
            })
            ->filter(fn($shop) => $shop->total_due > 0)
            ->sortByDesc('total_due')
            ->take(10);

        // Pending invoices count
        $pendingInvoices = Invoice::whereIn('shop_id', $assignedShopIds)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->count();

        // Total due for assigned shops
        $totalDue = Invoice::whereIn('shop_id', $assignedShopIds)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('due_amount');

        // Today's collections
        $todayCollection = Payment::where('collected_by', $user->id)
            ->whereDate('payment_date', today())
            ->sum('amount');

        // Recent payments by this collector
        $recentPayments = Payment::where('collected_by', $user->id)
            ->with(['shop', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('collector.dashboard', compact(
            'assignedShops',
            'shopsWithDues',
            'pendingInvoices',
            'totalDue',
            'todayCollection',
            'recentPayments'
        ));
    }
}
