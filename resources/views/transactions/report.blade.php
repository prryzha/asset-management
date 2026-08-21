@extends('layouts.app')

@section('title', __('ui.reports.peminjaman_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.reports.peminjaman_title')" />

    {{-- Search & Filter + Table --}}
    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.reports.daftar_peminjaman')">
            <a href="{{ route('transactions.report-export-pdf', request()->only(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="{{ __('ui.common.ekspor_pdf') }}" aria-label="{{ __('ui.common.ekspor_pdf') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a href="{{ route('transactions.report-export-csv', request()->only(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai'])) }}"
               class="btn-secondary btn-icon" title="{{ __('ui.common.ekspor_csv') }}" aria-label="{{ __('ui.common.ekspor_csv') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
            <form action="{{ route('transactions.report') }}" method="GET" class="filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('ui.reports.search_placeholder_peminjaman') }}"
                           class="form-input form-input-sm w-64 pl-8">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
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
                <button type="submit" class="btn-primary btn-icon" title="{{ __('ui.common.cari') }}" aria-label="{{ __('ui.common.cari') }}">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </button>
                @if(request()->hasAny(['search','status','category_id','location_id','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('transactions.report') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.reset_filter') }}" aria-label="{{ __('ui.common.reset_filter') }}"><svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-10">{{ __('ui.reports.no') }}</th>
                        <th>{{ __('ui.reports.barang') }}</th>
                        <th>{{ __('ui.reports.kategori') }}</th>
                        <th>{{ __('ui.reports.lokasi') }}</th>
                        <th>{{ __('ui.reports.peminjam') }}</th>
                        <th>{{ __('ui.reports.keperluan') }}</th>
                        <th>{{ __('ui.reports.tgl_pinjam') }}</th>
                        <th>{{ __('ui.reports.tgl_kembali') }}</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $i => $trx)
                    <tr>
                        <td class="text-secondary">{{ $transactions->firstItem() + $i }}</td>
                        <td>
                            <span class="font-normal">{{ $trx->asset?->kode_barang ?? __('ui.reports.barang_dihapus') }}</span>
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
                                :title="__('ui.reports.empty_peminjaman_title')"
                                :description="__('ui.reports.empty_peminjaman_desc')" />
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
