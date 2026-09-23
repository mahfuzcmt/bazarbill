<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index()
    {
        $market = auth()->user()->market->load('plan');
        $shopCount = $market->shops()->count();

        return view('market-owner.settings.index', compact('market', 'shopCount'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'address_bn' => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        $market = auth()->user()->market;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($market->logo) {
                Storage::disk('public')->delete($market->logo);
            }
            $validated['logo'] = $request->file('logo')->store('markets/logos', 'public');
        }

        $market->update($validated);

        return back()->with('success', __('settings.market_updated'));
    }

    public function updateSms(Request $request)
    {
        $validated = $request->validate([
            'sms_api_key' => 'nullable|string|max:255',
            'sms_sender_id' => 'nullable|string|max:20',
        ]);

        $market = auth()->user()->market;

        $market->update([
            'sms_api_key' => $validated['sms_api_key'],
            'sms_sender_id' => $validated['sms_sender_id'],
        ]);

        return back()->with('success', __('settings.sms_updated'));
    }

    public function updateInvoice(Request $request)
    {
        $validated = $request->validate([
            'settings.invoice_prefix' => 'nullable|string|max:10',
            'settings.due_days' => 'nullable|integer|min:1|max:30',
            'settings.late_fee_percent' => 'nullable|numeric|min:0|max:100',
            'settings.grace_days' => 'nullable|integer|min:0|max:15',
            'settings.auto_generate' => 'nullable|boolean',
            'settings.sms_on_invoice' => 'nullable|boolean',
            'settings.auto_reminder' => 'nullable|boolean',
            'settings.reminder_days_before' => 'nullable|integer|min:0|max:15',
            'settings.reminder_repeat_days' => 'nullable|integer|min:1|max:30',
        ]);

        $market = auth()->user()->market;
        $currentSettings = $market->settings ?? [];

        $newSettings = array_merge($currentSettings, [
            'invoice_prefix' => $validated['settings']['invoice_prefix'] ?? 'INV',
            'due_days' => (int) ($validated['settings']['due_days'] ?? 7),
            'late_fee_percent' => (float) ($validated['settings']['late_fee_percent'] ?? 0),
            'grace_days' => (int) ($validated['settings']['grace_days'] ?? 3),
            'auto_generate' => $request->has('settings.auto_generate'),
            'sms_on_invoice' => $request->has('settings.sms_on_invoice'),
            'auto_reminder' => $request->has('settings.auto_reminder'),
            'reminder_days_before' => (int) ($validated['settings']['reminder_days_before'] ?? 3),
            'reminder_repeat_days' => (int) ($validated['settings']['reminder_repeat_days'] ?? 7),
        ]);

        $market->update(['settings' => $newSettings]);

        return back()->with('success', __('settings.invoice_updated'));
    }

    public function updatePermissions(Request $request)
    {
        $validated = $request->validate([
            'settings.staff_permissions.can_create_shop' => 'nullable|boolean',
            'settings.staff_permissions.can_update_rent' => 'nullable|boolean',
            'settings.staff_permissions.can_send_sms' => 'nullable|boolean',
            'settings.staff_permissions.can_view_all_shops' => 'nullable|boolean',
            'settings.staff_permissions.can_manage_complaints' => 'nullable|boolean',
        ]);

        $market = auth()->user()->market;
        $currentSettings = $market->settings ?? [];

        $staffPermissions = [
            'can_create_shop' => $request->has('settings.staff_permissions.can_create_shop'),
            'can_update_rent' => $request->has('settings.staff_permissions.can_update_rent'),
            'can_send_sms' => $request->has('settings.staff_permissions.can_send_sms'),
            'can_view_all_shops' => $request->has('settings.staff_permissions.can_view_all_shops'),
            'can_manage_complaints' => $request->has('settings.staff_permissions.can_manage_complaints'),
        ];

        $currentSettings['staff_permissions'] = $staffPermissions;
        $market->update(['settings' => $currentSettings]);

        return back()->with('success', __('settings.permissions_updated'));
    }

    public function testSms(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $market = auth()->user()->market;
        $smsService = new SmsService($market);

        if (!$smsService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('settings.sms_not_configured'),
            ]);
        }

        try {
            $result = $smsService->send(
                $request->phone,
                __('settings.test_sms_message', ['market' => $market->name])
            );

            return response()->json([
                'success' => $result,
                'message' => $result
                    ? __('settings.test_sms_sent')
                    : __('settings.test_sms_failed') . ($smsService->lastError ? ' (' . $smsService->lastError . ')' : ''),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function smsCredits(Request $request)
    {
        $market = auth()->user()->market;

        $transactions = $market->smsCreditTransactions()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $usedThisMonth = (int) $market->smsLogs()
            ->where('status', 'sent')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('credits_used');

        return view('market-owner.settings.sms-credits', compact('market', 'transactions', 'usedThisMonth'));
    }

    public function export()
    {
        $market = auth()->user()->market;

        // Generate a comprehensive export of all market data
        $data = [
            'market' => $market->toArray(),
            'shops' => $market->shops()->with('shopOwner')->get()->toArray(),
            'invoices' => $market->invoices()->with('shop')->get()->toArray(),
            'payments' => $market->payments()->with(['shop', 'collector'])->get()->toArray(),
            'staff' => $market->users()->where('role', 'collector')->get()->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];

        $filename = 'market-export-' . $market->slug . '-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function destroy()
    {
        // This is a dangerous operation - requires additional confirmation
        // For now, just redirect with a message
        return redirect()->route('market-owner.settings.index')
            ->with('error', __('settings.delete_contact_support'));
    }
}
