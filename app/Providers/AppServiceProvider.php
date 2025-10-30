<?php

namespace App\Providers;

use App\Models\Company;
use App\Observers\CompanyObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No changes needed here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // This line registers your existing observer. We will keep it.
        Company::observe(CompanyObserver::class);

        // This tells Laravel to always wrap single JSON Resource responses in a 'data' key,
        // which fixes the inconsistency and makes the API predictable.
        JsonResource::wrap('data');
    }
}
