@extends('layouts.app')

@section('title', 'Data Aset')

@section('content')
<div class="page-content" x-data="{ selected: [] }">

    <x-ui.page-header title="Data Aset">
        <x-slot:actions>
            <a :href="selected.length > 0
                    ? '{{ route('assets.export-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('assets.export-pdf', request()->only(['search','category_id','location_id','kondisi','status'])) }}'"
               class="btn-secondary btn-xs">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor PDF Terpilih (' + selected.length + ')' : 'Ekspor PDF'"></span>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('assets.export-csv') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('assets.export-csv', request()->only(['search','category_id','location_id','kondisi','status'])) }}'"
               class="btn-secondary btn-xs">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor CSV Terpilih (' + selected.length + ')' : 'Ekspor CSV'"></span>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('assets.label-massal-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('assets.label-massal-pdf', request()->only(['search','category_id','location_id','kondisi','status'])) }}'"
               class="btn-secondary btn-xs">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                <span x-text="selected.length > 0 ? 'Cetak Label Terpilih (' + selected.length + ')' : 'Cetak Label Aset'"></span>
            </a>
            <a href="#" x-show="selected.length > 0" x-cloak @click.prevent="confirmBulkDelete()" class="btn-danger btn-xs">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span x-text="'Hapus Terpilih (' + selected.length + ')'"></span>
            </a>
            <a href="{{ route('assets.create') }}" class="btn-primary btn-xs">
                <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Aset
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Search & Filter + Table --}}
    <div class="card overflow-hidden">
        <div class="card-body py-2.5 border-b border-default">
            <form action="{{ route('assets.index') }}" method="GET" class="filter-form">
                <input type="hidden" name="f" value="1">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode, nama atau merk..."
                           class="form-input form-input-sm w-56 pl-8">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <select name="kondisi" class="form-input form-input-sm w-auto">
                    <option value="">Semua Kondisi</option>
                    <option value="Baik" {{ request('kondisi')=='Baik'?'selected':'' }}>Baik</option>
                    <option value="Kurang Baik" {{ request('kondisi')=='Kurang Baik'?'selected':'' }}>Kurang Baik</option>
                    <option value="Rusak Berat" {{ request('kondisi')=='Rusak Berat'?'selected':'' }}>Rusak Berat</option>
                </select>
                <select name="status" class="form-input form-input-sm w-auto">
                    <option value="">Semua Status</option>
                    <option value="Tersedia" {{ request('status')=='Tersedia'?'selected':'' }}>Tersedia</option>
                    <option value="Dipinjam" {{ request('status')=='Dipinjam'?'selected':'' }}>Dipinjam</option>
                    <option value="Perbaikan" {{ request('status')=='Perbaikan'?'selected':'' }}>Perbaikan</option>
                    <option value="Hilang" {{ request('status')=='Hilang'?'selected':'' }}>Hilang</option>
                </select>
                <select name="sort" class="form-input form-input-sm w-auto">
                    <option value="kode_asc" {{ request('sort','kode_asc')=='kode_asc'?'selected':'' }}>Kode (A-Z)</option>
                    <option value="kode_desc" {{ request('sort')=='kode_desc'?'selected':'' }}>Kode (Z-A)</option>
                    <option value="nama_asc" {{ request('sort')=='nama_asc'?'selected':'' }}>Nama (A-Z)</option>
                    <option value="nama_desc" {{ request('sort')=='nama_desc'?'selected':'' }}>Nama (Z-A)</option>
                    <option value="nilai_desc" {{ request('sort')=='nilai_desc'?'selected':'' }}>Nilai Tertinggi</option>
                    <option value="nilai_asc" {{ request('sort')=='nilai_asc'?'selected':'' }}>Nilai Terendah</option>
                    <option value="terbaru" {{ request('sort')=='terbaru'?'selected':'' }}>Terbaru Ditambahkan</option>
                </select>
                <button type="submit" class="btn-primary btn-xs">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search','category_id','location_id','kondisi','status','sort']))
                <a href="{{ route('assets.index') }}" class="btn-ghost btn-xs">Reset Filter</a>
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
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Kondisi</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Nilai</th>
                        <th class="text-center">Aksi</th>
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
                                {{ $asset->kondisi }}
                            </span>
                        </td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$asset->status" />
                        </td>
                        <td class="text-center font-normal">{{ $asset->nilai_perolehan ? 'Rp '.number_format($asset->nilai_perolehan,0,',','.') : '—' }}</td>
                        <td class="text-center">
                            <div class="table-actions">
                                <a href="{{ route('assets.edit', $asset) }}" class="btn-ghost btn-icon" title="Ubah" aria-label="Ubah {{ $asset->nama_barang }}">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-icon text-danger hover:text-white hover:bg-danger" title="Hapus" aria-label="Hapus {{ $asset->nama_barang }}">
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
                                title="Gunakan Filter untuk Menampilkan Data"
                                description="Isi kata kunci pencarian atau pilih kategori/lokasi, lalu klik Cari untuk menampilkan daftar aset." />
                            @else
                            <x-ui.empty-state
                                icon="package"
                                title="Tidak Ada Aset Ditemukan"
                                description="Tidak ada aset yang cocok dengan filter ini." />
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
            title: 'Hapus Aset?',
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
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
            title: 'Tidak Bisa Dihapus',
            html: 'Semua aset yang dipilih sedang dipinjam:<br><b>' + locked.map(c => c.dataset.kode).join(', ') + '</b>',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Mengerti'
        });
        return;
    }

    let html = `<p>${deletable.length} aset akan dihapus. Data yang dihapus tidak dapat dikembalikan.</p>`;
    if (locked.length > 0) {
        html += `<p class="mt-3 text-amber-600">🔒 ${locked.length} aset dilewati karena sedang dipinjam: <b>${locked.map(c => c.dataset.kode).join(', ')}</b></p>`;
    }

    Swal.fire({
        title: 'Hapus Aset Terpilih?',
        html: html,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Ya, Hapus ${deletable.length} Aset`,
        cancelButtonText: 'Batal'
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