<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class LandingController extends Controller
{
    public const CONTACT_PHONE = '01805995662';

    public function __invoke()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('monthly_price')->get();

        $phone = config('services.support.sales_phone') ?: self::CONTACT_PHONE;
        $whatsapp = 'https://wa.me/88' . preg_replace('/\D/', '', $phone) . '?text=' . rawurlencode(__('landing.whatsapp_prefill'));

        return view('landing', compact('plans', 'phone', 'whatsapp'));
    }
}
