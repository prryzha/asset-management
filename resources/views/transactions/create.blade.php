@extends('layouts.app')

@section('title', __('ui.transactions.form_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.transactions.form_title')">
        <x-slot:actions>
            <a href="{{ route('transactions.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.kembali') }}" aria-label="{{ __('ui.common.kembali') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('transactions.store') }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.transactions.data_peminjaman') }}</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.transactions.pilih_barang') }} <span class="text-danger">*</span></label>
                        <select name="asset_id" required class="form-input @error('asset_id') is-invalid @enderror">
                            <option value="">{{ __('ui.transactions.pilih_barang_placeholder') }}</option>
                            @foreach($assets as $ast)
                                <option value="{{ $ast->id }}" {{ old('asset_id', request('asset_id')) == $ast->id ? 'selected' : '' }}>
                                    {{ $ast->kode_barang }} — {{ $ast->nama_barang }} {{ $ast->merk ? '('.$ast->merk.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('asset_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.transactions.nama_peminjam') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}" required class="form-input @error('nama_peminjam') is-invalid @enderror" placeholder="{{ __('ui.transactions.nama_peminjam_placeholder') }}">
                        @error('nama_peminjam')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.transactions.tanggal_pinjam') }} <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required class="form-input @error('tanggal_pinjam') is-invalid @enderror">
                        @error('tanggal_pinjam')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.transactions.keperluan_penggunaan') }}</label>
                        <input type="text" name="keperluan" value="{{ old('keperluan') }}" class="form-input @error('keperluan') is-invalid @enderror" placeholder="{{ __('ui.transactions.keperluan_placeholder') }}">
                        @error('keperluan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('transactions.index') }}" class="btn-secondary btn-sm">{{ __('ui.common.batal') }}</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">{{ __('ui.transactions.proses_pinjam') }}</span>
                        <span x-show="submitting" class="inline-flex items-center gap-2">
                            <svg class="spinner" viewBox="0 0 24 24" fill="none">
                                <circle class="spinner-track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="spinner-fill" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            {{ __('ui.transactions.memproses') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection
