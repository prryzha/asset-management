@props(['status' => ''])

@php
$dot = 'bg-gray-400';
$classes = 'badge-gray';

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
        $classes = 'badge-red';
        $dot = 'bg-danger-500';
        break;
    case 'Dikerjakan':
        $classes = 'badge-blue';
        $dot = 'bg-primary-500';
        break;
}
@endphp

<span class="{{ $classes }} gap-1.5">
    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
    {{ $status }}
</span>