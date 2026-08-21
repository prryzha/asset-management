@extends('layouts.app')

@section('title', __('ui.maintenance.tambah_jadwal_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.maintenance.tambah_jadwal_title')">
        <x-slot:actions>
            <a href="{{ route('maintenance.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.kembali') }}" aria-label="{{ __('ui.common.kembali') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('maintenance.store') }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        @php
            $normalAssets = $assets->whereNotIn('id', $damagedAssetIds);
            $damagedAssets = $assets->whereIn('id', $damagedAssetIds);
        @endphp

        <div class="card">
            <div class="card-header">
                <h3>{{ __('ui.maintenance.rincian_perawatan') }}</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.maintenance.aset') }} <span class="text-danger">*</span></label>
                        <select name="asset_id" required class="form-input @error('asset_id') is-invalid @enderror">
                            <option value="">{{ __('ui.maintenance.pilih_aset_placeholder') }}</option>
                            @if($damagedAssets->count())
                            <optgroup label="🔴 {{ __('ui.maintenance.perlu_perbaikan', ['count' => $damagedAssets->count()]) }}">
                                @foreach($damagedAssets as $asset)
                                    <option value="{{ $asset->id }}" class="font-normal" {{ old('asset_id')==$asset->id ? 'selected' : '' }}>
                                        🛠️ {{ $asset->kode_barang }} - {{ $asset->nama_barang }} ({{ $asset->kondisi }})
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                            <optgroup label="✅ {{ __('ui.maintenance.aset_lainnya') }}">
                                @foreach($normalAssets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id')==$asset->id ? 'selected' : '' }}>
                                        {{ $asset->kode_barang }} - {{ $asset->nama_barang }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('asset_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.maintenance.jenis_perawatan') }} <span class="text-danger">*</span></label>
                        <input type="text" name="jenis_perawatan" value="{{ old('jenis_perawatan') }}" required class="form-input @error('jenis_perawatan') is-invalid @enderror" placeholder="{{ __('ui.maintenance.jenis_perawatan_placeholder') }}">
                        @error('jenis_perawatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.maintenance.tanggal_jadwal') }} <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_jadwal" value="{{ old('tanggal_jadwal') }}" required class="form-input @error('tanggal_jadwal') is-invalid @enderror">
                        @error('tanggal_jadwal')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.maintenance.teknisi_vendor') }}</label>
                        <input type="text" name="teknisi" value="{{ old('teknisi') }}" class="form-input @error('teknisi') is-invalid @enderror" placeholder="{{ __('ui.maintenance.teknisi_placeholder') }}">
                        @error('teknisi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.assets.catatan') }}</label>
                        <textarea name="catatan" rows="4" class="form-input @error('catatan') is-invalid @enderror" placeholder="{{ __('ui.maintenance.catatan_placeholder') }}">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('maintenance.index') }}" class="btn-secondary btn-sm">{{ __('ui.common.batal') }}</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">{{ __('ui.common.simpan') }}</span>
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
