@php
    $isBn = app()->getLocale() === 'bn';
    $bnDigits = fn ($v) => $isBn ? str_replace(['0','1','2','3','4','5','6','7','8','9'], ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], (string) $v) : (string) $v;
    $displayPhone = $bnDigits($phone);
    $telHref = 'tel:+88' . preg_replace('/\D/', '', $phone);
    $otherLocale = $isBn ? 'en' : 'bn';
    $headingCls = $isBn ? 'leading-snug' : 'tracking-tight';
    $waIcon = '<svg class="h-5 w-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>';
    $phoneIcon = '<svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>';
    $featureIcons = [
        'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
        'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
        'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
        'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
        'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129',
    ];
    $whoIcons = [
        'M3 3h18v4H3zM5 7v13h14V7M9 11h6',
        'M4 21V7l8-4 8 4v14M9 21v-6h6v6M9 9h1m4 0h1m-6 4h1m4 0h1',
        'M3 21h18M5 21V5a2 2 0 012-2h10a2 2 0 012 2v16M9 7h1m4 0h1M9 11h1m4 0h1M9 15h1m4 0h1',
        'M12 3l9 6-9 6-9-6 9-6zm-9 9l9 6 9-6M3 17l9 6 9-6',
    ];
    $previewShops = [
        ['no' => '১২', 'name' => $isBn ? 'রহিম স্টোর' : 'Rahim Store', 'amt' => '৮,৫০০', 's' => 'paid'],
        ['no' => '১৩', 'name' => $isBn ? 'নিউ ফ্যাশন' : 'New Fashion', 'amt' => '৬,০০০', 's' => 'partial'],
        ['no' => '২৭', 'name' => $isBn ? 'মদিনা ফার্মেসি' : 'Madina Pharmacy', 'amt' => '৮,৫০০', 's' => 'paid'],
        ['no' => '৩১', 'name' => $isBn ? 'করিম ইলেকট্রনিক্স' : 'Karim Electronics', 'amt' => '৭,২০০', 's' => 'due'],
    ];
    $statusCls = ['paid' => 'bg-emerald-100 text-emerald-700', 'partial' => 'bg-amber-100 text-amber-700', 'due' => 'bg-rose-100 text-rose-700'];
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
    <meta property="og:image" content="{{ asset('brand/og-image-1200x630.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="canonical" href="{{ url('/') }}">
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        [id] { scroll-margin-top: 5rem; }
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        details[open] .faq-icon { transform: rotate(45deg); }
    </style>
</head>
<body class="font-sans antialiased glass-bg text-slate-800 {{ $isBn ? 'font-bengali' : '' }}">

    {{-- ============ Header ============ --}}
    <header class="sticky top-0 z-40 glass-header">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2" aria-label="DueTap">
                <img src="{{ asset('icons/icon-192.png') }}" alt="" class="h-9 w-9 rounded-xl shadow-lg shadow-indigo-500/30">
                <span class="text-xl font-extrabold tracking-tight brand-mark font-sans">DueTap</span>
            </a>
            <nav class="hidden lg:flex items-center gap-6 text-sm font-medium text-slate-600" aria-label="Primary">
                <a href="#why" class="hover:text-indigo-600">{{ __('landing.nav_why') }}</a>
                <a href="#workflow" class="hover:text-indigo-600">{{ __('landing.nav_workflow') }}</a>
                <a href="#features" class="hover:text-indigo-600">{{ __('landing.nav_features') }}</a>
                <a href="#pricing" class="hover:text-indigo-600">{{ __('landing.nav_pricing') }}</a>
                <a href="#faq" class="hover:text-indigo-600">{{ __('landing.nav_faq') }}</a>
                <a href="#contact" class="hover:text-indigo-600">{{ __('landing.nav_contact') }}</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="{{ route('locale.switch', $otherLocale) }}" class="px-3 py-1.5 text-sm rounded-lg border border-white/70 bg-white/60 hover:bg-white font-sans">{{ __('landing.lang_switch') }}</a>
                <a href="{{ route('login') }}" class="hidden sm:inline-flex px-3 py-1.5 text-sm font-medium text-slate-700 hover:text-indigo-600">{{ __('landing.nav_login') }}</a>
                <a href="{{ route('register') }}" class="hidden sm:inline-flex btn-primary px-4 py-2 text-sm">{{ __('landing.nav_trial') }}</a>
                <button type="button" id="menu-btn" class="lg:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg border border-white/70 bg-white/60" aria-label="{{ __('landing.nav_menu') }}" aria-expanded="false" aria-controls="mobile-menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden lg:hidden border-t border-white/60 bg-white/80 backdrop-blur-xl">
            <nav class="mx-auto max-w-6xl px-4 py-3 grid gap-1 text-sm font-medium text-slate-700" aria-label="Mobile">
                @foreach(['why' => 'nav_why', 'workflow' => 'nav_workflow', 'features' => 'nav_features', 'pricing' => 'nav_pricing', 'faq' => 'nav_faq', 'contact' => 'nav_contact'] as $id => $key)
                    <a href="#{{ $id }}" class="px-3 py-2 rounded-lg hover:bg-white">{{ __('landing.' . $key) }}</a>
                @endforeach
                <div class="flex gap-2 pt-2">
                    <a href="{{ route('login') }}" class="flex-1 btn-secondary px-3 py-2 text-sm">{{ __('landing.nav_login') }}</a>
                    <a href="{{ route('register') }}" class="flex-1 btn-primary px-3 py-2 text-sm">{{ __('landing.nav_trial') }}</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
    {{-- ============ Hero ============ --}}
    <section class="mx-auto max-w-6xl px-4 sm:px-6 pt-12 pb-10 sm:pt-20 sm:pb-14">
        <div class="grid lg:grid-cols-12 gap-10 lg:gap-8 items-center">
            <div class="lg:col-span-6">
                <p class="inline-flex items-center gap-2 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-100">{{ __('landing.hero_kicker') }}</p>
                <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold text-slate-900 {{ $headingCls }}">{!! __('landing.hero_title') !!}</h1>
                <p class="mt-5 text-lg text-slate-600 leading-relaxed">{{ __('landing.hero_sub') }}</p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3">
                    <a href="{{ $telHref }}" class="btn-primary px-5 py-3 text-base">{!! $phoneIcon !!}<span>{{ __('landing.hero_call') }} {{ $displayPhone }}</span></a>
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-500/30 hover:bg-[#1ebe5d] transition">{!! $waIcon !!}<span>{{ __('landing.hero_whatsapp') }}</span></a>
                </div>
                <p class="mt-4 text-sm text-slate-500">
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:underline">{{ __('landing.hero_trial') }} &rarr;</a>
                    <span class="ml-2">{{ __('landing.hero_note') }}</span>
                </p>
            </div>

            {{-- Product preview --}}
            <div class="lg:col-span-6 relative">
                <div class="absolute -inset-4 rounded-3xl bg-gradient-to-br from-indigo-400/20 via-violet-400/10 to-sky-400/20 blur-2xl" aria-hidden="true"></div>
                <div class="relative glass-card p-4 sm:p-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('icons/icon-192.png') }}" alt="" class="h-8 w-8 rounded-lg">
                            <div>
                                <p class="text-sm font-bold text-slate-900">{{ __('landing.preview_market') }}</p>
                                <p class="text-xs text-slate-500">{{ __('landing.preview_month') }} · {{ $bnDigits('184') }} {{ __('landing.preview_shops') }}</p>
                            </div>
                        </div>
                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-100" aria-hidden="true"></span>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-emerald-50 p-3">
                            <p class="text-xs text-emerald-700">{{ __('landing.preview_collected') }}</p>
                            <p class="text-xl font-extrabold text-emerald-800">৳{{ $bnDigits('9,42,500') }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 p-3">
                            <p class="text-xs text-rose-700">{{ __('landing.preview_due') }}</p>
                            <p class="text-xl font-extrabold text-rose-800">৳{{ $bnDigits('1,36,200') }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('landing.preview_recent') }}</p>
                    <ul class="mt-2 divide-y divide-slate-100">
                        @foreach($previewShops as $row)
                        <li class="flex items-center justify-between py-2">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-xs font-bold text-indigo-700">{{ $row['no'] }}</span>
                                <span class="text-sm text-slate-800">{{ $row['name'] }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-slate-900">৳{{ $row['amt'] }}</span>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusCls[$row['s']] }}">{{ __('landing.status_' . $row['s']) }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    <div class="mt-3 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>{{ __('landing.preview_sms') }}</strong> — {{ __('landing.preview_sms_body') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Facts strip --}}
        <div class="mt-12 glass-card grid grid-cols-2 lg:grid-cols-4 divide-y lg:divide-y-0 divide-slate-200/70 lg:divide-x">
            @foreach(__('landing.facts') as $f)
            <div class="p-4 sm:p-5 text-center">
                <p class="text-lg sm:text-xl font-extrabold brand-mark">{{ $f['v'] }}</p>
                <p class="mt-1 text-xs sm:text-sm text-slate-600">{{ $f['l'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ============ Who ============ --}}
    <section class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold text-slate-900 {{ $headingCls }}">{{ __('landing.who_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.who_sub') }}</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(__('landing.who') as $i => $w)
            <div class="glass-card p-5">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/30">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $whoIcons[$i % 4] }}"/></svg>
                </span>
                <h3 class="mt-4 font-bold text-slate-900">{{ $w['t'] }}</h3>
                <p class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $w['d'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ============ Why ============ --}}
    <section id="why" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold text-slate-900 {{ $headingCls }}">{{ __('landing.why_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.why_sub') }}</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach(__('landing.why') as $item)
            <div class="glass-card p-5 sm:p-6 border-l-4 border-l-rose-400">
                <h3 class="font-bold text-slate-900">{{ $item['t'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $item['d'] }}</p>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ============ Workflow ============ --}}
    <section id="workflow" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold text-slate-900 {{ $headingCls }}">{{ __('landing.workflow_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.workflow_sub') }}</p>
        </div>
        <ol class="mt-10 relative grid md:grid-cols-5 gap-6 md:gap-4">
            <div class="hidden md:block absolute top-6 left-[10%] right-[10%] h-0.5 bg-gradient-to-r from-indigo-200 via-violet-300 to-indigo-200" aria-hidden="true"></div>
            @foreach(__('landing.workflow') as $n => $step)
            <li class="relative">
                <span class="relative z-10 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-lg font-extrabold text-white shadow-lg shadow-indigo-500/30 md:mx-auto">{{ $bnDigits($n + 1) }}</span>
                <h3 class="mt-4 font-bold text-slate-900 md:text-center">{{ $step['t'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed md:text-center">{{ $step['d'] }}</p>
            </li>
            @endforeach
        </ol>
    </section>

    {{-- ============ Features ============ --}}
    <section id="features" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold text-slate-900 {{ $headingCls }}">{{ __('landing.features_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.features_sub') }}</p>
        </div>
        <div class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            @foreach(__('landing.features') as $n => $f)
            <div class="glass-card p-5 sm:p-6 flex gap-4 {{ $n === 0 ? 'sm:col-span-2 lg:col-span-1 ring-2 ring-indigo-200' : '' }}">
                <span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $featureIcons[$n % count($featureIcons)] }}"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900">{{ $f['t'] }}</h3>
                    <p class="mt-1 text-sm text-slate-600 leading-relaxed">{{ $f['d'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ============ Pricing ============ --}}
    @if($plans->isNotEmpty())
    <section id="pricing" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-extrabold text-slate-900 {{ $headingCls }}">{{ __('landing.pricing_title') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('landing.pricing_sub') }}</p>
        </div>
        <div class="mt-10 grid md:grid-cols-3 gap-5">
            @foreach($plans as $i => $plan)
            @php
                $popular = $plans->count() >= 3 && $i === 1;
                $saveMonths = ($plan->yearly_price && $plan->monthly_price > 0) ? (int) round(12 - $plan->yearly_price / $plan->monthly_price) : 0;
            @endphp
            <div class="glass-card p-6 flex flex-col {{ $popular ? 'ring-2 ring-indigo-500 relative md:-mt-3 md:mb-3' : '' }}">
                @if($popular)<span class="absolute -top-3 left-6 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-1 text-xs font-bold text-white">{{ __('landing.popular') }}</span>@endif
                <h3 class="text-lg font-bold text-slate-900">{{ $plan->getLocalizedName() }}</h3>
                @if($plan->description)<p class="mt-1 text-sm text-slate-500">{{ $plan->description }}</p>@endif
                <p class="mt-5"><span class="text-4xl font-extrabold text-slate-900">৳{{ $bnDigits(number_format($plan->monthly_price)) }}</span> <span class="text-slate-500">{{ __('landing.per_month') }}</span></p>
                @if($plan->yearly_price)
                    <p class="text-sm text-slate-500">{{ __('landing.per_year', ['price' => $bnDigits(number_format($plan->yearly_price))]) }}@if($saveMonths > 0) <span class="ml-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">{{ __('landing.save_months', ['n' => $bnDigits($saveMonths)]) }}</span>@endif</p>
                @endif
                <ul class="mt-5 space-y-2.5 text-sm text-slate-700 flex-1">
                    <li class="flex gap-2"><svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ $plan->shop_limit ? __('landing.shops_upto', ['n' => $bnDigits($plan->shop_limit)]) : __('landing.shops_unlimited') }}</li>
                    <li class="flex gap-2"><svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ __('landing.sms_per_month', ['n' => $bnDigits(number_format($plan->sms_credits_per_month))]) }}</li>
                    @if($plan->trial_days)<li class="flex gap-2"><svg class="h-5 w-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ __('landing.trial_days', ['n' => $bnDigits($plan->trial_days)]) }}</li>@endif
                </ul>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="mt-6 {{ $popular ? 'btn-primary' : 'btn-secondary' }} px-4 py-2.5 text-sm">{{ __('landing.choose') }}</a>
            </div>
            @endforeach
        </div>
        <p class="mt-6 text-sm text-slate-600">{{ __('landing.pricing_included') }}</p>
        <p class="mt-1 text-sm text-slate-500">{{ __('landing.pricing_note') }}</p>
    </section>
    @endif

    {{-- ============ FAQ ============ --}}
    <section id="faq" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="grid lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4">
                <h2 class="text-3xl font-extrabold text-slate-900 {{ $headingCls }}">{{ __('landing.faq_title') }}</h2>
                <p class="mt-2 text-slate-600">{{ __('landing.faq_sub') }}</p>
            </div>
            <div class="lg:col-span-8 space-y-3">
                @foreach(__('landing.faq') as $item)
                <details class="glass-card group">
                    <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 font-semibold text-slate-900">
                        <span>{{ $item['q'] }}</span>
                        <span class="faq-icon flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 transition-transform" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </summary>
                    <p class="px-5 pb-5 text-sm text-slate-600 leading-relaxed">{{ $item['a'] }}</p>
                </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ Contact ============ --}}
    <section id="contact" class="mx-auto max-w-6xl px-4 sm:px-6 py-12 sm:py-16">
        <div class="rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 p-8 sm:p-12 text-center text-white shadow-2xl shadow-indigo-500/30">
            <h2 class="text-3xl font-extrabold {{ $headingCls }}">{{ __('landing.contact_title') }}</h2>
            <p class="mt-3 max-w-2xl mx-auto text-indigo-100">{{ __('landing.contact_sub') }}</p>
            <p class="mt-8 text-xs font-semibold uppercase tracking-wider text-indigo-200">{{ __('landing.contact_phone_label') }}</p>
            <a href="{{ $telHref }}" class="mt-1 inline-block text-4xl sm:text-5xl font-extrabold hover:underline font-sans">{{ $displayPhone }}</a>
            <p class="mt-1 text-sm text-indigo-200">{{ __('landing.contact_hours') }}</p>
            <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                <a href="{{ $telHref }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 font-semibold text-indigo-700 shadow hover:bg-indigo-50">{!! $phoneIcon !!}<span>{{ __('landing.hero_call') }}</span></a>
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#25D366] px-5 py-3 font-semibold text-white shadow hover:bg-[#1ebe5d]">{!! $waIcon !!}<span>{{ __('landing.hero_whatsapp') }}</span></a>
            </div>
            <p class="mt-6 text-sm text-indigo-200">{{ __('landing.contact_or') }} <a href="{{ route('register') }}" class="font-semibold text-white underline">{{ __('landing.hero_trial') }}</a></p>
        </div>
    </section>
    </main>

    {{-- ============ Footer ============ --}}
    <footer class="border-t border-white/60 bg-white/40 backdrop-blur">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-10 grid gap-8 sm:grid-cols-3">
            <div>
                <div class="flex items-center gap-2">
                    <img src="{{ asset('icons/icon-192.png') }}" alt="" class="h-8 w-8 rounded-lg">
                    <span class="text-lg font-extrabold brand-mark font-sans">DueTap</span>
                </div>
                <p class="mt-3 text-sm text-slate-600">{{ __('landing.footer_tagline') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('landing.footer_product') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    <li><a href="#features" class="hover:text-indigo-600">{{ __('landing.nav_features') }}</a></li>
                    <li><a href="#pricing" class="hover:text-indigo-600">{{ __('landing.nav_pricing') }}</a></li>
                    <li><a href="#faq" class="hover:text-indigo-600">{{ __('landing.nav_faq') }}</a></li>
                    <li><a href="{{ route('manual') }}" class="hover:text-indigo-600">{{ __('landing.nav_manual') }}</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-indigo-600">{{ __('landing.nav_login') }}</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-indigo-600">{{ __('landing.hero_trial') }}</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('landing.footer_contact') }}</p>
                <ul class="mt-3 space-y-2 text-sm text-slate-700">
                    <li><a href="{{ $telHref }}" class="hover:text-indigo-600 font-sans">{{ $displayPhone }}</a></li>
                    <li><a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="hover:text-indigo-600">{{ __('landing.wa_float') }}</a></li>
                    @if(config('services.support.email'))<li><a href="mailto:{{ config('services.support.email') }}" class="hover:text-indigo-600 font-sans">{{ config('services.support.email') }}</a></li>@endif
                    <li class="text-slate-500">{{ __('landing.contact_hours') }}</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/60">
            <p class="mx-auto max-w-6xl px-4 sm:px-6 py-4 text-xs text-slate-500">&copy; {{ $bnDigits(date('Y')) }} DueTap · duetap.com · {{ __('landing.footer_rights') }}</p>
        </div>
    </footer>

    {{-- Floating WhatsApp --}}
    <a href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="{{ __('landing.wa_float') }}"
       class="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-full bg-[#25D366] px-4 py-3 text-white font-semibold shadow-xl shadow-emerald-500/40 hover:bg-[#1ebe5d] transition">
        {!! str_replace('h-5 w-5', 'h-6 w-6', $waIcon) !!}
        <span class="hidden sm:inline">{{ __('landing.wa_float') }}</span>
    </a>

    <script>
        (function () {
            var btn = document.getElementById('menu-btn'), menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;
            btn.addEventListener('click', function () {
                var open = menu.classList.toggle('hidden') === false;
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            menu.querySelectorAll('a[href^="#"]').forEach(function (a) { a.addEventListener('click', function () { menu.classList.add('hidden'); btn.setAttribute('aria-expanded', 'false'); }); });
        })();
    </script>
</body>
</html>
