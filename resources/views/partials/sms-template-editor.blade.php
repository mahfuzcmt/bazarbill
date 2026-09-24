{{-- Shared editor for the three SMS texts. Expects: $fieldPrefix, $values (type => text|null), $defaults (type => text), $blankHint --}}
@php $sample = \App\Support\SmsTemplates::sampleData(); @endphp
<div class="space-y-5" data-sms-editor>
    <div>
        <h4 class="text-sm font-semibold text-gray-800">{{ __('settings.sms_templates') }}</h4>
        <p class="text-xs text-gray-500">{{ __('settings.sms_templates_desc') }} {{ $blankHint }}</p>
    </div>
    @foreach(\App\Support\SmsTemplates::TYPES as $type)
    @php $id = $fieldPrefix . '_' . $type; $current = old($fieldPrefix . '.' . $type, $values[$type] ?? ''); @endphp
    <div class="rounded-xl border border-gray-200 bg-white/50 p-4">
        <label for="{{ $id }}" class="block text-sm font-medium text-gray-800">{{ __('settings.sms_template_types.' . $type) }}</label>
        <textarea name="{{ $fieldPrefix }}[{{ $type }}]" id="{{ $id }}" rows="3" maxlength="{{ \App\Support\SmsTemplates::MAX_LENGTH }}"
                  data-sms-input data-default="{{ $defaults[$type] }}"
                  placeholder="{{ $defaults[$type] }}"
                  class="mt-1 block w-full glass-input text-sm focus:ring-indigo-500 focus:border-indigo-500">{{ $current }}</textarea>
        @error($fieldPrefix . '.' . $type)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="mt-2 flex flex-wrap gap-1.5">
            <span class="text-xs text-gray-500 mr-1">{{ __('settings.sms_placeholders_label') }}:</span>
            @foreach(\App\Support\SmsTemplates::placeholdersFor($type) as $ph)
                <button type="button" data-insert="{{ '{' . $ph . '}' }}" data-target="{{ $id }}"
                        class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-mono text-indigo-700 hover:bg-indigo-100"
                        title="{{ __('settings.sms_placeholders.' . $ph) }}">{{ '{' . $ph . '}' }}</button>
            @endforeach
        </div>
        <div class="mt-2 text-xs text-gray-600">
            <span class="font-medium">{{ __('settings.sms_preview_label') }}:</span>
            <span data-sms-preview class="text-gray-800"></span>
            <span data-sms-length class="ml-2 text-gray-400"></span>
        </div>
        @if(empty($current))
            <p class="mt-1 text-xs text-gray-400">{{ __('settings.sms_default_label') }}: {{ $defaults[$type] }}</p>
        @endif
    </div>
    @endforeach
</div>
@once
<script>
    (function () {
        var sample = @json($sample);
        var lengthTpl = @json(__('settings.sms_length'));
        function segments(text) {
            var unicode = /[^\x00-\x7F]/.test(text), len = text.length;
            if (!len) return 0;
            var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
            return len <= single ? 1 : Math.ceil(len / multi);
        }
        function render(area) {
            var text = area.value.trim() || area.dataset.default || '';
            var out = text.replace(/\{(\w+)\}/g, function (m, k) { return sample[k] !== undefined ? sample[k] : m; });
            var box = area.closest('.rounded-xl');
            box.querySelector('[data-sms-preview]').textContent = out;
            box.querySelector('[data-sms-length]').textContent = lengthTpl.replace(':chars', out.length).replace(':segments', segments(out));
        }
        document.querySelectorAll('[data-sms-input]').forEach(function (area) {
            area.addEventListener('input', function () { render(area); });
            render(area);
        });
        document.querySelectorAll('[data-insert]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var area = document.getElementById(btn.dataset.target);
                var s = area.selectionStart || area.value.length, e = area.selectionEnd || s;
                area.value = area.value.slice(0, s) + btn.dataset.insert + area.value.slice(e);
                area.focus(); area.selectionStart = area.selectionEnd = s + btn.dataset.insert.length;
                render(area);
            });
        });
    })();
</script>
@endonce
