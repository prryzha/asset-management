{{-- CHART --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Status --}}
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Status Aset</h3>
            <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-primary-700 dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                </svg>
            </div>
        </div>
        <div class="relative h-36">
            <canvas id="statusChart"></canvas>
        </div>
    </div>

    {{-- Kategori --}}
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Kategori</h3>
            <div class="w-8 h-8 bg-warning-50 dark:bg-amber-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-warning-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
        </div>
        <div class="relative h-36">
            <canvas id="kategoriChart"></canvas>
        </div>
    </div>

    {{-- Lokasi (bar chart) --}}
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Lokasi</h3>
            <div class="w-8 h-8 bg-danger-50 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-danger-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
        <div class="relative h-36">
            <canvas id="lokasiChart"></canvas>
        </div>
    </div>

    {{-- Kondisi --}}
    <div class="card p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Kondisi Aset</h3>
            <div class="w-8 h-8 bg-success-50 dark:bg-emerald-900/30 flex items-center justify-center">
                <svg class="w-4 h-4 text-success-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
        </div>
        <div class="relative h-36">
            <canvas id="kondisiChart"></canvas>
        </div>
    </div>

</div>
