<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Shop;
use App\Services\InvoiceGenerationService;
use App\Services\SmsService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['shop.shopOwner']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $month = $request->input('month', $request->input('billing_month'));
        if (filled($month)) {
            $query->where('billing_month', $month);
        }

        $shopId = $request->input('shop', $request->input('shop_id'));
        if (filled($shopId)) {
            $query->where('shop_id', $shopId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('shop', function ($q) use ($search) {
                      $q->where('shop_number', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Every shop can have invoices, including ones now vacant or suspended.
        $shops = Shop::orderBy('shop_number')->get();

        return view('market-owner.invoices.index', compact('invoices', 'shops'));
    }

    public function create()
    {
        $shops = Shop::with('shopOwner')
            ->where('status', 'active')
            ->orderBy('shop_number')
            ->get();

        $currentMonth = now()->format('Y-m');

        return view('market-owner.invoices.create', compact('shops', 'currentMonth'));
    }

    public function store(Request $request)
    {
        // The create form offers single or bulk generation from one page.
        if ($request->input('generation_type') === 'bulk') {
            return $this->generateBulk($request);
        }

        $validated = $request->validate([
            'shop_id' => ['required', Rule::exists('shops', 'id')->where('market_id', auth()->user()->market_id)],
            'billing_month' => 'required|date_format:Y-m',
            'rent_amount' => 'nullable|numeric|min:0',
            'previous_due' => 'nullable|numeric|min:0',
            'include_previous_due' => 'boolean',
            'discount' => 'nullable|numeric|min:0',
            'late_fee' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'notes' => 'nullable|string|max:500',
            'send_sms' => 'boolean',
        ]);

        $shop = Shop::findOrFail($validated['shop_id']);

        // Check if invoice already exists
        $exists = Invoice::where('shop_id', $validated['shop_id'])
            ->where('billing_month', $validated['billing_month'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['billing_month' => __('invoices.already_exists')]);
        }

        // Blank rent falls back to the shop's configured rent.
        $rentAmount = $validated['rent_amount'] ?? $shop->rent_amount;

        $previousDue = $validated['previous_due'] ?? 0;
        if ($request->boolean('include_previous_due') && !isset($validated['previous_due'])) {
            $previousDue = $this->outstandingDueForShop($shop->id);
        }

        $discount = $validated['discount'] ?? 0;
        $lateFee = $validated['late_fee'] ?? 0;
        $totalAmount = $rentAmount + $previousDue + $lateFee - $discount;

        $invoice = Invoice::create([
            'market_id' => auth()->user()->market_id,
            'shop_id' => $validated['shop_id'],
            'billing_month' => $validated['billing_month'],
            'rent_amount' => $rentAmount,
            'previous_due' => $previousDue,
            'discount' => $discount,
            'late_fee' => $lateFee,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'due_amount' => $totalAmount,
            'status' => 'pending',
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'],
        ]);

        // Send SMS if requested
        $smsError = null;
        if ($request->boolean('send_sms') && $shop->shopOwner?->phone) {
            $smsService = new SmsService(auth()->user()->market);
            $sent = $smsService->sendInvoiceNotification([
                'phone' => $shop->shopOwner->phone,
                'shop_owner' => $shop->shopOwner->getLocalizedName(),
                'month' => $invoice->getBillingMonthFormattedBn(),
                'amount' => number_format($invoice->total_amount),
                'invoice_no' => $invoice->invoice_number,
            ]);
            if (!$sent) {
                $smsError = $smsService->lastFailedForCredits
                    ? __('settings.sms_insufficient_credits', ['count' => 1])
                    : __('settings.sms_send_failed') . ($smsService->lastError ? ' — ' . $smsService->lastError : '');
            }
        }

        $redirect = redirect()->route('market-owner.invoices.index')->with('success', __('invoices.created'));

        return $smsError ? $redirect->with('error', $smsError) : $redirect;
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['shop.shopOwner', 'payments.collector']);

        return view('market-owner.invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', __('Cannot edit paid invoice'));
        }

        return view('market-owner.invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', __('Cannot edit paid invoice'));
        }

        $validated = $request->validate([
            'rent_amount' => 'nullable|numeric|min:0',
            'previous_due' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'late_fee' => 'nullable|numeric|min:0',
            'due_date' => 'required|date',
            'status' => 'nullable|in:pending,partial,paid,overdue',
            'notes' => 'nullable|string|max:500',
        ]);

        $rentAmount = $validated['rent_amount'] ?? $invoice->rent_amount;
        $previousDue = $validated['previous_due'] ?? $invoice->previous_due;
        $discount = $validated['discount'] ?? $invoice->discount;
        $lateFee = $validated['late_fee'] ?? $invoice->late_fee;
        $totalAmount = $rentAmount + $previousDue + $lateFee - $discount;
        $dueAmount = max(0, $totalAmount - $invoice->paid_amount);

        // A chosen status must stay consistent with what has actually been paid.
        $status = $validated['status'] ?? $invoice->status;
        if ($dueAmount <= 0) {
            $status = 'paid';
        } elseif ($status === 'paid') {
            $status = $invoice->paid_amount > 0 ? 'partial' : 'pending';
        }

        $invoice->update([
            'rent_amount' => $rentAmount,
            'previous_due' => $previousDue,
            'discount' => $discount,
            'late_fee' => $lateFee,
            'total_amount' => $totalAmount,
            'due_amount' => $dueAmount,
            'status' => $status,
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('market-owner.invoices.index')
            ->with('success', __('invoices.updated'));
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with('error', __('Cannot delete invoice with payments'));
        }

        $invoice->delete();

        return redirect()->route('market-owner.invoices.index')
            ->with('success', __('invoices.deleted'));
    }

    public function generateBulk(Request $request)
    {
        $validated = $request->validate([
            'billing_month' => 'required|date_format:Y-m',
            'due_date' => 'required|date',
            'include_previous_due' => 'boolean',
            'send_sms' => 'boolean',
        ]);

        // Bulk generation carries forward outstanding dues unless explicitly disabled.
        $includePreviousDue = !$request->has('include_previous_due') || $request->boolean('include_previous_due');

        $market = auth()->user()->market;

        $result = app(InvoiceGenerationService::class)->generateForMarket(
            $market,
            $validated['billing_month'],
            \Carbon\Carbon::parse($validated['due_date']),
            $includePreviousDue,
            $request->boolean('send_sms'),
        );

        $redirect = redirect()->route('market-owner.invoices.index')
            ->with('success', __('invoices.bulk_created', ['count' => $result['created']]));

        if ($result['sms_failed'] > 0) {
            $redirect->with('error', $result['sms_blocked_for_credits']
                ? __('settings.sms_insufficient_credits', ['count' => $result['sms_failed']])
                : __('settings.sms_send_failed_count', ['count' => $result['sms_failed']])
                    . ($result['sms_last_error'] ? ' — ' . $result['sms_last_error'] : ''));
        }

        return $redirect;
    }

    public function sendReminder(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return back()->with('error', __('Invoice is already paid'));
        }

        $shop = $invoice->shop;
        if (!$shop->shopOwner?->phone) {
            return back()->with('error', __('Shop owner phone not available'));
        }

        $smsService = new SmsService(auth()->user()->market);
        $sent = $smsService->sendPaymentReminder([
            'phone' => $shop->shopOwner->phone,
            'shop_owner' => $shop->shopOwner->getLocalizedName(),
            'amount' => number_format($invoice->due_amount),
        ]);

        if (!$sent) {
            return back()->with('error', $smsService->lastFailedForCredits
                ? __('settings.sms_insufficient_credits', ['count' => 1])
                : __('settings.sms_send_failed') . ($smsService->lastError ? ' (' . $smsService->lastError . ')' : ''));
        }

        return back()->with('success', __('Reminder sent successfully'));
    }

    /**
     * Sum of unpaid balances on the shop's existing invoices.
     */
    private function outstandingDueForShop(int $shopId): float
    {
        return (float) Invoice::where('shop_id', $shopId)
            ->where('status', '!=', 'paid')
            ->sum('due_amount');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $invoice->load(['shop.shopOwner', 'market']);

        $pdf = PdfService::fromView('market-owner.invoices.pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
