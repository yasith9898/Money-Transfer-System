<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access this page.');
        }

        // Check if user has super_admin role only
        $user = auth()->user();
        if ($user->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')->with('error', 'Access denied. Super admin privileges required.');
        }

        return $next($request);
    }
}
