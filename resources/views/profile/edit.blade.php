@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="p-8 max-w-3xl mx-auto">

    <x-ui.page-header title="Profile" subtitle="Kelola informasi akun anda." />

    @if(session('status') === 'profile-updated')
        <div class="alert alert-success mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Profile berhasil diperbarui.
        </div>
    @endif

    @if(session('status') === 'password-updated')
        <div class="alert alert-success mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Password berhasil diubah.
        </div>
    @endif

    <div class="space-y-6">

        {{-- Profile Information --}}
        <div class="card">
            <div class="card-header">
                <h3>Informasi Profile</h3>
                <p class="text-xs text-secondary mt-0.5">Perbarui informasi akun dan email anda.</p>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf @method('patch')
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="form-input @error('name') is-invalid @enderror">
                            @error('name')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="form-input @error('email') is-invalid @enderror">
                            @error('email')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="card-footer -mx-6 -mb-6 mt-6">
                        <div class="flex items-center gap-4">
                            <button type="submit" class="btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Update Password --}}
        <div class="card">
            <div class="card-header">
                <h3>Ubah Password</h3>
                <p class="text-xs text-secondary mt-0.5">Pastikan akun menggunakan password yang kuat.</p>
            </div>
            <div class="card-body">
                <form method="post" action="{{ route('password.update') }}">
                    @csrf @method('put')
                    <div class="space-y-5">
                        <div class="form-group">
                            <label class="form-label">Password Saat Ini</label>
                            <input type="password" name="current_password" required autocomplete="current-password" class="form-input @error('current_password', 'updatePassword') is-invalid @enderror">
                            @error('current_password', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" required autocomplete="new-password" class="form-input @error('password', 'updatePassword') is-invalid @enderror">
                            @error('password', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-input @error('password_confirmation', 'updatePassword') is-invalid @enderror">
                            @error('password_confirmation', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="card-footer -mx-6 -mb-6 mt-6">
                        <div class="flex items-center gap-4">
                            <button type="submit" class="btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Account --}}
        <div class="card border border-danger">
            <div class="card-header">
                <h3 class="text-danger">Hapus Akun</h3>
                <p class="text-xs text-secondary mt-0.5">Setelah akun dihapus, semua data akan dihapus secara permanen.</p>
            </div>
            <div class="card-body">
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="btn-danger">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Akun
                </button>
            </div>
        </div>

    </div>

</div>

{{-- Delete Confirmation Modal --}}
<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <div class="p-6">
        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf @method('delete')

            <div class="card-header -mx-6 -mt-6 mb-4">
                <h3>Yakin ingin menghapus akun?</h3>
            </div>

            <p class="text-sm text-secondary mb-6">Masukkan password untuk konfirmasi penghapusan akun.</p>

            <div class="form-group mb-6">
                <label class="form-label sr-only">Password</label>
                <input type="password" name="password" placeholder="Password" class="form-input @error('password', 'userDeletion') is-invalid @enderror">
                @error('password', 'userDeletion')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn-secondary">Batal</button>
                <button class="btn-danger">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Akun
                </button>
            </div>

        </form>
    </div>
</x-modal>
@endsection
