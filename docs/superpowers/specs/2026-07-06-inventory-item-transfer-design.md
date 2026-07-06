# Split/Transfer Stock of an Existing Inventory Item Across Racks

Date: 2026-07-06
Status: Approved for planning

## Problem

The multi-rack split feature (`docs/superpowers/specs/2026-07-02-multi-rack-stock-split-design.md`)
only covers `POST /inventory/items` (create). Once an item exists as a single
row tied to one rack, there is no way to relocate or split part of its stock
across other racks.

The codebase has a `InventoryItemController::move()` method, but it has no
registered route and no frontend UI — it is dead code. It also only supports
relocating an entire row to one new `location_id`, with no quantity/partial
support and no capacity validation.

## Scope

- Applies to an existing `inventory_items` row. User moves part or all of its
  *available* stock (see below) out to one or more other racks.
- Replaces the dead `move()` method entirely — no route currently points to
  it, so nothing regresses.
- Not in scope: bulk transfer of multiple items at once, transferring
  reserved stock, changing which sales order a reservation is tied to.

## Data Model Decision

No schema change, consistent with the create-flow split. Each destination
rack becomes its own new `inventory_items` row (own auto-generated SKU,
copied `product_name`/`unit_cost`/`selling_price`/`category_id`/
`production_order_id`/`source_type` from the source row), created with
`current_quantity = 0` and then incremented via
`InventoryService::adjustStock()` so the audit trail (`StockAdjustment`)
is written through the existing, already-tested code path.

**Always creates a new row per destination**, even if a row for the same
`product_name` already exists at that rack (matches the create-flow's
accepted consequence: N independent rows that happen to share a product
name — no merge/pivot logic).

## Behavior

### Reserved stock is untouched

Only `available_stock` (`current_quantity - reserved_quantity`) may be
moved. `reserved_quantity` represents a commitment to a specific sales order
tied to the source row and must stay there until that order completes or is
cancelled — moving it would desync `SalesOrderObserver`'s stock deduction
from the row it actually reserved against.

### Source row lifecycle

If a transfer moves out all of the source row's `available_stock` (i.e.
`current_quantity` reaches `reserved_quantity`, and specifically reaches `0`
when `reserved_quantity` is also `0`), and the resulting `current_quantity`
is `0`, the source row is soft-deleted (`InventoryItem` already uses
`SoftDeletes`). Otherwise the source row survives with its reduced
`current_quantity`, unchanged `location_id`.

### Backend service (`app/Services/InventoryService.php`)

New method `transferStock(InventoryItem $item, array $splits, string $reason, ?string $notes = null): array`:

- `$splits` is `array<int, array{location_id:int, quantity:int}>`, already
  validated by the request (see below).
- Wrapped in `DB::transaction()`. Reuses `adjustStock()` internally — nested
  `DB::transaction()` calls are safe in Laravel (savepoints).
- Lock the source item row and all destination `InventoryLocation` rows with
  `lockForUpdate()` before mutating (same pattern as the create-flow's rack
  lock), to prevent a race between two concurrent transfers/creates
  overfilling a destination rack or over-subtracting the source.
- `adjustStock($item, 'subtract', $totalQuantity, 'transfer', $reason, $notes)`
  on the source row.
- For each split: `InventoryItem::create([...copied fields, 'location_id' =>
  $split['location_id'], 'current_quantity' => 0])`, then
  `adjustStock($newItem, 'add', $split['quantity'], 'transfer', $reason, $notes)`.
- After the subtract, if `$item->fresh()->current_quantity === 0`, soft-delete
  the source row (`$item->delete()`).
- Returns the list of created `InventoryItem` rows (for the controller's
  success message / redirect target, if needed — this feature stays on the
  same Show page, so the return value is mainly for the flash message count).

### `StockAdjustment` model

Add a new adjustment type:

```php
const TYPE_TRANSFER = 'transfer';
```

Added to `getAdjustmentTypes()`: `self::TYPE_TRANSFER => 'Transfer Lokasi'`.
Both legs of a transfer (`subtract` on source, `add` on each destination) are
recorded with `adjustment_type = 'transfer'`, so they show up in the
existing per-item "Riwayat Penyesuaian" page (`adjustmentHistory()`) for both
the source item and every newly created destination item.

### Backend request (new `app/Http/Requests/TransferInventoryItemRequest.php`)

```php
'splits' => 'required|array|min:1',
'splits.*.location_id' => [
    'required', 'distinct',
    Rule::exists('inventory_locations', 'id')->where('tenant_id', $tenantId),
    Rule::notIn([$this->route('item')->location_id]),
],
'splits.*.quantity' => 'required|integer|min:1',
'reason' => 'required|string|max:255',
'notes' => 'nullable|string|max:1000',
```

`withValidator` after-hook (mirrors the create-flow's capacity check, plus
the new available-stock check):

- `sum(splits.*.quantity) <= $item->available_stock` — error on the
  `splits` key if exceeded: "Total yang dipindah ({n}) melebihi stock
  tersedia ({available_stock})."
- Per split, `quantity <= $location->available_capacity` (skip when
  `capacity` is null) — same message format as create-flow: "Rak {name}
  tidak cukup kapasitas (sisa: {n})."

`authorize()`: `$this->user()?->tenant_id === $this->route('item')->tenant_id`
(the route-model-bound `InventoryItem` is already tenant-scoped via
`TenantScope`, so a cross-tenant ID 404s before this even runs — this is a
defense-in-depth check, matching the project convention of not relying
solely on route-model binding for tenant isolation in Form Requests).

### Backend controller (`app/Http/Controllers/InventoryItemController.php`)

Replace `move()` with:

```php
public function transfer(TransferInventoryItemRequest $request, InventoryItem $item)
{
    $created = $this->inventoryService->transferStock(
        $item,
        $request->validated('splits'),
        $request->validated('reason'),
        $request->validated('notes'),
    );

    return back()->with('success', 'Stock berhasil dipindah ke '.count($created).' lokasi.');
}
```

### Route (`routes/web.php`)

```php
Route::post('items/{item}/transfer', [InventoryItemController::class, 'transfer'])->name('items.transfer');
```

Added next to the existing `items/{item}/adjust` route.

### `InventoryItemController::show()`

Add a `locations` prop, built by inlining the same
`InventoryLocation::active()->orderBy('name')->get(['id', 'name', 'code',
'capacity'])` + `available_capacity` pattern used by
`getFormDataForCreateOrEdit()` directly in `show()` (not calling that service
method — it also loads `patterns`/`productionOrders`/`categories`, which
`show()` doesn't need) so the modal can render the rack picker with capacity
hints without an extra request.

## Frontend

### `resources/js/pages/Inventory/Items/TransferStockModal.vue` (new)

Mirrors `AdjustStockModal.vue`'s structure (Teleport, Transition, header,
footer actions) but with the create-flow's repeatable-rack-row UX
(`resources/js/pages/Inventory/Items/Form.vue`'s `location-splits` block):

- Header: item name + SKU, info box "Stock Tersedia: {available_stock}"
  (current_quantity − reserved_quantity — the real ceiling, not the raw
  current_quantity).
- Repeatable rows `{ location_id: number|null, quantity: number }[]`,
  default 1 row. "+ Tambah Rak" appends a row. Each row's dropdown excludes:
  locations already chosen in other rows of this submission, **and** the
  item's current `location_id`.
- Per-row remaining-capacity hint + inline capacity-exceeded error (same
  `capacityErrorFor` logic as the create-flow fix).
- Footer: "Total Dipindah: {sum}" with an inline error if `sum >
  available_stock` (mirrors backend's available-stock check, doesn't
  replace it) — submit is blocked client-side in that case, same pattern as
  `hasCapacityErrors` in `Form.vue`.
- `reason` required text input (used for both `StockAdjustment` legs' shared
  `reason` field).
- `notes` optional textarea.
- Submit → `form.post(/inventory/items/{item.id}/transfer)`, `preserveScroll: true`,
  `onSuccess` closes the modal (mirrors `AdjustStockModal`).

### `Show.vue`

- Add `locations: Location[]` prop (same `Location` interface shape as
  `Form.vue`/`Create.vue`: `{ id, name, code, capacity: number | null,
  available_capacity: number }`).
- Add a "Pindah/Split Stock" button next to the existing "Adjust Stock"
  button, toggling `showTransferModal`.
- Render `<TransferStockModal :show="showTransferModal" :item="item"
  :locations="locations" @close="showTransferModal = false" />`.

## Testing

`tests/Feature/InventoryItemTest.php` (new block):

1. Partial transfer: source row's `current_quantity` decreases correctly;
   one new row per destination rack with correct `current_quantity`,
   distinct SKUs from the source and each other.
2. Full available-stock transfer with `reserved_quantity = 0`: source row is
   soft-deleted (`assertSoftDeleted`).
3. Full available-stock transfer with `reserved_quantity > 0`: source row
   survives with `current_quantity === reserved_quantity` (not deleted,
   since it's not actually empty).
4. Reject: `sum(quantities) > available_stock` (both the plain case and the
   reserved-quantity-constrained case) → 422 on `splits`, zero new rows,
   source row unchanged (transaction rollback).
5. Reject: destination rack capacity insufficient → 422, zero new rows,
   source row unchanged.
6. Reject: duplicate destination rack within one submission (`distinct`
   rule).
7. Reject: destination rack equals the item's current `location_id`.
8. Reject: cross-tenant item (404 via route-model binding + `TenantScope`)
   and cross-tenant destination location (422 via `Rule::exists` tenant
   scoping).
9. Assert `StockAdjustment` rows: one `subtract` on the source item, one
   `add` per destination item, all `adjustment_type === 'transfer'`, correct
   `quantity_before`/`quantity_after`/`adjustment_quantity`.

`tests/Browser/InventoryItemTransferTest.php` (new, mirrors
`InventoryItemSplitTest.php`): visit an existing item's Show page, click
"Pindah/Split Stock", fill 2 destination racks, submit, assert the resulting
DB state (source row's reduced quantity, two new rows with correct
`location_id`/`current_quantity`).
