@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-8">

    {{-- ===== PAGE HEADER ===== --}}
    <x-ui.page-header title="Dashboard" subtitle="Overview status aset dan aktivitas sekolah." />

    {{-- ===== BARIS 1: SUMMARY CARDS ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- Total Aset --}}
        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-primary-50 dark:bg-primary-900/30">
                    <svg class="w-5 h-5 text-primary dark:text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Total Aset</p>
                    <h2 class="stat-value">{{ $totalAsset }}</h2>
                    <p class="stat-detail">Nilai: Rp {{ number_format($totalNilai, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Tersedia --}}
        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-success-50 dark:bg-emerald-900/30">
                    <svg class="w-5 h-5 text-success dark:text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Tersedia</p>
                    <h2 class="stat-value">{{ $tersedia }}</h2>
                    <p class="stat-detail">{{ $totalAsset > 0 ? round(($tersedia/$totalAsset)*100) : 0 }}% dari total</p>
                </div>
            </div>
        </div>

        {{-- Dipinjam --}}
        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-warning-50 dark:bg-amber-900/30">
                    <svg class="w-5 h-5 text-warning dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Dipinjam</p>
                    <h2 class="stat-value">{{ $dipinjam }}</h2>
                    <p class="stat-detail">{{ $totalAsset > 0 ? round(($dipinjam/$totalAsset)*100) : 0 }}% dari total</p>
                </div>
            </div>
        </div>

        {{-- Perbaikan --}}
        <div class="stat-card">
            <div class="flex items-start gap-4">
                <div class="stat-icon bg-danger-50 dark:bg-red-900/30">
                    <svg class="w-5 h-5 text-danger dark:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="stat-label">Perbaikan</p>
                    <h2 class="stat-value">{{ $perbaikan }}</h2>
                    <p class="stat-detail">{{ $totalAsset > 0 ? round(($perbaikan/$totalAsset)*100) : 0 }}% dari total</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ===== BARIS 2: TWO COLUMNS ===== --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 mb-6">

        {{-- LEFT COLUMN (3/5) --}}
        <div class="xl:col-span-3 space-y-6">

            {{-- Charts Grid --}}
            @include('components.dashboard.charts')

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
                                        <a href="{{ route('maintenance.edit', $ms) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">Edit</a>
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
        </div>

        {{-- RIGHT COLUMN (2/5) --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Alert Cards / Info Highlights --}}
            @include('components.dashboard.alerts')

            {{-- Kondisi Aset Progress Bars --}}
            <div class="card">
                <div class="card-header">
                    <h3>Kondisi Aset</h3>
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-sm text-gray-900 dark:text-gray-100">Baik</span>
                            <span class="text-sm font-normal">{{ $baik }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2">
                            <div class="bg-success h-2 transition-all duration-500" style="width: {{ $totalAsset > 0 ? ($baik/$totalAsset)*100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-sm text-gray-900 dark:text-gray-100">Kurang Baik</span>
                            <span class="text-sm font-normal">{{ $kurangBaik }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2">
                            <div class="bg-warning h-2 transition-all duration-500" style="width: {{ $totalAsset > 0 ? ($kurangBaik/$totalAsset)*100 : 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-sm text-gray-900 dark:text-gray-100">Rusak Berat</span>
                            <span class="text-sm font-normal">{{ $rusakBerat }}</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2">
                            <div class="bg-danger h-2 transition-all duration-500" style="width: {{ $totalAsset > 0 ? ($rusakBerat/$totalAsset)*100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transaction Log --}}
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3>Log Transaksi</h3>
                        <p class="text-xs text-secondary mt-0.5">Riwayat peminjaman terbaru</p>
                    </div>
                    <a href="{{ route('transactions.index') }}" class="text-xs font-normal text-primary hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                        Lihat Semua &rarr;
                    </a>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Peminjam</th>
                                <th>Barang</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($recentTransactions ?? collect()) as $trx)
                            <tr>
                                <td class="font-normal">{{ $trx->nama_peminjam }}</td>
                                <td class="text-secondary">{{ $trx->asset?->kode_barang ?? '-' }}</td>
                                <td class="text-center">
                                    <x-ui.badge-status :status="$trx->status_peminjaman" />
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-10">
                                    <span class="text-sm text-secondary">Belum ada transaksi.</span>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== BARIS 3: RECENT ACTIVITY (FULL WIDTH) ===== --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h3>Aktivitas Terbaru</h3>
                <p class="text-xs text-secondary mt-0.5">Log sistem dan riwayat aktivitas pengguna</p>
            </div>
            <a href="{{ route('activity-logs.index') }}" class="text-xs font-normal text-primary hover:text-primary-700 dark:hover:text-primary-300 transition-colors">
                Lihat Semua &rarr;
            </a>
        </div>
        <div class="divide-y divide-border">
            @forelse($recentActivities as $activity)
            <div class="flex items-start gap-3 px-5 py-3.5 hover:bg-[#F5F7FA] dark:hover:bg-gray-700/50 transition-colors">
                <div class="flex-shrink-0 mt-0.5">
                    @php
                        $event = $activity->event ?? '';
                        $bg = 'bg-[#F5F7FA] dark:bg-gray-700';
                        $iconColor = 'text-secondary';
                        $svgIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>';
                        if(\Illuminate\Support\Str::contains($event,'created')){ $bg='bg-primary-50 dark:bg-primary-900/30'; $iconColor='text-primary dark:text-primary-300'; $svgIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'; }
                        elseif(\Illuminate\Support\Str::contains($event,'updated')){ $bg='bg-primary-50 dark:bg-primary-900/30'; $iconColor='text-primary dark:text-primary-300'; $svgIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 14l-4 1 1-4 8.414-8.414z"/>'; }
                        elseif(\Illuminate\Support\Str::contains($event,'deleted')){ $bg='bg-danger-50 dark:bg-red-900/30'; $iconColor='text-danger dark:text-red-300'; $svgIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>'; }
                    @endphp
                    <div class="w-8 h-8 {{ $bg }} flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $iconColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svgIcon !!}</svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-2">
                        <div class="min-w-0">
                            <h4 class="font-normal text-sm">{{ $activity->user->name ?? 'System' }}</h4>
                            <p class="text-secondary text-xs mt-0.5">{{ $activity->description }}</p>
                        </div>
                        <span class="text-xs text-secondary whitespace-nowrap flex-shrink-0 mt-0.5">{{ $activity->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-10 text-center text-secondary text-sm">Belum ada aktivitas.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function themeColors() {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            text: isDark ? '#D1D5DB' : '#374151',
            grid: isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.06)',
        };
    }

    function buildChartDefaults() {
        const c = themeColors();
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 14, color: c.text, font: { size: 11, family: 'Poppins', weight: '500' } }
                }
            }
        };
    }

    const charts = [];

    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        charts.push(new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Tersedia', 'Dipinjam', 'Perbaikan', 'Hilang', 'Disposed'],
                datasets: [{
                    data: [{{ $tersedia }}, {{ $dipinjam }}, {{ $perbaikan }}, {{ $hilang }}, {{ $disposed }}],
                    backgroundColor: ['#16A34A', '#D97706', '#DC2626', '#7C3AED', '#6B7280'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: { ...buildChartDefaults(), cutout: '72%' }
        }));
    }

    const kondisiCanvas = document.getElementById('kondisiChart');
    if (kondisiCanvas) {
        charts.push(new Chart(kondisiCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Baik', 'Kurang Baik', 'Rusak Berat'],
                datasets: [{
                    data: [{{ $baik }}, {{ $kurangBaik }}, {{ $rusakBerat }}],
                    backgroundColor: ['#16A34A', '#D97706', '#DC2626'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: { ...buildChartDefaults(), cutout: '72%' }
        }));
    }

    const kategoriCanvas = document.getElementById('kategoriChart');
    if (kategoriCanvas) {
        charts.push(new Chart(kategoriCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($kategoriLabels),
                datasets: [{
                    data: @json($kategoriData),
                    backgroundColor: ['#2563EB','#16A34A','#D97706','#DC2626','#8B5CF6','#06B6D4','#EC4899','#14B8A6'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: { ...buildChartDefaults(), cutout: '72%' }
        }));
    }

    const lokasiCanvas = document.getElementById('lokasiChart');
    if (lokasiCanvas) {
        const c = themeColors();
        charts.push(new Chart(lokasiCanvas, {
            type: 'bar',
            data: {
                labels: @json($lokasiLabels),
                datasets: [{
                    label: 'Jumlah Aset',
                    data: @json($lokasiData),
                    backgroundColor: '#2563EB',
                    borderRadius: 4,
                    barPercentage: 0.55
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: c.text, font: { size: 10, family: 'Poppins' } }, grid: { color: c.grid }, border: { color: c.grid } },
                    x: { ticks: { color: c.text, font: { size: 9, family: 'Poppins' } }, grid: { color: c.grid }, border: { color: c.grid } }
                }
            }
        }));
    }

    // Update chart colors live saat mode gelap/terang di-toggle
    window.addEventListener('theme-changed', function () {
        const c = themeColors();
        charts.forEach(function (chart) {
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = c.text;
            }
            if (chart.options.scales?.y) {
                chart.options.scales.y.ticks.color = c.text;
                chart.options.scales.y.grid.color = c.grid;
                chart.options.scales.y.border.color = c.grid;
            }
            if (chart.options.scales?.x) {
                chart.options.scales.x.ticks.color = c.text;
                chart.options.scales.x.grid.color = c.grid;
                chart.options.scales.x.border.color = c.grid;
            }
            chart.update();
        });
    });
});
</script>
@endpush
