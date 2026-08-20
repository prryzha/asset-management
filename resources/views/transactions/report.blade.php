@extends('layouts.app')

@section('title', 'Laporan Peminjaman')

@section('content')
<div class="page-content">

    <x-ui.page-header title="Laporan Peminjaman" />

    {{-- Search & Filter + Table --}}
    <div class="card overflow-hidden">
        <x-ui.table-heading title="Daftar Peminjaman">
            <a href="{{ route('transactions.report-export-pdf', request()->only(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="Ekspor PDF" aria-label="Ekspor PDF">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('transactions.report-export-csv', request()->only(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="Ekspor CSV" aria-label="Ekspor CSV">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
            <form action="{{ route('transactions.report') }}" method="GET" class="filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode aset, nama aset atau peminjam..."
                           class="form-input form-input-sm w-64 pl-8">
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
                <select name="status" class="form-input form-input-sm w-auto">
                    <option value="">Semua Status</option>
                    <option value="Dipinjam" {{ request('status')=='Dipinjam'?'selected':'' }}>Dipinjam</option>
                    <option value="Dikembalikan" {{ request('status')=='Dikembalikan'?'selected':'' }}>Dikembalikan</option>
                </select>
                <label class="filter-label">Dari:</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="filter-label">Sampai:</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-xs">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('transactions.report') }}" class="btn-ghost btn-xs">Reset Filter</a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-10">No</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Peminjam</th>
                        <th>Keperluan</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Kembali</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $i => $trx)
                    <tr>
                        <td class="text-secondary">{{ $transactions->firstItem() + $i }}</td>
                        <td>
                            <span class="font-normal">{{ $trx->asset?->kode_barang ?? 'Barang Dihapus' }}</span>
                            <span class="text-xs text-secondary block">{{ $trx->asset?->nama_barang ?? '-' }}</span>
                        </td>
                        <td class="text-secondary">{{ $trx->asset?->category?->nama ?? '-' }}</td>
                        <td class="text-secondary">{{ $trx->asset?->location?->nama ?? '-' }}</td>
                        <td class="font-normal">{{ $trx->nama_peminjam }}</td>
                        <td class="text-secondary">{{ $trx->keperluan ?? '-' }}</td>
                        <td class="text-secondary">{{ \Carbon\Carbon::parse($trx->tanggal_pinjam)->format('d/m/Y') }}</td>
                        <td class="text-secondary">{{ $trx->tanggal_kembali ? \Carbon\Carbon::parse($trx->tanggal_kembali)->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$trx->status_peminjaman" />
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-16">
                            <x-ui.empty-state
                                icon="search"
                                title="Tidak Ada Transaksi"
                                description="Belum ada transaksi peminjaman yang sesuai dengan filter ini." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
