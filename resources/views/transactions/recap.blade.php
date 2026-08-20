@extends('layouts.app')

@section('title', 'Rekap Peminjaman')

@section('content')
<div class="page-content">

    <x-ui.page-header title="Rekap Peminjaman" />

    <div class="card overflow-hidden">
        <x-ui.table-heading title="Rekapitulasi Peminjaman">
            <a href="{{ route('transactions.recap-export-pdf', request()->only(['category_id','location_id','status','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="Ekspor PDF" aria-label="Ekspor PDF">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('transactions.recap-export-csv', request()->only(['category_id','location_id','status','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="Ekspor CSV" aria-label="Ekspor CSV">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
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
                <button type="submit" class="btn-primary btn-xs">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('transactions.recap') }}" class="btn-ghost btn-xs">Reset Filter</a>
            </form>
        </div>

        @if($recap['total_transaksi'] === 0)
        <div class="card-body">
            <x-ui.empty-state
                icon="search"
                title="Tidak Ada Data"
                description="Belum ada transaksi peminjaman yang sesuai dengan filter ini." />
        </div>
        @else
        <div class="card-body space-y-6">

            {{-- Ringkasan --}}
            <div>
                <p class="dropdown-section-title">Ringkasan</p>
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Total Peminjaman</td>
                                <td class="text-right font-normal">{{ $recap['total_peminjaman'] }}</td>
                            </tr>
                            <tr>
                                <td>Sedang Dipinjam</td>
                                <td class="text-right font-normal">{{ $recap['sedang_dipinjam'] }}</td>
                            </tr>
                            <tr>
                                <td>Total Pengembalian</td>
                                <td class="text-right font-normal">{{ $recap['total_pengembalian'] }}</td>
                            </tr>
                            <tr>
                                <td>Total Transaksi</td>
                                <td class="text-right font-normal">{{ $recap['total_transaksi'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per Kategori --}}
            <div>
                <p class="dropdown-section-title">Peminjaman per Kategori</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recap['per_kategori'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-right font-normal">{{ $row['jumlah'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per Lokasi --}}
            <div>
                <p class="dropdown-section-title">Peminjaman per Lokasi</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Lokasi</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recap['per_lokasi'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-right font-normal">{{ $row['jumlah'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per Status --}}
            <div>
                <p class="dropdown-section-title">Peminjaman berdasarkan Status</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recap['per_status'] as $row)
                            <tr>
                                <td><x-ui.badge-status :status="$row['label']" /></td>
                                <td class="text-right font-normal">{{ $row['jumlah'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per Bulan --}}
            <div>
                <p class="dropdown-section-title">Peminjaman per Bulan</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recap['per_bulan'] as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-right font-normal">{{ $row['jumlah'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Peminjam Terbanyak --}}
            <div>
                <p class="dropdown-section-title">Peminjam Terbanyak</p>
                <p class="text-xs text-secondary -mt-1.5 mb-2">Maksimal 10 nama teratas. Nama disamakan berdasarkan huruf besar/kecil dan spasi ganda saja, bukan pencocokan otomatis lain.</p>
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
                                <td class="text-right font-normal">{{ $row['jumlah'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        @endif
    </div>

</div>
@endsection
