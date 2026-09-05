<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['en', 'bn'])) {
            abort(400);
        }

        $request->session()->put('locale', $locale);

        // Update user preference if logged in
        if (auth()->check()) {
            auth()->user()->update(['language_preference' => $locale]);
        }

        return redirect()->back();
    }
}
