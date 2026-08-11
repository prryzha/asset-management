@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="p-8 max-w-2xl">

    <x-ui.page-header title="Tambah User" subtitle="Buat akun baru untuk admin atau staff.">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card">
        <div class="card-header">
            <h3>Data User</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf

                <div class="space-y-5">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="form-input @error('name') is-invalid @enderror" placeholder="Masukkan nama lengkap">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="form-input @error('email') is-invalid @enderror" placeholder="contoh@sekolah.sch.id">
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" required class="form-input @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" required class="form-input" placeholder="Ulangi password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" required class="form-input @error('role') is-invalid @enderror">
                            <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff (Guru)</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        @error('role')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
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
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
