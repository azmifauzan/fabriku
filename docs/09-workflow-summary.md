# Workflow Summary - Fabriku (Simplified Preparation)

**Version**: 1.0 (Post-Refactoring)  
**Date**: 15 Januari 2026

---

## 🔄 Complete Workflow Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    FABRIKU WORKFLOW                              │
│                 (Simplified Preparation)                          │
└─────────────────────────────────────────────────────────────────┘

1. 📦 INPUT MATERIAL (Material Receipt)
   ├─ Material master data
   ├─ Stock tracking
   └─ Dynamic attributes (warna, expired, dll)
          ↓
2. 📐 CREATE PATTERN (Optional)
   ├─ Product template
   ├─ Spesifikasi lengkap
   └─ Referensi material needs (estimasi)
          ↓
3. ✂️ PREPARATION ORDER (Core Simplification)
   ├─ Manual input materials used
   ├─ Pattern as reference (optional)
   ├─ Output quantity tracking
   └─ Auto deduct stock on completed
          ↓
4. 🧵 PRODUCTION ORDER
   ├─ Link to prep order
   ├─ Internal or outsource
   ├─ Quality tracking
   └─ Cost calculation
          ↓
5. 📊 INVENTORY MANAGEMENT
   ├─ Finished goods storage
   ├─ Location tracking (racks)
   └─ FIFO/FEFO management
          ↓
6. 💰 SALES ORDER
   ├─ Multi-channel sales
   ├─ Payment tracking
   └─ Auto stock deduction
```

---

## 🎯 Key Business Rules

### Material Management
- ✅ Dynamic attributes per category (warna, expired, gramasi, dll)
- ✅ Stock tracking dengan current_stock field
- ✅ Reorder point alerts
- ✅ FIFO/FEFO support

### Pattern (Optional Reference)
- ✅ Pattern can be used or skipped
- ✅ No auto-fill dari pattern ke prep order
- ✅ Pattern hanya sebagai template/referensi
- ✅ Estimasi material needs (tidak binding)

### Preparation Order (Core Changes)
- ✅ **Manual input** materials yang actually used
- ✅ **No BOM table** (simplified)
- ✅ **No cutting_results** (simplified)
- ✅ materials_used stored as **JSON array**
- ✅ Pattern ID **nullable** (optional reference)
- ✅ **Auto deduct stock** via Observer when status = completed
- ✅ Stock validation before save
- ✅ Status workflow: draft → in_progress → completed

### Production Order
- ✅ Link to preparation_order_id (FK)
- ✅ Can be internal or external (contractor)
- ✅ Quality tracking (Grade A/B/Reject)
- ✅ Cost calculation (labor + overhead)

---

## 📊 Database Schema (Simplified)

### Core Tables

```
materials (Bahan Baku)
├─ id, tenant_id
├─ code, name, type, unit
├─ current_stock ← Real-time stock
├─ standard_price, reorder_point
└─ is_active

material_attributes (Dynamic Attributes)
├─ material_id
├─ attribute_name (warna, lebar, expired, dll)
└─ attribute_value

patterns (Templates)
├─ id, tenant_id
├─ code, name, category, product_type
├─ size, target_output, output_unit
└─ estimated_material_needs (optional text)

preparation_orders (Core Simplified)
├─ id, tenant_id, order_number
├─ pattern_id (nullable - optional reference)
├─ order_date, prepared_by
├─ materials_used (JSONB) ← Manual input
│   Example: [
│     { material_id: 1, material_name: "Kain Katun", 
│       quantity: 25, unit: "meter" },
│     { material_id: 2, material_name: "Benang", 
│       quantity: 2, unit: "roll" }
│   ]
├─ output_quantity, output_unit
├─ status (draft, in_progress, completed, cancelled)
├─ notes
├─ started_at, completed_at
└─ Observer: auto deduct stock on completed

production_orders (Manufacturing)
├─ id, tenant_id, order_number
├─ preparation_order_id (FK) ← Link to prep
├─ type (internal/external)
├─ contractor_id (nullable)
├─ quantity_requested, quantity_produced
├─ quantity_good, quantity_reject
├─ labor_cost, status, priority
└─ completion tracking
```

### Deleted Tables (Post-Refactoring)
- ❌ `pattern_materials` (BOM table) - removed
- ❌ `cutting_results` (detailed results) - removed

---

## 🔄 Data Flow

### 1. Material Receipt → Stock Increase

```sql
-- User creates material receipt
INSERT INTO material_receipts (material_id, quantity, ...) 
VALUES (1, 100, ...);

-- Material stock auto increases via Observer
UPDATE materials 
SET current_stock = current_stock + 100 
WHERE id = 1;

-- Result: Material stock = 100 meter
```

### 2. Preparation Order → Stock Decrease

**Input**:
```javascript
// User creates prep order
{
  pattern_id: 5,  // optional
  materials_used: [
    { material_id: 1, quantity: 25, unit: "meter" },
    { material_id: 2, quantity: 2, unit: "roll" }
  ],
  output_quantity: 10,
  output_unit: "pieces",
  status: "completed"  // ← Triggers stock deduction
}
```

**Auto Deduction** (via PreparationOrder Observer):
```php
// Observer: PreparationOrder::updated()
if ($order->status === 'completed') {
    foreach ($order->materials_used as $mat) {
        Material::find($mat['material_id'])
            ->decrement('current_stock', $mat['quantity']);
    }
}
```

**Result**:
```
Material 1: 100 - 25 = 75 meter
Material 2: 50 - 2 = 48 roll
```

### 3. Production Order → Link to Prep

```sql
-- Production order references prep order
INSERT INTO production_orders (
  order_number, 
  preparation_order_id, -- ← FK to prep
  type, 
  quantity_requested, 
  ...
) VALUES (
  'PRD-2026-001',
  123,  -- prep order id
  'internal',
  10,
  ...
);

-- Can query materials used via prep order
SELECT po.*, 
       prep.materials_used,
       prep.output_quantity
FROM production_orders po
JOIN preparation_orders prep ON po.preparation_order_id = prep.id
WHERE po.id = 1;
```

---

## ✅ Validation Rules

### Material Receipt
- ✅ Quantity > 0
- ✅ Material must exist
- ✅ Tenant isolation

### Preparation Order
- ✅ Pattern is optional (can be null)
- ✅ materials_used array required (min 1 item)
- ✅ Each material must have: material_id, quantity, unit
- ✅ **Stock availability check** before save:
  ```php
  foreach ($materials_used as $mat) {
      $material = Material::find($mat['material_id']);
      if ($material->current_stock < $mat['quantity']) {
          throw ValidationException::withMessages([
              'materials_used' => 'Stock not sufficient'
          ]);
      }
  }
  ```
- ✅ output_quantity > 0
- ✅ Cannot edit if status = completed or cancelled
- ✅ Tenant isolation

### Production Order
- ✅ preparation_order_id must exist and completed
- ✅ If external, contractor_id required
- ✅ quantity_requested > 0
- ✅ Tenant isolation

---

## 🎨 UI/UX Flow

### Preparation Order Form

**Form Layout**:
```
┌───────────────────────────────────────────────┐
│ Tambah Preparation Order                      │
├───────────────────────────────────────────────┤
│ [Section: Informasi Dasar]                    │
│   Pattern (Optional):  [Dropdown ▼]           │
│   Tanggal Order:       [Date Picker]          │
│   Penanggung Jawab:    [Dropdown ▼]           │
├───────────────────────────────────────────────┤
│ [Section: Material yang Digunakan]            │
│   ┌─────────────────────────────────────────┐ │
│   │ Material: [Kain Katun ▼] (Stock: 100m) │ │
│   │ Jumlah:   [25]  Satuan: [meter]        │ │
│   │                              [X Remove] │ │
│   └─────────────────────────────────────────┘ │
│   ┌─────────────────────────────────────────┐ │
│   │ Material: [Benang ▼] (Stock: 50 roll)  │ │
│   │ Jumlah:   [2]   Satuan: [roll]         │ │
│   │                              [X Remove] │ │
│   └─────────────────────────────────────────┘ │
│   [+ Tambah Material]                         │
├───────────────────────────────────────────────┤
│ [Section: Output]                             │
│   Output Quantity: [10]                       │
│   Output Unit:     [pieces ▼]                 │
├───────────────────────────────────────────────┤
│ [Section: Status]                             │
│   Status: [Completed ▼]                       │
│   ⚠️ Warning: Stock will be deducted!         │
│   Notes:  [Text area...]                      │
├───────────────────────────────────────────────┤
│            [Batal]  [Simpan]                  │
└───────────────────────────────────────────────┘
```

**User Actions**:
1. Select pattern (optional) - for reference only
2. Click "+ Tambah Material"
3. Select material from dropdown (shows current stock)
4. Input quantity
5. Repeat for more materials
6. Input output quantity & unit
7. Select status (draft/in_progress/completed)
8. Click "Simpan"

**Validation**:
- If stock insufficient → Show error, prevent save
- If status = completed → Show warning about stock deduction
- Require at least 1 material

---

## 🧪 Testing Checklist

### Unit Tests
- ✅ PreparationOrder model auto-generates order number
- ✅ PreparationOrder casts materials_used to array
- ✅ PreparationOrder Observer deducts stock on completed
- ✅ MaterialStockService checks availability
- ✅ MaterialStockService deducts stock correctly

### Feature Tests
- ✅ Create prep order with materials
- ✅ Create prep order without pattern (optional)
- ✅ Update prep order status to completed → stock deducted
- ✅ Prevent save if stock insufficient
- ✅ Cannot edit completed order
- ✅ Can delete draft order
- ✅ Tenant isolation

### Browser Tests (Pest 4)
- ✅ Navigate to prep order form
- ✅ Add materials dynamically
- ✅ Select material shows stock info
- ✅ Submit form successfully
- ✅ Stock deduction visible after completed
- ✅ Error message if stock insufficient

---

## 🚀 Migration from Old to New

### What Changed
1. **Removed**:
   - `pattern_materials` table (BOM)
   - `cutting_results` table
   - Complex BOM calculations

2. **Simplified**:
   - Preparation order now stores materials as JSON
   - Pattern is optional reference
   - Direct manual input

3. **Added**:
   - Auto deduct via Observer
   - Stock availability validation
   - Simpler status workflow

### Migration Steps
1. ✅ Drop old tables (pattern_materials, cutting_results)
2. ✅ Create new preparation_orders table with JSON field
3. ✅ Update production_orders FK to preparation_order_id
4. ✅ Update PreparationOrder model with Observer
5. ✅ Update controllers for manual material input
6. ✅ Update Vue components for new workflow
7. ✅ Update tests
8. ✅ Update documentation

---

## 📝 Developer Notes

### When Adding New Features

**DO**:
- ✅ Use PreparationOrder::materials_used (JSON) for material tracking
- ✅ Trust the Observer for stock deduction
- ✅ Validate stock availability in controller
- ✅ Make pattern_id nullable everywhere
- ✅ Test with and without pattern

**DON'T**:
- ❌ Try to create pattern_materials records (table removed)
- ❌ Try to create cutting_results (table removed)
- ❌ Assume pattern must exist
- ❌ Manually deduct stock (Observer handles it)

### Code Examples

**Creating Prep Order**:
```php
$order = PreparationOrder::create([
    'tenant_id' => auth()->user()->tenant_id,
    'pattern_id' => 5, // optional
    'order_date' => now(),
    'materials_used' => [
        ['material_id' => 1, 'quantity' => 25, 'unit' => 'meter'],
        ['material_id' => 2, 'quantity' => 2, 'unit' => 'roll'],
    ],
    'output_quantity' => 10,
    'output_unit' => 'pieces',
    'status' => 'completed', // ← Triggers Observer
]);
// Stock auto deducted by Observer
```

**Querying with Materials**:
```php
$order = PreparationOrder::with('pattern')->find(1);
$materialsUsed = $order->materials_used; // Array
foreach ($materialsUsed as $mat) {
    echo "{$mat['material_name']}: {$mat['quantity']} {$mat['unit']}";
}
```

---

**Last Updated**: 15 Januari 2026  
**Status**: ✅ Production Ready
