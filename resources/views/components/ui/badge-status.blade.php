@props(['status' => ''])

@php
$dot = 'badge-dot-neutral';
$classes = 'badge-gray';
// Internal DB value tetap "Disposed" (lihat AssetController::statusLabel()) — cuma
// label yang ditampilkan yang diterjemahkan, supaya tidak perlu migration/rename kolom.
$label = $status === 'Disposed' ? 'Dihapuskan' : $status;

switch($status) {
    case 'Tersedia':
    case 'Dikembalikan':
    case 'Selesai':
    case 'Baik':
        $classes = 'badge-green';
        $dot = 'badge-dot-success';
        break;
    case 'Dipinjam':
    case 'Dijadwalkan':
    case 'Kurang Baik':
    case 'Menunggu Persetujuan':
        $classes = 'badge-yellow';
        $dot = 'badge-dot-warning';
        break;
    case 'Perbaikan':
    case 'Rusak Berat':
    case 'Dibatalkan':
    case 'Ditolak':
    case 'Hilang':
        $classes = 'badge-red';
        $dot = 'badge-dot-danger';
        break;
    case 'Dikerjakan':
        $classes = 'badge-blue';
        $dot = 'badge-dot-primary';
        break;
    case 'Disposed':
        $classes = 'badge-gray';
        $dot = 'badge-dot-neutral';
        break;
}
@endphp

<span class="{{ $classes }} gap-1.5">
    <span class="badge-dot {{ $dot }}"></span>
    {{ $label }}
</span>