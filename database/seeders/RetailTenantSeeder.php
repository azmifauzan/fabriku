<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryItemCategory;
use App\Models\InventoryLocation;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RetailTenantSeeder extends Seeder
{
    /**
     * Run the database seeds for the Retail tenant only.
     */
    public function run(): void
    {
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
    }
}
