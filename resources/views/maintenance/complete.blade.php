@extends('layouts.app')

@section('title', __('ui.maintenance.selesaikan_perawatan_title'))

@section('content')
<div class="page-content">

    <x-ui.page-header :title="__('ui.maintenance.selesaikan_perawatan_title')">
        <x-slot:actions>
            <a href="{{ route('maintenance.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.kembali') }}" aria-label="{{ __('ui.common.kembali') }}">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card">
        <div class="card-header">
            <h3>{{ __('ui.maintenance.informasi_aset') }}</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-xs text-secondary block mb-1">{{ __('ui.maintenance.kode_aset') }}</span>
                    <span class="font-normal">{{ $maintenanceSchedule->asset->kode_barang ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-secondary block mb-1">{{ __('ui.maintenance.nama_aset') }}</span>
                    <span class="font-normal">{{ $maintenanceSchedule->asset->nama_barang ?? __('ui.maintenance.aset_dihapus') }}</span>
                </div>
                <div>
                    <span class="text-xs text-secondary block mb-1">{{ __('ui.maintenance.jenis_perawatan') }}</span>
                    <span class="font-normal">{{ $maintenanceSchedule->jenis_perawatan }}</span>
                </div>
                <div>
                    <span class="text-xs text-secondary block mb-1">{{ __('ui.maintenance.tanggal') }}</span>
                    <span class="font-normal">{{ \Carbon\Carbon::parse($maintenanceSchedule->tanggal_jadwal)->translatedFormat('d M Y') }}</span>
                </div>
                @if($maintenanceSchedule->teknisi)
                <div>
                    <span class="text-xs text-secondary block mb-1">{{ __('ui.maintenance.teknisi_vendor') }}</span>
                    <span class="font-normal">{{ $maintenanceSchedule->teknisi }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="card mt-6">
        <div class="card-header">
            <h3>{{ __('ui.maintenance.hasil_perawatan') }}</h3>
        </div>
        <form action="{{ route('maintenance.complete', $maintenanceSchedule) }}" method="POST">
            @csrf @method('PUT')

            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">{{ __('ui.maintenance.kondisi_setelah_perawatan') }} <span class="text-danger">*</span></label>
                        <select name="kondisi" required class="form-input @error('kondisi') is-invalid @enderror">
                            <option value="Baik">{{ __('ui.status.Baik') }}</option>
                            <option value="Kurang Baik">{{ __('ui.status.Kurang Baik') }}</option>
                            <option value="Rusak Berat">{{ __('ui.status.Rusak Berat') }}</option>
                        </select>
                        @error('kondisi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('ui.maintenance.biaya_perawatan_rp') }}</label>
                        <input type="number" name="biaya" min="0" value="{{ old('biaya') }}" class="form-input @error('biaya') is-invalid @enderror" placeholder="0">
                        @error('biaya')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group md:col-span-2">
                        <label class="form-label">{{ __('ui.maintenance.catatan_penyelesaian') }}</label>
                        <textarea name="catatan_selesai" rows="5" class="form-input @error('catatan_selesai') is-invalid @enderror" placeholder="{{ __('ui.maintenance.catatan_penyelesaian_placeholder') }}">{{ old('catatan_selesai') }}</textarea>
                        @error('catatan_selesai')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('maintenance.index') }}" class="btn-secondary btn-sm">{{ __('ui.common.batal') }}</a>
                    <button type="submit" class="btn-success btn-sm">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('ui.maintenance.selesaikan_perawatan') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
