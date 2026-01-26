<?php

namespace Database\Seeders;

use App\Models\Contractor;
use App\Models\Customer;
use App\Models\InventoryItem;
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
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
        $tenantGarment = Tenant::create([
            'name' => 'Konveksi Fabriku',
            'business_category' => 'garment',
            'subscription_plan' => 'trial',
            'subscription_expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Users for Garment
        User::create([
            'tenant_id' => $tenantGarment->id,
            'name' => 'Admin Konveksi',
            'email' => 'admin@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'tenant_id' => $tenantGarment->id,
            'name' => 'Manager Produksi',
            'email' => 'manager@konveksi.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

        // 3. Create Staff
        $staffPreparation = Staff::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'STF-001',
            'name' => 'Siti Nurhaliza',
            'position' => 'Cutting',
            'phone' => '08123456789',
            'is_active' => true,
        ]);

        $staffQC = Staff::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'STF-002',
            'name' => 'Ahmad Yani',
            'position' => 'Quality Control',
            'phone' => '08123456790',
            'is_active' => true,
        ]);

        // 4. Create Contractors
        $contractor1 = Contractor::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'CTR-001',
            'name' => 'Konveksi Berkah',
            'type' => 'company',
            'specialty' => 'Jahit mukena & gamis',
            'contact_person' => 'Bu Aisyah',
            'phone' => '08111222333',
            'is_active' => true,
        ]);

        $contractor2 = Contractor::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'CTR-002',
            'name' => 'Penjahit Maju Jaya',
            'type' => 'individual',
            'specialty' => 'Jahit mukena',
            'contact_person' => 'Pak Budi',
            'phone' => '08222333444',
            'is_active' => true,
        ]);

        // 5. Create Material Types
        $materialTypeKain = MaterialType::create([
            'tenant_id' => $tenantGarment->id,
            'name' => 'Kain',
            'code' => 'MAT-KAIN',
            'unit' => 'meter',
            'description' => 'Kain baku untuk produksi pakaian',
        ]);

        $materialTypeBenang = MaterialType::create([
            'tenant_id' => $tenantGarment->id,
            'name' => 'Benang',
            'code' => 'MAT-BENANG',
            'unit' => 'cone',
            'description' => 'Benang jahit',
        ]);

        // 6. Create Materials
        $materialKatun = Material::create([
            'tenant_id' => $tenantGarment->id,
            'material_type_id' => $materialTypeKain->id,
            'code' => 'KTN-001',
            'name' => 'Kain Katun Premium',
            'supplier_name' => 'PT Tekstil Indo',
            'price_per_unit' => 45000,
            'stock_quantity' => 0,
            'min_stock' => 20,
            'unit' => 'meter',
            'description' => 'Kain katun premium untuk mukena',
        ]);

        $materialPolyester = Material::create([
            'tenant_id' => $tenantGarment->id,
            'material_type_id' => $materialTypeKain->id,
            'code' => 'PLY-001',
            'name' => 'Kain Polyester',
            'supplier_name' => 'CV Kain Jaya',
            'price_per_unit' => 35000,
            'stock_quantity' => 0,
            'min_stock' => 15,
            'unit' => 'meter',
            'description' => 'Kain polyester untuk gamis',
        ]);

        $materialBenang = Material::create([
            'tenant_id' => $tenantGarment->id,
            'material_type_id' => $materialTypeBenang->id,
            'code' => 'BNG-001',
            'name' => 'Benang Jahit Polyester',
            'supplier_name' => 'Toko Benang Sentosa',
            'price_per_unit' => 25000,
            'stock_quantity' => 0,
            'min_stock' => 10,
            'unit' => 'cone',
        ]);

        // 7. Create Material Receipts (Stock In)
        // 7. Create Material Receipts (Stock In)
        $receiptKatun = MaterialReceipt::create([
            'tenant_id' => $tenantGarment->id,
            'material_id' => $materialKatun->id,
            'receipt_number' => 'RCV-2026-001',
            'supplier_name' => 'PT Tekstil Indo',
            'quantity' => 100,
            'remaining_quantity' => 100, // Initial remaining
            'status' => 'active',
            'unit' => 'meter',
            'price_per_unit' => 45000,
            'total_cost' => 4500000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BATCH-KTN-001',
        ]);

        // Additional batch for Kain Katun (Different supplier, different price)
        MaterialReceipt::create([
            'tenant_id' => $tenantGarment->id,
            'material_id' => $materialKatun->id,
            'receipt_number' => 'RCV-2026-001-B',
            'supplier_name' => 'CV Tekstil Murah',
            'quantity' => 150,
            'remaining_quantity' => 150,
            'status' => 'active',
            'unit' => 'meter',
            'price_per_unit' => 42000, // Cheaper batch
            'total_cost' => 6300000,
            'receipt_date' => now()->subDays(2),
            'batch_number' => 'BATCH-KTN-002',
        ]);

        // Another batch for Kain Katun (Same supplier, recent date)
        MaterialReceipt::create([
            'tenant_id' => $tenantGarment->id,
            'material_id' => $materialKatun->id,
            'receipt_number' => 'RCV-2026-001-C',
            'supplier_name' => 'PT Tekstil Indo',
            'quantity' => 50,
            'remaining_quantity' => 50,
            'status' => 'active',
            'unit' => 'meter',
            'price_per_unit' => 46000, // Price increase
            'total_cost' => 2300000,
            'receipt_date' => now()->subHours(12),
            'batch_number' => 'BATCH-KTN-003',
        ]);

        $receiptPolyester = MaterialReceipt::create([
            'tenant_id' => $tenantGarment->id,
            'material_id' => $materialPolyester->id,
            'receipt_number' => 'RCV-2026-002',
            'supplier_name' => 'CV Kain Jaya',
            'quantity' => 80,
            'remaining_quantity' => 80, // Initial remaining
            'status' => 'active',
            'unit' => 'meter',
            'price_per_unit' => 35000,
            'total_cost' => 2800000,
            'receipt_date' => now()->subDays(4),
            'batch_number' => 'BATCH-PLY-001',
        ]);

        $receiptBenang = MaterialReceipt::create([
            'tenant_id' => $tenantGarment->id,
            'material_id' => $materialBenang->id,
            'receipt_number' => 'RCV-2026-003',
            'supplier_name' => 'Toko Benang Sentosa',
            'quantity' => 50,
            'remaining_quantity' => 50, // Initial remaining
            'status' => 'active',
            'unit' => 'cone',
            'price_per_unit' => 25000,
            'total_cost' => 1250000,
            'receipt_date' => now()->subDays(5),
            'batch_number' => 'BATCH-BNG-001',
        ]);

        // 8. Create Patterns (Product Templates with BOM)
        $patternMukena = Pattern::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'PTN-MUKENA-001',
            'name' => 'Mukena Dewasa Premium',
            'output_quantity' => 1,
            'description' => 'Mukena dewasa bahan katun premium',
            'estimated_labor_cost' => 15000,
            'instructions' => 'Pola mukena standar dewasa dengan tas',
            'is_active' => true,
        ]);

        $patternGamis = Pattern::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'PTN-GAMIS-001',
            'name' => 'Gamis Casual',
            'output_quantity' => 1,
            'description' => 'Gamis casual bahan polyester',
            'estimated_labor_cost' => 20000,
            'instructions' => 'Pola gamis casual',
            'is_active' => true,
        ]);

        // 9. Create Preparation Orders (Cutting Orders) - COMPLETED
        $preparationMukena = PreparationOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'PRP-2026-001',
            'pattern_id' => $patternMukena->id,
            'prepared_by' => $staffPreparation->id,
            'output_quantity' => 20,
            // JSON material_usage for legacy/display purposes
            'material_usage' => [
                ['material_id' => $materialKatun->id, 'material_name' => $materialKatun->name, 'quantity' => 52, 'unit' => $materialKatun->unit], // 2.5 x 20 + waste
                ['material_id' => $materialBenang->id, 'material_name' => $materialBenang->name, 'quantity' => 2.5, 'unit' => $materialBenang->unit],
            ],
            'waste_percentage' => 4.0,
            'status' => 'completed',
            'preparation_date' => now()->subDays(3),
            'completed_date' => now()->subDays(2),
            'notes' => 'Cutting 20 pcs mukena dewasa',
        ]);

        $preparationGamis = PreparationOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'PRP-2026-002',
            'pattern_id' => $patternGamis->id,
            'prepared_by' => $staffPreparation->id,
            'output_quantity' => 15,
            'material_usage' => [
                ['material_id' => $materialPolyester->id, 'material_name' => $materialPolyester->name, 'quantity' => 47, 'unit' => $materialPolyester->unit], // 3.0 x 15 + waste
                ['material_id' => $materialBenang->id, 'material_name' => $materialBenang->name, 'quantity' => 2.5, 'unit' => $materialBenang->unit],
            ],
            'waste_percentage' => 4.5,
            'status' => 'completed',
            'preparation_date' => now()->subDays(2),
            'completed_date' => now()->subDays(1),
            'notes' => 'Cutting 15 pcs gamis size L',
        ]);

        // 10. Create Production Orders - Mix of statuses
        $productionMukena = ProductionOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'PO-2026-001',
            'preparation_order_id' => $preparationMukena->id,
            'type' => 'external',
            'contractor_id' => $contractor1->id,
            'labor_cost' => 300000, // 20 x 15000
            'estimated_completion_date' => now()->addDays(7),
            'sent_date' => now()->subDays(2),
            'completed_date' => now()->subHours(12),
            'status' => 'completed', // COMPLETED - ready for inventory
            'priority' => 'normal',
            'notes' => 'Order 20 pcs mukena ke Konveksi Berkah',
        ]);

        $productionGamis = ProductionOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'PO-2026-002',
            'preparation_order_id' => $preparationGamis->id,
            'type' => 'external',
            'contractor_id' => $contractor2->id,
            'labor_cost' => 180000, // 15 x 12000
            'estimated_completion_date' => now()->addDays(10),
            'sent_date' => now()->subHours(6),
            'status' => 'sent', // SENT - in progress with contractor
            'priority' => 'normal',
            'notes' => 'Order 15 pcs gamis ke Penjahit Maju Jaya',
        ]);

        // Create another preparation that's NOT yet sent to production
        $preparationMukenaBatch2 = PreparationOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'PRP-2026-003',
            'pattern_id' => $patternMukena->id,
            'prepared_by' => $staffPreparation->id,
            'output_quantity' => 10,
            'material_usage' => [
                ['material_id' => $materialKatun->id, 'material_name' => $materialKatun->name, 'quantity' => 26, 'unit' => $materialKatun->unit],
                ['material_id' => $materialBenang->id, 'material_name' => $materialBenang->name, 'quantity' => 1.2, 'unit' => $materialBenang->unit],
            ],
            'waste_percentage' => 4.0,
            'status' => 'completed',
            'preparation_date' => now()->subHours(12),
            'completed_date' => now()->subHours(6),
            'notes' => 'Cutting 10 pcs mukena batch 2',
        ]);

        // Production order DRAFT (ready to send to contractor)
        ProductionOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'PO-2026-003',
            'preparation_order_id' => $preparationMukenaBatch2->id,
            'type' => 'external',
            'contractor_id' => $contractor1->id,
            'labor_cost' => 150000,
            'estimated_completion_date' => now()->addDays(8),
            'status' => 'draft', // DRAFT - not yet sent
            'priority' => 'normal',
            'notes' => 'Order 10 pcs mukena batch 2',
        ]);

        // 11. Create Inventory Locations
        $location1 = InventoryLocation::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'RACK-A1',
            'name' => 'Rak A1 - Mukena',
            'capacity' => 100,
            'is_active' => true,
        ]);

        $location2 = InventoryLocation::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'RACK-B1',
            'name' => 'Rak B1 - Gamis',
            'capacity' => 100,
            'is_active' => true,
        ]);

        // 12. Create Inventory Items (from completed production order)
        $inventoryMukena = InventoryItem::create([
            'tenant_id' => $tenantGarment->id,
            'sku' => 'INV-MUKENA-001',
            'production_order_id' => $productionMukena->id,
            'location_id' => $location1->id,
            'product_name' => 'Mukena Dewasa Premium',
            'product_code' => 'PTN-MUKENA-001',
            'target_quantity' => 18,
            'current_quantity' => 18,
            'reserved_quantity' => 0,
            'quality_grade' => 'A',
            'unit_cost' => 65000, // material + labor
            'selling_price' => 150000,
        ]);

        // 13. Create Customers
        $customer1 = Customer::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'CUST-001',
            'name' => 'Toko Busana Muslim Amanah',
            'phone' => '08555666777',
            'email' => 'amanah@example.com',
            'address' => 'Jl. Merdeka No. 123, Jakarta',
        ]);

        $customer2 = Customer::create([
            'tenant_id' => $tenantGarment->id,
            'code' => 'CUST-002',
            'name' => 'Ibu Siti',
            'phone' => '08666777888',
            'address' => 'Jl. Sudirman No. 45, Bandung',
        ]);

        // 14. Create Sales Orders
        $salesOrder1 = SalesOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'SO-2026-001',
            'customer_id' => $customer1->id,
            'order_date' => now(),
            'delivery_date' => now()->addDays(2),
            'channel' => 'offline',
            'status' => 'completed',
            'subtotal' => 1500000, // 10 pcs x 150000
            'discount_amount' => 100000,
            'tax_amount' => 0,
            'shipping_cost' => 50000,
            'total_amount' => 1450000,
            'payment_status' => 'paid',
            'paid_amount' => 1450000,
            'completed_date' => now(),
            'shipping_address' => 'Jl. Merdeka No. 123, Jakarta',
            'notes' => 'Order reseller - 10 pcs mukena',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder1->id,
            'inventory_item_id' => $inventoryMukena->id,
            'product_name' => 'Mukena Dewasa Premium',
            'sku' => 'INV-MUKENA-001',
            'quantity' => 10,
            'unit_price' => 150000,
            'discount_amount' => 100000,
            'subtotal' => 1400000,
        ]);

        // Update inventory after sales
        $inventoryMukena->decrement('current_quantity', 10);

        $salesOrder2 = SalesOrder::create([
            'tenant_id' => $tenantGarment->id,
            'order_number' => 'SO-2026-002',
            'customer_id' => $customer2->id,
            'order_date' => now(),
            'delivery_date' => now()->addDays(1),
            'channel' => 'online',
            'status' => 'confirmed',
            'subtotal' => 300000, // 2 pcs x 150000
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 25000,
            'total_amount' => 325000,
            'payment_status' => 'pending',
            'paid_amount' => 0,
            'shipping_address' => 'Jl. Sudirman No. 45, Bandung',
            'notes' => 'Order online marketplace',
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $salesOrder2->id,
            'inventory_item_id' => $inventoryMukena->id,
            'product_name' => 'Mukena Dewasa Premium',
            'sku' => 'INV-MUKENA-001',
            'quantity' => 2,
            'unit_price' => 150000,
            'discount_amount' => 0,
            'subtotal' => 300000,
        ]);

        // Reserve inventory for pending order
        $inventoryMukena->increment('reserved_quantity', 2);

        // ==========================================
        // TENANT 2: KUE MAMA HOMEMADE (FOOD)
        // ==========================================
        $tenantFood = Tenant::create([
            'name' => 'Kue Mama Homemade',
            'business_category' => 'food',
            'subscription_plan' => 'trial',
            'subscription_expires_at' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Users for Food
        User::create([
            'tenant_id' => $tenantFood->id,
            'name' => 'Admin Kue Mama',
            'email' => 'admin@kuemama.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'tenant_id' => $tenantFood->id,
            'name' => 'Manager Dapur',
            'email' => 'manager@kuemama.com',
            'password' => Hash::make('password'),
            'role' => 'manager',
            'email_verified_at' => now(),
        ]);

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

        // Update material stock after preparation
        $materialTepung->decrement('stock_quantity', 2.9);
        $materialGula->decrement('stock_quantity', 2.0);
        $materialTelur->decrement('stock_quantity', 1.5);

        // Production Orders for Food (Baking)
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
        echo "   • Users: 2 users\n";
        echo "   • Staff: 2 (cutting, QC)\n";
        echo "   • Contractors: 2 external\n";
        echo "   • Materials: 3 types (Kain Katun, Polyester, Benang)\n";
        echo "   • Patterns: 2 (Mukena & Gamis)\n";
        echo "   • Preparation Orders: 3 (all completed)\n";
        echo "   • Production Orders: 3 (1 completed, 1 sent, 1 pending)\n";
        echo "   • Inventory: 18 pcs mukena (6 available, 2 reserved, 10 sold)\n";
        echo "   • Sales Orders: 2 (1 delivered, 1 processing)\n";
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
    }
}
