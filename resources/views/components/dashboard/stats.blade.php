<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    {{-- Total Asset --}}
    <div class="stat-card">
        <div class="flex items-start gap-4">
            <div class="stat-icon bg-primary-50 dark:bg-primary-900/30">
                <svg class="w-5 h-5 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="stat-label">Total Aset</p>
                <h2 class="stat-value">{{ $totalAsset }}</h2>
                <p class="stat-detail">Nilai: Rp {{ number_format($totalNilai, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Tersedia --}}
    <div class="stat-card">
        <div class="flex items-start gap-4">
            <div class="stat-icon bg-success-50 dark:bg-emerald-900/30">
                <svg class="w-5 h-5 text-success-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="stat-label">Tersedia</p>
                <h2 class="stat-value">{{ $tersedia }}</h2>
                <p class="stat-detail">{{ $totalAsset > 0 ? round(($tersedia/$totalAsset)*100) : 0 }}% dari total</p>
            </div>
        </div>
    </div>

    {{-- Dipinjam --}}
    <div class="stat-card">
        <div class="flex items-start gap-4">
            <div class="stat-icon bg-warning-50 dark:bg-amber-900/30">
                <svg class="w-5 h-5 text-warning-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="stat-label">Aset Dipinjam</p>
                <h2 class="stat-value">{{ $dipinjam }}</h2>
                <p class="stat-detail">{{ $totalAsset > 0 ? round(($dipinjam/$totalAsset)*100) : 0 }}% dari total</p>
            </div>
        </div>
    </div>

    {{-- Perbaikan --}}
    <div class="stat-card">
        <div class="flex items-start gap-4">
            <div class="stat-icon bg-danger-50 dark:bg-red-900/30">
                <svg class="w-5 h-5 text-danger-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="stat-label">Aset Perbaikan</p>
                <h2 class="stat-value">{{ $perbaikan }}</h2>
                <p class="stat-detail">{{ $totalAsset > 0 ? round(($perbaikan/$totalAsset)*100) : 0 }}% dari total</p>
            </div>
        </div>
    </div>

</div>