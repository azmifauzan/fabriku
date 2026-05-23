<?php

namespace App\Http\Controllers\Auth;

use App\Events\TenantRegistered;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

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
            'business_category' => ['required', 'string', 'in:'.implode(',', config('business.enabled_categories'))],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        /** @var Tenant $tenant */
        $tenant = null;
        /** @var User $user */
        $user = null;

        DB::transaction(function () use ($validated, &$tenant, &$user) {
            // Always create trial account - 30 days free
            $expiresAt = now()->addDays(30);

            // Create tenant
            $tenant = Tenant::create([
                'name' => $validated['business_name'],
                'business_category' => $validated['business_category'],
                'subscription_plan' => 'trial',
                'subscription_expires_at' => $expiresAt,
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

            // Auto-create default data for retail category
            if ($validated['business_category'] === 'retail') {
                $this->seedRetailDefaults($tenant);
            }
        });

        // Trigger verification email
        event(new Registered($user));

        // Notify admin via Telegram
        event(new TenantRegistered($tenant, $user));

        // Auto-login the user
        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    private function seedRetailDefaults(Tenant $tenant): void
    {
        InventoryLocation::create([
            'tenant_id' => $tenant->id,
            'code' => 'TOKO-UTAMA',
            'name' => 'Toko Utama',
            'is_active' => true,
        ]);

        Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'WALK-IN',
            'name' => 'Walk-in Customer',
            'is_active' => true,
        ]);

        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Umum'],
            ['description' => 'Produk umum']
        );

        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Best Seller'],
            ['description' => 'Produk terlaris']
        );
    }
}
