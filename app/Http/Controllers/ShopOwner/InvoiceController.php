<?php

namespace App\Http\Controllers\ShopOwner;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('shop-owner.dashboard');
        }

        $invoices = Invoice::where('shop_id', $shop->id)
            ->orderBy('billing_month', 'desc')
            ->paginate(12);

        $totalBilled = Invoice::where('shop_id', $shop->id)->sum('total_amount');
        $totalPaid = Invoice::where('shop_id', $shop->id)->sum('paid_amount');
        $totalDue = Invoice::where('shop_id', $shop->id)->sum('due_amount');

        return view('shop-owner.invoices.index', compact('invoices', 'shop', 'totalBilled', 'totalPaid', 'totalDue'));
    }

    public function show(Invoice $invoice)
    {
        $user = auth()->user();

        // Ensure this invoice belongs to the shop owner's shop
        if (!$user->shop || $invoice->shop_id !== $user->shop->id) {
            abort(403);
        }

        $invoice->load(['payments.collector', 'shop']);

        return view('shop-owner.invoices.show', compact('invoice'));
    }

    public function downloadPdf(Invoice $invoice)
    {
        $user = auth()->user();

        // Ensure this invoice belongs to the shop owner's shop
        if (!$user->shop || $invoice->shop_id !== $user->shop->id) {
            abort(403);
        }

        $invoice->load(['shop.shopOwner', 'market']);

        $pdf = PdfService::fromView('shop-owner.invoices.pdf', compact('invoice'));

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
