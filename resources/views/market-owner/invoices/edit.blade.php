<x-app-layout>
    <x-slot name="header">{{ __('invoices.edit_invoice') }} - {{ $invoice->invoice_number }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <form action="{{ route('market-owner.invoices.update', $invoice) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="glass-card p-6 space-y-6">
                <!-- Invoice Info (Read Only) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Invoice Number') }}</p>
                        <p class="font-medium text-gray-900">{{ $invoice->invoice_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Shop') }}</p>
                        <p class="font-medium text-gray-900">{{ $invoice->shop->shop_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Billing Month') }}</p>
                        <p class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($invoice->billing_month)->format('F Y') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Rent Amount -->
                    <div>
                        <label for="rent_amount" class="block text-sm font-medium text-gray-700">{{ __('Rent Amount') }} <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative">
                            <span class="absolute left-3 top-2 text-gray-500">৳</span>
                            <input type="number" name="rent_amount" id="rent_amount"
                                   value="{{ old('rent_amount', $invoice->rent_amount) }}" required step="0.01" min="0"
                                   class="pl-8 block w-full glass-input">
                        </div>
                        @error('rent_amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Previous Due -->
                    <div>
                        <label for="previous_due" class="block text-sm font-medium text-gray-700">{{ __('Previous Due') }}</label>
                        <div class="mt-1 relative">
                            <span class="absolute left-3 top-2 text-gray-500">৳</span>
                            <input type="number" name="previous_due" id="previous_due"
                                   value="{{ old('previous_due', $invoice->previous_due) }}" step="0.01" min="0"
                                   class="pl-8 block w-full glass-input">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Late Fee -->
                    <div>
                        <label for="late_fee" class="block text-sm font-medium text-gray-700">{{ __('Late Fee') }}</label>
                        <div class="mt-1 relative">
                            <span class="absolute left-3 top-2 text-gray-500">৳</span>
                            <input type="number" name="late_fee" id="late_fee"
                                   value="{{ old('late_fee', $invoice->late_fee) }}" step="0.01" min="0"
                                   class="pl-8 block w-full glass-input">
                        </div>
                    </div>

                    <!-- Discount -->
                    <div>
                        <label for="discount" class="block text-sm font-medium text-gray-700">{{ __('Discount') }}</label>
                        <div class="mt-1 relative">
                            <span class="absolute left-3 top-2 text-gray-500">৳</span>
                            <input type="number" name="discount" id="discount"
                                   value="{{ old('discount', $invoice->discount) }}" step="0.01" min="0"
                                   class="pl-8 block w-full glass-input">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">{{ __('Due Date') }} <span class="text-red-500">*</span></label>
                        <input type="date" name="due_date" id="due_date"
                               value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required
                               class="mt-1 block w-full glass-input">
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                        <select name="status" id="status"
                                class="mt-1 block w-full glass-input">
                            <option value="pending" {{ old('status', $invoice->status) === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                            <option value="partial" {{ old('status', $invoice->status) === 'partial' ? 'selected' : '' }}>{{ __('Partial') }}</option>
                            <option value="paid" {{ old('status', $invoice->status) === 'paid' ? 'selected' : '' }}>{{ __('Paid') }}</option>
                            <option value="overdue" {{ old('status', $invoice->status) === 'overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                    <textarea name="notes" id="notes" rows="3"
                              class="mt-1 block w-full glass-input">{{ old('notes', $invoice->notes) }}</textarea>
                </div>

                <!-- Calculated Total -->
                <div class="p-4 bg-indigo-50 rounded-lg">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">{{ __('Current Total Amount') }}</span>
                        <span class="text-2xl font-bold text-indigo-600">৳ {{ number_format($invoice->total_amount) }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">{{ __('Total will be recalculated on save') }}</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('market-owner.invoices.show', $invoice) }}" class="text-gray-600 hover:text-gray-800">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="px-6 py-2 btn-primary">
                    {{ __('Update Invoice') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
