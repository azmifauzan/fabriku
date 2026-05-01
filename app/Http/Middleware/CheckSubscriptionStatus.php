<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
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
                // Provide a more helpful message for assistant routes
                if ($request->routeIs('assistant.*')) {
                    return response()->json([
                        'success' => false,
                        'type' => 'subscription_expired',
                        'message' => '⏰ Masa aktif langganan Anda telah berakhir. '.
                            'Fabriku Assistant tidak dapat digunakan sampai Anda memperpanjang langganan. '.
                            'Silakan perpanjang langganan di menu Pengaturan > Langganan untuk melanjutkan menggunakan fitur ini.',
                    ], 403);
                }

                return response()->json([
                    'message' => 'Membership expired. Account is in read-only mode. Please upgrade to continue.',
                ], 403);
            }

            return redirect()->back()->with('warning', 'Langganan Anda telah berakhir. Akun dalam mode read-only. Silakan perpanjang langganan untuk melanjutkan.');
        }

        return $next($request);
    }
}
