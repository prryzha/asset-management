@extends('layouts.app')

@section('title', __('ui.locations.tambah_lokasi'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.locations.tambah_lokasi')">
        <x-slot:actions>
            <a href="{{ route('locations.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.kembali') }}" aria-label="{{ __('ui.common.kembali') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('locations.store') }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.locations.informasi_lokasi') }}</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('ui.locations.nama_lokasi') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required class="form-input @error('nama') is-invalid @enderror" placeholder="{{ __('ui.locations.nama_lokasi_placeholder') }}">
                        @error('nama')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('ui.categories.kode') }} <span class="text-xs text-secondary font-normal">{{ __('ui.categories.kode_optional_hint') }}</span></label>
                        <input type="text" name="kode" value="{{ old('kode') }}" maxlength="20" placeholder="{{ __('ui.locations.kode_placeholder') }}" class="form-input uppercase @error('kode') is-invalid @enderror">
                        @error('kode')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.locations.gedung_lantai') }}</label>
                        <input type="text" name="gedung" value="{{ old('gedung') }}" maxlength="100" placeholder="{{ __('ui.locations.gedung_placeholder') }}" class="form-input @error('gedung') is-invalid @enderror">
                        @error('gedung')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('locations.index') }}" class="btn-secondary btn-sm">{{ __('ui.common.batal') }}</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">{{ __('ui.common.simpan') }}</span>
                        <span x-show="submitting" class="inline-flex items-center gap-2">
                            <svg class="spinner" viewBox="0 0 24 24" fill="none">
                                <circle class="spinner-track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="spinner-fill" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            {{ __('ui.common.menyimpan') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection
