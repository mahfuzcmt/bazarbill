<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
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

    public function __construct(protected SubscriptionService $subscriptions) {}

    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('monthly_price')->get();

        return view('admin.markets.create', compact('plans'));
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
            'plan_id' => ['nullable', Rule::exists('plans', 'id')->where('is_active', true)],
            'subscription_start' => 'nullable|in:trial,none',
        ]);

        $planId = $validated['plan_id'] ?? null;
        $start = $validated['subscription_start'] ?? 'trial';
        unset($validated['plan_id'], $validated['subscription_start']);

        $validated['slug'] = Str::slug($validated['name']) . '-' . Str::random(5);

        $market = Market::create($validated);

        if ($planId && $start === 'trial') {
            $this->subscriptions->startTrial($market, Plan::findOrFail($planId), auth()->id());
        } elseif ($planId) {
            // Plan assigned without a period: the admin records the paid activation next.
            $market->forceFill(['plan_id' => $planId])->save();
        }

        return redirect()->route('admin.markets.index')
            ->with('success', __('Market created successfully.'));
    }

    public function show(Market $market)
    {
        $market->load(['shops', 'users', 'plan']);

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
