<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\ProductionOrder;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Staff;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database - Complete workflow from materials to sales.
     */
    public function run(): void
    {
        // ==========================================
        // ADMIN SETUP (Platform Level)
        // ==========================================
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            AdminUserSeeder::class,
        ]);

        // ==========================================
        // TENANT 1: KONVEKSI FABRIKU (GARMENT)
        // ==========================================
        $tenantGarment = Tenant::firstOrCreate(
            ['name' => 'Konveksi Fabriku'],
            [
                'business_category' => 'garment',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        // Users for Garment
        User::firstOrCreate(
            ['email' => 'admin@konveksi.com'],
            [
                'tenant_id' => $tenantGarment->id,
                'name' => 'Admin Konveksi',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@konveksi.com'],
            [
                'tenant_id' => $tenantGarment->id,
                'name' => 'Manager Produksi',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        // 3. Run Seeders for Garment Tenant
        // Use the newly refactored seeders to populate data using the definitions in those files
        // This ensures that any changes to MaterialSeeder etc are reflected here, and removes hardcoded 'demo' data.

        $this->command->info('Seeding Garment Tenant data...');

        (new StaffSeeder)->run($tenantGarment);
        (new ContractorSeeder)->run($tenantGarment);
        (new MaterialTypeSeeder)->run($tenantGarment);
        (new MaterialSeeder)->run($tenantGarment);
        (new PatternSeeder)->run($tenantGarment);
        (new InventorySeeder)->run($tenantGarment);

        // NOTE: Previous hardcoded Production/Preparation/Sales orders are removed
        // to allow for a clean state based on the seeded Master Data.
        // If specific demo transactions are needed, they should be added to a dedicated DemoTransactionSeeder
        // or implemented in the individual seeders.

        // ==========================================
        // TENANT 2: KUE MAMA HOMEMADE (FOOD)
        // ==========================================
        $tenantFood = Tenant::firstOrCreate(
            ['name' => 'Kue Mama Homemade'],
            [
                'business_category' => 'food',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        // Users for Food
        User::firstOrCreate(
            ['email' => 'admin@kuemama.com'],
            [
                'tenant_id' => $tenantFood->id,
                'name' => 'Admin Kue Mama',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@kuemama.com'],
            [
                'tenant_id' => $tenantFood->id,
                'name' => 'Manager Dapur',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        // Staff for Food
        $staffMixing = Staff::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'STF-F001',
            'name' => 'Ibu Ratna',
            'position' => 'Mixing',
            'phone' => '08111223344',
            'is_active' => true,
        ]);

        $staffBaking = Staff::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'STF-F002',
            'name' => 'Ibu Dewi',
            'position' => 'Baking',
            'phone' => '08222334455',
            'is_active' => true,
        ]);

        // Contractors for Food
        $contractorBakery = Contractor::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'CTR-F001',
            'name' => 'Dapur Kue Sentosa',
            'type' => 'company',
            'specialty' => 'Baking kue & cookies',
            'contact_person' => 'Bu Ani',
            'phone' => '08333445566',
            'is_active' => true,
        ]);

        // Material Types for Food
        $materialTypeBahanKering = MaterialType::create([
            'tenant_id' => $tenantFood->id,
            'name' => 'Bahan Kering',
            'code' => 'MAT-F-KERING',
            'unit' => 'kg',
            'description' => 'Bahan kering seperti tepung, gula',
        ]);

        $materialTypeBahanBasah = MaterialType::create([
            'tenant_id' => $tenantFood->id,
            'name' => 'Bahan Basah',
            'code' => 'MAT-F-BASAH',
            'unit' => 'kg',
            'description' => 'Bahan basah seperti telur, mentega',
        ]);

        // Materials for Food
        $materialTepung = Material::create([
            'tenant_id' => $tenantFood->id,
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
            'tenant_id' => $tenantFood->id,
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
            'tenant_id' => $tenantFood->id,
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

        // Material Receipts for Food
        MaterialReceipt::create([
            'tenant_id' => $tenantFood->id,
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
            'tenant_id' => $tenantFood->id,
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
            'tenant_id' => $tenantFood->id,
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

        // Patterns (Recipes) for Food
        $recipeBrownies = Pattern::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'RCP-BROWNIES-001',
            'name' => 'Brownies Coklat Premium',
            'output_quantity' => 16,
            'description' => 'Brownies coklat premium potong 16 (ukuran 20x20cm)',
            'estimated_labor_cost' => 30000,
            'instructions' => 'Campur semua bahan, panggang 180C selama 35 menit',
            'is_active' => true,
        ]);

        $recipeCookies = Pattern::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'RCP-COOKIES-001',
            'name' => 'Cookies Coklat Chip',
            'output_quantity' => 50,
            'description' => 'Cookies coklat chip isi 50 pcs ukuran medium',
            'estimated_labor_cost' => 25000,
            'instructions' => 'Bentuk bulat, panggang 170C selama 15 menit',
            'is_active' => true,
        ]);

        // Preparation Orders for Food (Mixing/Prep)
        $prepBrownies = PreparationOrder::create([
            'tenant_id' => $tenantFood->id,
            'order_number' => 'PREP-F-2026-001',
            'pattern_id' => $recipeBrownies->id,
            'prepared_by' => $staffMixing->id,
            'output_quantity' => 48,
            'material_usage' => [
                ['material_id' => $materialTepung->id, 'material_name' => $materialTepung->name, 'quantity' => 1.6, 'unit' => $materialTepung->unit],
                ['material_id' => $materialGula->id, 'material_name' => $materialGula->name, 'quantity' => 1.3, 'unit' => $materialGula->unit],
                ['material_id' => $materialTelur->id, 'material_name' => $materialTelur->name, 'quantity' => 1.0, 'unit' => $materialTelur->unit],
            ],
            'waste_percentage' => 3.0,
            'status' => 'completed',
            'preparation_date' => now()->subDays(3),
            'completed_date' => now()->subDays(2),
            'notes' => 'Mixing 3 loyang brownies (16 pcs x 3)',
        ]);

        $prepCookies = PreparationOrder::create([
            'tenant_id' => $tenantFood->id,
            'order_number' => 'PREP-F-2026-002',
            'pattern_id' => $recipeCookies->id,
            'prepared_by' => $staffMixing->id,
            'output_quantity' => 100,
            'material_usage' => [
                ['material_id' => $materialTepung->id, 'material_name' => $materialTepung->name, 'quantity' => 1.3, 'unit' => $materialTepung->unit],
                ['material_id' => $materialGula->id, 'material_name' => $materialGula->name, 'quantity' => 0.7, 'unit' => $materialGula->unit],
                ['material_id' => $materialTelur->id, 'material_name' => $materialTelur->name, 'quantity' => 0.5, 'unit' => $materialTelur->unit],
            ],
            'waste_percentage' => 2.5,
            'status' => 'completed',
            'preparation_date' => now()->subDays(2),
            'completed_date' => now()->subDays(1),
            'notes' => 'Mixing 100 pcs cookies',
        ]);

        // Production Orders for Food (Baking)
        // NOTE: Material stock already auto-deducted by PreparationOrderObserver
        $productionBrownies = ProductionOrder::create([
            'tenant_id' => $tenantFood->id,
            'order_number' => 'PO-F-2026-001',
            'preparation_order_id' => $prepBrownies->id,
            'type' => 'internal',
            'contractor_id' => null,
            'labor_cost' => 90000,
            'estimated_completion_date' => now()->addDays(5),
            'sent_date' => null,
            'completed_date' => now()->subHours(12),
            'status' => 'completed',
            'priority' => 'normal',
            'notes' => 'Baking brownies internal dapur',
        ]);

        $productionCookies = ProductionOrder::create([
            'tenant_id' => $tenantFood->id,
            'order_number' => 'PO-F-2026-002',
            'preparation_order_id' => $prepCookies->id,
            'type' => 'external',
            'contractor_id' => $contractorBakery->id,
            'labor_cost' => 50000,
            'estimated_completion_date' => now()->addDays(7),
            'sent_date' => now()->subHours(8),
            'status' => 'sent',
            'priority' => 'normal',
            'notes' => 'Order baking cookies ke Dapur Kue Sentosa',
        ]);

        // Inventory Locations for Food
        $locationCooling = InventoryLocation::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'COOL-1',
            'name' => 'Cooling Rack 1 - Brownies',
            'capacity' => 100,
            'is_active' => true,
        ]);

        $locationPacking = InventoryLocation::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'PACK-1',
            'name' => 'Packaging Area 1 - Cookies',
            'capacity' => 150,
            'is_active' => true,
        ]);

        // Inventory Items for Food
        $inventoryBrownies = InventoryItem::create([
            'tenant_id' => $tenantFood->id,
            'sku' => 'INV-BROWNIES-001',
            'production_order_id' => $productionBrownies->id,
            'source_type' => 'production',
            'location_id' => $locationCooling->id,
            'product_name' => 'Brownies Coklat Premium',
            'product_code' => 'RCP-BROWNIES-001',
            'target_quantity' => 46,
            'current_quantity' => 46,
            'reserved_quantity' => 0,
            'quality_grade' => 'A',
            'unit_cost' => 8000,
            'selling_price' => 15000,
            'expired_date' => now()->addDays(7),
        ]);

        // Customers for Food
        $customerCafe = Customer::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'CUST-F001',
            'name' => 'Cafe Corner Kopi',
            'phone' => '08777888999',
            'email' => 'cafe@example.com',
            'address' => 'Jl. Senopati No. 88, Jakarta',
        ]);

        $customerRetail = Customer::create([
            'tenant_id' => $tenantFood->id,
            'code' => 'CUST-F002',
            'name' => 'Ibu Lina',
            'phone' => '08888999000',
            'address' => 'Jl. Cikini No. 12, Jakarta',
        ]);

        // Sales Orders for Food
        $salesOrderCafe = SalesOrder::create([
            'tenant_id' => $tenantFood->id,
            'order_number' => 'SO-F-2026-001',
            'customer_id' => $customerCafe->id,
            'order_date' => now(),
            'delivery_date' => now()->addDays(1),
            'channel' => 'offline',
            'status' => 'completed',
            'subtotal' => 450000,
            'discount_amount' => 50000,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'total_amount' => 400000,
            'payment_status' => 'paid',
            'paid_amount' => 400000,
            'completed_date' => now(),
            'shipping_address' => 'Jl. Senopati No. 88, Jakarta',
            'notes' => 'Order reseller cafe - 30 pcs brownies',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrderCafe->id,
            'inventory_item_id' => $inventoryBrownies->id,
            'product_name' => 'Brownies Coklat Premium',
            'sku' => 'INV-BROWNIES-001',
            'quantity' => 30,
            'unit_price' => 15000,
            'discount_amount' => 50000,
            'subtotal' => 400000,
        ]);

        $inventoryBrownies->decrement('current_quantity', 30);

        $salesOrderRetail = SalesOrder::create([
            'tenant_id' => $tenantFood->id,
            'order_number' => 'SO-F-2026-002',
            'customer_id' => $customerRetail->id,
            'order_date' => now(),
            'delivery_date' => now()->addDays(1),
            'channel' => 'online',
            'status' => 'confirmed',
            'subtotal' => 90000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 20000,
            'total_amount' => 110000,
            'payment_status' => 'pending',
            'paid_amount' => 0,
            'shipping_address' => 'Jl. Cikini No. 12, Jakarta',
            'notes' => 'Order online via marketplace',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrderRetail->id,
            'inventory_item_id' => $inventoryBrownies->id,
            'product_name' => 'Brownies Coklat Premium',
            'sku' => 'INV-BROWNIES-001',
            'quantity' => 6,
            'unit_price' => 15000,
            'discount_amount' => 0,
            'subtotal' => 90000,
        ]);

        $inventoryBrownies->increment('reserved_quantity', 6);

        echo "\n✅ Database seeded successfully!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📊 GARMENT Demo - Konveksi Fabriku:\n";
        echo "   • Tenant: {$tenantGarment->name}\n";
        echo "   • Users: 2 users (Admin, Manager)\n";
        echo "   • Data: Populated via Seeders (Staff, Materials, Patterns, Inventory)\n";
        echo "   • Note: Transactions (Orders/Sales) are clean.\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔑 GARMENT Login:\n";
        echo "   🧵 Email: admin@konveksi.com\n";
        echo "   🔐 Password: password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        echo "📊 FOOD Demo - Kue Mama Homemade:\n";
        echo "   • Tenant: {$tenantFood->name}\n";
        echo "   • Users: 2 users\n";
        echo "   • Staff: 2 (mixing, baking)\n";
        echo "   • Contractors: 1 external\n";
        echo "   • Materials: 3 types (Tepung, Gula, Telur)\n";
        echo "   • Recipes: 2 (Brownies & Cookies)\n";
        echo "   • Preparation Orders: 2 (all completed)\n";
        echo "   • Production Orders: 2 (1 completed, 1 sent)\n";
        echo "   • Inventory: 46 pcs brownies (10 available, 6 reserved, 30 sold)\n";
        echo "   • Sales Orders: 2 (1 delivered, 1 processing)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔑 FOOD Login:\n";
        echo "   🍰 Email: admin@kuemama.com\n";
        echo "   🔐 Password: password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // ==========================================
        // TENANT 3: CRAFTY HANDMADE (CRAFT)
        // ==========================================
        $tenantCraft = Tenant::firstOrCreate(
            ['name' => 'Crafty Handmade'],
            [
                'business_category' => 'craft',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        // Users for Craft
        User::firstOrCreate(
            ['email' => 'admin@crafty.com'],
            [
                'tenant_id' => $tenantCraft->id,
                'name' => 'Admin Crafty',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@crafty.com'],
            [
                'tenant_id' => $tenantCraft->id,
                'name' => 'Manager Produksi',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        // Staff for Craft
        Staff::create([
            'tenant_id' => $tenantCraft->id,
            'code' => 'STF-C001',
            'name' => 'Ibu Rina',
            'position' => 'Crafter',
            'phone' => '08123456001',
            'is_active' => true,
        ]);

        // Material Types for Craft
        $materialTypeCraftBase = MaterialType::create([
            'tenant_id' => $tenantCraft->id,
            'name' => 'Bahan Dasar',
            'code' => 'MAT-C-BASE',
            'unit' => 'pcs',
            'description' => 'Bahan dasar kerajinan',
        ]);

        // Materials for Craft
        $materialKertas = Material::create([
            'tenant_id' => $tenantCraft->id,
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
            'tenant_id' => $tenantCraft->id,
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

        // Material Receipts for Craft
        MaterialReceipt::create([
            'tenant_id' => $tenantCraft->id,
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
            'tenant_id' => $tenantCraft->id,
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

        // Pattern for Craft
        Pattern::create([
            'tenant_id' => $tenantCraft->id,
            'code' => 'DSN-GIFTBOX-001',
            'name' => 'Gift Box Premium',
            'output_quantity' => 1,
            'description' => 'Gift box premium ukuran medium',
            'estimated_labor_cost' => 10000,
            'instructions' => 'Lipat kertas, lem, dan hias dengan pita',
            'is_active' => true,
        ]);

        // Customers for Craft
        Customer::create([
            'tenant_id' => $tenantCraft->id,
            'code' => 'CUST-C001',
            'name' => 'Event Organizer Sukses',
            'phone' => '08123456789',
            'email' => 'eo@example.com',
            'address' => 'Jl. Event No. 1, Yogyakarta',
        ]);

        // Inventory Locations for Craft
        InventoryLocation::create([
            'tenant_id' => $tenantCraft->id,
            'code' => 'RACK-C1',
            'name' => 'Rak Produk Jadi',
            'capacity' => 200,
            'is_active' => true,
        ]);

        echo "📊 CRAFT Demo - Crafty Handmade:\n";
        echo "   • Tenant: {$tenantCraft->name}\n";
        echo "   • Users: 2 users\n";
        echo "   • Staff: 1 (crafter)\n";
        echo "   • Materials: 2 types (Kertas, Pita)\n";
        echo "   • Patterns: 1 (Gift Box)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔑 CRAFT Login:\n";
        echo "   🎨 Email: admin@crafty.com\n";
        echo "   🔐 Password: password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // ==========================================
        // TENANT 4: GLOW BEAUTY LAB (COSMETIC)
        // ==========================================
        $tenantCosmetic = Tenant::firstOrCreate(
            ['name' => 'Glow Beauty Lab'],
            [
                'business_category' => 'cosmetic',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        // Users for Cosmetic
        User::firstOrCreate(
            ['email' => 'admin@glowbeauty.com'],
            [
                'tenant_id' => $tenantCosmetic->id,
                'name' => 'Admin Glow Beauty',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'manager@glowbeauty.com'],
            [
                'tenant_id' => $tenantCosmetic->id,
                'name' => 'Manager QC',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'email_verified_at' => now(),
            ]
        );

        // Staff for Cosmetic
        Staff::create([
            'tenant_id' => $tenantCosmetic->id,
            'code' => 'STF-CO001',
            'name' => 'Ibu Sari',
            'position' => 'Formulator',
            'phone' => '08123456002',
            'is_active' => true,
        ]);

        // Material Types for Cosmetic
        $materialTypeCosmeticBase = MaterialType::create([
            'tenant_id' => $tenantCosmetic->id,
            'name' => 'Base Ingredient',
            'code' => 'MAT-CO-BASE',
            'unit' => 'gram',
            'description' => 'Bahan dasar kosmetik',
        ]);

        $materialTypeCosmeticActive = MaterialType::create([
            'tenant_id' => $tenantCosmetic->id,
            'name' => 'Active Ingredient',
            'code' => 'MAT-CO-ACTIVE',
            'unit' => 'ml',
            'description' => 'Bahan aktif kosmetik',
        ]);

        // Materials for Cosmetic
        $materialBaseOil = Material::create([
            'tenant_id' => $tenantCosmetic->id,
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
            'tenant_id' => $tenantCosmetic->id,
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

        // Material Receipts for Cosmetic
        MaterialReceipt::create([
            'tenant_id' => $tenantCosmetic->id,
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
            'tenant_id' => $tenantCosmetic->id,
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

        // Pattern (Formula) for Cosmetic
        Pattern::create([
            'tenant_id' => $tenantCosmetic->id,
            'code' => 'FRM-SERUM-001',
            'name' => 'Brightening Serum 30ml',
            'output_quantity' => 1,
            'description' => 'Serum wajah untuk mencerahkan kulit',
            'estimated_labor_cost' => 25000,
            'instructions' => 'Campur base oil dengan niacinamide, aduk hingga homogen',
            'is_active' => true,
        ]);

        // Contractors for Cosmetic
        Contractor::create([
            'tenant_id' => $tenantCosmetic->id,
            'code' => 'CTR-CO001',
            'name' => 'Maklon Cantik Indonesia',
            'type' => 'company',
            'specialty' => 'Contract manufacturing skincare',
            'contact_person' => 'Bu Diana',
            'phone' => '08555666777',
            'is_active' => true,
        ]);

        // Customers for Cosmetic
        Customer::create([
            'tenant_id' => $tenantCosmetic->id,
            'code' => 'CUST-CO001',
            'name' => 'Klinik Kecantikan Bunda',
            'phone' => '08666777888',
            'email' => 'klinik@example.com',
            'address' => 'Jl. Kecantikan No. 99, Surabaya',
        ]);

        // Inventory Locations for Cosmetic
        InventoryLocation::create([
            'tenant_id' => $tenantCosmetic->id,
            'code' => 'RACK-CO1',
            'name' => 'Rak Produk Jadi - Cool Storage',
            'capacity' => 500,
            'is_active' => true,
        ]);

        echo "📊 COSMETIC Demo - Glow Beauty Lab:\n";
        echo "   • Tenant: {$tenantCosmetic->name}\n";
        echo "   • Users: 2 users\n";
        echo "   • Staff: 1 (formulator)\n";
        echo "   • Contractors: 1 (maklon)\n";
        echo "   • Materials: 2 types (Jojoba Oil, Niacinamide)\n";
        echo "   • Formulas: 1 (Brightening Serum)\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔑 COSMETIC Login:\n";
        echo "   💄 Email: admin@glowbeauty.com\n";
        echo "   🔐 Password: password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

        // ==========================================
        // TENANT 5: TOKO SERBA ADA (RETAIL)
        // ==========================================
        $tenantRetail = Tenant::firstOrCreate(
            ['name' => 'Toko Serba Ada'],
            [
                'business_category' => 'retail',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        // Users for Retail
        User::firstOrCreate(
            ['email' => 'admin@tokoserbaada.com'],
            [
                'tenant_id' => $tenantRetail->id,
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@tokoserbaada.com'],
            [
                'tenant_id' => $tenantRetail->id,
                'name' => 'Kasir Toko',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
            ]
        );

        // Default data for retail
        $locationToko = InventoryLocation::firstOrCreate(
            ['tenant_id' => $tenantRetail->id, 'code' => 'TOKO-UTAMA'],
            [
                'name' => 'Toko Utama',
                'is_active' => true,
            ]
        );

        $walkIn = Customer::firstOrCreate(
            ['tenant_id' => $tenantRetail->id, 'code' => 'WALK-IN'],
            [
                'name' => 'Walk-in Customer',
                'is_active' => true,
            ]
        );

        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenantRetail->id, 'name' => 'Umum'],
            ['description' => 'Produk umum']
        );
        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenantRetail->id, 'name' => 'Best Seller'],
            ['description' => 'Produk terlaris']
        );

        // Demo inventory items
        $itemIndomie = InventoryItem::firstOrCreate(
            ['tenant_id' => $tenantRetail->id, 'sku' => 'INV-RTL-001'],
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
            ['tenant_id' => $tenantRetail->id, 'sku' => 'INV-RTL-002'],
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
            ['tenant_id' => $tenantRetail->id, 'sku' => 'INV-RTL-003'],
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
            ['tenant_id' => $tenantRetail->id, 'sku' => 'INV-RTL-004'],
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

        // Demo purchase receipt (batch)
        $batchId = Str::uuid()->toString();
        $adjustments = [
            ['item' => $itemIndomie, 'qty' => 120, 'cost' => 2800],
            ['item' => $itemAquaGelas, 'qty' => 60, 'cost' => 600],
            ['item' => $itemSabunMandi, 'qty' => 48, 'cost' => 5500],
            ['item' => $itemRokokSurya, 'qty' => 8, 'cost' => 26000],
        ];

        foreach ($adjustments as $adj) {
            StockAdjustment::firstOrCreate(
                ['batch_id' => $batchId, 'inventory_item_id' => $adj['item']->id],
                [
                    'tenant_id' => $tenantRetail->id,
                    'adjustment_type' => StockAdjustment::TYPE_PURCHASE,
                    'quantity_before' => 0,
                    'quantity_after' => $adj['qty'],
                    'adjustment_quantity' => $adj['qty'],
                    'reason' => 'Stock awal pembelian',
                    'supplier_name' => 'Grosir Maju Jaya',
                    'purchase_invoice' => 'INV/GM/2026/001',
                    'unit_cost' => $adj['cost'],
                    'adjusted_by' => User::where('email', 'admin@tokoserbaada.com')->value('id'),
                ]
            );
        }

        // Demo sales orders for retail
        $salesRetail1 = SalesOrder::firstOrCreate(
            ['tenant_id' => $tenantRetail->id, 'order_number' => 'SO-RTL-2026-001'],
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

        echo "📊 RETAIL Demo - Toko Serba Ada:\n";
        echo "   • Tenant: {$tenantRetail->name}\n";
        echo "   • Users: 2 (Admin + Kasir)\n";
        echo "   • Inventory: 4 produk\n";
        echo "   • Purchase: 1 batch dari Grosir Maju Jaya\n";
        echo "   • Sales: 1 transaksi demo\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🔑 RETAIL Login:\n";
        echo "   🏪 Email: admin@tokoserbaada.com\n";
        echo "   🔐 Password: password\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }
}
