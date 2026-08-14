@extends('layouts.app')

@section('title', 'Data Aset')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Data Aset" subtitle="Kelola seluruh aset yang dimiliki sekolah.">
        <x-slot:actions>
            <a href="{{ route('assets.export-pdf', request()->only(['category_id','location_id'])) }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('assets.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Aset
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    {{-- Search & Filter Card --}}
    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form action="{{ route('assets.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode, nama atau merk..."
                           class="form-input form-input-sm w-56 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="category_id" onchange="this.form.submit()" class="form-input form-input-sm w-auto">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                    @endforeach
                </select>
                <select name="location_id" onchange="this.form.submit()" class="form-input form-input-sm w-auto">
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
                @if(request()->hasAny(['search','category_id','location_id','kondisi','status']))
                <a href="{{ route('assets.index') }}" class="btn-ghost btn-sm">Reset Filter</a>
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
                            <div class="flex items-center gap-3">
                                @if($asset->foto)
                                <div class="w-10 h-10 overflow-hidden flex-shrink-0 bg-gray-100 dark:bg-gray-700">
                                    <img src="{{ asset('storage/'.$asset->foto) }}" alt="{{ $asset->nama_barang }}" class="w-full h-full object-cover">
                                </div>
                                @else
                                <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-primary dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="font-semibold">{{ $asset->kode_barang }}</span>
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
                            <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold {{ $kondisiBadge[$asset->kondisi] ?? 'bg-gray-100 text-secondary dark:bg-gray-700' }}">
                                {{ $asset->kondisi }}
                            </span>
                        </td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$asset->status" />
                        </td>
                        <td class="text-center font-medium">{{ $asset->nilai_perolehan ? 'Rp '.number_format($asset->nilai_perolehan,0,',','.') : '—' }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('assets.edit', $asset) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="delete-form">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-sm px-2 py-1 text-xs text-danger hover:text-white hover:bg-danger">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-16">
                            <x-ui.empty-state
                                icon="package"
                                title="Belum Ada Data Aset"
                                description="Belum ada aset yang terdaftar. Silakan tambah aset baru." />
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
// Row click to detail
document.querySelectorAll('.asset-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('a') || e.target.closest('button') || e.target.closest('form')) return;
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
</script>
@endpush