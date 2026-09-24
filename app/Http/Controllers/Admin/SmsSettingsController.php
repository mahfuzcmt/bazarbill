<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\Setting;
use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsSettingsController extends Controller
{
    public function index()
    {
        $apiKey = Setting::smsApiKey();

        $settings = [
            'api_key' => $apiKey,
            'sender_id' => Setting::smsSenderId(),
            'low_credit_threshold' => Setting::smsLowCreditThreshold(),
            'source' => Setting::get('sms.api_key') ? 'database' : (config('services.sms.api_key') ? 'env' : 'none'),
        ];

        $balance = $apiKey ? SmsService::platformBalance() : null;

        $stats = [
            'sent_today' => SmsLog::withoutGlobalScopes()->where('status', 'sent')->whereDate('created_at', today())->count(),
            'failed_today' => SmsLog::withoutGlobalScopes()->where('status', 'failed')->whereDate('created_at', today())->count(),
            'sent_month' => SmsLog::withoutGlobalScopes()->where('status', 'sent')->where('created_at', '>=', now()->startOfMonth())->count(),
            'platform_markets' => Market::where(fn ($q) => $q->whereNull('sms_api_key')->orWhere('sms_api_key', ''))->count(),
        ];

        $recentLogs = SmsLog::withoutGlobalScopes()->with('market')->latest('id')->limit(15)->get();

        return view('admin.sms-settings.index', compact('settings', 'balance', 'stats', 'recentLogs'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'api_key' => ['nullable', 'string', 'max:255'],
            'sender_id' => ['required', 'string', 'max:20'],
            'low_credit_threshold' => ['required', 'integer', 'min:0', 'max:10000'],
        ]);

        // An empty submitted key keeps the stored one (the field is masked in the form).
        $values = [
            'sms.sender_id' => trim($validated['sender_id']),
            'sms.low_credit_threshold' => (int) $validated['low_credit_threshold'],
        ];
        if ($request->filled('api_key')) {
            $values['sms.api_key'] = trim($validated['api_key']);
        }
        if ($request->boolean('clear_api_key')) {
            $values['sms.api_key'] = null;
        }

        Setting::setMany($values);

        return redirect()->route('admin.sms-settings.index')->with('success', __('SMS gateway settings saved.'));
    }

    public function test(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^(\+?88)?01[3-9]\d{8}$/'],
        ]);

        if (!Setting::smsApiKey()) {
            return back()->with('error', __('No platform API key is set. Save one first.'));
        }

        $result = SmsService::platformTest($validated['phone'], 'DueTap test SMS ' . now()->format('H:i'));

        return back()->with($result['ok'] ? 'success' : 'error', $result['ok']
            ? __('Test SMS accepted by the gateway for :phone.', ['phone' => $validated['phone']])
            : __('Gateway rejected the test SMS: :error', ['error' => $result['error']]));
    }

    public function logs(Request $request)
    {
        $logs = SmsLog::withoutGlobalScopes()
            ->with('market')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->gateway))
            ->when($request->filled('market_id'), fn ($q) => $q->where('market_id', $request->market_id))
            ->when($request->filled('phone'), fn ($q) => $q->where('recipient_phone', 'like', '%' . preg_replace('/\D/', '', $request->phone) . '%'))
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        $markets = Market::orderBy('name')->get(['id', 'name']);

        return view('admin.sms-settings.logs', compact('logs', 'markets'));
    }
}
