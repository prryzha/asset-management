@extends('layouts.app')

@section('title', 'Manajemen Lokasi')

@section('content')
<div class="page-content">

    <x-ui.page-header title="Manajemen Lokasi" subtitle="Kelola seluruh lokasi penyimpanan aset.">
        <x-slot:actions>
            <a href="{{ route('locations.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Lokasi
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Lokasi</th>
                        <th>Gedung / Lantai</th>
                        <th class="text-center">Jumlah Aset</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($locations as $location)
                    <tr>
                        <td>
                            <span class="font-mono text-sm text-secondary">{{ $location->kode ?? '—' }}</span>
                        </td>
                        <td class="font-normal">{{ $location->nama }}</td>
                        <td class="text-secondary">{{ $location->gedung ?? '—' }}</td>
                        <td class="text-center">{{ $location->assets_count }}</td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('locations.edit', $location) }}"
                                   class="btn-ghost btn-sm px-2 py-1 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Ubah
                                </a>
                                <form action="{{ route('locations.destroy', $location) }}"
                                      method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus lokasi {{ $location->nama }}?')">
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
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="map-pin"
                                title="Belum Ada Lokasi"
                                description="Belum ada lokasi penyimpanan. Silakan tambah lokasi baru." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $locations->links() }}
    </div>

</div>
@endsection