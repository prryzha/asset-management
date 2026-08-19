@extends('layouts.app')

@section('title', 'Ubah Kategori')

@section('content')
<div class="page-content-narrow">

    <x-ui.page-header title="Ubah Kategori" subtitle="Perbarui informasi kategori.">
        <x-slot:actions>
            <a href="{{ route('categories.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('categories.update', $category) }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3>Informasi Kategori</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama', $category->nama) }}" required class="form-input @error('nama') is-invalid @enderror" placeholder="Masukkan nama kategori">
                        @error('nama')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" value="{{ old('kode', $category->kode) }}" maxlength="20" class="form-input uppercase @error('kode') is-invalid @enderror">
                        @error('kode')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('categories.index') }}" class="btn-secondary btn-sm">Batal</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">Simpan Perubahan</span>
                        <span x-show="submitting" class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                            </svg>
                            Menyimpan...
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection
