# Enhancement Plan: Mode Toko Sederhana (Simple Shop / Retail)

## Tujuan

Memungkinkan tenant yang HANYA butuh fitur **stock barang** dan **jual-beli langsung** memakai Fabriku, **tanpa**:
- Mengubah alur existing (material → preparation → production → inventory → sales).
- Mengganggu kategori bisnis yang sudah ada (garment, food, craft, cosmetic).
- Memaksa user toko sederhana berjalan lewat workflow produksi yang tidak relevan.

## Prinsip Desain

1. **Additive only** — tambah kategori baru `retail` ke `config/business.php`, jangan ubah skema modul existing.
2. **Reuse, jangan duplikasi** — pakai `inventory_items`, `sales_orders`, `customers` yang sudah ada. Module Material/Pattern/Preparation/Production di-hide saja untuk tenant kategori `retail`.
3. **UI gating, bukan fork codebase** — sidebar, dashboard, dan permission default di-filter berdasarkan kategori tenant.
4. **Single source of truth tetap di model** — observer, scope, audit log tetap bekerja; ini bukan branch baru.

## Skenario User

- Toko kelontong, butik dropship, reseller skincare: barang dibeli jadi dari supplier → masuk stock → dijual.
- Tidak ada produksi, tidak ada bahan baku, tidak ada pattern.
- Butuh: catat barang masuk (purchase), catat barang keluar (sales), lihat stock current, lihat omzet.

## Mapping Fitur Existing ke Retail

| Fitur Existing | Untuk Retail | Strategi |
|---|---|---|
| `inventory_items` (current_quantity, selling_price, unit_cost) | **PAKAI sebagai katalog produk** | Sudah punya kolom yang dibutuhkan. `source_type='purchase'` sudah ada. |
| `inventory_item_categories` | **PAKAI sebagai kategori produk toko** | Sudah ada CRUD. |
| `inventory_locations` | **PAKAI sebagai rak/etalase toko** | Optional untuk toko kecil, biarkan nullable. |
| `stock_adjustments` (TYPE_OPENING_BALANCE, TYPE_PURCHASE, TYPE_CORRECTION, dst) | **PAKAI untuk barang masuk** | Tambah tipe `TYPE_PURCHASE_RECEIPT` kalau belum ada; kalau sudah, reuse. |
| `sales_orders` + `sales_order_items` | **PAKAI sebagai transaksi POS** | Sudah komplit. Tambah opsi quick-checkout. |
| `customers` | **OPSIONAL** untuk retail walk-in | Tambah customer default "Walk-in" auto-created per tenant. |
| `material_*`, `pattern_*`, `preparation_*`, `production_*`, `contractors` | **TIDAK DIPAKAI** | Hide dari sidebar dan permission default untuk retail. Tabel tetap exist, hanya kosong. |

## Roadmap Implementasi

### Fase 1: Kategori `retail` (Backend Config)

**Files**: `config/business.php`

Tambahkan entry baru:

```php
'retail' => [
    'label' => 'Toko / Retail',
    'description' => 'Toko sederhana yang menjual barang jadi tanpa proses produksi',
    'icon' => 'shop',
    'mode' => 'simple', // flag baru — dibaca oleh sidebar & permission seeder
    'terminology' => [
        'material' => 'Pembelian',          // dipakai kalau dibutuhkan di UI tapi normally tidak muncul
        'inventory' => 'Stock Barang',
        'production' => 'N/A',
        'preparation' => 'N/A',
        'production_order' => 'N/A',
        'contractor' => 'N/A',
    ],
    'product_types' => [
        'umum' => 'Umum',
        'makanan' => 'Makanan & Minuman',
        'rumah_tangga' => 'Rumah Tangga',
        'fashion' => 'Fashion',
        'elektronik' => 'Elektronik',
        'lainnya' => 'Lainnya',
    ],
    'material_types' => [],   // kosong, modul material tidak dipakai
    'material_attributes' => [],
    'rules' => [
        'enable_production_flow' => false,
        'enable_material_module' => false,
        'enable_preparation_module' => false,
        'enable_pattern_module' => false,
        'enable_contractor_module' => false,
        'enable_inventory_module' => true,
        'enable_sales_module' => true,
        'enable_purchase_module' => true,   // module baru ringan untuk catat barang masuk
        'track_batch_number' => false,
        'track_expired_date' => true,       // optional, useful untuk minimarket
    ],
],
```

Tambah `'retail'` ke `enabled_categories`.

**Tidak perlu migrasi.** Semua kolom rules sudah ada.

### Fase 2: Sidebar & Menu Filtering

**Files**: `resources/js/components/Sidebar.vue`, `resources/js/composables/useBusinessContext.ts`

Composable `useBusinessContext` sudah ada — extend dengan helper:

```ts
const isModuleEnabled = (moduleKey: string): boolean => {
  return businessContext.value?.rules?.[`enable_${moduleKey}_module`] !== false
}
```

Sidebar conditionally render group menu:
- Group "Bahan Baku" (Materials, Material Types, Material Receipts) — show if `enable_material_module`.
- Group "Persiapan" (Patterns, Preparation Orders) — show if `enable_preparation_module`.
- Group "Produksi" (Contractors, Production Orders) — show if `enable_production_module`.
- Group "Penjualan" / "Inventory" — always show kalau enabled.
- Menu "Pembelian" (new, hanya retail) — show if `enable_purchase_module`.

**HandleInertiaRequests** (`app/Http/Middleware/HandleInertiaRequests.php`) sudah share tenant info — pastikan share `category_config` lengkap rules.

### Fase 3: Permission Seeder Diferensiasi

**Files**: `database/seeders/RolePermissionSeeder.php` (atau yang setara)

Buat default role `retail_admin`, `retail_cashier` dengan permission subset:
- `inventory.view`, `inventory.edit`
- `sales.view`, `sales.edit`
- `purchase.view`, `purchase.edit` (permission baru)
- `report.view` (terbatas: hanya sales + inventory)
- TIDAK ada `material.*`, `pattern.*`, `preparation.*`, `production.*`

Pas register tenant dengan kategori `retail`, auto-assign role `retail_admin` ke admin user (existing flow di `RegisterController::store` sudah bisa di-extend tanpa breaking).

### Fase 4: Modul "Pembelian" (Quick Purchase Receipt)

Ini fitur baru terkecil yang dibutuhkan. Alasan tidak reuse `material_receipts`: retail tidak punya konsep "bahan baku jadi produk". Toko langsung beli produk jadi → masuk stok.

**Pendekatan: reuse `stock_adjustments` + UI wrapper.**

Tidak perlu tabel baru. Buat:
- Controller `PurchaseReceiptController` (resource lightweight)
- Form: pilih beberapa `InventoryItem`, qty per item, harga modal per item, supplier name (text bebas), tanggal, notes
- Submit: per item, create `StockAdjustment` dengan `adjustment_type = TYPE_PURCHASE`, `type = add`, sekaligus update `inventory_items.unit_cost` (moving-average optional, simple replace untuk MVP)
- Halaman list: query `StockAdjustment::where('adjustment_type', TYPE_PURCHASE)` dikelompokkan per transaksi (perlu kolom `batch_id` untuk grouping)

**Migrasi minimal** (additive, optional):
```
add column stock_adjustments.batch_id (nullable uuid) — untuk group multiple item dalam satu transaksi pembelian
add column stock_adjustments.supplier_name (nullable string)
add column stock_adjustments.purchase_invoice (nullable string)
```

Field ini nullable, tidak ganggu data existing.

### Fase 5: Quick Sales (POS-like UX)

**Files**: `resources/js/pages/SalesOrders/QuickCheckout.vue` (baru)

Halaman alternatif untuk retail: layout POS — daftar produk grid kiri, cart kanan, tombol cash/qris/transfer, langsung print thermal.

**Backend**: sama dengan `SalesOrderController::store` — tidak ada endpoint baru. UI saja yang berbeda. Default values:
- `customer_id` → "Walk-in" default customer (auto-create per tenant saat first checkout)
- `status` → langsung `completed` (skip draft → confirmed → completed; trigger observer deduct stock penuh)
- `payment_status` → `paid`
- `paid_amount` → `total_amount`

**Catatan**: observer SalesOrder existing harus diperbaiki dulu (lihat `code-review.md` CRITICAL #3) supaya transition draft→completed tidak terjadi via path yang ambigu. Untuk POS, langsung create as `completed` — observer perlu handle creating event juga, atau controller handle stock deduct langsung.

### Fase 6: Dashboard Retail-Spesifik

**Files**: `app/Http/Controllers/DashboardController.php`

Dashboard saat ini panggil stats material + pending_production. Untuk tenant retail, query ini balikin 0 — bukan masalah tampilan, tapi cards "Pending Production" menyesatkan.

**Strategi**: split dashboard ke component conditional:
- `Dashboard.vue` baca `categoryConfig.rules.enable_production_flow`.
- Kalau false: render `RetailDashboard.vue` yang fokus ke: omzet hari ini, total transaksi, top selling, stok rendah, value inventory.
- Kalau true: render dashboard existing.

Controller bisa skip query yang tidak relevan untuk hemat DB:

```php
if (! $tenant->categoryRule('enable_production_flow')) {
    return $this->retailDashboard($tenantId);
}
```

### Fase 7: Reports

Existing `ReportController` sudah modular (`material`, `inventory`, `sales`, `production`). Untuk retail:
- Hide menu Material Report & Production Report di sidebar.
- Tambah "Purchase Report" (data dari stock_adjustments type purchase).

### Fase 8: Onboarding & Default Data

Pas register dengan kategori `retail`:
1. Auto-create `InventoryLocation` default ("Toko Utama").
2. Auto-create `Customer` default ("Walk-in Customer").
3. Auto-create 1-2 `InventoryItemCategory` contoh ("Umum", "Best Seller").
4. Skip seeding `MaterialType`, `Pattern`, dst.

Lokasi: handler event `TenantRegistered` atau extend `RegisterController::store`.

## Yang TIDAK Akan Diubah

- Tabel & migrasi modul produksi (`materials`, `patterns`, `production_orders`, dst) — biarkan ada, hanya tidak di-CRUD.
- Endpoint existing — semua route tetap aktif; permission middleware yang menentukan akses per role.
- Observer SalesOrder workflow — kecuali perbaikan dari `code-review.md` (independent dari plan ini).
- Schema `inventory_items` — sudah cukup. `production_order_id` nullable, `source_type` punya nilai `purchase`.
- Wayfinder TS bindings — auto-regenerate.

## Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Tenant existing (garment/food/craft/cosmetic) terdampak oleh perubahan sidebar | Sidebar pakai feature flag dari config kategori; default semua module enabled untuk kategori existing. |
| Permission baru `purchase.*` belum di-seed untuk tenant lama | Migrasi seeder idempotent — insert kalau belum ada, di-grant ke admin role tenant existing tapi tidak ke role staff. |
| `RetailDashboard.vue` blank kalau data kosong | Empty state dengan CTA "Tambah produk pertama Anda" pointing ke inventory.create. |
| `StockAdjustment` reused untuk purchase membuat report adjustment bercampur | Filter di UI: tab "Penyesuaian Stok" vs tab "Pembelian" berdasarkan `adjustment_type`. |
| User retail salah pilih kategori saat register | Tambah link "Ubah kategori" di settings (lewat admin panel saja, tidak self-serve, untuk hindari data orphan). |
| Observer SalesOrder + quick-completion → double-decrement | Selesaikan dulu CRITICAL #3 di code-review sebelum ship quick-sales. |

## Estimasi Effort

| Fase | Effort | Blocker |
|---|---|---|
| 1. Config kategori `retail` | 0.5 hari | - |
| 2. Sidebar/composable filtering | 1 hari | Fase 1 |
| 3. Permission seeder retail | 1 hari | Fase 1 |
| 4. Modul Pembelian (CRUD lightweight) | 2 hari | Fase 1, migrasi minimal |
| 5. Quick Sales POS UI | 2 hari | CRITICAL #3 di code-review.md selesai |
| 6. Retail dashboard | 1 hari | Fase 2 |
| 7. Reports adjust | 0.5 hari | Fase 4 |
| 8. Onboarding default data | 0.5 hari | Fase 1, 2, 3 |
| **Total** | **8.5 hari** + 2 hari testing/QA | - |

## Acceptance Criteria

- Tenant baru pilih kategori "Toko / Retail" → masuk dashboard, sidebar hanya tampil: Dashboard, Stock Barang (Inventory), Pembelian, Penjualan (Quick Checkout), Pelanggan, Laporan, Pengaturan.
- Workflow lengkap: tambah produk → catat pembelian (stok bertambah) → quick checkout (stok berkurang, transaksi tersimpan) → cetak struk.
- Tenant existing dengan kategori garment/food/craft/cosmetic: tidak ada perubahan visible. Semua modul lama tetap berfungsi identik.
- Tidak ada migrasi yang menyentuh tabel/kolom yang dipakai modul produksi.
- Test integrasi baru: `RetailWorkflowTest.php` mengcover register retail → add product → purchase → quick sale → report.
- Test existing semua tetap hijau.

## Tahap Setelahnya (Out of Scope)

- Barcode scanner integration untuk POS — pakai library yang sudah ada (`html5-qrcode`).
- Multi-cashier session.
- Cash register/shift open-close.
- Loyalty points.
- Multi-outlet untuk satu tenant retail (butuh refactor multi-warehouse).
