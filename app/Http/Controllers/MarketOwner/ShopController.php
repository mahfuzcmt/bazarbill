<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $shops = $query->orderBy('shop_number')->paginate(15)->withQueryString();

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
            'area_sqft' => 'nullable|numeric|min:0',
            'rent_amount' => 'required|numeric|min:0',
            'advance_deposit' => 'nullable|numeric|min:0',
            'shop_type' => 'required|in:general,food,clothing,electronics,jewelry,pharmacy,other',
            'status' => 'required|in:active,vacant,suspended',
            'shop_owner_id' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
            'collector_id' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
            'notes' => 'nullable|string|max:500',
            'owner_name' => 'nullable|string|max:255|required_with:owner_phone,owner_email',
            'owner_phone' => 'nullable|string|max:20|required_with:owner_name',
            'owner_email' => 'nullable|email|max:255|unique:users,email',
        ]);

        $validated['market_id'] = auth()->user()->market_id;
        $validated['advance_deposit'] = $validated['advance_deposit'] ?? 0;

        // Create a shop owner login when owner details were supplied and no existing owner was picked.
        if (empty($validated['shop_owner_id']) && !empty($validated['owner_name'])) {
            $owner = User::create([
                'market_id' => auth()->user()->market_id,
                'name' => $validated['owner_name'],
                'email' => ($validated['owner_email'] ?? null) ?: User::placeholderEmail($validated['owner_phone'], auth()->user()->market_id),
                'phone' => $validated['owner_phone'],
                'password' => Hash::make($validated['owner_phone']),
                'role' => 'shop_owner',
                'is_active' => true,
                'language_preference' => 'bn',
            ]);
            $owner->assignRole('shop_owner');
            $validated['shop_owner_id'] = $owner->id;
        }

        unset($validated['owner_name'], $validated['owner_phone'], $validated['owner_email']);

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
            'shop_owner_id' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
            'collector_id' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['advance_deposit'] = $validated['advance_deposit'] ?? 0;

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
            'collector_id' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
        ]);

        $shop->update(['collector_id' => $validated['collector_id']]);

        return back()->with('success', __('Collector assigned successfully'));
    }

    public function assignOwner(Request $request, Shop $shop)
    {
        $validated = $request->validate([
            'shop_owner_id' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
        ]);

        $shop->update(['shop_owner_id' => $validated['shop_owner_id']]);

        return back()->with('success', __('Owner assigned successfully'));
    }
}
