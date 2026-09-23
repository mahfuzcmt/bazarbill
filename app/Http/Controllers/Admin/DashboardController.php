<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\User;
use App\Models\Shop;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_markets' => Market::count(),
            'active_markets' => Market::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_shops' => Shop::count(),
            'total_invoices' => Invoice::count(),
            'total_payments' => Payment::count(),
            'total_collection' => Payment::sum('amount'),
            'total_due' => Invoice::sum('due_amount'),
            'paying_markets' => Market::where('subscription_status', 'active')->count(),
            'trial_markets' => Market::where('subscription_status', 'trial')->count(),
            'expiring_markets' => Market::whereIn('subscription_status', ['trial', 'active'])
                ->whereBetween('subscription_ends_at', [today(), today()->addDays(7)])->count(),
            'mrr' => (float) Market::where('subscription_status', 'active')
                ->join('plans', 'plans.id', '=', 'markets.plan_id')
                ->sum('plans.monthly_price'),
        ];

        $recentMarkets = Market::latest()->take(5)->get();
        $recentUsers = User::with('market')->latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentMarkets', 'recentUsers'));
    }
}
