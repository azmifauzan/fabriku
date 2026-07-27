<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TenantOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private const SESSION_KEY = 'pending_google_user';

    public function __construct(private readonly TenantOnboardingService $onboarding) {}

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Existing password-based account with a matching, Google-verified email: link it.
                $user->forceFill([
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();
            }
        }

        if ($user) {
            Auth::login($user, true);
            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('dashboard'));
        }

        // Brand new email: hold the Google profile in session and ask for business details before creating the tenant.
        $request->session()->put(self::SESSION_KEY, [
            'google_id' => $googleUser->getId(),
            'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: $googleUser->getEmail(),
            'email' => $googleUser->getEmail(),
        ]);

        return redirect()->route('google.complete');
    }

    public function showComplete(Request $request): Response|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('register');
        }

        $categories = collect(config('business.enabled_categories'))
            ->mapWithKeys(function ($category) {
                $config = config("business.categories.{$category}");

                return [$category => [
                    'label' => $config['label'],
                    'icon' => $config['icon'],
                    'description' => $config['description'],
                ]];
            });

        return Inertia::render('Auth/CompleteGoogleRegistration', [
            'categories' => $categories,
            'name' => $pending['name'],
            'email' => $pending['email'],
        ]);
    }

    public function storeComplete(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! $pending) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_category' => ['required', 'string', 'in:'.implode(',', config('business.enabled_categories'))],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $this->onboarding->register([
            'business_name' => $validated['business_name'],
            'business_category' => $validated['business_category'],
            'name' => $validated['name'],
            'email' => $pending['email'],
            'google_id' => $pending['google_id'],
            'email_verified_at' => now(),
        ]);

        $request->session()->forget(self::SESSION_KEY);

        Auth::login($user, true);

        return redirect()->route('dashboard');
    }
}
