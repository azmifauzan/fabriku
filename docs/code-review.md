# Code Review

Hasil pembacaan langsung kode per Mei 2026. Severity: **CRITICAL** (data corruption / security), **HIGH** (bug fungsional), **MEDIUM** (maintainability / risiko), **LOW** (nit / konsistensi).

Format: `path:line — severity — masalah — perbaikan singkat`.

Baris bertanda ✅ **RESOLVED** sudah diperbaiki di codebase.

---

## CRITICAL

### ✅ RESOLVED — Cross-tenant data leak lewat validation `exists`
Semua Form Request (Store/Update Sales, Inventory, Material, Production, Preparation) sudah pakai `Rule::exists('table','id')->where('tenant_id', $tenantId)`. Tidak ada lagi raw `exists:table,id` di `app/Http/Requests/`.

### ✅ RESOLVED — `FormRequest::authorize()` selalu `true`
Semua Store/Update Request sudah return `return $this->user() !== null;`.

### ✅ RESOLVED — Double-reservation stok pada SalesOrder
Controller manual `reserveStock`/`releaseReservedStock` di `store()`, `update()`, `destroy()` sudah dihapus. Observer adalah single source of truth:
- create → status `draft` (no reserve)
- status `draft→confirmed/processing` → observer reserve
- status `confirmed/processing/shipped→completed` → observer deduct
- status `confirmed/processing/shipped→cancelled` → observer release
- soft-delete → observer release (jika confirmed/processing/shipped)
- Destroy: `canBeCancelled()` guard, kemudian `$salesOrder->delete()` (observer handle release).

### ✅ RESOLVED — Order bisa dibuat dengan `status=confirmed` lewat client
`SalesOrderController::store` hardcode `status = 'draft'` dan `StoreSalesOrderRequest::prepareForValidation()` default ke `'draft'`.

### ✅ RESOLVED — Kondisi race + integritas stok
`InventoryItem::reserveStock()`, `releaseReservedStock()`, `deductStock()` sudah punya `DB::transaction` + `self::lockForUpdate()->find($this->id)` di dalam method itu sendiri. `Illuminate\Support\Facades\DB` sudah di-import di model.

### ✅ RESOLVED — `SalesOrderController::update` tidak enforce `canBeEdited()`
`canBeEdited()` sekarang strict `status === 'draft'` (sebelumnya juga true untuk `confirmed` dan `completed` belum lunas). Guard `if (! $salesOrder->canBeEdited()) { abort(403, ...); }` tetap di awal `update()`.

### ✅ RESOLVED — Order `processing`/`shipped` nyangkut, observer tidak handle `shipped`
`processing`/`shipped` mentok tanpa jalur lanjut (lihat `docs/plan.md` CRITICAL #1-3, Juni 2026). Fix:
- `SalesOrder::transitionMap()` (state machine tunggal, dipakai `canTransitionTo()` + `allowedTransitions()`): `draft→{confirmed,processing,cancelled}`, `confirmed→{processing,shipped,completed,cancelled}`, `processing→{shipped,completed,cancelled}`, `shipped→{completed,cancelled}`.
- `PATCH sales-orders/{id}/update-status` (`UpdateStatusRequest`, permission `sales.edit`) — 422 bila transisi di luar map. `resi_number`+`shipped_date` diisi saat target `shipped`; `completed_date` saat target `completed`.
- `StatusUpdateModal.vue` di Index & Show (frontend mirror transition map untuk UX, backend tetap source of truth).
- `SalesOrderObserver` — `'shipped'` ditambahkan ke ketiga `in_array` check (`updated`/`deleted`/`forceDeleting`) sehingga `shipped→completed` deduct & `shipped→cancelled`/hapus order `shipped` release reserved stock.
- Test: `tests/Feature/SalesOrderUpdateStatusTest.php`.

### ✅ RESOLVED — Telegram webhook tanpa rate limit
`routes/api.php` — ditambah `->middleware('throttle:60,1')`. Log level debug diturunkan ke info dengan payload ringkas.

---

## HIGH

### ✅ RESOLVED — Schema documentation drift
`.github/COLUMN-NAMING-CONVENTIONS.md` sudah dihapus (tidak lagi di repo). CLAUDE.md memuat schema aktual sebagai referensi.

### ✅ RESOLVED — Duplicate key di array `stats`
`SalesOrderController::index` — duplikat `'pending_payment'` sudah dihapus.

### ✅ RESOLVED — Storage disk hardcoded `fabriku_s3`
Semua referensi literal `'fabriku_s3'` diganti dengan `config('filesystems.uploads_disk', 'fabriku_s3')`. Disk name bisa di-override lewat env `UPLOADS_DISK`. File terimpak: `InventoryItem`, `Material`, `MaterialReceipt`, `SubscriptionPayment`, `InventoryItemController`, `MaterialController`, `MaterialReceiptController`, `SubscriptionController`.

### ✅ RESOLVED — `temporaryUrl()` di S3 dipanggil tiap kali accessor diakses
`InventoryItem::getImageUrlAttribute` + `Material::getImageUrlAttribute` + `MaterialReceipt::getImageUrlAttribute` + `SubscriptionPayment::getProofUrlAttribute` — URL sekarang di-cache dengan `Cache::remember()` per `md5(image_path)` selama TTL - 1 menit. TTL dikonfigurasi lewat `config('filesystems.url_ttl_minutes')` (default 25 menit, env `UPLOAD_URL_TTL`).

### ✅ RESOLVED — `Role` tanpa TenantScope
`Role` sudah punya `static::addGlobalScope(new TenantScope)` + `creating` listener di `booted()`.

### ✅ RESOLVED — `Customer` model tanpa auto-fill `tenant_id`
`Customer::booted()` sudah punya `creating` listener: `$customer->tenant_id = auth()->user()->tenant_id`.

### ✅ RESOLVED — Massa data demo reset tiap jam di production
`routes/console.php` sudah bungkus `demo:reset` dengan `if (app()->environment(['local', 'staging']))`.

### Observer mutating tanpa transaction yang membungkus seluruh update
`SalesOrderObserver::updated` pakai `DB::transaction` inner. Nested transaction Laravel di-handle via savepoint — OK secara teknis. Risiko residual: queue job yang ubah status tanpa outer transaction bisa partial-fail.

### Email log dapat membocorkan PII di plain text
`email_logs` simpan subject/body tanpa redaksi/retention. Bisa simpan link reset password.
**Fix**: skip log body untuk template yang mengandung token, atau encrypt body.

---

## MEDIUM

### ✅ RESOLVED — Inkonsistensi pattern auto-fill `tenant_id`
`InventoryItem::booted()` sekarang punya `auth()->check()` guard di listener `creating`, seragam dengan `Material` dan model lain.

### Accessor alias berlapis di `InventoryItem`
`current_stock`, `reserved_stock`, `inventory_location_id`, `name`, `pattern`, `batch_number`, `expiry_date` di `$appends` — duplikasi surface, payload membengkak.
**Fix**: pilih nama canonical, hapus alias. Frontend update.

### ✅ RESOLVED — `DB::raw` dan `whereRaw` di controller (column-rename risk)
`DashboardController` — `whereRaw('stock_quantity <= min_stock')` diganti dengan `whereColumn('stock_quantity', '<=', 'min_stock')` di semua tempat yang relevan.

### ✅ RESOLVED — Sub-query N+1 di Dashboard
`DashboardController::index` — `materialStockSummary` dan `inventorySummary` sekarang dihitung lewat DB agregasi, bukan load semua rows ke PHP. `topMaterialsByValue` pakai `orderByDesc(DB::raw(...))` langsung di DB.

### ✅ RESOLVED — `OpenAIService` default model `gpt-5-nano`
`app/Services/Assistant/OpenAIService.php` — default diganti ke `gpt-4o-mini`. Env `OPENAI_MODEL` tetap bisa override.

### ✅ RESOLVED — `OpenAIService::chat` selalu sertakan `temperature` & `max_tokens`
Parameter sekarang conditional: tidak dikirim jika model match pattern `o1|o3|o4|gpt-5` (reasoning model family).

### ✅ RESOLVED — Tidak ada test untuk observer behavior baru pada force-delete
`tests/Feature/SalesOrderTest.php` — ditambah 2 test:
- "releases reserved stock when force deleting a confirmed order not yet soft-deleted"
- "does not double-release reserved stock when force deleting an already soft-deleted order"

### `assistant_messages` log tanpa truncation
Full content + tool_calls JSON tiap pesan. Tidak ada retention policy.

### ✅ RESOLVED — Wayfinder TS output tidak di-gitignore
`resources/js/actions/`, `resources/js/routes/`, `resources/js/wayfinder` sudah ada di `.gitignore`.

### ✅ RESOLVED — Logging payload Telegram di level `debug`
`TelegramWebhookController` — semua `Log::debug(...)` dengan full payload user diganti ke `Log::info(...)` dengan payload minimal (update_id, type, chat_id, from username).

### `subscription.check` tidak skip GET assistant route
`assistant.message` adalah POST. User expired bisa GET history. Inconsistent dengan pesan UI.
Didokumentasikan di `CheckSubscriptionStatus.php` sebagai keputusan desain yang disengaja: GET diizinkan untuk semua route, `EnsureTenantContext` menangani blokir JSON/assistant untuk expired tenant.

### ✅ RESOLVED — `EnsureTenantContext` overlap dengan `CheckSubscriptionStatus`
Kedua middleware cek `tenant->isActive()`. Handoff dan pembagian tanggung jawab kini didokumentasikan via komentar inline di `CheckSubscriptionStatus::handle()`.

---

## LOW

### ✅ RESOLVED — Konfigurasi `temporaryUrl` 30 menit hardcoded
Semua hardcoded `addMinutes(30)` diganti dengan `config('filesystems.url_ttl_minutes', 25)`. Dapat diatur lewat env `UPLOAD_URL_TTL`.

### ✅ RESOLVED — Dead code / komentar TODO
`InventoryService::moveItem` — blok comment-out `StockMovement::create([...])` dan variabel `$oldLocationId` yang tidak terpakai sudah dihapus.

### `print()` controller return Inertia render, bukan PDF
`SalesOrderController::print` — nama method menyesatkan. Return Inertia view (bukan PDF). Rename ke `invoice()` saat ada bandwidth untuk update route + frontend.

### ✅ RESOLVED — Magic numbers di SKU generator
`InventoryItem::generateSku` — prefix per kategori dipindah ke `config/business.php` (key `sku_prefix` per kategori). Fallback tetap `'INV-ITM'`. Kategori cosmetic kini dapat prefix `INV-COS`.

### Tidak konsisten antara `paginate(15)` vs `paginate(20)`
SalesOrder pakai 15, Inventory pakai 20. Bisa distandarkan ke konstanta.

### `app/Helpers/helpers.php` auto-loaded tanpa konten review
Perlu pastikan tidak ada fungsi global yang berkonflik.

### Tidak ada `php artisan optimize:clear` di alur deploy
README hanya menyebut cache. Tidak ada dokumen deploy.

### Unit test kosong
`tests/Unit/ExampleTest.php` cuma boilerplate. Domain logic (MaterialStockService, SKU generator, status transition) seharusnya punya unit test.

### `Inertia::render` controller terlalu gemuk
`InventoryItemController::create/edit` bangun query kompleks. Pindahkan ke service / view model.

### ✅ RESOLVED — `composer.json` `name: "laravel/blank-vue-starter-kit"`
Diganti ke `"fabriku/fabriku"`.

---

## Catatan Arsitektur

- Tidak ada layer `Resource` / API transformer. Data tenant di-pass langsung lewat Inertia. `$appends` di InventoryItem memperparah ini.
- `HasAuditLogs` listener fire untuk semua attribute update. Tidak ada whitelist — update kolom besar clone 2x di audit_logs.
- Observer + Controller manual stock manipulation → sudah dikonsolidasikan ke observer.

---

## Sisa yang Belum Diselesaikan

1. **HIGH** — Email log PII: skip/encrypt body untuk template token-bearing.
2. **HIGH** — Observer partial-fail risk: wrap queue job status changes dalam outer transaction.
3. **MEDIUM** — `InventoryItem` accessor alias cleanup: hapus `$appends` alias, update frontend.
4. **MEDIUM** — `subscription.check` GET assistant: keputusan desain, dokumentasikan atau block GET juga.
5. **MEDIUM** — `assistant_messages` retention policy.
6. **LOW** — `print()` method rename ke `invoice()`.
7. **LOW** — Standarisasi `paginate()` ke konstanta tunggal.
8. **LOW** — Unit test untuk domain logic (MaterialStockService, SKU generator, status transitions).
9. **LOW** — `InventoryItemController` query pindah ke service/view model.
