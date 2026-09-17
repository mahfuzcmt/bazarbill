<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    public function index(Request $request)
    {
        $query = Complaint::with(['shop', 'submitter', 'assignee']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('shop')) {
            $query->where('shop_id', $request->shop);
        }

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $shops = Shop::orderBy('shop_number')->get();
        $openCount = Complaint::where('status', 'open')->count();
        $inProgressCount = Complaint::where('status', 'in_progress')->count();
        $resolvedCount = Complaint::whereIn('status', ['resolved', 'closed'])->count();

        $staff = User::where('market_id', auth()->user()->market_id)
            ->whereIn('role', ['market_owner', 'collector'])
            ->where('is_active', true)
            ->get();

        return view('market-owner.complaints.index', compact(
            'complaints', 'staff', 'shops', 'openCount', 'inProgressCount', 'resolvedCount'
        ));
    }

    public function show(Complaint $complaint)
    {
        $complaint->load(['shop.shopOwner', 'submitter', 'assignee']);

        $staff = User::where('market_id', auth()->user()->market_id)
            ->whereIn('role', ['market_owner', 'collector'])
            ->where('is_active', true)
            ->get();

        return view('market-owner.complaints.show', compact('complaint', 'staff'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $validated['priority'] = $validated['priority'] ?? $complaint->priority;

        if (in_array($validated['status'], ['resolved', 'closed'], true) && !$complaint->resolved_at) {
            $validated['resolved_at'] = now();
        } elseif (in_array($validated['status'], ['open', 'in_progress'], true)) {
            $validated['resolved_at'] = null;
        }

        $complaint->update($validated);

        return back()->with('success', __('complaints.updated'));
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'assigned_to' => ['required', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
        ]);

        $complaint->assignTo($validated['assigned_to']);

        return back()->with('success', __('Complaint assigned successfully'));
    }

    public function resolve(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'resolution_notes' => 'required|string|max:1000',
        ]);

        $complaint->resolve($validated['resolution_notes']);

        return back()->with('success', __('complaints.resolved'));
    }
}
