<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="glass-thead">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Time') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Market') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('To') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Gateway') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Message / gateway response') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200/70">
            @forelse($logs as $log)
                <tr class="hover:bg-white/50 transition align-top">
                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $log->created_at->format('d M, H:i') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800">{{ $log->market?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $log->recipient_phone }}</td>
                    <td class="px-4 py-3 text-xs text-gray-600">{{ $log->gateway === 'own' ? __('Own key') : __('Platform') }}@if($log->credits_used) · {{ $log->credits_used }} cr @endif</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full {{ $log->status === 'sent' ? 'bg-green-100 text-green-800' : ($log->status === 'failed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($log->status) }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 max-w-md">
                        <div class="truncate" title="{{ $log->message }}">{{ \Illuminate\Support\Str::limit($log->message, 80) }}</div>
                        @if($log->status === 'failed' && $log->api_response)
                            <div class="mt-1 text-red-700 break-all">{{ \Illuminate\Support\Str::limit($log->api_response, 160) }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">{{ __('No SMS sent yet.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
