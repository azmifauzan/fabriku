<?php

use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\StockAdjustment;
use App\Models\Tenant;
use App\Models\User;

describe('Retail Tenant Workflow', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'RETAIL',
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
    });

    it('successfully manages a retail user journey end-to-end', function () {
        // Step 1: Login & Access Dashboard
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertSuccessful();

        // Step 2: Create inventory location
        $location = InventoryLocation::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Toko Depan',
            'code' => 'TK-01',
        ]);

        // Step 3: Create inventory item
        $item = InventoryItem::factory()->create([
            'tenant_id' => $this->tenant->id,
            'location_id' => $location->id,
            'sku' => 'RTL-BARANG-01',
            'product_name' => 'Barang Retail A',
            'current_quantity' => 0,
            'minimum_stock' => 5,
            'unit_cost' => 10000,
            'status' => 'available',
        ]);

        // Step 4: Record a purchase receipt (increments stock)
        $purchaseData = [
            'supplier_name' => 'Supplier Bahagia',
            'purchase_invoice' => 'INV/RTL/1001',
            'purchase_date' => now()->format('Y-m-d'),
            'notes' => 'Pembelian perdana barang retail',
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity' => 20,
                    'unit_cost' => 12000,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('purchase-receipts.store'), $purchaseData)
            ->assertRedirect(route('purchase-receipts.index'));

        // Verify stock updated in database
        $item->refresh();
        expect($item->current_quantity)->toBe(20);
        expect((float) $item->unit_cost)->toBe(12000.0);

        // Verify stock adjustment was created
        $adjustment = StockAdjustment::where('tenant_id', $this->tenant->id)
            ->where('adjustment_type', StockAdjustment::TYPE_PURCHASE)
            ->first();
        expect($adjustment)->not->toBeNull();
        expect($adjustment->supplier_name)->toBe('Supplier Bahagia');
        expect($adjustment->adjustment_quantity)->toBe(20);

        // Step 5: Check purchase report page is accessible and has the correct Inertia data
        $response = $this->actingAs($this->user)
            ->get(route('reports.purchase'))
            ->assertSuccessful();

        $inertiaData = $response->original->getData();
        $batches = $inertiaData['page']['props']['batches'];
        expect($batches)->toHaveCount(1);
        expect($batches[0]['supplier_name'])->toBe('Supplier Bahagia');
        expect($batches[0]['total_cost'])->toBe(240000); // 20 * 12000

        // Step 6: Test Export Purchase Excel and PDF
        $this->actingAs($this->user)
            ->get(route('reports.purchase.export', ['format' => 'excel']))
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get(route('reports.purchase.export', ['format' => 'pdf']))
            ->assertSuccessful();

        // Step 7: Record a quick checkout (decrements stock)
        $customer = Customer::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Walk-in Customer',
        ]);

        $checkoutData = [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'discount' => 2000,
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'quantity' => 5,
                    'unit_price' => 15000,
                ],
            ],
        ];

        $this->actingAs($this->user)
            ->post(route('sales-orders.quick-checkout.store'), $checkoutData)
            ->assertRedirect(route('sales-orders.index'));

        // Verify stock is correctly decremented
        $item->refresh();
        expect($item->current_quantity)->toBe(15);
    });
});
