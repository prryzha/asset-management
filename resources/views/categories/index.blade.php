@extends('layouts.app')

@section('title', 'Manajemen Kategori')

@section('content')
<div class="page-content">

    <x-ui.page-header title="Manajemen Kategori" />

    <div class="card overflow-hidden">
        <x-ui.table-heading title="Daftar Kategori">
            <a href="{{ route('categories.create') }}" class="btn-primary btn-icon" title="Tambah Kategori" aria-label="Tambah Kategori">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </a>
        </x-ui.table-heading>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th class="text-center">Jumlah Aset</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <span class="font-mono text-secondary">{{ $category->kode ?? '—' }}</span>
                        </td>
                        <td class="font-normal">{{ $category->nama }}</td>
                        <td class="text-center">{{ $category->assets_count }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('categories.edit', $category) }}"
                                   class="btn-ghost btn-icon" title="Ubah" aria-label="Ubah {{ $category->nama }}">
                                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('categories.destroy', $category) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus kategori {{ $category->nama }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-ghost btn-icon text-danger hover:text-white hover:bg-danger" title="Hapus" aria-label="Hapus {{ $category->nama }}">
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
                        <td colspan="4" class="text-center py-16">
                            <x-ui.empty-state
                                icon="folder"
                                title="Belum Ada Kategori"
                                description="Belum ada kategori aset. Silakan tambah kategori baru." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

</div>
@endsection