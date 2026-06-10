<?php

namespace App\Console\Commands;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
use App\Models\Pattern;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Staff;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\ContractorSeeder;
use Database\Seeders\InventorySeeder;
use Database\Seeders\MaterialSeeder;
use Database\Seeders\MaterialTypeSeeder;
use Database\Seeders\PatternSeeder;
use Database\Seeders\ServiceTenantSeeder;
use Database\Seeders\StaffSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'admin@tokoserbaada.com',
            'admin@homemade.com',
            'admin@bengkel.com',
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

            DB::table('services')->where('tenant_id', $tenant->id)->delete();

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
            case 'retail':
                $this->reseedRetailTenant($tenant);
                break;
            case 'homemade':
                $this->reseedHomemadeTenant($tenant);
                break;
            case 'service':
                $this->reseedServiceTenant($tenant);
                break;
            default:
                $this->warn("  Unknown business category: {$tenant->business_category}");
        }
    }

    private function reseedGarmentTenant(Tenant $tenant)
    {
        // Get the user for this tenant (needed for MaterialSeeder)
        $user = User::where('tenant_id', $tenant->id)->first();

        if (! $user) {
            return;
        }

        // Use the existing seeders with tenant context
        $staffSeeder = new StaffSeeder;
        $staffSeeder->run($tenant);

        $contractorSeeder = new ContractorSeeder;
        $contractorSeeder->run($tenant);

        $materialTypeSeeder = new MaterialTypeSeeder;
        $materialTypeSeeder->run($tenant);

        $materialSeeder = new MaterialSeeder;
        $patternSeeder = new PatternSeeder;
        $patternSeeder->run($tenant);

        $inventorySeeder = new InventorySeeder;
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

    private function reseedRetailTenant(Tenant $tenant)
    {
        // 1. Inventory Location
        $locationToko = InventoryLocation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'TOKO-UTAMA'],
            [
                'name' => 'Toko Utama',
                'is_active' => true,
            ]
        );

        // 2. Customer
        $walkIn = Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'WALK-IN'],
            [
                'name' => 'Walk-in Customer',
                'is_active' => true,
            ]
        );

        // 3. Categories
        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Umum'],
            ['description' => 'Produk umum']
        );
        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Best Seller'],
            ['description' => 'Produk terlaris']
        );

        // 4. Inventory Items
        $itemIndomie = InventoryItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'INV-RTL-001'],
            [
                'product_name' => 'Indomie Goreng',
                'product_code' => 'PROD-001',
                'source_type' => 'purchase',
                'location_id' => $locationToko->id,
                'current_quantity' => 120,
                'reserved_quantity' => 0,
                'target_quantity' => 200,
                'minimum_stock' => 20,
                'unit_cost' => 2800,
                'selling_price' => 3500,
                'status' => 'available',
            ]
        );

        $itemAquaGelas = InventoryItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'INV-RTL-002'],
            [
                'product_name' => 'Aqua Gelas 240ml',
                'product_code' => 'PROD-002',
                'source_type' => 'purchase',
                'location_id' => $locationToko->id,
                'current_quantity' => 60,
                'reserved_quantity' => 0,
                'target_quantity' => 100,
                'minimum_stock' => 10,
                'unit_cost' => 600,
                'selling_price' => 800,
                'status' => 'available',
            ]
        );

        $itemSabunMandi = InventoryItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'INV-RTL-003'],
            [
                'product_name' => 'Sabun Mandi Lifebuoy 110g',
                'product_code' => 'PROD-003',
                'source_type' => 'purchase',
                'location_id' => $locationToko->id,
                'current_quantity' => 48,
                'reserved_quantity' => 0,
                'target_quantity' => 100,
                'minimum_stock' => 10,
                'unit_cost' => 5500,
                'selling_price' => 7000,
                'status' => 'available',
            ]
        );

        $itemRokokSurya = InventoryItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'INV-RTL-004'],
            [
                'product_name' => 'Rokok Surya 16',
                'product_code' => 'PROD-004',
                'source_type' => 'purchase',
                'location_id' => $locationToko->id,
                'current_quantity' => 8,
                'reserved_quantity' => 0,
                'target_quantity' => 50,
                'minimum_stock' => 10,
                'unit_cost' => 26000,
                'selling_price' => 28000,
                'status' => 'available',
            ]
        );

        // 5. Purchase batch (StockAdjustments)
        $batchId = Str::uuid()->toString();
        $adjustments = [
            ['item' => $itemIndomie, 'qty' => 120, 'cost' => 2800],
            ['item' => $itemAquaGelas, 'qty' => 60, 'cost' => 600],
            ['item' => $itemSabunMandi, 'qty' => 48, 'cost' => 5500],
            ['item' => $itemRokokSurya, 'qty' => 8, 'cost' => 26000],
        ];

        // Get retail admin user id
        $adminUserId = User::where('tenant_id', $tenant->id)->where('role', 'admin')->value('id');

        foreach ($adjustments as $adj) {
            StockAdjustment::firstOrCreate(
                ['batch_id' => $batchId, 'inventory_item_id' => $adj['item']->id],
                [
                    'tenant_id' => $tenant->id,
                    'adjustment_type' => StockAdjustment::TYPE_PURCHASE,
                    'quantity_before' => 0,
                    'quantity_after' => $adj['qty'],
                    'adjustment_quantity' => $adj['qty'],
                    'reason' => 'Stock awal pembelian',
                    'supplier_name' => 'Grosir Maju Jaya',
                    'purchase_invoice' => 'INV/GM/2026/001',
                    'unit_cost' => $adj['cost'],
                    'adjusted_by' => $adminUserId,
                ]
            );
        }

        // 6. Demo sales orders
        $salesRetail1 = SalesOrder::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => 'SO-RTL-2026-001'],
            [
                'customer_id' => $walkIn->id,
                'order_date' => now()->subHours(3),
                'channel' => 'offline',
                'status' => 'completed',
                'subtotal' => 14300,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'shipping_cost' => 0,
                'total_amount' => 14300,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_amount' => 14300,
                'completed_date' => now()->subHours(3),
            ]
        );

        SalesOrderItem::firstOrCreate(
            ['sales_order_id' => $salesRetail1->id, 'inventory_item_id' => $itemIndomie->id],
            ['product_name' => $itemIndomie->product_name, 'sku' => $itemIndomie->sku, 'quantity' => 3, 'unit_price' => 3500, 'discount_amount' => 0, 'subtotal' => 10500]
        );
        SalesOrderItem::firstOrCreate(
            ['sales_order_id' => $salesRetail1->id, 'inventory_item_id' => $itemAquaGelas->id],
            ['product_name' => $itemAquaGelas->product_name, 'sku' => $itemAquaGelas->sku, 'quantity' => 4, 'unit_price' => 800, 'discount_amount' => 0, 'subtotal' => 3200]
        );
    }

    private function reseedServiceTenant(Tenant $tenant)
    {
        // Walk-in customer untuk Quick Checkout
        Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'WALK-IN'],
            ['name' => 'Walk-in Customer', 'is_active' => true]
        );

        // Lokasi, kategori, sparepart inventory, staff montir, katalog layanan,
        // dan consumable mapping di-handle oleh ServiceTenantSeeder.
        (new ServiceTenantSeeder)->run($tenant);
    }

    private function reseedHomemadeTenant(Tenant $tenant)
    {
        // 1. Inventory Location
        $locationDapur = InventoryLocation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'DAPUR-UTAMA'],
            [
                'name' => 'Dapur Utama',
                'is_active' => true,
            ]
        );

        // 2. Customer
        $walkIn = Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'WALK-IN'],
            [
                'name' => 'Walk-in Customer',
                'is_active' => true,
            ]
        );

        // 3. Category
        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Coklat'],
            ['description' => 'Produk coklat praline dan bar']
        );

        // 4. Material Types
        $materialTypeBaku = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Bahan Baku',
            'code' => 'MAT-H-BAKU',
            'unit' => 'kg',
            'description' => 'Bahan baku utama produksi',
        ]);

        $materialTypeKemasan = MaterialType::create([
            'tenant_id' => $tenant->id,
            'name' => 'Kemasan',
            'code' => 'MAT-H-KEMASAN',
            'unit' => 'pcs',
            'description' => 'Kotak dan kemasan kemas',
        ]);

        // 5. Materials
        $materialCoklat = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeBaku->id,
            'code' => 'CKL-001',
            'name' => 'Coklat Batang Dark',
            'supplier_name' => 'Distributor Bahan Coklat',
            'price_per_unit' => 45000,
            'stock_quantity' => 0,
            'min_stock' => 5,
            'unit' => 'kg',
            'description' => 'Coklat batang dark premium',
        ]);

        $materialGulaHalus = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeBaku->id,
            'code' => 'GULH-001',
            'name' => 'Gula Halus Tepung',
            'supplier_name' => 'Distributor Bahan Coklat',
            'price_per_unit' => 18000,
            'stock_quantity' => 0,
            'min_stock' => 2,
            'unit' => 'kg',
            'description' => 'Gula halus tepung berkualitas',
        ]);

        $materialKotak = Material::create([
            'tenant_id' => $tenant->id,
            'material_type_id' => $materialTypeKemasan->id,
            'code' => 'BOX-001',
            'name' => 'Kotak Praline Isi 12',
            'supplier_name' => 'Percetakan Kemasan Utama',
            'price_per_unit' => 3000,
            'stock_quantity' => 0,
            'min_stock' => 20,
            'unit' => 'pcs',
            'description' => 'Kotak praline isi 12 eksklusif',
        ]);

        // 6. Receipts
        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialCoklat->id,
            'receipt_number' => 'RCV-H-2026-001',
            'supplier_name' => 'Distributor Bahan Coklat',
            'quantity' => 15,
            'remaining_quantity' => 15,
            'status' => 'active',
            'unit' => 'kg',
            'price_per_unit' => 45000,
            'total_cost' => 675000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BCH-CKL-001',
        ]);

        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialGulaHalus->id,
            'receipt_number' => 'RCV-H-2026-002',
            'supplier_name' => 'Distributor Bahan Coklat',
            'quantity' => 10,
            'remaining_quantity' => 10,
            'status' => 'active',
            'unit' => 'kg',
            'price_per_unit' => 18000,
            'total_cost' => 180000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BCH-GUL-001',
        ]);

        MaterialReceipt::create([
            'tenant_id' => $tenant->id,
            'material_id' => $materialKotak->id,
            'receipt_number' => 'RCV-H-2026-003',
            'supplier_name' => 'Percetakan Kemasan Utama',
            'quantity' => 100,
            'remaining_quantity' => 100,
            'status' => 'active',
            'unit' => 'pcs',
            'price_per_unit' => 3000,
            'total_cost' => 300000,
            'receipt_date' => now()->subDays(4),
            'batch_number' => 'BCH-BOX-001',
        ]);

        // 7. Patterns (Recipes)
        $recipePraline = Pattern::create([
            'tenant_id' => $tenant->id,
            'code' => 'RCP-PRALINE',
            'name' => 'Coklat Praline Klasik',
            'output_quantity' => 1,
            'description' => 'Coklat praline klasik dengan isian cream',
            'estimated_labor_cost' => 5000,
            'instructions' => 'Lelehkan coklat dark, tuangkan ke cetakan, dinginkan',
            'is_active' => true,
        ]);

        // 8. Inventory Items
        $itemPralineBox = InventoryItem::create([
            'tenant_id' => $tenant->id,
            'sku' => 'INV-HMD-001',
            'product_name' => 'Coklat Praline Isi 12 Box',
            'product_code' => 'PROD-HMD-01',
            'source_type' => 'production',
            'location_id' => $locationDapur->id,
            'current_quantity' => 20,
            'reserved_quantity' => 0,
            'target_quantity' => 50,
            'minimum_stock' => 5,
            'unit_cost' => 8000,
            'selling_price' => 25000,
            'status' => 'available',
        ]);

        // Log opening balance adjustment
        $adminUserId = User::where('tenant_id', $tenant->id)->where('role', 'admin')->value('id');
        StockAdjustment::create([
            'tenant_id' => $tenant->id,
            'inventory_item_id' => $itemPralineBox->id,
            'adjustment_type' => StockAdjustment::TYPE_OPENING_BALANCE,
            'quantity_before' => 0,
            'quantity_after' => 20,
            'adjustment_quantity' => 20,
            'reason' => 'Stock awal pembukaan',
            'notes' => 'Saldo awal produk jadi',
            'unit_cost' => 8000,
            'adjusted_by' => $adminUserId,
        ]);
    }
}
