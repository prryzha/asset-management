@extends('layouts.app')

@section('title', __('ui.users.ubah_user_page_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.users.ubah_user_page_title')">
        <x-slot:actions>
            <a href="{{ route('users.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.kembali') }}" aria-label="{{ __('ui.common.kembali') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('users.update', $user) }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.users.data_user') }}</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('ui.profile.nama_lengkap') }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input @error('name') is-invalid @enderror" placeholder="{{ __('ui.users.nama_lengkap_placeholder') }}">
                        @error('name')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.profile.nama_pengguna') }}</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" class="form-input @error('username') is-invalid @enderror" placeholder="cth. budi_santoso">
                        <p class="text-xs text-secondary mt-1.5">{{ __('ui.profile.username_hint') }}</p>
                        @error('username')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.auth.email') }} <span class="text-danger">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input @error('email') is-invalid @enderror" placeholder="{{ __('ui.users.email_placeholder') }}">
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.profile.peran') }} <span class="text-danger">*</span></label>
                        <select name="role" required class="form-input @error('role') is-invalid @enderror">
                            <option value="staff" {{ old('role', $user->role) == 'staff' ? 'selected' : '' }} @disabled($user->isLastAdmin())>{{ __('ui.users.role_staff_option') }}</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('ui.users.role_admin_option') }}</option>
                        </select>
                        @error('role')<p class="form-error">{{ $message }}</p>@enderror
                        @if($user->isLastAdmin())
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">
                            {{ __('ui.users.last_admin_hint') }}
                        </p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.auth.password') }} <span class="text-xs text-secondary font-normal">{{ __('ui.users.password_optional_hint') }}</span></label>
                        <input type="password" name="password" class="form-input @error('password') is-invalid @enderror" placeholder="{{ __('ui.users.password_placeholder') }}">
                        @error('password')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.profile.konfirmasi_kata_sandi_baru') }}</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="{{ __('ui.users.password_confirm_placeholder') }}">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">{{ __('ui.common.batal') }}</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">{{ __('ui.common.simpan_perubahan') }}</span>
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
