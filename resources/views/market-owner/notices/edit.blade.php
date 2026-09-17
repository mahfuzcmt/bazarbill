<x-app-layout>
    <x-slot name="header">{{ __('notices.edit_notice') }}</x-slot>

    <div class="max-w-3xl">
        <form action="{{ route('market-owner.notices.update', $notice) }}" method="POST" class="glass-card p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title (English) -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        {{ __('notices.title') }} (English) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $notice->title) }}" required
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title (Bengali) -->
                <div>
                    <label for="title_bn" class="block text-sm font-medium text-gray-700">
                        {{ __('notices.title') }} (বাংলা)
                    </label>
                    <input type="text" name="title_bn" id="title_bn" value="{{ old('title_bn', $notice->title_bn) }}"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    @error('title_bn')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Content (English) -->
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700">
                    {{ __('notices.content') }} (English) <span class="text-red-500">*</span>
                </label>
                <textarea name="content" id="content" rows="4" required
                          class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('content', $notice->content) }}</textarea>
                @error('content')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Content (Bengali) -->
            <div>
                <label for="content_bn" class="block text-sm font-medium text-gray-700">
                    {{ __('notices.content') }} (বাংলা)
                </label>
                <textarea name="content_bn" id="content_bn" rows="4"
                          class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('content_bn', $notice->content_bn) }}</textarea>
                @error('content_bn')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Target Role -->
                <div>
                    <label for="target_role" class="block text-sm font-medium text-gray-700">
                        {{ __('notices.target_audience') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="target_role" id="target_role" required
                            class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach(['all', 'shop_owner', 'collector'] as $role)
                        <option value="{{ $role }}" {{ old('target_role', $notice->target_role) === $role ? 'selected' : '' }}>{{ __('notices.target_' . $role) }}</option>
                        @endforeach
                    </select>
                    @error('target_role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiry Date -->
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700">
                        {{ __('notices.expires_at') }}
                    </label>
                    <input type="date" name="expires_at" id="expires_at" value="{{ old('expires_at', $notice->expires_at?->format('Y-m-d')) }}"
                           class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">{{ __('notices.leave_blank_no_expiry') }}</p>
                    @error('expires_at')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pin Notice -->
                <div class="flex items-end">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_pinned" value="1" {{ old('is_pinned', $notice->is_pinned) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-700">{{ __('notices.pin_notice') }}</span>
                    </label>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t">
                <a href="{{ route('market-owner.notices.index') }}"
                   class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                    {{ __('messages.cancel') }}
                </a>
                <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
