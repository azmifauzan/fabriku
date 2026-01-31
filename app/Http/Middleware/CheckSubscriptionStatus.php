<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(\Illuminate\Http\Request $request, \Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        $user = $request->user();

        if (! $user || ! $user->tenant) {
            return $next($request);
        }

        // Allow read-only operations (GET, HEAD, OPTIONS)
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // Allow specific routes even when expired
        if ($request->routeIs('logout') ||
            $request->routeIs('subscription.*')) {
            return $next($request);
        }

        // Check if subscription is active
        if (! $user->tenant->isActive()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Membership expired. Account is in read-only mode. Please upgrade to continue.',
                ], 403);
            }

            return redirect()->back()->with('error', 'Membership Anda sudah expired. Akun dalam mode read-only. Silakan upgrade untuk melanjutkan.');
        }

        return $next($request);
    }
}
