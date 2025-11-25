<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use App\Models\MaintenanceRequest;
use App\Observers\MaintenanceRequestObserver;
use Illuminate\Support\ServiceProvider;

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
        Paginator::defaultView('vendor.pagination.custom1');

        // Register MaintenanceRequest observer to sync with native requests
        MaintenanceRequest::observe(MaintenanceRequestObserver::class);
    }
}
