@extends('layouts.app')

@section('title', 'Aktivitas Sistem')

@section('content')
<div class="p-8">

    <x-ui.page-header title="Aktivitas Sistem" subtitle="Riwayat seluruh aktivitas pengguna." />

    {{-- Filter --}}
    <div class="card mb-6">
        <div class="card-body py-2.5">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari deskripsi..."
                           class="form-input form-input-sm w-48 pl-8">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="event" class="form-input form-input-sm w-auto">
                    <option value="">Semua Aktivitas</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ $event }}</option>
                    @endforeach
                </select>
                <label class="text-xs font-normal text-gray-500 whitespace-nowrap">Dari:</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="text-xs font-normal text-gray-500 whitespace-nowrap">Sampai:</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    {{-- List --}}
    <div class="card overflow-hidden">
        @forelse($logs as $log)
        <div class="flex items-start justify-between px-5 py-4 border-b last:border-b-0 border-[#E5E7EB] dark:border-gray-700 hover:bg-[#F5F7FA] dark:hover:bg-gray-700/50 transition-colors">
            <div class="flex gap-3 min-w-0 flex-1">
                @php
                    $eventBg = 'bg-primary-50 dark:bg-primary-900/30';
                    $eventIcon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>';
                    $eventColor = 'text-primary dark:text-primary-300';
                    if(\Illuminate\Support\Str::contains($log->event, 'deleted')){ $eventBg='bg-danger-50 dark:bg-red-900/30'; $eventColor='text-danger dark:text-red-300'; $eventIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>'; }
                    elseif(\Illuminate\Support\Str::contains($log->event, 'created')){ $eventBg='bg-primary-50 dark:bg-primary-900/30'; $eventColor='text-primary dark:text-primary-300'; $eventIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>'; }
                    elseif(\Illuminate\Support\Str::contains($log->event, 'updated')){ $eventBg='bg-primary-50 dark:bg-primary-900/30'; $eventColor='text-primary dark:text-primary-300'; $eventIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 14l-4 1 1-4 8.414-8.414z"/>'; }
                    elseif(\Illuminate\Support\Str::contains($log->event, 'maintenance')){ $eventBg='bg-warning-50 dark:bg-amber-900/30'; $eventColor='text-warning dark:text-amber-300'; $eventIcon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'; }
                @endphp
                <div class="w-9 h-9 {{ $eventBg }} flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-5 h-5 {{ $eventColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $eventIcon !!}</svg>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="font-normal text-sm">{{ $log->user?->name ?? 'System' }}</h4>
                        <span class="text-xs text-secondary whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-secondary mt-0.5">{{ $log->description }}</p>
                </div>
            </div>
            <span class="ml-3 inline-flex items-center px-2.5 py-1 text-xs font-normal bg-gray-100 dark:bg-gray-700 text-secondary whitespace-nowrap">
                {{ $log->event }}
            </span>
        </div>
        @empty
        <div class="px-5 py-16 text-center">
            <x-ui.empty-state
                icon="activity"
                title="Belum Ada Aktivitas"
                description="Belum ada aktivitas yang tercatat." />
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $logs->links() }}
    </div>

</div>
@endsection