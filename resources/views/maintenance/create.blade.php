@extends('layouts.app')

@section('title', 'Tambah Jadwal Maintenance')

@section('content')
<div class="p-8 max-w-2xl mx-auto">

    <x-ui.page-header title="Tambah Jadwal Maintenance" subtitle="Buat jadwal maintenance baru.">
        <x-slot:actions>
            <a href="{{ route('maintenance.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card">
        <div class="card-header">
            <h3>Detail Maintenance</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('maintenance.store') }}" method="POST">
                @csrf

                @php
                    $normalAssets = $assets->whereNotIn('id', $damagedAssetIds);
                    $damagedAssets = $assets->whereIn('id', $damagedAssetIds);
                @endphp

                <div class="space-y-5">
                    <div class="form-group">
                        <label class="form-label">Asset <span class="text-danger">*</span></label>
                        <select name="asset_id" required class="form-input @error('asset_id') is-invalid @enderror">
                            <option value="">-- Pilih Asset --</option>
                            @if($damagedAssets->count())
                            <optgroup label="🔴 Perlu Perbaikan ({{ $damagedAssets->count() }})">
                                @foreach($damagedAssets as $asset)
                                    <option value="{{ $asset->id }}" class="font-medium" {{ old('asset_id')==$asset->id ? 'selected' : '' }}>
                                        🛠️ {{ $asset->kode_barang }} - {{ $asset->nama_barang }} ({{ $asset->kondisi }})
                                    </option>
                                @endforeach
                            </optgroup>
                            @endif
                            <optgroup label="✅ Asset Lainnya">
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
                        <label class="form-label">Jenis Perawatan <span class="text-danger">*</span></label>
                        <input type="text" name="jenis_perawatan" value="{{ old('jenis_perawatan') }}" required class="form-input @error('jenis_perawatan') is-invalid @enderror" placeholder="mis. Service AC, Ganti Filter">
                        @error('jenis_perawatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Jadwal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_jadwal" value="{{ old('tanggal_jadwal') }}" required class="form-input @error('tanggal_jadwal') is-invalid @enderror">
                        @error('tanggal_jadwal')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" rows="4" class="form-input @error('catatan') is-invalid @enderror" placeholder="Catatan tambahan...">{{ old('catatan') }}</textarea>
                        @error('catatan')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn-primary">Simpan</button>
                        <a href="{{ route('maintenance.index') }}" class="btn-secondary">Kembali</a>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection