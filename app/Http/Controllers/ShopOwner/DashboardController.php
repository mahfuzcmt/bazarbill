<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Notice;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shops = $user->ownedShops()->orderBy('shop_number')->get();
        $shop = $shops->first();

        if (!$shop) {
            return view('shop-owner.dashboard', ['shop' => null]);
        }

        $shopIds = $shops->pluck('id');

        // This month's invoice for each shop
        $currentInvoices = Invoice::whereIn('shop_id', $shopIds)
            ->with('shop')
            ->where('billing_month', now()->format('Y-m'))
            ->orderBy('shop_id')
            ->get();
        $currentInvoice = $currentInvoices->first();

        // Total due across all shops
        $totalDue = Invoice::whereIn('shop_id', $shopIds)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('due_amount');

        // Total paid
        $totalPaid = Payment::whereIn('shop_id', $shopIds)->sum('amount');

        // Total invoices
        $totalInvoices = Invoice::whereIn('shop_id', $shopIds)->count();

        // Recent invoices
        $recentInvoices = Invoice::whereIn('shop_id', $shopIds)
            ->with('shop')
            ->orderBy('billing_month', 'desc')
            ->limit(6)
            ->get();

        // Recent payments
        $recentPayments = Payment::whereIn('shop_id', $shopIds)
            ->with(['collector', 'shop'])
            ->orderBy('payment_date', 'desc')
            ->limit(5)
            ->get();

        // Active notices
        $notices = Notice::active()
            ->forRole('shop_owner')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('shop-owner.dashboard', compact(
            'shop',
            'shops',
            'currentInvoices',
            'currentInvoice',
            'totalDue',
            'totalPaid',
            'totalInvoices',
            'recentInvoices',
            'recentPayments',
            'notices'
        ));
    }

    public function notices()
    {
        $notices = Notice::active()
            ->forRole('shop_owner')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withQueryString();

        return view('shop-owner.notices.index', compact('notices'));
    }
}
