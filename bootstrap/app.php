<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckSubscriptionStatus;
use App\Http\Middleware\EnsureTenantContext;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveStorefront;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                SubstituteBindings::class,
                ResolveStorefront::class,
            ])
                ->domain('{storefront_host}')
                ->where(['storefront_host' => '^(?!.*(app\.fabriku\.id|fabriku\.id$|^localhost$)).*'])
                ->group(base_path('routes/storefront.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withEvents(discover: false)
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'internal/webhooks/*',
        ]);

        $middleware->alias([
            'tenant' => EnsureTenantContext::class,
            'admin' => AdminMiddleware::class,
            'subscription.check' => CheckSubscriptionStatus::class,
            'permission' => CheckPermission::class,
            'storefront' => ResolveStorefront::class,
        ]);

        // Trust all proxies for reverse proxy setup
        $middleware->trustProxies(at: '*', headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO |
            Request::HEADER_X_FORWARDED_AWS_ELB);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->header('X-Inertia')) {
                    return redirect()->route('login')
                        ->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
                }

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sesi CSRF telah berakhir. Silakan muat ulang halaman.',
                    ], 419);
                }

                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan masuk kembali.');
            }
        });
    })->create();
