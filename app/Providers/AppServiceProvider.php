<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Matikan Debugbar secara default — dia开销 banget! Enable hanya kalo butuh debug.
        // Untuk enable: tambah DEBUGBAR_ENABLED=true di .env
        if (env('DEBUGBAR_ENABLED', false) !== true) {
            config(['debugbar.enabled' => false]);
        }

        Paginator::useTailwind();

        // Register observer untuk auto-invalidate cache activity events
        ActivityLog::observe(ActivityLogObserver::class);
    }
}
