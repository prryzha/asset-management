@props(['status' => ''])

@php
$dot = 'badge-dot-neutral';
$classes = 'badge-gray';
// Nilai DB (mis. "Tersedia", "Disposed") tetap dipakai apa adanya sebagai
// KEY lookup — tidak pernah diubah, itu identifier teknis. Yang berubah
// per-locale cuma label yang ditampilkan, lewat ui.status di lang/{id,en}.
$label = __('ui.status.' . $status);
$label = $label === 'ui.status.' . $status ? $status : $label;

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