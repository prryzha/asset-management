@if($maintenanceTertunda > 0 || $borrowedCount > 0 || $perbaikan > 0)
<div class="space-y-3">

    @if($maintenanceTertunda > 0)
    <a href="{{ route('maintenance.index') }}"
       class="card p-4 block border-l-4 border-l-red-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-100">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-red-50 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400 font-normal">Perawatan Terlambat</p>
                <h3 class="text-xl font-normal text-gray-900 dark:text-gray-100">{{ $maintenanceTertunda }}</h3>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-normal rounded bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Kritis</span>
        </div>
    </a>
    @endif

    @if($borrowedCount > 0)
    <a href="{{ route('transactions.index') }}"
       class="card p-4 block border-l-4 border-l-amber-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-100">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400 font-normal">Barang Dipinjam</p>
                <h3 class="text-xl font-normal text-gray-900 dark:text-gray-100">{{ $borrowedCount }}</h3>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-normal rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">Perhatian</span>
        </div>
    </a>
    @endif

    @if($perbaikan > 0)
    <a href="{{ route('assets.index') }}"
       class="card p-4 block border-l-4 border-l-primary-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-100">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-500 dark:text-gray-400 font-normal">Dalam Perbaikan</p>
                <h3 class="text-xl font-normal text-gray-900 dark:text-gray-100">{{ $perbaikan }}</h3>
            </div>
            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-normal rounded bg-blue-100 text-blue-700 dark:bg-primary-900/30 dark:text-primary-300">Info</span>
        </div>
    </a>
    @endif

</div>
@endif
