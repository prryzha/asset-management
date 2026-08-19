@extends('layouts.app')

@section('title', 'Laporan Peminjaman')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Laporan Peminjaman" subtitle="Laporan administratif peminjaman aset berdasarkan data transaksi yang tercatat. Laporan ini hanya membaca data, tidak mengubah status aset maupun transaksi.">
        <x-slot:actions>
            <a href="{{ route('transactions.report-export-pdf', request()->only(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Ekspor PDF
            </a>
            <a href="{{ route('transactions.report-export-csv', request()->only(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Ekspor CSV
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.report-tabs :tabs="[
        ['label' => 'Riwayat', 'url' => route('transactions.report'), 'active' => true],
        ['label' => 'Rekap', 'url' => route('transactions.recap'), 'active' => false],
    ]" />

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-primary-50 dark:bg-primary-900/30">
                    <svg class="w-5 h-5 text-primary dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Peminjaman</p>
                    <h2 class="stat-value">{{ $stats->total_peminjaman }}</h2>
                    <p class="stat-detail">Kejadian peminjaman tercatat</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-warning-50 dark:bg-amber-900/30">
                    <svg class="w-5 h-5 text-warning dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Sedang Dipinjam</p>
                    <h2 class="stat-value">{{ $stats->sedang_dipinjam }}</h2>
                    <p class="stat-detail">Transaksi berstatus Dipinjam</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-success-50 dark:bg-emerald-900/30">
                    <svg class="w-5 h-5 text-success dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Pengembalian</p>
                    <h2 class="stat-value">{{ $stats->total_pengembalian }}</h2>
                    <p class="stat-detail">Transaksi berstatus Dikembalikan</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-gray-100 dark:bg-gray-700">
                    <svg class="w-5 h-5 text-secondary dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Transaksi</p>
                    <h2 class="stat-value">{{ $stats->total_transaksi }}</h2>
                    <p class="stat-detail">Seluruh record transaksi</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form action="{{ route('transactions.report') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Kode aset, nama aset atau peminjam..."
                           class="form-input form-input-sm w-64 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                @if(request()->hasAny(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('transactions.report') }}" class="btn-ghost btn-sm">Reset Filter</a>
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
                        <td class="text-sm text-secondary">{{ $transactions->firstItem() + $i }}</td>
                        <td>
                            <span class="font-normal">{{ $trx->asset?->kode_barang ?? 'Barang Dihapus' }}</span>
                            <span class="text-xs text-secondary block">{{ $trx->asset?->nama_barang ?? '-' }}</span>
                        </td>
                        <td class="text-secondary text-sm">{{ $trx->asset?->category?->nama ?? '-' }}</td>
                        <td class="text-secondary text-sm">{{ $trx->asset?->location?->nama ?? '-' }}</td>
                        <td class="font-normal">{{ $trx->nama_peminjam }}</td>
                        <td class="text-secondary text-sm">{{ $trx->keperluan ?? '-' }}</td>
                        <td class="text-sm text-secondary">{{ \Carbon\Carbon::parse($trx->tanggal_pinjam)->format('d/m/Y') }}</td>
                        <td class="text-sm text-secondary">{{ $trx->tanggal_kembali ? \Carbon\Carbon::parse($trx->tanggal_kembali)->format('d/m/Y') : '-' }}</td>
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
        <div class="px-5 py-3 border-t border-[#E5E7EB] dark:border-gray-700">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
