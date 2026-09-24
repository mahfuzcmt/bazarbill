<x-app-layout>
    <x-slot name="header">{{ __('SMS Gateway') }}</x-slot>

    <div class="space-y-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Gateway balance') }}</p>
                <p class="text-2xl font-bold {{ $balance === null ? 'text-gray-400' : ($balance < 100 ? 'text-red-600' : 'text-gray-800') }}">
                    {{ $balance === null ? '—' : '৳ ' . number_format($balance, 2) }}
                </p>
                <p class="text-xs text-gray-500">{{ $settings['api_key'] ? __('bulksmsbd.net account') : __('No API key set') }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Sent today') }}</p>
                <p class="text-2xl font-bold text-green-600">{{ $stats['sent_today'] }}</p>
                @if($stats['failed_today'])<p class="text-xs text-red-600">{{ $stats['failed_today'] }} {{ __('failed') }}</p>@endif
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Sent this month') }}</p>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['sent_month']) }}</p>
            </div>
            <div class="glass-card p-4">
                <p class="text-sm text-gray-500">{{ __('Markets on platform gateway') }}</p>
                <p class="text-2xl font-bold text-indigo-600">{{ $stats['platform_markets'] }}</p>
            </div>
        </div>

        @if(!$settings['api_key'])
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                {{ __('No platform API key is configured. Markets without their own key cannot send SMS until you save one below.') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Credentials -->
            <div class="glass-card">
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('Platform credentials') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Your bulksmsbd.net account. Every market that has not entered its own key sends through this account and pays with the SMS credits you assign.') }}</p>
                </div>
                <form method="POST" action="{{ route('admin.sms-settings.update') }}" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1">{{ __('API key') }}</label>
                        <input type="password" name="api_key" id="api_key" autocomplete="off"
                               placeholder="{{ $settings['api_key'] ? '••••••••' . substr($settings['api_key'], -4) : __('Paste the API key from bulksmsbd.net') }}"
                               class="w-full glass-input">
                        <p class="mt-1 text-xs text-gray-500">
                            @if($settings['source'] === 'database') {{ __('Currently set from this page. Leave blank to keep it.') }}
                            @elseif($settings['source'] === 'env') {{ __('Currently read from the server .env file. Saving here overrides it.') }}
                            @else {{ __('Not set.') }} @endif
                        </p>
                        @error('api_key')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    @if($settings['source'] === 'database')
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="clear_api_key" value="1" class="rounded border-gray-300">
                        {{ __('Remove the stored key') }}
                    </label>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="sender_id" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sender ID') }}</label>
                            <input type="text" name="sender_id" id="sender_id" value="{{ old('sender_id', $settings['sender_id']) }}" required maxlength="20" class="w-full glass-input">
                            <p class="mt-1 text-xs text-gray-500">{{ __('Non-masking number from your bulksmsbd account, e.g. 8809617642636.') }}</p>
                            @error('sender_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="low_credit_threshold" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Low credit warning at') }}</label>
                            <input type="number" name="low_credit_threshold" id="low_credit_threshold" min="0" max="10000" value="{{ old('low_credit_threshold', $settings['low_credit_threshold']) }}" required class="w-full glass-input">
                            <p class="mt-1 text-xs text-gray-500">{{ __('Market owners see a warning at or below this many credits.') }}</p>
                            @error('low_credit_threshold')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 btn-primary transition">{{ __('Save settings') }}</button>
                </form>
            </div>

            <!-- Test -->
            <div class="space-y-6">
                <div class="glass-card">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('Send a test SMS') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Uses the platform credentials directly. Does not consume any market\'s credits.') }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.sms-settings.test') }}" class="p-6 flex flex-col sm:flex-row gap-3 sm:items-end">
                        @csrf
                        <div class="flex-1">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Mobile number') }}</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required class="w-full glass-input">
                            @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="px-4 py-2 btn-secondary transition" {{ $settings['api_key'] ? '' : 'disabled' }}>{{ __('Send test') }}</button>
                    </form>
                </div>

                <div class="glass-card p-6 text-sm text-gray-700 space-y-2">
                    <h3 class="text-base font-semibold text-gray-800">{{ __('Why an SMS may fail') }}</h3>
                    <ul class="list-disc ml-5 space-y-1 text-gray-600">
                        <li>{{ __('Gateway balance is zero: recharge your bulksmsbd.net account.') }}</li>
                        <li>{{ __('The market has no SMS credits: add some under SMS Credits.') }}</li>
                        <li>{{ __('Wrong API key or sender ID: the gateway error appears in the log below.') }}</li>
                        <li>{{ __('Sender ID not approved for masking: use the non-masking number.') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent logs -->
        <div class="glass-card">
            <div class="p-6 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800">{{ __('Recent SMS') }}</h3>
                <a href="{{ route('admin.sms-logs.index') }}" class="text-sm text-indigo-600 hover:underline">{{ __('Full log') }} &rarr;</a>
            </div>
            @include('admin.sms-settings._log-table', ['logs' => $recentLogs])
        </div>
    </div>
</x-app-layout>
