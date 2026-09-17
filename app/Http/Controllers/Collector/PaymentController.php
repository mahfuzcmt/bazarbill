<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use App\Services\SmsService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $payments = Payment::where('collected_by', $user->id)
            ->with(['shop', 'invoice'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        $base = Payment::where('collected_by', $user->id);
        $todayTotal = (clone $base)->whereDate('payment_date', today())->sum('amount');
        $weekTotal = (clone $base)->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $monthTotal = (clone $base)->whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');

        return view('collector.payments.index', compact('payments', 'todayTotal', 'weekTotal', 'monthTotal'));
    }

    public function create(Request $request, ?Invoice $invoice = null)
    {
        $user = auth()->user();

        // Get assigned shops
        $assignedShopIds = Shop::where('collector_id', $user->id)->pluck('id');

        // Get pending invoices for assigned shops
        $invoices = Invoice::whereIn('shop_id', $assignedShopIds)
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->with('shop.shopOwner')
            ->orderBy('due_date')
            ->get();

        $selectedInvoice = $invoice && $invoice->shop->collector_id === $user->id
            ? $invoice->load('shop.shopOwner')
            : null;

        return view('collector.payments.create', compact('invoices', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:1',
            'notes' => 'nullable|string|max:500',
            'send_sms' => 'boolean',
        ]);

        $invoice = Invoice::with('shop.shopOwner')->findOrFail($validated['invoice_id']);

        // Ensure collector is assigned to this shop
        if ($invoice->shop->collector_id !== auth()->id()) {
            abort(403);
        }

        if ($validated['amount'] > $invoice->due_amount) {
            return back()->withErrors(['amount' => __('payments.amount_exceeds')]);
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'market_id' => auth()->user()->market_id,
            'shop_id' => $invoice->shop_id,
            'collected_by' => auth()->id(),
            'amount' => $validated['amount'],
            'payment_method' => 'cash',
            'payment_date' => today(),
            'notes' => $validated['notes'],
        ]);

        // Send SMS if requested
        if ($request->boolean('send_sms') && $invoice->shop->shopOwner?->phone) {
            $smsService = new SmsService(auth()->user()->market);
            $smsService->sendPaymentConfirmation([
                'phone' => $invoice->shop->shopOwner->phone,
                'amount' => number_format($payment->amount),
                'receipt_no' => $payment->receipt_number,
            ]);
        }

        return redirect()->route('collector.payments.index')
            ->with('success', __('payments.created'));
    }

    public function show(Payment $payment)
    {
        // Ensure collector made this payment
        if ($payment->collected_by !== auth()->id()) {
            abort(403);
        }

        $payment->load(['shop.shopOwner', 'invoice']);

        return view('collector.payments.show', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        // Ensure collector made this payment
        if ($payment->collected_by !== auth()->id()) {
            abort(403);
        }

        $payment->load(['shop.shopOwner', 'invoice', 'market']);

        return view('collector.payments.receipt', compact('payment'));
    }
}
