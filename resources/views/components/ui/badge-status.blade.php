@props(['status' => ''])

@php
$dot = 'bg-gray-400';
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
        $dot = 'bg-success-500';
        break;
    case 'Dipinjam':
    case 'Dijadwalkan':
    case 'Kurang Baik':
    case 'Menunggu Persetujuan':
        $classes = 'badge-yellow';
        $dot = 'bg-warning-500';
        break;
    case 'Perbaikan':
    case 'Rusak Berat':
    case 'Dibatalkan':
    case 'Ditolak':
    case 'Hilang':
        $classes = 'badge-red';
        $dot = 'bg-danger-500';
        break;
    case 'Dikerjakan':
        $classes = 'badge-blue';
        $dot = 'bg-primary-500';
        break;
    case 'Disposed':
        $classes = 'badge-gray';
        $dot = 'bg-gray-400';
        break;
}
@endphp

<span class="{{ $classes }} gap-1.5">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ $label }}
</span>