<!DOCTYPE html>
<html lang="id">
<head>
    {{-- Dark Mode inline script — cegah FOUC --}}
    <script>if(localStorage.getItem('darkMode')==='true')document.documentElement.classList.add('dark')</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Informasi Aset') - Manajemen Aset</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&family=Google+Sans:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body class="bg-[#F5F7FA]">

    {{-- APP HEADER (full width, topmost) --}}
    <header class="app-header">
        <div class="app-header-inner">
            {{-- Left: Brand --}}
            <div class="flex items-center gap-3">
                <div class="app-header-brand-icon">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <div class="app-header-title">Sistem Informasi Aset</div>
                    <div class="app-header-subtitle">Manajemen Aset</div>
                </div>
            </div>

            {{-- Right: Module label + actions --}}
            <div class="flex items-center gap-6">
                <div class="hidden sm:block">
                    <div class="app-header-module-label">Aplikasi</div>
                    <div class="app-header-module-title">Manajemen Aset</div>
                </div>

                <div class="flex items-center gap-4 pl-6 border-l border-gray-200">
                    {{-- Notifications --}}
                    <button class="flex items-center justify-center w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors" title="Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>

                    {{-- Dark Mode Toggle --}}
                    <button id="darkModeToggle" class="flex items-center justify-center w-5 h-5 text-gray-400 hover:text-gray-600 transition-colors" title="Mode Gelap">
                        <svg id="sunIcon" class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                        </svg>
                        <svg id="moonIcon" class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                        </svg>
                    </button>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                        @csrf
                        <button type="submit" class="flex items-center justify-center w-5 h-5 text-gray-400 hover:text-danger-600 transition-colors" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- LAYOUT WRAPPER --}}
    <div class="flex min-h-screen pt-[70px]">

        {{-- SIDEBAR --}}
        @include('components.sidebar')

        {{-- RIGHT CONTENT --}}
        <div class="flex-1 flex flex-col ml-64">

            {{-- CONTENT TOPBAR: breadcrumb + live clock --}}
            <div class="content-topbar">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span>@yield('title', 'Sistem Informasi Aset')</span>
                </div>
                <div id="liveClock" class="content-topbar-clock"></div>
            </div>

            {{-- MAIN CONTENT --}}
            <main class="flex-1 overflow-y-auto scrollbar-thin">
                <div class="px-8">
                    <x-flash-message />
                </div>
                @yield('content')
            </main>

        </div>
    </div>

    @stack('scripts')

    {{-- Live Clock --}}
    <script>
        (function() {
            const clockEl = document.getElementById('liveClock');
            if (!clockEl) return;
            function tick() {
                clockEl.textContent = new Date().toLocaleTimeString('id-ID', { hour12: false });
            }
            tick();
            setInterval(tick, 1000);
        })();
    </script>

    {{-- Dark Mode Toggle --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cek localStorage untuk preferensi dark mode
            const html = document.documentElement;
            const toggle = document.getElementById('darkModeToggle');

            function applyTheme(isDark) {
                if (isDark) {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
                localStorage.setItem('darkMode', isDark ? 'true' : 'false');
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { isDark } }));
            }

            // Apply saved preference
            const saved = localStorage.getItem('darkMode');
            if (saved === 'true') {
                applyTheme(true);
            } else if (saved === null) {
                // Default: light mode
                applyTheme(false);
            }

            // Toggle on click
            if (toggle) {
                toggle.addEventListener('click', function() {
                    const isDark = !html.classList.contains('dark');
                    applyTheme(isDark);
                });
            }
        });
    </script>
</body>
</html>
