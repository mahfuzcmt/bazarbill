<x-app-layout>
    <x-slot name="header">{{ __('settings.settings') }}</x-slot>

    <div class="max-w-4xl space-y-6">
        <!-- Subscription -->
        @if($market->plan)
        <div class="glass-card overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('settings.subscription') }}</h3>
                <p class="text-sm text-gray-500">{{ __('settings.subscription_desc') }}</p>
            </div>
            <div class="px-4 sm:px-6 py-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500">{{ __('settings.plan') }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $market->plan->getLocalizedName() }}</p>
                    <p class="text-xs text-gray-500">{{ $market->isOnTrial() ? __('settings.on_trial') : __('settings.status_' . ($market->subscription_status ?? 'active')) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('settings.valid_until') }}</p>
                    @if($market->subscription_ends_at)
                        @php $days = $market->subscriptionDaysRemaining(); @endphp
                        <p class="text-lg font-semibold {{ $days <= 7 ? 'text-red-600' : 'text-gray-900' }}">{{ $market->subscription_ends_at->format('d M Y') }}</p>
                        <p class="text-xs text-gray-500">{{ __('settings.days_left', ['days' => $days]) }}</p>
                    @else
                        <p class="text-lg font-semibold text-gray-900">—</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('settings.shops_used') }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $shopCount }} / {{ $market->shopLimit() ?? '∞' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('settings.sms_credit_balance') }}</p>
                    <p class="text-lg font-semibold text-indigo-600">{{ number_format($market->sms_credits) }}</p>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-3 bg-indigo-50 text-sm text-indigo-800">
                {{ __('settings.renew_hint') }}
                @if(config('services.support.phone'))<strong>{{ config('services.support.phone') }}</strong>@endif
            </div>
        </div>
        @endif

        <!-- Market Information -->
        <form action="{{ route('market-owner.settings.update') }}" method="POST" enctype="multipart/form-data"
              class="glass-card overflow-hidden">
            @csrf
            @method('PUT')

            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('settings.market_info') }}</h3>
                <p class="text-sm text-gray-500">{{ __('settings.market_info_desc') }}</p>
            </div>

            <div class="px-4 sm:px-6 py-4 space-y-6">
                <!-- Logo -->
                <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:space-x-6">
                    <div class="flex-shrink-0">
                        @if($market->logo)
                        <img src="{{ Storage::url($market->logo) }}" alt="{{ $market->name }}"
                             class="w-24 h-24 object-cover rounded-lg">
                        @else
                        <div class="w-24 h-24 bg-white/50 border border-white/70 rounded-xl flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700">{{ __('settings.market_logo') }}</label>
                        <input type="file" name="logo" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">{{ __('settings.logo_hint') }}</p>
                        @error('logo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Market Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.market_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $market->name) }}" required
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Market Name (Bengali) -->
                    <div>
                        <label for="name_bn" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.market_name_bn') }}
                        </label>
                        <input type="text" name="name_bn" id="name_bn" value="{{ old('name_bn', $market->name_bn) }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name_bn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.contact_email') }}
                        </label>
                        <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $market->contact_email) }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('contact_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.contact_phone') }}
                        </label>
                        <input type="tel" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $market->contact_phone) }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('contact_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700">
                        {{ __('settings.address') }}
                    </label>
                    <textarea name="address" id="address" rows="2"
                              class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('address', $market->address) }}</textarea>
                    @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Address (Bengali) -->
                <div>
                    <label for="address_bn" class="block text-sm font-medium text-gray-700">
                        {{ __('settings.address_bn') }}
                    </label>
                    <textarea name="address_bn" id="address_bn" rows="2"
                              class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">{{ old('address_bn', $market->address_bn) }}</textarea>
                    @error('address_bn')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="px-4 sm:px-6 py-4 bg-white/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>

        <!-- SMS Settings -->
        <form action="{{ route('market-owner.settings.sms') }}" method="POST"
              class="glass-card overflow-hidden">
            @csrf
            @method('PUT')

            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('settings.sms_settings') }}</h3>
                <p class="text-sm text-gray-500">{{ __('settings.sms_settings_desc') }} {{ __('settings.sms_gateway_choice') }}</p>
            </div>

            <div class="px-4 sm:px-6 py-4 space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- SMS API Key -->
                    <div>
                        <label for="sms_api_key" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.sms_api_key') }}
                        </label>
                        <input type="password" name="sms_api_key" id="sms_api_key"
                               value="{{ old('sms_api_key', $market->sms_api_key) }}"
                               placeholder="••••••••"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('sms_api_key')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SMS Sender ID -->
                    <div>
                        <label for="sms_sender_id" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.sms_sender_id') }}
                        </label>
                        <input type="text" name="sms_sender_id" id="sms_sender_id"
                               value="{{ old('sms_sender_id', $market->sms_sender_id) }}"
                               placeholder="BAZARBILL"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        @error('sms_sender_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if($market->usesPlatformSms())
                <div class="p-4 rounded-lg {{ $market->hasLowSmsCredits() ? 'bg-red-50' : 'bg-indigo-50' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <p class="text-sm {{ $market->hasLowSmsCredits() ? 'text-red-800' : 'text-indigo-800' }}">
                                <strong>{{ __('settings.sms_credit_balance') }}:</strong>
                                <span class="text-lg font-bold">{{ number_format($market->sms_credits) }}</span>
                            </p>
                            <p class="text-xs {{ $market->hasLowSmsCredits() ? 'text-red-700' : 'text-indigo-700' }} mt-1">
                                {{ $market->hasLowSmsCredits() ? __('settings.sms_credits_low') : __('settings.sms_credits_hint') }}
                            </p>
                        </div>
                        <a href="{{ route('market-owner.settings.sms-credits') }}"
                           class="text-sm font-medium text-indigo-700 hover:underline whitespace-nowrap">
                            {{ __('settings.sms_credit_history') }} &rarr;
                        </a>
                    </div>
                </div>
                @else
                <div class="p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>{{ __('settings.sms_provider') }}:</strong> bulksmsbd.net ({{ __('settings.sms_own_gateway') }})
                    </p>
                    <p class="text-xs text-blue-600 mt-1">
                        {{ __('settings.sms_own_gateway_hint') }}
                    </p>
                </div>
                @endif

                <!-- Test SMS -->
                <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                    <div class="flex-1">
                        <label for="test_phone" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.test_sms') }}
                        </label>
                        <input type="tel" id="test_phone" placeholder="01XXXXXXXXX"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <button type="button" onclick="sendTestSms()"
                            class="w-full sm:w-auto px-4 py-2 border border-indigo-600 text-indigo-600 rounded-md hover:bg-indigo-50 transition">
                        {{ __('settings.send_test') }}
                    </button>
                </div>
            </div>

            <div class="px-4 sm:px-6 py-4 bg-white/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>

        <!-- Invoice Settings -->
        <form action="{{ route('market-owner.settings.invoice') }}" method="POST"
              class="glass-card overflow-hidden">
            @csrf
            @method('PUT')

            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('settings.invoice_settings') }}</h3>
                <p class="text-sm text-gray-500">{{ __('settings.invoice_settings_desc') }}</p>
            </div>

            <div class="px-4 sm:px-6 py-4 space-y-4">
                @php
                    $settings = $market->settings ?? [];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Invoice Prefix -->
                    <div>
                        <label for="invoice_prefix" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.invoice_prefix') }}
                        </label>
                        <input type="text" name="settings[invoice_prefix]" id="invoice_prefix"
                               value="{{ old('settings.invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Due Days -->
                    <div>
                        <label for="due_days" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.due_days') }}
                        </label>
                        <input type="number" name="settings[due_days]" id="due_days" min="1" max="30"
                               value="{{ old('settings.due_days', $settings['due_days'] ?? 7) }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Late Fee Percentage -->
                    <div>
                        <label for="late_fee_percent" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.late_fee_percent') }}
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="number" name="settings[late_fee_percent]" id="late_fee_percent"
                                   min="0" max="100" step="0.5"
                                   value="{{ old('settings.late_fee_percent', $settings['late_fee_percent'] ?? 0) }}"
                                   class="block w-full pr-10 border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Grace Period -->
                    <div>
                        <label for="grace_days" class="block text-sm font-medium text-gray-700">
                            {{ __('settings.grace_days') }}
                        </label>
                        <input type="number" name="settings[grace_days]" id="grace_days" min="0" max="15"
                               value="{{ old('settings.grace_days', $settings['grace_days'] ?? 3) }}"
                               class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Auto Generate Invoices -->
                <div class="space-y-3">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="settings[auto_generate]" value="1"
                               {{ ($settings['auto_generate'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">{{ __('settings.auto_generate_invoices') }}</span>
                    </label>
                    <p class="text-xs text-gray-500 ml-7">{{ __('settings.auto_generate_hint') }}</p>
                </div>

                <!-- Send SMS on Invoice -->
                <div class="space-y-3">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="settings[sms_on_invoice]" value="1"
                               {{ ($settings['sms_on_invoice'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">{{ __('settings.sms_on_invoice') }}</span>
                    </label>
                    <p class="text-xs text-gray-500 ml-7">{{ __('settings.sms_on_invoice_hint') }}</p>
                </div>

                <!-- Automatic reminders -->
                <div class="space-y-3">
                    <label class="flex items-center space-x-3">
                        <input type="checkbox" name="settings[auto_reminder]" value="1"
                               {{ ($settings['auto_reminder'] ?? false) ? 'checked' : '' }}
                               class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">{{ __('settings.auto_reminder') }}</span>
                    </label>
                    <p class="text-xs text-gray-500 ml-7">{{ __('settings.auto_reminder_hint') }}</p>
                    <div class="ml-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="reminder_days_before" class="block text-sm font-medium text-gray-700">{{ __('settings.reminder_days_before') }}</label>
                            <input type="number" name="settings[reminder_days_before]" id="reminder_days_before" min="0" max="15"
                                   value="{{ old('settings.reminder_days_before', $settings['reminder_days_before'] ?? 3) }}"
                                   class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div>
                            <label for="reminder_repeat_days" class="block text-sm font-medium text-gray-700">{{ __('settings.reminder_repeat_days') }}</label>
                            <input type="number" name="settings[reminder_repeat_days]" id="reminder_repeat_days" min="1" max="30"
                                   value="{{ old('settings.reminder_repeat_days', $settings['reminder_repeat_days'] ?? 7) }}"
                                   class="mt-1 block w-full glass-input focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="px-4 sm:px-6 py-4 bg-white/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>

        <!-- Staff Permissions -->
        <form action="{{ route('market-owner.settings.permissions') }}" method="POST"
              class="glass-card overflow-hidden">
            @csrf
            @method('PUT')

            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('settings.staff_permissions') }}</h3>
                <p class="text-sm text-gray-500">{{ __('settings.staff_permissions_desc') }}</p>
            </div>

            <div class="px-4 sm:px-6 py-4 space-y-4">
                @php
                    $permissions = $settings['staff_permissions'] ?? [];
                @endphp

                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="settings[staff_permissions][can_create_shop]" value="1"
                           {{ ($permissions['can_create_shop'] ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ __('settings.perm_create_shop') }}</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="settings[staff_permissions][can_update_rent]" value="1"
                           {{ ($permissions['can_update_rent'] ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ __('settings.perm_update_rent') }}</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="settings[staff_permissions][can_send_sms]" value="1"
                           {{ ($permissions['can_send_sms'] ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ __('settings.perm_send_sms') }}</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="settings[staff_permissions][can_view_all_shops]" value="1"
                           {{ ($permissions['can_view_all_shops'] ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ __('settings.perm_view_all_shops') }}</span>
                </label>

                <label class="flex items-center space-x-3">
                    <input type="checkbox" name="settings[staff_permissions][can_manage_complaints]" value="1"
                           {{ ($permissions['can_manage_complaints'] ?? false) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ __('settings.perm_manage_complaints') }}</span>
                </label>
            </div>

            <div class="px-4 sm:px-6 py-4 bg-white/40 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">
                    {{ __('messages.save_changes') }}
                </button>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="glass-card overflow-hidden border border-red-200">
            <div class="px-4 sm:px-6 py-4 border-b border-red-200 bg-red-50">
                <h3 class="text-lg font-semibold text-red-800">{{ __('settings.danger_zone') }}</h3>
                <p class="text-sm text-red-600">{{ __('settings.danger_zone_desc') }}</p>
            </div>

            <div class="px-4 sm:px-6 py-4 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-medium text-gray-900">{{ __('settings.export_data') }}</p>
                        <p class="text-sm text-gray-500">{{ __('settings.export_data_desc') }}</p>
                    </div>
                    <a href="{{ route('market-owner.settings.export') }}"
                       class="w-full sm:w-auto text-center px-4 py-2 btn-secondary transition">
                        {{ __('settings.download_export') }}
                    </a>
                </div>

                <hr class="border-red-100">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <p class="font-medium text-red-800">{{ __('settings.delete_market') }}</p>
                        <p class="text-sm text-red-600">{{ __('settings.delete_market_desc') }}</p>
                    </div>
                    <button type="button" onclick="confirmDeleteMarket()"
                            class="w-full sm:w-auto px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                        {{ __('settings.delete_market') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function sendTestSms() {
            const phone = document.getElementById('test_phone').value;
            if (!phone) {
                alert('{{ __('settings.enter_phone_number') }}');
                return;
            }
            // AJAX call to send test SMS
            fetch('{{ route('market-owner.settings.test-sms') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone: phone })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('{{ __('settings.test_sms_sent') }}');
                } else {
                    alert(data.message || '{{ __('settings.test_sms_failed') }}');
                }
            })
            .catch(error => {
                alert('{{ __('settings.test_sms_failed') }}');
            });
        }

        function confirmDeleteMarket() {
            if (confirm('{{ __('settings.delete_confirmation') }}')) {
                if (confirm('{{ __('settings.delete_final_confirmation') }}')) {
                    // Submit delete request
                    window.location.href = '{{ route('market-owner.settings.destroy') }}';
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
