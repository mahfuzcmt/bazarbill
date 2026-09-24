@php
    $toc = [
        'intro' => '১. ভূমিকা', 'start' => '২. শুরু করা', 'owner' => '৩. মার্কেট মালিকের গাইড', 'collector' => '৪. কালেক্টরের গাইড',
        'tenant' => '৫. দোকানদারের গাইড', 'admin' => '৬. প্ল্যাটফর্ম অ্যাডমিন', 'trouble' => '৭. সমস্যা ও সমাধান', 'contact' => '৮. সাপোর্ট',
    ];
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ডিউট্যাপ ব্যবহার সহায়িকা — DueTap User Guide</title>
    <meta name="description" content="ডিউট্যাপ (DueTap) মার্কেট ভাড়া আদায় সিস্টেমের সম্পূর্ণ বাংলা ব্যবহার সহায়িকা: মার্কেট মালিক, কালেক্টর ও দোকানদারের জন্য।">
    @include('partials.pwa-head')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:600,700,800|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; } [id] { scroll-margin-top: 5rem; }
        .manual h2 { font-size: 1.75rem; font-weight: 700; color: #0f172a; margin: 2.5rem 0 1rem; padding-bottom: .5rem; border-bottom: 2px solid #c7d2fe; }
        .manual h3 { font-size: 1.2rem; font-weight: 700; color: #312e81; margin: 1.75rem 0 .6rem; }
        .manual p { margin: .6rem 0; line-height: 1.75; color: #1e293b; }
        .manual ul, .manual ol { margin: .6rem 0 .6rem 1.4rem; line-height: 1.75; color: #1e293b; }
        .manual ul { list-style: disc; } .manual ol { list-style: decimal; } .manual li { margin: .3rem 0; }
        .manual table { width: 100%; border-collapse: collapse; margin: .9rem 0; font-size: .95rem; }
        .manual th, .manual td { border: 1px solid #e2e8f0; padding: .55rem .7rem; text-align: left; vertical-align: top; line-height: 1.6; }
        .manual th { background: #eef2ff; color: #312e81; font-weight: 600; }
        .manual code { background: #eef2ff; color: #4338ca; padding: .05rem .35rem; border-radius: .3rem; font-size: .9em; }
        .manual em { font-style: normal; color: #4338ca; }
        @media print { .no-print { display: none !important; } body { background: #fff !important; } .glass-card { box-shadow: none !important; border: 0 !important; background: #fff !important; } }
    </style>
</head>
<body class="font-sans font-bengali antialiased glass-bg text-slate-800">
    <header class="sticky top-0 z-40 glass-header no-print">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <img src="{{ asset('icons/icon-192.png') }}" alt="" class="h-9 w-9 rounded-xl">
                <span class="text-xl font-extrabold tracking-tight brand-mark font-sans">DueTap</span>
                <span class="hidden sm:inline text-sm text-slate-500 ml-2">ব্যবহার সহায়িকা</span>
            </a>
            <div class="flex items-center gap-2">
                <a href="{{ route('manual.pdf') }}" class="btn-primary px-3 py-1.5 text-sm">পিডিএফ ডাউনলোড</a>
                <button type="button" onclick="window.print()" class="hidden sm:inline-flex btn-secondary px-3 py-1.5 text-sm">প্রিন্ট</button>
                @auth
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 text-sm font-medium text-slate-700 hover:text-indigo-600">ড্যাশবোর্ড</a>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1.5 text-sm font-medium text-slate-700 hover:text-indigo-600">লগইন</a>
                @endauth
            </div>
        </div>
    </header>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 py-8 grid lg:grid-cols-12 gap-8">
        <aside class="lg:col-span-3 no-print">
            <nav class="glass-card p-4 lg:sticky lg:top-24" aria-label="সূচি">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">সূচিপত্র</p>
                <ul class="space-y-1 text-sm">
                    @foreach($toc as $id => $label)
                        <li><a href="#{{ $id }}" class="block rounded-lg px-2 py-1.5 text-slate-700 hover:bg-white hover:text-indigo-600">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </aside>
        <main class="lg:col-span-9">
            <div class="glass-card p-5 sm:p-8 manual">
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">DueTap · ডিউট্যাপ</p>
                <h1 class="mt-1 text-3xl sm:text-4xl font-extrabold text-slate-900 leading-snug">ব্যবহার সহায়িকা</h1>
                <p class="mt-2 text-slate-600">সংস্করণ {{ now()->format('Y.m') }} · মার্কেট মালিক, কালেক্টর ও দোকানদারের জন্য ধাপে ধাপে নির্দেশনা।</p>
                @include('manual._content')
            </div>
        </main>
    </div>
    <footer class="mx-auto max-w-6xl px-4 sm:px-6 py-8 text-xs text-slate-500 no-print">&copy; {{ date('Y') }} DueTap · duetap.com</footer>
</body>
</html>
