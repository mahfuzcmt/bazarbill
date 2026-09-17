<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('shop-owner.dashboard');
        }

        $complaints = Complaint::where('shop_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withQueryString();

        return view('shop-owner.complaints.index', compact('complaints'));
    }

    public function create()
    {
        return view('shop-owner.complaints.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'priority' => 'required|in:low,medium,high',
        ]);

        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('shop-owner.dashboard');
        }

        Complaint::create([
            'market_id' => $user->market_id,
            'shop_id' => $shop->id,
            'submitted_by' => $user->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('shop-owner.complaints.index')
            ->with('success', __('complaints.created'));
    }

    public function show(Complaint $complaint)
    {
        $user = auth()->user();

        // Ensure this complaint belongs to the shop owner's shop
        if (!$user->shop || $complaint->shop_id !== $user->shop->id) {
            abort(403);
        }

        $complaint->load(['assignee', 'market']);

        return view('shop-owner.complaints.show', compact('complaint'));
    }

    public function feedback(Request $request, Complaint $complaint)
    {
        $user = auth()->user();

        // Ensure this complaint belongs to the shop owner's shop
        if (!$user->shop || $complaint->shop_id !== $user->shop->id) {
            abort(403);
        }

        // Only allow feedback on resolved complaints
        if ($complaint->status !== 'resolved') {
            return back()->with('error', __('complaints.cannot_give_feedback'));
        }

        $validated = $request->validate([
            'satisfaction' => 'required|in:very_satisfied,satisfied,neutral,unsatisfied,very_unsatisfied',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $complaint->update([
            'satisfaction_rating' => $validated['satisfaction'],
            'feedback' => $validated['feedback'],
            'feedback_given' => true,
            'status' => 'closed',
        ]);

        return back()->with('success', __('complaints.feedback_submitted'));
    }
}
