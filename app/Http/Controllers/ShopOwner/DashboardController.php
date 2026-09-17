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
        $shop = $user->shop;

        if (!$shop) {
            return view('shop-owner.dashboard', ['shop' => null]);
        }

        // Current invoice
        $currentInvoice = Invoice::where('shop_id', $shop->id)
            ->where('billing_month', now()->format('Y-m'))
            ->first();

        // Total due
        $totalDue = Invoice::where('shop_id', $shop->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('due_amount');

        // Total paid
        $totalPaid = Payment::where('shop_id', $shop->id)->sum('amount');

        // Total invoices
        $totalInvoices = Invoice::where('shop_id', $shop->id)->count();

        // Recent invoices
        $recentInvoices = Invoice::where('shop_id', $shop->id)
            ->orderBy('billing_month', 'desc')
            ->limit(6)
            ->get();

        // Recent payments
        $recentPayments = Payment::where('shop_id', $shop->id)
            ->with('collector')
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
