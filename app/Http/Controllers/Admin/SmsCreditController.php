<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InsufficientSmsCreditsException;
use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\SmsCreditTransaction;
use App\Services\SmsCreditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SmsCreditController extends Controller
{
    public function __construct(protected SmsCreditService $credits) {}

    /**
     * Balance overview for every market.
     */
    public function index(Request $request)
    {
        $monthStart = now()->startOfMonth();

        $markets = Market::query()
            ->withSum(['smsLogs as credits_used_this_month' => fn ($q) => $q
                ->where('status', 'sent')
                ->where('created_at', '>=', $monthStart)], 'credits_used')
            ->withCount(['smsLogs as sms_sent_this_month' => fn ($q) => $q
                ->where('status', 'sent')
                ->where('created_at', '>=', $monthStart)])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q) use ($request) {
                $term = '%' . $request->search . '%';
                $q->where('name', 'like', $term)->orWhere('name_bn', 'like', $term);
            }))
            ->when($request->filled('gateway'), fn ($q) => $request->gateway === 'own'
                ? $q->whereNotNull('sms_api_key')->where('sms_api_key', '!=', '')
                : $q->where(fn ($q) => $q->whereNull('sms_api_key')->orWhere('sms_api_key', '')))
            ->when($request->boolean('low_only'), fn ($q) => $q
                ->where('sms_credits', '<=', (int) config('services.sms.low_credit_threshold', 20)))
            ->orderBy('sms_credits')
            ->paginate(20)
            ->withQueryString();

        $totals = [
            'credits_outstanding' => Market::sum('sms_credits'),
            'used_this_month' => (int) \App\Models\SmsLog::withoutGlobalScopes()
                ->where('status', 'sent')
                ->where('created_at', '>=', $monthStart)
                ->sum('credits_used'),
            'low_markets' => Market::where(fn ($q) => $q->whereNull('sms_api_key')->orWhere('sms_api_key', ''))
                ->where('sms_credits', '<=', (int) config('services.sms.low_credit_threshold', 20))
                ->count(),
        ];

        return view('admin.sms-credits.index', compact('markets', 'totals'));
    }

    /**
     * Ledger and top-up form for one market.
     */
    public function show(Request $request, Market $market)
    {
        $transactions = $market->smsCreditTransactions()
            ->with('creator')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'allocated' => (int) $market->smsCreditTransactions()->whereIn('type', SmsCreditTransaction::MANUAL_TYPES)->where('amount', '>', 0)->sum('amount'),
            'deducted' => (int) abs($market->smsCreditTransactions()->whereIn('type', SmsCreditTransaction::MANUAL_TYPES)->where('amount', '<', 0)->sum('amount')),
            'used' => (int) abs($market->smsCreditTransactions()->where('type', SmsCreditTransaction::TYPE_USAGE)->sum('amount'))
                - (int) $market->smsCreditTransactions()->where('type', SmsCreditTransaction::TYPE_REFUND)->sum('amount'),
            'sent_this_month' => $market->smsLogs()->withoutGlobalScopes()
                ->where('status', 'sent')->where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.sms-credits.show', compact('market', 'transactions', 'summary'));
    }

    /**
     * Record a subscription allocation, offline recharge, or manual adjustment.
     */
    public function store(Request $request, Market $market)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(SmsCreditTransaction::MANUAL_TYPES)],
            'operation' => ['required', Rule::in(['add', 'deduct'])],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $meta = [
            'reference' => $validated['reference'] ?? null,
            'note' => $validated['note'] ?? null,
            'created_by' => auth()->id(),
        ];

        try {
            if ($validated['operation'] === 'add') {
                $this->credits->credit($market, (int) $validated['amount'], $validated['type'], $meta);
            } else {
                $this->credits->debit($market, (int) $validated['amount'], $validated['type'], $meta);
            }
        } catch (InsufficientSmsCreditsException $e) {
            return back()->withInput()->with('error', __('Cannot deduct :required credits; the market only has :available.', [
                'required' => $e->required,
                'available' => $e->available,
            ]));
        }

        return redirect()->route('admin.markets.sms-credits', $market)
            ->with('success', $validated['operation'] === 'add'
                ? __(':amount SMS credits added. New balance: :balance', ['amount' => $validated['amount'], 'balance' => $market->sms_credits])
                : __(':amount SMS credits deducted. New balance: :balance', ['amount' => $validated['amount'], 'balance' => $market->sms_credits]));
    }
}
