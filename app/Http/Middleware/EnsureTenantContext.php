<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Ensure user has a tenant
        if (! $user->tenant_id) {
            abort(403, 'User is not associated with any tenant.');
        }

        // Suspended by platform admin: hard block (web + API)
        if (! $user->tenant->is_active) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'type' => 'tenant_suspended',
                    'message' => 'Akun tenant Anda telah dinonaktifkan. Hubungi admin Fabriku.',
                ], 403);
            }

            abort(403, 'Akun tenant Anda telah dinonaktifkan. Hubungi admin Fabriku.');
        }

        // Ensure tenant subscription is active
        if (! $user->tenant->isActive()) {
            // For API/JSON requests (like assistant), return proper JSON response
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
                    'success' => false,
                    'type' => 'subscription_expired',
                    'message' => 'Tenant subscription has expired or is inactive.',
                ], 403);
            }

            // For web requests: allow through in read-only mode.
            // The subscription.check middleware handles write operations,
            // and the UI shows the expired status via the tenant.is_expired shared prop.
        }

        return $next($request);
    }
}
