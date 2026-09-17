<x-app-layout>
    <x-slot name="header">{{ __('payments.record_payment') }}</x-slot>

    <div class="max-w-3xl">
        <form action="{{ route('market-owner.payments.store') }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Invoice Selection -->
                <div class="md:col-span-2">
                    <label for="invoice_id" class="block text-sm font-medium text-gray-700">
                        {{ __('invoices.invoice') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="invoice_id" id="invoice_id" required
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500"
                            onchange="updateInvoiceDetails()">
                        <option value="">{{ __('payments.select_invoice') }}</option>
                        @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}"
                                data-due="{{ $invoice->due_amount }}"
                                data-shop="{{ $invoice->shop->shop_number }}"
                                data-owner="{{ $invoice->shop->shopOwner?->getLocalizedName() ?? '-' }}"
                                data-month="{{ $invoice->billing_month_formatted }}"
                                {{ (old('invoice_id') ?? request('invoice')) == $invoice->id ? 'selected' : '' }}>
                            {{ $invoice->invoice_number }} - {{ $invoice->shop->shop_number }} ({{ __('invoices.due') }}: ৳{{ number_format($invoice->due_amount) }})
                        </option>
                        @endforeach
                    </select>
                    @error('invoice_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Invoice Details Preview -->
                <div id="invoice-details" class="md:col-span-2 bg-white/40 rounded-xl p-4 hidden">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('payments.invoice_details') }}</h4>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">{{ __('shops.shop') }}:</span>
                            <span id="detail-shop" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500">{{ __('shops.owner') }}:</span>
                            <span id="detail-owner" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500">{{ __('invoices.billing_month') }}:</span>
                            <span id="detail-month" class="font-medium text-gray-900 ml-1">-</span>
                        </div>
                        <div>
                            <span class="text-gray-500">{{ __('invoices.due_amount') }}:</span>
                            <span id="detail-due" class="font-medium text-red-600 ml-1">-</span>
                        </div>
                    </div>
                </div>

                <!-- Amount -->
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">
                        {{ __('payments.amount') }} (৳) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" required min="1" step="0.01"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p id="amount-warning" class="mt-1 text-sm text-red-600 hidden">{{ __('payments.amount_exceeds_due') }}</p>
                </div>

                <!-- Payment Date -->
                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700">
                        {{ __('payments.date') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('payment_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">
                        {{ __('payments.method') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="payment_method" id="payment_method" required
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>{{ __('payments.method_cash') }}</option>
                        <option value="bkash" {{ old('payment_method') === 'bkash' ? 'selected' : '' }}>{{ __('payments.method_bkash') }}</option>
                        <option value="nagad" {{ old('payment_method') === 'nagad' ? 'selected' : '' }}>{{ __('payments.method_nagad') }}</option>
                        <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>{{ __('payments.method_bank') }}</option>
                    </select>
                    @error('payment_method')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Collector -->
                <div>
                    <label for="collected_by" class="block text-sm font-medium text-gray-700">
                        {{ __('payments.collected_by') }}
                    </label>
                    <select name="collected_by" id="collected_by"
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">{{ __('payments.self_collected') }}</option>
                        @foreach($collectors as $collector)
                        <option value="{{ $collector->id }}" {{ old('collected_by') == $collector->id ? 'selected' : '' }}>
                            {{ $collector->getLocalizedName() }}
                        </option>
                        @endforeach
                    </select>
                    @error('collected_by')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Transaction Reference (for digital payments) -->
            <div id="trx-section" class="hidden">
                <label for="transaction_reference" class="block text-sm font-medium text-gray-700">
                    {{ __('payments.transaction_reference') }}
                </label>
                <input type="text" name="transaction_reference" id="transaction_reference" value="{{ old('transaction_reference') }}"
                       placeholder="{{ __('payments.trx_placeholder') }}"
                       class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    {{ __('payments.notes') }}
                </label>
                <textarea name="notes" id="notes" rows="2"
                          class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Send SMS -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="send_sms" value="1" {{ old('send_sms') ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ __('payments.send_sms_confirmation') }}</span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.payments.index') }}"
                   class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                    {{ __('payments.record_payment') }}
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

            if (selectedOption.value) {
                document.getElementById('detail-shop').textContent = selectedOption.dataset.shop;
                document.getElementById('detail-owner').textContent = selectedOption.dataset.owner;
                document.getElementById('detail-month').textContent = selectedOption.dataset.month;
                document.getElementById('detail-due').textContent = '৳' + parseFloat(selectedOption.dataset.due).toLocaleString();
                detailsDiv.classList.remove('hidden');

                // Set max amount
                document.getElementById('amount').max = selectedOption.dataset.due;
            } else {
                detailsDiv.classList.add('hidden');
            }
        }

        document.getElementById('amount').addEventListener('input', function() {
            const select = document.getElementById('invoice_id');
            const selectedOption = select.options[select.selectedIndex];
            const warning = document.getElementById('amount-warning');

            if (selectedOption.value && parseFloat(this.value) > parseFloat(selectedOption.dataset.due)) {
                warning.classList.remove('hidden');
            } else {
                warning.classList.add('hidden');
            }
        });

        document.getElementById('payment_method').addEventListener('change', function() {
            const trxSection = document.getElementById('trx-section');
            if (['bkash', 'nagad', 'bank'].includes(this.value)) {
                trxSection.classList.remove('hidden');
            } else {
                trxSection.classList.add('hidden');
            }
        });

        // Initial setup
        updateInvoiceDetails();
        document.getElementById('payment_method').dispatchEvent(new Event('change'));
    </script>
    @endpush
</x-app-layout>
