<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('market-owner.notices.index', compact('notices'));
    }

    public function create()
    {
        return view('market-owner.notices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_bn' => 'nullable|string|max:255',
            'content' => 'required|string',
            'content_bn' => 'nullable|string',
            'target_role' => 'required|in:all,shop_owner,collector',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date|after:today',
        ]);

        Notice::create([
            'market_id' => auth()->user()->market_id,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'title_bn' => $validated['title_bn'],
            'content' => $validated['content'],
            'content_bn' => $validated['content_bn'],
            'target_role' => $validated['target_role'],
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $validated['expires_at'],
        ]);

        return redirect()->route('market-owner.notices.index')
            ->with('success', __('notices.created'));
    }

    public function show(Notice $notice)
    {
        return view('market-owner.notices.show', compact('notice'));
    }

    public function edit(Notice $notice)
    {
        return view('market-owner.notices.edit', compact('notice'));
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'title_bn' => 'nullable|string|max:255',
            'content' => 'required|string',
            'content_bn' => 'nullable|string',
            'target_role' => 'required|in:all,shop_owner,collector',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date',
        ]);

        $notice->update([
            'title' => $validated['title'],
            'title_bn' => $validated['title_bn'],
            'content' => $validated['content'],
            'content_bn' => $validated['content_bn'],
            'target_role' => $validated['target_role'],
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $validated['expires_at'],
        ]);

        return redirect()->route('market-owner.notices.index')
            ->with('success', __('notices.updated'));
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('market-owner.notices.index')
            ->with('success', __('notices.deleted'));
    }
}
