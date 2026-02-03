<?php

namespace App\Console\Commands;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
use App\Models\Pattern;
use App\Models\Staff;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:reset {--tenant= : Specific tenant ID or slug to reset} {--no-reseed : Skip reseeding data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset demo data to initial state for demo tenants and reseed with default data';

    /**
     * Execute the command.
     */
    public function handle()
    {
        // Demo tenant emails
        $demoEmails = [
            'admin@konveksi.com',
            'admin@kuemama.com',
            'admin@crafty.com',
            'admin@glowbeauty.com',
        ];

        $tenantOption = $this->option('tenant');
        $noReseed = $this->option('no-reseed');

        if ($tenantOption) {
            // Reset specific tenant
            $tenant = is_numeric($tenantOption)
                ? Tenant::find($tenantOption)
                : Tenant::where('name', 'like', "%{$tenantOption}%")->first();

            if (! $tenant) {
                return 1;
            }

            $this->resetTenant($tenant, $noReseed);
        } else {
            // Reset all demo tenants
            $demoTenants = Tenant::whereHas('users', function ($query) use ($demoEmails) {
                $query->whereIn('email', $demoEmails);
            })->get();

            if ($demoTenants->isEmpty()) {
                return 0;
            }

            foreach ($demoTenants as $tenant) {
                $this->resetTenant($tenant, $noReseed);
            }
        }

        return 0;
    }

    private function resetTenant(Tenant $tenant, bool $noReseed = false)
    {
        DB::beginTransaction();
        try {
            // Delete in correct order to avoid FK constraints
            // Check if assistant tables exist before trying to clear them
            if (DB::getSchemaBuilder()->hasTable('assistant_messages')) {
                DB::table('assistant_messages')->whereIn('conversation_id', function ($query) use ($tenant) {
                    $query->select('id')->from('assistant_conversations')->where('tenant_id', $tenant->id);
                })->delete();

                DB::table('assistant_conversations')->where('tenant_id', $tenant->id)->delete();

                DB::table('assistant_usage')->where('tenant_id', $tenant->id)->delete();
            }

            DB::table('sales_order_items')->whereIn('sales_order_id', function ($query) use ($tenant) {
                $query->select('id')->from('sales_orders')->where('tenant_id', $tenant->id);
            })->delete();

            DB::table('sales_orders')->where('tenant_id', $tenant->id)->delete();

            DB::table('inventory_items')->where('tenant_id', $tenant->id)->delete();
            DB::table('stock_adjustments')->where('tenant_id', $tenant->id)->delete();

            DB::table('production_orders')->where('tenant_id', $tenant->id)->delete();

            DB::table('preparation_material_usages')->whereIn('preparation_order_id', function ($query) use ($tenant) {
                $query->select('id')->from('preparation_orders')->where('tenant_id', $tenant->id);
            })->delete();

            DB::table('preparation_orders')->where('tenant_id', $tenant->id)->delete();

            DB::table('patterns')->where('tenant_id', $tenant->id)->delete();

            DB::table('material_receipts')->where('tenant_id', $tenant->id)->delete();

            DB::table('material_attributes')->whereIn('material_id', function ($query) use ($tenant) {
                $query->select('id')->from('materials')->where('tenant_id', $tenant->id);
            })->delete();

            DB::table('materials')->where('tenant_id', $tenant->id)->delete();

            DB::table('material_types')->where('tenant_id', $tenant->id)->delete();

            DB::table('contractors')->where('tenant_id', $tenant->id)->delete();

            DB::table('customers')->where('tenant_id', $tenant->id)->delete();

            DB::table('staff')->where('tenant_id', $tenant->id)->delete();

            DB::table('inventory_locations')->where('tenant_id', $tenant->id)->delete();

            // Keep users but reset subscription to trial with fresh 30 days
            // Force update to ensure subscription is reset properly
            $tenant->subscription_plan = 'trial';
            $tenant->subscription_expires_at = now()->startOfDay()->addDays(30);
            $tenant->trial_reminder_7days_sent_at = null;
            $tenant->trial_reminder_3days_sent_at = null;
            $tenant->trial_reminder_1day_sent_at = null;
            $tenant->is_active = true;
            $tenant->save();

            DB::commit();

            // Reseed data for this specific tenant
            if (! $noReseed) {
                $this->reseedTenant($tenant);
            }
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    private function reseedTenant(Tenant $tenant)
    {
        // Determine which seeder to run based on business category
        switch ($tenant->business_category) {
            case 'garment':
                $this->reseedGarmentTenant($tenant);
                break;
            case 'food':
                $this->reseedFoodTenant($tenant);
                break;
            case 'craft':
                $this->reseedCraftTenant($tenant);
                break;
            case 'cosmetic':
                $this->reseedCosmeticTenant($tenant);
                break;
            default:
                $this->warn("  Unknown business category: {$tenant->business_category}");
        }
    }

    private function reseedGarmentTenant(Tenant $tenant)
    {
        // Get the user for this tenant (needed for MaterialSeeder)
        $user = \App\Models\User::where('tenant_id', $tenant->id)->first();

        if (! $user) {
            return;
        }

        // Use the existing seeders with tenant context
        $staffSeeder = new \Database\Seeders\StaffSeeder;
        $staffSeeder->run($tenant);

        $contractorSeeder = new \Database\Seeders\ContractorSeeder;
        $contractorSeeder->run($tenant);

        $materialTypeSeeder = new \Database\Seeders\MaterialTypeSeeder;
        $materialTypeSeeder->run($tenant);

        $materialSeeder = new \Database\Seeders\MaterialSeeder;
        $patternSeeder = new \Database\Seeders\PatternSeeder;
        $patternSeeder->run($tenant);

        $inventorySeeder = new \Database\Seeders\InventorySeeder;
        $inventorySeeder->run($tenant);
    }

    private function reseedFoodTenant(Tenant $tenant)
    {
        // Staff
        $staffMixing = Staff::create([
            'tenant_id' => $tenant->id,
            'code' => 'STF-F001',
            'name' => 'Ibu Ratna',
            'position' => 'Mixing',
            'phone' => '08111223344',
            'is_active' => true,
        ]);

        $staffBaking = Staff::create([
            'tenant_id' => $tenant->id,
            'code' => 'STF-F002',
            'name' => 'Ibu Dewi',
            'position' => 'Baking',
            'phone' => '08222334455',
            'is_active' => true,
        ]);

        // Contractors
        $contractorBakery = Contractor::create([
            'tenant_id' => $tenant->id,
            'code' => 'CTR-F001',
            'name' => 'Dapur Kue Sentosa',
            'type' => 'company',
            'specialty' => 'Baking kue & cookies',
            'contact_person' => 'Bu Ani',
            'phone' => '08333445566',
            'is_active' => true,
        ]);

        // Material Types
        $materialTypeBahanKering = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bahan Kering',
            'code' => 'MAT-F-KERING',
            'unit' => 'kg',
            'description' => 'Bahan kering seperti tepung, gula',
        ]);

        $materialTypeBahanBasah = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bahan Basah',
            'code' => 'MAT-F-BASAH',
            'unit' => 'kg',
            'description' => 'Bahan basah seperti telur, mentega',
        ]);

        // Materials
        $materialTepung = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeBahanKering->id,
            'code' => 'TPG-001',
            'name' => 'Tepung Terigu Premium',
            'supplier_name' => 'Toko Bahan Kue Makmur',
            'price_per_unit' => 15000,
            'stock_quantity' => 0,
            'min_stock' => 10,
            'unit' => 'kg',
            'description' => 'Tepung terigu protein tinggi untuk cake',
        ]);

        $materialGula = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeBahanKering->id,
            'code' => 'GUL-001',
            'name' => 'Gula Pasir Halus',
            'supplier_name' => 'Toko Bahan Kue Makmur',
            'price_per_unit' => 18000,
            'stock_quantity' => 0,
            'min_stock' => 8,
            'unit' => 'kg',
            'description' => 'Gula pasir halus',
        ]);

        $materialTelur = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeBahanBasah->id,
            'code' => 'TLR-001',
            'name' => 'Telur Ayam',
            'supplier_name' => 'Peternak Telur Jaya',
            'price_per_unit' => 28000,
            'stock_quantity' => 0,
            'min_stock' => 10,
            'unit' => 'kg',
            'description' => 'Telur ayam segar',
        ]);

        // Material Receipts
        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialTepung->id,
            'receipt_number' => 'RCV-F-2026-001',
            'supplier_name' => 'Toko Bahan Kue Makmur',
            'quantity' => 50,
            'remaining_quantity' => 50,
            'status' => 'active',
            'unit' => 'kg',
            'price_per_unit' => 15000,
            'total_cost' => 750000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BATCH-TPG-001',
            'expired_date' => now()->addMonths(6),
        ]);

        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialGula->id,
            'receipt_number' => 'RCV-F-2026-002',
            'supplier_name' => 'Toko Bahan Kue Makmur',
            'quantity' => 30,
            'remaining_quantity' => 30,
            'status' => 'active',
            'unit' => 'kg',
            'price_per_unit' => 18000,
            'total_cost' => 540000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BATCH-GUL-001',
        ]);

        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialTelur->id,
            'receipt_number' => 'RCV-F-2026-003',
            'supplier_name' => 'Peternak Telur Jaya',
            'quantity' => 25,
            'remaining_quantity' => 25,
            'status' => 'active',
            'unit' => 'kg',
            'price_per_unit' => 28000,
            'total_cost' => 700000,
            'receipt_date' => now()->subDays(4),
            'batch_number' => 'BATCH-TLR-001',
            'expired_date' => now()->addDays(14),
        ]);

        // Patterns (Recipes)
        $recipeBrownies = Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'RCP-BROWNIES-001',
            'name' => 'Brownies Coklat Premium',
            'output_quantity' => 16,
            'description' => 'Brownies coklat premium potong 16 (ukuran 20x20cm)',
            'estimated_labor_cost' => 30000,
            'instructions' => 'Campur semua bahan, panggang 180C selama 35 menit',
            'is_active' => true,
        ]);

        $recipeCookies = Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'RCP-COOKIES-001',
            'name' => 'Cookies Coklat Chip',
            'output_quantity' => 50,
            'description' => 'Cookies coklat chip isi 50 pcs ukuran medium',
            'estimated_labor_cost' => 25000,
            'instructions' => 'Bentuk bulat, panggang 170C selama 15 menit',
            'is_active' => true,
        ]);

        // Inventory Locations
        InventoryLocation::create([
            'tenant_id' => $tenant->id,
            'code' => 'COOL-1',
            'name' => 'Cooling Rack 1 - Brownies',
            'capacity' => 100,
            'is_active' => true,
        ]);

        InventoryLocation::create([
            'tenant_id' => $tenant->id,
            'code' => 'PACK-1',
            'name' => 'Packaging Area 1 - Cookies',
            'capacity' => 150,
            'is_active' => true,
        ]);

        // Customers
        Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'CUST-F001',
            'name' => 'Cafe Corner Kopi',
            'phone' => '08777888999',
            'email' => 'cafe@example.com',
            'address' => 'Jl. Senopati No. 88, Jakarta',
        ]);

        Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'CUST-F002',
            'name' => 'Ibu Lina',
            'phone' => '08888999000',
            'address' => 'Jl. Cikini No. 12, Jakarta',
        ]);
    }

    private function reseedCraftTenant(Tenant $tenant)
    {
        // Staff
        Staff::create([
            'tenant_id' => $tenant->id,
            'code' => 'STF-C001',
            'name' => 'Ibu Rina',
            'position' => 'Crafter',
            'phone' => '08123456001',
            'is_active' => true,
        ]);

        // Material Types
        $materialTypeCraftBase = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bahan Dasar',
            'code' => 'MAT-C-BASE',
            'unit' => 'pcs',
            'description' => 'Bahan dasar kerajinan',
        ]);

        // Materials
        $materialKertas = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeCraftBase->id,
            'code' => 'KRT-001',
            'name' => 'Kertas Karton Premium',
            'supplier_name' => 'Toko Kertas Indah',
            'price_per_unit' => 5000,
            'stock_quantity' => 0,
            'min_stock' => 50,
            'unit' => 'lembar',
            'description' => 'Kertas karton untuk gift box',
        ]);

        $materialPita = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeCraftBase->id,
            'code' => 'PIT-001',
            'name' => 'Pita Satin',
            'supplier_name' => 'Toko Pita Cantik',
            'price_per_unit' => 15000,
            'stock_quantity' => 0,
            'min_stock' => 20,
            'unit' => 'roll',
            'description' => 'Pita satin untuk dekorasi',
        ]);

        // Material Receipts
        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialKertas->id,
            'receipt_number' => 'RCV-C-2026-001',
            'supplier_name' => 'Toko Kertas Indah',
            'quantity' => 100,
            'remaining_quantity' => 100,
            'status' => 'active',
            'unit' => 'lembar',
            'price_per_unit' => 5000,
            'total_cost' => 500000,
            'receipt_date' => now()->subDays(3),
            'batch_number' => 'BATCH-KRT-001',
        ]);

        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialPita->id,
            'receipt_number' => 'RCV-C-2026-002',
            'supplier_name' => 'Toko Pita Cantik',
            'quantity' => 30,
            'remaining_quantity' => 30,
            'status' => 'active',
            'unit' => 'roll',
            'price_per_unit' => 15000,
            'total_cost' => 450000,
            'receipt_date' => now()->subDays(3),
            'batch_number' => 'BATCH-PIT-001',
        ]);

        // Pattern
        Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'DSN-GIFTBOX-001',
            'name' => 'Gift Box Premium',
            'output_quantity' => 1,
            'description' => 'Gift box premium ukuran medium',
            'estimated_labor_cost' => 10000,
            'instructions' => 'Lipat kertas, lem, dan hias dengan pita',
            'is_active' => true,
        ]);

        // Customer
        Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'CUST-C001',
            'name' => 'Event Organizer Sukses',
            'phone' => '08123456789',
            'email' => 'eo@example.com',
            'address' => 'Jl. Event No. 1, Yogyakarta',
        ]);

        // Inventory Location
        InventoryLocation::create([
            'tenant_id' => $tenant->id,
            'code' => 'RACK-C1',
            'name' => 'Rak Produk Jadi',
            'capacity' => 200,
            'is_active' => true,
        ]);
    }

    private function reseedCosmeticTenant(Tenant $tenant)
    {
        // Staff
        Staff::create([
            'tenant_id' => $tenant->id,
            'code' => 'STF-CO001',
            'name' => 'Ibu Sari',
            'position' => 'Formulator',
            'phone' => '08123456002',
            'is_active' => true,
        ]);

        // Material Types
        $materialTypeCosmeticBase = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Base Ingredient',
            'code' => 'MAT-CO-BASE',
            'unit' => 'gram',
            'description' => 'Bahan dasar kosmetik',
        ]);

        $materialTypeCosmeticActive = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Active Ingredient',
            'code' => 'MAT-CO-ACTIVE',
            'unit' => 'ml',
            'description' => 'Bahan aktif kosmetik',
        ]);

        // Materials
        $materialBaseOil = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeCosmeticBase->id,
            'code' => 'OIL-001',
            'name' => 'Jojoba Oil',
            'supplier_name' => 'PT Cosmetic Supply Indonesia',
            'price_per_unit' => 500,
            'stock_quantity' => 0,
            'min_stock' => 500,
            'unit' => 'gram',
            'description' => 'Jojoba oil untuk base serum',
        ]);

        $materialNiacinamide = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeCosmeticActive->id,
            'code' => 'NIA-001',
            'name' => 'Niacinamide 10%',
            'supplier_name' => 'PT Cosmetic Supply Indonesia',
            'price_per_unit' => 1500,
            'stock_quantity' => 0,
            'min_stock' => 200,
            'unit' => 'ml',
            'description' => 'Niacinamide untuk brightening serum',
        ]);

        // Material Receipts
        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialBaseOil->id,
            'receipt_number' => 'RCV-CO-2026-001',
            'supplier_name' => 'PT Cosmetic Supply Indonesia',
            'quantity' => 1000,
            'remaining_quantity' => 1000,
            'status' => 'active',
            'unit' => 'gram',
            'price_per_unit' => 500,
            'total_cost' => 500000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BATCH-OIL-001',
            'expired_date' => now()->addYears(2),
        ]);

        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialNiacinamide->id,
            'receipt_number' => 'RCV-CO-2026-002',
            'supplier_name' => 'PT Cosmetic Supply Indonesia',
            'quantity' => 500,
            'remaining_quantity' => 500,
            'status' => 'active',
            'unit' => 'ml',
            'price_per_unit' => 1500,
            'total_cost' => 750000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BATCH-NIA-001',
            'expired_date' => now()->addYears(1),
        ]);

        // Pattern (Formula)
        Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'FRM-SERUM-001',
            'name' => 'Brightening Serum 30ml',
            'output_quantity' => 1,
            'description' => 'Serum wajah untuk mencerahkan kulit',
            'estimated_labor_cost' => 25000,
            'instructions' => 'Campur base oil dengan niacinamide, aduk hingga homogen',
            'is_active' => true,
        ]);

        // Contractor
        Contractor::create([
            'tenant_id' => $tenant->id,
            'code' => 'CTR-CO001',
            'name' => 'Maklon Cantik Indonesia',
            'type' => 'company',
            'specialty' => 'Contract manufacturing skincare',
            'contact_person' => 'Bu Diana',
            'phone' => '08555666777',
            'is_active' => true,
        ]);

        // Customer
        Customer::create([
            'tenant_id' => $tenant->id,
            'code' => 'CUST-CO001',
            'name' => 'Klinik Kecantikan Bunda',
            'phone' => '08666777888',
            'email' => 'klinik@example.com',
            'address' => 'Jl. Kecantikan No. 99, Surabaya',
        ]);

        // Inventory Location
        InventoryLocation::create([
            'tenant_id' => $tenant->id,
            'code' => 'RACK-CO1',
            'name' => 'Rak Produk Jadi - Cool Storage',
            'capacity' => 500,
            'is_active' => true,
        ]);
    }
}
