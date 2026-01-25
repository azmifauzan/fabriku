<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = $request->user()?->tenant;
        $categoryConfig = $tenant?->getCategoryConfig() ?? [];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                ] : null,
                'admin' => $request->user('admin') ? [
                    'id' => $request->user('admin')->id,
                    'name' => $request->user('admin')->name,
                    'email' => $request->user('admin')->email,
                    'role' => $request->user('admin')->role,
                ] : null,
            ],
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'business_category' => $tenant->business_category,
                'category_label' => $tenant->getCategoryLabel(),
                'terminology' => [
                    'material' => $tenant->getTerminology('material'),
                    'pattern' => $tenant->getTerminology('pattern'),
                    'preparation' => $tenant->getTerminology('preparation'),
                    'preparation_order' => $tenant->getTerminology('preparation_order'),
                    'production' => $tenant->getTerminology('production'),
                    'production_order' => $tenant->getTerminology('production_order'),
                    'contractor' => $tenant->getTerminology('contractor'),
                ],
                'category_config' => [
                    'product_types' => $categoryConfig['product_types'] ?? [],
                    'sizes' => $categoryConfig['sizes'] ?? [],
                ],
            ] : null,
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
