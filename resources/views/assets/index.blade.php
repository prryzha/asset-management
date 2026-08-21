@extends('layouts.app')

@section('title', __('ui.assets.title'))

@section('content')
<div class="page-content" x-data="{ selected: [] }">

    <x-ui.page-header :title="__('ui.assets.title')" />

    {{-- Search & Filter + Table --}}
    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.assets.daftar_aset')">
            <a :href="selected.length > 0
                    ? '{{ route('assets.export-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('assets.export-pdf', request()->only(['search','category_id','location_id','kondisi','status'])) }}'"
               class="btn-secondary btn-icon"
               :title="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_pdf_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_pdf')) }}"
               :aria-label="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_pdf_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_pdf')) }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('assets.export-csv') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('assets.export-csv', request()->only(['search','category_id','location_id','kondisi','status'])) }}'"
               class="btn-secondary btn-icon"
               :title="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_csv_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_csv')) }}"
               :aria-label="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_csv_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_csv')) }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('assets.label-massal-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('assets.label-massal-pdf', request()->only(['search','category_id','location_id','kondisi','status'])) }}'"
               class="btn-secondary btn-icon"
               :title="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.cetak_label_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.assets.cetak_label_aset')) }}"
               :aria-label="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.cetak_label_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.assets.cetak_label_aset')) }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </a>
            <a href="#" x-show="selected.length > 0" x-cloak @click.prevent="confirmBulkDelete()" class="btn-danger btn-icon"
               :title="{{ \Illuminate\Support\Js::from(__('ui.assets.hapus_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')')" :aria-label="{{ \Illuminate\Support\Js::from(__('ui.assets.hapus_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')')">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </a>
            <a href="{{ route('assets.create') }}" class="btn-primary btn-icon" title="{{ __('ui.assets.tambah_aset') }}" aria-label="{{ __('ui.assets.tambah_aset') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
            <form action="{{ route('assets.index') }}" method="GET" class="filter-form">
                <input type="hidden" name="f" value="1">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('ui.assets.search_placeholder') }}"
                           class="form-input form-input-sm w-56 pl-8">
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
                <select name="location_id" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.assets.semua_lokasi') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->nama }}</option>
                    @endforeach
                </select>
                <select name="kondisi" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.assets.semua_kondisi') }}</option>
                    <option value="Baik" {{ request('kondisi')=='Baik'?'selected':'' }}>{{ __('ui.status.Baik') }}</option>
                    <option value="Kurang Baik" {{ request('kondisi')=='Kurang Baik'?'selected':'' }}>{{ __('ui.status.Kurang Baik') }}</option>
                    <option value="Rusak Berat" {{ request('kondisi')=='Rusak Berat'?'selected':'' }}>{{ __('ui.status.Rusak Berat') }}</option>
                </select>
                <select name="status" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.assets.semua_status') }}</option>
                    <option value="Tersedia" {{ request('status')=='Tersedia'?'selected':'' }}>{{ __('ui.status.Tersedia') }}</option>
                    <option value="Dipinjam" {{ request('status')=='Dipinjam'?'selected':'' }}>{{ __('ui.status.Dipinjam') }}</option>
                    <option value="Perbaikan" {{ request('status')=='Perbaikan'?'selected':'' }}>{{ __('ui.status.Perbaikan') }}</option>
                    <option value="Hilang" {{ request('status')=='Hilang'?'selected':'' }}>{{ __('ui.status.Hilang') }}</option>
                </select>
                <select name="sort" class="form-input form-input-sm w-auto">
                    <option value="kode_asc" {{ request('sort','kode_asc')=='kode_asc'?'selected':'' }}>{{ __('ui.assets.sort_kode_asc') }}</option>
                    <option value="kode_desc" {{ request('sort')=='kode_desc'?'selected':'' }}>{{ __('ui.assets.sort_kode_desc') }}</option>
                    <option value="nama_asc" {{ request('sort')=='nama_asc'?'selected':'' }}>{{ __('ui.assets.sort_nama_asc') }}</option>
                    <option value="nama_desc" {{ request('sort')=='nama_desc'?'selected':'' }}>{{ __('ui.assets.sort_nama_desc') }}</option>
                    <option value="nilai_desc" {{ request('sort')=='nilai_desc'?'selected':'' }}>{{ __('ui.assets.sort_nilai_desc') }}</option>
                    <option value="nilai_asc" {{ request('sort')=='nilai_asc'?'selected':'' }}>{{ __('ui.assets.sort_nilai_asc') }}</option>
                    <option value="terbaru" {{ request('sort')=='terbaru'?'selected':'' }}>{{ __('ui.assets.sort_terbaru') }}</option>
                </select>
                <button type="submit" class="btn-primary btn-icon" title="{{ __('ui.common.cari') }}" aria-label="{{ __('ui.common.cari') }}">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </button>
                @if(request()->hasAny(['search','category_id','location_id','kondisi','status','sort']))
                <a href="{{ route('assets.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.reset_filter') }}" aria-label="{{ __('ui.common.reset_filter') }}"><svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-8">
                            <input type="checkbox"
                                   class="checkbox"
                                   :checked="selected.length > 0 && selected.length === {{ $assets->count() }}"
                                   @change="selected = ($event.target.checked ? {{ $assets->pluck('id')->values() }} : []).map(String)">
                        </th>
                        <th>{{ __('ui.assets.barang') }}</th>
                        <th>{{ __('ui.assets.kategori') }}</th>
                        <th>{{ __('ui.assets.lokasi') }}</th>
                        <th>{{ __('ui.assets.kondisi') }}</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">{{ __('ui.assets.nilai') }}</th>
                        <th class="text-center">{{ __('ui.common.aksi') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assets as $asset)
                    <tr class="cursor-pointer asset-row" data-url="{{ route('assets.show', $asset) }}">
                        <td>
                            <input type="checkbox" value="{{ $asset->id }}" x-model="selected"
                                   data-status="{{ $asset->status }}" data-kode="{{ $asset->kode_barang }}"
                                   class="checkbox">
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                @if($asset->foto)
                                <div class="table-thumb">
                                    <img src="{{ asset('storage/'.$asset->foto) }}" alt="{{ $asset->nama_barang }}">
                                </div>
                                @else
                                <div class="table-thumb-icon stat-icon-primary">
                                    <svg class="icon-lg text-primary dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <td>
                            @php
                                $kondisiBadge = [
                                    'Baik' => 'badge-subtle-success',
                                    'Kurang Baik' => 'badge-subtle-warning',
                                    'Rusak Berat' => 'badge-subtle-danger',
                                ];
                            @endphp
                            <span class="badge-subtle {{ $kondisiBadge[$asset->kondisi] ?? 'badge-subtle-neutral text-secondary' }}">
                                {{ __('ui.status.' . $asset->kondisi) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$asset->status" />
                        </td>
                        <td class="text-center font-normal">{{ $asset->nilai_perolehan ? 'Rp '.number_format($asset->nilai_perolehan,0,',','.') : '—' }}</td>
                        <td class="text-center">
                            <div class="table-actions">
                                <a href="{{ route('assets.edit', $asset) }}" class="btn-ghost btn-icon" title="{{ __('ui.common.ubah') }}" aria-label="{{ __('ui.common.ubah') }} {{ $asset->nama_barang }}">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-icon text-danger hover:text-white hover:bg-danger" title="{{ __('ui.common.hapus') }}" aria-label="{{ __('ui.common.hapus') }} {{ $asset->nama_barang }}">
                                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-16">
                            @if(!$hasFilter)
                            <x-ui.empty-state
                                icon="search"
                                :title="__('ui.assets.empty_filter_title')"
                                :description="__('ui.assets.empty_filter_desc')" />
                            @else
                            <x-ui.empty-state
                                icon="package"
                                :title="__('ui.assets.empty_notfound_title')"
                                :description="__('ui.assets.empty_notfound_desc')" />
                            @endif
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

    <form id="bulkDeleteForm" method="POST" action="{{ route('assets.bulk-destroy') }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>

</div>
@endsection

@push('scripts')
<script>
const assetsI18n = @json(__('ui.assets'));
const commonI18n = @json(__('ui.common'));

// Row click to detail
document.querySelectorAll('.asset-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form') || e.target.closest('input')) return;
        window.location = this.dataset.url;
    });
});

// Delete confirmation
document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: assetsI18n.confirm_delete_title,
            text: assetsI18n.confirm_delete_text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: commonI18n.ya_hapus,
            cancelButtonText: commonI18n.batal
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});

// Hapus terpilih (bulk delete) — aset berstatus "Dipinjam" dilewati, tidak boleh dihapus
window.confirmBulkDelete = function() {
    const checked = [...document.querySelectorAll('tbody input[type=checkbox]:checked')];
    const deletable = checked.filter(c => c.dataset.status !== 'Dipinjam');
    const locked = checked.filter(c => c.dataset.status === 'Dipinjam');

    if (deletable.length === 0) {
        Swal.fire({
            icon: 'error',
            title: assetsI18n.cannot_delete_title,
            html: assetsI18n.cannot_delete_html.replace(':kodes', locked.map(c => c.dataset.kode).join(', ')),
            confirmButtonColor: '#dc2626',
            confirmButtonText: assetsI18n.mengerti
        });
        return;
    }

    let html = `<p>${assetsI18n.bulk_delete_confirm_text.replace(':count', deletable.length)}</p>`;
    if (locked.length > 0) {
        html += `<p class="mt-3 text-amber-600">🔒 ${assetsI18n.bulk_delete_locked_text.replace(':lockedCount', locked.length).replace(':kodes', locked.map(c => c.dataset.kode).join(', '))}</p>`;
    }

    Swal.fire({
        title: assetsI18n.confirm_bulk_delete_title,
        html: html,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: assetsI18n.ya_hapus_count.replace(':count', deletable.length),
        cancelButtonText: commonI18n.batal
    }).then((result) => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('bulkDeleteForm');
        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
        deletable.forEach(c => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = c.value;
            form.appendChild(input);
        });
        form.submit();
    });
};
</script>
@endpush
