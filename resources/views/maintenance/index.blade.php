@extends('layouts.app')

@section('title', 'Manajemen Perawatan')

@section('content')
<div class="p-8" x-data="{ selected: [] }">

    <x-ui.page-header title="Manajemen Perawatan" subtitle="Kelola jadwal perawatan seluruh aset.">
        <x-slot:actions>
            <a :href="selected.length > 0
                    ? '{{ route('maintenance.export-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('maintenance.export-pdf', request()->only(['status','search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor PDF Terpilih (' + selected.length + ')' : 'Ekspor PDF'"></span>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('maintenance.export-csv') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('maintenance.export-csv', request()->only(['status','search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor CSV Terpilih (' + selected.length + ')' : 'Ekspor CSV'"></span>
            </a>
            <a href="{{ route('maintenance.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Jadwalkan Perawatan
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form method="GET" action="{{ route('maintenance.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode aset, nama aset atau jenis..."
                           class="form-input form-input-sm w-56 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="status" class="form-input form-input-sm w-auto">
                    <option value="">Semua Status</option>
                    <option value="Dijadwalkan" {{ request('status')=='Dijadwalkan'?'selected':'' }}>Dijadwalkan</option>
                    <option value="Dikerjakan" {{ request('status')=='Dikerjakan'?'selected':'' }}>Dikerjakan</option>
                    <option value="Selesai" {{ request('status')=='Selesai'?'selected':'' }}>Selesai</option>
                    <option value="Dibatalkan" {{ request('status')=='Dibatalkan'?'selected':'' }}>Dibatalkan</option>
                </select>
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
                @if(request()->hasAny(['search','status','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('maintenance.index') }}" class="btn-ghost btn-sm">Reset Filter</a>
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
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600"
                                   :checked="selected.length > 0 && selected.length === {{ $maintenanceSchedules->count() }}"
                                   @change="selected = ($event.target.checked ? {{ $maintenanceSchedules->pluck('id')->values() }} : []).map(String)">
                        </th>
                        <th>Aset</th>
                        <th>Jenis Perawatan</th>
                        <th class="text-center">Jadwal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenanceSchedules as $maintenance)
                    <tr>
                        <td>
                            <input type="checkbox" value="{{ $maintenance->id }}" x-model="selected"
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                        </td>
                        <td>
                            <div class="font-normal">{{ $maintenance->asset->kode_barang ?? '-' }}</div>
                            <div class="text-xs text-secondary">
                                {{ $maintenance->asset->nama_barang ?? 'Aset sudah dihapus' }}
                                @if($maintenance->asset?->trashed())
                                    <span class="text-danger">(Dihapus)</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $maintenance->jenis_perawatan }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($maintenance->tanggal_jadwal)->format('d M Y') }}</td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$maintenance->status"/>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                @if($maintenance->status=='Dijadwalkan')
                                    <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Ubah
                                    </a>
                                    <form action="{{ route('maintenance.start', $maintenance) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-success btn-sm px-2 py-1 text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            </svg>
                                            Mulai
                                        </button>
                                    </form>
                                    <form action="{{ route('maintenance.cancel', $maintenance) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Batalkan perawatan ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-ghost btn-sm px-2 py-1 text-xs text-danger hover:text-white hover:bg-danger">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Batal
                                        </button>
                                    </form>
                                @elseif($maintenance->status=='Dikerjakan')
                                    <a href="{{ route('maintenance.complete-form', $maintenance) }}" class="btn-primary btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Selesaikan
                                    </a>
                                @else
                                    <span class="text-xs text-secondary">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <x-ui.empty-state
                                icon="tool"
                                title="Belum Ada Jadwal Perawatan"
                                description="Belum ada jadwal perawatan. Silakan buat jadwal baru." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $maintenanceSchedules->links() }}
    </div>

</div>
@endsection