@extends('layouts.app')

@section('title', 'Tambah Lokasi')

@section('content')
<div class="p-8 max-w-2xl mx-auto">

    <x-ui.page-header title="Tambah Lokasi" subtitle="Tambahkan lokasi penyimpanan baru.">
        <x-slot:actions>
            <a href="{{ route('locations.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card">
        <div class="card-header">
            <h3>Informasi Lokasi</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('locations.store') }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Nama Lokasi <span class="text-danger">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required class="form-input @error('nama') is-invalid @enderror" placeholder="Masukkan nama lokasi">
                        @error('nama')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode <span class="text-xs text-secondary font-normal">(opsional)</span></label>
                        <input type="text" name="kode" value="{{ old('kode') }}" maxlength="20" placeholder="mis. SRV" class="form-input uppercase @error('kode') is-invalid @enderror">
                        @error('kode')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Gedung / Lantai</label>
                        <input type="text" name="gedung" value="{{ old('gedung') }}" maxlength="100" placeholder="mis. Gedung A Lantai 2" class="form-input @error('gedung') is-invalid @enderror">
                        @error('gedung')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <div class="flex items-center gap-3">
                        <button type="submit" :disabled="submitting" class="btn-primary">
                            <span x-show="!submitting">Simpan</span>
                            <span x-show="submitting" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                        <a href="{{ route('locations.index') }}" class="btn-secondary">Kembali</a>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection