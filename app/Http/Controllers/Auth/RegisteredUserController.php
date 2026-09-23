<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Self-service signup: creates the market, its owner account and a free trial.
 */
class RegisteredUserController extends Controller
{
    public function __construct(protected SubscriptionService $subscriptions) {}

    public function create(): View
    {
        $plan = Plan::default();

        return view('auth.register', compact('plan'));
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'market_name' => ['required', 'string', 'max:255'],
            'market_name_bn' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => __('messages.auth.invalid_bd_phone'),
        ]);

        $plan = Plan::default();

        if (!$plan) {
            throw ValidationException::withMessages([
                'market_name' => __('messages.auth.signup_unavailable'),
            ]);
        }

        $user = DB::transaction(function () use ($request, $plan) {
            $market = Market::create([
                'name' => $request->market_name,
                'name_bn' => $request->market_name_bn,
                'slug' => Str::slug($request->market_name) . '-' . Str::random(5),
                'phone' => $request->phone,
                'email' => $request->email,
                'status' => 'active',
                'settings' => [
                    'invoice_prefix' => 'INV',
                    'due_days' => 7,
                    'grace_days' => 3,
                    'late_fee_percent' => 0,
                    'auto_generate' => false,
                    'sms_on_invoice' => true,
                    'auto_reminder' => false,
                ],
            ]);

            $user = User::create([
                'market_id' => $market->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'market_owner',
                'is_active' => true,
                'language_preference' => app()->getLocale(),
            ]);
            $user->assignRole('market_owner');

            $this->subscriptions->startTrial($market, $plan, $user->id);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('success', __('messages.auth.trial_started', ['days' => $plan->trial_days]));
    }
}
