<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('market');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('market_id')) {
            $query->where('market_id', $request->market_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $markets = Market::all();

        return view('admin.users.index', compact('users', 'markets'));
    }

    public function create()
    {
        $markets = Market::where('status', 'active')->get();
        $shopsByMarket = $this->shopsByMarket();

        return view('admin.users.create', compact('markets', 'shopsByMarket'));
    }

    /**
     * Unowned shops per market (plus the given user's own), for the shop-owner picker.
     */
    protected function shopsByMarket(?User $user = null): array
    {
        return \App\Models\Shop::withoutGlobalScopes()
            ->where(function ($q) use ($user) {
                $q->whereNull('shop_owner_id');
                if ($user) {
                    $q->orWhere('shop_owner_id', $user->id);
                }
            })
            ->orderBy('shop_number')
            ->get(['id', 'market_id', 'shop_number', 'floor', 'rent_amount', 'shop_owner_id'])
            ->groupBy('market_id')
            ->map(fn ($shops) => $shops->map(fn ($s) => [
                'id' => $s->id,
                'label' => $s->shop_number . ($s->floor ? ' · ' . $s->floor : '') . ' · ৳' . number_format($s->rent_amount),
                'mine' => $user ? $s->shop_owner_id === $user->id : false,
            ])->values())
            ->toArray();
    }

    protected function userRules(?User $user = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:20', 'required_without:email'],
            'password' => $user ? ['nullable', 'confirmed', Password::defaults()] : ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:super_admin,market_owner,collector,shop_owner',
            'market_id' => ['nullable', 'exists:markets,id', 'required_unless:role,super_admin'],
            'is_active' => 'boolean',
            'shop_ids' => 'nullable|array',
            'shop_ids.*' => 'integer|exists:shops,id',
        ];
    }

    protected function userMessages(): array
    {
        return [
            'phone.required_without' => __('Enter an email or a mobile number so the user can log in.'),
            'market_id.required_unless' => __('Choose the market this user belongs to.'),
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->userRules(), $this->userMessages());

        $shopIds = $validated['shop_ids'] ?? [];
        unset($validated['shop_ids']);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);
        if ($validated['role'] === 'super_admin') {
            $validated['market_id'] = null;
        }
        if (empty($validated['email'])) {
            $validated['email'] = User::placeholderEmail($validated['phone'], (int) $validated['market_id']);
        }

        $user = User::create($validated);
        $user->assignRole($validated['role']);

        if ($user->role === 'shop_owner' && $user->market_id) {
            \App\Models\Shop::syncOwner($user, $shopIds, $user->market_id);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User created successfully.') . ' ' . __('Login: :id', ['id' => $user->loginIdentifier()]));
    }

    public function show(User $user)
    {
        $user->load('market');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $markets = Market::where('status', 'active')->get();
        $shopsByMarket = $this->shopsByMarket($user);
        $ownedShopIds = $user->ownedShops()->pluck('id')->all();

        return view('admin.users.edit', compact('user', 'markets', 'shopsByMarket', 'ownedShopIds'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate($this->userRules($user), $this->userMessages());

        $shopIds = $validated['shop_ids'] ?? [];
        unset($validated['shop_ids']);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);
        if ($validated['role'] === 'super_admin') {
            $validated['market_id'] = null;
        }
        if (empty($validated['email'])) {
            $validated['email'] = User::placeholderEmail($validated['phone'], (int) $validated['market_id']);
        }

        $user->update($validated);
        $user->syncRoles([$validated['role']]);

        if ($user->role === 'shop_owner' && $user->market_id) {
            \App\Models\Shop::syncOwner($user, $shopIds, $user->market_id);
        } else {
            $user->ownedShops()->update(['shop_owner_id' => null]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', __('You cannot delete yourself.'));
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', __('User deleted successfully.'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return back()->with('success', __('User status updated.'));
    }
}
