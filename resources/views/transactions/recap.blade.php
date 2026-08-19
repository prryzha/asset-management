@extends('layouts.app')

@section('title', 'Rekap Peminjaman')

@section('content')
<div class="page-content">

    <x-ui.page-header title="Rekap Peminjaman">
        <x-slot:actions>
            <a href="{{ route('transactions.recap-export-pdf', request()->only(['category_id','location_id','status','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-sm">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Ekspor PDF
            </a>
            <a href="{{ route('transactions.recap-export-csv', request()->only(['category_id','location_id','status','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-sm">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                Ekspor CSV
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.report-tabs :tabs="[
        ['label' => 'Riwayat', 'url' => route('transactions.report'), 'active' => false],
        ['label' => 'Rekap', 'url' => route('transactions.recap'), 'active' => true],
    ]" />

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body-compact">
            <form action="{{ route('transactions.recap') }}" method="GET" class="filter-form">
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
                <button type="submit" class="btn-primary btn-sm">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('transactions.recap') }}" class="btn-ghost btn-sm">Reset Filter</a>
            </form>
        </div>
    </div>

    {{-- Ringkasan Utama --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-primary">
                    <svg class="icon-lg text-primary dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Peminjaman</p>
                    <h2 class="stat-value">{{ $recap['total_peminjaman'] }}</h2>
                    <p class="stat-detail">Kejadian peminjaman pada periode ini</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-warning">
                    <svg class="icon-lg text-warning dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Sedang Dipinjam</p>
                    <h2 class="stat-value">{{ $recap['sedang_dipinjam'] }}</h2>
                    <p class="stat-detail">Belum dikembalikan</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-success">
                    <svg class="icon-lg text-success dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Pengembalian</p>
                    <h2 class="stat-value">{{ $recap['total_pengembalian'] }}</h2>
                    <p class="stat-detail">Sudah dikembalikan</p>
                </div>
            </div>
        </div>

        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon stat-icon-neutral">
                    <svg class="icon-lg text-secondary dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Transaksi</p>
                    <h2 class="stat-value">{{ $recap['total_transaksi'] }}</h2>
                    <p class="stat-detail">Seluruh record pada periode ini</p>
                </div>
            </div>
        </div>
    </div>

    @if($recap['total_transaksi'] === 0)
    <div class="card">
        <div class="card-body">
            <x-ui.empty-state
                icon="search"
                title="Tidak Ada Data"
                description="Belum ada transaksi peminjaman yang sesuai dengan filter ini." />
        </div>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Per Kategori --}}
        <div class="card">
            <div class="card-header">
                <h3>Peminjaman per Kategori</h3>
            </div>
            <div class="card-body space-y-4">
                @foreach($recap['per_kategori'] as $row)
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs text-gray-900 dark:text-gray-100">{{ $row['label'] }}</span>
                        <span class="text-xs font-normal">{{ $row['jumlah'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-2">
                        <div class="bg-primary h-2" style="width: {{ $recap['total_transaksi'] > 0 ? ($row['jumlah'] / $recap['total_transaksi']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Per Lokasi --}}
        <div class="card">
            <div class="card-header">
                <h3>Peminjaman per Lokasi</h3>
            </div>
            <div class="card-body space-y-4">
                @foreach($recap['per_lokasi'] as $row)
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs text-gray-900 dark:text-gray-100">{{ $row['label'] }}</span>
                        <span class="text-xs font-normal">{{ $row['jumlah'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-2">
                        <div class="bg-primary h-2" style="width: {{ $recap['total_transaksi'] > 0 ? ($row['jumlah'] / $recap['total_transaksi']) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Per Status --}}
        <div class="card">
            <div class="card-header">
                <h3>Peminjaman berdasarkan Status</h3>
            </div>
            <div class="card-body">
                <div class="flex flex-wrap gap-3">
                    @foreach($recap['per_status'] as $row)
                    <div class="flex items-center gap-2 border border-default rounded-md px-3 py-2">
                        <x-ui.badge-status :status="$row['label']" />
                        <span class="text-xs font-normal">{{ $row['jumlah'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Per Bulan --}}
        <div class="card">
            <div class="card-header">
                <h3>Peminjaman per Bulan</h3>
            </div>
            <div class="card-body space-y-4">
                @foreach($recap['per_bulan'] as $row)
                @php($maxBulan = max(array_column($recap['per_bulan'], 'jumlah')))
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs text-gray-900 dark:text-gray-100">{{ $row['label'] }}</span>
                        <span class="text-xs font-normal">{{ $row['jumlah'] }}</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-2">
                        <div class="bg-primary h-2" style="width: {{ $maxBulan > 0 ? ($row['jumlah'] / $maxBulan) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Peminjam Terbanyak --}}
    <div class="card overflow-hidden">
        <div class="card-header">
            <div>
                <h3>Peminjam Terbanyak</h3>
                <p class="text-xs text-secondary mt-0.5">Maksimal 10 nama teratas. Nama disamakan berdasarkan huruf besar/kecil dan spasi ganda saja, bukan pencocokan otomatis lain.</p>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-10">No</th>
                        <th>Nama Peminjam</th>
                        <th class="text-right">Jumlah Peminjaman</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recap['top_peminjam'] as $i => $row)
                    <tr>
                        <td class="text-xs text-secondary">{{ $i + 1 }}</td>
                        <td class="font-normal">{{ $row['nama'] }}</td>
                        <td class="text-right">{{ $row['jumlah'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
