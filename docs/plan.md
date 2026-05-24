# Enhancement Plans

> **Status (Mei 2026)**: Plan A (Retail) dan Plan B (Homemade) keduanya **SELESAI PENUH**. Semua fitur aktif di production. Lihat `docs/current-status.md` untuk detail modul.

---

## Plan A: Retail — SELESAI ✅ (semua fase diimplementasikan)

### Fase 7: Purchase Report ✅ SELESAI

Diimplementasikan:
- `GET /reports/purchase` (`reports.purchase`) + `GET /reports/purchase/export` (`reports.purchase.export`) di `ReportController`
- Query: `StockAdjustment` where `adjustment_type = 'purchase'`, group per `batch_id`, join `inventoryItem` + `adjustedBy`
- Vue page: `resources/js/pages/Reports/PurchaseReport.vue` — tabel per transaksi + drill-down per batch (collapsible)
- Export Excel via `app/Exports/PurchaseReportExport.php` + view `resources/views/exports/purchase-report.blade.php`
- Export PDF via `resources/views/pdf/purchase-report.blade.php`
- Wayfinder: `purchase` + `exportPurchase` ter-generate di `resources/js/actions/App/Http/Controllers/ReportController.ts`
- Sidebar: menu "Pembelian" muncul di Laporan kalau `isModuleEnabled('purchase')` (retail only); Material + Produksi laporan sudah hidden untuk retail

### Test Integrasi ✅ SELESAI

File: `tests/Feature/Integration/RetailWorkflowTest.php`

Cover yang diimplementasikan:
1. Login tenant retail → dashboard accessible
2. Buat inventory item
3. Catat purchase receipt → stok bertambah (20 unit), `StockAdjustment` TYPE_PURCHASE tercreate
4. Cek laporan pembelian → 1 batch, total_cost 240000
5. Export purchase Excel + PDF → HTTP 200
6. Quick checkout → stok berkurang (20 → 15)

Tidak di-cover (low priority):
- Sidebar module visibility assertions (material/produksi tidak muncul)

---

## Plan B: Kategori `homemade` — SELESAI (semua fase diimplementasikan)

### Latar Belakang & Keputusan Desain

**Target user**: UMKM produksi skala rumah — toko kue rumahan, kerajinan tangan, frozen food rumahan. Mereka:
- Membeli bahan baku (tepung, mentega, kemasan) — **perlu modul material**
- Tidak mencatat line production detail (tidak pakai Production Order, tidak pakai Contractor)
- Langsung input produk jadi ke inventory setelah selesai produksi
- Menjual via quick checkout (mirip retail)

**Keputusan: kategori baru `homemade`** (bukan modifikasi `food`/`craft` yang sudah ada, bukan opsi di register step).

Alasan:
- `food` dan `craft` existing diasumsikan full-flow (preparation → production order). Mengubah perilaku mereka merusak tenant yang sudah pakai.
- Kategori baru = config bersih, tidak ada conditional dalam conditional.
- Satu langkah pilih di register (tidak ada step tambahan), konsisten dengan UX saat ini.

### Perbedaan vs Kategori Lain

| Fitur | retail | homemade | food/craft/garment/cosmetic |
|---|---|---|---|
| Bahan baku (Materials) | ❌ | ✅ | ✅ |
| Pattern / Resep | ❌ | ✅ (opsional) | ✅ |
| Preparation Order | ❌ | ❌ | ✅ |
| Production Order | ❌ | ❌ | ✅ |
| Input Produk Jadi (Simple Production) | ❌ | ✅ **baru** | ❌ (lewat prod. order) |
| Quick Checkout POS | ✅ | ✅ | ❌ |
| Purchase Receipt (beli produk jadi) | ✅ | ❌ | ❌ |

### Prinsip Desain

1. **Additive only** — tambah kategori ke `config/business.php`, tidak ubah tabel.
2. **Reuse maksimal** — material, material_receipts, inventory, sales sudah ada. Hanya perlu satu fitur baru: "Simple Production" (input produk jadi).
3. **UI gating** — sama dengan retail: sidebar + dashboard filter via `rules`.
4. **"Simple Production" bukan fork Production Order** — halaman sederhana satu form, bukan state machine. Tidak pakai tabel `production_orders`.

### Roadmap Implementasi

#### Fase 1: Config Kategori `homemade` ✅ SELESAI

**File**: `config/business.php`

```php
'homemade' => [
    'label' => 'Produksi Rumahan',
    'description' => 'UMKM yang membuat produk sendiri dari bahan baku, tanpa alur produksi formal',
    'icon' => 'home',
    'mode' => 'homemade',
    'terminology' => [
        'material'         => 'Bahan Baku',
        'inventory'        => 'Produk Jadi',
        'production'       => 'Catatan Produksi',
        'production_order' => 'Catatan Produksi',
        'preparation'      => 'Resep',
        'contractor'       => 'N/A',
    ],
    'material_types' => ['bahan_baku', 'kemasan', 'bahan_tambahan'],
    'material_attributes' => [],
    'rules' => [
        'enable_production_flow'    => false,   // sembunyikan Production Order full
        'enable_material_module'    => true,    // bahan baku tetap ada
        'enable_preparation_module' => true,    // resep opsional
        'enable_pattern_module'     => true,    // alias resep
        'enable_contractor_module'  => false,   // tidak pakai kontraktor
        'enable_simple_production'  => true,    // flag baru: "Catatan Produksi" sederhana
        'enable_inventory_module'   => true,
        'enable_sales_module'       => true,
        'enable_purchase_module'    => false,   // tidak beli produk jadi
        'track_batch_number'        => true,    // useful untuk food (nomor batch produksi)
        'track_expired_date'        => true,    // expired date untuk food
    ],
],
```

Tambah `'homemade'` ke `enabled_categories`. Tidak perlu migrasi.

#### Fase 2: Sidebar Filtering ✅ SELESAI

`Sidebar.vue` sudah pakai `isModuleEnabled` — extend:
- Sembunyikan "Kontraktor" kalau `!enable_contractor_module`
- Sembunyikan "Production Order" kalau `!enable_production_flow` (sudah ada via `isRetailMode`, perlu generalisasi ke `enable_production_flow`)
- Tampilkan "Catatan Produksi" kalau `enable_simple_production`

Composable `useBusinessContext.ts`:
```ts
const isSimpleProductionMode = computed<boolean>(() =>
    businessContext.value?.rules?.enable_simple_production === true
)
```

#### Fase 3: "Catatan Produksi" (Simple Production Entry) ✅ SELESAI

Ini satu-satunya fitur baru yang membutuhkan controller + halaman baru. **Tidak pakai tabel baru** — reuse `stock_adjustments` + `material_receipts`.

**Alur**:
1. User pilih resep (opsional) atau input manual
2. Input: produk yang dibuat, qty yang dihasilkan, bahan baku yang dipakai (per item + qty)
3. Submit:
   - Per bahan baku: create `StockAdjustment` type `subtract` (deduct bahan baku)
   - Untuk produk jadi: create `InventoryItem` baru atau update `current_quantity` item existing + create `StockAdjustment` type `add` dengan `adjustment_type = 'production_entry'`
4. Semua adjustment di-group via `batch_id` (kolom sudah ada dari Fase 4 Plan A)

**Controller**: `SimpleProductionController` (resource, tidak pakai `production_orders`)

**Form fields**:
- Tanggal produksi
- Pilih inventory item (produk jadi) atau buat baru inline
- Qty dihasilkan
- Catatan / batch number
- Section bahan baku: multi-row (material + qty pakai)

**Migrasi**: tidak perlu — `adjustment_type = 'production_entry'` adalah nilai string baru di kolom existing.

#### Fase 4: Permission Seeder ✅ SELESAI

Role `homemade_admin`, `homemade_staff`:
- `material.view`, `material.edit`
- `pattern.view` (resep)
- `inventory.view`, `inventory.edit`
- `sales.view`, `sales.edit`
- `report.view`
- Permission baru: `simple_production.view`, `simple_production.edit`
- TIDAK ada `production.view`, `production.edit`, `purchase.view`

#### Fase 5: Dashboard `homemade` ✅ SELESAI

Fork `DashboardController`: kalau `enable_simple_production && !enable_production_flow`, return dashboard yang tampilkan:
- Stok bahan baku rendah (peringatan)
- Produksi hari ini (dari `stock_adjustments` type `production_entry` hari ini)
- Omzet hari ini / bulan ini
- Top produk terjual
- Value inventory produk jadi

Bisa reuse sebagian besar `RetailDashboard.vue` — tambah section "Stok Bahan Baku".

#### Fase 6: Quick Checkout untuk `homemade` ✅ SELESAI

Reuse `QuickCheckout.vue` existing — sudah tidak terikat kategori `retail` secara hardcode. Verifikasi bahwa `isRetailMode` sudah generalized ke `!enable_production_flow || enable_simple_production`, atau tambah computed terpisah `useQuickCheckout`.

#### Fase 7: Onboarding Default Data ✅ SELESAI

Pas register kategori `homemade`:
1. Auto-create `MaterialType` default ("Bahan Baku", "Kemasan")
2. Auto-create `InventoryLocation` default ("Dapur / Workshop")
3. Auto-create `Customer` default ("Walk-in Customer")
4. Auto-create `InventoryItemCategory` contoh ("Kue Kering", "Minuman", "Kemasan")

#### Fase 8: Test Integrasi ✅ SELESAI

File: `tests/Feature/Integration/HomemadeWorkflowTest.php`

Cover:
1. Register tenant `homemade` → sidebar tampil Material, Resep, Catatan Produksi; tidak tampil Production Order, Kontraktor
2. Beli bahan baku (`material_receipt`) → stok bahan baku bertambah
3. Catat produksi sederhana → bahan baku berkurang, produk jadi bertambah di inventory
4. Quick checkout → stok produk jadi berkurang, SO `completed`

### Estimasi Effort

| Fase | Effort | Blocker |
|---|---|---|
| 1. Config `homemade` | 0.5 hari | - |
| 2. Sidebar filtering generalisasi | 0.5 hari | Fase 1 |
| 3. Simple Production Controller + UI | 3 hari | Fase 1; batch_id dari Plan A sudah ada |
| 4. Permission seeder | 0.5 hari | Fase 1 |
| 5. Dashboard homemade | 1 hari | Fase 2 |
| 6. Quick Checkout generalisasi | 0.5 hari | Fase 2 |
| 7. Onboarding data | 0.5 hari | Fase 1 |
| 8. Test integrasi | 1.5 hari | Semua fase selesai |
| **Total** | **8 hari** + 1 hari QA | - |

### Acceptance Criteria

- Tenant baru pilih "Produksi Rumahan" → sidebar: Dashboard, Bahan Baku, Resep (opsional), Catatan Produksi, Inventory Produk Jadi, Penjualan (Quick Checkout), Laporan, Pengaturan. Tidak ada: Production Order, Kontraktor, Pembelian Produk Jadi.
- Workflow: beli bahan baku → catat produksi (bahan baku berkurang, produk jadi masuk inventory) → quick checkout (produk jadi berkurang, transaksi tersimpan).
- Tenant `food`/`craft`/`garment`/`cosmetic`/`retail` existing: tidak ada perubahan visible.
- Tidak ada migrasi yang menyentuh tabel yang dipakai kategori lain.
- Test `HomemadeWorkflowTest.php` hijau.

### Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| `isRetailMode` hardcoded di beberapa tempat, tidak cover `homemade` | Grep `isRetailMode` sebelum implementasi; ganti ke logika generik `!enable_production_flow` |
| Simple Production deduct bahan baku: race condition kalau qty tidak cukup | Validasi di controller sebelum create adjustments; wrap dalam DB transaction |
| User `homemade` bingung perbedaan "Resep" vs "Catatan Produksi" | Copy UI: Resep = template (opsional), Catatan Produksi = actual entry. Onboarding tooltip. |
| `stock_adjustments` makin overloaded (purchase + production_entry + correction) | Filter ketat di setiap UI berdasarkan `adjustment_type`; tidak campur di list umum |

### Out of Scope

- Perhitungan HPP otomatis per produk (butuh cost allocation yang kompleks).
- Waste/scrap tracking dari produksi.
- Multi-batch produksi paralel.
- Perencanaan produksi (MRP lite).

### Catatan Post-Implementasi (Review Mei 2026)

**Bug yang ditemukan dan diperbaiki saat review:**
1. `Sidebar.vue:34` — `rules` tidak di-destructure dari `useBusinessContext()` → runtime error saat akses `rules.value.enable_simple_production`. Fix: tambah `rules` ke destructure.
2. `Sidebar.vue` — Quick Checkout muncul dua kali untuk homemade (top-level "Penjualan" + child di Sales Order section). Fix: kondisi child berubah ke `!retail && !hasSimpleProduction`; nama section Sales jadi "Riwayat Penjualan" untuk homemade.

**Deviasi dari plan (intentional):**
- Role RBAC `homemade_admin`/`homemade_staff` tidak dibuat di PermissionSeeder. Konsisten dengan retail yang juga tidak punya role terpisah — sistem pakai `users.role = 'admin'/'manager'/'staff'` untuk simple role check.
- `SimpleProductionController::store()` deduct bahan baku via `PreparationOrder` (reuse FIFO logic dari service), bukan langsung `StockAdjustment`. Ini lebih konsisten dengan alur existing tapi menambah `preparation_orders` record tersembunyi.
- `show()` match PrepOrder via `notes LIKE` — fragile kalau ada banyak produksi di hari sama. Low priority tapi perlu refactor ke simpan `preparation_order_id` di `StockAdjustment` atau metadata.
- `customers.code` per-tenant unique diperbaiki langsung di migration original (bukan via alter migration terpisah) — konsisten dengan fresh install, tidak backward compatible untuk DB yang sudah running.
- Demo tenant homemade pakai `subscription_plan: 'trial'` (bukan PRO).
