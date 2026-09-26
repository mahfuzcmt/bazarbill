<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admins bypass market check
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has a market and it's active
        if ($user && $user->market_id) {
            if (!$user->market || !$user->market->isActive()) {
                auth()->logout();
                return redirect()->route('login')
                    ->with('error', __('messages.market_inactive'));
            }

            // Subscription lapsed: keep the session, show the renewal page.
            if (!$user->market->hasActiveSubscription() && !$request->routeIs('subscription.expired', 'logout', 'profile.*', 'locale.switch', 'market.switch')) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
