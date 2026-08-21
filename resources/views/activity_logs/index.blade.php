@extends('layouts.app')

@section('title', __('ui.activity_logs.title'))

@section('content')
@php
    $subjectShortLabels = [
        \App\Models\Asset::class => 'Asset',
        \App\Models\Category::class => 'Category',
        \App\Models\Location::class => 'Location',
        \App\Models\Transaction::class => 'Transaction',
        \App\Models\MaintenanceSchedule::class => 'MaintenanceSchedule',
    ];
@endphp
<div class="page-content">

    <x-ui.page-header :title="__('ui.activity_logs.title')" />

    {{-- Filter --}}
    <div class="card overflow-hidden">
        <x-ui.table-heading :title="__('ui.activity_logs.log_aktivitas_sistem')" />

        <div class="card-body-compact border-b border-default">
            <form method="GET" class="filter-form">
                <div class="search-input-wrapper">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="{{ __('ui.activity_logs.search_placeholder') }}"
                           class="form-input form-input-sm w-48 pl-8">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </div>
                <select name="event" class="form-input form-input-sm w-auto">
                    <option value="">{{ __('ui.activity_logs.semua_aktivitas') }}</option>
                    @foreach($events as $event)
                        <option value="{{ $event }}" {{ request('event') == $event ? 'selected' : '' }}>{{ __('ui.activity_logs.event.' . $event) }}</option>
                    @endforeach
                </select>
                <label class="filter-label">{{ __('ui.activity_logs.dari') }}</label>
                <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}" class="form-input form-input-sm w-auto">
                <label class="filter-label">{{ __('ui.activity_logs.sampai') }}</label>
                <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}" class="form-input form-input-sm w-auto">
                <button type="submit" class="btn-primary btn-icon" title="{{ __('ui.common.cari') }}" aria-label="{{ __('ui.common.cari') }}">
                    <svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5-5m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                    </svg>
                </button>
                @if(request()->hasAny(['search','event','tanggal_dari','tanggal_sampai']))
                <a href="{{ route('activity-logs.index') }}" class="btn-ghost btn-icon" title="{{ __('ui.common.reset_filter') }}" aria-label="{{ __('ui.common.reset_filter') }}"><svg class="icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></a>
                @endif
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('ui.activity_logs.tanggal_waktu') }}</th>
                        <th>{{ __('ui.activity_logs.pengguna') }}</th>
                        <th>{{ __('ui.activity_logs.aktivitas') }}</th>
                        <th>{{ __('ui.activity_logs.objek') }}</th>
                        <th>{{ __('ui.activity_logs.detail') }}</th>
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
                        $subjectKey = $subjectShortLabels[$log->subject_type] ?? null;
                    @endphp
                    <tr>
                        <td class="whitespace-nowrap">
                            <div class="text-xs">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-secondary">{{ $log->created_at->format('H:i') }}</div>
                        </td>
                        <td class="text-xs">{{ $log->user?->name ?? __('ui.activity_logs.sistem') }}</td>
                        <td>
                            <span class="{{ $eventBadge }} whitespace-nowrap">{{ __('ui.activity_logs.event.' . $log->event) }}</span>
                        </td>
                        <td class="text-secondary text-xs whitespace-nowrap">{{ $subjectKey ? __('ui.activity_logs.subject.' . $subjectKey) : '—' }}</td>
                        <td class="text-secondary text-xs max-w-md truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <x-ui.empty-state
                                icon="activity"
                                :title="__('ui.activity_logs.empty_title')"
                                :description="__('ui.activity_logs.empty_desc')" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-5 py-3 border-t border-default">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
