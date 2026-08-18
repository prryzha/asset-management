@extends('layouts.app')

@section('title', 'Ubah Jadwal Perawatan')

@section('content')
<div class="p-8 max-w-2xl mx-auto">

    <x-ui.page-header title="Ubah Jadwal Perawatan" subtitle="Perbarui jadwal perawatan.">
        <x-slot:actions>
            <a href="{{ route('maintenance.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('maintenance.update', $maintenanceSchedule) }}" method="POST" x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf @method('PUT')

        @php
            $normalAssets = $assets->whereNotIn('id', $damagedAssetIds);
            $damagedAssets = $assets->whereIn('id', $damagedAssetIds);
        @endphp

        <div class="card">
            <div class="card-header">
                <h3>Rincian Perawatan</h3>
            </div>
            <div class="card-body">
                <div class="space-y-5">
                    <div class="form-group">
                        <label class="form-label">Aset <span class="text-danger">*</span></label>
                        <select name="asset_id" required class="form-input @error('asset_id') is-invalid @enderror">
                            @if($damagedAssets->count())
                            <optgroup label="🔴 Perlu Perbaikan ({{ $damagedAssets->count() }})">
                                @foreach($damagedAssets as $asset)
                                    <option value="{{ $asset->id }}" class="font-normal" {{ old('asset_id', $maintenanceSchedule->asset_id)==$asset->id ? 'selected' : '' }}>
                                        🛠️ {{ $asset->kode_barang }} - {{ $asset->nama_barang }} ({{ $asset->kondisi }})
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                            <optgroup label="✅ Aset Lainnya">
                                @foreach($normalAssets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id', $maintenanceSchedule->asset_id)==$asset->id ? 'selected' : '' }}>
                                        {{ $asset->kode_barang }} - {{ $asset->nama_barang }}
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                        @error('asset_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Jenis Perawatan <span class="text-danger">*</span></label>
                        <input type="text" name="jenis_perawatan" value="{{ old('jenis_perawatan', $maintenanceSchedule->jenis_perawatan) }}" required class="form-input @error('jenis_perawatan') is-invalid @enderror" placeholder="mis. Service AC, Ganti Filter">
                        @error('jenis_perawatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Jadwal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_jadwal" value="{{ old('tanggal_jadwal', $maintenanceSchedule->tanggal_jadwal) }}" required class="form-input @error('tanggal_jadwal') is-invalid @enderror">
                        @error('tanggal_jadwal')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Teknisi / Vendor</label>
                        <input type="text" name="teknisi" value="{{ old('teknisi', $maintenanceSchedule->teknisi) }}" class="form-input @error('teknisi') is-invalid @enderror" placeholder="mis. Budi (Teknisi AC), CV Sejahtera Elektronik">
                        @error('teknisi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" rows="4" class="form-input @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan...">{{ old('catatan', $maintenanceSchedule->catatan) }}</textarea>
                        @error('catatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('maintenance.index') }}" class="btn-secondary btn-sm">Batal</a>
                    <button type="submit" :disabled="submitting" class="btn-primary btn-sm">
                        <span x-show="!submitting">Simpan Perubahan</span>
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
        </div>

    </form>

</div>
@endsection
