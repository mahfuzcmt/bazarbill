@php
    $bnDigits = fn ($v) => app()->getLocale() === 'bn' ? str_replace(['0','1','2','3','4','5','6','7','8','9'], ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], (string) $v) : (string) $v;
    $displayPhone = $bnDigits($phone);
    $telHref = 'tel:+88' . preg_replace('/\D/', '', $phone);
    $otherLocale = app()->getLocale() === 'bn' ? 'en' : 'bn';
    $icons = [
        'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z',
        'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
        'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
        'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.meta_title') }}</title>
    <meta name="description" content="{{ __('landing.meta_description') }}">
    <meta property="og:title" content="{{ __('landing.meta_title') }}">
    <meta property="og:description" content="{{ __('landing.meta_description') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>html { scroll-behavior: smooth; }</style>
</head>
<body class="font-sans antialiased glass-bg text-slate-800">

    <!-- Top bar -->
    <header class="sticky top-0 z-40 glass-topbar">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7l1.5-3h13L20 7M4 7h16M4 7v11a2 2 0 002 2h12a2 2 0 002-2V7M9 11a3 3 0 006 0"/></svg>
                </span>
                <span class="text-xl font-extrabold tracking-tight brand-mark">DueTap</span>
            </a>
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="#why" class="hover:text-indigo-600">{{ __('landing.why_title') }}</a>
                <a href="#workflow" class="hover:text-indigo-600">{{ __('landing.nav_workflow') }}</a>
                <a href="#features" class="hover:text-indigo-600">{{ __('landing.nav_features') }}</a>
                <a href="#pricing" class="hover:text-indigo-600">{{ __('landing.nav_pricing') }}</a>
                <a href="#contact" class="hover:text-indigo-600">{{ __('landing.nav_contact') }}</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('locale.switch', $otherLocale) }}" class="px-3 py-1.5 text-sm rounded-lg border border-white/70 bg-white/60 hover:bg-white">{{ __('landing.lang_switch') }}</a>
                <a href="{{ route('login') }}" class="hidden sm:inline-flex px-3 py-1.5 text-sm font-medium text-slate-700 hover:text-indigo-600">{{ __('landing.nav_login') }}</a>
                <a href="{{ route('register') }}" class="btn-primary px-4 py-2 text-sm">{{ __('landing.nav_trial') }}</a>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="mx-auto max-w-6xl px-4 sm:px-6 pt-14 pb-10 sm:pt-20 sm:pb-16">
        <div class="grid lg:grid-cols-2 gap-10 items-center">
            <div>
                <p class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">{{ __('landing.hero_kicker') }}</p>
                <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold leading-tight tracking-tight text-slate-900">{!! __('landing.hero_title') !!}</h1>
                <p class="mt-5 text-lg text-slate-600 leading-relaxed">{{ __('landing.hero_sub') }}</p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ $telHref }}" class="btn-primary px-5 py-3 text-base">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        {{ __('landing.hero_call') }} {{ $displayPhone }}
                    </a>
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 hover:bg-[#1ebe5d] transition">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        {{ __('landing.hero_whatsapp') }}
                    </a>
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:underline">{{ __('landing.hero_trial') }} &rarr;</a>
                    <span>{{ __('landing.hero_note') }}</span>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 sm:gap-4">
                @foreach([1,2,3] as $i)
                <div class="glass-card p-4 sm:p-6 text-center">
                    <p class="text-2xl sm:text-3xl font-extrabold brand-mark">{{ __('landing.stat_' . $i . '_value') }}</p>
                    <p class="mt-1 text-xs sm:text-sm text-slate-600">{{ __('landing.stat_' . $i . '_label') }}</p>
                </div>
                @endforeach
                <div class="col-span-3 glass-card p-5 sm:p-6">
                    <ol class="space-y-2 text-sm text-slate-700">
                        @foreach(__('landing.workflow') as $n => $step)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 text-xs font-bold text-white">{{ $bnDigits($n + 1) }}</span>
                            <span class="font-medium">{{ $step['t'] }}</span>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Why -->
    <section id="why" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('landing.why_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.why_sub') }}</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach(__('landing.why') as $item)
            <div class="glass-card p-5 sm:p-6">
                <h3 class="font-bold text-slate-900">{{ $item['t'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $item['d'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Workflow -->
    <section id="workflow" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('landing.workflow_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.workflow_sub') }}</p>
        </div>
        <div class="mt-8 grid md:grid-cols-5 gap-4">
            @foreach(__('landing.workflow') as $n => $step)
            <div class="glass-card p-5 relative">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-lg font-extrabold text-white shadow-lg shadow-indigo-500/30">{{ $bnDigits($n + 1) }}</span>
                <h3 class="mt-4 font-bold text-slate-900">{{ $step['t'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $step['d'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('landing.features_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.features_sub') }}</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach(__('landing.features') as $n => $f)
            <div class="glass-card p-5 sm:p-6 flex gap-4">
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$n % count($icons)] }}"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900">{{ $f['t'] }}</h3>
                    <p class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $f['d'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Pricing -->
    @if($plans->isNotEmpty())
    <section id="pricing" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ __('landing.pricing_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.pricing_sub') }}</p>
        </div>
        <div class="mt-8 grid md:grid-cols-3 gap-4 sm:gap-5">
            @foreach($plans as $i => $plan)
            @php $popular = $plans->count() >= 3 && $i === 1; @endphp
            <div class="glass-card p-6 flex flex-col {{ $popular ? 'ring-2 ring-indigo-500 relative' : '' }}">
                @if($popular)<span class="absolute -top-3 left-6 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-1 text-xs font-bold text-white">{{ __('landing.popular') }}</span>@endif
                <h3 class="text-lg font-bold text-slate-900">{{ $plan->getLocalizedName() }}</h3>
                @if($plan->description)<p class="mt-1 text-sm text-slate-500">{{ $plan->description }}</p>@endif
                <p class="mt-4"><span class="text-4xl font-extrabold text-slate-900">৳{{ $bnDigits(number_format($plan->monthly_price)) }}</span> <span class="text-slate-500">{{ __('landing.per_month') }}</span></p>
                @if($plan->yearly_price)<p class="text-sm text-slate-500">{{ __('landing.per_year', ['price' => $bnDigits(number_format($plan->yearly_price))]) }}</p>@endif
                <ul class="mt-5 space-y-2 text-sm text-slate-700 flex-1">
                    <li class="flex gap-2"><span class="text-emerald-600">✓</span>{{ $plan->shop_limit ? __('landing.shops_upto', ['n' => $bnDigits($plan->shop_limit)]) : __('landing.shops_unlimited') }}</li>
                    <li class="flex gap-2"><span class="text-emerald-600">✓</span>{{ __('landing.sms_per_month', ['n' => $bnDigits(number_format($plan->sms_credits_per_month))]) }}</li>
                    @if($plan->trial_days)<li class="flex gap-2"><span class="text-emerald-600">✓</span>{{ __('landing.trial_days', ['n' => $bnDigits($plan->trial_days)]) }}</li>@endif
                </ul>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-6 {{ $popular ? 'btn-primary' : 'btn-secondary' }} px-4 py-2.5 text-sm">{{ __('landing.choose') }}</a>
            </div>
            @endforeach
        </div>
        <p class="mt-4 text-sm text-slate-500">{{ __('landing.pricing_note') }}</p>
    </section>
    @endif

    <!-- Contact -->
    <section id="contact" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="glass-card p-8 sm:p-12 text-center bg-gradient-to-br from-indigo-600/90 to-violet-600/90 text-white border-0">
            <h2 class="text-3xl font-extrabold tracking-tight">{{ __('landing.contact_title') }}</h2>
            <p class="mt-3 max-w-2xl mx-auto text-indigo-100">{{ __('landing.contact_sub') }}</p>
            <p class="mt-8 text-sm uppercase tracking-wide text-indigo-200">{{ __('landing.contact_phone_label') }}</p>
            <a href="{{ $telHref }}" class="mt-1 inline-block text-4xl sm:text-5xl font-extrabold tracking-tight hover:underline">{{ $displayPhone }}</a>
            <p class="mt-1 text-sm text-indigo-200">{{ __('landing.contact_hours') }}</p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                <a href="{{ $telHref }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 font-semibold text-indigo-700 shadow hover:bg-indigo-50">{{ __('landing.hero_call') }}</a>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 font-semibold text-white shadow hover:bg-[#1ebe5d]">{{ __('landing.hero_whatsapp') }}</a>
            </div>
            <p class="mt-6 text-sm text-indigo-200">{{ __('landing.contact_or') }} — <a href="{{ route('register') }}" class="font-semibold text-white underline">{{ __('landing.hero_trial') }}</a></p>
        </div>
    </section>

    <footer class="mx-auto max-w-6xl px-4 sm:px-6 py-10 text-sm text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-3">
        <div><span class="font-bold brand-mark">DueTap</span> · {{ __('landing.footer_tagline') }}</div>
        <div>&copy; {{ $bnDigits(date('Y')) }} DueTap · duetap.com · {{ __('landing.footer_rights') }}</div>
    </footer>

    <!-- Floating WhatsApp -->
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="{{ __('landing.wa_float') }}"
       class="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-full bg-[#25D366] px-4 py-3 text-white font-semibold shadow-xl shadow-emerald-500/40 hover:bg-[#1ebe5d] transition">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        <span class="hidden sm:inline">{{ __('landing.wa_float') }}</span>
    </a>
</body>
</html>
