<?php

namespace App\Providers;

use App\Models\SystemConfiguration;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
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
        Paginator::defaultView('vendor.pagination.custom');

        try {
            if (Schema::hasTable('system_configurations')) {
                SystemConfiguration::current()?->syncToRuntimeConfig();
            }
        } catch (\Throwable $e) {
            // Silently ignore if database is unready
        }
    }
}
