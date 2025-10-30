<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\RateLimiter; 
use Illuminate\Cache\RateLimiting\Limit; 

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api', // All routes in api.php get /api prefix
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        // Define rate limiters after basic routing setup
        then: function () {
            // Define the 'api' rate limiter used by $middleware->throttleApi()
            RateLimiter::for('api', function (Request $request) { // Now Request is recognized
                // Use Limit imported above
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Apply the 'api' rate limiter defined above to all routes in routes/api.php
        $middleware->throttleApi();

        // Configure Sanctum middleware for SPA authentication
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Register custom middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserHasAdminRole::class,
            'role'  => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling configuration can go here if needed later
    })->create();
