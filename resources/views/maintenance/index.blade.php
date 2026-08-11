@extends('layouts.app')

@section('title', 'Manajemen Maintenance')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Manajemen Maintenance" subtitle="Kelola jadwal maintenance seluruh aset.">
        <x-slot:actions>
            <a href="{{ route('maintenance.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Jadwalkan Maintenance
            </a>
        </x-slot:actions>
    </x-ui.page-header>

    <div class="card mb-6">
        <div class="card-body">
            <form method="GET" action="{{ route('maintenance.index') }}">
                <div class="flex items-center gap-3">
                    <label class="form-label mb-0 whitespace-nowrap">Filter Status:</label>
                    <select name="status" onchange="this.form.submit()" class="form-input w-auto">
                        <option value="">Semua Status</option>
                        <option value="Dijadwalkan" {{ request('status')=='Dijadwalkan'?'selected':'' }}>Dijadwalkan</option>
                        <option value="Dikerjakan" {{ request('status')=='Dikerjakan'?'selected':'' }}>Dikerjakan</option>
                        <option value="Selesai" {{ request('status')=='Selesai'?'selected':'' }}>Selesai</option>
                        <option value="Dibatalkan" {{ request('status')=='Dibatalkan'?'selected':'' }}>Dibatalkan</option>
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
                        <th>Asset</th>
                        <th>Jenis Perawatan</th>
                        <th class="text-center">Jadwal</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenanceSchedules as $maintenance)
                    <tr>
                        <td>
                            <div class="font-medium">{{ $maintenance->asset->kode_barang }}</div>
                            <div class="text-xs text-secondary">{{ $maintenance->asset->nama_barang }}</div>
                        </td>
                        <td>{{ $maintenance->jenis_perawatan }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($maintenance->tanggal_jadwal)->format('d M Y') }}</td>
                        <td class="text-center">
                            <x-ui.badge-status :status="$maintenance->status"/>
                        </td>
                        <td>
                            <div class="flex items-center justify-center gap-1">
                                @if($maintenance->status=='Dijadwalkan')
                                    <a href="{{ route('maintenance.edit', $maintenance) }}" class="btn-ghost btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('maintenance.start', $maintenance) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-success btn-sm px-2 py-1 text-xs">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                            </svg>
                                            Mulai
                                        </button>
                                    </form>
                                    <form action="{{ route('maintenance.cancel', $maintenance) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Batalkan maintenance ini?')">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-ghost btn-sm px-2 py-1 text-xs text-danger hover:text-white hover:bg-danger">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Batal
                                        </button>
                                    </form>
                                @elseif($maintenance->status=='Dikerjakan')
                                    <a href="{{ route('maintenance.complete-form', $maintenance) }}" class="btn-primary btn-sm px-2 py-1 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Selesaikan
                                    </a>
                                @else
                                    <span class="text-xs text-secondary">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="tool"
                                title="Belum Ada Jadwal Maintenance"
                                description="Belum ada jadwal maintenance. Silakan buat jadwal baru." />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">
        {{ $maintenanceSchedules->links() }}
    </div>

</div>
@endsection