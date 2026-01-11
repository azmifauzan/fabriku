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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
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

        // Ensure tenant is active
        if (! $user->tenant->isActive()) {
            auth()->logout();
            abort(403, 'Tenant subscription has expired or is inactive.');
        }

        return $next($request);
    }
}
