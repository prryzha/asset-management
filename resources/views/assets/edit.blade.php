@extends('layouts.app')

@section('title', 'Edit Aset')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Edit Aset" subtitle="Perbarui informasi aset.">
        <x-slot:actions>
            <a href="{{ route('assets.show', $asset) }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('assets.update', $asset) }}" method="POST" enctype="multipart/form-data" id="assetForm">
        @csrf @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3>Informasi Aset</h3>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Kode Barang --}}
                    <div class="form-group">
                        <label class="form-label">Kode Barang</label>
                        <div class="flex gap-2">
                            <input type="text" id="prefix_input" placeholder="Prefix (contoh: MON)"
                                   class="form-input w-40 uppercase" maxlength="10"
                                   value="{{ explode('-', $asset->kode_barang)[0] ?? '' }}">
                            <input type="text" name="kode_barang" id="kode_barang_input"
                                   value="{{ old('kode_barang', $asset->kode_barang) }}"
                                   class="form-input flex-1 bg-gray-50 dark:bg-gray-700" readonly>
                        </div>
                        @error('kode_barang')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Lokasi --}}
                    <div class="form-group">
                        <label class="form-label">Lokasi</label>
                        <select name="location_id" class="form-input">
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $asset->location_id == $location->id ? 'selected' : '' }}>{{ $location->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Barang --}}
                    <div class="form-group">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang', $asset->nama_barang) }}" class="form-input" placeholder="Masukkan nama barang">
                        @error('nama_barang')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Kondisi --}}
                    <div class="form-group">
                        <label class="form-label">Kondisi</label>
                        <select name="kondisi" class="form-input">
                            <option value="Baik" {{ $asset->kondisi=='Baik'?'selected':'' }}>Baik</option>
                            <option value="Kurang Baik" {{ $asset->kondisi=='Kurang Baik'?'selected':'' }}>Kurang Baik</option>
                            <option value="Rusak Berat" {{ $asset->kondisi=='Rusak Berat'?'selected':'' }}>Rusak Berat</option>
                        </select>
                    </div>

                    {{-- Merk --}}
                    <div class="form-group">
                        <label class="form-label">Merk / Spesifikasi</label>
                        <input type="text" name="merk" value="{{ old('merk', $asset->merk) }}" class="form-input" placeholder="Masukkan merk">
                    </div>

                    {{-- Nomor Seri --}}
                    <div class="form-group">
                        <label class="form-label">Nomor Seri</label>
                        <input type="text" name="nomor_seri" value="{{ old('nomor_seri', $asset->nomor_seri) }}" class="form-input" placeholder="SN-xxx">
                    </div>

                    {{-- Status --}}
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            <option value="Tersedia" {{ $asset->status=='Tersedia'?'selected':'' }}>Tersedia</option>
                            <option value="Dipinjam" {{ $asset->status=='Dipinjam'?'selected':'' }}>Dipinjam</option>
                            <option value="Perbaikan" {{ $asset->status=='Perbaikan'?'selected':'' }}>Perbaikan</option>
                        </select>
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div class="form-group">
                        <label class="form-label">Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab', $asset->penanggung_jawab) }}" class="form-input" placeholder="Nama PIC / pengelola aset">
                    </div>

                    {{-- Kategori --}}
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" class="form-input">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $asset->category_id == $category->id ? 'selected' : '' }}>{{ $category->nama }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Jumlah & Satuan --}}
                    <div class="form-group">
                        <label class="form-label">Jumlah</label>
                        <div class="flex gap-2">
                            <input type="number" min="1" name="jumlah" value="{{ old('jumlah', $asset->jumlah) }}" class="form-input flex-1">
                            <select name="satuan" class="form-input w-32">
                                @foreach(['Unit','Buah','Set','Paket','Pcs','Box'] as $satuanOption)
                                    <option value="{{ $satuanOption }}" {{ old('satuan', $asset->satuan) == $satuanOption ? 'selected' : '' }}>{{ $satuanOption }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Tahun Perolehan --}}
                    <div class="form-group">
                        <label class="form-label">Tahun Perolehan</label>
                        <input type="number" min="1900" max="{{ date('Y') }}" name="tahun_perolehan"
                               value="{{ old('tahun_perolehan', $asset->tahun_perolehan) }}" class="form-input">
                    </div>

                    {{-- Nilai Perolehan --}}
                    <div class="form-group">
                        <label class="form-label">Nilai Perolehan per Unit (Rp)</label>
                        <input type="number" min="0" name="nilai_perolehan" value="{{ old('nilai_perolehan', $asset->nilai_perolehan) }}" class="form-input">
                    </div>

                    {{-- Catatan --}}
                    <div class="form-group lg:col-span-2">
                        <label class="form-label">Catatan</label>
                        <textarea rows="4" name="catatan" class="form-input" placeholder="Catatan tambahan...">{{ old('catatan', $asset->catatan) }}</textarea>
                    </div>

                    {{-- Foto --}}
                    <div class="lg:col-span-2">
                        <div class="card">
                            <div class="card-body">
                                <label class="form-label">Foto Aset</label>
                                <div class="flex items-start gap-6">
                                    <div class="flex-1">
                                        <input type="file" name="foto" id="foto_input" accept="image/*" class="form-input file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300 dark:hover:file:bg-primary-900/50 transition">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Format: JPG, JPEG, PNG. Maks: 2MB</p>
                                        @error('foto')<p class="form-error">{{ $message }}</p>@enderror
                                        @if($asset->foto)
                                        <div class="mt-3">
                                            <button type="button" id="deleteFotoBtn" class="btn-ghost btn-sm text-danger hover:text-white hover:bg-danger">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus Foto
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div id="foto_current" class="{{ $asset->foto ? '' : 'hidden' }}">
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1.5 font-medium">Foto Saat Ini</p>
                                            <img src="{{ $asset->foto ? asset('storage/'.$asset->foto) : '' }}" id="foto_current_img"
                                                 class="w-32 h-32 object-cover border border-[#E5E7EB] dark:border-gray-600">
                                        </div>
                                        <div id="foto_preview" class="w-32 h-32 border-2 border-dashed border-[#E5E7EB] dark:border-gray-600 flex items-center justify-center overflow-hidden bg-gray-50 dark:bg-gray-700 hidden">
                                            <img id="foto_preview_img" class="w-full h-full object-cover">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('assets.show', $asset) }}" class="btn-secondary">Batal</a>
                    <button type="submit" id="submitBtn" class="btn-primary">
                        <span id="submitText">Simpan Perubahan</span>
                        <span id="submitLoading" class="inline-flex items-center gap-2 hidden">
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

    {{-- Hidden form untuk hapus foto --}}
    <form action="{{ route('assets.delete-foto', $asset) }}" method="POST" id="deleteFotoForm" class="hidden">
        @csrf @method('DELETE')
    </form>

</div>
@endsection

@push('scripts')
<script>
// Kode barang
document.getElementById('prefix_input').addEventListener('input', function() {
    const prefix = this.value.trim().toUpperCase();
    const kodeInput = document.getElementById('kode_barang_input');
    if (prefix.length === 0) { kodeInput.value = ''; return; }
    fetch('{{ url('assets/next-code') }}/' + encodeURIComponent(prefix))
        .then(res => res.json())
        .then(data => { kodeInput.value = data.kode_barang; })
        .catch(() => { kodeInput.value = prefix + '-???'; });
});

// Image preview
document.getElementById('foto_input').addEventListener('change', function(e) {
    const current = document.getElementById('foto_current');
    const preview = document.getElementById('foto_preview');
    const previewImg = document.getElementById('foto_preview_img');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewImg.src = ev.target.result;
            preview.classList.remove('hidden');
            if (current) current.classList.add('hidden');
        }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        previewImg.src = '';
        if (current) current.classList.remove('hidden');
    }
});

// Delete foto confirmation
document.getElementById('deleteFotoBtn')?.addEventListener('click', function() {
    Swal.fire({
        title: 'Hapus Foto?',
        text: 'Foto aset akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('deleteFotoForm').submit();
    });
});

// Prevent double submit
const assetForm = document.getElementById('assetForm');
const submitBtn = document.getElementById('submitBtn');
if (assetForm && submitBtn) {
    assetForm.addEventListener('submit', function(e) {
        if (submitBtn.disabled) { e.preventDefault(); return; }
        submitBtn.disabled = true;
        document.getElementById('submitText').classList.add('hidden');
        document.getElementById('submitLoading').classList.remove('hidden');
    });
}
</script>
@endpush