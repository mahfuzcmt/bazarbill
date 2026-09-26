<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The markets an owner belongs to, and opening additional ones.
 */
class MyMarketController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptions) {}

    public function index()
    {
        $user = auth()->user();
        $markets = $user->markets()->with('plan')->withCount('shops')->orderBy('name')->get();
        $plan = Plan::default();

        return view('market-owner.markets.index', compact('markets', 'plan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
        ]);

        $plan = Plan::default();
        if (!$plan) {
            return back()->with('error', __('mymarkets.no_plan'));
        }

        $user = auth()->user();

        $market = DB::transaction(function () use ($validated, $plan, $user) {
            $market = Market::create([
                'name' => $validated['name'],
                'name_bn' => $validated['name_bn'] ?? null,
                'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? $user->phone,
                'email' => $user->email,
                'status' => 'active',
                'settings' => ['invoice_prefix' => 'INV', 'due_days' => 7, 'grace_days' => 3, 'late_fee_percent' => 0, 'auto_generate' => false, 'sms_on_invoice' => true, 'auto_reminder' => false],
            ]);

            $user->markets()->attach($market->id);
            $this->subscriptions->startTrial($market, $plan, $user->id);
            $user->switchMarket($market);

            return $market;
        });

        return redirect()->route('market-owner.dashboard')
            ->with('success', __('mymarkets.created', ['market' => $market->name, 'days' => $plan->trial_days]));
    }
}
