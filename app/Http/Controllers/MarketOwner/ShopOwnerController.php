<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Shop owner (tenant) logins managed by the market owner.
 * A shop owner may own several shops; shops.shop_owner_id links them.
 */
class ShopOwnerController extends Controller
{
    public function index(Request $request)
    {
        $marketId = auth()->user()->market_id;

        $owners = User::where('market_id', $marketId)
            ->where('role', 'shop_owner')
            ->withCount(['ownedShops'])
            ->with(['ownedShops' => fn ($q) => $q->orderBy('shop_number')])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('name_bn', 'like', $term)
                      ->orWhere('phone', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhereHas('ownedShops', fn ($s) => $s->where('shop_number', 'like', $term));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('is_active', $request->status === 'active'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $unassignedShops = Shop::where('market_id', $marketId)->whereNull('shop_owner_id')->count();

        return view('market-owner.shop-owners.index', compact('owners', 'unassignedShops'));
    }

    public function create()
    {
        $shops = $this->assignableShops();

        return view('market-owner.shop-owners.create', compact('shops'));
    }

    public function store(Request $request)
    {
        $marketId = auth()->user()->market_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+?88)?01[3-9]\d{8}$/',
                Rule::unique('users', 'phone')->where('market_id', $marketId)],
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'language_preference' => 'nullable|in:bn,en',
            'shop_ids' => 'nullable|array',
            'shop_ids.*' => [Rule::exists('shops', 'id')->where('market_id', $marketId)],
        ], [
            'phone.regex' => __('messages.auth.invalid_bd_phone'),
            'phone.unique' => __('shop_owners.phone_taken'),
        ]);

        $owner = DB::transaction(function () use ($validated, $marketId) {
            $owner = User::create([
                'market_id' => $marketId,
                'name' => $validated['name'],
                'name_bn' => $validated['name_bn'] ?? null,
                'email' => ($validated['email'] ?? null) ?: $this->placeholderEmail($validated['phone']),
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'shop_owner',
                'is_active' => true,
                'language_preference' => $validated['language_preference'] ?? 'bn',
            ]);
            $owner->assignRole('shop_owner');

            $this->syncShops($owner, $validated['shop_ids'] ?? [], $marketId);

            return $owner;
        });

        return redirect()->route('market-owner.shop-owners.index')
            ->with('success', __('shop_owners.created', ['name' => $owner->name]));
    }

    public function edit(User $shopOwner)
    {
        $this->ensureOwn($shopOwner);

        $shops = $this->assignableShops($shopOwner);
        $assignedShopIds = $shopOwner->ownedShops()->pluck('id')->all();

        return view('market-owner.shop-owners.edit', compact('shopOwner', 'shops', 'assignedShopIds'));
    }

    public function update(Request $request, User $shopOwner)
    {
        $this->ensureOwn($shopOwner);
        $marketId = auth()->user()->market_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+?88)?01[3-9]\d{8}$/',
                Rule::unique('users', 'phone')->where('market_id', $marketId)->ignore($shopOwner->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($shopOwner->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'language_preference' => 'nullable|in:bn,en',
            'shop_ids' => 'nullable|array',
            'shop_ids.*' => [Rule::exists('shops', 'id')->where('market_id', $marketId)],
            'is_active' => 'nullable|boolean',
        ], [
            'phone.regex' => __('messages.auth.invalid_bd_phone'),
            'phone.unique' => __('shop_owners.phone_taken'),
        ]);

        DB::transaction(function () use ($validated, $shopOwner, $marketId, $request) {
            $data = [
                'name' => $validated['name'],
                'name_bn' => $validated['name_bn'] ?? null,
                'phone' => $validated['phone'],
                'language_preference' => $validated['language_preference'] ?? $shopOwner->language_preference,
                'is_active' => $request->has('is_active'),
            ];

            if (!empty($validated['email'])) {
                $data['email'] = $validated['email'];
            } elseif (str_ends_with($shopOwner->email, '@bazarbill.local')) {
                $data['email'] = $this->placeholderEmail($validated['phone']);
            }

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $shopOwner->update($data);

            $this->syncShops($shopOwner, $validated['shop_ids'] ?? [], $marketId);
        });

        return redirect()->route('market-owner.shop-owners.index')
            ->with('success', __('shop_owners.updated', ['name' => $shopOwner->name]));
    }

    public function toggleStatus(User $shopOwner)
    {
        $this->ensureOwn($shopOwner);

        $shopOwner->update(['is_active' => !$shopOwner->is_active]);

        return back()->with('success', $shopOwner->is_active
            ? __('shop_owners.activated', ['name' => $shopOwner->name])
            : __('shop_owners.deactivated', ['name' => $shopOwner->name]));
    }

    public function destroy(User $shopOwner)
    {
        $this->ensureOwn($shopOwner);

        if ($shopOwner->submittedComplaints()->exists()) {
            // Keep history intact: deactivate instead of deleting.
            $shopOwner->update(['is_active' => false]);
            $shopOwner->ownedShops()->update(['shop_owner_id' => null]);

            return redirect()->route('market-owner.shop-owners.index')
                ->with('success', __('shop_owners.deactivated_instead', ['name' => $shopOwner->name]));
        }

        DB::transaction(function () use ($shopOwner) {
            $shopOwner->ownedShops()->update(['shop_owner_id' => null]);
            $shopOwner->delete();
        });

        return redirect()->route('market-owner.shop-owners.index')
            ->with('success', __('shop_owners.deleted'));
    }

    /**
     * Shops that can be given to a shop owner: unowned ones, plus (when editing) their own.
     */
    protected function assignableShops(?User $owner = null)
    {
        return Shop::where('market_id', auth()->user()->market_id)
            ->where(function ($q) use ($owner) {
                $q->whereNull('shop_owner_id');
                if ($owner) {
                    $q->orWhere('shop_owner_id', $owner->id);
                }
            })
            ->orderBy('shop_number')
            ->get();
    }

    protected function syncShops(User $owner, array $shopIds, int $marketId): void
    {
        Shop::where('market_id', $marketId)
            ->where('shop_owner_id', $owner->id)
            ->whereNotIn('id', $shopIds ?: [0])
            ->update(['shop_owner_id' => null]);

        if ($shopIds) {
            Shop::where('market_id', $marketId)
                ->whereIn('id', $shopIds)
                ->where(fn ($q) => $q->whereNull('shop_owner_id')->orWhere('shop_owner_id', $owner->id))
                ->update(['shop_owner_id' => $owner->id]);
        }
    }

    protected function placeholderEmail(string $phone): string
    {
        return 'owner.' . preg_replace('/\D/', '', $phone) . '@bazarbill.local';
    }

    protected function ensureOwn(User $user): void
    {
        if ($user->market_id !== auth()->user()->market_id || $user->role !== 'shop_owner') {
            abort(404);
        }
    }
}
