<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; 

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role  // This will be 'company_admin' from our route
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // 1. Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Get the authenticated user
        $user = $request->user();

        // 3. Check if the user's role matches the required role
        if ($user->role === $role) {
            // 4. Role matches, proceed with the request
            return $next($request);
        }

        // 5. Role does not match, return a 403 Forbidden error
        return response()->json([
            'message' => 'This action is unauthorized.'
        ], 403);
    }
}
