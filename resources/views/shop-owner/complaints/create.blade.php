<x-app-layout>
    <x-slot name="header">{{ __('complaints.submit_complaint') }}</x-slot>

    <div class="max-w-2xl">
        <form action="{{ route('shop-owner.complaints.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700">
                    {{ __('complaints.subject') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" required
                       placeholder="{{ __('complaints.subject_placeholder') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('subject')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Priority -->
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700">
                    {{ __('complaints.priority') }} <span class="text-red-500">*</span>
                </label>
                <select name="priority" id="priority" required
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>{{ __('complaints.priority_low') }}</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>{{ __('complaints.priority_medium') }}</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>{{ __('complaints.priority_high') }}</option>
                </select>
                @error('priority')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    {{ __('complaints.description') }} <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="5" required
                          placeholder="{{ __('complaints.description_placeholder') }}"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
                <a href="{{ route('shop-owner.complaints.index') }}"
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition">
                    {{ __('complaints.submit') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
