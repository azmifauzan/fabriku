# Database Column Naming Conventions

## Critical: Inventory Items Column Names

⚠️ **IMPORTANT**: The `inventory_items` table uses specific column names that must be used consistently throughout the codebase.

### Correct Column Names

| Column | Type | Purpose |
|--------|------|---------|
| `current_quantity` | INTEGER | Current available stock |
| `target_quantity` | INTEGER | Production target / low stock threshold |
| `product_name` | VARCHAR(255) | Product display name |
| `product_code` | VARCHAR(100) | Optional product code |
| `location_id` | BIGINT | Foreign key to inventory_locations |
| `production_order_id` | BIGINT | Foreign key to production_orders |
| `expired_date` | DATE | Expiry date (for food products) |

### ❌ DO NOT USE These Column Names

These column names **do not exist** in the database:

- ❌ `current_stock` (use `current_quantity`)
- ❌ `reserved_stock` (REMOVED - not needed)
- ❌ `reserved_quantity` (REMOVED - not needed)
- ❌ `minimum_stock` (use `target_quantity`)
- ❌ `name` (use `product_name`)
- ❌ `inventory_location_id` (use `location_id`)
- ❌ `pattern_id` (removed - use `production_order_id`)
- ❌ `status` (removed - determine from quantities)
- ❌ `category` (stored in `attributes` JSON)

## Inventory Locations Column Names

| Column | Type | Purpose |
|--------|------|---------|
| `zone` | VARCHAR(100) | Storage zone (A, B, C) |
| `rack` | VARCHAR(100) | Rack identifier (A1, A2, B1) |
| `status` | VARCHAR(50) | active, inactive, maintenance |
| `temperature_min` | INTEGER | Min temperature for food storage |
| `temperature_max` | INTEGER | Max temperature for food storage |

### ❌ DO NOT USE

- ❌ `inventory_location_id` in relationships (use `location_id`)
- ❌ `is_active` (use `status`)

## Sales Orders Column Names

⚠️ **IMPORTANT**: The `sales_orders` table uses specific column names for monetary amounts.

### Correct Column Names

| Column | Type | Purpose |
|--------|------|---------|
| `total` | DECIMAL(15,2) | Final order total amount |
| `subtotal` | DECIMAL(15,2) | Order subtotal before tax/discount |
| `discount` | DECIMAL(15,2) | Discount amount |
| `tax` | DECIMAL(15,2) | Tax amount |
| `shipping_cost` | DECIMAL(15,2) | Shipping cost |
| `paid_amount` | DECIMAL(15,2) | Amount already paid |
| `payment_status` | ENUM | Payment status (pending, partial, paid, refunded) |
| `fulfillment_status` | ENUM | Order status (pending, processing, shipped, delivered, cancelled) |
| `sales_channel` | ENUM | Sales channel (offline, online, marketplace, reseller) |

### ❌ DO NOT USE These Column Names

These column names **do not exist** in the database:

- ❌ `total_amount` (use `total`)
- ❌ `amount` (use `total`)
- ❌ `status` (use `fulfillment_status` for order status or `payment_status` for payment)
- ❌ `channel` (use `sales_channel`)
- ❌ `discount_amount` (use `discount`)
- ❌ `tax_amount` (use `tax`)
- ❌ `discount_percentage` (removed - not used)

```php
// ✅ CORRECT
$order = SalesOrder::select('id', 'order_number', 'total', 'status')->first();
$totalSales = SalesOrder::sum('total');

// ❌ WRONG
$order = SalesOrder::select('id', 'order_number', 'total_amount', 'status')->first();
$totalSales = SalesOrder::sum('total_amount');
```

## Low Stock Logic

Low stock is determined by comparing quantities:

```php
// ✅ CORRECT
$isLowStock = $item->current_quantity <= $item->target_quantity;

// ✅ Or use the scope
InventoryItem::lowStock()->get();

// ❌ WRONG - These columns don't exist
$isLowStock = $item->current_stock <= $item->minimum_stock;
```

## Available Stock Calculation

```php
// ✅ CORRECT - Now just current_quantity (reserved_quantity removed)
$availableStock = $item->current_quantity;

// ✅ Or use the accessor
$availableStock = $item->available_stock;

// ❌ WRONG - reserved_quantity has been removed
$availableStock = $item->current_quantity - $item->reserved_quantity;
```

## Model Scopes

Use these scopes defined in `InventoryItem` model:

```php
// Low stock items
InventoryItem::lowStock()->get();
// WHERE current_quantity <= target_quantity

// Expiring items (food)
InventoryItem::expiring(7)->get();
// WHERE expired_date <= NOW() + 7 days

// Expired items
InventoryItem::expired()->get();
// WHERE expired_date < NOW()
```

## Common Mistakes to Avoid

1. **Using `current_stock` instead of `current_quantity`**
   ```php
   // ❌ WRONG
   $total = InventoryItem::sum('current_stock');
   
   // ✅ CORRECT
   $total = InventoryItem::sum('current_quantity');
   ```

2. **Using `name` instead of `product_name`**
   ```php
   // ❌ WRONG
   $item->name = 'Mukena Bali';
   
   // ✅ CORRECT
   $item->product_name = 'Mukena Bali';
   ```

3. **Using wrong foreign key names**
   ```php
   // ❌ WRONG
   $item->inventory_location_id = 1;
   
   // ✅ CORRECT
   $item->location_id = 1;
   ```

## Validation Rules

When creating Form Requests, use correct column names:

```php
// ✅ CORRECT
public function rules(): array
{
    return [
        'product_name' => 'required|string|max:255',
        'current_quantity' => 'required|integer|min:0',
        'reserved_quantity' => 'nullable|integer|min:0',
        'target_quantity' => 'required|integer|min:0',
        'location_id' => 'required|exists:inventory_locations,id',
    ];
}
```

## Testing

Ensure tests use correct column names in factories:

```php
// ✅ CORRECT
InventoryItem::factory()->create([
    'product_name' => 'Test Product',
    'current_quantity' => 100,
    'target_quantity' => 10,
]);
```

## Sales Orders Column Names

⚠️ **IMPORTANT**: The `sales_orders` table uses:

| Column | Type | Purpose |
|--------|------|---------|
| `total` | DECIMAL(15,2) | Total order amount |
| `subtotal` | DECIMAL(15,2) | Subtotal before tax/shipping |
| `discount` | DECIMAL(15,2) | Discount amount |
| `tax` | DECIMAL(15,2) | Tax amount |
| `shipping_cost` | DECIMAL(15,2) | Shipping cost |
| `paid_amount` | DECIMAL(15,2) | Amount already paid |

### ❌ DO NOT USE

- ❌ `total_amount` (use `total`)
- ❌ `amount` (use `total`)

```php
// ✅ CORRECT
$totalSales = SalesOrder::sum('total');

// ❌ WRONG
$totalSales = SalesOrder::sum('total_amount');
```

## Migration Guidelines

If you need to modify the inventory_items table:

1. **DO NOT** add back old column names
2. **DO** use the current naming convention
3. **DO** run migration:fresh to test
4. **DO** update all related code (controllers, models, views, tests)
5. **DO** update documentation

## Error Prevention

Before committing code:

1. ✅ Run `get_errors` tool to check for undefined column references
2. ✅ Run `vendor/bin/pint --dirty` to format code
3. ✅ Run `php artisan test --filter=Inventory` to test inventory features
4. ✅ Test in browser to ensure UI works correctly

---

**Last Updated**: 2026-01-16
**Migration Version**: 2026_01_06_000001_create_inventory_tables.php
