@extends('layouts.app')

@section('title', 'Riwayat Peminjaman')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Riwayat Peminjaman Aset" subtitle="Kelola barang yang sedang dipinjam oleh guru atau siswa.">
        <x-slot:actions>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('transactions.export-pdf', request()->only(['status'])) }}" class="btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
            <a href="{{ route('transactions.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Catat Peminjaman Baru
            </a>
            @endif
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.index') }}">
                <div class="flex items-center gap-3">
                    <label class="form-label mb-0 whitespace-nowrap">Filter Status:</label>
                    <select name="status" onchange="this.form.submit()" class="form-input w-auto">
                        <option value="">Semua Status</option>
                        <option value="Menunggu Persetujuan" {{ request('status')=='Menunggu Persetujuan'?'selected':'' }}>Menunggu Persetujuan</option>
                        <option value="Dipinjam" {{ request('status')=='Dipinjam'?'selected':'' }}>Dipinjam</option>
                        <option value="Ditolak" {{ request('status')=='Ditolak'?'selected':'' }}>Ditolak</option>
                        <option value="Dikembalikan" {{ request('status')=='Dikembalikan'?'selected':'' }}>Dikembalikan</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th>Peminjam</th>
                        <th>Keperluan</th>
                        <th>Tgl Pinjam</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $trx)
                    <tr>
                        <td>
                            <span class="font-semibold">{{ $trx->asset->kode_barang ?? 'Barang Dihapus' }}</span>
                            <span class="text-xs text-secondary block">{{ $trx->asset->nama_barang ?? '-' }}</span>
                        </td>
                        <td class="font-medium">{{ $trx->nama_peminjam }}</td>
                        <td class="text-secondary text-sm">{{ $trx->keperluan ?? '-' }}</td>
                        <td class="text-sm text-secondary">{{ \Carbon\Carbon::parse($trx->tanggal_pinjam)->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$trx->status_peminjaman" />
                        </td>
                        <td class="text-right">
                            @if($trx->status_peminjaman == 'Menunggu Persetujuan' && auth()->user()->isAdmin())
                                <form action="{{ route('transactions.approve', $trx) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-success btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Setujui
                                    </button>
                                </form>
                                <button type="button" onclick="showRejectModal({{ $trx->id }}, '{{ $trx->asset->kode_barang }}', '{{ $trx->nama_peminjam }}')" class="btn-danger btn-sm px-2 py-1 text-xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Tolak
                                </button>
                            @elseif($trx->status_peminjaman == 'Dipinjam' && auth()->user()->isAdmin())
                                <form action="{{ route('transactions.return', $trx) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-success btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                        Terima Pengembalian
                                    </button>
                                </form>
                            @elseif($trx->status_peminjaman == 'Ditolak' && $trx->rejection_reason)
                                <span class="text-xs text-secondary cursor-help" title="{{ $trx->rejection_reason }}">Lihat Alasan</span>
                            @elseif($trx->status_peminjaman == 'Dikembalikan')
                                <span class="text-sm text-secondary">Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-16">
                            <x-ui.empty-state
                                icon="refresh-cw"
                                title="Belum Ada Riwayat Peminjaman"
                                description="Belum ada riwayat peminjaman barang." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="px-5 py-3 border-t border-[#E5E7EB]">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

</div>

@if(auth()->user()->isAdmin())
<div id="rejectModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50" style="display:none">
    <div class="card max-w-md w-full mx-4">
        <div class="card-header">
            <h3>Tolak Peminjaman</h3>
        </div>
        <div class="card-body">
            <p class="text-sm text-secondary mb-4" id="rejectInfo"></p>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Alasan Penolakan <span class="text-danger">*</span></label>
                    <textarea name="rejection_reason" required rows="3" class="form-input" placeholder="Contoh: Barang sedang dipakai untuk keperluan lain..."></textarea>
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeRejectModal()" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-danger">Tolak Peminjaman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal(id, kode, peminjam) {
    document.getElementById('rejectInfo').textContent = 'Menolak peminjaman ' + kode + ' oleh ' + peminjam;
    document.getElementById('rejectForm').action = '/transactions/' + id + '/reject';
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) closeRejectModal();
});
</script>
@endif

@endsection
