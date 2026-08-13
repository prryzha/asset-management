@extends('layouts.app')

@section('title', 'Selesaikan Perawatan')

@section('content')
<div class="p-8 max-w-2xl mx-auto">

    <x-ui.page-header title="Selesaikan Perawatan" subtitle="Laporkan hasil penyelesaian perawatan.">
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
            <h3>Informasi Aset</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <span class="text-xs text-secondary block mb-1">Kode Aset</span>
                    <span class="font-medium">{{ $maintenanceSchedule->asset->kode_barang ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-xs text-secondary block mb-1">Nama Aset</span>
                    <span class="font-medium">{{ $maintenanceSchedule->asset->nama_barang ?? 'Aset sudah dihapus' }}</span>
                </div>
                <div>
                    <span class="text-xs text-secondary block mb-1">Jenis Perawatan</span>
                    <span class="font-medium">{{ $maintenanceSchedule->jenis_perawatan }}</span>
                </div>
                <div>
                    <span class="text-xs text-secondary block mb-1">Tanggal</span>
                    <span class="font-medium">{{ \Carbon\Carbon::parse($maintenanceSchedule->tanggal_jadwal)->format('d M Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-6">
        <div class="card-header">
            <h3>Hasil Perawatan</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('maintenance.complete', $maintenanceSchedule) }}" method="POST">
                @csrf @method('PUT')

                <div class="space-y-5">
                    <div class="form-group">
                        <label class="form-label">Kondisi Setelah Perawatan <span class="text-danger">*</span></label>
                        <select name="kondisi" required class="form-input @error('kondisi') is-invalid @enderror">
                            <option value="Baik">Baik</option>
                            <option value="Kurang Baik">Kurang Baik</option>
                            <option value="Rusak Berat">Rusak Berat</option>
                        </select>
                        @error('kondisi')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Catatan Penyelesaian</label>
                        <textarea name="catatan_selesai" rows="5" class="form-input @error('catatan_selesai') is-invalid @enderror" placeholder="Contoh: Ganti RAM, install ulang Windows, membersihkan kipas...">{{ old('catatan_selesai') }}</textarea>
                        @error('catatan_selesai')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <div class="flex items-center gap-3">
                        <button type="submit" class="btn-success">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Selesaikan Perawatan
                        </button>
                        <a href="{{ route('maintenance.index') }}" class="btn-secondary">Kembali</a>
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection