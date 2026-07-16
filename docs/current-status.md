# Status Saat Ini

Dokumen ini merangkum kondisi aktual codebase Fabriku per Juli 2026. Diturunkan dari pembacaan langsung kode, migrasi, dan routing — bukan dari roadmap atau rencana.

## Ringkasan Eksekusi

- Stack: Laravel 12.47, PHP 8.4, Inertia v2, Vue 3.5, Tailwind v4, PostgreSQL 16 (atau MySQL 8), Redis 7.
- Frontend type-safety: Laravel Wayfinder (`resources/js/actions/`, `resources/js/routes/`).
- Multi-tenant: tenant isolation lewat `App\Models\Scopes\TenantScope` + auto-fill `tenant_id` di event `creating` model.
- Dua guard auth terpisah: `web` (User tenant) dan `admin` (AdminUser platform).
- Storage upload: disk dikonfigurasi via `config('filesystems.uploads_disk')` (env `UPLOADS_DISK`, default `fabriku_s3`). URL temporary di-cache 25 menit (env `UPLOAD_URL_TTL`).
- AI: integrasi ke OpenAI Chat Completions API tersedia di backend (`App\Services\Assistant\OpenAIService`) namun **tidak aktif di UI** (ChatWidget disembunyikan).
- Telegram: bot dua arah — webhook penerimaan + push notifikasi keluar.
- Tests: 30+ feature test files (Pest 4), 3 browser test files, 0 unit test meaningful.

## Modul Aktif

| Modul | Status | Catatan |
|---|---|---|
| Auth tenant (register, login, verifikasi email, reset password) | aktif | trial 30 hari otomatis pada register |
| Auth admin (login, dashboard, monitoring) | aktif | guard terpisah, password reset reuse notification |
| Tenant management (admin) | aktif | CRUD tenant, suspend/activate |
| User management (admin) | aktif | reset password, manage user lintas tenant |
| RBAC (roles + permissions) | aktif | tabel `roles`, `permissions`, pivot `role_permissions`, `user_roles`; middleware `permission:<slug>`; tenant admin kelola role custom sendiri di `/roles` (`RoleController`, admin-only via `isAdmin()` di controller, bukan `permission:` middleware) — role sistem (`tenant_id` null) read-only dari sisi tenant, hanya admin platform (`/admin/roles`) yang bisa ubah |
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
| Inventory items | aktif | sumber: production / opening_balance / purchase / return; SKU auto-generate per kategori; **multi-rack split**: create/edit form bisa alokasi stock ke beberapa rak sekaligus (`locations[]`, N `inventory_items` row terpisah per rak, tanpa pivot table); split stock item existing lewat "Pindah/Split Stock" (`POST items/{item}/transfer` → `InventoryService::transferStock()`, hanya `available_stock` yang bisa dipindah, `reserved_quantity` tetap di row asal, row asal soft-delete bila kosong total) — tombol tersedia di Show page dan di Edit form (`Form.vue`, embed `TransferStockModal`) |
| Inventory item categories | aktif | klasifikasi item tambahan |
| Stock adjustments | aktif | tipe: opening_balance, correction, damage, lost, found, dst |
| Customers | aktif | CRUD |
| Sales orders | aktif | line items; full status lifecycle `draft→confirmed→processing→shipped→completed`/`cancelled` via state machine `SalesOrder::transitionMap()` + modal Update Status (`PATCH sales-orders/{id}/update-status`); edit form dikunci ke `draft` saja (`canBeEdited()`); cetak invoice + surat jalan; export CSV; **`payment_due_date`**: input jatuh tempo di form, badge Overdue di Index/Show, filter `?payment_status=overdue`; **`shipping_cost`**: input ongkir aktif, masuk `total_amount`; **`invoice_number`**: auto-generate `INV/YYYY/MM/NNNN` saat SO keluar draft (saving hook), manual override tetap bisa; status form dikunci read-only (UI sebelumnya menyesatkan) |
| Catatan pembayaran (payments ledger) | aktif | tabel `payments` (`amount`, `method`, `paid_at`, `note`); `paid_amount` = SUM, `payment_status` derivasi; `PATCH sales-orders/{id}/update-payment` = "tambah baris pembayaran" (`StorePaymentRequest`); cancel order berbayar auto-catat refund (baris negatif, `payment_status='refunded'`); riwayat pembayaran di Show.vue |
| Reports (material, inventory, sales, sales-recap, production) | aktif | export Excel/PDF |
| Dashboard | aktif | KPI bulanan + tren 7 hari + top produk 30 hari + low stock |
| Settings tenant | aktif | data perusahaan, alamat, logo |
| Settings admin (system settings) | aktif | per-tenant overrides untuk `max_staff_per_tenant`, harga membership, dll |
| Staff management | aktif | bikin akun User otomatis, kirim kredensial via email; cek `max_staff_per_tenant`; dropdown role di form staff gabung role sistem + role custom tenant, sorted by name |
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
| **Kategori `service` (Jasa & Layanan)** | aktif | UMKM jasa (bengkel, salon, barbershop, laundry) — katalog layanan + jual produk/sparepart seperti retail |
| Katalog Layanan (Services) | aktif | `ServiceController` (resource tanpa `show`) — CRUD `services` (code unique per tenant); destroy diblokir bila layanan sudah dipakai transaksi (FK `RESTRICT`); permission `service.view/*` |
| Service line di Sales Order | aktif | `sales_order_items.inventory_item_id` jadi nullable + kolom `service_id` (FK restrict); validasi "tepat satu dari produk/layanan" per baris; `SalesOrderObserver` + `SalesOrderItemObserver` skip semua stock transition untuk line layanan |
| Quick Checkout campur jasa + produk | aktif | `QuickCheckout.vue` — grid gabungan produk + layanan (badge JASA, stok unlimited); satu cart bisa campur; snapshot harga di line item; selector staff (`served_by`) per baris layanan |
| Dashboard service | aktif | `ServiceDashboard.vue`; `DashboardController` fork ke `serviceDashboard()` bila `enable_service_module`; KPI omzet + layanan terlaris 30 hari |
| Laporan Layanan | aktif | `ReportController::service()` + `exportService()`; query `sales_order_items` where `service_id`; rekap per layanan + per staff (`served_by`); export Excel (`ServiceReportExport`) + PDF; route `reports.service` + `reports.service.export` |
| Staff Assignment per Layanan | aktif | kolom `sales_order_items.served_by` (FK `staff`, nullOnDelete); diisi via Quick Checkout; dipakai laporan omzet per staff (dasar komisi) |
| Consumable Auto-Deduct | aktif | tabel `service_consumables` (mapping `service` → `inventory_item` + qty); saat layanan terjual via Quick Checkout, stok bahan pendukung auto-deduct; stok divalidasi sebelum transaksi; mapping diatur di form layanan (`ServiceController::syncConsumables`) |

## Struktur Routing Tenant

Stack middleware standar: `auth → verified → tenant → subscription.check → permission:<slug>`.

Permission slug yang terpakai di routes/web.php:
- `material.view`
- `pattern.view`, `preparation.view`
- `production.view`, `production.edit`
- `inventory.view`
- `sales.view`
- `purchase.view`, `purchase.edit` (retail/service)
- `simple_production.view`, `simple_production.create` (homemade only)
- `service.view` (service only — seluruh resource `services` digate satu slug ini)
- `report.view` (mencakup semua laporan termasuk `reports.purchase` untuk retail)

Modul tanpa permission middleware (open untuk semua user tenant ter-verifikasi): staff, customers (via sales.view), settings, subscription, telegram.

## Database (39 tabel)

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
- `2026_06_10_*`: tabel `services` (code unique per tenant) + alter `sales_order_items` (`inventory_item_id` → nullable, tambah `service_id` FK `RESTRICT`); tabel `service_consumables` (mapping service→inventory item, unique `service_id`+`inventory_item_id`) + kolom `sales_order_items.served_by` (FK `staff`, nullOnDelete)

## Konfigurasi Kategori Bisnis

`config/business.php` — 7 kategori aktif: `garment`, `food`, `craft`, `cosmetic`, `retail`, `homemade`, `service`. Default: `garment`. Setiap tenant memilih satu kategori saat register. Terminologi UI (Pattern/Resep, Cutting/Mixing, dll) di-resolve dari `Tenant::getTerminology($key)`.

Gating rules yang dibaca sidebar + DashboardController:
- `retail`: `mode = 'simple'`, `rules.enable_production_flow = false` → sembunyikan produksi, tampilkan Quick Checkout top-level
- `homemade`: `mode = 'homemade'`, `rules.enable_simple_production = true`, `rules.enable_contractor_module = false`, `rules.enable_purchase_module = false` → tampilkan "Catatan Produksi" sederhana, Quick Checkout top-level, sembunyikan Production Order + Kontraktor + Pembelian Produk Jadi
- `service`: `mode = 'service'`, `rules.enable_service_module = true`, `enable_material_module = false`, `enable_purchase_module = true` → tampilkan menu Layanan + Pembelian + Quick Checkout; sembunyikan seluruh alur material/produksi. **Penting**: semua kategori lain punya `enable_service_module = false` eksplisit karena `isModuleEnabled()` di frontend men-default `true` bila key tidak ada.

## Catatan Implementasi Service/Jasa (dipindah dari plan.md, selesai Juni 2026)

Plan C (kategori `service`) selesai penuh: config kategori, tabel `services` + `service_consumables`, CRUD katalog layanan (dengan mapping consumable), service line di sales order + quick checkout, staff assignment (`served_by`), consumable auto-deduct, dashboard service, laporan layanan, onboarding register, demo tenant `admin@bengkel.com`, test `ServiceWorkflowTest.php` (9 test) + `ServiceEnhancementsTest.php` (7 test).

Catatan teknis yang masih relevan:

- **Observer skip layanan**: `SalesOrderObserver::reserveStock/deductStock/releaseReservedStock` dan `SalesOrderItemObserver` hanya menyentuh stok bila `inventory_item_id` terisi. Line layanan (`service_id`) tidak punya efek stok sama sekali. Jangan tambah stock call untuk layanan.
- **Consumable auto-deduct ≠ observer**: deduct bahan pendukung layanan dilakukan eksplisit di `SalesOrderController::quickCheckoutStore` (dalam DB transaction, `lockForUpdate`), bukan di observer. Validasi stok consumable di-akumulasi per inventory item di `after`-hook validator sebelum transaksi. Sales order manual (non-quick-checkout) belum trigger consumable deduct.
- **Validasi line item**: tepat satu dari `inventory_item_id`/`service_id` per baris — enforce di `StoreSalesOrderRequest`/`UpdateSalesOrderRequest::withValidator()` dan validator inline `quickCheckoutStore` (bukan DB check constraint). Semua FK validation tenant-scoped (`Rule::exists()->where('tenant_id', ...)`), termasuk `served_by` → `staff`.
- **Destroy layanan**: FK `service_id` di `sales_order_items` adalah `RESTRICT`. `ServiceController::destroy` cek pemakaian dulu dan redirect dengan flash `error` (sarankan nonaktifkan) — tidak pernah lempar QueryException. `service_consumables.service_id` cascade saat layanan dihapus.
- **UI penjualan**: `Show.vue` menampilkan nama layanan + staff (`served_by.name`); `Print.vue`, `DeliveryOrder.vue` fallback ke `item.service?.name`/`code` bila `inventory_item` null. Halaman Create/Edit SO manual belum support pilih layanan — layanan dijual lewat Quick Checkout (keputusan MVP).
- **Laporan layanan**: `reports.service` (rekap per layanan + per staff), export Excel/PDF. Laporan sales umum juga sudah menampilkan line layanan.
- Demo tenant service: `admin@bengkel.com` (Bengkel Motor Maju Jaya, trial) — `ServiceTenantSeeder` seed 3 layanan + 3 sparepart + 2 staff montir + 1 consumable mapping (Ganti Oli → 1 Oli). Terdaftar di `demo:reset`; reset hapus `services` (cascade ke `service_consumables`) + `staff` per tenant.

## Catatan Implementasi Retail & Homemade (dipindah dari plan.md, selesai Mei 2026)

Plan A (Retail: Purchase Receipt, Quick Checkout, Dashboard Retail, Purchase Report) dan Plan B (kategori `homemade`: Simple Production, Dashboard Homemade, onboarding default) selesai penuh. Deviasi dari plan yang disengaja dan catatan teknis yang masih relevan:

- Role RBAC `homemade_admin`/`homemade_staff` tidak dibuat di PermissionSeeder — konsisten dengan retail; sistem pakai `users.role = 'admin'/'manager'/'staff'` untuk simple role check.
- `SimpleProductionController::store()` deduct bahan baku via `PreparationOrder` (reuse FIFO logic dari service), bukan langsung `StockAdjustment` — menambah record `preparation_orders` tersembunyikan per catatan produksi.
- **Known issue**: `SimpleProductionController::show()` mencocokkan PreparationOrder via `notes LIKE` — fragile bila ada banyak produksi di hari sama. Refactor yang disarankan: simpan `preparation_order_id` di `StockAdjustment` atau metadata.
- `customers.code` unique per-tenant diperbaiki langsung di migration original + alter migration untuk DB berjalan (`2026_05_*` drop `customers_code_unique` global).
- Demo tenant homemade pakai `subscription_plan: 'trial'` (bukan PRO).

## Test Coverage

- `tests/Feature/`: 30+ file, dominan happy-path CRUD + observer test untuk SalesOrder.
- `tests/Feature/Integration/`: 9 file user-journey end-to-end per modul + multi-kategori; termasuk `RetailWorkflowTest.php` (purchase receipt → purchase report → quick checkout), `HomemadeWorkflowTest.php` (material receipt → simple production → quick checkout), dan `ServiceWorkflowTest.php` (CRUD layanan → quick checkout campur jasa+produk → cross-tenant guard → delete guard → onboarding register). `ServiceEnhancementsTest.php` cover laporan layanan, staff assignment (`served_by`), dan consumable auto-deduct.
- **Suite hijau penuh**: 60 passed, 1 skipped (Juni 2026). ±100 test feature lama yang sempat gagal di sqlite sudah diperbaiki (test rot, bukan bug produk): default factory `UserFactory` jadi `role=admin` (route ber-permission), `MaterialTypeFactory` di-scope tenant, migration `customers_code_unique` pakai `Schema::getIndexes()` (driver-agnostik, bukan `information_schema`), payload integration test diselaraskan ke kontrak API/observer terkini, atribut master data usang (`category`/`product_type`/`planned_quantity`) dibuang. Dua bug produk ikut diperbaiki saat proses ini: tenant suspended (`is_active=false`) kini diblokir di `EnsureTenantContext`; double-reservation stok di `SalesOrderController::update` (bulk delete item lewati observer) → ganti `->get()->each->delete()` + reorder agar observer fire sekali.
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

7 tenant demo:
- `admin@konveksi.com` (garment)
- `admin@kuemama.com` (food)
- `admin@crafty.com` (craft)
- `admin@glowbeauty.com` (cosmetic)
- `admin@tokoserbaada.com` (retail)
- `admin@homemade.com` (homemade — Dapur Coklat Rumahan, trial plan)
- `admin@bengkel.com` (service — Bengkel Motor Maju Jaya, trial plan)

Admin platform: `admin@fabriku.com`. Semua password `password`. Data reset tiap jam.

## Yang BELUM Ada

- Barcode scanning untuk material (hanya inventory yang punya QR).
- Multi-warehouse (hanya `inventory_locations` per tenant tunggal).
- Payment gateway terintegrasi (Midtrans/Xendit). Subscription payment masih manual upload bukti.
- Mobile app native.
- Shipping integration (JNE/JNT/SiCepat).
- Multi-bahasa (semua hardcoded Bahasa Indonesia).
- Backup/restore otomatis per tenant.
