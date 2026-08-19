@extends('layouts.app')

@section('title', 'Tambah Aset')

@section('content')
<div class="page-content">

    <x-ui.page-header title="Tambah Aset" subtitle="Tambahkan aset baru ke sistem.">
        <x-slot:actions>
            <a href="{{ route('assets.index') }}" class="btn-ghost btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data" id="assetForm">
        @csrf

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
                            <input type="text" id="prefix_input" placeholder="Awalan (contoh: MON)"
                                   class="form-input w-40 uppercase" maxlength="10">
                            <input type="text" name="kode_barang" id="kode_barang_input"
                                   value="{{ old('kode_barang') }}"                                   class="form-input flex-1 bg-gray-50 dark:bg-gray-700" readonly placeholder="Terisi otomatis dari awalan">
                        </div>
                        @error('kode_barang')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Lokasi --}}
                    <div class="form-group">
                        <label class="form-label">Lokasi</label>
                        <select name="location_id" class="form-input">
                            <option value="">Pilih Lokasi</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nama Barang --}}
                    <div class="form-group">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang') }}" class="form-input" placeholder="Masukkan nama barang">
                        @error('nama_barang')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Kondisi --}}
                    <div class="form-group">
                        <label class="form-label">Kondisi</label>
                        <select name="kondisi" class="form-input">
                            <option>Baik</option>
                            <option>Kurang Baik</option>
                            <option>Rusak Berat</option>
                        </select>
                    </div>

                    {{-- Merk --}}
                    <div class="form-group">
                        <label class="form-label">Merk / Spesifikasi</label>
                        <input type="text" name="merk" value="{{ old('merk') }}" class="form-input" placeholder="Masukkan merk">
                    </div>

                    {{-- Nomor Seri --}}
                    <div class="form-group">
                        <label class="form-label">Nomor Seri</label>
                        <input type="text" name="nomor_seri" id="nomor_seri_input" value="{{ old('nomor_seri') }}" class="form-input" placeholder="SN-xxx">
                        <p id="nomor_seri_hint" class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 hidden">Dikosongkan otomatis karena jumlah unit &gt; 1 — nomor seri tiap unit fisik biasanya berbeda, isi belakangan lewat halaman Ubah per unit kalau perlu.</p>
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div class="form-group">
                        <label class="form-label">Penanggung Jawab</label>
                        <input type="text" name="penanggung_jawab" value="{{ old('penanggung_jawab') }}" class="form-input" placeholder="Nama PIC / pengelola aset">
                    </div>

                    {{-- Kategori --}}
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <select name="category_id" id="category_select" class="form-input">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-kode="{{ $category->kode }}">{{ $category->nama }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<p class="form-error">{{ $message }}</p>@enderror
                    </div>

                    {{-- Jumlah Unit (bulk create) --}}
                    <div class="form-group">
                        <label class="form-label">Jumlah Unit Ditambahkan</label>
                        <input type="number" min="1" max="50" name="jumlah_unit" id="jumlah_unit_input" value="{{ old('jumlah_unit', 1) }}" class="form-input">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Kalau ada beberapa unit identik (mis. 5 kursi), isi jumlahnya di sini — sistem otomatis membuat unit terpisah dengan kode barang berurutan.</p>
                    </div>

                    {{-- Tahun Perolehan --}}
                    <div class="form-group">
                        <label class="form-label">Tahun Perolehan</label>
                        <input type="number" min="1900" max="{{ date('Y') }}" name="tahun_perolehan"
                               value="{{ old('tahun_perolehan', date('Y')) }}" class="form-input">
                    </div>

                    {{-- Nilai Perolehan --}}
                    <div class="form-group">
                        <label class="form-label">Nilai Perolehan (Rp)</label>
                        <input type="number" min="0" name="nilai_perolehan" value="{{ old('nilai_perolehan', 0) }}" class="form-input">
                    </div>

                    {{-- Catatan --}}
                    <div class="form-group lg:col-span-2">
                        <label class="form-label">Catatan</label>
                        <textarea rows="4" name="catatan" class="form-input" placeholder="Catatan tambahan..."></textarea>
                    </div>

                    {{-- Foto --}}
                    <div class="lg:col-span-2 pt-6 border-t border-[#E5E7EB] dark:border-gray-700">
                        <label class="form-label">Foto Aset</label>
                        <div class="flex items-start gap-6">
                            <div class="flex-1">
                                <input type="file" name="foto" id="foto_input" accept="image/*" class="form-input file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-normal file:bg-primary-50 file:text-primary hover:file:bg-primary-100 dark:file:bg-primary-900/30 dark:file:text-primary-300 dark:hover:file:bg-primary-900/50 transition">
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5">Format: JPG, JPEG, PNG. Maks: 2MB</p>
                                @error('foto')<p class="form-error">{{ $message }}</p>@enderror
                            </div>
                            <div id="foto_preview" class="w-32 h-32 border-2 border-dashed border-[#E5E7EB] dark:border-gray-600 flex items-center justify-center overflow-hidden flex-shrink-0 bg-gray-50 dark:bg-gray-700 hidden">
                                <img id="foto_preview_img" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="card-footer">
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('assets.index') }}" class="btn-secondary btn-sm">Batal</a>
                    <button type="submit" id="submitBtn" class="btn-primary btn-sm">
                        <span id="submitText">Simpan Aset</span>
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

</div>
@endsection

@push('scripts')
<script>
// Auto-generate kode barang
document.getElementById('prefix_input').addEventListener('input', function() {
    const prefix = this.value.trim().toUpperCase();
    const kodeInput = document.getElementById('kode_barang_input');
    if (prefix.length === 0) { kodeInput.value = ''; return; }
    fetch('{{ url('assets/next-code') }}/' + encodeURIComponent(prefix))
        .then(res => res.json())
        .then(data => { kodeInput.value = data.kode_barang; })
        .catch(() => { kodeInput.value = prefix + '-???'; });
});

// Auto-generate kode based on category selection
document.getElementById('category_select').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const kodeKategori = selected.getAttribute('data-kode');
    const prefixInput = document.getElementById('prefix_input');
    if (kodeKategori) {
        prefixInput.value = kodeKategori;
        const event = new Event('input', { bubbles: true });
        prefixInput.dispatchEvent(event);
    }
});

window.addEventListener('DOMContentLoaded', function() {
    const catSelect = document.getElementById('category_select');
    if (catSelect.value) {
        const event = new Event('change', { bubbles: true });
        catSelect.dispatchEvent(event);
    }
});

// Image preview
document.getElementById('foto_input').addEventListener('change', function(e) {
    const preview = document.getElementById('foto_preview');
    const previewImg = document.getElementById('foto_preview_img');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) { previewImg.src = ev.target.result; preview.classList.remove('hidden'); }
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        previewImg.src = '';
    }
});

// Nonaktifkan Nomor Seri saat bikin banyak unit sekaligus — nomor seri fisik
// tiap unit biasanya beda, jadi jangan sampai satu nilai ke-copy ke semua unit.
const jumlahUnitInput = document.getElementById('jumlah_unit_input');
const nomorSeriInput = document.getElementById('nomor_seri_input');
const nomorSeriHint = document.getElementById('nomor_seri_hint');
function toggleNomorSeri() {
    const isBulk = parseInt(jumlahUnitInput.value, 10) > 1;
    nomorSeriInput.disabled = isBulk;
    nomorSeriInput.classList.toggle('bg-gray-50', isBulk);
    nomorSeriInput.classList.toggle('dark:bg-gray-700', isBulk);
    nomorSeriHint.classList.toggle('hidden', !isBulk);
    if (isBulk) nomorSeriInput.value = '';
}
jumlahUnitInput.addEventListener('input', toggleNomorSeri);
toggleNomorSeri();

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