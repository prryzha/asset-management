@extends('layouts.app')

@section('title', __('ui.mutasi.title'))

@section('content')
<div class="page-content" x-data="{ selected: [] }">

    <x-ui.page-header :title="__('ui.mutasi.title')" />

    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.mutasi.daftar_mutasi')">
            <a :href="selected.length > 0
                    ? '{{ route('mutasi.export-pdf') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('mutasi.export-pdf', request()->only(['search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-icon"
               :title="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_pdf_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_pdf')) }}"
               :aria-label="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_pdf_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_pdf')) }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </a>
            <a :href="selected.length > 0
                    ? '{{ route('mutasi.export-csv') }}?' + selected.map(id => 'ids[]=' + id).join('&')
                    : '{{ route('mutasi.export-csv', request()->only(['search','tanggal_dari','tanggal_sampai'])) }}'"
               class="btn-secondary btn-icon"
               :title="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_csv_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_csv')) }}"
               :aria-label="selected.length > 0 ? {{ \Illuminate\Support\Js::from(__('ui.assets.ekspor_csv_terpilih', ['count' => ''])) }}.replace('()', '(' + selected.length + ')') : {{ \Illuminate\Support\Js::from(__('ui.common.ekspor_csv')) }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15v4a2 2 0 002 2h14a2 2 0 002-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            </a>
        </x-ui.table-heading>

        <div class="card-body-compact border-b border-default">
            <form method="GET" action="{{ route('mutasi.index') }}" class="filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('ui.mutasi.search_placeholder') }}"
                           class="form-input form-input-sm w-64 pl-8">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <label class="filter-label">{{ __('ui.transactions.dari') }}</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="filter-label">{{ __('ui.transactions.sampai') }}</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-icon" title="{{ __('ui.common.cari') }}" aria-label="{{ __('ui.common.cari') }}">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </button>
                @if(request()->hasAny(['search','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('mutasi.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.reset_filter') }}" aria-label="{{ __('ui.common.reset_filter') }}"><svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></a>
                @endif
            </form>
        </div>

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
                        <th>{{ __('ui.mutasi.aset') }}</th>
                        <th>{{ __('ui.mutasi.deskripsi') }}</th>
                        <th>{{ __('ui.mutasi.petugas') }}</th>
                        <th class="text-center">{{ __('ui.mutasi.tanggal') }}</th>
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
                                {{ $log->asset->nama_barang ?? __('ui.maintenance.aset_dihapus') }}
                                @if($log->asset?->trashed())
                                    <span class="text-danger">{{ __('ui.maintenance.dihapus') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-secondary text-xs">{{ $log->deskripsi }}</td>
                        <td class="text-secondary text-xs">{{ $log->user->name ?? __('ui.mutasi.sistem') }}</td>
                        <td class="text-center text-xs text-secondary">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="map-pin"
                                :title="__('ui.mutasi.empty_title')"
                                :description="__('ui.mutasi.empty_desc')" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mutasiLogs->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $mutasiLogs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
