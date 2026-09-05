<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $staff = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'collector')
            ->withCount('assignedShops')
            ->orderBy('name')
            ->paginate(15);

        return view('market-owner.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('market-owner.staff.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'market_id' => auth()->user()->market_id,
            'name' => $validated['name'],
            'name_bn' => $validated['name_bn'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'collector',
            'is_active' => true,
            'language_preference' => 'bn',
        ]);

        $user->assignRole('collector');

        return redirect()->route('market-owner.staff.index')
            ->with('success', __('staff.created'));
    }

    public function show(User $staff)
    {
        // Load assigned shops with their invoices
        $assignedShops = Shop::where('collector_id', $staff->id)
            ->with(['shopOwner', 'invoices' => function ($q) {
                $q->whereIn('status', ['pending', 'partial', 'overdue']);
            }])
            ->get();

        // Total collections by this staff
        $totalCollections = Payment::where('collected_by', $staff->id)->sum('amount');

        // This month's collections
        $thisMonthCollections = Payment::where('collected_by', $staff->id)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        // Total pending dues for assigned shops
        $assignedShopIds = $assignedShops->pluck('id');
        $pendingDues = Invoice::whereIn('shop_id', $assignedShopIds)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->sum('due_amount');

        // Recent payments
        $recentPayments = Payment::where('collected_by', $staff->id)
            ->with('shop')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();

        return view('market-owner.staff.show', compact(
            'staff',
            'assignedShops',
            'totalCollections',
            'thisMonthCollections',
            'pendingDues',
            'recentPayments'
        ));
    }

    public function edit(User $staff)
    {
        $marketId = auth()->user()->market_id;

        // All shops in the market for assignment
        $shops = Shop::where('market_id', $marketId)
            ->with('shopOwner')
            ->orderBy('shop_number')
            ->get();

        // Currently assigned shop IDs
        $assignedShopIds = Shop::where('collector_id', $staff->id)
            ->pluck('id')
            ->toArray();

        return view('market-owner.staff.edit', compact('staff', 'shops', 'assignedShopIds'));
    }

    public function update(Request $request, User $staff)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'shop_ids' => 'nullable|array',
            'shop_ids.*' => 'exists:shops,id',
            'is_active' => 'nullable|boolean',
        ]);

        $staff->update([
            'name' => $validated['name'],
            'name_bn' => $validated['name_bn'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        if (!empty($validated['password'])) {
            $staff->update(['password' => Hash::make($validated['password'])]);
        }

        // Update shop assignments
        $marketId = auth()->user()->market_id;

        // Unassign all current shops from this collector
        Shop::where('market_id', $marketId)
            ->where('collector_id', $staff->id)
            ->update(['collector_id' => null]);

        // Assign new shops
        if (!empty($validated['shop_ids'])) {
            Shop::where('market_id', $marketId)
                ->whereIn('id', $validated['shop_ids'])
                ->update(['collector_id' => $staff->id]);
        }

        return redirect()->route('market-owner.staff.show', $staff)
            ->with('success', __('staff.updated'));
    }

    public function destroy(User $staff)
    {
        // Unassign from all shops
        $staff->assignedShops()->update(['collector_id' => null]);

        $staff->delete();

        return redirect()->route('market-owner.staff.index')
            ->with('success', __('staff.deleted'));
    }

    public function toggleStatus(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $message = $user->is_active ? __('staff.activated') : __('staff.deactivated');

        return back()->with('success', $message);
    }
}
