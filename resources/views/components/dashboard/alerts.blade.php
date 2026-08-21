{{-- Perlu Perhatian: daftar compact, bukan kumpulan card besar --}}
<div class="card">
    <div class="card-header">
        <h3>{{ __('ui.dashboard.perlu_ditindaklanjuti') }}</h3>
    </div>

    @if($maintenanceTertunda > 0 || $borrowedCount > 0 || $hilang > 0)
    <div class="divide-y divide-default">

        @if($maintenanceTertunda > 0)
        <a href="{{ route('maintenance.index') }}"
           class="flex items-center justify-between gap-3 px-5 py-2 border-l-2 border-l-danger-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="min-w-0">
                <p class="text-xs text-gray-900 dark:text-gray-100">{{ __('ui.dashboard.jadwal_perawatan_terlambat') }}</p>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.dashboard.jadwal_melewati_tenggat', ['count' => $maintenanceTertunda]) }}</p>
            </div>
            <svg class="icon text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

        @if($hilang > 0)
        <a href="{{ route('assets.hilang') }}"
           class="flex items-center justify-between gap-3 px-5 py-2 border-l-2 border-l-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="min-w-0">
                <p class="text-xs text-gray-900 dark:text-gray-100">{{ __('ui.dashboard.laporan_aset_hilang') }}</p>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.dashboard.laporan_menunggu_penanganan', ['count' => $hilang]) }}</p>
            </div>
            <svg class="icon text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

        @if($borrowedCount > 0)
        <a href="{{ route('transactions.index') }}"
           class="flex items-center justify-between gap-3 px-5 py-2 border-l-2 border-l-warning-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="min-w-0">
                <p class="text-xs text-gray-900 dark:text-gray-100">{{ __('ui.dashboard.aset_sedang_dipinjam_title') }}</p>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.dashboard.aset_belum_dikembalikan', ['count' => $borrowedCount]) }}</p>
            </div>
            <svg class="icon text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

    </div>
    @else
    <div class="px-5 py-8 text-center text-secondary text-xs">{{ __('ui.dashboard.semua_kondisi_baik') }}</div>
    @endif
</div>
