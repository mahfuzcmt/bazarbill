<?php

namespace App\Http\Controllers;

use App\Models\Market;

class MarketSwitchController extends Controller
{
    public function __invoke(Market $market)
    {
        $user = auth()->user();

        if ($user->isSuperAdmin() || !$user->switchMarket($market)) {
            abort(403);
        }

        return redirect()->route('dashboard')->with('success', __('mymarkets.switched', ['market' => $market->getLocalizedName()]));
    }
}
