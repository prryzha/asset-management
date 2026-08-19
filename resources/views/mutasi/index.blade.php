@extends('layouts.app')

@section('title', 'Riwayat Mutasi Aset')

@section('content')
<div class="p-8" x-data="{ selected: [] }">

    <x-ui.page-header title="Riwayat Mutasi Aset" subtitle="Riwayat perpindahan lokasi seluruh aset.">
        <x-slot:actions>
            <a :href="selected.length > 0
                    ? '{{ route('mutasi.export-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('mutasi.export-pdf', request()->only(['search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor PDF Terpilih (' + selected.length + ')' : 'Ekspor PDF'"></span>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('mutasi.export-csv') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('mutasi.export-csv', request()->only(['search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor CSV Terpilih (' + selected.length + ')' : 'Ekspor CSV'"></span>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form method="GET" action="{{ route('mutasi.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode aset, nama aset atau deskripsi..."
                           class="form-input form-input-sm w-64 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <label class="text-xs font-normal text-gray-500 whitespace-nowrap">Dari:</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="text-xs font-normal text-gray-500 whitespace-nowrap">Sampai:</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('mutasi.index') }}" class="btn-ghost btn-sm">Reset Filter</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-8">
                            <input type="checkbox"
                                   class="checkbox"
                                   :checked="selected.length > 0 && selected.length === {{ $mutasiLogs->count() }}"
                                   @change="selected = ($event.target.checked ? {{ $mutasiLogs->pluck('id')->values() }} : []).map(String)">
                        </th>
                        <th>Aset</th>
                        <th>Deskripsi</th>
                        <th>Petugas</th>
                        <th class="text-center">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasiLogs as $log)
                    <tr>
                        <td>
                            <input type="checkbox" value="{{ $log->id }}" x-model="selected"
                                   class="checkbox">
                        </td>
                        <td>
                            <div class="font-normal">{{ $log->asset->kode_barang ?? '-' }}</div>
                            <div class="text-xs text-secondary">
                                {{ $log->asset->nama_barang ?? 'Aset sudah dihapus' }}
                                @if($log->asset?->trashed())
                                    <span class="text-danger">(Dihapus)</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-secondary text-sm">{{ $log->deskripsi }}</td>
                        <td class="text-secondary text-sm">{{ $log->user->name ?? 'Sistem' }}</td>
                        <td class="text-center text-sm text-secondary">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="map-pin"
                                title="Belum Ada Riwayat Mutasi"
                                description="Belum ada perpindahan lokasi aset yang tercatat." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $mutasiLogs->links() }}
    </div>

</div>
@endsection
