<aside class="sidebar">

    {{-- User profile --}}
    <div class="sidebar-user relative" x-data="{ open: false }" x-on:keydown.escape.window="open = false" x-on:click.outside="open = false">
        <button type="button" class="sidebar-user-trigger" x-on:click="open = !open">
            <div class="header-user-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
            <div class="text-left leading-tight min-w-0">
                <p class="header-user-name ui-title">{{ auth()->user()->name }}</p>
                <p class="header-user-role">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition
            class="card dropdown-panel dropdown-panel-left w-48 py-1"
        >
            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profil Saya
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item w-full text-left">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>

    {{-- Navigation --}}
    @php
        // Satu boolean per link, dihitung sekali dan dipakai dua kali (class
        // "active" link itu sendiri + di-OR ke status expanded parent-nya) —
        // supaya kondisi route-nya tidak ditulis dua kali secara terpisah.
        $isDashboard = request()->routeIs('dashboard');

        $isAset = request()->routeIs('assets.*') && !request()->routeIs('assets.archive*') && !request()->routeIs('assets.hilang*');
        $isKategori = request()->routeIs('categories.*');
        $isLokasi = request()->routeIs('locations.*');
        $masterDataOpen = $isAset || $isKategori || $isLokasi;

        $isPeminjaman = request()->routeIs('transactions.*') && !request()->routeIs('transactions.report*') && !request()->routeIs('transactions.recap*');
        $isPerawatan = request()->routeIs('maintenance.*');
        $isMutasi = request()->routeIs('mutasi.*');
        $transaksiOpen = $isPeminjaman || $isPerawatan || $isMutasi;

        $isLaporanAset = request()->routeIs('assets.hilang*');
        $isArsipAset = request()->routeIs('assets.archive*');
        $isLaporanPeminjaman = request()->routeIs('transactions.report*');
        $isRekapPeminjaman = request()->routeIs('transactions.recap*');
        $laporanOpen = $isLaporanAset || $isArsipAset || $isLaporanPeminjaman || $isRekapPeminjaman;

        $isManajemenUser = request()->routeIs('users.*');
        $isAktivitasSistem = request()->routeIs('activity-logs.*');
        $pengaturanOpen = $isManajemenUser || $isAktivitasSistem;
    @endphp
    <nav class="sidebar-nav scrollbar-hidden">

        {{-- Dasbor — tanpa submenu, langsung flat di top-level nav, tanpa section title --}}
        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ $isDashboard ? 'active' : '' }}">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 001 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dasbor
        </a>

        {{-- Master Data — collapsible --}}
        <div class="sidebar-section" x-data="{ open: {{ $masterDataOpen ? 'true' : 'false' }} }">
            <button type="button" class="sidebar-section-title sidebar-section-trigger"
                    x-on:click="open = !open" :aria-expanded="open" aria-controls="nav-master-data">
                <span class="flex items-center gap-2.5">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Master Data
                </span>
                <svg class="sidebar-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition id="nav-master-data" class="sidebar-subnav">
                <a href="{{ route('assets.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isAset ? 'active' : '' }}">
                    Aset
                </a>

                <a href="{{ route('categories.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isKategori ? 'active' : '' }}">
                    Kategori
                </a>

                <a href="{{ route('locations.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isLokasi ? 'active' : '' }}">
                    Lokasi
                </a>
            </div>
        </div>

        {{-- Transaksi — collapsible --}}
        <div class="sidebar-section" x-data="{ open: {{ $transaksiOpen ? 'true' : 'false' }} }">
            <button type="button" class="sidebar-section-title sidebar-section-trigger"
                    x-on:click="open = !open" :aria-expanded="open" aria-controls="nav-transaksi">
                <span class="flex items-center gap-2.5">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    Transaksi
                </span>
                <svg class="sidebar-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition id="nav-transaksi" class="sidebar-subnav">
                <a href="{{ route('transactions.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isPeminjaman ? 'active' : '' }}">
                    Peminjaman
                </a>

                <a href="{{ route('maintenance.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isPerawatan ? 'active' : '' }}">
                    Perawatan
                </a>

                <a href="{{ route('mutasi.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isMutasi ? 'active' : '' }}">
                    Mutasi
                </a>
            </div>
        </div>

        {{-- Laporan — collapsible --}}
        <div class="sidebar-section" x-data="{ open: {{ $laporanOpen ? 'true' : 'false' }} }">
            <button type="button" class="sidebar-section-title sidebar-section-trigger"
                    x-on:click="open = !open" :aria-expanded="open" aria-controls="nav-laporan">
                <span class="flex items-center gap-2.5">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Laporan
                </span>
                <svg class="sidebar-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition id="nav-laporan" class="sidebar-subnav">
                <a href="{{ route('assets.hilang') }}"
                   class="sidebar-link sidebar-link-child {{ $isLaporanAset ? 'active' : '' }}">
                    Laporan Aset
                </a>

                <a href="{{ route('assets.archive') }}"
                   class="sidebar-link sidebar-link-child {{ $isArsipAset ? 'active' : '' }}">
                    Arsip Aset
                </a>

                <a href="{{ route('transactions.report') }}"
                   class="sidebar-link sidebar-link-child {{ $isLaporanPeminjaman ? 'active' : '' }}">
                    Laporan Peminjaman
                </a>

                <a href="{{ route('transactions.recap') }}"
                   class="sidebar-link sidebar-link-child {{ $isRekapPeminjaman ? 'active' : '' }}">
                    Rekap Peminjaman
                </a>
            </div>
        </div>

        {{-- Pengaturan — collapsible --}}
        <div class="sidebar-section" x-data="{ open: {{ $pengaturanOpen ? 'true' : 'false' }} }">
            <button type="button" class="sidebar-section-title sidebar-section-trigger"
                    x-on:click="open = !open" :aria-expanded="open" aria-controls="nav-pengaturan">
                <span class="flex items-center gap-2.5">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Pengaturan
                </span>
                <svg class="sidebar-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak x-transition id="nav-pengaturan" class="sidebar-subnav">
                @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isManajemenUser ? 'active' : '' }}">
                    Manajemen User
                </a>
                @endif

                <a href="{{ route('activity-logs.index') }}"
                   class="sidebar-link sidebar-link-child {{ $isAktivitasSistem ? 'active' : '' }}">
                    Aktivitas Sistem
                </a>
            </div>
        </div>

        {{-- Keluar — flat action at the end of nav, same route/CSRF as the
             existing logout inside the user-profile dropdown above; this one
             is reachable directly without opening that menu first. --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-link w-full text-left">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </form>
    </nav>
</aside>
