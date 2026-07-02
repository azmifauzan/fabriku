# Multi-Rack Stock Split on Inventory Item Create

Date: 2026-07-02
Status: Approved for planning

## Problem

`inventory_items` schema ties one row to exactly one `location_id` (rack).
When a finished-goods batch exceeds a single rack's capacity (e.g. 400 pcs
Mukena, rack capacity 300 pcs), users have no way to distribute the same
batch across multiple racks from the item-create form. The "Informasi
Tambahan" section only exposes a single Lokasi dropdown + single quantity
field.

There is currently no capacity enforcement anywhere in the create flow —
`InventoryLocation::available_capacity` is informational only (used in
dashboard/visualization), never validated on `InventoryItem` create.

## Scope

- **Create only.** Applies to `POST /inventory/items` (both production-order
  entries and manual entries — the Lokasi section is shared UI for both
  entry types in `Form.vue`).
- **Not in scope:** editing/splitting stock of an already-existing item
  (`PUT /inventory/items/{id}`), moving stock between racks after creation
  (existing `move()` endpoint is untouched), any cross-item aggregation
  reporting.

## Data Model Decision

No schema change. Each rack in a split becomes its own `inventory_items`
row (own auto-generated SKU, same `product_name`), created via multiple
`InventoryItem::create()` calls in one transaction — not a single logical
item spread across a pivot table.

Rejected alternative: a `inventory_item_locations` pivot table with
`current_quantity`/`reserved_quantity` as derived sums. Rejected because it
would require touching `reserveStock`/`releaseReservedStock`/`deductStock`/
`available_stock`/`scopeLowStock` and `SalesOrderObserver` — all of which
currently assume one row = one authoritative quantity. That refactor has a
much larger blast radius than this feature warrants.

**Consequence (accepted):** after a split, the N rows are independent
inventory items that happen to share a product name. There is no batch/group
id linking them. Stock reports, low-stock alerts, and available-stock
calculations remain per-row. If a "total stock for this product across
racks" view is needed later, it's a separate, additive feature (e.g. a
sum-by-product_name query) — not built here.

## Behavior

### Frontend (`resources/js/pages/Inventory/Items/Form.vue`)

- Replace the single Lokasi dropdown + the "Jumlah Stock"/"Hasil Produksi
  Aktual" quantity input with a repeatable list of splits:
  `{ location_id: number|null, quantity: number }[]`.
- Default state: one row (so the common single-rack case looks identical
  to today).
- "+ Tambah Rak" button appends a row. Each row can be removed (except when
  only one remains).
- Each row's Lokasi dropdown excludes locations already chosen in other
  rows of the same form (no duplicate rack in one submission).
- Each row shows the rack's remaining capacity as a hint (`sisa kapasitas:
  X` — locations with `capacity === null` show "unlimited" and are never
  blocked).
- A read-only "Total Stock" summary shows `sum(rows.quantity)`. This value
  is what previously came from the single `current_stock` input; it is no
  longer independently editable.
- `target_quantity` (from the selected production order) is unchanged —
  still one value, copied to every row created from that submission (it
  represents the PO's target output, not a per-rack allocation).
- Client-side validation before submit: every row has a location and
  `quantity >= 1`; if a row's quantity exceeds that rack's known remaining
  capacity, show an inline error (mirrors backend rule, doesn't replace it).

### Backend request (`app/Http/Requests/StoreInventoryItemRequest.php`)

Replace the top-level `location_id` / `current_quantity` rules with:

```php
'locations' => 'required|array|min:1',
'locations.*.location_id' => ['required', 'distinct', Rule::exists('inventory_locations', 'id')->where('tenant_id', $tenantId)],
'locations.*.quantity' => 'required|integer|min:1',
```

Add a `withValidator` after-hook: for each submitted location, verify
`quantity <= $location->available_capacity` (skip the check when
`capacity` is null). Fail with a 422 naming the offending rack, e.g.
"Rak {name} tidak cukup kapasitas (sisa: {n})."

`current_quantity`/`location_id` backward-compat aliases are removed from
this request only — `UpdateInventoryItemRequest` (edit flow) is untouched
and keeps the single-location shape.

### Backend controller (`app/Http/Controllers/InventoryItemController.php::store`)

- Wrap creation in `DB::transaction()`.
- Lock the involved `InventoryLocation` rows with `lockForUpdate()` before
  the capacity check runs inside the transaction, to prevent a race between
  two concurrent submissions overfilling the same rack (same pattern as the
  existing consumable-deduct lock in `quickCheckoutStore`).
- Loop over `locations[]`; for each entry, build the same `$data` payload
  used today (shared `product_name`, `unit_cost`, `selling_price`,
  `category_id`, `production_order_id`, `source_type`, `notes`,
  `image_path`) with that entry's `location_id` and `quantity` as
  `current_quantity`, and call `InventoryItem::create()`. SKU is
  auto-generated per row via existing `InventoryItem::generateSku()` — no
  special suffixing needed.
- On success, redirect to the show page of the **first** created item (or a
  list view if that reads better) with a success message summarizing how
  many rows were created.

### `InventoryService::getFormDataForCreateOrEdit()`

Add `available_capacity` to each location in the returned collection (using
the existing `InventoryLocation::available_capacity` accessor) so the
frontend can render the "sisa kapasitas" hint without extra requests.

## Testing

- Feature test: submitting 2 valid splits creates 2 `inventory_items` rows
  with correct `location_id`/`current_quantity` each, distinct SKUs.
- Feature test: a split quantity exceeding a rack's `available_capacity`
  is rejected with a 422 and does not create any rows (transaction
  rollback — assert row count unchanged).
- Feature test: duplicate `location_id` across two rows in one submission
  is rejected (422, `distinct` rule).
- Feature test: single-row submission (today's behavior) still works
  unchanged.
