<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantOnboardingService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function __construct(private readonly TenantOnboardingService $onboarding) {}

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
            'business_category' => ['required', 'string', 'in:'.implode(',', config('business.enabled_categories'))],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var User $user */
        $user = $this->onboarding->register($validated);

        // Trigger verification email
        event(new Registered($user));

        // Auto-login the user
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
