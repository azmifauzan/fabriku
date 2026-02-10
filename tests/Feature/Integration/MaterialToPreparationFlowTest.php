<?php

use App\Models\Material;
use App\Models\MaterialReceipt;
use App\Models\MaterialType;
use App\Models\Pattern;
use App\Models\PreparationOrder;
use App\Models\Tenant;
use App\Models\User;

describe('Material to Preparation Integration', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create([
            'business_category' => 'GARMENT',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'email_verified_at' => now(),
        ]);
    });

    it('tracks material from receipt through preparation for garment', function () {
        // Step 1: Create material type
        $this->actingAs($this->user)
            ->post(route('material-types.store'), [
                'name' => 'Kain',
                'category' => 'FABRIC',
                'description' => 'Bahan kain untuk garment',
            ])
            ->assertRedirect();

        $materialType = MaterialType::where('tenant_id', $this->tenant->id)->first();
        expect($materialType)->not->toBeNull();

        // Step 2: Create material
        $this->actingAs($this->user)
            ->post(route('materials.store'), [
                'material_type_id' => $materialType->id,
                'name' => 'Kain Katun Premium',
                'code' => 'KT-PREM-001',
                'unit' => 'METER',
                'description' => 'Kain katun import',
            ])
            ->assertRedirect();

        $material = Material::where('code', 'KT-PREM-001')->first();
        expect($material)->not->toBeNull();
        expect($material->stock_quantity)->toBe(0.0);

        // Step 3: Receive material
        $this->actingAs($this->user)
            ->post(route('material-receipts.store'), [
                'material_id' => $material->id,
                'supplier_name' => 'PT Kain Sejahtera',
                'quantity' => 200,
                'unit_price' => 75000,
                'received_at' => now()->format('Y-m-d'),
                'batch_number' => 'BATCH-KT-001',
                'notes' => 'Pembelian bulanan',
                'attributes' => [
                    'color' => 'Navy Blue',
                    'width' => 150,
                    'weight' => 200,
                ],
            ])
            ->assertRedirect();

        // Verify receipt created
        $receipt = MaterialReceipt::where('material_id', $material->id)->first();
        expect($receipt)->not->toBeNull();
        expect($receipt->quantity)->toBe(200.0);
        expect($receipt->attributes['color'])->toBe('Navy Blue');

        // Verify stock updated
        $material->refresh();
        expect($material->stock_quantity)->toBe(200.0);

        // Step 4: Create pattern
        $this->actingAs($this->user)
            ->post(route('patterns.store'), [
                'code' => 'GMB-001',
                'name' => 'Gamis Casual',
                'product_type' => 'GAMIS',
                'category' => 'GARMENT',
                'description' => 'Gamis casual untuk sehari-hari',
            ])
            ->assertRedirect();

        $pattern = Pattern::where('code', 'GMB-001')->first();
        expect($pattern)->not->toBeNull();

        // Step 5: Create preparation order
        $this->actingAs($this->user)
            ->post(route('preparation-orders.store'), [
                'pattern_id' => $pattern->id,
                'planned_quantity' => 15,
                'notes' => 'Cutting untuk order bulan ini',
            ])
            ->assertRedirect();

        $prepOrder = PreparationOrder::where('tenant_id', $this->tenant->id)->first();
        expect($prepOrder)->not->toBeNull();
        expect($prepOrder->status)->toBe('DRAFT');

        // Step 6: Add material to preparation (via edit)
        $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $prepOrder), [
                'pattern_id' => $pattern->id,
                'planned_quantity' => 15,
                'status' => 'IN_PROGRESS',
                'materials' => [
                    [
                        'material_id' => $material->id,
                        'quantity_used' => 45,
                        'unit' => 'METER',
                        'batch_number' => 'BATCH-KT-001',
                    ],
                ],
            ])
            ->assertRedirect();

        $prepOrder->refresh();
        expect($prepOrder->status)->toBe('IN_PROGRESS');

        // Step 7: Complete preparation
        $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $prepOrder), [
                'pattern_id' => $pattern->id,
                'planned_quantity' => 15,
                'actual_output' => 15,
                'status' => 'COMPLETED',
                'materials' => [
                    [
                        'material_id' => $material->id,
                        'quantity_used' => 45,
                        'unit' => 'METER',
                        'batch_number' => 'BATCH-KT-001',
                    ],
                ],
            ])
            ->assertRedirect();

        // Verify material stock deducted
        $material->refresh();
        expect($material->stock_quantity)->toBe(155.0);

        $prepOrder->refresh();
        expect($prepOrder->status)->toBe('COMPLETED');
        expect($prepOrder->actual_output)->toBe(15);
    });

    it('tracks food materials with expiry dates', function () {
        $this->tenant->update(['business_category' => 'FOOD']);

        // Create ingredient
        $materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bahan Kue',
            'category' => 'BAKING',
        ]);

        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $materialType->id,
            'name' => 'Coklat Bubuk Premium',
            'code' => 'CKL-001',
            'unit' => 'KG',
        ]);

        // Receive with expiry date
        $this->actingAs($this->user)
            ->post(route('material-receipts.store'), [
                'material_id' => $material->id,
                'supplier_name' => 'Toko Bahan Kue',
                'quantity' => 25,
                'unit_price' => 150000,
                'received_at' => now()->format('Y-m-d'),
                'batch_number' => 'BATCH-CKL-001',
                'attributes' => [
                    'expired_date' => now()->addMonths(12)->format('Y-m-d'),
                    'storage_temp' => 'ROOM_TEMP',
                    'halal_certified' => true,
                ],
            ])
            ->assertRedirect();

        $material->refresh();
        expect($material->stock_quantity)->toBe(25.0);

        // Create recipe
        $recipe = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CAKE-001',
            'name' => 'Chocolate Cake',
            'product_type' => 'CAKE',
            'category' => 'FOOD',
        ]);

        // Create preparation (mixing)
        $prepOrder = PreparationOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'pattern_id' => $recipe->id,
            'planned_quantity' => 10,
            'status' => 'DRAFT',
        ]);

        $prepOrder->materialUsages()->create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
            'quantity_used' => 2,
            'unit' => 'KG',
            'batch_number' => 'BATCH-CKL-001',
        ]);

        // Complete preparation
        $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $prepOrder), [
                'pattern_id' => $recipe->id,
                'planned_quantity' => 10,
                'actual_output' => 10,
                'status' => 'COMPLETED',
                'materials' => [
                    [
                        'material_id' => $material->id,
                        'quantity_used' => 2,
                        'unit' => 'KG',
                        'batch_number' => 'BATCH-CKL-001',
                    ],
                ],
            ])
            ->assertRedirect();

        $material->refresh();
        expect($material->stock_quantity)->toBe(23.0);
    });

    it('validates material stock before preparation', function () {
        $materialType = MaterialType::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'material_type_id' => $materialType->id,
            'stock_quantity' => 10,
        ]);

        $pattern = Pattern::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);

        $prepOrder = PreparationOrder::factory()->create([
            'tenant_id' => $this->tenant->id,
            'pattern_id' => $pattern->id,
            'status' => 'DRAFT',
        ]);

        // Try to use more material than available
        $response = $this->actingAs($this->user)
            ->patch(route('preparation-orders.update', $prepOrder), [
                'pattern_id' => $pattern->id,
                'planned_quantity' => 50,
                'actual_output' => 50,
                'status' => 'COMPLETED',
                'materials' => [
                    [
                        'material_id' => $material->id,
                        'quantity_used' => 100, // More than available (10)
                        'unit' => 'METER',
                    ],
                ],
            ]);

        // Should fail due to insufficient stock
        $response->assertSessionHasErrors();
    });

    it('allows viewing material usage history', function () {
        $material = Material::factory()->create([
            'tenant_id' => $this->tenant->id,
            'stock_quantity' => 100,
        ]);

        // Create multiple receipts
        MaterialReceipt::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'material_id' => $material->id,
        ]);

        // View material detail
        $this->actingAs($this->user)
            ->get(route('materials.show', $material))
            ->assertSuccessful()
            ->assertSee($material->name);
    });
});
