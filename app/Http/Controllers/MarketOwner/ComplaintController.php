<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use Illuminate\Http\Request;

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

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15);

        $staff = User::where('market_id', auth()->user()->market_id)
            ->whereIn('role', ['market_owner', 'collector'])
            ->where('is_active', true)
            ->get();

        return view('market-owner.complaints.index', compact('complaints', 'staff'));
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
            'priority' => 'required|in:low,medium,high',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        if ($validated['status'] === 'resolved' && $complaint->status !== 'resolved') {
            $validated['resolved_at'] = now();
        }

        $complaint->update($validated);

        return back()->with('success', __('complaints.updated'));
    }

    public function assign(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
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
