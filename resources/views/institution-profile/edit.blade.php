@extends('layouts.app')

@section('title', __('ui.institution.title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.institution.title')">
        <x-slot:actions>
            <a href="{{ route('dashboard') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.kembali') }}" aria-label="{{ __('ui.common.kembali') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    @if(session('status') === 'institution-profile-updated')
        <div class="alert alert-success mb-6">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ __('ui.institution.status_updated') }}
        </div>
    @endif

    <form action="{{ route('institution-profile.update') }}" method="POST" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.institution.identitas_instansi') }}</h3>
                <p class="text-xs text-secondary mt-0.5">{{ __('ui.institution.identitas_instansi_desc') }}</p>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Logo --}}
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.institution.logo_instansi') }}</label>
                        <div class="flex items-center gap-4">
                            @if($profile->logo)
                            <div id="logo_preview" class="w-16 h-16 rounded overflow-hidden flex-shrink-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                <img id="logo_preview_img" src="{{ asset('storage/'.$profile->logo) }}" class="w-full h-full object-cover">
                            </div>
                            @else
                            <div id="logo_preview" class="w-16 h-16 rounded flex-shrink-0 flex items-center justify-center bg-gray-50 dark:bg-gray-700">
                                <svg id="logo_preview_placeholder" class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <img id="logo_preview_img" class="w-full h-full object-cover hidden">
                            </div>
                            @endif
                            <div class="flex-1">
                                <input type="file" name="logo" id="logo_input" accept="image/*" class="form-input @error('logo') is-invalid @enderror">
                                <p class="text-xs text-secondary mt-1.5">{{ __('ui.profile.foto_format_hint') }}</p>
                                @error('logo')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.institution.nama_instansi') }} <span class="text-danger">*</span></label>
                        <input type="text" name="nama_instansi" value="{{ old('nama_instansi', $profile->nama_instansi) }}" required class="form-input @error('nama_instansi') is-invalid @enderror" placeholder="{{ __('ui.institution.placeholder_nama_instansi') }}">
                        @error('nama_instansi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.institution.nama_singkat') }}</label>
                        <input type="text" name="nama_singkat" value="{{ old('nama_singkat', $profile->nama_singkat) }}" class="form-input @error('nama_singkat') is-invalid @enderror" placeholder="{{ __('ui.institution.placeholder_nama_singkat') }}">
                        @error('nama_singkat')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.institution.alamat') }}</label>
                        <textarea name="alamat" rows="2" class="form-input @error('alamat') is-invalid @enderror" placeholder="{{ __('ui.institution.placeholder_alamat') }}">{{ old('alamat', $profile->alamat) }}</textarea>
                        @error('alamat')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.institution.nomor_telepon') }}</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $profile->telepon) }}" class="form-input @error('telepon') is-invalid @enderror" placeholder="{{ __('ui.institution.placeholder_telepon') }}">
                        @error('telepon')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.auth.email') }}</label>
                        <input type="email" name="email" value="{{ old('email', $profile->email) }}" class="form-input @error('email') is-invalid @enderror" placeholder="info@instansi.sch.id">
                        @error('email')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.institution.website') }}</label>
                        <input type="url" name="website" value="{{ old('website', $profile->website) }}" class="form-input @error('website') is-invalid @enderror" placeholder="https://instansi.sch.id">
                        @error('website')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.institution.deskripsi_slogan') }}</label>
                        <input type="text" name="deskripsi" value="{{ old('deskripsi', $profile->deskripsi) }}" class="form-input @error('deskripsi') is-invalid @enderror" placeholder="{{ __('ui.institution.placeholder_deskripsi') }}">
                        @error('deskripsi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm">{{ __('ui.common.batal') }}</a>
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

@push('scripts')
<script>
document.getElementById('logo_input').addEventListener('change', function(e) {
    const preview = document.getElementById('logo_preview_img');
    const placeholder = document.getElementById('logo_preview_placeholder');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            preview.src = ev.target.result;
            preview.classList.remove('hidden');
            placeholder?.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
