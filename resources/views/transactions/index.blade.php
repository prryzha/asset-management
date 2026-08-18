@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="p-8" x-data="{ selected: [] }">

    <x-ui.page-header title="Riwayat Peminjaman Aset" subtitle="Kelola barang yang sedang dipinjam oleh guru atau siswa.">
        <x-slot:actions>
            <a :href="selected.length > 0
                    ? '{{ route('transactions.export-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('transactions.export-pdf', request()->only(['status','search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor PDF Terpilih (' + selected.length + ')' : 'Ekspor PDF'"></span>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('transactions.export-csv') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('transactions.export-csv', request()->only(['status','search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                <span x-text="selected.length > 0 ? 'Ekspor CSV Terpilih (' + selected.length + ')' : 'Ekspor CSV'"></span>
            </a>
            <a href="{{ route('transactions.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Catat Peminjaman Baru
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Nama peminjam atau kode aset..."
                           class="form-input form-input-sm w-56 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="status" class="form-input form-input-sm w-auto">
                    <option value="">Semua Status</option>
                    <option value="Dipinjam" {{ request('status')=='Dipinjam'?'selected':'' }}>Dipinjam</option>
                    <option value="Dikembalikan" {{ request('status')=='Dikembalikan'?'selected':'' }}>Dikembalikan</option>
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
                <a href="{{ route('transactions.index') }}" class="btn-ghost btn-sm">Reset Filter</a>
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
                                   :checked="selected.length > 0 && selected.length === {{ $transactions->count() }}"
                                   @change="selected = ($event.target.checked ? {{ $transactions->pluck('id')->values() }} : []).map(String)">
                        </th>
                        <th>Barang</th>
                        <th>Peminjam</th>
                        <th>Keperluan</th>
                        <th>Tgl Pinjam</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                    <tr>
                        <td>
                            <input type="checkbox" value="{{ $trx->id }}" x-model="selected"
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600">
                        </td>
                        <td>
                            <span class="font-normal">{{ $trx->asset->kode_barang ?? 'Barang Dihapus' }}</span>
                            <span class="text-xs text-secondary block">{{ $trx->asset->nama_barang ?? '-' }}</span>
                        </td>
                        <td class="font-normal">{{ $trx->nama_peminjam }}</td>
                        <td class="text-secondary text-sm">{{ $trx->keperluan ?? '-' }}</td>
                        <td class="text-sm text-secondary">{{ \Carbon\Carbon::parse($trx->tanggal_pinjam)->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$trx->status_peminjaman" />
                        </td>
                        <td class="text-right">
                            @if($trx->status_peminjaman == 'Dipinjam')
                                <form action="{{ route('transactions.return', $trx) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-success btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        Terima Pengembalian
                                    </button>
                                </form>
                            @elseif($trx->status_peminjaman == 'Dikembalikan')
                                <span class="text-sm text-secondary">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-16">
                            <x-ui.empty-state
                                icon="refresh-cw"
                                title="Belum Ada Riwayat Peminjaman"
                                description="Belum ada riwayat peminjaman barang." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-5 py-3 border-t border-[#E5E7EB] dark:border-gray-700">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>

@endsection
