<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptions) {}

    /**
     * Every market with its plan, status and renewal date.
     */
    public function index(Request $request)
    {
        $markets = Market::with('plan')
            ->when($request->filled('status'), function ($q) use ($request) {
                if ($request->status === 'none') {
                    return $q->whereNull('subscription_status');
                }
                if ($request->status === 'expiring') {
                    return $q->whereIn('subscription_status', ['trial', 'active'])
                        ->whereBetween('subscription_ends_at', [today(), today()->addDays(7)]);
                }
                return $q->where('subscription_status', $request->status);
            })
            ->when($request->filled('plan_id'), fn ($q) => $q->where('plan_id', $request->plan_id))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->orderByRaw('subscription_ends_at IS NULL')
            ->orderBy('subscription_ends_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'trial' => Market::where('subscription_status', 'trial')->count(),
            'active' => Market::where('subscription_status', 'active')->count(),
            'expiring' => Market::whereIn('subscription_status', ['trial', 'active'])
                ->whereBetween('subscription_ends_at', [today(), today()->addDays(7)])->count(),
            'expired' => Market::whereIn('subscription_status', ['expired', 'cancelled'])->count(),
            'none' => Market::whereNull('subscription_status')->count(),
            'mrr' => (float) Market::where('subscription_status', 'active')
                ->join('plans', 'plans.id', '=', 'markets.plan_id')
                ->sum('plans.monthly_price'),
        ];

        $plans = Plan::orderBy('sort_order')->get();

        return view('admin.subscriptions.index', compact('markets', 'counts', 'plans'));
    }

    public function show(Market $market)
    {
        $market->load('plan');
        $current = $market->currentSubscription();
        $history = $market->subscriptions()->with(['plan', 'creator'])->latest('starts_at')->latest('id')->get();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('monthly_price')->get();
        $shopCount = $market->shops()->count();

        return view('admin.subscriptions.show', compact('market', 'current', 'history', 'plans', 'shopCount'));
    }

    /**
     * Record a paid activation / renewal entered by the super admin.
     */
    public function store(Request $request, Market $market)
    {
        $validated = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'billing_cycle' => ['required', Rule::in([Subscription::CYCLE_MONTHLY, Subscription::CYCLE_YEARLY])],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['nullable', Rule::in(Subscription::PAYMENT_METHODS)],
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);

        if ($validated['billing_cycle'] === Subscription::CYCLE_YEARLY && !$plan->offersYearly()) {
            return back()->withInput()->with('error', __('This plan does not offer yearly billing.'));
        }

        $subscription = $this->subscriptions->activate($market, $plan, $validated['billing_cycle'], [
            'amount_paid' => $validated['amount_paid'],
            'payment_method' => $validated['payment_method'] ?? null,
            'payment_reference' => $validated['payment_reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ], auth()->id());

        return redirect()->route('admin.markets.subscription', $market)
            ->with('success', __(':plan activated until :date.', [
                'plan' => $plan->name,
                'date' => $subscription->ends_at->format('d M Y'),
            ]));
    }

    public function trial(Request $request, Market $market)
    {
        $validated = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
        ]);

        if ($market->hasActiveSubscription() && $market->subscription_ends_at !== null) {
            return back()->with('error', __('This market already has a current subscription.'));
        }

        $subscription = $this->subscriptions->startTrial($market, Plan::findOrFail($validated['plan_id']), auth()->id());

        return redirect()->route('admin.markets.subscription', $market)
            ->with('success', __('Trial started until :date.', ['date' => $subscription->ends_at->format('d M Y')]));
    }

    public function cancel(Request $request, Market $market)
    {
        $validated = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $this->subscriptions->cancel($market, $validated['reason'] ?? null);

        return redirect()->route('admin.markets.subscription', $market)
            ->with('success', __('Subscription cancelled. The market is now locked out until renewed.'));
    }
}
