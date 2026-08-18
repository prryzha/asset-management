@extends('layouts.app')

@section('title', $asset->kode_barang)

@section('content')
<div class="p-8">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5 mb-8">
        <div>
            <a href="{{ route('assets.index') }}" class="text-sm text-primary-600 hover:text-primary-700 mb-2 inline-flex items-center gap-1.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Data Aset
            </a>
            <h1 class="text-xl font-normal text-gray-900 dark:text-gray-100 mt-1">{{ $asset->kode_barang }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-0.5">{{ $asset->nama_barang }}</p>
        </div>

        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('assets.edit', $asset) }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 14l-4 1 1-4 8.414-8.414z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('assets.qr-code', $asset) }}" target="_blank" class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                QR Code
            </a>
            <a href="{{ route('assets.label-pdf', $asset) }}" target="_blank" class="btn-danger btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                PDF Label
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- Main Content --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Detail Aset --}}
            <div class="card">
                <div class="card-header">
                    <h3>Detail Aset</h3>
                </div>
                <div class="card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach([
                            'Kode Barang' => $asset->kode_barang,
                            'Nama Barang' => $asset->nama_barang,
                            'Merk / Spesifikasi' => $asset->merk ?? '-',
                            'Kategori' => $asset->category?->nama ?? '-',
                            'Lokasi' => $asset->location?->nama ?? '-',
                            'Penanggung Jawab' => $asset->penanggung_jawab ?? '-',
                        ] as $label => $value)
                        <div>
                            <p class="text-xs font-normal text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ $label }}</p>
                            <p class="font-normal text-gray-900 dark:text-gray-100 mt-1">{{ $value }}</p>
                        </div>
                        @endforeach
                        <div>
                            <p class="text-xs font-normal text-gray-400 uppercase tracking-wider">Kondisi</p>
                            <div class="mt-1"><x-ui.badge-status :status="$asset->kondisi" /></div>
                        </div>
                        <div>
                            <p class="text-xs font-normal text-gray-400 uppercase tracking-wider">Status</p>
                            <div class="mt-1"><x-ui.badge-status :status="$asset->status" /></div>
                        </div>
                        @if($asset->catatan)
                        <div class="md:col-span-2">
                            <p class="text-xs font-normal text-gray-400 uppercase tracking-wider">Catatan</p>
                            <p class="text-gray-900 dark:text-gray-100 mt-1 bg-gray-50 dark:bg-gray-700 rounded p-4 text-sm border border-gray-200 dark:border-gray-600">{{ $asset->catatan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Riwayat Peminjaman --}}
            <div class="card">
                <div class="card-header">
                    <h3>Riwayat Peminjaman</h3>
                </div>
                <div class="card-body">
                    @if($transactions->count())
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Peminjam</th>
                                    <th>Keperluan</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $t)
                                <tr>
                                    <td class="font-normal">{{ $t->nama_peminjam }}</td>
                                    <td>{{ $t->keperluan }}</td>
                                    <td>{{ \Carbon\Carbon::parse($t->tanggal_pinjam)->format('d/m/Y') }}</td>
                                    <td><x-ui.badge-status :status="$t->status_peminjaman" /></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-400 dark:text-gray-500 text-sm">Belum ada riwayat peminjaman.</p>
                    @endif
                </div>
            </div>

            {{-- Jadwal Perawatan --}}
            <div class="card">
                <div class="card-header">
                    <h3>Jadwal Perawatan</h3>
                </div>
                <div class="card-body">
                    @if($maintenanceSchedules->count())
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Jenis</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($maintenanceSchedules as $m)
                                <tr>
                                    <td class="font-normal">{{ $m->jenis_perawatan }}</td>
                                    <td>{{ \Carbon\Carbon::parse($m->tanggal_jadwal)->format('d/m/Y') }}</td>
                                    <td><x-ui.badge-status :status="$m->status" /></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-gray-400 dark:text-gray-500 text-sm">Belum ada jadwal perawatan.</p>
                    @endif
                </div>
            </div>

            {{-- Log Aktivitas Aset --}}
            <div class="card">
                <div class="card-header">
                    <h3>Log Aktivitas Aset</h3>
                </div>
                <div class="card-body">
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Log aktivitas dicatat secara otomatis oleh sistem. Kamu juga bisa menambahkan catatan manual.</p>
                        <button type="button" onclick="openManualLog()"
                                class="btn-ghost btn-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Tambah Catatan Manual
                        </button>
                    </div>
                    @if($assetLogs->count())
                    @php
                        $logColors = [
                            'mutasi' => ['border-blue-400 dark:border-blue-600', 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'],
                            'perawatan' => ['border-amber-400 dark:border-amber-600', 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'],
                            'kondisi' => ['border-purple-400 dark:border-purple-600', 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'],
                            'peminjaman' => ['border-indigo-400 dark:border-indigo-600', 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'],
                            'pengembalian' => ['border-teal-400 dark:border-teal-600', 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300'],
                        ];
                        $logColorDefault = ['border-gray-400 dark:border-gray-600', 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'];
                    @endphp
                    <div class="space-y-4">
                        @foreach($assetLogs as $log)
                        @php [$logBorder, $logBadge] = $logColors[$log->tipe] ?? $logColorDefault; @endphp
                        <div class="flex items-start gap-4 pl-4 border-l-4 {{ $logBorder }}">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 text-xs font-normal {{ $logBadge }}">
                                        {{ ucfirst($log->tipe) }}
                                    </span>
                                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="font-normal text-gray-900 dark:text-gray-100 mt-1">{{ $log->deskripsi }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">oleh {{ $log->user?->name ?? 'System' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-gray-400 dark:text-gray-500 text-sm">Belum ada log aktivitas untuk aset ini.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            @if($asset->foto)
            <div class="card overflow-hidden">
                <img src="{{ asset('storage/'.$asset->foto) }}" alt="{{ $asset->nama_barang }}" class="w-full h-64 object-cover">
            </div>
            @else
            <div class="card p-8 flex items-center justify-center h-48">
                <div class="text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">Belum ada foto</p>
                </div>
            </div>
            @endif

            <div class="card p-6">
                <h3 class="font-normal text-gray-900 dark:text-gray-100 mb-4 text-sm uppercase tracking-wider text-gray-400 dark:text-gray-500">Info Tambahan</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400 dark:text-gray-500">Nomor Seri</span>
                        <span class="font-normal text-gray-900 dark:text-gray-100">{{ $asset->nomor_seri ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 dark:text-gray-500">Tahun Perolehan</span>
                        <span class="font-normal text-gray-900 dark:text-gray-100">{{ $asset->tahun_perolehan ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400 dark:text-gray-500">Nilai Perolehan</span>
                        <span class="font-normal text-gray-900 dark:text-gray-100">{{ $asset->nilai_perolehan ? 'Rp '.number_format($asset->nilai_perolehan,0,',','.') : '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="font-normal text-gray-900 dark:text-gray-100 mb-4 text-sm uppercase tracking-wider text-gray-400 dark:text-gray-500">Aksi Cepat</h3>
                <div class="space-y-2">
                    <a href="{{ route('assets.edit', $asset) }}"
                       class="flex items-center gap-2 w-full px-3 py-2 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-primary-900/30 dark:text-primary-300 dark:hover:bg-primary-900/50 transition text-sm font-normal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 14l-4 1 1-4 8.414-8.414z"/>
                        </svg>
                        Edit Aset
                    </a>
                    <a href="{{ route('transactions.create') }}?asset_id={{ $asset->id }}"
                       class="flex items-center gap-2 w-full px-3 py-2 rounded bg-warning-50 text-warning-700 hover:bg-warning-100 dark:bg-amber-900/30 dark:text-amber-300 dark:hover:bg-amber-900/50 transition text-sm font-normal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Catat Peminjaman
                    </a>
                    <a href="{{ route('maintenance.create') }}?asset_id={{ $asset->id }}"
                       class="flex items-center gap-2 w-full px-3 py-2 rounded bg-orange-50 text-orange-700 hover:bg-orange-100 dark:bg-orange-900/30 dark:text-orange-300 dark:hover:bg-orange-900/50 transition text-sm font-normal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Jadwalkan Perawatan
                    </a>                            <form action="{{ route('assets.report-damage', $asset) }}" method="POST" id="reportDamageForm" class="hidden">
                                @csrf
                                <input type="hidden" name="kondisi" id="damage_kondisi">
                                <input type="hidden" name="deskripsi" id="damage_deskripsi">
                            </form>
                            <button type="button" onclick="openReportDamage()"
                                    class="flex items-center gap-2 w-full px-3 py-2 rounded bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 transition text-sm font-normal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                                Laporkan Kerusakan
                            </button>
                            <form action="{{ route('assets.destroy', $asset) }}" method="POST" class="delete-form">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="flex items-center gap-2 w-full px-3 py-2 rounded bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/50 transition text-sm font-normal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Hapus Aset
                                </button>
                            </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Manual Log Modal --}}
<div id="manualLogModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded shadow-xl max-w-md w-full p-6 mx-4">
        <h3 class="text-lg font-normal text-gray-900 dark:text-gray-100 mb-2">Tambah Catatan Manual</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Tambahkan catatan aktivitas untuk aset ini.</p>
        <form action="{{ route('assets.logs.store', $asset) }}" method="POST">
            @csrf
            <div class="form-group mb-4">
                <label class="form-label">Tipe</label>
                <select name="tipe" class="form-input" required>
                    <option value="lainnya">Lainnya</option>
                    <option value="mutasi">Mutasi</option>
                    <option value="perawatan">Perawatan</option>
                    <option value="kondisi">Kondisi</option>
                </select>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" rows="3" class="form-input" required placeholder="Jelaskan aktivitas..."></textarea>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeManualLog()" class="btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn-primary btn-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openManualLog() { document.getElementById('manualLogModal').style.display = 'flex'; }
function closeManualLog() { document.getElementById('manualLogModal').style.display = 'none'; }

function openReportDamage() {
    Swal.fire({
        title: 'Laporkan Kerusakan',
        html: `
            <div class="text-left">
                <label class="block text-sm font-normal text-gray-700 mb-1.5">Kondisi</label>
                <select id="swal-kondisi" class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-4">
                    <option value="Kurang Baik">Kurang Baik</option>
                    <option value="Rusak Berat">Rusak Berat</option>
                </select>
                <label class="block text-sm font-normal text-gray-700 mb-1.5">Deskripsi Kerusakan</label>
                <textarea id="swal-deskripsi" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Jelaskan kerusakan..."></textarea>
            </div>
        `,
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Laporkan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        preConfirm: () => {
            const kondisi = document.getElementById('swal-kondisi').value;
            const deskripsi = document.getElementById('swal-deskripsi').value;
            if (!deskripsi) { Swal.showValidationMessage('Deskripsi harus diisi'); return; }
            return { kondisi, deskripsi };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('damage_kondisi').value = result.value.kondisi;
            document.getElementById('damage_deskripsi').value = result.value.deskripsi;
            document.getElementById('reportDamageForm').submit();
        }
    });
}
document.getElementById('manualLogModal')?.addEventListener('click', function(e) { if (e.target === this) closeManualLog(); });

document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Hapus Aset?',
            text: 'Data yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => { if (result.isConfirmed) form.submit(); });
    });
});
</script>
@endpush
