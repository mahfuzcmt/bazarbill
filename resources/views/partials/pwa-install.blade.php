{{-- "Add to home screen" bar: shown only when the browser offers an install prompt and the user has not dismissed it --}}
<div id="pwa-install" class="hidden fixed bottom-0 inset-x-0 z-40 p-3 sm:hidden">
    <div class="glass-card flex items-center gap-3 px-4 py-3 border-l-4 border-l-indigo-500">
        <img src="{{ asset('icons/icon-192.png') }}" alt="" class="h-10 w-10 rounded-xl">
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-slate-800">{{ __('messages.pwa.title') }}</p>
            <p class="text-xs text-slate-500 truncate">{{ __('messages.pwa.body') }}</p>
        </div>
        <button type="button" id="pwa-install-btn" class="btn-primary px-3 py-1.5 text-xs">{{ __('messages.pwa.install') }}</button>
        <button type="button" id="pwa-install-dismiss" class="text-slate-400 hover:text-slate-600" aria-label="{{ __('messages.cancel') }}">&times;</button>
    </div>
</div>
<script>
    (function () {
        var deferred = null, bar = document.getElementById('pwa-install');
        if (!bar) return;
        var dismissed = false;
        try { dismissed = localStorage.getItem('pwa-dismissed') === '1'; } catch (e) {}
        window.addEventListener('beforeinstallprompt', function (e) {
            if (dismissed || window.matchMedia('(display-mode: standalone)').matches) return;
            e.preventDefault(); deferred = e; bar.classList.remove('hidden');
        });
        document.getElementById('pwa-install-btn').addEventListener('click', function () {
            if (!deferred) return; deferred.prompt(); deferred.userChoice.finally(function () { bar.classList.add('hidden'); deferred = null; });
        });
        document.getElementById('pwa-install-dismiss').addEventListener('click', function () {
            bar.classList.add('hidden'); try { localStorage.setItem('pwa-dismissed', '1'); } catch (e) {}
        });
    })();
</script>
