<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
use App\Models\Pattern;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HomemadeTenantSeeder extends Seeder
{
    /**
     * Run the database seeds for the Homemade demo tenant only.
     *
     * Safe to run multiple times (uses firstOrCreate for tenant/user).
     * Use `demo:reset --tenant=homemade` to wipe and reseed transactional data.
     */
    public function run(): void
    {
        // ==========================================
        // TENANT 6: DAPUR COKLAT RUMAHAN (HOMEMADE)
        // ==========================================
        $tenant = Tenant::firstOrCreate(
            ['name' => 'Dapur Coklat Rumahan'],
            [
                'business_category' => 'homemade',
                'subscription_plan' => 'trial',
                'subscription_expires_at' => now()->addDays(30),
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@homemade.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Admin Dapur Coklat',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $locationDapur = InventoryLocation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'DAPUR-UTAMA'],
            ['name' => 'Dapur Utama', 'is_active' => true]
        );

        Customer::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'WALK-IN'],
            ['name' => 'Walk-in Customer', 'is_active' => true]
        );

        InventoryItemCategory::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Coklat'],
            ['description' => 'Produk coklat praline dan bar']
        );

        // Material Types
        $materialTypeBaku = MaterialType::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'MAT-H-BAKU'],
            ['name' => 'Bahan Baku', 'unit' => 'kg', 'description' => 'Bahan baku utama produksi']
        );

        $materialTypeKemasan = MaterialType::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'MAT-H-KEMASAN'],
            ['name' => 'Kemasan', 'unit' => 'pcs', 'description' => 'Kotak dan kemasan kemas']
        );

        // Materials
        $materialCoklat = Material::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'CKL-001'],
            [
                'material_type_id' => $materialTypeBaku->id,
                'name' => 'Coklat Batang Dark',
                'supplier_name' => 'Distributor Bahan Coklat',
                'price_per_unit' => 45000,
                'stock_quantity' => 0,
                'min_stock' => 5,
                'unit' => 'kg',
                'description' => 'Coklat batang dark premium',
            ]
        );

        $materialGulaHalus = Material::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'GULH-001'],
            [
                'material_type_id' => $materialTypeBaku->id,
                'name' => 'Gula Halus Tepung',
                'supplier_name' => 'Distributor Bahan Coklat',
                'price_per_unit' => 18000,
                'stock_quantity' => 0,
                'min_stock' => 2,
                'unit' => 'kg',
                'description' => 'Gula halus tepung berkualitas',
            ]
        );

        $materialKotak = Material::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'BOX-001'],
            [
                'material_type_id' => $materialTypeKemasan->id,
                'name' => 'Kotak Praline Isi 12',
                'supplier_name' => 'Percetakan Kemasan Utama',
                'price_per_unit' => 3000,
                'stock_quantity' => 0,
                'min_stock' => 20,
                'unit' => 'pcs',
                'description' => 'Kotak praline isi 12 eksklusif',
            ]
        );

        // Material Receipts
        MaterialReceipt::firstOrCreate(
            ['tenant_id' => $tenant->id, 'receipt_number' => 'RCV-H-2026-001'],
            [
                'material_id' => $materialCoklat->id,
                'supplier_name' => 'Distributor Bahan Coklat',
                'quantity' => 15,
                'remaining_quantity' => 15,
                'status' => 'active',
                'unit' => 'kg',
                'price_per_unit' => 45000,
                'total_cost' => 675000,
                'receipt_date' => now()->subDays(5),
                'batch_number' => 'BCH-CKL-001',
            ]
        );

        MaterialReceipt::firstOrCreate(
            ['tenant_id' => $tenant->id, 'receipt_number' => 'RCV-H-2026-002'],
            [
                'material_id' => $materialGulaHalus->id,
                'supplier_name' => 'Distributor Bahan Coklat',
                'quantity' => 10,
                'remaining_quantity' => 10,
                'status' => 'active',
                'unit' => 'kg',
                'price_per_unit' => 18000,
                'total_cost' => 180000,
                'receipt_date' => now()->subDays(5),
                'batch_number' => 'BCH-GUL-001',
            ]
        );

        MaterialReceipt::firstOrCreate(
            ['tenant_id' => $tenant->id, 'receipt_number' => 'RCV-H-2026-003'],
            [
                'material_id' => $materialKotak->id,
                'supplier_name' => 'Percetakan Kemasan Utama',
                'quantity' => 100,
                'remaining_quantity' => 100,
                'status' => 'active',
                'unit' => 'pcs',
                'price_per_unit' => 3000,
                'total_cost' => 300000,
                'receipt_date' => now()->subDays(4),
                'batch_number' => 'BCH-BOX-001',
            ]
        );

        // Pattern (Recipe)
        Pattern::firstOrCreate(
            ['tenant_id' => $tenant->id, 'code' => 'RCP-PRALINE'],
            [
                'name' => 'Coklat Praline Klasik',
                'output_quantity' => 1,
                'description' => 'Coklat praline klasik dengan isian cream',
                'estimated_labor_cost' => 5000,
                'instructions' => 'Lelehkan coklat dark, tuangkan ke cetakan, dinginkan',
                'is_active' => true,
            ]
        );

        // Inventory Item (Finished Good)
        $adminUserId = User::where('email', 'admin@homemade.com')->value('id');

        $itemPralineBox = InventoryItem::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'INV-HMD-001'],
            [
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
            ]
        );

        StockAdjustment::firstOrCreate(
            ['tenant_id' => $tenant->id, 'inventory_item_id' => $itemPralineBox->id,
                'adjustment_type' => StockAdjustment::TYPE_OPENING_BALANCE],
            [
                'quantity_before' => 0,
                'quantity_after' => 20,
                'adjustment_quantity' => 20,
                'reason' => 'Stock awal pembukaan',
                'notes' => 'Saldo awal produk jadi',
                'unit_cost' => 8000,
                'adjusted_by' => $adminUserId,
            ]
        );

        $this->command->info('✅ Homemade tenant seeded: admin@homemade.com / password');
    }
}
