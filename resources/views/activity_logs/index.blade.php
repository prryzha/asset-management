@extends('layouts.app')

@section('title', 'Aktivitas Sistem')

@section('content')
@php
    $eventLabels = [
        'asset.created' => 'Aset Ditambahkan',
        'asset.updated' => 'Aset Diubah',
        'asset.deleted' => 'Aset Dihapus',
        'asset.photo-deleted' => 'Foto Aset Dihapus',
        'asset.reported-damage' => 'Kerusakan Dilaporkan',
        'asset.reported-lost' => 'Aset Dilaporkan Hilang',
        'asset.marked-found' => 'Aset Ditandai Ditemukan',
        'asset.disposed' => 'Aset Dihapuskan',
        'transaction.borrowed' => 'Aset Dipinjam',
        'transaction.returned' => 'Aset Dikembalikan',
        'maintenance.created' => 'Perawatan Dijadwalkan',
        'maintenance.updated' => 'Jadwal Perawatan Diubah',
        'maintenance.started' => 'Perawatan Dimulai',
        'maintenance.completed' => 'Perawatan Selesai',
        'maintenance.cancelled' => 'Perawatan Dibatalkan',
        'category.created' => 'Kategori Ditambahkan',
        'category.updated' => 'Kategori Diubah',
        'category.deleted' => 'Kategori Dihapus',
        'location.created' => 'Lokasi Ditambahkan',
        'location.updated' => 'Lokasi Diubah',
        'location.deleted' => 'Lokasi Dihapus',
    ];

    $subjectLabels = [
        \App\Models\Asset::class => 'Aset',
        \App\Models\Category::class => 'Kategori',
        \App\Models\Location::class => 'Lokasi',
        \App\Models\Transaction::class => 'Peminjaman',
        \App\Models\MaintenanceSchedule::class => 'Perawatan',
    ];
@endphp
<div class="page-content">

    <x-ui.page-header title="Aktivitas Sistem" />

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body-compact">
            <form method="GET" class="filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari deskripsi..."
                           class="form-input form-input-sm w-48 pl-8">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="event" class="form-input form-input-sm w-auto">
                    <option value="">Semua Aktivitas</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ $eventLabels[$event] ?? $event }}</option>
                    @endforeach
                </select>
                <label class="filter-label">Dari:</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="filter-label">Sampai:</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-sm">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                    Cari
                </button>
                @if(request()->hasAny(['search','event','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('activity-logs.index') }}" class="btn-ghost btn-sm">Reset Filter</a>
                @endif
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal/Waktu</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Objek</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $eventBadge = 'badge-gray';
                        if(\Illuminate\Support\Str::contains($log->event, 'deleted')){ $eventBadge = 'badge-red'; }
                        elseif(\Illuminate\Support\Str::contains($log->event, ['created','borrowed'])){ $eventBadge = 'badge-blue'; }
                        elseif(\Illuminate\Support\Str::contains($log->event, ['updated','returned','marked-found'])){ $eventBadge = 'badge-blue'; }
                        elseif(\Illuminate\Support\Str::contains($log->event, 'maintenance')){ $eventBadge = 'badge-yellow'; }
                        elseif(\Illuminate\Support\Str::contains($log->event, ['reported-damage','reported-lost'])){ $eventBadge = 'badge-red'; }
                        elseif(\Illuminate\Support\Str::contains($log->event, 'disposed')){ $eventBadge = 'badge-gray'; }
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap">
                            <div class="text-xs">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-secondary">{{ $log->created_at->format('H:i') }}</div>
                        </td>
                        <td class="text-xs">{{ $log->user?->name ?? 'Sistem' }}</td>
                        <td>
                            <span class="{{ $eventBadge }} whitespace-nowrap">{{ $eventLabels[$log->event] ?? $log->event }}</span>
                        </td>
                        <td class="text-secondary text-xs whitespace-nowrap">{{ $subjectLabels[$log->subject_type] ?? '—' }}</td>
                        <td class="text-secondary text-xs max-w-md truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="activity"
                                title="Belum Ada Aktivitas"
                                description="Belum ada aktivitas yang tercatat." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $logs->links() }}
    </div>

</div>
@endsection