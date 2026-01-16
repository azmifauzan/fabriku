# Refactoring Plan: Simplify Preparation Process

**Date**: January 15, 2026  
**Status**: PLANNING  
**Estimated Time**: 6-9 hours (1 working day)

## Executive Summary

Simplifikasi proses Preparation (Cutting/Mixing/Assembly) untuk menghilangkan kompleksitas BOM (Bill of Materials) yang terlalu presisi untuk UMKM. Pendekatan baru: flexibel, multi-category, dengan auto stock deduction.

**Key Changes**:
- ❌ Drop `pattern_materials` & `cutting_results` tables
- 🔄 Rename `cutting_orders` → `preparation_orders`
- ➕ Add flexible `materials_used` JSONB field
- ➕ Auto stock deduction when preparation completed
- ✅ Support all categories (garment, food, craft, cosmetic)

---

## 📋 Detailed Migration Plan

### STEP 1: Database Migrations (30 mins)

#### Migration 1: Modify `patterns` table
**File**: `database/migrations/2026_01_15_XXXXXX_simplify_patterns_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            // Add category field
            $table->string('category', 50)->default('other')->after('name');
            
            // Drop complex fields
            $table->dropColumn(['estimated_time', 'standard_waste_percentage']);
        });
        
        // Add index for category
        Schema::table('patterns', function (Blueprint $table) {
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->integer('estimated_time')->nullable();
            $table->decimal('standard_waste_percentage', 5, 2)->default(5);
        });
    }
};
```

#### Migration 2: Drop `pattern_materials` table
**File**: `database/migrations/2026_01_15_XXXXXX_drop_pattern_materials_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('pattern_materials');
    }

    public function down(): void
    {
        // Recreate table if rollback needed
        Schema::create('pattern_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pattern_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_needed', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['pattern_id', 'material_id']);
        });
    }
};
```

#### Migration 3: Rename & modify to `preparation_orders`
**File**: `database/migrations/2026_01_15_XXXXXX_rename_cutting_to_preparation_orders.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename table
        Schema::rename('cutting_orders', 'preparation_orders');
        
        // Modify structure
        Schema::table('preparation_orders', function (Blueprint $table) {
            // Make pattern_id nullable
            $table->dropForeign(['pattern_id']);
            $table->foreignId('pattern_id')->nullable()->change()->constrained()->restrictOnDelete();
            
            // Remove old fields
            $table->dropColumn(['target_quantity']);
            
            // Add new fields
            $table->decimal('output_quantity', 10, 2)->after('prepared_by');
            $table->string('output_unit', 20)->default('pieces')->after('output_quantity');
            $table->jsonb('materials_used')->after('output_unit');
            
            // Rename prepared_by from cutter_id if exists
            if (Schema::hasColumn('preparation_orders', 'cutter_id')) {
                $table->renameColumn('cutter_id', 'prepared_by');
            }
        });
        
        // Update order_number prefix from CO- to PRP-
        DB::table('preparation_orders')
            ->update(['order_number' => DB::raw("REPLACE(order_number, 'CO-', 'PRP-')")]);
    }

    public function down(): void
    {
        Schema::table('preparation_orders', function (Blueprint $table) {
            $table->dropColumn(['output_quantity', 'output_unit', 'materials_used']);
            $table->integer('target_quantity')->after('order_date');
            $table->foreignId('pattern_id')->nullable(false)->change();
            
            if (Schema::hasColumn('preparation_orders', 'prepared_by')) {
                $table->renameColumn('prepared_by', 'cutter_id');
            }
        });
        
        DB::table('preparation_orders')
            ->update(['order_number' => DB::raw("REPLACE(order_number, 'PRP-', 'CO-')")]);
        
        Schema::rename('preparation_orders', 'cutting_orders');
    }
};
```

#### Migration 4: Drop `cutting_results` table
**File**: `database/migrations/2026_01_15_XXXXXX_drop_cutting_results_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('cutting_results');
    }

    public function down(): void
    {
        Schema::create('cutting_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cutting_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('material_used', 10, 2);
            $table->decimal('material_wasted', 10, 2)->default(0);
            $table->decimal('waste_percentage', 5, 2)->default(0);
            $table->integer('actual_quantity');
            $table->integer('defect_quantity')->default(0);
            $table->decimal('efficiency_percentage', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('cutting_order_id');
        });
    }
};
```

#### Migration 5: Update `production_orders` reference
**File**: `database/migrations/2026_01_15_XXXXXX_update_production_orders_references.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['cutting_result_id']);
            
            // Add new nullable reference to preparation_orders
            $table->foreignId('preparation_order_id')
                ->nullable()
                ->after('order_number')
                ->constrained('preparation_orders')
                ->restrictOnDelete();
            
            // Remove cutting_result_id
            $table->dropColumn('cutting_result_id');
        });
    }

    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropForeign(['preparation_order_id']);
            $table->dropColumn('preparation_order_id');
            
            // Recreate cutting_result_id
            $table->foreignId('cutting_result_id')
                ->after('order_number')
                ->constrained('cutting_results')
                ->restrictOnDelete();
        });
    }
};
```

---

### STEP 2: Backend Refactoring (2-3 hours)

#### 2.1 Update Pattern Model
**File**: `app/Models/Pattern.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\TenantScope;

class Pattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',  // NEW: garment/food/craft/cosmetic/other
        'product_type',
        'size',
        'description',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
    }

    // Relationships
    public function preparationOrders(): HasMany
    {
        return $this->hasMany(PreparationOrder::class);
    }

    // Removed: materials() relationship (no more BOM)

    // Helpers
    public function canBeDeleted(): bool
    {
        return $this->preparationOrders()->doesntExist();
    }
}
```

#### 2.2 Create PreparationOrder Model (rename from CuttingOrder)
**File**: `app/Models/PreparationOrder.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\TenantScope;
use App\Services\MaterialStockService;

class PreparationOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'pattern_id',  // NULLABLE
        'order_date',
        'status',
        'prepared_by',
        'output_quantity',
        'output_unit',
        'materials_used',  // JSONB
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'materials_used' => 'array',  // Auto cast JSONB to array
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());
        
        // Auto-generate order number on creating
        static::creating(function (PreparationOrder $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
        
        // Auto deduct stock when status changed to completed
        static::updating(function (PreparationOrder $order) {
            if ($order->isDirty('status') && $order->status === 'completed' && $order->getOriginal('status') !== 'completed') {
                app(MaterialStockService::class)->deductStock($order);
            }
        });
    }

    // Relationships
    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    // Helpers
    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $tenant_id = auth()->user()->tenant_id;
        
        $lastOrder = static::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenant_id)
            ->where('order_number', 'like', "PRP-{$year}-%")
            ->orderBy('order_number', 'desc')
            ->first();
        
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return "PRP-{$year}-{$newNumber}";
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft' && $this->productionOrders()->doesntExist();
    }
}
```

#### 2.3 Create MaterialStockService
**File**: `app/Services/MaterialStockService.php`

```php
<?php

namespace App\Services;

use App\Models\PreparationOrder;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialStockService
{
    /**
     * Deduct material stock based on preparation order materials_used
     */
    public function deductStock(PreparationOrder $order): void
    {
        DB::transaction(function () use ($order) {
            $materialsUsed = $order->materials_used;
            
            foreach ($materialsUsed as $materialData) {
                $materialId = $materialData['material_id'];
                $quantityUsed = $materialData['quantity'];
                
                $material = Material::find($materialId);
                
                if (!$material) {
                    Log::warning("Material ID {$materialId} not found for PreparationOrder {$order->id}");
                    continue;
                }
                
                // Deduct stock
                $material->current_stock -= $quantityUsed;
                
                // Ensure stock doesn't go below 0
                if ($material->current_stock < 0) {
                    $material->current_stock = 0;
                    Log::warning("Material {$material->code} stock went negative, set to 0");
                }
                
                $material->save();
                
                Log::info("Deducted {$quantityUsed} {$materialData['unit']} from Material {$material->code}. New stock: {$material->current_stock}");
            }
        });
    }
    
    /**
     * Check if all materials have sufficient stock
     */
    public function checkStockAvailability(array $materialsUsed): array
    {
        $insufficientMaterials = [];
        
        foreach ($materialsUsed as $materialData) {
            $material = Material::find($materialData['material_id']);
            
            if (!$material) {
                continue;
            }
            
            if ($material->current_stock < $materialData['quantity']) {
                $insufficientMaterials[] = [
                    'material_id' => $material->id,
                    'material_name' => $material->name,
                    'required' => $materialData['quantity'],
                    'available' => $material->current_stock,
                    'shortage' => $materialData['quantity'] - $material->current_stock,
                ];
            }
        }
        
        return $insufficientMaterials;
    }
}
```

#### 2.4 Create PreparationOrderController
**File**: `app/Http/Controllers/PreparationOrderController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\PreparationOrder;
use App\Models\Pattern;
use App\Models\Material;
use App\Models\User;
use App\Http\Requests\StorePreparationOrderRequest;
use App\Http\Requests\UpdatePreparationOrderRequest;
use App\Services\MaterialStockService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PreparationOrderController extends Controller
{
    public function __construct(
        protected MaterialStockService $stockService
    ) {}

    public function index(Request $request): Response
    {
        $orders = PreparationOrder::query()
            ->with(['pattern', 'preparedBy'])
            ->when($request->search, fn($q) => $q->where('order_number', 'like', "%{$request->search}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->pattern_id, fn($q) => $q->where('pattern_id', $request->pattern_id))
            ->latest('order_date')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('PreparationOrders/Index', [
            'orders' => $orders,
            'filters' => $request->only(['search', 'status', 'pattern_id']),
        ]);
    }

    public function create(): Response
    {
        $patterns = Pattern::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'category', 'product_type']);
        
        $materials = Material::where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'unit', 'current_stock']);
        
        $staff = User::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('PreparationOrders/Form', [
            'patterns' => $patterns,
            'materials' => $materials,
            'staff' => $staff,
        ]);
    }

    public function store(StorePreparationOrderRequest $request)
    {
        // Check stock availability
        $insufficient = $this->stockService->checkStockAvailability($request->materials_used);
        
        if (!empty($insufficient)) {
            return back()->withErrors([
                'materials_used' => 'Stock tidak mencukupi untuk beberapa material: ' . 
                    implode(', ', array_column($insufficient, 'material_name'))
            ])->withInput();
        }

        $order = PreparationOrder::create($request->validated());

        return redirect()
            ->route('preparation-orders.index')
            ->with('success', 'Preparation order berhasil dibuat');
    }

    public function show(PreparationOrder $preparationOrder): Response
    {
        $preparationOrder->load(['pattern', 'preparedBy', 'productionOrders']);

        return Inertia::render('PreparationOrders/Show', [
            'order' => $preparationOrder,
        ]);
    }

    public function edit(PreparationOrder $preparationOrder): Response
    {
        if (!$preparationOrder->canBeEdited()) {
            abort(403, 'Order cannot be edited');
        }

        $patterns = Pattern::where('is_active', true)->get(['id', 'code', 'name', 'category']);
        $materials = Material::where('is_active', true)->get(['id', 'code', 'name', 'unit', 'current_stock']);
        $staff = User::where('tenant_id', auth()->user()->tenant_id)->get(['id', 'name']);

        return Inertia::render('PreparationOrders/Form', [
            'order' => $preparationOrder,
            'patterns' => $patterns,
            'materials' => $materials,
            'staff' => $staff,
        ]);
    }

    public function update(UpdatePreparationOrderRequest $request, PreparationOrder $preparationOrder)
    {
        if (!$preparationOrder->canBeEdited()) {
            abort(403, 'Order cannot be edited');
        }

        // Check stock if materials changed
        if ($request->has('materials_used')) {
            $insufficient = $this->stockService->checkStockAvailability($request->materials_used);
            
            if (!empty($insufficient)) {
                return back()->withErrors([
                    'materials_used' => 'Stock tidak mencukupi'
                ])->withInput();
            }
        }

        $preparationOrder->update($request->validated());

        return redirect()
            ->route('preparation-orders.index')
            ->with('success', 'Preparation order berhasil diupdate');
    }

    public function destroy(PreparationOrder $preparationOrder)
    {
        if (!$preparationOrder->canBeDeleted()) {
            abort(403, 'Order cannot be deleted');
        }

        $preparationOrder->delete();

        return redirect()
            ->route('preparation-orders.index')
            ->with('success', 'Preparation order berhasil dihapus');
    }
}
```

#### 2.5 Update Form Requests

Create `StorePreparationOrderRequest` and `UpdatePreparationOrderRequest` with proper validation for materials_used JSONB field.

---

### STEP 3: Frontend Refactoring (2-3 hours)

Create new Vue components:
- `PreparationOrders/Index.vue` - List dengan filters
- `PreparationOrders/Form.vue` - Create/Edit dengan material selector
- `PreparationOrders/Show.vue` - Detail view

Update navigation in `Sidebar.vue`.

---

### STEP 4: Update Routes (15 mins)

**File**: `routes/web.php`

```php
// Change from cutting-orders to preparation-orders
Route::resource('preparation-orders', PreparationOrderController::class);

// Remove old cutting routes if any
// Route::resource('cutting-orders', CuttingOrderController::class); // DELETE
```

---

### STEP 5: Update Tests (1-2 hours)

Modify existing tests to work with new PreparationOrder model.
Add new tests for:
- Stock deduction logic
- Stock availability checking
- Multi-category scenarios

---

### STEP 6: Update Seeders & Factories (30 mins)

- Update `PatternSeeder` to include `category` field
- Create `PreparationOrderFactory`
- Create `PreparationOrderSeeder` with demo data

---

## ✅ Validation Checklist

- [ ] Run `get_errors` - zero errors
- [ ] Run `vendor/bin/pint --dirty` - all files formatted
- [ ] Run `php artisan migrate:fresh --seed` - migrations work
- [ ] Run `php artisan test --filter="Pattern|Preparation"` - all tests pass
- [ ] Run `npm run build` - frontend builds successfully
- [ ] Manual browser test - create preparation order works
- [ ] Verify stock deduction works correctly
- [ ] Test multi-category (garment, food, craft)
- [ ] Git commit & push

---

## 🎯 Success Criteria

1. ✅ All migrations run successfully
2. ✅ No BOM complexity - simple pattern templates
3. ✅ Flexible materials_used JSON works
4. ✅ Auto stock deduction working
5. ✅ Multi-category support (garment, food, craft, cosmetic)
6. ✅ All tests passing
7. ✅ Frontend UI intuitive and easy to use
8. ✅ Zero compile/lint errors
9. ✅ Code formatted with Pint
10. ✅ Documentation updated

---

## 📝 Notes

- Backup database before running migrations
- Test thoroughly with demo data
- Consider adding material_transactions table for audit trail (Phase 2)
- May need to educate users about new simpler workflow

---

## 🚀 Ready to Execute?

Once all documentation is reviewed and approved, proceed with STEP 1 (Database Migrations).
