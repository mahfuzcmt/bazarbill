<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'bn' ? 'ltr' : 'ltr' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('messages.welcome') }} - BazarBill</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|hind-siliguri:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles

        <style>
            [lang="bn"] {
                font-family: 'Hind Siliguri', sans-serif;
            }
        </style>
    </head>
    <body class="font-sans antialiased {{ app()->getLocale() === 'bn' ? 'font-bengali' : '' }}">
        <div class="min-h-screen bg-gray-100">
            <!-- Sidebar -->
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
                <div class="flex flex-col h-full">
                    <!-- Logo -->
                    <div class="flex items-center justify-center h-16 bg-indigo-600">
                        <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-white">
                            BazarBill
                        </a>
                    </div>

                    <!-- Market Info -->
                    @if(auth()->user()->market)
                    <div class="px-4 py-3 bg-indigo-50 border-b">
                        <p class="text-sm font-medium text-indigo-900">{{ auth()->user()->market->getLocalizedName() }}</p>
                        <p class="text-xs text-indigo-600">{{ auth()->user()->getLocalizedName() }}</p>
                    </div>
                    @endif

                    <!-- Navigation -->
                    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                        @include('layouts.partials.sidebar-navigation')
                    </nav>

                    <!-- Language Toggle -->
                    <div class="px-4 py-3 border-t bg-gray-50">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">{{ __('Language') }}</span>
                            <div class="flex space-x-2">
                                <a href="{{ route('locale.switch', 'en') }}"
                                   class="px-2 py-1 text-xs rounded {{ app()->getLocale() === 'en' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    EN
                                </a>
                                <a href="{{ route('locale.switch', 'bn') }}"
                                   class="px-2 py-1 text-xs rounded {{ app()->getLocale() === 'bn' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    বাং
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Mobile sidebar backdrop -->
            <div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden hidden"></div>

            <!-- Main Content -->
            <div class="lg:pl-64">
                <!-- Top Navigation -->
                <header class="sticky top-0 z-30 bg-white shadow-sm">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                        <!-- Mobile menu button -->
                        <button id="sidebar-toggle" class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-600 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        <!-- Page Title -->
                        @isset($header)
                        <h1 class="text-xl font-semibold text-gray-900">
                            {{ $header }}
                        </h1>
                        @endisset

                        <!-- Right side -->
                        <div class="flex items-center space-x-4">
                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="hidden sm:inline-block text-sm">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-cloak
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ __('Profile') }}
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('messages.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                    <div class="bg-green-50 border border-green-200 rounded-md p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                    <div class="bg-red-50 border border-red-200 rounded-md p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                        </div>
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
    </body>
</html>
