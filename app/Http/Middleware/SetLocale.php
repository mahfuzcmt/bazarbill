<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check session first
        if ($request->session()->has('locale')) {
            app()->setLocale($request->session()->get('locale'));
        }
        // Then check user preference
        elseif ($request->user()) {
            app()->setLocale($request->user()->getPreferredLocale());
        }
        // Default to Bengali
        else {
            app()->setLocale('bn');
        }

        return $next($request);
    }
}
