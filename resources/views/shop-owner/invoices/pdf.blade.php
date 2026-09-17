{{-- Shop owner invoice PDF: same layout as the market owner invoice PDF --}}
@include('market-owner.invoices.pdf', ['invoice' => $invoice])
