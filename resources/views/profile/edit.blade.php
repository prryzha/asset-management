@extends('layouts.app')

@section('title', __('ui.profile.title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.profile.title')" />

    @if(session('status') === 'profile-updated')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('ui.profile.status_updated') }}
        </div>
    @endif

    @if(session('status') === 'password-updated')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('ui.profile.status_password_updated') }}
        </div>
    @endif

    @if(session('status') === 'email-change-requested')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {!! __('ui.profile.status_email_change_requested') !!}
        </div>
    @endif

    @if(session('status') === 'verification-link-sent')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('ui.profile.status_verification_link_sent') }}
        </div>
    @endif

    @if(session('status') === 'email-already-verified')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('ui.profile.status_email_already_verified') }}
        </div>
    @endif

    <div class="space-y-6">

        {{-- ============ PROFIL ============ --}}
        <p class="text-xs font-normal text-secondary tracking-wider">{{ __('ui.profile.section_profil') }}</p>

        {{-- Informasi Profil --}}
        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf @method('patch')
            <div class="card">
                <div class="card-header">
                    <h3>{{ __('ui.profile.informasi_profil') }}</h3>
                    <p class="text-xs text-secondary mt-0.5">{{ __('ui.profile.informasi_profil_desc') }}</p>
                </div>
                <div class="card-body space-y-4">

                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('ui.profile.foto_profil') }}</label>
                        <div class="flex items-center gap-4">
                            <div id="foto_profil_preview" class="w-16 h-16 rounded-full overflow-hidden flex-shrink-0 flex items-center justify-center bg-primary text-white {{ $user->foto_profil ? '' : 'text-xs' }}">
                                @if($user->foto_profil)
                                    <img id="foto_profil_preview_img" src="{{ asset('storage/'.$user->foto_profil) }}" class="w-full h-full object-cover">
                                @else
                                    <img id="foto_profil_preview_img" class="w-full h-full object-cover hidden">
                                    <span id="foto_profil_initial">{{ substr($user->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-1">
                                <input type="file" name="foto_profil" id="foto_profil_input" accept="image/*" class="form-input @error('foto_profil') is-invalid @enderror">
                                <p class="text-xs text-secondary mt-1.5">{{ __('ui.profile.foto_format_hint') }}</p>
                                @error('foto_profil')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0 pt-5 border-t border-default">
                        <label class="form-label">{{ __('ui.profile.nama_lengkap') }}</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" class="form-input @error('name') is-invalid @enderror">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group mb-0 pt-5 border-t border-default">
                        <label class="form-label">{{ __('ui.profile.nama_pengguna') }}</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" autocomplete="username" placeholder="cth. budi_santoso" class="form-input @error('username') is-invalid @enderror">
                        <p class="text-xs text-secondary mt-1.5">{{ __('ui.profile.username_hint') }}</p>
                        @error('username')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-5 border-t border-default">
                        <label class="form-label">{{ __('ui.profile.email_aktif') }}</label>
                        <div class="flex flex-wrap items-center gap-2.5">
                            <span class="text-xs text-gray-900 dark:text-gray-100 font-normal">{{ $user->email }}</span>
                            @if($user->hasVerifiedEmail())
                                <span class="badge-green gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span>
                                    {{ __('ui.profile.terverifikasi') }}
                                </span>
                            @else
                                <span class="badge-yellow gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-warning-500"></span>
                                    {{ __('ui.profile.belum_terverifikasi') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-secondary mt-2">{{ __('ui.profile.ubah_email_hint') }}</p>
                    </div>

                    <div class="pt-5 border-t border-default">
                        <label class="form-label">{{ __('ui.profile.peran') }}</label>
                        <p class="text-xs text-gray-900 dark:text-gray-100 font-normal">{{ ucfirst($user->role) }}</p>
                        <p class="text-xs text-secondary mt-1">{{ __('ui.profile.peran_hint') }}</p>
                    </div>

                </div>
                <div class="card-footer">
                    <div class="flex items-center justify-end gap-3">
                        <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                            <span x-show="!submitting">{{ __('ui.profile.simpan_perubahan_profil') }}</span>
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

        {{-- Kirim Ulang Verifikasi Email — dipisah dari card Informasi Profil
             supaya form utama (Nama+Foto) tidak ikut ke-submit saat tombol
             ini diklik (form tidak boleh nested di dalam form). --}}
        @unless($user->hasVerifiedEmail())
        <form method="post" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="text-xs font-normal text-primary hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                {{ __('ui.profile.kirim_ulang_verifikasi_email') }}
            </button>
        </form>
        @endunless

        {{-- Ubah Email --}}
        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.profile.ubah_email') }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.profile.ubah_email_desc') }}</p>
            </div>
            <form method="post" action="{{ route('profile.email.update') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label class="form-label">{{ __('ui.profile.email_baru') }}</label>
                        <input type="email" name="new_email" value="{{ old('new_email') }}" placeholder="email-baru@contoh.com" required autocomplete="email" class="form-input @error('new_email', 'changeEmail') is-invalid @enderror">
                        @error('new_email', 'changeEmail')<p class="form-error">{{ $message }}</p>@enderror
                        <p class="text-xs text-secondary mt-2">{{ __('ui.profile.email_baru_hint', ['email' => $user->email]) }}</p>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-primary btn-sm">{{ __('ui.profile.kirim_link_verifikasi') }}</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- ============ KEAMANAN AKUN ============ --}}
        <p class="text-xs font-normal text-secondary tracking-wider pt-2">{{ __('ui.profile.section_keamanan_akun') }}</p>

        {{-- Ubah Kata Sandi --}}
        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.profile.ubah_kata_sandi') }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.profile.ubah_kata_sandi_desc') }}</p>
            </div>
            <form method="post" action="{{ route('password.update') }}">
                @csrf @method('put')
                <div class="card-body">
                    <div class="space-y-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('ui.profile.kata_sandi_saat_ini') }}</label>
                            <input type="password" name="current_password" required autocomplete="current-password" class="form-input @error('current_password', 'updatePassword') is-invalid @enderror">
                            @error('current_password', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('ui.profile.kata_sandi_baru') }}</label>
                            <input type="password" name="password" required autocomplete="new-password" class="form-input @error('password', 'updatePassword') is-invalid @enderror">
                            @error('password', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('ui.profile.konfirmasi_kata_sandi_baru') }}</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-input @error('password_confirmation', 'updatePassword') is-invalid @enderror">
                            @error('password_confirmation', 'updatePassword')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="flex items-center gap-4">
                        <button type="submit" class="btn-primary btn-sm">{{ __('ui.profile.ubah_kata_sandi') }}</button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Delete Account --}}
        <div class="card border border-danger">
            <div class="card-header">
                <h3 class="text-danger">{{ __('ui.profile.hapus_akun') }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.profile.hapus_akun_desc') }}</p>
            </div>
            <div class="card-body">
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" class="btn-danger btn-icon" title="{{ __('ui.profile.hapus_akun') }}" aria-label="{{ __('ui.profile.hapus_akun') }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
                <h3>{{ __('ui.profile.confirm_delete_title') }}</h3>
            </div>

            <p class="text-xs text-secondary mb-6">{{ __('ui.profile.confirm_delete_desc') }}</p>

            <div class="form-group mb-6">
                <label class="form-label sr-only">{{ __('ui.auth.password') }}</label>
                <input type="password" name="password" placeholder="{{ __('ui.auth.password') }}" class="form-input @error('password', 'userDeletion') is-invalid @enderror">
                @error('password', 'userDeletion')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="btn-secondary btn-icon" title="{{ __('ui.common.batal') }}" aria-label="{{ __('ui.common.batal') }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <button class="btn-danger btn-icon" title="{{ __('ui.profile.hapus_akun') }}" aria-label="{{ __('ui.profile.hapus_akun') }}">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>

        </form>
    </div>
</x-modal>
@endsection

@push('scripts')
<script>
document.getElementById('foto_profil_input').addEventListener('change', function(e) {
    const preview = document.getElementById('foto_profil_preview_img');
    const initial = document.getElementById('foto_profil_initial');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            preview.classList.remove('hidden');
            initial?.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
