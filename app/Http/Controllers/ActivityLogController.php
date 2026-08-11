<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('event'), function ($query) use ($request) {
                $query->where('event', $request->input('event'));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        /*
         * Cache daftar event — query DISTINCT pada tabel besar sangat lambat.
         * Cache di-invalidate otomatis oleh ActivityLog::created/updated via observer
         * atau di-refresh setiap 30 menit.
         */
        $events = Cache::remember('activity_events_list', 1800, function () {
            return ActivityLog::query()
                ->select('event')
                ->distinct()
                ->orderBy('event')
                ->pluck('event');
        });

        return view('activity_logs.index', compact('logs', 'events'));
    }
}
