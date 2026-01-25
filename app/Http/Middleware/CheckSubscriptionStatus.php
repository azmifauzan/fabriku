<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        if (!$user || !$user->tenant) {
            return $next($request);
        }

        // Allow read-only operations
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // Allow specific functional routes
        if ($request->routeIs('logout') || 
            $request->routeIs('subscription.store') || 
            $request->routeIs('profile.*')) { // Allow profile updates? Maybe just logout/subscription
            return $next($request);
        }

        // Check subscription status
        if (!$user->tenant->isActive()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subscription expired. Read-only mode active.'], 403);
            }

            return redirect()->back()->with('error', 'Membership expired. You are in read-only mode.');
        }

        return $next($request);
    }
}
