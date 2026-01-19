<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function create(): Response
    {
        $categories = collect(config('business.enabled_categories'))
            ->mapWithKeys(function ($category) {
                $config = config("business.categories.{$category}");
                return [$category => [
                    'label' => $config['label'],
                    'icon' => $config['icon'],
                    'description' => $config['description'],
                ]];
            });

        return Inertia::render('Auth/Register', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'in:' . implode(',', config('business.enabled_categories'))],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $tenant = null;
        $user = null;

        DB::transaction(function () use ($validated, &$tenant, &$user) {
            // Create tenant with trial subscription
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'business_category' => $validated['business_category'],
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]);

            // Create admin user for the tenant
            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => 'admin',
                'is_active' => true,
            ]);
        });

        // Auto-login the user
        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
