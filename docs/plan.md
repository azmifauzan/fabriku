# Enhancement Plans

> **Status (Juni 2026)**: Plan A (Retail) dan Plan B (Homemade) **selesai penuh dan sudah dipindah** ke `docs/current-status.md` (lihat bagian "Catatan Implementasi Retail & Homemade"). Dokumen ini sekarang berisi satu plan aktif: **Plan C — kategori `service` (UMKM bidang jasa)**.

---

## Plan C: Kategori `service` — UMKM Bidang Jasa (BELUM DIMULAI)

### Latar Belakang

**Target user**: UMKM yang menjual jasa/layanan, bukan (atau tidak hanya) produk fisik — bengkel motor/mobil, salon, barbershop, laundry, service elektronik, jahit permak. Karakteristik:

- Pendapatan utama dari **layanan** dengan harga per jenis layanan (cukur, creambath, ganti oli, servis ringan, cuci kiloan).
- Sebagian punya **produk fisik pendamping**: bengkel jual sparepart + oli, salon jual shampoo/vitamin — pola beli-jual ini identik dengan kategori `retail` (purchase receipt → stok → jual).
- Tidak ada bahan baku/produksi — modul material, pattern, preparation, production tidak relevan.
- Transaksi dominan walk-in → **Quick Checkout** adalah UI kasir utama.

**Keputusan: kategori baru `service`** — konsisten dengan keputusan `retail`/`homemade` (kategori baru, bukan opsi dalam kategori existing). Secara fungsional `service` = **superset retail**: semua fitur retail (inventory produk jadi, purchase receipt, quick checkout) + satu konsep baru: **Katalog Layanan**.

### Perbedaan vs Kategori Lain

| Fitur | retail | homemade | **service** | full-flow (garment dll) |
|---|---|---|---|---|
| Bahan baku (Materials) | ❌ | ✅ | ❌ | ✅ |
| Production / Preparation Order | ❌ | ❌ | ❌ | ✅ |
| Simple Production | ❌ | ✅ | ❌ | ❌ |
| Inventory produk fisik | ✅ | ✅ | ✅ (sparepart/produk jual) | ✅ |
| Purchase Receipt | ✅ | ❌ | ✅ | ❌ |
| **Katalog Layanan (Services)** | ❌ | ❌ | ✅ **baru** | ❌ |
| Quick Checkout POS | ✅ | ✅ | ✅ (layanan + produk dalam satu cart) | ❌ |

### Blocker Teknis Utama (WAJIB dibaca sebelum implementasi)

1. **`sales_order_items.inventory_item_id` adalah NOT NULL + FK cascade** (`2026_01_07_000001_create_sales_tables.php:50`). Line item layanan tidak punya inventory item → kolom harus jadi nullable + tambah `service_id` nullable FK. Migrasi menyentuh tabel yang dipakai SEMUA kategori — harus additive, dan ingat aturan Laravel 12: saat modify kolom, sertakan semua atribut yang sebelumnya didefinisikan.
2. **`SalesOrderObserver` adalah single source of truth untuk stok** (reserve saat confirmed, deduct saat completed, release saat cancelled). Observer harus **skip line item layanan** (`service_id` terisi, `inventory_item_id` null) — layanan tidak punya stok. Jangan tambah manual stock call di controller.
3. Banyak kode existing meng-assume relasi `items.inventoryItem` selalu ada (eager load, invoice print, export, report sales). Audit semua pemakaian sebelum membuat `inventory_item_id` nullable; gunakan `product_name` (sudah ada, nullable) sebagai display name fallback untuk line layanan.

### Prinsip Desain

1. **Additive only** — kategori baru di `config/business.php`; satu tabel baru `services`; alter `sales_order_items` hanya menambah/melonggarkan kolom (nullable), tidak mengubah perilaku kategori lain.
2. **Reuse maksimal** — inventory, purchase receipt, quick checkout, dashboard retail, laporan sudah ada. Fitur benar-benar baru hanya: katalog layanan + dukungan service line di sales order.
3. **UI gating via `rules`** — sama dengan retail/homemade: sidebar + DashboardController baca `rules` dari `useBusinessContext()`, tanpa hardcode kategori.
4. **Validasi line item**: tepat satu dari `inventory_item_id` / `service_id` terisi. Enforce di Form Request (bukan DB check constraint, supaya portable pgsql/mysql).

### Roadmap Implementasi

#### Fase 1: Config Kategori `service`

**File**: `config/business.php`

```php
'service' => [
    'label' => 'Jasa & Layanan',
    'description' => 'UMKM bidang jasa: bengkel, salon, barbershop, laundry, service elektronik',
    'icon' => 'wrench',
    'mode' => 'service',
    'terminology' => [
        'material'         => 'N/A',
        'inventory'        => 'Produk & Sparepart',
        'production'       => 'N/A',
        'production_order' => 'N/A',
        'preparation'      => 'N/A',
        'contractor'       => 'N/A',
        'service'          => 'Layanan',          // key terminologi baru
    ],
    'material_types' => [],
    'material_attributes' => [],
    'rules' => [
        'enable_production_flow'    => false,
        'enable_material_module'    => false,
        'enable_preparation_module' => false,
        'enable_pattern_module'     => false,
        'enable_contractor_module'  => false,
        'enable_simple_production'  => false,
        'enable_inventory_module'   => true,   // sparepart / produk jual
        'enable_sales_module'       => true,
        'enable_purchase_module'    => true,   // beli sparepart/produk seperti retail
        'enable_service_module'     => true,   // flag baru: katalog layanan
        'track_batch_number'        => false,
        'track_expired_date'        => false,
    ],
],
```

Tambah `'service'` ke `enabled_categories`. Tambah computed `isServiceMode` / `hasServiceModule` di `useBusinessContext.ts`.

#### Fase 2: Migrasi

**Tabel baru `services`** (tenant-scoped, pola sama dengan master data lain):

```sql
CREATE TABLE services (
    id BIGSERIAL PRIMARY KEY,
    tenant_id BIGINT NOT NULL REFERENCES tenants(id) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL,             -- auto-generate, unique per tenant
    name VARCHAR(255) NOT NULL,            -- "Cukur Dewasa", "Ganti Oli", "Cuci Kiloan /kg"
    category VARCHAR(100),                 -- grouping bebas: "Potong Rambut", "Servis Ringan"
    description TEXT,
    price NUMERIC(15,2) NOT NULL,
    duration_minutes INTEGER,              -- opsional, estimasi durasi
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP, updated_at TIMESTAMP,
    CONSTRAINT services_tenant_code_unique UNIQUE (tenant_id, code)
);
```

Model `Service`: `TenantScope` global scope + auto-fill `tenant_id` di `booted()` + trait `HasAuditLogs` — ikuti pola model tenant-owned existing. **Unique langsung per-tenant dari awal** (jangan ulangi bug global-unique yang dulu diperbaiki di `2026_04_30_*`/`2026_05_01_*`).

**Alter `sales_order_items`** (satu migrasi):
- `inventory_item_id` → nullable (pertahankan FK + semua atribut lain saat modify).
- Tambah `service_id` BIGINT nullable, FK ke `services(id)` ON DELETE RESTRICT (line layanan di transaksi historis tidak boleh hilang karena layanan dihapus — soft-deactivate via `is_active`).

#### Fase 3: CRUD Katalog Layanan

- `ServiceController` (resource) + Form Requests; FK validation pakai `Rule::exists('services','id')->where('tenant_id', ...)`.
- Permission baru: `service.view`, `service.edit`; route middleware `permission:service.*`.
- Vue pages `resources/js/pages/Services/` (Index, Form) — pola CRUD master data existing; dark mode wajib.
- Sidebar: menu "Layanan" muncul bila `rules.enable_service_module`.
- Regenerate Wayfinder setelah route baru.

#### Fase 4: Service Line di Sales Order + Quick Checkout

- `SalesOrderItem`: tambah relasi `service()`; validasi Form Request "tepat satu dari `inventory_item_id`/`service_id`"; snapshot `product_name` + harga dari service saat create (harga layanan bisa berubah, transaksi historis tidak boleh ikut berubah).
- **`SalesOrderObserver`: skip semua stock transition untuk line dengan `service_id`** — tidak ada reserve/deduct/release.
- `QuickCheckout.vue`: tab/section "Layanan" di samping grid produk; layanan dan produk bisa campur dalam satu cart. Untuk tenant service, tab Layanan jadi default.
- Invoice print + export CSV: tampilkan nama layanan dari `product_name`/relasi service; pastikan tidak error saat `inventoryItem` null.

#### Fase 5: Dashboard `service`

`DashboardController` fork `serviceDashboard()` bila `enable_service_module` (pola sama `retailDashboard()`/`homemadeDashboard()`):
- Omzet hari ini / bulan ini, split **jasa vs produk**.
- Layanan terlaris 30 hari.
- Top produk/sparepart terjual.
- Low stock produk/sparepart (reuse logic retail).

Vue: `ServiceDashboard.vue` — fork ringan dari `RetailDashboard.vue` + section layanan.

#### Fase 6: Laporan Layanan

- `reports.service`: rekap penjualan layanan per periode (qty, omzet per layanan, per kategori layanan) — query `sales_order_items` where `service_id` not null. Export Excel + PDF (pola `PurchaseReport`).
- Laporan sales existing: pastikan line layanan ikut terhitung di omzet (kemungkinan sudah otomatis via `total_amount`, verifikasi query yang join `inventory_items`).
- Sidebar Laporan untuk tenant service: tampilkan Penjualan, Layanan, Pembelian, Inventory; sembunyikan Material + Produksi.

#### Fase 7: Permission Seeder, Onboarding, Demo Tenant

- PermissionSeeder: tambah `service.view`, `service.edit` (tanpa role kategori terpisah — konsisten retail/homemade, pakai `users.role`).
- Onboarding saat register kategori `service`: auto-create `InventoryLocation` default ("Etalase / Gudang"), `Customer` default ("Walk-in Customer"), contoh kategori layanan + 2–3 layanan contoh.
- Demo tenant baru: `admin@bengkelberkah.com` (bengkel motor) — seeder `ServiceTenantSeeder` (pola `HomemadeTenantSeeder`): katalog layanan (ganti oli, servis ringan, tambal ban), sparepart di inventory, beberapa transaksi campur jasa+produk. Daftarkan di `demo:reset`.

#### Fase 8: Test Integrasi

File: `tests/Feature/Integration/ServiceWorkflowTest.php`

Cover:
1. Register tenant `service` → sidebar tampil Layanan, Inventory, Pembelian, Quick Checkout; TIDAK tampil Material, Produksi, Kontraktor, Catatan Produksi.
2. CRUD layanan → code unique per tenant (tenant lain boleh pakai code sama).
3. Purchase receipt sparepart → stok bertambah.
4. Quick checkout campur: 1 layanan + 1 sparepart → SO `completed`; stok sparepart berkurang; **stok TIDAK berkurang/error untuk line layanan**.
5. Laporan layanan → agregat benar; export 200.
6. Regression observer: tenant non-service buat SO normal → reserve/deduct tetap jalan (pastikan skip logic tidak bocor).

Tambah juga unit/feature test khusus observer untuk service line (draft→confirmed→completed→cancelled tidak menyentuh stok).

### Estimasi Effort

| Fase | Effort | Blocker |
|---|---|---|
| 1. Config `service` | 0.5 hari | - |
| 2. Migrasi services + alter sales_order_items | 1 hari | - |
| 3. CRUD Katalog Layanan | 1.5 hari | Fase 1–2 |
| 4. Service line SO + Quick Checkout + Observer | 3 hari | Fase 2; **paling berisiko** |
| 5. Dashboard service | 1 hari | Fase 1 |
| 6. Laporan layanan | 1 hari | Fase 4 |
| 7. Seeder + onboarding + demo tenant | 1 hari | Fase 1–3 |
| 8. Test integrasi | 1.5 hari | Semua |
| **Total** | **10.5 hari** + 1 hari QA | - |

### Acceptance Criteria

- Tenant baru pilih "Jasa & Layanan" → sidebar: Dashboard, Layanan, Produk & Sparepart (inventory), Pembelian, Penjualan (Quick Checkout), Riwayat Penjualan, Laporan, Pengaturan. Tidak ada: Bahan Baku, Pattern/Resep, Persiapan, Production Order, Kontraktor, Catatan Produksi.
- Quick checkout bisa campur layanan + produk dalam satu transaksi; stok hanya berubah untuk produk.
- Harga layanan diubah → transaksi historis tidak berubah (snapshot).
- Tenant kategori lain: tidak ada perubahan visible; semua test existing tetap hijau (terutama SalesOrder observer test).
- `ServiceWorkflowTest.php` hijau.

### Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Nullable `inventory_item_id` memecah kode yang assume relasi selalu ada (print, export, report) | Grep semua pemakaian `inventoryItem`/`inventory_item_id` di SO sebelum migrasi; null-safe access + fallback `product_name` |
| Skip logic observer bocor → stok produk tidak ter-deduct, atau line layanan error | Test observer eksplisit per transisi status, untuk service line dan mixed cart |
| `total_amount` SO query report join `inventory_items` → line layanan hilang dari omzet | Verifikasi semua report query; hitung dari `sales_order_items` langsung, bukan via join inventory |
| Migrasi modify kolom drop atribut (perilaku Laravel 12) | Tulis ulang definisi kolom lengkap di migrasi alter; test migrate fresh pgsql + mysql |
| Scope creep: booking, komisi, work order | Tegas out of scope (lihat bawah); rilis MVP kasir dulu |

### Fase Opsional (setelah MVP, kalau ada demand)

- **Staff assignment per layanan**: kolom `served_by` (FK `staff`) nullable di `sales_order_items` + laporan omzet per staff → dasar perhitungan komisi salon/barbershop.
- **Consumable auto-deduct**: layanan tertentu otomatis deduct produk (ganti oli → deduct 1 botol oli) via mapping service→inventory item.

### Out of Scope

- Booking / appointment / antrian online.
- Perhitungan komisi otomatis (persentase, payroll).
- Work order tracking detail untuk bengkel (status kendaraan: diterima → dikerjakan → selesai → diambil) — status SO existing cukup untuk MVP.
- Data aset pelanggan (kendaraan, riwayat servis per unit motor).
- Paket/membership layanan (voucher 10x cuci).
