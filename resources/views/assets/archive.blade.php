@extends('layouts.app')

@section('title', 'Arsip Aset')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Arsip Aset" subtitle="Aset yang sudah dihapuskan dari inventaris aktif. Data dan histori tetap tersimpan untuk kebutuhan audit dan laporan.">
        <x-slot:actions>
            <a href="{{ route('assets.archive-export-pdf', request()->only(['search','category_id','location_id'])) }}"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Ekspor PDF
            </a>
            <a href="{{ route('assets.archive-export-csv', request()->only(['search','category_id','location_id'])) }}"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Ekspor CSV
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Search & Filter --}}
    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form action="{{ route('assets.archive') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode, nama, kategori atau lokasi..."
                           class="form-input form-input-sm w-64 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="category_id" class="form-input form-input-sm w-auto">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                    @endforeach
                </select>
                <select name="location_id" class="form-input form-input-sm w-auto">
                    <option value="">Semua Lokasi</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary btn-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search','category_id','location_id']))
                <a href="{{ route('assets.archive') }}" class="btn-ghost btn-sm">Reset Filter</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Lokasi Terakhir</th>
                        <th>Kondisi</th>
                        <th class="text-center">Status</th>
                        <th>Penghapusan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    @php $disposalLog = $asset->assetLogs->first(); @endphp
                    <tr class="cursor-pointer asset-row" data-url="{{ route('assets.show', $asset) }}">
                        <td>
                            <div class="flex items-center gap-3">
                                @if($asset->foto)
                                <div class="w-10 h-10 overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700">
                                    <img src="{{ asset('storage/'.$asset->foto) }}" alt="{{ $asset->nama_barang }}" class="w-full h-full object-cover">
                                </div>
                                @else
                                <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="font-normal">{{ $asset->kode_barang }}</span>
                                    <div class="text-sm text-secondary truncate">{{ $asset->nama_barang }}</div>
                                    <div class="text-xs text-secondary truncate">{{ $asset->merk }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $asset->category?->nama ?? '-' }}</td>
                        <td class="text-secondary">{{ $asset->location?->nama ?? '-' }}</td>
                        <td>
                            @php
                                $kondisiBadge = [
                                    'Baik' => 'bg-success-50 text-success dark:bg-emerald-900/30 dark:text-emerald-300',
                                    'Kurang Baik' => 'bg-warning-50 text-warning dark:bg-amber-900/30 dark:text-amber-300',
                                    'Rusak Berat' => 'bg-danger-50 text-danger dark:bg-red-900/30 dark:text-red-300',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-normal {{ $kondisiBadge[$asset->kondisi] ?? 'bg-gray-100 text-secondary dark:bg-gray-700' }}">
                                {{ $asset->kondisi }}
                            </span>
                        </td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$asset->status" />
                        </td>
                        <td>
                            @if($disposalLog)
                                <div class="text-sm">{{ $disposalLog->created_at->format('d/m/Y') }}</div>
                                <div class="text-xs text-secondary">oleh {{ $disposalLog->user?->name ?? 'Sistem' }}</div>
                            @else
                                <span class="text-xs text-secondary">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('assets.show', $asset) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Rincian
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-16">
                            <x-ui.empty-state
                                icon="package"
                                title="Belum Ada Aset di Arsip"
                                description="Aset yang diproses penghapusan akan muncul di sini." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $assets->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
// Klik baris untuk membuka detail
document.querySelectorAll('.asset-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form') || e.target.closest('input')) return;
        window.location = this.dataset.url;
    });
});
</script>
@endpush
