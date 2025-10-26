<?php

namespace App\Providers;

use App\Models\Company; 
use App\Observers\CompanyObserver; 
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No changes needed here for observers
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tell Laravel to use CompanyObserver whenever Company events occur
        Company::observe(CompanyObserver::class);
    }
}
