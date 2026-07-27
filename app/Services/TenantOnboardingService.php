<?php

namespace App\Services;

use App\Events\TenantRegistered;
use App\Models\Customer;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantOnboardingService
{
    /**
     * Create a trial tenant plus its admin user.
     *
     * @param  array{business_name: string, business_category: string, name: string, email: string, password?: string|null, google_id?: string|null, email_verified_at?: Carbon|null}  $data
     */
    public function register(array $data): User
    {
        /** @var Tenant $tenant */
        $tenant = null;
        /** @var User $user */
        $user = null;

        DB::transaction(function () use ($data, &$tenant, &$user) {
            $tenant = Tenant::create([
                'name' => $data['business_name'],
                'business_category' => $data['business_category'],
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'email' => $data['email'],
                // Google-only accounts get an unusable random password; they can only authenticate via Google.
                'password' => $data['password'] ?? Str::random(40),
                'google_id' => $data['google_id'] ?? null,
                'email_verified_at' => $data['email_verified_at'] ?? null,
                'role' => 'admin',
                'is_active' => true,
            ]);

            if ($data['business_category'] === 'retail') {
                $this->seedRetailDefaults($tenant);
            }

            if ($data['business_category'] === 'service') {
                $this->seedServiceDefaults($tenant);
            }
        });

        event(new TenantRegistered($tenant, $user));

        return $user;
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

    private function seedServiceDefaults(Tenant $tenant): void
    {
        InventoryLocation::create([
            'tenant_id' => $tenant->id,
            'code' => 'TEMPAT-USAHA',
            'name' => 'Tempat Usaha',
            'is_active' => true,
        ]);

        Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'WALK-IN',
            'name' => 'Walk-in Customer',
            'is_active' => true,
        ]);

        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Produk & Sparepart'],
            ['description' => 'Produk fisik pendamping layanan']
        );
    }
}
