<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if a user is logged in AND if their role is one of the admin types
        if ($request->user() && in_array($request->user()->role, ['hr_admin', 'super_admin'])) {
            return $next($request); // If yes, allow the request to continue
        }

        abort(403, 'UNAUTHORIZED ACTION'); // If no, stop and show a "Forbidden" error
    }
}
