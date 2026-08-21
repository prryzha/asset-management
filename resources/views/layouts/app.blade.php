<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    {{-- Dark Mode inline script — cegah FOUC --}}
    <script>if(localStorage.getItem('darkMode')==='true')document.documentElement.classList.add('dark')</script>
    {{-- Sidebar state inline script — cegah blink sidebar tiap ganti halaman --}}
    <script>if(localStorage.getItem('sidebarOpen')==='false')document.documentElement.classList.add('sidebar-closed')</script>
    {{-- Topbar collapse state inline script — cegah blink topbar tiap ganti halaman --}}
    <script>if(localStorage.getItem('topbarCollapsed')==='true')document.documentElement.classList.add('topbar-collapsed')</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('ui.layout.system_title')) - {{ __('ui.layout.system_subtitle') }}</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    {{-- APP HEADER (top info bar + main header, full width, topmost) --}}
    <div class="app-header-group">
        <div class="app-topbar">
            <span class="app-topbar-tagline">{{ __('ui.layout.tagline') }}</span>

            <div class="app-topbar-actions">
                {{-- Notifications --}}
                @if($headerNotifications !== null)
                <div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false">
                    <button
                        type="button"
                        class="relative icon-btn"
                        title="{{ __('ui.layout.notifications') }}"
                        x-on:click="open = !open"
                    >
                        <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($headerNotifications['total'] > 0)
                        <span class="badge-count">
                            {{ $headerNotifications['total'] > 9 ? '9+' : $headerNotifications['total'] }}
                        </span>
                        @endif
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition
                        class="card dropdown-panel dropdown-panel-scroll w-80"
                    >
                        <div class="card-header">
                            <h3>{{ __('ui.layout.notifications') }}</h3>
                        </div>
                        <div class="card-body space-y-4">
                            @if($headerNotifications['total'] === 0)
                                <p class="text-xs text-secondary text-center py-2">{{ __('ui.layout.no_new_notifications') }}</p>
                            @endif

                            @foreach($headerNotifications['sections'] as $section)
                            <div>
                                <p class="dropdown-section-title">{{ $section['title'] }} ({{ $section['count'] }})</p>
                                <div class="space-y-2">
                                    @foreach($section['items'] as $item)
                                    @if($item['url'])
                                    <a href="{{ $item['url'] }}" class="dropdown-list-item">
                                        <span class="item-label">{{ $item['label'] }}</span>
                                        @if($item['sublabel'])
                                        <span class="item-sublabel">{{ $item['sublabel'] }}</span>
                                        @endif
                                    </a>
                                    @else
                                    <div class="dropdown-list-item">
                                        <span class="item-label">{{ $item['label'] }}</span>
                                        @if($item['sublabel'])
                                        <span class="item-sublabel">{{ $item['sublabel'] }}</span>
                                        @endif
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                                @if($section['moreUrl'])
                                <a href="{{ $section['moreUrl'] }}" class="text-xs font-normal text-primary-600 hover:text-primary-700">{{ __('ui.layout.see_all') }}</a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                {{-- Dark Mode Toggle --}}
                <button id="darkModeToggle" class="icon-btn" title="{{ __('ui.layout.dark_mode') }}">
                    <svg id="sunIcon" class="icon-lg dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                    </svg>
                    <svg id="moonIcon" class="icon-lg hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                    </svg>
                </button>

                {{-- Language Selector — real locale switch lewat
                     route('language.switch'), locale disimpan di session
                     (SetLocale middleware). Active/inactive state sekarang
                     dari app()->getLocale() (server), bukan Alpine state
                     lokal lagi. --}}
                <div class="flex items-center gap-1">
                    <a href="{{ route('language.switch', 'id') }}" class="icon-btn {{ app()->getLocale() !== 'id' ? 'lang-flag-inactive' : '' }}" title="{{ __('ui.layout.language_indonesia') }}">
                        <span class="lang-flag-icon" aria-hidden="true">
                            <svg viewBox="0 0 20 15" xmlns="http://www.w3.org/2000/svg">
                                <rect width="20" height="7.5" fill="var(--color-danger)"/>
                                <rect y="7.5" width="20" height="7.5" fill="var(--color-text-on-primary)"/>
                            </svg>
                        </span>
                    </a>
                    <a href="{{ route('language.switch', 'en') }}" class="icon-btn {{ app()->getLocale() !== 'en' ? 'lang-flag-inactive' : '' }}" title="{{ __('ui.layout.language_english') }}">
                        <span class="lang-flag-icon" aria-hidden="true">
                            {{-- UK flag (simplified Union Jack) --}}
                            <svg viewBox="0 0 20 15" xmlns="http://www.w3.org/2000/svg">
                                <rect width="20" height="15" fill="#00247D"/>
                                <path d="M0 0L20 15M20 0L0 15" stroke="#FFFFFF" stroke-width="2.6"/>
                                <path d="M0 0L20 15M20 0L0 15" stroke="#CF142B" stroke-width="1.3"/>
                                <path d="M10 0V15M0 7.5H20" stroke="#FFFFFF" stroke-width="4.3"/>
                                <path d="M10 0V15M0 7.5H20" stroke="#CF142B" stroke-width="2.6"/>
                            </svg>
                        </span>
                    </a>
                </div>

                {{-- Developer Profile — static UI data (no backend/database),
                     separate from the admin user menu in the sidebar. Trigger
                     reuses the plain .icon-btn + .icon-lg pattern (same as
                     notification/dark-mode) instead of a bespoke avatar size;
                     the colored-circle avatar only appears inside the opened
                     panel, reusing the existing .header-user-avatar style
                     already used for the sidebar admin menu. --}}
                <div class="relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false">
                    <button type="button" class="icon-btn" title="{{ __('ui.layout.developer_profile') }}" x-on:click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()">
                        <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </button>

                    <div
                        x-show="open"
                        x-cloak
                        x-transition
                        class="card dropdown-panel w-72"
                    >
                        <div class="card-header dev-profile-header">
                            <div class="flex items-center gap-3">
                                <div class="header-user-avatar">P</div>
                                <div class="text-left leading-tight min-w-0">
                                    <p class="header-user-name ui-title">Pryzha Aulia Susanto</p>
                                    <p class="header-user-role">{{ __('ui.layout.developer_role') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body dev-profile-body">
                            <a href="mailto:pryzhaaulias2009@gmail.com" class="dropdown-item dev-profile-item">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                pryzhaaulias2009@gmail.com
                            </a>
                            <a href="tel:08996985234" class="dropdown-item dev-profile-item">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                0899-6985-234
                            </a>
                            <a href="https://github.com/prryzha" target="_blank" rel="noopener" class="dropdown-item dev-profile-item">
                                {{-- GitHub logo (brand mark, filled — not the generic stroke icon set) --}}
                                <svg class="icon" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                                github.com/prryzha
                            </a>
                            <a href="https://instagram.com/prryzha" target="_blank" rel="noopener" class="dropdown-item dev-profile-item">
                                {{-- Instagram logo (rounded square + lens + flash dot) --}}
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="2" y="2" width="20" height="20" rx="5" stroke-width="2"/>
                                    <path stroke-width="2" d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/>
                                    <path stroke-width="2" stroke-linecap="round" d="M17.5 6.5h.01"/>
                                </svg>
                                @prryzha
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Logout — direct action, same route/CSRF as the sidebar's
                     existing logout button, just also reachable straight
                     from the topbar next to the developer profile icon. --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="icon-btn" title="{{ __('ui.nav.keluar') }}">
                        <svg class="icon-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>

                {{-- Collapse the topbar. Bringing it back happens via
                     the hover peek zone below, not this button. --}}
                <button type="button" id="topbarToggle" class="icon-btn" title="{{ __('ui.layout.hide_info_bar') }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Decorative accent line, plain CSS — no image --}}
        <div class="app-header-accent" aria-hidden="true">
            <div class="app-header-accent-stripe app-header-accent-stripe--red"></div>
            <div class="app-header-accent-stripe app-header-accent-stripe--white"></div>
        </div>

        {{-- Hover peek zone: only relevant while the topbar is collapsed,
             invisible until the cursor is actually in this corner. --}}
        <div class="app-topbar-peek">
            <button type="button" id="topbarExpandBtn" class="icon-btn" title="{{ __('ui.layout.show_info_bar') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <header class="app-header">
            <div class="app-header-main">
                {{-- Branding instansi (kiri) — hanya tampil kalau Profil
                     Instansi sudah diisi Admin (lihat Pengaturan > Profil
                     Instansi). Div ini tetap dirender kosong kalau belum
                     diisi supaya .app-header-main (space-between) tetap
                     punya 2 flex child, sehingga identitas sistem di kanan
                     tetap menempel ke kanan persis seperti sebelumnya. --}}
                <div class="app-header-institution">
                    @if($institutionProfile && $institutionProfile->nama_instansi)
                        @if($institutionProfile->logo)
                            <img src="{{ asset('storage/'.$institutionProfile->logo) }}" alt="{{ $institutionProfile->nama_instansi }}" class="app-header-institution-logo">
                        @else
                            <div class="app-header-institution-logo app-header-institution-logo-fallback">
                                {{ substr($institutionProfile->nama_singkat ?: $institutionProfile->nama_instansi, 0, 1) }}
                            </div>
                        @endif
                        <div class="text-left leading-tight min-w-0">
                            <div class="app-header-title ui-title truncate">{{ $institutionProfile->nama_instansi }}</div>
                            @if($institutionProfile->nama_singkat)
                                <div class="app-header-institution-subtitle hidden sm:block truncate">{{ $institutionProfile->nama_singkat }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- App identity (kanan) — dibungkus satu wrapper supaya
                     .app-header-main (space-between) cuma punya 2 flex child
                     (institusi kiri, grup ini kanan), bukan 3 — kalau tidak,
                     space-between akan menyebar text+icon ke 2 ujung yang
                     berbeda alih-alih tetap sebagai satu grup di kanan. --}}
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <div class="app-header-title ui-title">{{ __('ui.layout.system_title') }}</div>
                        <div class="app-header-subtitle hidden sm:block">{{ __('ui.layout.system_subtitle') }}</div>
                    </div>
                    <div class="app-header-brand-icon">
                        <svg class="icon text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>
        </header>
    </div>

    {{-- LAYOUT WRAPPER --}}
    <div class="app-content-wrapper">

        {{-- SIDEBAR --}}
        @include('components.sidebar')

        {{-- RIGHT CONTENT --}}
        <div id="mainContentWrapper" class="flex-1 flex flex-col min-w-0">

            @php
                // Breadcrumb trail derived from the current route, mirroring the
                // sidebar's own grouping — kept in the layout so every page gets a
                // consistent trail without each view needing to declare its own.
                $breadcrumbMap = [
                    ['pattern' => 'assets.hilang*', 'group' => __('ui.nav.laporan'), 'label' => __('ui.nav.laporan_aset')],
                    ['pattern' => 'assets.archive*', 'group' => __('ui.nav.laporan'), 'label' => __('ui.nav.arsip_aset')],
                    ['pattern' => 'transactions.report*', 'group' => __('ui.nav.laporan'), 'label' => __('ui.nav.laporan_peminjaman')],
                    ['pattern' => 'transactions.recap*', 'group' => __('ui.nav.laporan'), 'label' => __('ui.nav.rekap_peminjaman')],
                    ['pattern' => 'assets.*', 'group' => __('ui.nav.master_data'), 'label' => __('ui.nav.aset')],
                    ['pattern' => 'categories.*', 'group' => __('ui.nav.master_data'), 'label' => __('ui.nav.kategori')],
                    ['pattern' => 'locations.*', 'group' => __('ui.nav.master_data'), 'label' => __('ui.nav.lokasi')],
                    ['pattern' => 'transactions.*', 'group' => __('ui.nav.transaksi'), 'label' => __('ui.nav.peminjaman')],
                    ['pattern' => 'maintenance.*', 'group' => __('ui.nav.transaksi'), 'label' => __('ui.nav.perawatan')],
                    ['pattern' => 'mutasi.*', 'group' => __('ui.nav.transaksi'), 'label' => __('ui.nav.mutasi')],
                    ['pattern' => 'users.*', 'group' => __('ui.nav.pengaturan'), 'label' => __('ui.nav.manajemen_user')],
                    ['pattern' => 'institution-profile.*', 'group' => __('ui.nav.pengaturan'), 'label' => __('ui.nav.profil_instansi')],
                    ['pattern' => 'activity-logs.*', 'group' => __('ui.nav.pengaturan'), 'label' => __('ui.nav.aktivitas_sistem')],
                    ['pattern' => 'profile.*', 'group' => null, 'label' => __('ui.nav.profil_saya')],
                ];
                $currentBreadcrumb = collect($breadcrumbMap)->first(fn ($entry) => request()->routeIs($entry['pattern']));
            @endphp

            {{-- CONTENT TOPBAR: sidebar toggle + breadcrumb + clock --}}
            <div class="content-topbar">
                <div class="content-topbar-left">
                    <button
                        type="button"
                        id="sidebarToggle"
                        class="icon-btn icon-btn-lg"
                        title="{{ __('ui.layout.toggle_sidebar') }}"
                    >
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="topbar-breadcrumb">
                        @if(request()->routeIs('dashboard'))
                            <span class="current">{{ __('ui.nav.dashboard') }}</span>
                        @elseif($currentBreadcrumb)
                            @if($currentBreadcrumb['group'])
                                <span>{{ $currentBreadcrumb['group'] }}</span>
                                <span class="separator">/</span>
                            @endif
                            <span class="current">{{ $currentBreadcrumb['label'] }}</span>
                        @else
                            <span class="current">@yield('title', __('ui.layout.system_title'))</span>
                        @endif
                    </div>
                </div>

                {{-- Realtime clock --}}
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

    {{-- Sidebar Toggle --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const toggle = document.getElementById('sidebarToggle');

            if (toggle) {
                toggle.addEventListener('click', function() {
                    const isClosed = html.classList.toggle('sidebar-closed');
                    localStorage.setItem('sidebarOpen', isClosed ? 'false' : 'true');
                });
            }
        });
    </script>

    {{-- Topbar Collapse/Expand --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const collapseBtn = document.getElementById('topbarToggle');
            const expandBtn = document.getElementById('topbarExpandBtn');

            function setCollapsed(isCollapsed) {
                html.classList.toggle('topbar-collapsed', isCollapsed);
                localStorage.setItem('topbarCollapsed', isCollapsed ? 'true' : 'false');
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', function() {
                    setCollapsed(true);
                });
            }

            if (expandBtn) {
                expandBtn.addEventListener('click', function() {
                    setCollapsed(false);
                });
            }
        });
    </script>
</body>
</html>
