<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MarketController extends Controller
{
    public function index()
    {
        $markets = Market::withCount(['shops', 'users'])
            ->latest()
            ->paginate(10)->withQueryString();

        return view('admin.markets.index', compact('markets'));
    }

    public function create()
    {
        return view('admin.markets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address_bn' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);

        $market = Market::create($validated);

        return redirect()->route('admin.markets.index')
            ->with('success', __('Market created successfully.'));
    }

    public function show(Market $market)
    {
        $market->load(['shops', 'users']);

        $stats = [
            'total_shops' => $market->shops()->count(),
            'active_shops' => $market->shops()->where('status', 'active')->count(),
            'total_users' => $market->users()->count(),
            'total_collection' => $market->payments()->sum('amount'),
            'total_due' => $market->invoices()->sum('due_amount'),
        ];

        return view('admin.markets.show', compact('market', 'stats'));
    }

    public function edit(Market $market)
    {
        return view('admin.markets.edit', compact('market'));
    }

    public function update(Request $request, Market $market)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address_bn' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $market->update($validated);

        return redirect()->route('admin.markets.index')
            ->with('success', __('Market updated successfully.'));
    }

    public function destroy(Market $market)
    {
        $market->delete();

        return redirect()->route('admin.markets.index')
            ->with('success', __('Market deleted successfully.'));
    }
}
