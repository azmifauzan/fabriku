# Multi-Rack Stock Split on Inventory Item Create — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let users split one inventory-item batch across 2+ storage racks when creating it, so a 400 pcs batch can go 300 to Rak A + 100 to Rak B instead of overflowing one rack.

**Architecture:** No schema change. Each rack in a split becomes its own `inventory_items` row (own auto-generated SKU, shared `product_name`), created via multiple `InventoryItem::create()` calls in one DB transaction. Applies to `POST /inventory/items` only (both production-order and manual entries); editing an existing item keeps its single-location shape untouched.

**Tech Stack:** Laravel 12 (FormRequest, Eloquent), Inertia.js v2 + Vue 3.5 `<script setup>`, Pest 4 (feature + browser tests).

## Global Constraints

- Spec: `docs/superpowers/specs/2026-07-02-multi-rack-stock-split-design.md` — read it if anything below is ambiguous.
- Create-only. Do not touch `UpdateInventoryItemRequest`, the `update()`/`edit()` controller actions, or `Edit.vue`'s single-location behavior.
- No new DB table/column. Capacity check reads `InventoryLocation::available_capacity` (existing accessor, `current_quantity` sum, unlimited when `capacity` is null).
- Indonesian user-facing strings (validation messages, UI labels), per project convention.
- Every change needs a test (project convention). Run `vendor/bin/pint --dirty --format agent` before finalizing PHP changes.

---

### Task 1: Expose `available_capacity` per location on the create form

**Files:**
- Modify: `app/Services/InventoryService.php:317-355` (method `getFormDataForCreateOrEdit`)
- Test: `tests/Feature/InventoryItemTest.php` (new test appended to existing file)

**Interfaces:**
- Produces: each `InventoryLocation` model returned in the `locations` collection now carries an `available_capacity` (int) attribute in addition to `id`, `name`, `code`, `capacity`. Task 3 (frontend) reads this field.

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/InventoryItemTest.php` (append after the `it('cannot access other tenant items', ...)` block, anywhere in the file's top-level `it()` list):

```php
it('exposes available capacity per location on the create form', function () {
    $limitedLocation = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    InventoryItem::factory()
        ->for($this->tenant)
        ->for($limitedLocation, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['location_id' => $limitedLocation->id, 'current_quantity' => 250]);

    $response = $this->get('/inventory/items/create');

    $response->assertSuccessful();
    $response->assertInertia(fn (AssertableInertia $page) => $page->component('Inventory/Items/Create')
        ->where('locations', function ($locations) use ($limitedLocation) {
            $match = collect($locations)->firstWhere('id', $limitedLocation->id);

            return $match && $match['available_capacity'] === 50;
        })
    );
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter="exposes available capacity per location"`
Expected: FAIL — `available_capacity` key missing from the `locations` payload (assertion returns false).

- [ ] **Step 3: Implement**

In `app/Services/InventoryService.php`, replace:

```php
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']);
```

with:

```php
        $locations = InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code', 'capacity']);
        $locations->each(function (InventoryLocation $location) {
            $location->available_capacity = $location->available_capacity;
        });
```

(Reassigning the accessor's value back onto the model forces it into `$attributes`, so it serializes to the Inertia response without needing to add it to the model's global `$appends`.)

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter="exposes available capacity per location"`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/InventoryService.php tests/Feature/InventoryItemTest.php
git commit -m "feat(inventory): expose available_capacity per location on item create form"
```

---

### Task 2: `locations[]` split validation, capacity enforcement, and transactional multi-row creation

**Files:**
- Modify: `app/Http/Requests/StoreInventoryItemRequest.php` (full rewrite)
- Modify: `app/Http/Controllers/InventoryItemController.php:129-165` (method `store`)
- Modify: `tests/Feature/InventoryItemTest.php` (update 3 existing tests, add 3 new tests)

**Interfaces:**
- Consumes: none beyond Laravel's `Rule::exists`/`FormRequest` (already used in this file).
- Produces: `StoreInventoryItemRequest::validated('locations')` returns `array<int, array{location_id:int, quantity:int}>`. `InventoryItemController::store()` creates one `InventoryItem` row per entry and redirects to the first created item's show page.

- [ ] **Step 1: Update existing tests to the new `locations[]` payload shape (this makes them fail against the current implementation — expected)**

In `tests/Feature/InventoryItemTest.php`, replace the `it('can create new inventory item', ...)` test body:

```php
it('can create new inventory item', function () {
    $itemData = [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'TEST001',
        'name' => 'Test Product',
        'locations' => [
            ['location_id' => $this->location->id, 'quantity' => 95],
        ],
        'target_quantity' => 100,
        'minimum_stock' => 10,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
        'quality_grade' => 'grade_a',
        'status' => 'available',
    ];

    $response = $this->post('/inventory/items', $itemData);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'TEST001',
        'product_name' => 'Test Product',
        'location_id' => $this->location->id,
        'target_quantity' => 100,
        'current_quantity' => 95,
        'minimum_stock' => 10,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
        'quality_grade' => 'grade_a',
        'status' => 'available',
        'tenant_id' => $this->tenant->id,
    ]);
});
```

Replace the `it('validates required fields when creating item', ...)` test body:

```php
it('validates required fields when creating item', function () {
    $response = $this->post('/inventory/items', []);

    // production_order_id is no longer required (nullable for manual entry/opening balance)
    // product_name is required when no production_order_id is provided
    $response->assertSessionHasErrors([
        'product_name', 'locations', 'unit_cost',
    ]);
});
```

Replace the `it('validates unique SKU within tenant', ...)` test body:

```php
it('validates unique SKU within tenant', function () {
    InventoryItem::factory()
        ->for($this->tenant)
        ->for($this->location, 'inventoryLocation')
        ->for($this->productionOrder)
        ->create(['sku' => 'EXISTING001']);

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'sku' => 'EXISTING001',
        'name' => 'Test Product',
        'locations' => [
            ['location_id' => $this->location->id, 'quantity' => 100],
        ],
        'target_quantity' => 100,
        'unit_cost' => 25.50,
    ]);

    $response->assertSessionHasErrors(['sku']);
});
```

- [ ] **Step 2: Add new tests for the split feature**

Append to `tests/Feature/InventoryItemTest.php`:

```php
it('can split stock across multiple racks on create', function () {
    $rackA = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);
    $rackB = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $rackA->id, 'quantity' => 300],
            ['location_id' => $rackB->id, 'quantity' => 100],
        ],
        'target_quantity' => 400,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $rackA->id,
        'current_quantity' => 300,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $rackB->id,
        'current_quantity' => 100,
    ]);

    expect(InventoryItem::where('product_name', 'Mukena Bali Putih')->count())->toBe(2);
});

it('rejects a split that exceeds rack capacity and creates no rows', function () {
    $smallRack = InventoryLocation::factory()->for($this->tenant)->create(['capacity' => 300]);
    $countBefore = InventoryItem::count();

    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $smallRack->id, 'quantity' => 400],
        ],
        'target_quantity' => 400,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertSessionHasErrors(['locations.0.quantity']);
    expect(InventoryItem::count())->toBe($countBefore);
});

it('rejects duplicate rack within one split submission', function () {
    $response = $this->post('/inventory/items', [
        'production_order_id' => $this->productionOrder->id,
        'name' => 'Mukena Bali Putih',
        'locations' => [
            ['location_id' => $this->location->id, 'quantity' => 50],
            ['location_id' => $this->location->id, 'quantity' => 50],
        ],
        'target_quantity' => 100,
        'unit_cost' => 25.50,
        'selling_price' => 45.00,
    ]);

    $response->assertSessionHasErrors(['locations.0.location_id', 'locations.1.location_id']);
});
```

- [ ] **Step 3: Run tests to verify they fail**

Run: `php artisan test --compact tests/Feature/InventoryItemTest.php`
Expected: multiple FAIL — `locations` field not recognized by current validation rules, capacity not enforced, duplicate rack not rejected.

- [ ] **Step 4: Rewrite `StoreInventoryItemRequest`**

Replace the full contents of `app/Http/Requests/StoreInventoryItemRequest.php`:

```php
<?php

namespace App\Http\Requests;

use App\Models\InventoryLocation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInventoryItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare data for validation - map field names
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && ! $this->has('product_name')) {
            $this->merge(['product_name' => $this->input('name')]);
        }

        // For manual entry, default target_quantity to 0 if not provided
        if (empty($this->input('production_order_id')) && ! $this->has('target_quantity')) {
            $this->merge(['target_quantity' => 0]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isManualEntry = empty($this->input('production_order_id'));
        $tenantId = $this->user()->tenant_id;

        return [
            // Source type for tracking
            'source_type' => 'nullable|in:production,opening_balance,purchase,return',

            // Production order is now optional (nullable for manual entry / opening balance)
            'production_order_id' => ['nullable', Rule::exists('production_orders', 'id')->where('tenant_id', $tenantId)],

            'sku' => 'nullable|string|max:100|unique:inventory_items,sku,NULL,id,tenant_id,'.$tenantId,

            // Product name required for manual entry
            'product_name' => $isManualEntry ? 'required|string|max:255' : 'nullable|string|max:255',
            'name' => 'sometimes|string|max:255', // backwards compatibility

            // One or more rack allocations for this batch
            'locations' => 'required|array|min:1',
            'locations.*.location_id' => ['required', 'distinct', Rule::exists('inventory_locations', 'id')->where('tenant_id', $tenantId)],
            'locations.*.quantity' => 'required|integer|min:1',

            // Quantities - required for manual entry
            'target_quantity' => $isManualEntry ? 'nullable|integer|min:0' : 'required|integer|min:0',
            'minimum_stock' => 'integer|min:0',

            // Category
            'category_id' => ['nullable', Rule::exists('inventory_item_categories', 'id')->where('tenant_id', $tenantId)],

            // Pricing - required for manual entry
            'unit_cost' => 'required|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',

            'quality_grade' => 'nullable|in:grade_a,grade_b,reject,A,B,Reject',
            'expired_date' => ['nullable', 'date'],
            'status' => 'nullable|in:available,reserved,damaged,expired',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Block any rack allocation that would overflow that rack's remaining capacity.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $locations = $this->input('locations', []);

            if (! is_array($locations)) {
                return;
            }

            foreach ($locations as $index => $entry) {
                $locationId = $entry['location_id'] ?? null;
                $quantity = (int) ($entry['quantity'] ?? 0);

                if (! $locationId || $quantity < 1) {
                    continue;
                }

                $location = InventoryLocation::find($locationId);

                if (! $location || $location->capacity === null) {
                    continue;
                }

                if ($quantity > $location->available_capacity) {
                    $validator->errors()->add(
                        "locations.{$index}.quantity",
                        "Rak {$location->name} tidak cukup kapasitas (sisa: {$location->available_capacity})."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'production_order_id.exists' => 'Production order tidak ditemukan.',
            'product_name.required' => 'Nama produk harus diisi untuk manual entry.',
            'sku.unique' => 'SKU sudah digunakan.',
            'locations.required' => 'Minimal 1 lokasi harus diisi.',
            'locations.*.location_id.required' => 'Lokasi harus dipilih.',
            'locations.*.location_id.distinct' => 'Lokasi tidak boleh dipilih dua kali dalam satu form.',
            'locations.*.location_id.exists' => 'Lokasi inventory tidak ditemukan.',
            'locations.*.quantity.required' => 'Jumlah stock harus diisi.',
            'locations.*.quantity.min' => 'Jumlah stock minimal 1.',
            'target_quantity.required' => 'Jumlah target harus diisi.',
            'unit_cost.required' => 'Harga modal harus diisi.',
        ];
    }
}
```

- [ ] **Step 5: Rewrite `InventoryItemController::store()`**

In `app/Http/Controllers/InventoryItemController.php`, add the import (near the other `use` statements at the top of the file):

```php
use Illuminate\Support\Facades\DB;
```

Replace the `store()` method (currently lines 129-165):

```php
    public function store(StoreInventoryItemRequest $request)
    {
        $data = $request->safe()->except(['image', 'locations']);
        $locations = $request->validated('locations');

        // Set source_type based on whether production_order_id is present
        if (empty($data['production_order_id'])) {
            $data['source_type'] = $data['source_type'] ?? 'opening_balance';
        } else {
            $data['source_type'] = 'production';

            // Get product_name from production order's pattern if not provided
            if (empty($data['product_name'])) {
                $productionOrder = ProductionOrder::with('preparationOrder.pattern')
                    ->find($data['production_order_id']);
                if ($productionOrder && $productionOrder->preparationOrder && $productionOrder->preparationOrder->pattern) {
                    $data['product_name'] = $productionOrder->preparationOrder->pattern->name;
                }
            }
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->storePublicly(
                'tenants/'.auth()->user()->tenant_id.'/inventory',
                config('filesystems.uploads_disk', 'fabriku_s3')
            );
        }

        $locationIds = array_column($locations, 'location_id');

        $firstItem = DB::transaction(function () use ($data, $locations, $locationIds) {
            // Lock the involved racks so two concurrent submissions can't both
            // pass the capacity check and overfill the same rack.
            InventoryLocation::whereIn('id', $locationIds)->lockForUpdate()->get();

            $createdItems = [];
            foreach ($locations as $entry) {
                $createdItems[] = InventoryItem::create([
                    ...$data,
                    'location_id' => $entry['location_id'],
                    'current_quantity' => $entry['quantity'],
                ]);
            }

            return $createdItems[0];
        });

        $message = count($locations) > 1
            ? 'Inventory item berhasil dibuat di '.count($locations).' lokasi.'
            : 'Inventory item berhasil dibuat.';

        return redirect()
            ->route('inventory.items.show', $firstItem)
            ->with('success', $message);
    }
```

- [ ] **Step 6: Run tests to verify they pass**

Run: `php artisan test --compact tests/Feature/InventoryItemTest.php`
Expected: PASS (all tests in the file, including the 3 updated and 3 new ones)

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Requests/StoreInventoryItemRequest.php app/Http/Controllers/InventoryItemController.php tests/Feature/InventoryItemTest.php
git commit -m "feat(inventory): support splitting a new item's stock across multiple racks"
```

---

### Task 3: Multi-rack split UI in `Form.vue` (create mode only)

**Files:**
- Modify: `resources/js/pages/Inventory/Items/Form.vue`
- Modify: `resources/js/pages/Inventory/Items/Create.vue` (Location interface)
- Modify: `resources/js/pages/Inventory/Items/Edit.vue` (Location interface, for type consistency — edit behavior itself is unchanged)

**Interfaces:**
- Consumes: `Location.available_capacity` (int) from Task 1's backend change.
- Produces: on submit, `form.locations` is `{ location_id: number|null, quantity: number }[]`, posted as `locations[]` — matches Task 2's `StoreInventoryItemRequest` rules exactly.

- [ ] **Step 1: Update the `Location` interface and add `LocationSplit`**

In `resources/js/pages/Inventory/Items/Form.vue`, replace:

```ts
interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number;
}
```

with:

```ts
interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number | null;
    available_capacity: number;
}

interface LocationSplit {
    location_id: number | null;
    quantity: number;
}
```

- [ ] **Step 2: Move `isEditing` up and add the split state/logic**

Replace:

```ts
const props = defineProps<{
    item?: Item;
    locations: Location[];
    productionOrders: ProductionOrder[];
    categories: Category[];
    allowManualEntry?: boolean;
    sourceTypes?: Record<string, string>;
}>();

const { isRetailMode, rules } = useBusinessContext();
const isRetail = computed(() => isRetailMode.value || rules.value.enable_production_flow === false);

// Entry type: 'production' or 'manual'
const entryType = ref<'production' | 'manual'>(
    isRetail.value ? 'manual' : props.item?.production_order_id ? 'production' : props.item?.id ? 'manual' : 'production',
);

// Source type for manual entry
const selectedSourceType = ref<string>(props.item?.source_type || 'opening_balance');

const form = useForm({
    production_order_id: props.item?.production_order_id || null,
    source_type: props.item?.source_type || (isRetail.value ? 'opening_balance' : 'production'),
    category_id: props.item?.category_id || null,
    sku: props.item?.sku || '',
    name: props.item?.name || '',
    inventory_location_id: props.item?.inventory_location_id || null,
    target_quantity: props.item?.target_quantity || 0,
    current_stock: props.item?.current_stock || 0,
    unit_cost: props.item?.unit_cost || '0',
    selling_price: props.item?.selling_price || '0',
    notes: props.item?.notes || '',
    image: null as File | null,
});
```

with:

```ts
const props = defineProps<{
    item?: Item;
    locations: Location[];
    productionOrders: ProductionOrder[];
    categories: Category[];
    allowManualEntry?: boolean;
    sourceTypes?: Record<string, string>;
}>();

const isEditing = !!props.item?.id;

const { isRetailMode, rules } = useBusinessContext();
const isRetail = computed(() => isRetailMode.value || rules.value.enable_production_flow === false);

// Entry type: 'production' or 'manual'
const entryType = ref<'production' | 'manual'>(
    isRetail.value ? 'manual' : props.item?.production_order_id ? 'production' : props.item?.id ? 'manual' : 'production',
);

// Source type for manual entry
const selectedSourceType = ref<string>(props.item?.source_type || 'opening_balance');

const form = useForm({
    production_order_id: props.item?.production_order_id || null,
    source_type: props.item?.source_type || (isRetail.value ? 'opening_balance' : 'production'),
    category_id: props.item?.category_id || null,
    sku: props.item?.sku || '',
    name: props.item?.name || '',
    inventory_location_id: props.item?.inventory_location_id || null,
    target_quantity: props.item?.target_quantity || 0,
    current_stock: props.item?.current_stock || 0,
    locations: (isEditing ? [] : [{ location_id: null, quantity: 0 }]) as LocationSplit[],
    unit_cost: props.item?.unit_cost || '0',
    selling_price: props.item?.selling_price || '0',
    notes: props.item?.notes || '',
    image: null as File | null,
});

const totalSplitQuantity = computed(() => form.locations.reduce((sum, split) => sum + (Number(split.quantity) || 0), 0));

const availableLocationsFor = (index: number) => {
    const chosenElsewhere = form.locations.filter((_, i) => i !== index).map((split) => split.location_id);
    return props.locations.filter((loc) => !chosenElsewhere.includes(loc.id) || loc.id === form.locations[index].location_id);
};

const remainingCapacityLabel = (locationId: number | null) => {
    const location = props.locations.find((loc) => loc.id === locationId);
    if (!location) return '';
    return location.capacity === null ? 'Kapasitas tidak terbatas' : `Sisa kapasitas: ${location.available_capacity}`;
};

const addLocationSplit = () => {
    form.locations.push({ location_id: null, quantity: 0 });
};

const removeLocationSplit = (index: number) => {
    if (form.locations.length > 1) {
        form.locations.splice(index, 1);
    }
};
```

- [ ] **Step 3: Remove the now-duplicate `isEditing` declaration further down the file**

Find (near the camera-modal refs, below the `submit` function):

```ts
const isEditing = !!props.item?.id;

const showCameraModal = ref(false);
```

Replace with:

```ts
const showCameraModal = ref(false);
```

- [ ] **Step 4: Gate the old single "Jumlah Stock" input to edit mode only**

Find (inside the "Data Stock" section, the div immediately after the `target_quantity` div):

```html
                        <div>
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
```

Replace with:

```html
                        <div v-if="isEditing">
                            <label for="current_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
```

- [ ] **Step 5: Replace the Lokasi field with an edit/create branch**

Find this whole block (inside "Informasi Tambahan"):

```html
                        <div>
                            <label for="inventory_location_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Lokasi <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="inventory_location_id"
                                v-model="form.inventory_location_id"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.inventory_location_id }"
                            >
                                <option :value="null">Pilih Lokasi</option>
                                <option v-for="location in locations" :key="location.id" :value="location.id">
                                    {{ location.name }} ({{ location.code }})
                                </option>
                            </select>
                            <p v-if="form.errors.inventory_location_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.inventory_location_id }}
                            </p>
                        </div>
```

Replace with:

```html
                        <div v-if="isEditing">
                            <label for="inventory_location_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Lokasi <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="inventory_location_id"
                                v-model="form.inventory_location_id"
                                required
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-300': form.errors.inventory_location_id }"
                            >
                                <option :value="null">Pilih Lokasi</option>
                                <option v-for="location in locations" :key="location.id" :value="location.id">
                                    {{ location.name }} ({{ location.code }})
                                </option>
                            </select>
                            <p v-if="form.errors.inventory_location_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.inventory_location_id }}
                            </p>
                        </div>

                        <div v-else data-testid="location-splits">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Lokasi & Jumlah Stock <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-2 space-y-3">
                                <div
                                    v-for="(split, index) in form.locations"
                                    :key="index"
                                    data-testid="location-split-row"
                                    class="flex flex-col gap-2 rounded-lg border border-gray-200 p-3 sm:flex-row sm:items-start dark:border-gray-700"
                                >
                                    <div class="flex-1">
                                        <select
                                            :name="`locations[${index}][location_id]`"
                                            v-model="split.location_id"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-300': form.errors[`locations.${index}.location_id`] }"
                                        >
                                            <option :value="null">Pilih Rak</option>
                                            <option v-for="location in availableLocationsFor(index)" :key="location.id" :value="location.id">
                                                {{ location.name }} ({{ location.code }})
                                            </option>
                                        </select>
                                        <p v-if="split.location_id" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ remainingCapacityLabel(split.location_id) }}
                                        </p>
                                        <p
                                            v-if="form.errors[`locations.${index}.location_id`]"
                                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors[`locations.${index}.location_id`] }}
                                        </p>
                                    </div>
                                    <div class="w-full sm:w-40">
                                        <input
                                            :name="`locations[${index}][quantity]`"
                                            v-model.number="split.quantity"
                                            type="number"
                                            min="1"
                                            required
                                            placeholder="Jumlah"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-300': form.errors[`locations.${index}.quantity`] }"
                                        />
                                        <p
                                            v-if="form.errors[`locations.${index}.quantity`]"
                                            class="mt-1 text-sm text-red-600 dark:text-red-400"
                                        >
                                            {{ form.errors[`locations.${index}.quantity`] }}
                                        </p>
                                    </div>
                                    <button
                                        v-if="form.locations.length > 1"
                                        type="button"
                                        data-testid="remove-rack-button"
                                        @click="removeLocationSplit(index)"
                                        class="mt-1 self-start rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700"
                                        title="Hapus rak ini"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            <button
                                type="button"
                                data-testid="add-rack-button"
                                @click="addLocationSplit"
                                class="mt-3 inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm text-indigo-700 transition-colors hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300 dark:hover:bg-indigo-900/40"
                            >
                                <Plus class="h-4 w-4" />
                                Tambah Rak
                            </button>
                            <p v-if="form.errors.locations" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.locations }}
                            </p>
                            <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-300" data-testid="total-split-quantity">
                                Total Stock: {{ totalSplitQuantity }} {{ productionUnit }}
                            </p>
                        </div>
```

- [ ] **Step 6: Update `Location` interface in the two parent pages**

In `resources/js/pages/Inventory/Items/Create.vue` and `resources/js/pages/Inventory/Items/Edit.vue`, in both files replace:

```ts
interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number;
}
```

with:

```ts
interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number | null;
    available_capacity: number;
}
```

- [ ] **Step 7: Manually verify in the browser**

Run: `composer dev` (or `php artisan serve` + `npm run dev` separately), then:
1. Log in as `admin@tokoserbaada.com` / `password` (retail tenant — manual entry only, simplest path to click through).
2. Go to `/inventory/items/create`.
3. Confirm one Lokasi/Jumlah row shows by default (looks like the old single-field UI).
4. Click "+ Tambah Rak", confirm a second row appears and the already-picked rack disappears from the second row's dropdown.
5. Enter a quantity greater than a rack's remaining capacity, submit, confirm a red error shows under that row referencing the rack name.
6. Fix the quantities, submit, confirm redirect to the created item's show page and that a second item exists in `/inventory/items` with the same product name, different rack.
7. Edit an existing item — confirm the Lokasi field is still the old single dropdown (no split UI, no regression).

- [ ] **Step 8: Format and commit**

```bash
npm run lint
npm run format
git add resources/js/pages/Inventory/Items/Form.vue resources/js/pages/Inventory/Items/Create.vue resources/js/pages/Inventory/Items/Edit.vue
git commit -m "feat(inventory): add multi-rack split UI to the item create form"
```

---

### Task 4: Browser test for the end-to-end split flow

**Files:**
- Create: `tests/Browser/InventoryItemSplitTest.php`

**Interfaces:**
- Consumes: `data-testid="add-rack-button"` and the `locations[N][location_id]` / `locations[N][quantity]` field names from Task 3's template.

- [ ] **Step 1: Write the browser test**

```php
<?php

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenant::factory()->create([
        'name' => 'Toko Serba Ada',
        'slug' => 'toko-serba-ada-test',
        'business_category' => 'retail',
    ]);

    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'email' => 'test@toko.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);

    $this->rackA = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak A',
        'capacity' => 300,
    ]);

    $this->rackB = InventoryLocation::factory()->for($this->tenant)->create([
        'name' => 'Rak B',
        'capacity' => 300,
    ]);
});

it('splits stock across two racks when creating an inventory item', function () {
    actingAs($this->user);

    $page = visit('/inventory/items/create');

    $page->assertSee('Tambah Item Inventory')
        ->fill('name', 'Mukena Bali Putih')
        ->select('locations[0][location_id]', (string) $this->rackA->id)
        ->fill('locations[0][quantity]', '300')
        ->click('[data-testid="add-rack-button"]')
        ->select('locations[1][location_id]', (string) $this->rackB->id)
        ->fill('locations[1][quantity]', '100')
        ->fill('unit_cost', '25000')
        ->fill('selling_price', '45000')
        ->click('button[type="submit"]')
        ->assertNoJavascriptErrors();

    expect(InventoryItem::where('product_name', 'Mukena Bali Putih')->count())->toBe(2);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->rackA->id,
        'current_quantity' => 300,
    ]);

    $this->assertDatabaseHas('inventory_items', [
        'product_name' => 'Mukena Bali Putih',
        'location_id' => $this->rackB->id,
        'current_quantity' => 100,
    ]);
});
```

- [ ] **Step 2: Run it**

Run: `php artisan test --compact --filter=browser tests/Browser/InventoryItemSplitTest.php`
Expected: PASS. If a selector doesn't match (e.g. the `select()`/`fill()` name lookup), re-check the `name`/`data-testid` attributes added in Task 3 Step 5 against this test — they must match exactly.

- [ ] **Step 3: Commit**

```bash
git add tests/Browser/InventoryItemSplitTest.php
git commit -m "test(inventory): add browser test for multi-rack split creation flow"
```

---

### Task 5: Full verification pass

- [ ] **Step 1: Run the full backend test suite**

Run: `php artisan test --compact`
Expected: all tests PASS, including `tests/Feature/InventoryItemTest.php` and `tests/Browser/InventoryItemSplitTest.php`.

- [ ] **Step 2: Run PHP formatting**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no changes needed (already formatted per-task), or auto-fixes applied — re-run tests if it changes anything.

- [ ] **Step 3: Run frontend lint**

Run: `npm run lint`
Expected: no errors.

- [ ] **Step 4: Final commit if Steps 2-3 produced changes**

```bash
git add -A
git commit -m "chore: pint/lint fixes for multi-rack stock split feature"
```
