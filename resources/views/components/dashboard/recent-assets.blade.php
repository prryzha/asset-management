<div class="card">

    <div class="card-header">
        <div>
            <h3>Aset Terbaru</h3>
            <p class="subtitle">5 asset yang terakhir ditambahkan.</p>
        </div>
        <a href="{{ route('assets.index') }}" class="text-xs font-normal text-primary-600 hover:text-primary-700 transition-colors">
            Lihat Semua &rarr;
        </a>
    </div>

    <div>
        @forelse($recentAssets as $asset)
        <a href="{{ route('assets.show', $asset) }}" class="flex items-center justify-between px-6 py-4 border-b last:border-b-0 border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-100 group">
            <div class="flex items-center gap-4">
                @if($asset->foto)
                <div class="w-12 h-12 overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                    <img src="{{ asset('storage/'.$asset->foto) }}" alt="{{ $asset->nama_barang }}" class="w-full h-full object-cover">
                </div>
                @else
                <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
                <div>
                    <h3 class="font-normal text-gray-800 dark:text-gray-200 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors text-sm">{{ $asset->nama_barang }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $asset->kode_barang }}</p>
                </div>
            </div>
            <div class="text-right flex items-center gap-3">
                <x-ui.badge-status :status="$asset->status" />
                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
        @empty
        <div class="p-10 text-center text-gray-400 dark:text-gray-500">Belum ada aset.</div>
        @endforelse
    </div>

</div>