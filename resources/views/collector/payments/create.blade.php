<x-app-layout>
    <x-slot name="header">{{ __('payments.collect_payment') }}</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('collector.payments.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <!-- Invoice Selection -->
            <div>
                <label for="invoice_id" class="block text-sm font-medium text-gray-700">
                    {{ __('invoices.select_invoice') }} <span class="text-red-500">*</span>
                </label>
                <select name="invoice_id" id="invoice_id" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        onchange="updateInvoiceDetails()">
                    <option value="">{{ __('payments.select_invoice') }}</option>
                    @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}"
                            data-due="{{ $invoice->due_amount }}"
                            data-shop="{{ $invoice->shop->shop_number }}"
                            data-owner="{{ $invoice->shop->shopOwner?->getLocalizedName() ?? '-' }}"
                            data-phone="{{ $invoice->shop->shopOwner?->phone ?? '-' }}"
                            {{ old('invoice_id', request('invoice')) == $invoice->id ? 'selected' : '' }}>
                        {{ $invoice->shop->shop_number }} - {{ $invoice->invoice_number }} (৳{{ number_format($invoice->due_amount) }} {{ __('invoices.due') }})
                    </option>
                    @endforeach
                </select>
                @error('invoice_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Invoice Details Preview -->
            <div id="invoice-details" class="bg-gray-50 rounded-lg p-4 hidden">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">{{ __('shops.shop') }}:</span>
                        <span id="detail-shop" class="font-medium text-gray-900 ml-1">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('shops.owner') }}:</span>
                        <span id="detail-owner" class="font-medium text-gray-900 ml-1">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('messages.phone') }}:</span>
                        <span id="detail-phone" class="font-medium text-gray-900 ml-1">-</span>
                    </div>
                    <div>
                        <span class="text-gray-500">{{ __('invoices.due_amount') }}:</span>
                        <span id="detail-due" class="font-bold text-red-600 ml-1">-</span>
                    </div>
                </div>
            </div>

            <!-- Amount -->
            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700">
                    {{ __('payments.amount') }} (৳) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="1" step="0.01"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-lg">
                @error('amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p id="amount-hint" class="mt-1 text-sm text-gray-500 hidden"></p>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    {{ __('payments.notes') }}
                </label>
                <textarea name="notes" id="notes" rows="2"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Send SMS -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="send_sms" value="1" {{ old('send_sms', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ __('payments.send_sms_confirmation') }}</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('collector.dashboard') }}"
                   class="w-full sm:w-auto text-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition text-lg font-medium">
                    {{ __('payments.collect') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function updateInvoiceDetails() {
            const select = document.getElementById('invoice_id');
            const selectedOption = select.options[select.selectedIndex];
            const detailsDiv = document.getElementById('invoice-details');
            const amountHint = document.getElementById('amount-hint');

            if (selectedOption.value) {
                document.getElementById('detail-shop').textContent = selectedOption.dataset.shop;
                document.getElementById('detail-owner').textContent = selectedOption.dataset.owner;
                document.getElementById('detail-phone').textContent = selectedOption.dataset.phone;
                document.getElementById('detail-due').textContent = '৳' + parseFloat(selectedOption.dataset.due).toLocaleString();
                detailsDiv.classList.remove('hidden');

                // Set max amount hint
                document.getElementById('amount').max = selectedOption.dataset.due;
                amountHint.textContent = '{{ __('payments.max_amount') }}: ৳' + parseFloat(selectedOption.dataset.due).toLocaleString();
                amountHint.classList.remove('hidden');

                // Auto-fill amount with full due
                if (!document.getElementById('amount').value) {
                    document.getElementById('amount').value = selectedOption.dataset.due;
                }
            } else {
                detailsDiv.classList.add('hidden');
                amountHint.classList.add('hidden');
            }
        }

        // Initial setup
        updateInvoiceDetails();
    </script>
    @endpush
</x-app-layout>
