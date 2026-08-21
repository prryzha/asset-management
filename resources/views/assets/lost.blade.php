@extends('layouts.app')

@section('title', __('ui.reports.hilang_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.reports.hilang_title')" />

    {{-- Search & Filter --}}
    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.reports.daftar_aset_hilang')">
            <a href="{{ route('assets.hilang-export-pdf', request()->only(['search','category_id'])) }}"
               class="btn-secondary btn-icon" title="{{ __('ui.common.ekspor_pdf') }}" aria-label="{{ __('ui.common.ekspor_pdf') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('assets.hilang-export-csv', request()->only(['search','category_id'])) }}"
               class="btn-secondary btn-icon" title="{{ __('ui.common.ekspor_csv') }}" aria-label="{{ __('ui.common.ekspor_csv') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
            <form action="{{ route('assets.hilang') }}" method="GET" class="filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('ui.reports.search_placeholder_arsip') }}"
                           class="form-input form-input-sm w-64 pl-8">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="category_id" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.assets.semua_kategori') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary btn-icon" title="{{ __('ui.common.cari') }}" aria-label="{{ __('ui.common.cari') }}">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </button>
                @if(request()->hasAny(['search','category_id']))
                <a href="{{ route('assets.hilang') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.reset_filter') }}" aria-label="{{ __('ui.common.reset_filter') }}"><svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ui.reports.barang') }}</th>
                        <th>{{ __('ui.reports.kategori') }}</th>
                        <th>{{ __('ui.reports.lokasi_terakhir') }}</th>
                        <th class="text-center">{{ __('ui.reports.tanggal_laporan') }}</th>
                        <th>{{ __('ui.reports.kronologi') }}</th>
                        <th>{{ __('ui.reports.petugas') }}</th>
                        <th class="text-center">{{ __('ui.common.aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    @php $lostLog = $asset->assetLogs->first(); @endphp
                    <tr class="cursor-pointer asset-row" data-url="{{ route('assets.show', $asset) }}">
                        <td>
                            <div class="flex items-center gap-3">
                                @if($asset->foto)
                                <div class="table-thumb">
                                    <img src="{{ asset('storage/'.$asset->foto) }}" alt="{{ $asset->nama_barang }}">
                                </div>
                                @else
                                <div class="table-thumb-icon stat-icon-neutral">
                                    <svg class="icon-lg text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="font-normal">{{ $asset->kode_barang }}</span>
                                    <div class="text-xs text-secondary truncate">{{ $asset->nama_barang }}</div>
                                    <div class="text-xs text-secondary truncate">{{ $asset->merk }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="text-secondary">{{ $asset->category?->nama ?? '-' }}</td>
                        <td class="text-secondary">{{ $asset->location?->nama ?? '-' }}</td>
                        <td class="text-center">
                            @if($lostLog)
                                <span class="font-normal text-xs">{{ $lostLog->created_at->format('d/m/Y') }}</span>
                                <div class="text-xs text-secondary">{{ $lostLog->created_at->format('H:i') }}</div>
                            @else
                                <span class="text-xs text-secondary">—</span>
                            @endif
                        </td>
                        <td>
                            @if($lostLog)
                                <span class="text-xs text-secondary block max-w-xs truncate" title="{{ $lostLog->deskripsi }}">{{ $lostLog->deskripsi }}</span>
                            @else
                                <span class="text-xs text-secondary">—</span>
                            @endif
                        </td>
                        <td class="text-secondary text-xs">{{ $lostLog?->user?->name ?? __('ui.mutasi.sistem') }}</td>
                        <td class="text-center">
                            <a href="{{ route('assets.show', $asset) }}" class="btn-ghost btn-icon" title="{{ __('ui.assets.rincian') }}" aria-label="{{ __('ui.assets.rincian') }} {{ $asset->nama_barang }}">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-16">
                            <x-ui.empty-state
                                icon="search"
                                :title="__('ui.reports.empty_hilang_title')"
                                :description="__('ui.reports.empty_hilang_desc')" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assets->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $assets->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.asset-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form') || e.target.closest('input')) return;
        window.location = this.dataset.url;
    });
});
</script>
@endpush
