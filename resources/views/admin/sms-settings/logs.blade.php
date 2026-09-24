<x-app-layout>
    <x-slot name="header">{{ __('SMS Log') }}</x-slot>

    <div class="glass-card">
        <div class="p-6 border-b">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 sm:items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Status') }}</label>
                    <select name="status" class="glass-input w-full sm:w-36">
                        <option value="">{{ __('All') }}</option>
                        @foreach(['sent', 'failed', 'pending'] as $s)
                            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Gateway') }}</label>
                    <select name="gateway" class="glass-input w-full sm:w-36">
                        <option value="">{{ __('All') }}</option>
                        <option value="platform" @selected(request('gateway') === 'platform')>{{ __('Platform') }}</option>
                        <option value="own" @selected(request('gateway') === 'own')>{{ __('Own key') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Market') }}</label>
                    <select name="market_id" class="glass-input w-full sm:w-48">
                        <option value="">{{ __('All') }}</option>
                        @foreach($markets as $m)
                            <option value="{{ $m->id }}" @selected(request('market_id') == $m->id)>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('Phone') }}</label>
                    <input type="text" name="phone" value="{{ request('phone') }}" class="w-full glass-input" placeholder="017...">
                </div>
                <button type="submit" class="px-4 py-2 btn-primary transition">{{ __('Filter') }}</button>
                <a href="{{ route('admin.sms-settings.index') }}" class="px-4 py-2 btn-secondary transition text-center">{{ __('Gateway settings') }}</a>
            </form>
        </div>
        @include('admin.sms-settings._log-table', ['logs' => $logs])
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t">{{ $logs->links() }}</div>
        @endif
    </div>
</x-app-layout>
