<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Co-managers: other owner-level logins for the current market
 * (committee secretary, accountant, a partner). They see and do everything the owner can.
 */
class ManagerController extends Controller
{
    public function index()
    {
        $market = auth()->user()->market;
        $managers = $market->managers()->orderBy('name')->get();

        return view('market-owner.managers.index', compact('market', 'managers'));
    }

    public function store(Request $request)
    {
        $market = auth()->user()->market;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => 'nullable|string|min:6|confirmed',
        ], ['phone.regex' => __('messages.auth.invalid_bd_phone')]);

        // An existing owner-level account (e.g. a partner who already runs another market) can be
        // added by email or phone; otherwise a new login is created.
        $existing = null;
        if (!empty($validated['email'])) {
            $existing = User::where('email', $validated['email'])->first();
        }
        $existing ??= User::whereIn('phone', User::phoneVariants($validated['phone']))->where('role', 'market_owner')->first();

        if ($existing && $existing->role !== 'market_owner') {
            return back()->withInput()->with('error', __('mymarkets.manager_email_taken'));
        }

        DB::transaction(function () use ($existing, $validated, $market) {
            if ($existing) {
                $market->members()->syncWithoutDetaching([$existing->id]);
                return;
            }

            if (empty($validated['password'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['password' => __('mymarkets.manager_password_required')]);
            }

            $user = User::create([
                'market_id' => $market->id,
                'name' => $validated['name'],
                'email' => ($validated['email'] ?? null) ?: User::placeholderEmail($validated['phone'], $market->id),
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'market_owner',
                'is_active' => true,
                'language_preference' => auth()->user()->getPreferredLocale(),
            ]);
            $user->assignRole('market_owner');
        });

        return redirect()->route('market-owner.managers.index')
            ->with('success', $existing ? __('mymarkets.manager_added_existing', ['name' => $existing->name]) : __('mymarkets.manager_created', ['name' => $validated['name']]));
    }

    public function destroy(User $manager)
    {
        $market = auth()->user()->market;

        if ($manager->id === auth()->id()) {
            return back()->with('error', __('mymarkets.cannot_remove_self'));
        }
        if ($manager->role !== 'market_owner' || !$manager->isMemberOf($market)) {
            abort(404);
        }

        DB::transaction(function () use ($manager, $market) {
            $market->members()->detach($manager->id);

            $remaining = $manager->markets()->first();
            if ($remaining) {
                if ($manager->market_id === $market->id) {
                    $manager->forceFill(['market_id' => $remaining->id])->save();
                }
            } else {
                // No market left: keep the account but lock it.
                $manager->forceFill(['is_active' => false])->save();
            }
        });

        return redirect()->route('market-owner.managers.index')->with('success', __('mymarkets.manager_removed', ['name' => $manager->name]));
    }
}
