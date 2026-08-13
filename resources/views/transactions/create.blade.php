@extends('layouts.app')

@section('title', 'Form Peminjaman Barang')

@section('content')
<div class="p-8 max-w-2xl mx-auto">

    <x-ui.page-header title="Form Peminjaman Barang" subtitle="Catat peminjaman asset oleh guru atau siswa.">
        <x-slot:actions>
            <a href="{{ route('transactions.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card">
        <div class="card-header">
            <h3>Data Peminjaman</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('transactions.store') }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf

                <div class="space-y-5">
                    <div class="form-group">
                        <label class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                        <select name="asset_id" required class="form-input @error('asset_id') is-invalid @enderror">
                            <option value="">-- Pilih Barang (Hanya yang Tersedia) --</option>
                            @foreach($assets as $ast)
                                <option value="{{ $ast->id }}" {{ old('asset_id', request('asset_id')) == $ast->id ? 'selected' : '' }}>
                                    {{ $ast->kode_barang }} — {{ $ast->nama_barang }} {{ $ast->merk ? '('.$ast->merk.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('asset_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Peminjam <span class="text-danger">*</span></label>
                        <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}" required class="form-input @error('nama_peminjam') is-invalid @enderror" placeholder="Contoh: Bpk. Budi / Ahmad (XI RPL)">
                        @error('nama_peminjam')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keperluan Penggunaan</label>
                        <input type="text" name="keperluan" value="{{ old('keperluan') }}" class="form-input @error('keperluan') is-invalid @enderror" placeholder="Contoh: Praktikum Jaringan Lab Komputer">
                        @error('keperluan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required class="form-input @error('tanggal_pinjam') is-invalid @enderror">
                        @error('tanggal_pinjam')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('transactions.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" :disabled="submitting" class="btn-primary">
                            <span x-show="!submitting">Proses Pinjam</span>
                            <span x-show="submitting" class="inline-flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
