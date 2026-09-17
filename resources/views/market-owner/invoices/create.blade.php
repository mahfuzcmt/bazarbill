<x-app-layout>
    <x-slot name="header">{{ __('invoices.generate_invoice') }}</x-slot>

    <div class="max-w-3xl">
        <form action="{{ route('market-owner.invoices.store') }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf

            <!-- Bulk or Single -->
            <div class="border-b pb-6">
                <div class="flex items-center space-x-6">
                    <label class="flex items-center">
                        <input type="radio" name="generation_type" value="single" checked
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                               onchange="toggleGenerationType()">
                        <span class="ml-2 text-sm text-gray-700">{{ __('invoices.single_shop') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" name="generation_type" value="bulk"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                               onchange="toggleGenerationType()">
                        <span class="ml-2 text-sm text-gray-700">{{ __('invoices.all_shops') }}</span>
                    </label>
                </div>
            </div>

            <!-- Single Shop Selection -->
            <div id="single-shop-section">
                <label for="shop_id" class="block text-sm font-medium text-gray-700">
                    {{ __('shops.shop') }} <span class="text-red-500">*</span>
                </label>
                <select name="shop_id" id="shop_id"
                        class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">{{ __('invoices.select_shop') }}</option>
                    @foreach($shops as $shop)
                    <option value="{{ $shop->id }}" data-rent="{{ $shop->rent_amount }}" {{ old('shop_id') == $shop->id ? 'selected' : '' }}>
                        {{ $shop->shop_number }} - {{ $shop->shopOwner?->getLocalizedName() ?? __('shops.no_owner') }} (৳{{ number_format($shop->rent_amount) }})
                    </option>
                    @endforeach
                </select>
                @error('shop_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Billing Month -->
                <div>
                    <label for="billing_month" class="block text-sm font-medium text-gray-700">
                        {{ __('invoices.billing_month') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="month" name="billing_month" id="billing_month" value="{{ old('billing_month', now()->format('Y-m')) }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('billing_month')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Due Date -->
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">
                        {{ __('invoices.due_date') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date', now()->endOfMonth()->format('Y-m-d')) }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Amount Section (for single shop) -->
            <div id="amount-section" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Rent Amount -->
                <div>
                    <label for="rent_amount" class="block text-sm font-medium text-gray-700">
                        {{ __('invoices.rent_amount') }} (৳)
                    </label>
                    <input type="number" name="rent_amount" id="rent_amount" value="{{ old('rent_amount') }}" min="0" step="0.01"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500"
                           onchange="calculateTotal()">
                    <p class="mt-1 text-xs text-gray-500">{{ __('invoices.leave_blank_default') }}</p>
                </div>

                <!-- Discount -->
                <div>
                    <label for="discount" class="block text-sm font-medium text-gray-700">
                        {{ __('invoices.discount') }} (৳)
                    </label>
                    <input type="number" name="discount" id="discount" value="{{ old('discount', 0) }}" min="0" step="0.01"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500"
                           onchange="calculateTotal()">
                </div>

                <!-- Late Fee -->
                <div>
                    <label for="late_fee" class="block text-sm font-medium text-gray-700">
                        {{ __('invoices.late_fee') }} (৳)
                    </label>
                    <input type="number" name="late_fee" id="late_fee" value="{{ old('late_fee', 0) }}" min="0" step="0.01"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500"
                           onchange="calculateTotal()">
                </div>
            </div>

            <!-- Total Preview -->
            <div id="total-preview" class="bg-white/40 rounded-xl p-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-700">{{ __('invoices.estimated_total') }}:</span>
                    <span id="total-amount" class="text-lg font-bold text-indigo-600">৳0</span>
                </div>
            </div>

            <!-- Include Previous Due -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="include_previous_due" value="1" {{ old('include_previous_due', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ __('invoices.include_previous_due') }}</span>
                </label>
            </div>

            <!-- Send SMS -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="send_sms" value="1" {{ old('send_sms') ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ __('invoices.send_sms_notification') }}</span>
                </label>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    {{ __('invoices.notes') }}
                </label>
                <textarea name="notes" id="notes" rows="2"
                          class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.invoices.index') }}"
                   class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('invoices.generate_invoice') }}
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function toggleGenerationType() {
            const isBulk = document.querySelector('input[name="generation_type"]:checked').value === 'bulk';
            document.getElementById('single-shop-section').style.display = isBulk ? 'none' : 'block';
            document.getElementById('amount-section').style.display = isBulk ? 'none' : 'grid';
            document.getElementById('total-preview').style.display = isBulk ? 'none' : 'block';
        }

        function calculateTotal() {
            const shopSelect = document.getElementById('shop_id');
            const selectedOption = shopSelect.options[shopSelect.selectedIndex];
            const defaultRent = selectedOption ? parseFloat(selectedOption.dataset.rent) || 0 : 0;

            const rentAmount = parseFloat(document.getElementById('rent_amount').value) || defaultRent;
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const lateFee = parseFloat(document.getElementById('late_fee').value) || 0;

            const total = rentAmount - discount + lateFee;
            document.getElementById('total-amount').textContent = '৳' + total.toLocaleString();
        }

        document.getElementById('shop_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.dataset.rent) {
                document.getElementById('rent_amount').placeholder = '৳' + parseFloat(selectedOption.dataset.rent).toLocaleString();
            }
            calculateTotal();
        });

        // Initial calculation
        calculateTotal();
    </script>
    @endpush
</x-app-layout>
