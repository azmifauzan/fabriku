<?php

use App\Models\Tenant;
use App\Models\User;
use App\Models\Material;
use App\Models\MaterialType;
use App\Models\MaterialReceipt;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\StockAdjustment;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

describe('Homemade Tenant Workflow', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'homemade',
            'is_active' => true,
            'subscription_plan' => 'PRO',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Dapur Utama',
            'code' => 'DPR-01',
            'is_active' => true,
        ]);

        $this->materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bahan Baku',
            'code' => 'MAT-BAKU',
        ]);
    });

    it('successfully manages a homemade user journey end-to-end', function () {
        // Step 1: Login & Access Homemade Dashboard
        $this->actingAs($this->user)
            ->get(route('dashboard', ['view' => 'stats']))
            ->assertSuccessful();

        // Step 2: Create Material
        $material = Material::create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $this->materialType->id,
            'code' => 'CKL-TEST',
            'name' => 'Coklat Batang dark',
            'price_per_unit' => 45000,
            'stock_quantity' => 0,
            'min_stock' => 5,
            'unit' => 'kg',
        ]);

        // Step 3: Purchase Material (Increases stock)
        $receipt = MaterialReceipt::create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
            'receipt_number' => 'RCV-H-2026-TEST',
            'supplier_name' => 'Distributor Bahan Coklat',
            'quantity' => 15,
            'remaining_quantity' => 15,
            'status' => 'active',
            'unit' => 'kg',
            'price_per_unit' => 45000,
            'total_cost' => 675000,
            'receipt_date' => now()->format('Y-m-d'),
            'batch_number' => 'BCH-CKL-01',
        ]);

        // Verify stock of material is now 15
        $material->refresh();
        expect((float) $material->stock_quantity)->toBe(15.0);

        // Step 4: Simple Production (Produces finished item and auto-deducts material)
        $productionData = [
            'product_name' => 'Coklat Praline Premium',
            'product_code' => 'FG-PRALINE',
            'location_id' => $this->location->id,
            'quantity' => 10,
            'notes' => 'Uji coba produksi praline',
            'production_date' => now()->format('Y-m-d'),
            'materials' => [
                [
                    'material_id' => $material->id,
                    'quantity' => 2,
                ]
            ]
        ];

        $this->actingAs($this->user)
            ->post(route('simple-production.store'), $productionData)
            ->assertRedirect(route('simple-production.index'));

        // Verify finished item was created in inventory
        $item = InventoryItem::where('tenant_id', $this->tenant->id)
            ->where('product_name', 'Coklat Praline Premium')
            ->first();
        expect($item)->not->toBeNull();
        expect($item->current_quantity)->toBe(10);
        // HPP should be 9000: (2 kg * 45000) / 10 pcs
        expect((float) $item->unit_cost)->toBe(9000.0);

        // Verify material stock was deducted (15 - 2 = 13)
        $material->refresh();
        expect((float) $material->stock_quantity)->toBe(13.0);

        $receipt->refresh();
        expect((float) $receipt->remaining_quantity)->toBe(13.0);

        // Verify stock adjustment was created for finished goods
        $adjustment = StockAdjustment::where('tenant_id', $this->tenant->id)
            ->where('adjustment_type', StockAdjustment::TYPE_PRODUCTION_ENTRY)
            ->first();
        expect($adjustment)->not->toBeNull();
        expect($adjustment->adjustment_quantity)->toBe(10);
        expect((float) $adjustment->unit_cost)->toBe(9000.0);

        // Step 5: Quick Checkout (Sells finished item and decrements stock)
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Walk-in Customer',
        ]);

        $checkoutData = [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'discount' => 0,
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity' => 3,
                    'unit_price' => 25000,
                ]
            ]
        ];

        $this->actingAs($this->user)
            ->post(route('sales-orders.quick-checkout.store'), $checkoutData)
            ->assertRedirect(route('sales-orders.index'));

        // Verify stock is correctly decremented (10 - 3 = 7)
        $item->refresh();
        expect($item->current_quantity)->toBe(7);
    });
});
