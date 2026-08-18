@php use Illuminate\Support\Str; @endphp

<div class="card">

    <div class="card-header">
        <div>
            <h3>Aktivitas Terbaru</h3>
            <p class="subtitle">Riwayat aktivitas terbaru sistem.</p>
        </div>
        <a href="{{ route('activity-logs.index') }}" class="text-xs font-normal text-primary-600 hover:text-primary-700 transition-colors">
            Lihat Semua &rarr;
        </a>
    </div>

    <div>
        @forelse($recentActivities as $activity)
        <div class="flex gap-4 px-6 py-4 border-b last:border-b-0 border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-100">
            <div class="flex-shrink-0">
                @php
                    $bg = 'bg-gray-100 dark:bg-gray-700';
                    $svg = '<svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>';
                    if(Str::contains($activity->event,'created')){ $bg='bg-emerald-100 dark:bg-emerald-900/30'; $svg='<svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'; }
                    elseif(Str::contains($activity->event,'updated')){ $bg='bg-blue-100 dark:bg-blue-900/30'; $svg='<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.586-9.414a2 2 0 112.828 2.828L12 14l-4 1 1-4 8.414-8.414z"/></svg>'; }
                    elseif(Str::contains($activity->event,'deleted')){ $bg='bg-red-100 dark:bg-red-900/30'; $svg='<svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'; }
                    elseif(Str::contains($activity->event,'maintenance')){ $bg='bg-amber-100 dark:bg-amber-900/30'; $svg='<svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'; }
                @endphp
                <div class="w-10 h-10 {{ $bg }} flex items-center justify-center">{!! $svg !!}</div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start gap-2">
                    <div class="min-w-0">
                        <h3 class="font-normal text-gray-800 dark:text-gray-200 text-sm">{{ $activity->user->name ?? 'Sistem' }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mt-0.5 truncate">{{ $activity->description }}</p>
                    </div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 whitespace-nowrap flex-shrink-0">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="p-10 text-center text-gray-400 dark:text-gray-500">Belum ada aktivitas.</div>
        @endforelse
    </div>

</div>