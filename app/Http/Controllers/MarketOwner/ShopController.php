<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Shop::with(['shopOwner', 'collector']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('floor')) {
            $query->where('floor', $request->floor);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('shop_number', 'like', "%{$search}%")
                  ->orWhereHas('shopOwner', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $shops = $query->orderBy('shop_number')->paginate(15);

        $floors = Shop::distinct()->pluck('floor')->filter()->sort();
        $collectors = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'collector')
            ->where('is_active', true)
            ->get();

        return view('market-owner.shops.index', compact('shops', 'floors', 'collectors'));
    }

    public function create()
    {
        $collectors = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'collector')
            ->where('is_active', true)
            ->get();

        $shopOwners = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'shop_owner')
            ->where('is_active', true)
            ->whereDoesntHave('shop')
            ->get();

        return view('market-owner.shops.create', compact('collectors', 'shopOwners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('shops')->where('market_id', auth()->user()->market_id),
            ],
            'floor' => 'nullable|string|max:50',
            'area_sqft' => 'nullable|numeric|min:0',
            'rent_amount' => 'required|numeric|min:0',
            'advance_deposit' => 'nullable|numeric|min:0',
            'shop_type' => 'required|in:general,food,clothing,electronics,jewelry,pharmacy,other',
            'status' => 'required|in:active,vacant,suspended',
            'shop_owner_id' => 'nullable|exists:users,id',
            'collector_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['market_id'] = auth()->user()->market_id;

        Shop::create($validated);

        return redirect()->route('market-owner.shops.index')
            ->with('success', __('shops.created'));
    }

    public function show(Shop $shop)
    {
        $shop->load(['shopOwner', 'collector', 'invoices' => function ($q) {
            $q->orderBy('billing_month', 'desc')->limit(12);
        }, 'payments' => function ($q) {
            $q->orderBy('payment_date', 'desc')->limit(10);
        }]);

        return view('market-owner.shops.show', compact('shop'));
    }

    public function edit(Shop $shop)
    {
        $collectors = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'collector')
            ->where('is_active', true)
            ->get();

        $shopOwners = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'shop_owner')
            ->where('is_active', true)
            ->where(function ($q) use ($shop) {
                $q->whereDoesntHave('shop')
                  ->orWhere('id', $shop->shop_owner_id);
            })
            ->get();

        return view('market-owner.shops.edit', compact('shop', 'collectors', 'shopOwners'));
    }

    public function update(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'shop_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('shops')->where('market_id', auth()->user()->market_id)->ignore($shop->id),
            ],
            'floor' => 'nullable|string|max:50',
            'area_sqft' => 'nullable|numeric|min:0',
            'rent_amount' => 'required|numeric|min:0',
            'advance_deposit' => 'nullable|numeric|min:0',
            'shop_type' => 'required|in:general,food,clothing,electronics,jewelry,pharmacy,other',
            'status' => 'required|in:active,vacant,suspended',
            'shop_owner_id' => 'nullable|exists:users,id',
            'collector_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $shop->update($validated);

        return redirect()->route('market-owner.shops.index')
            ->with('success', __('shops.updated'));
    }

    public function destroy(Shop $shop)
    {
        // Check if shop has invoices
        if ($shop->invoices()->exists()) {
            return back()->with('error', __('Cannot delete shop with existing invoices'));
        }

        $shop->delete();

        return redirect()->route('market-owner.shops.index')
            ->with('success', __('shops.deleted'));
    }

    public function assignCollector(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'collector_id' => 'nullable|exists:users,id',
        ]);

        $shop->update(['collector_id' => $validated['collector_id']]);

        return back()->with('success', __('Collector assigned successfully'));
    }

    public function assignOwner(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'shop_owner_id' => 'nullable|exists:users,id',
        ]);

        $shop->update(['shop_owner_id' => $validated['shop_owner_id']]);

        return back()->with('success', __('Owner assigned successfully'));
    }
}
