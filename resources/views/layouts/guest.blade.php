<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'DueTap') }}</title>

        @include('partials.pwa-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased glass-bg">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10">
            <a href="/" class="mb-8 flex flex-col items-center gap-3">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/40">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7l1.5-3h13L20 7M4 7h16M4 7v11a2 2 0 002 2h12a2 2 0 002-2V7M9 11a3 3 0 006 0"/>
                    </svg>
                </span>
                <span class="text-3xl font-bold tracking-tight brand-mark">DueTap</span>
                <span class="text-sm text-slate-500">{{ __('messages.tagline') }}</span>
            </a>

            <div class="w-full sm:max-w-md glass-card px-8 py-8">
                {{ $slot }}
            </div>

            <p class="mt-8 text-xs text-slate-500">&copy; {{ date('Y') }} DueTap &middot; duetap.com</p>
        </div>
    </body>
</html>
