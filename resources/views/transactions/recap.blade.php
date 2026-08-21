@extends('layouts.app')

@section('title', __('ui.reports.rekap_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.reports.rekap_title')" />

    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.reports.rekapitulasi_peminjaman')">
            <a href="{{ route('transactions.recap-export-pdf', request()->only(['category_id','location_id','status','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="{{ __('ui.common.ekspor_pdf') }}" aria-label="{{ __('ui.common.ekspor_pdf') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('transactions.recap-export-csv', request()->only(['category_id','location_id','status','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="{{ __('ui.common.ekspor_csv') }}" aria-label="{{ __('ui.common.ekspor_csv') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
            <form action="{{ route('transactions.recap') }}" method="GET" class="filter-form">
                <select name="category_id" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.assets.semua_kategori') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nama }}</option>
                    @endforeach
                </select>
                <select name="location_id" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.assets.semua_lokasi') }}</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->nama }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.transactions.semua_status') }}</option>
                    <option value="Dipinjam" {{ request('status')=='Dipinjam'?'selected':'' }}>{{ __('ui.status.Dipinjam') }}</option>
                    <option value="Dikembalikan" {{ request('status')=='Dikembalikan'?'selected':'' }}>{{ __('ui.status.Dikembalikan') }}</option>
                </select>
                <label class="filter-label">{{ __('ui.transactions.dari') }}</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="filter-label">{{ __('ui.transactions.sampai') }}</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-icon" title="{{ __('ui.reports.terapkan_filter') }}" aria-label="{{ __('ui.reports.terapkan_filter') }}">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
                <a href="{{ route('transactions.recap') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.reset_filter') }}" aria-label="{{ __('ui.common.reset_filter') }}"><svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></a>
            </form>
        </div>

        @if($recap['total_transaksi'] === 0)
        <div class="card-body">
            <x-ui.empty-state
                icon="search"
                :title="__('ui.reports.tidak_ada_data')"
                :description="__('ui.reports.empty_rekap_desc')" />
        </div>
        @else
        <div class="card-body space-y-6">

            {{-- Ringkasan --}}
            <div>
                <p class="dropdown-section-title">{{ __('ui.reports.ringkasan') }}</p>
                <div class="table-container">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>{{ __('ui.reports.total_peminjaman') }}</td>
                                <td class="text-right font-normal">{{ $recap['total_peminjaman'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('ui.reports.sedang_dipinjam') }}</td>
                                <td class="text-right font-normal">{{ $recap['sedang_dipinjam'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('ui.reports.total_pengembalian') }}</td>
                                <td class="text-right font-normal">{{ $recap['total_pengembalian'] }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('ui.reports.total_transaksi') }}</td>
                                <td class="text-right font-normal">{{ $recap['total_transaksi'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Per Kategori --}}
            <div>
                <p class="dropdown-section-title">{{ __('ui.reports.peminjaman_per_kategori') }}</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.reports.kategori') }}</th>
                                <th class="text-right">{{ __('ui.reports.jumlah') }}</th>
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
                <p class="dropdown-section-title">{{ __('ui.reports.peminjaman_per_lokasi') }}</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.reports.lokasi') }}</th>
                                <th class="text-right">{{ __('ui.reports.jumlah') }}</th>
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
                <p class="dropdown-section-title">{{ __('ui.reports.peminjaman_per_status') }}</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-right">{{ __('ui.reports.jumlah') }}</th>
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
                <p class="dropdown-section-title">{{ __('ui.reports.peminjaman_per_bulan') }}</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('ui.reports.bulan') }}</th>
                                <th class="text-right">{{ __('ui.reports.jumlah') }}</th>
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
                <p class="dropdown-section-title">{{ __('ui.reports.peminjam_terbanyak') }}</p>
                <p class="text-xs text-secondary -mt-1.5 mb-2">{{ __('ui.reports.peminjam_terbanyak_desc') }}</p>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="w-10">{{ __('ui.reports.no') }}</th>
                                <th>{{ __('ui.reports.nama_peminjam') }}</th>
                                <th class="text-right">{{ __('ui.reports.jumlah_peminjaman') }}</th>
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
