<?php

namespace App\Observers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;

class ActivityLogObserver
{
    /**
     * Handle the ActivityLog "created" event.
     */
    public function created(ActivityLog $activityLog): void
    {
        Cache::forget('activity_events_list');
    }

    /**
     * Handle the ActivityLog "updated" event.
     */
    public function updated(ActivityLog $activityLog): void
    {
        Cache::forget('activity_events_list');
    }

    /**
     * Handle the ActivityLog "deleted" event.
     */
    public function deleted(ActivityLog $activityLog): void
    {
        Cache::forget('activity_events_list');
    }
}
