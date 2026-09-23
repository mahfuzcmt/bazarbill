<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Shop::where('collector_id', $user->id)
            ->with(['shopOwner', 'invoices' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue']);
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shop_number', 'like', "%{$search}%")
                  ->orWhereHas('shopOwner', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $shops = $query->orderBy('shop_number')->get()->map(function ($shop) {
            $shop->total_due = $shop->invoices->sum('due_amount');
            return $shop;
        });

        return view('collector.shops.index', compact('shops'));
    }

    public function show(Shop $shop)
    {
        // Ensure collector is assigned to this shop
        if ($shop->collector_id !== auth()->id()) {
            abort(403);
        }

        $shop->load('shopOwner');

        // Total due amount
        $totalDue = Invoice::where('shop_id', $shop->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('due_amount');

        // Total paid
        $totalPaid = Payment::where('shop_id', $shop->id)->sum('amount');

        // Pending invoices count
        $pendingInvoices = Invoice::where('shop_id', $shop->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->count();

        // Pending invoices list
        $pendingInvoicesList = Invoice::where('shop_id', $shop->id)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('billing_month', 'desc')
            ->get();

        // Recent payments
        $recentPayments = Payment::where('shop_id', $shop->id)
            ->with('collector')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        return view('collector.shops.show', compact(
            'shop',
            'totalDue',
            'totalPaid',
            'pendingInvoices',
            'pendingInvoicesList',
            'recentPayments'
        ));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->market->canAddShop()) {
            return back()->withInput()->with('error', __('shops.limit_reached', ['limit' => auth()->user()->market->shopLimit()]));
        }

        $validated = $request->validate([
            'shop_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('shops')->where('market_id', auth()->user()->market_id),
            ],
            'floor' => 'nullable|string|max:50',
            'rent_amount' => 'required|numeric|min:0',
            'shop_type' => 'required|in:general,food,clothing,electronics,jewelry,pharmacy,other',
            'owner_name' => 'required|string|max:255',
            'owner_phone' => 'required|string|max:20',
            'owner_email' => 'nullable|email|unique:users,email',
        ]);

        // Create shop owner user
        $shopOwner = User::create([
            'market_id' => auth()->user()->market_id,
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'] ?? strtolower(str_replace(' ', '.', $validated['owner_name'])) . '@' . auth()->user()->market_id . '.local',
            'phone' => $validated['owner_phone'],
            'password' => Hash::make('123456'), // Default password
            'role' => 'shop_owner',
            'is_active' => true,
            'language_preference' => 'bn',
        ]);
        $shopOwner->assignRole('shop_owner');

        // Create shop
        Shop::create([
            'market_id' => auth()->user()->market_id,
            'shop_owner_id' => $shopOwner->id,
            'collector_id' => auth()->id(),
            'shop_number' => $validated['shop_number'],
            'floor' => $validated['floor'],
            'rent_amount' => $validated['rent_amount'],
            'shop_type' => $validated['shop_type'],
            'status' => 'active',
        ]);

        return redirect()->route('collector.shops.index')
            ->with('success', __('shops.created'));
    }
}
