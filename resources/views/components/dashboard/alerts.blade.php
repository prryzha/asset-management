{{-- Perlu Perhatian: daftar compact, bukan kumpulan card besar --}}
<div class="card">
    <div class="card-header">
        <h3>Perlu Ditindaklanjuti</h3>
    </div>

    @if($maintenanceTertunda > 0 || $borrowedCount > 0 || $hilang > 0)
    <div class="divide-y divide-default">

        @if($maintenanceTertunda > 0)
        <a href="{{ route('maintenance.index') }}"
           class="flex items-center justify-between gap-3 px-5 py-3 border-l-2 border-l-danger-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="min-w-0">
                <p class="text-xs text-gray-900 dark:text-gray-100">Jadwal Perawatan Terlambat</p>
                <p class="text-xs text-secondary mt-0.5">{{ $maintenanceTertunda }} jadwal perawatan melewati tenggat</p>
            </div>
            <svg class="icon text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

        @if($hilang > 0)
        <a href="{{ route('assets.hilang') }}"
           class="flex items-center justify-between gap-3 px-5 py-3 border-l-2 border-l-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="min-w-0">
                <p class="text-xs text-gray-900 dark:text-gray-100">Laporan Aset Hilang</p>
                <p class="text-xs text-secondary mt-0.5">{{ $hilang }} laporan menunggu penanganan</p>
            </div>
            <svg class="icon text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

        @if($borrowedCount > 0)
        <a href="{{ route('transactions.index') }}"
           class="flex items-center justify-between gap-3 px-5 py-3 border-l-2 border-l-warning-500 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
            <div class="min-w-0">
                <p class="text-xs text-gray-900 dark:text-gray-100">Aset Sedang Dipinjam</p>
                <p class="text-xs text-secondary mt-0.5">{{ $borrowedCount }} aset belum dikembalikan</p>
            </div>
            <svg class="icon text-gray-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @endif

    </div>
    @else
    <div class="px-5 py-8 text-center text-secondary text-xs">Semua aset dalam kondisi baik. Tidak ada item yang perlu ditindaklanjuti.</div>
    @endif
</div>
