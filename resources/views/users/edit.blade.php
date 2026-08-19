@extends('layouts.app')

@section('title', 'Ubah User')

@section('content')
<div class="page-content-narrow">

    <x-ui.page-header title="Ubah User">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn-ghost btn-sm">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('users.update', $user) }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3>Data User</h3>
            </div>
            <div class="card-body">
                <div class="space-y-5">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input @error('name') is-invalid @enderror" placeholder="Masukkan nama lengkap">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input @error('email') is-invalid @enderror" placeholder="contoh@sekolah.sch.id">
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password <span class="text-xs text-secondary font-normal">(kosongkan jika tidak diubah)</span></label>
                        <input type="password" name="password" class="form-input @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter">
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select name="role" required class="form-input @error('role') is-invalid @enderror">
                            <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }} @disabled($user->isLastAdmin())>Staff (Sarpras/TU)</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin Sarpras</option>
                        </select>
                        @error('role')<p class="form-error">{{ $message }}</p>@enderror
                        @if($user->isLastAdmin())
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            Ini satu-satunya akun Admin, rolenya tidak dapat diturunkan menjadi Staff. Buat Admin lain terlebih dahulu jika ingin mengubahnya.
                        </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">Batal</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">Simpan Perubahan</span>
                        <span x-show="submitting" class="inline-flex items-center gap-2">
                            <svg class="spinner" viewBox="0 0 24 24" fill="none">
                                <circle class="spinner-track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="spinner-fill" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
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
