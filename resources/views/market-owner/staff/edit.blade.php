<x-app-layout>
    <x-slot name="header">{{ __('staff.edit_staff') }}</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('market-owner.staff.update', $staff) }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('staff.basic_info') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            {{ __('staff.name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $staff->name) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bengali Name -->
                    <div>
                        <label for="name_bn" class="block text-sm font-medium text-gray-700">
                            {{ __('staff.name_bn') }}
                        </label>
                        <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $staff->name_bn) }}"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name_bn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            {{ __('staff.email') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email', $staff->email) }}" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">
                            {{ __('staff.phone') }}
                        </label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $staff->phone) }}"
                               placeholder="01XXXXXXXXX"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Password Change (Optional) -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('staff.change_password') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('staff.leave_blank_password') }}</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            {{ __('staff.new_password') }}
                        </label>
                        <input type="password" name="password" id="password"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                            {{ __('staff.confirm_password') }}
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Shop Assignment -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('staff.assigned_shops') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('staff.select_shops_to_assign') }}</p>

                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md p-3 space-y-2">
                    @foreach($shops as $shop)
                    <label class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input type="checkbox" name="shop_ids[]" value="{{ $shop->id }}"
                               {{ in_array($shop->id, old('shop_ids', $assignedShopIds)) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="flex-1">
                            <span class="font-medium text-gray-900">{{ $shop->shop_number }}</span>
                            <span class="text-gray-500">- {{ $shop->floor }}</span>
                            @if($shop->shopOwner)
                            <span class="text-gray-400">({{ $shop->shopOwner->name }})</span>
                            @endif
                        </span>
                        @if($shop->collector_id && $shop->collector_id != $staff->id)
                        <span class="text-xs text-amber-600">{{ __('staff.assigned_to_other') }}</span>
                        @endif
                    </label>
                    @endforeach
                </div>
                @error('shop_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">{{ __('staff.active_status') }}</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">{{ __('staff.inactive_description') }}</p>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 pt-4 border-t">
                <button type="button" onclick="confirmDelete()"
                        class="w-full sm:w-auto order-last sm:order-first px-4 py-2 text-red-600 hover:text-red-800 transition text-center border border-red-300 sm:border-0 rounded-md sm:rounded-none">
                    {{ __('messages.delete') }}
                </button>
                <div class="flex flex-col-reverse sm:flex-row gap-3">
                    <a href="{{ route('market-owner.staff.show', $staff) }}"
                       class="w-full sm:w-auto text-center px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                        {{ __('messages.save_changes') }}
                    </button>
                </div>
            </div>
        </form>

        <!-- Delete Form (Hidden) -->
        <form id="delete-form" action="{{ route('market-owner.staff.destroy', $staff) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>

    @push('scripts')
    <script>
        function confirmDelete() {
            if (confirm('{{ __('staff.delete_confirmation') }}')) {
                document.getElementById('delete-form').submit();
            }
        }
    </script>
    @endpush
</x-app-layout>
