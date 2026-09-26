<?php

namespace App\Http\Controllers;

class SubscriptionExpiredController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $market = $user->market;

        if ($user->isSuperAdmin() || !$market || $market->hasActiveSubscription()) {
            return redirect()->route('dashboard');
        }

        $market->load('plan');
        $lastSubscription = $market->subscriptions()->with('plan')->latest('ends_at')->first();
        $otherMarkets = $user->markets()->where('markets.id', '!=', $market->id)->get()->filter->hasActiveSubscription();

        return view('subscription.expired', compact('market', 'lastSubscription', 'otherMarkets'));
    }
}
