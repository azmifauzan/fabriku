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
- status `confirmed/processing→completed` → observer deduct
- status `confirmed/processing→cancelled` → observer release
- soft-delete → observer release (jika confirmed/processing)
- Destroy: `canBeCancelled()` guard, kemudian `$salesOrder->delete()` (observer handle release).

### ✅ RESOLVED — Order bisa dibuat dengan `status=confirmed` lewat client
`SalesOrderController::store` hardcode `status = 'draft'` dan `StoreSalesOrderRequest::prepareForValidation()` default ke `'draft'`.

### ✅ RESOLVED — Kondisi race + integritas stok
`InventoryItem::reserveStock()`, `releaseReservedStock()`, `deductStock()` sudah punya `DB::transaction` + `self::lockForUpdate()->find($this->id)` di dalam method itu sendiri. `Illuminate\Support\Facades\DB` sudah di-import di model.

### ✅ RESOLVED — `SalesOrderController::update` tidak enforce `canBeEdited()`
Added guard: `if (! $salesOrder->canBeEdited()) { abort(403, ...); }` di awal `update()`.

### Telegram webhook tanpa rate limit
`routes/api.php:17` — endpoint POST publik tanpa throttle. Verifikasi `X-Telegram-Bot-Api-Secret-Token` ada, tapi attacker tetap bisa hammer endpoint.
**Fix**: `->middleware('throttle:60,1')`. Kurangi level log `debug` payload ke `info` ringkas.

---

## HIGH

### ✅ RESOLVED — Schema documentation drift
`.github/COLUMN-NAMING-CONVENTIONS.md` sudah dihapus (tidak lagi di repo). CLAUDE.md memuat schema aktual sebagai referensi.

### ✅ RESOLVED — Duplicate key di array `stats`
`SalesOrderController::index` — duplikat `'pending_payment'` sudah dihapus.

### Storage disk hardcoded `fabriku_s3`
`app/Models/InventoryItem.php:183`, `app/Models/Material.php:86`, beberapa controller — string literal `'fabriku_s3'` tersebar.
**Fix**: tarik ke `config/filesystems.php` dengan key alias atau `config('app.uploads_disk')`. Test gagal kalau env tidak punya disk ini.

### `temporaryUrl()` di S3 dipanggil tiap kali accessor diakses
`InventoryItem::getImageUrlAttribute` + `Material::getImageUrlAttribute` — `temporaryUrl` adalah HTTP call ke S3. Karena `$appends`, setiap serialize ke JSON (mis. list 20 item) trigger 20x S3 call.
**Fix**: jangan masukkan ke `$appends`; expose lazy lewat resource transformer atau cache URL 25 menit.

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

### Inkonsistensi pattern auto-fill `tenant_id`
- Material/Pattern: ada `auth()->check()` guard
- InventoryItem: tidak ada `auth()->check()` (null reference via console)
- SalesOrder: controller isi manual
**Fix**: ekstrak ke trait `BelongsToTenant` dengan listener seragam + defensive null-check.

### Accessor alias berlapis di `InventoryItem`
`current_stock`, `reserved_stock`, `inventory_location_id`, `name`, `pattern`, `batch_number`, `expiry_date` di `$appends` — duplikasi surface, payload membengkak.
**Fix**: pilih nama canonical, hapus alias. Frontend update.

### `DB::raw` dan `whereRaw` di controller
`DashboardController` pakai `whereRaw(...)`. Rentan saat kolom di-rename.
**Fix**: `whereColumn('stock_quantity', '<=', 'min_stock')`.

### Sub-query N+1 di Dashboard
`DashboardController::index` aggregate di PHP setelah `get()`. OOM risk saat data besar.
**Fix**: agregasi di DB.

### `OpenAIService` default model `gpt-5-nano`
`app/Services/Assistant/OpenAIService.php:21` — bukan model OpenAI valid. Jika ENV tidak di-set, request runtime gagal.
**Fix**: default ke `gpt-4o-mini`. Validasi di boot.

### `OpenAIService::chat` selalu sertakan `temperature` & `max_tokens`
Tidak semua model terima param ini. Akan error 400 pada model baru (o1, gpt-5 family).
**Fix**: filter param berdasarkan model capability.

### Tidak ada test untuk observer behavior baru pada force-delete
`SalesOrderObserver::forceDeleting` melepas reservasi jika tidak trashed. Asumsinya benar tapi belum ter-cover test.

### `assistant_messages` log tanpa truncation
Full content + tool_calls JSON tiap pesan. Tidak ada retention policy.

### Wayfinder TS output tidak di-gitignore
`resources/js/actions/` & `resources/js/routes/` auto-generated. Kalau ikut commit → conflict besar.
**Fix**: gitignore + generate di CI.

### Logging payload Telegram di level `debug`
`TelegramWebhookController::handle:35` — `Log::debug(...)` penuh pesan user. Privacy concern di production.

### `subscription.check` tidak skip GET assistant route
`assistant.message` adalah POST. User expired bisa GET history. Inconsistent dengan pesan UI.

### `EnsureTenantContext` overlap dengan `CheckSubscriptionStatus`
Kedua middleware cek `tenant->isActive()`. Logic spread di dua tempat.
**Fix**: gabungkan atau dokumentasikan handoff di kode.

---

## LOW

### Konfigurasi `temporaryUrl` 30 menit hardcoded
`InventoryItem::getImageUrlAttribute` — tarik ke config.

### Dead code / komentar TODO
`InventoryService::moveItem` line 79-86 — comment-out StockMovement insert. Hapus atau buat tiket.

### `print()` controller return Inertia render, bukan PDF
`SalesOrderController::print` — nama method menyesatkan. Return Inertia view.

### Magic numbers di SKU generator
`InventoryItem::generateSku` — prefix per category hardcoded. Pindahkan ke `config/business.php`.

### Tidak konsisten antara `paginate(15)` vs `paginate(20)`
SalesOrder pakai 15, Inventory pakai 20.

### `app/Helpers/helpers.php` auto-loaded tanpa konten review
Perlu pastikan tidak ada fungsi global yang berkonflik.

### Tidak ada `php artisan optimize:clear` di alur deploy
README hanya menyebut cache. Tidak ada dokumen deploy.

### Unit test kosong
`tests/Unit/ExampleTest.php` cuma boilerplate. Domain logic (MaterialStockService, SKU generator, status transition) seharusnya punya unit test.

### `Inertia::render` controller terlalu gemuk
`InventoryItemController::create/edit` bangun query kompleks. Pindahkan ke service / view model.

### `composer.json` `name: "laravel/blank-vue-starter-kit"`
Belum di-rename ke project.

---

## Catatan Arsitektur

- Tidak ada layer `Resource` / API transformer. Data tenant di-pass langsung lewat Inertia. `$appends` di InventoryItem memperparah ini.
- `HasAuditLogs` listener fire untuk semua attribute update. Tidak ada whitelist — update kolom besar clone 2x di audit_logs.
- Observer + Controller manual stock manipulation → sudah dikonsolidasikan ke observer.

---

## Rekomendasi Prioritas (sisa)

1. **Telegram rate limit** — satu line tambah throttle middleware.
2. **HIGH storage disk** — extract ke config agar test bisa mock.
3. **HIGH temporaryUrl S3** — remove dari `$appends`, lazy via resource transformer.
4. **MEDIUM InventoryItem alias accessor** — bersihkan API surface.
5. **MEDIUM OpenAIService** — fix default model id.
6. Lainnya bisa antri.
