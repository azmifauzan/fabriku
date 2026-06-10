# Status Saat Ini

Dokumen ini merangkum kondisi aktual codebase Fabriku per Juni 2026. Diturunkan dari pembacaan langsung kode, migrasi, dan routing — bukan dari roadmap atau rencana.

## Ringkasan Eksekusi

- Stack: Laravel 12.47, PHP 8.4, Inertia v2, Vue 3.5, Tailwind v4, PostgreSQL 16 (atau MySQL 8), Redis 7.
- Frontend type-safety: Laravel Wayfinder (`resources/js/actions/`, `resources/js/routes/`).
- Multi-tenant: tenant isolation lewat `App\Models\Scopes\TenantScope` + auto-fill `tenant_id` di event `creating` model.
- Dua guard auth terpisah: `web` (User tenant) dan `admin` (AdminUser platform).
- Storage upload: disk dikonfigurasi via `config('filesystems.uploads_disk')` (env `UPLOADS_DISK`, default `fabriku_s3`). URL temporary di-cache 25 menit (env `UPLOAD_URL_TTL`).
- AI: integrasi ke OpenAI Chat Completions API tersedia di backend (`App\Services\Assistant\OpenAIService`) namun **tidak aktif di UI** (ChatWidget disembunyikan).
- Telegram: bot dua arah — webhook penerimaan + push notifikasi keluar.
- Tests: 30+ feature test files (Pest 4), 1 browser test, 0 unit test meaningful.

## Modul Aktif

| Modul | Status | Catatan |
|---|---|---|
| Auth tenant (register, login, verifikasi email, reset password) | aktif | trial 30 hari otomatis pada register |
| Auth admin (login, dashboard, monitoring) | aktif | guard terpisah, password reset reuse notification |
| Tenant management (admin) | aktif | CRUD tenant, suspend/activate |
| User management (admin) | aktif | reset password, manage user lintas tenant |
| RBAC (roles + permissions) | aktif | tabel `roles`, `permissions`, pivot `role_permissions`, `user_roles`; middleware `permission:<slug>` |
| Audit log | aktif | trait `HasAuditLogs`, polymorphic `audit_logs` |
| Subscription payments | aktif | upload bukti, approval/reject oleh admin |
| Material types | aktif | CRUD master data |
| Materials | aktif | CRUD, stock dihitung dari `material_receipts` (lihat command `material:recalculate-stock`) |
| Material receipts | aktif | atribut dinamis per kategori bisnis |
| Pattern / Recipe | aktif | CRUD, dipakai opsional di preparation order |
| Preparation orders | aktif | manual material usage, auto-deduct stock saat completed |
| Contractors | aktif | CRUD, dipakai di production order eksternal |
| Production orders | aktif | status: draft/pending/in_progress/completed; actions: send/start/mark-complete |
| Inventory locations | aktif | QR code generate + print |
| Inventory items | aktif | sumber: production / opening_balance / purchase / return; SKU auto-generate per kategori |
| Inventory item categories | aktif | klasifikasi item tambahan |
| Stock adjustments | aktif | tipe: opening_balance, correction, damage, lost, found, dst |
| Customers | aktif | CRUD |
| Sales orders | aktif | line items, payment tracking, status sampai shipped/completed; cetak invoice + surat jalan; export CSV |
| Reports (material, inventory, sales, sales-recap, production) | aktif | export Excel/PDF |
| Dashboard | aktif | KPI bulanan + tren 7 hari + top produk 30 hari + low stock |
| Settings tenant | aktif | data perusahaan, alamat, logo |
| Settings admin (system settings) | aktif | per-tenant overrides untuk `max_staff_per_tenant`, harga membership, dll |
| Staff management | aktif | bikin akun User otomatis, kirim kredensial via email; cek `max_staff_per_tenant` |
| Assistant (web chat) | **disembunyikan** | Backend tersedia, ChatWidget dan routes `/assistant/*` ada di kode tapi tidak ditampilkan ke user |
| Telegram bot (webhook) | aktif | command `/start /help /status /disconnect`, connect via 8-char token; forward ke assistant dinonaktifkan di UI |
| Telegram notifikasi keluar | aktif | new user registered, payment uploaded |
| Email system | aktif | verifikasi email, reset password, welcome, trial reminder (7/3/1 hari) |
| Demo data reset | aktif | scheduler `hourly` jalan `demo:reset` |
| **Mode Retail (kategori `retail`)** | aktif | sidebar di-filter via `isRetailMode`; modul material/produksi disembunyikan untuk tenant retail |
| Purchase receipts (retail) | aktif | `PurchaseReceiptController`; reuse `stock_adjustments` dengan `batch_id`, `supplier_name`, `purchase_invoice`; permission `purchase.view/edit` |
| Quick Checkout / POS (retail + homemade) | aktif | `QuickCheckout.vue` — grid produk + cart, default Walk-in Customer, SO langsung `completed`; muncul di sidebar bila `isRetailMode || enable_simple_production` |
| Dashboard retail | aktif | `RetailDashboard.vue`; `DashboardController` fork ke `retailDashboard()` bila `!enable_production_flow` |
| **Purchase Report (retail)** | aktif | `ReportController::purchase()`; query `StockAdjustment` type purchase, group per `batch_id`; tabel + drill-down per batch; export Excel + PDF; route `reports.purchase` + `reports.purchase.export` |
| **Kategori `homemade` (Produksi Rumahan)** | aktif | UMKM produksi sederhana — pakai material + input produk jadi langsung tanpa Production Order |
| Simple Production (Catatan Produksi) | aktif | `SimpleProductionController` — form produksi sederhana; deduct bahan baku via `PreparationOrder`; add produk jadi ke inventory via `StockAdjustment TYPE_PRODUCTION_ENTRY`; semua adjustment di-group `batch_id`; route: `simple-production.*` permission `simple_production.view/create` |
| Dashboard homemade | aktif | `HomemadeDashboard.vue`; `DashboardController` fork ke `homemadeDashboard()` bila `enable_simple_production` |

## Struktur Routing Tenant

Stack middleware standar: `auth → verified → tenant → subscription.check → permission:<slug>`.

Permission slug yang terpakai di routes/web.php:
- `material.view`
- `pattern.view`, `preparation.view`
- `production.view`, `production.edit`
- `inventory.view`
- `sales.view`
- `purchase.view`, `purchase.edit` (retail only)
- `simple_production.view`, `simple_production.create` (homemade only)
- `report.view` (mencakup semua laporan termasuk `reports.purchase` untuk retail)

Modul tanpa permission middleware (open untuk semua user tenant ter-verifikasi): staff, customers (via sales.view), settings, subscription, telegram.

## Database (37 tabel)

Migrasi tunggal per modul:
- `tenants`, `users`, `cache`, `jobs`
- `master_data_tables` (material_types, dst)
- `materials_tables` (materials, material_receipts, material_attributes)
- `patterns_and_preparations_tables`
- `production_tables`
- `inventory_tables` (inventory_locations, inventory_items)
- `sales_tables` (sales_orders, sales_order_items)
- `admin_users`, `roles`, `permissions`, `role_permissions`, `user_roles`
- `system_settings`, `audit_logs`, `subscription_payments`
- `stock_adjustments` + patch `2026_05_23_*` tambah `batch_id`, `supplier_name`, `purchase_invoice` (nullable, additive)
- `assistant_tables` (assistant_conversations, assistant_messages, assistant_usages, assistant_pending_actions) — *tabel tetap ada, fitur UI disembunyikan*
- `email_logs`
- `inventory_item_categories` + add `category_id` ke inventory_items
- Patch migrations: tambah staff_user, tambah `shipped` status SO, fix unique constraints jadi per-tenant (staff.code, contractors.code, sales_orders.order_number, inventory_items.sku, inventory_locations.code), tambah kolom audit log

## Konfigurasi Kategori Bisnis

`config/business.php` — 6 kategori aktif: `garment`, `food`, `craft`, `cosmetic`, `retail`, `homemade`. Default: `garment`. Setiap tenant memilih satu kategori saat register. Terminologi UI (Pattern/Resep, Cutting/Mixing, dll) di-resolve dari `Tenant::getTerminology($key)`.

Gating rules yang dibaca sidebar + DashboardController:
- `retail`: `mode = 'simple'`, `rules.enable_production_flow = false` → sembunyikan produksi, tampilkan Quick Checkout top-level
- `homemade`: `mode = 'homemade'`, `rules.enable_simple_production = true`, `rules.enable_contractor_module = false`, `rules.enable_purchase_module = false` → tampilkan "Catatan Produksi" sederhana, Quick Checkout top-level, sembunyikan Production Order + Kontraktor + Pembelian Produk Jadi

## Catatan Implementasi Retail & Homemade (dipindah dari plan.md, selesai Mei 2026)

Plan A (Retail: Purchase Receipt, Quick Checkout, Dashboard Retail, Purchase Report) dan Plan B (kategori `homemade`: Simple Production, Dashboard Homemade, onboarding default) selesai penuh. Deviasi dari plan yang disengaja dan catatan teknis yang masih relevan:

- Role RBAC `homemade_admin`/`homemade_staff` tidak dibuat di PermissionSeeder — konsisten dengan retail; sistem pakai `users.role = 'admin'/'manager'/'staff'` untuk simple role check.
- `SimpleProductionController::store()` deduct bahan baku via `PreparationOrder` (reuse FIFO logic dari service), bukan langsung `StockAdjustment` — menambah record `preparation_orders` tersembunyikan per catatan produksi.
- **Known issue**: `SimpleProductionController::show()` mencocokkan PreparationOrder via `notes LIKE` — fragile bila ada banyak produksi di hari sama. Refactor yang disarankan: simpan `preparation_order_id` di `StockAdjustment` atau metadata.
- `customers.code` unique per-tenant diperbaiki langsung di migration original + alter migration untuk DB berjalan (`2026_05_*` drop `customers_code_unique` global).
- Demo tenant homemade pakai `subscription_plan: 'trial'` (bukan PRO).

## Test Coverage

- `tests/Feature/`: 30+ file, dominan happy-path CRUD + observer test untuk SalesOrder.
- `tests/Feature/Integration/`: 8 file user-journey end-to-end per modul + multi-kategori; termasuk `RetailWorkflowTest.php` (purchase receipt → purchase report → quick checkout) dan `HomemadeWorkflowTest.php` (material receipt → simple production → quick checkout).
- `tests/Browser/ApplicationFlowTest.php`: 1 file browser test (Pest 4).
- `tests/Unit/`: hanya `ExampleTest.php` (kosong) — tidak ada unit test domain.
- Setup: `RefreshDatabase` otomatis untuk Feature via `tests/Pest.php`.

## Gap Operasional

- File `.github/COLUMN-NAMING-CONVENTIONS.md` sudah dihapus (usang dan menyesatkan). Schema aktual ada di CLAUDE.md dan migrasi.
- Tidak ada CI/CD workflow di `.github/workflows/`.
- Tidak ada pre-commit hook standar.
- Tidak ada feature flag — semua modul on/off lewat permission saja.
- Demo reset sudah di-guard dengan `app()->environment(['local', 'staging'])` di `routes/console.php`.

## Lingkungan Demo

6 tenant demo:
- `admin@konveksi.com` (garment)
- `admin@kuemama.com` (food)
- `admin@crafty.com` (craft)
- `admin@glowbeauty.com` (cosmetic)
- `admin@tokoserbaada.com` (retail)
- `admin@homemade.com` (homemade — Dapur Coklat Rumahan, trial plan)

Admin platform: `admin@fabriku.com`. Semua password `password`. Data reset tiap jam.

## Yang BELUM Ada

- Barcode scanning untuk material (hanya inventory yang punya QR).
- Multi-warehouse (hanya `inventory_locations` per tenant tunggal).
- Payment gateway terintegrasi (Midtrans/Xendit). Subscription payment masih manual upload bukti.
- Mobile app native.
- Shipping integration (JNE/JNT/SiCepat).
- Multi-bahasa (semua hardcoded Bahasa Indonesia).
- Backup/restore otomatis per tenant.
