<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Month-by-month statement for every shop this person owns:
     * bill, what was paid and when, and what is still due.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $shops = $user->ownedShops()->orderBy('shop_number')->get();

        if ($shops->isEmpty()) {
            return redirect()->route('shop-owner.dashboard');
        }

        $shopIds = $shops->pluck('id');

        $years = Invoice::whereIn('shop_id', $shopIds)
            ->selectRaw('substr(billing_month, 1, 4) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->all();

        $year = $request->filled('year') && in_array($request->year, $years, true) ? $request->year : null;

        $query = Invoice::whereIn('shop_id', $shopIds)
            ->with(['shop', 'payments' => fn ($q) => $q->orderBy('payment_date')])
            ->when($year, fn ($q) => $q->where('billing_month', 'like', $year . '-%'))
            ->when($shops->count() > 1 && $request->filled('shop_id') && $shopIds->contains((int) $request->shop_id),
                fn ($q) => $q->where('shop_id', (int) $request->shop_id))
            ->orderBy('billing_month', 'desc')
            ->orderBy('shop_id');

        $invoices = $query->paginate(12)->withQueryString();

        $totals = Invoice::whereIn('shop_id', $shopIds)
            ->selectRaw('COALESCE(SUM(total_amount),0) as billed, COALESCE(SUM(paid_amount),0) as paid, COALESCE(SUM(due_amount),0) as due')
            ->first();

        return view('shop-owner.invoices.index', [
            'invoices' => $invoices,
            'shops' => $shops,
            'shop' => $shops->first(),
            'years' => $years,
            'year' => $year,
            'totalBilled' => $totals->billed,
            'totalPaid' => $totals->paid,
            'totalDue' => $totals->due,
        ]);
    }

    public function show(Invoice $invoice)
    {
        $this->ensureOwnInvoice($invoice);

        $invoice->load(['payments.collector', 'shop']);

        return view('shop-owner.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->ensureOwnInvoice($invoice);

        $invoice->load(['shop.shopOwner', 'market']);

        $pdf = PdfService::fromView('shop-owner.invoices.pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    protected function ensureOwnInvoice(Invoice $invoice): void
    {
        if (!auth()->user()->ownedShops()->where('id', $invoice->shop_id)->exists()) {
            abort(403);
        }
    }
}
