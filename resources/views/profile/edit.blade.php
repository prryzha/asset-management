@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="p-8 max-w-3xl mx-auto">

    <x-ui.page-header title="Profil" />

    @if(session('status') === 'profile-updated')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Nama berhasil diperbarui.
        </div>
    @endif

    @if(session('status') === 'password-updated')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Password berhasil diubah.
        </div>
    @endif

    @if(session('status') === 'email-change-requested')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Link verifikasi telah dikirim ke email baru Anda. Email aktif Anda <strong>belum berubah</strong> sampai link tersebut dibuka dan diverifikasi.
        </div>
    @endif

    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Link verifikasi telah dikirim ke email Anda.
        </div>
    @endif

    @if(session('status') === 'email-already-verified')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Email Anda sudah terverifikasi.
        </div>
    @endif

    <div class="space-y-6">

        {{-- ============ PROFIL ============ --}}
        <p class="text-xs font-normal text-secondary tracking-wider">Profil</p>

        {{-- Informasi Dasar --}}
        <div class="card">
            <div class="card-header">
                <h3>Informasi Dasar</h3>
                <p class="text-xs text-secondary mt-0.5">Nama, email aktif, dan peran akun Anda.</p>
            </div>
            <div class="card-body space-y-5">

                <form method="post" action="{{ route('profile.update') }}" id="form-nama">
                    @csrf @method('patch')
                    <div class="form-group mb-0">
                        <label class="form-label">Nama</label>
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="form-input @error('name') is-invalid @enderror">
                                @error('name')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" class="btn-primary btn-sm shrink-0">Simpan</button>
                        </div>
                    </div>
                </form>

                <div class="pt-5 border-t border-default">
                    <label class="form-label">Email Aktif</label>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="text-xs text-gray-900 dark:text-gray-100 font-normal">{{ $user->email }}</span>
                        @if($user->hasVerifiedEmail())
                            <span class="badge-green gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span>
                                Terverifikasi
                            </span>
                        @else
                            <span class="badge-yellow gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-warning-500"></span>
                                Belum Terverifikasi
                            </span>
                            <form method="post" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="text-xs font-normal text-primary hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                                    Kirim Ulang Verifikasi
                                </button>
                            </form>
                        @endif
                    </div>
                    <p class="text-xs text-secondary mt-2">Untuk mengubah alamat email, gunakan form "Ubah Email" di bawah.</p>
                </div>

                <div class="pt-5 border-t border-default">
                    <label class="form-label">Role</label>
                    <p class="text-xs text-gray-900 dark:text-gray-100 font-normal">{{ ucfirst($user->role) }}</p>
                    <p class="text-xs text-secondary mt-1">Role hanya dapat diubah oleh Admin lewat Manajemen User.</p>
                </div>

            </div>
        </div>

        {{-- Ubah Email --}}
        <div class="card">
            <div class="card-header">
                <h3>Ubah Email</h3>
                <p class="text-xs text-secondary mt-0.5">Email aktif tidak akan berubah sampai email baru diverifikasi.</p>
            </div>
            <form method="post" action="{{ route('profile.email.update') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label class="form-label">Email Baru</label>
                        <input type="email" name="new_email" value="{{ old('new_email') }}" placeholder="email-baru@contoh.com" required autocomplete="email" class="form-input @error('new_email', 'changeEmail') is-invalid @enderror">
                        @error('new_email', 'changeEmail')<p class="form-error">{{ $message }}</p>@enderror
                        <p class="text-xs text-secondary mt-2">Link verifikasi akan dikirim ke alamat ini. Email aktif Anda saat ini ({{ $user->email }}) tetap berlaku sampai link tersebut diverifikasi.</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-primary btn-sm">Kirim Link Verifikasi</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ============ KEAMANAN AKUN ============ --}}
        <p class="text-xs font-normal text-secondary tracking-wider pt-2">Keamanan Akun</p>

        {{-- Ubah Password --}}
        <div class="card">
            <div class="card-header">
                <h3>Ubah Password</h3>
                <p class="text-xs text-secondary mt-0.5">Pastikan akun menggunakan password yang kuat.</p>
            </div>
            <form method="post" action="{{ route('password.update') }}">
                @csrf @method('put')
                <div class="card-body">
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
                </div>
                <div class="card-footer">
                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-primary btn-sm">Simpan</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Delete Account --}}
        <div class="card border border-danger">
            <div class="card-header">
                <h3 class="text-danger">Hapus Akun</h3>
                <p class="text-xs text-secondary mt-0.5">Setelah akun dihapus, semua data akan dihapus secara permanen.</p>
            </div>
            <div class="card-body">
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="btn-danger">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

            <p class="text-xs text-secondary mb-6">Masukkan password untuk konfirmasi penghapusan akun.</p>

            <div class="form-group mb-6">
                <label class="form-label sr-only">Password</label>
                <input type="password" name="password" placeholder="Password" class="form-input @error('password', 'userDeletion') is-invalid @enderror">
                @error('password', 'userDeletion')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn-secondary">Batal</button>
                <button class="btn-danger">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus Akun
                </button>
            </div>

        </form>
    </div>
</x-modal>
@endsection
