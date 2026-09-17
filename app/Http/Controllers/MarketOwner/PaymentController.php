<?php

namespace App\Http\Controllers\MarketOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['shop', 'invoice', 'collector']);

        if ($request->filled('shop')) {
            $query->where('shop_id', $request->shop);
        }

        if ($request->filled('collector')) {
            $query->where('collected_by', $request->collector);
        }

        $from = $request->input('from', $request->input('date_from'));
        if (filled($from)) {
            $query->whereDate('payment_date', '>=', $from);
        }

        $to = $request->input('to', $request->input('date_to'));
        if (filled($to)) {
            $query->whereDate('payment_date', '<=', $to);
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(15)->withQueryString();

        $shops = Shop::orderBy('shop_number')->get();
        $collectors = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'collector')->orderBy('name')->get();

        $todayTotal = Payment::whereDate('payment_date', today())->sum('amount');
        $weekTotal = Payment::whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('amount');
        $monthTotal = Payment::whereYear('payment_date', now()->year)
            ->whereMonth('payment_date', now()->month)
            ->sum('amount');
        $allTimeTotal = Payment::sum('amount');

        return view('market-owner.payments.index', compact(
            'payments', 'shops', 'collectors', 'todayTotal', 'weekTotal', 'monthTotal', 'allTimeTotal'
        ));
    }

    public function create(Request $request)
    {
        $invoices = Invoice::with('shop')
            ->whereIn('status', ['pending', 'partial', 'overdue'])
            ->orderBy('due_date')
            ->get();

        $selectedInvoice = $request->invoice_id
            ? Invoice::with('shop.shopOwner')->find($request->invoice_id)
            : null;

        $collectors = User::where('market_id', auth()->user()->market_id)
            ->where('role', 'collector')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('market-owner.payments.create', compact('invoices', 'selectedInvoice', 'collectors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => ['required', Rule::exists('invoices', 'id')->where('market_id', auth()->user()->market_id)],
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|in:cash,bkash,nagad,bank',
            'transaction_reference' => 'nullable|string|max:100',
            'collected_by' => ['nullable', Rule::exists('users', 'id')->where('market_id', auth()->user()->market_id)],
            'notes' => 'nullable|string|max:500',
            'send_sms' => 'boolean',
        ]);

        $invoice = Invoice::with('shop.shopOwner')->findOrFail($validated['invoice_id']);

        if ($validated['amount'] > $invoice->due_amount) {
            return back()->withErrors(['amount' => __('payments.amount_exceeds')]);
        }

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'market_id' => auth()->user()->market_id,
            'shop_id' => $invoice->shop_id,
            'collected_by' => $validated['collected_by'] ?? auth()->id(),
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'] ?? 'cash',
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'payment_date' => $validated['payment_date'],
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

        return redirect()->route('market-owner.payments.index')
            ->with('success', __('payments.created'));
    }

    public function show(Payment $payment)
    {
        $payment->load(['shop.shopOwner', 'invoice', 'collector']);

        return view('market-owner.payments.show', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        // Reverse the payment from invoice
        $invoice = $payment->invoice;
        $invoice->paid_amount -= $payment->amount;
        $invoice->due_amount += $payment->amount;

        if ($invoice->paid_amount <= 0) {
            $invoice->status = 'pending';
        } elseif ($invoice->due_amount > 0) {
            $invoice->status = 'partial';
        }

        $invoice->save();

        $payment->delete();

        return redirect()->route('market-owner.payments.index')
            ->with('success', __('payments.deleted'));
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['shop.shopOwner', 'invoice', 'collector', 'market']);

        return view('market-owner.payments.receipt', compact('payment'));
    }
}
