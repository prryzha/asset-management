@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-8">

    {{-- ===== PAGE HEADER ===== --}}
    <x-ui.page-header title="Dashboard" subtitle="Ringkasan aset dan aktivitas terkini." />

    {{-- ===== BARIS 1: SUMMARY CARDS ===== --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">

        {{-- Total Aset --}}
        <div class="stat-card">
            <div class="flex items-start gap-3">
                <div class="stat-icon bg-primary-50 dark:bg-primary-900/30">
                    <svg class="w-5 h-5 text-primary dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Aset Aktif</p>
                    <h2 class="stat-value">{{ $totalAsset }}</h2>
                    <p class="stat-detail truncate">Rp {{ number_format($totalNilai, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Tersedia --}}
        <div class="stat-card">
            <div class="flex items-start gap-3">
                <div class="stat-icon bg-success-50 dark:bg-emerald-900/30">
                    <svg class="w-5 h-5 text-success dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Tersedia</p>
                    <h2 class="stat-value">{{ $tersedia }}</h2>
                    <p class="stat-detail">Aset tersedia</p>
                </div>
            </div>
        </div>

        {{-- Dipinjam --}}
        <div class="stat-card">
            <div class="flex items-start gap-3">
                <div class="stat-icon bg-warning-50 dark:bg-amber-900/30">
                    <svg class="w-5 h-5 text-warning dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Dipinjam</p>
                    <h2 class="stat-value">{{ $dipinjam }}</h2>
                    <p class="stat-detail">Aset sedang dipinjam</p>
                </div>
            </div>
        </div>

        {{-- Perbaikan --}}
        <div class="stat-card">
            <div class="flex items-start gap-3">
                <div class="stat-icon bg-danger-50 dark:bg-red-900/30">
                    <svg class="w-5 h-5 text-danger dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Perbaikan</p>
                    <h2 class="stat-value">{{ $perbaikan }}</h2>
                    <p class="stat-detail">Aset dalam perbaikan</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== BARIS 2: TWO COLUMNS ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 mb-6">

        {{-- LEFT COLUMN (3/5) --}}
        <div class="xl:col-span-3 space-y-6 min-w-0">

            {{-- Jadwal Perawatan --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Jadwal Perawatan</h3>
                        <p class="text-xs text-secondary mt-0.5">Daftar jadwal perawatan aset</p>
                    </div>
                    <a href="{{ route('maintenance.create') }}" class="btn-primary btn-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Jadwalkan
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>Nama Aset</th>
                                <th>Jenis</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($maintenanceUpcoming ?? collect()) as $ms)
                            <tr>
                                <td class="font-normal">{{ \Carbon\Carbon::parse($ms->tanggal_jadwal)->format('d M Y') }}</td>
                                <td>
                                    <span class="font-normal">{{ $ms->asset?->kode_barang ?? '-' }}</span>
                                    <span class="text-xs text-secondary block">{{ $ms->asset?->nama_barang ?? 'Aset Dihapus' }}</span>
                                </td>
                                <td class="text-secondary">{{ $ms->jenis_perawatan }}</td>
                                <td class="text-center">
                                    <x-ui.badge-status :status="$ms->status" />
                                </td>
                                <td class="text-center">
                                    @if($ms->status == 'Dijadwalkan')
                                        <a href="{{ route('maintenance.edit', $ms) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">Ubah</a>
                                    @elseif($ms->status == 'Dikerjakan')
                                        <a href="{{ route('maintenance.complete-form', $ms) }}" class="btn-primary btn-sm px-2 py-1 text-xs">Selesaikan</a>
                                    @else
                                        <span class="text-xs text-secondary">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-10">
                                    <span class="text-sm text-secondary">Tidak ada jadwal perawatan.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(($maintenanceUpcoming ?? collect())->count() > 0)
                <div class="px-5 py-3 border-t border-[#E5E7EB] dark:border-gray-700">
                    <a href="{{ route('maintenance.index') }}" class="text-xs font-normal text-primary hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                        Lihat Semua Jadwal &rarr;
                    </a>
                </div>
                @endif
            </div>

            {{-- Aktivitas Terbaru — sama persis dengan sistem visual Jadwal Perawatan di atas --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Aktivitas Terbaru</h3>
                        <p class="text-xs text-secondary mt-0.5">Log aktivitas sistem terbaru</p>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pengguna</th>
                                <th>Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActivities as $activity)
                            <tr>
                                <td class="whitespace-nowrap text-secondary">{{ $activity->created_at->format('d/m/Y') }}</td>
                                <td class="font-normal">{{ $activity->user->name ?? 'Sistem' }}</td>
                                <td class="text-secondary">{{ $activity->description }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-10">
                                    <span class="text-sm text-secondary">Belum ada aktivitas.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentActivities->count() > 0)
                <div class="px-5 py-3 border-t border-[#E5E7EB] dark:border-gray-700">
                    <a href="{{ route('activity-logs.index') }}" class="text-xs font-normal text-primary hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                        Lihat Semua &rarr;
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN (2/5) --}}
        <div class="xl:col-span-2 space-y-6 min-w-0">

            {{-- Alert Cards / Info Highlights --}}
            @include('components.dashboard.alerts')

            {{-- Status Aset --}}
            @include('components.dashboard.charts')
        </div>
    </div>

</div>
@endsection
