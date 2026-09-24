<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($header) ? strip_tags($header) : __('messages.welcome') }} - DueTap</title>

        @include('partials.pwa-head')

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="font-sans antialiased glass-bg {{ app()->getLocale() === 'bn' ? 'font-bengali' : '' }}">
        <div class="min-h-screen">
            <!-- Sidebar -->
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 glass-sidebar transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
                <div class="flex flex-col h-full">
                    <!-- Logo -->
                    <div class="flex items-center gap-3 h-16 px-5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/40">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7l1.5-3h13L20 7M4 7h16M4 7v11a2 2 0 002 2h12a2 2 0 002-2V7M9 11a3 3 0 006 0"/>
                            </svg>
                        </span>
                        <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-tight brand-mark">DueTap</a>
                    </div>

                    <!-- Market Info -->
                    @if(auth()->user()->market)
                    <div class="mx-4 mb-2 rounded-xl border border-white/60 bg-white/40 px-4 py-3">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->market->getLocalizedName() }}</p>
                        <p class="text-xs text-indigo-600 truncate">{{ auth()->user()->getLocalizedName() }}</p>
                    </div>
                    @endif

                    <!-- Navigation -->
                    <nav class="flex-1 px-4 py-3 space-y-1 overflow-y-auto">
                        @include('layouts.partials.sidebar-navigation')
                    </nav>

                    <!-- Language Toggle -->
                    <div class="px-4 py-4 border-t border-white/60">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-slate-500">{{ __('Language') }}</span>
                            <div class="flex rounded-xl border border-white/70 bg-white/50 p-0.5">
                                @foreach(['en' => 'EN', 'bn' => 'বাং'] as $code => $label)
                                <a href="{{ route('locale.switch', $code) }}"
                                   class="px-3 py-1 text-xs font-medium rounded-lg transition {{ app()->getLocale() === $code ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow' : 'text-slate-600 hover:bg-white/80' }}">
                                    {{ $label }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile sidebar backdrop -->
            <div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden hidden"></div>

            <!-- Main Content -->
            <div class="lg:pl-64">
                <!-- Top Navigation -->
                <header class="sticky top-0 z-30 glass-header">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Mobile menu button -->
                            <button id="sidebar-toggle" class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-white/70 hover:text-slate-700 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                            </button>

                            <!-- Page Title -->
                            @isset($header)
                            <h1 class="text-lg sm:text-xl font-semibold text-slate-800 truncate">
                                {{ $header }}
                            </h1>
                            @endisset
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 rounded-xl px-2 py-1.5 text-slate-700 hover:bg-white/70 transition">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-indigo-600 to-violet-600 text-sm font-semibold text-white shadow">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden sm:inline-block text-sm font-medium">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute right-0 mt-2 w-48 glass-card py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-white/70">
                                    {{ __('Profile') }}
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-slate-700 hover:bg-white/70">
                                        {{ __('messages.logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Flash Messages -->
                @foreach(['success' => ['emerald', 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z'], 'error' => ['rose', 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z']] as $key => [$color, $icon])
                @if(session($key))
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                    <div class="glass-card flex items-center gap-3 border-l-4 border-l-{{ $color }}-500 px-4 py-3">
                        <svg class="h-5 w-5 flex-shrink-0 text-{{ $color }}-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="{{ $icon }}" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-sm text-{{ $color }}-800">{{ session($key) }}</p>
                    </div>
                </div>
                @endif
                @endforeach

                @if($errors->any())
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                    <div class="glass-card border-l-4 border-l-rose-500 px-4 py-3">
                        <p class="text-sm font-medium text-rose-800">{{ __('messages.error') }}</p>
                        <ul class="mt-1 list-disc pl-5 text-sm text-rose-700">
                            @foreach($errors->all() as $message)
                            <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Page Content -->
                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @livewireScripts

        <script>
            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');

            sidebarToggle?.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                sidebarBackdrop.classList.toggle('hidden');
            });

            sidebarBackdrop?.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                sidebarBackdrop.classList.add('hidden');
            });
        </script>

        @stack('scripts')
        @include('partials.pwa-install')
    </body>
</html>
