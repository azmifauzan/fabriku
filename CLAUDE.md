# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Fabriku — multi-tenant SaaS for Indonesian UMKM production & sales management. Category-agnostic platform: one codebase serves Garment, Food, Craft, Cosmetic, Retail, Homemade, and Service businesses via dynamic terminology and per-category rules in `config/business.php`.

Stack: Laravel 12 + Inertia.js v2 + Vue 3.5 (`<script setup>`, Composition API) + Tailwind v4 + PostgreSQL/MySQL + Redis. Type-safe routes via Laravel Wayfinder (auto-generates TS in `resources/js/actions/` and `resources/js/routes/`).

## Common Commands

### Dev
```bash
composer dev                              # concurrent: serve + queue + vite
php artisan serve                         # backend only (localhost:8000)
npm run dev                               # vite only (127.0.0.1:5173, strictPort)
php artisan queue:work                    # background jobs
php artisan schedule:work                 # scheduler (demo reset + trial reminders)
```

### Test (Pest 4)
```bash
php artisan test --compact                                          # all
php artisan test --compact tests/Feature/MaterialTest.php           # one file
php artisan test --compact --filter=testName                        # by name
php artisan test --parallel                                         # parallel
php artisan test --filter=browser                                   # browser tests (Pest 4)
```
Feature tests auto-use `RefreshDatabase` via `tests/Pest.php`. Browser tests live in `tests/Browser/`.

### Lint / Format
```bash
vendor/bin/pint --dirty --format agent    # PHP (run before finalizing)
npm run lint                              # ESLint --fix
npm run format                            # Prettier
```

### Build / Wayfinder
```bash
npm run build                             # prod assets
npm run build:ssr                         # SSR bundle
php artisan wayfinder:generate            # regen TS route bindings (Vite plugin does this on dev, run manually after route changes if needed)
```

### Domain commands
```bash
php artisan demo:reset                    # reset + reseed all demo tenants (also runs hourly via scheduler)
php artisan demo:reset --tenant=1 --no-reseed
php artisan material:recalculate-stock    # rebuild material stock from receipts
php artisan trial:send-reminders          # trial expiry emails (daily 09:00 via scheduler)
```

## Architecture

### Multi-tenancy (CRITICAL)
- Tenant isolation enforced at model level via `App\Models\Scopes\TenantScope` global scope. Every tenant-owned model adds it in `booted()` and auto-fills `tenant_id` from `auth()->user()->tenant_id` on create.
- `EnsureTenantContext` middleware (alias `tenant`) blocks users without `tenant_id`; for expired subscriptions, returns 403 JSON for API/assistant routes, allows web through in read-only mode (writes blocked by `subscription.check`).
- Each tenant picks one `business_category` (garment/food/craft/cosmetic/retail/homemade/service). `Tenant::getCategoryConfig()` / `getTerminology($key)` reads `config/business.php`. Kategori `retail` punya `rules.enable_production_flow = false`; kategori `homemade` punya `rules.enable_simple_production = true` + `enable_contractor_module = false`; kategori `service` punya `rules.enable_service_module = true` (katalog layanan, tanpa material/produksi) — dibaca `Sidebar.vue` (via `rules` dari `useBusinessContext()`) dan `DashboardController` untuk UI gating tanpa mengubah tabel. **`isModuleEnabled()` di frontend men-default `true` bila key rule tidak ada** — rule opt-in baru (seperti `enable_service_module`) wajib di-set `false` eksplisit di semua kategori lain.
- Separate `admin` auth guard (`App\Models\AdminUser`) for platform-level admin panel at `/admin/*`. Tenant users use default `web` guard.

### Authorization layers
Tenant routes stack: `auth` → `verified` → `tenant` → `subscription.check` → `permission:<slug>`.
- `permission:<slug>` (`CheckPermission` middleware) — RBAC via `User::hasPermission()` walking `roles → permissions`. Permission slugs follow `module.action` (e.g. `material.view`, `sales.edit`).
- Admin routes: `auth:admin` + `AdminMiddleware`.

### Audit logging
Models include `HasAuditLogs` trait → boots `created/updated/deleted/restored` listeners writing polymorphic rows to `audit_logs`. Models override `getAuditableAttributes()` to control what's logged.

### Bootstrap (Laravel 12 streamlined)
All middleware aliases, exception handling, and route registration live in `bootstrap/app.php`. No `app/Http/Kernel.php`. Console commands in `app/Console/Commands/` are auto-discovered; scheduled tasks declared in `routes/console.php`.

### Frontend
- Inertia pages in `resources/js/pages/<Module>/` (Vue SFC, `<script setup>`).
- Layouts: `AppLayout.vue` (tenant), `AdminLayout.vue` (admin panel).
- Wayfinder: import controller actions from `@/actions/...` for type-safe `{ url, method }` objects; named imports only (tree-shaking).
- Composables in `resources/js/composables/` (`useBusinessContext` reads tenant category, `useDarkMode`, `useSweetAlert`).
- Dark mode supported app-wide via `dark:` classes — preserve when adding components.

### Services
Domain logic in `app/Services/`:
- `MaterialStockService` — FIFO/FEFO stock movements
- `InventoryService`, `ProductionService` — workflow state transitions
- `Services/Assistant/` — OpenAI chat (`OpenAIService`), conversation persistence (`AssistantService`), business data queries (`AssistantDataService`)
- `Services/Telegram/` — bot integration

### Known Sharp Edges (see `docs/code-review.md` for full list)
- **SalesOrder stock flow — observer is single source of truth**: `SalesOrderController` no longer calls `reserveStock`/`releaseReservedStock` manually. `SalesOrderObserver` handles all stock transitions on status changes (draft→confirmed/processing=reserve, confirmed/processing/shipped→completed=deduct, confirmed/processing/shipped→cancelled=release). New code must not add manual stock calls in controller.
- **SalesOrder status transitions go through `update-status`, not Edit**: `SalesOrder::transitionMap()` is the single source of truth for the state machine (`canTransitionTo()`/`allowedTransitions()` both derive from it). `PATCH sales-orders/{id}/update-status` (`UpdateStatusRequest`) validates against it (422 if invalid). `canBeEdited()` is strict `status === 'draft'` — confirmed+ orders use Update Status / Update Pembayaran instead of the Edit form.
- **Service lines have no stock**: `sales_order_items` punya `inventory_item_id` (nullable) ATAU `service_id` (nullable, FK `RESTRICT` ke `services`) — tepat satu terisi (divalidasi di Form Request, bukan DB constraint). Kedua observer skip semua stock ops bila `inventory_item_id` null. Kode yang membaca `items.inventoryItem` wajib null-safe dan fallback ke `items.service`. Kolom `served_by` (FK `staff`) opsional untuk line layanan.
- **Consumable auto-deduct bukan via observer**: `service_consumables` memetakan layanan→inventory item. Deduct stok bahan pendukung dilakukan eksplisit di `SalesOrderController::quickCheckoutStore` (DB transaction + `lockForUpdate`), divalidasi stoknya di `after`-hook validator. Observer tidak tahu soal consumable. Sales order manual belum trigger consumable deduct.
- **`FormRequest::authorize()` returns `$this->user() !== null`** — relies on middleware for tenant/permission checks. If you add an action that bypasses the route middleware stack (e.g. an internal job), check tenancy yourself. Always use `Rule::exists('table','id')->where('tenant_id', auth()->user()->tenant_id)` for FK validation in new Form Requests.
- **`Storage::disk('fabriku_s3')` is hardcoded** in `InventoryItem`, `Material`, and several controllers. Tests fail without this disk configured.
- **`OpenAIService` default model is `gpt-5-nano`** (not a valid OpenAI ID). Set `OPENAI_MODEL` in `.env` or change the default in `config/services.php` before relying on AI features.
- **`demo:reset` is guarded** by `app()->environment(['local', 'staging'])` in `routes/console.php` — safe to run scheduler in production.
- **Inventory item multi-rack split**: create/edit forms and the "Pindah/Split Stock" flow (`TransferStockModal.vue` → `POST items/{item}/transfer` → `InventoryService::transferStock()`) each rack becomes its own `inventory_items` row (own SKU) — there is no pivot table linking split rows. Only `available_stock` (`current_quantity - reserved_quantity`) can be transferred out of an existing item; `reserved_quantity` stays on the source row until its sales order resolves. A form that embeds a sub-modal doing its own Inertia post (like `TransferStockModal` inside `Form.vue`'s edit mode) must `watch()` the reloaded `item` prop and resync any `useForm()` fields seeded from it — `useForm()` only snapshots props once at setup, so a same-page Inertia reload silently leaves stale values in already-initialized form fields.
- **`Model::whereNull('tenant_id')` on a tenant-scoped model silently returns nothing** for any authenticated tenant user. `TenantScope::apply()` unconditionally ANDs `tenant_id = auth()->user()->tenant_id` whenever a tenant user is authenticated, so combining it with an explicit `whereNull('tenant_id')` produces a query that can never match (`tenant_id = X AND tenant_id IS NULL`). To query global/system rows (e.g. `roles` with `tenant_id IS NULL`) from tenant-facing code, use `Model::withoutGlobalScope(TenantScope::class)->whereNull('tenant_id')...` — see `RoleController::index()` and `StaffController::create()`/`edit()` for the pattern. This bug previously made the staff-form role dropdown always empty (fixed in the tenant role management feature).
- **Tenant role management**: tenant admins manage their own custom roles at `/roles` (`RoleController`, gated by `isAdmin()` in the controller like `StaffController`, not `permission:` middleware). Global system roles (`roles.tenant_id IS NULL`, seeded by `RoleSeeder`) are read-only from the tenant side — implicit route-model binding on `Role` 404s automatically for them (and for other tenants' roles) because of the `TenantScope`, so `edit()`/`update()`/`destroy()` need no extra ownership check. `StoreRoleRequest`/`UpdateRoleRequest` reject any `permission_ids` whose `module = 'tenant'` (platform-only, super-admin territory) server-side, independent of what the create/edit UI renders. Deleting a custom role is blocked while any `Staff` or `User` (via `user_roles`) still references it.
- **New "code/number" columns on tenant-scoped tables must be `unique(['tenant_id', col])`, never bare `unique()`.** Recurring bug class: a migration declares a global unique index while the generator (per-tenant `count()+1`, e.g. `MaterialReceipt::whereYear(...)->count()+1`) or the Form Request validation (`unique:table,col,...,tenant_id,X`) is already tenant-scoped — so two tenants independently producing the same value (`REC-2026-0001`, material type code `MS`) hit SQLSTATE 23505 on the second insert. Fixed so far: `staff.code`, `contractors.code`, `sales_orders`/`production_orders.order_number`, `inventory_items.sku`, `inventory_locations.code`, `customers.code`, `material_receipts.receipt_number`, `material_types.code` (see `docs/code-review.md` CRITICAL section for the full audit). Columns that are intentionally still global: `users.email`, `admin_users.email`, `jobs.uuid`, `permissions.slug` (non-tenant system tables), `material_receipts.barcode` (`uniqid()`-based, not counter-based).
- **`resources/views/errors/500.blade.php` must gate `$exception->getMessage()` behind `config('app.debug')`** — it renders for every uncaught non-HTTP exception (DB errors, etc.) with the real `$exception`, unlike 401/403/404/419/429/503 whose messages are always developer-written strings from `abort()`. Printing it unconditionally leaks SQL/stack info to end users in production regardless of `APP_DEBUG`, since Laravel treats a custom `errors::500` view as fully the app's responsibility (no framework-level redaction). Confirmed live via `ExceptionHandler::render()` probe on the prod container even with `APP_DEBUG=false`.
- **Auto-generated tenant-scoped codes (`order_number`, `sku`, location `code`) must query `withTrashed()` when computing the next value.** Every generator (`SalesOrder::generateOrderNumber()`, `ProductionOrder::booted()`, `InventoryItem::generateSku()`, `InventoryLocation::generateCode()`) looks at the tenant's *own* existing rows to pick the next number — but Eloquent's default query excludes soft-deleted rows, while the DB unique index (`unique(['tenant_id', col])`) does not care about `deleted_at`. Soft-delete the row currently holding the highest number, and the generator recomputes the exact same value forever, colliding with the (still physically present) trashed row on every retry — the same "permanently stuck" failure mode as the receipt_number bug above, but reachable within a single tenant, no cross-tenant collision required. Controllers touching these (`SalesOrderController`, `ProductionOrderController`, `InventoryItemController`, `InventoryLocationController`) also catch `UniqueConstraintViolationException` as a defense-in-depth backstop, matching the pattern in `MaterialController`/`MaterialReceiptController`.
- **Inventory item merge (same rack)**: "Gabung Item" button on Show page (`MergeStockModal.vue`, hidden when there are no candidates) → `POST items/{item}/merge` → `InventoryService::mergeStock()` — moves all `current_quantity` from source into destination, writes two `StockAdjustment` rows (`TYPE_MERGE`, shared `batch_id`), then soft-deletes the source. Both items must have `reserved_quantity = 0` and match exactly on every field in `InventoryService::MERGE_COMPATIBILITY_FIELDS` (location, product_name/code, category, production_order, source_type, unit_cost, selling_price, quality_grade, status, expired_date). `InventoryItemController::show()` builds the `mergeCandidates` prop by iterating that same constant — **any new field added to the compatibility check must go through the constant, not a hardcoded array**, or the candidate list will drift from what the service actually accepts and users will get "Item tidak kompatibel" on a suggestion the UI itself offered.

## Database Column Conventions (actual schema)

Always check the actual migration file for the source of truth. `.github/COLUMN-NAMING-CONVENTIONS.md` has been deleted (was stale and misleading). Below is the actual schema:

**`inventory_items`** (`2026_01_06_000001_create_inventory_tables.php`):
- `sku`, `product_name`, `product_code`, `location_id` (FK), `production_order_id` (FK, nullable), `category_id` (FK)
- `source_type` (enum-like string: `production` / `opening_balance` / `purchase` / `return`)
- `current_quantity` (int), `reserved_quantity` (int), `target_quantity` (int), `minimum_stock` (int)
- `unit_cost`, `selling_price`, `quality_grade`, `status` (enum: `available` / `reserved` / `damaged` / `expired`)
- `expired_date` (nullable, food category)
- Available stock = `current_quantity - reserved_quantity` (use `$item->available_stock` accessor).
- Low stock = `(current_quantity - reserved_quantity) <= minimum_stock` (or `InventoryItem::lowStock()` scope which uses `current_quantity <= minimum_stock` — note the scope does NOT subtract reserved).

**`inventory_locations`**: `code`, `name`, `capacity` (nullable=unlimited), `is_active` (boolean), `zone`, `rack`, `type`, `temperature_min`/`max`.

**`sales_orders`** (`2026_01_07_000001_create_sales_tables.php`):
- `order_number`, `invoice_number`, `resi_number`, `customer_id`, `order_date`, `delivery_date`
- `channel` (enum: `offline` / `online` / `marketplace` / `reseller`)
- `status` (enum: `draft` / `confirmed` / `processing` / `shipped` / `completed` / `cancelled`)
- `subtotal`, `discount_amount`, `discount_percentage`, `tax_amount`, `shipping_cost`, `total_amount`, `paid_amount`
- `payment_method`, `payment_status` (enum: `unpaid` / `pending` / `partial` / `paid` / `refunded`)
- `payment_due_date`, `shipped_date`, `completed_date`, `shipping_address`

**`payments`** (`2026_06_11_*_create_payments_table.php`): simple payment ledger, `tenant_id`, `sales_order_id`, `amount` (negative = refund), `method`, `paid_at`, `note`. `sales_orders.paid_amount` is derived as `payments()->sum('amount')` and `payment_status` from that sum vs `total_amount` — both written via `SalesOrderController::updatePayment()` (adds a row) and the refund branch of `updateStatus()` (adds a negative row on cancel). Don't write `paid_amount`/`payment_status` directly outside these paths — they'll drift from the ledger.

**`inventory_items` model has confusing alias accessors** (`current_stock`, `reserved_stock`, `name`, `inventory_location_id`, `pattern`, `batch_number`, `expiry_date`) that proxy to the canonical columns. Prefer canonical names in new code; aliases exist for backwards compatibility only.

**Per-tenant unique constraints**: `staff.code`, `contractors.code`, `sales_orders.order_number`, `inventory_items.sku`, `inventory_locations.code` are unique per `tenant_id` (fixed in `2026_04_30_*` and `2026_05_01_*` migrations) — do not write code assuming globally unique.

**`stock_adjustments`** has three nullable columns added in `2026_05_23_*`: `batch_id` (uuid, groups multiple items in one purchase transaction), `supplier_name` (string), `purchase_invoice` (string). These are only populated for `adjustment_type = 'purchase'` rows created by `PurchaseReceiptController`.

## Conventions

- **PHP**: explicit return types always; PHP 8 constructor property promotion; curly braces for all control structures; prefer PHPDoc over inline comments; casts via `casts()` method not `$casts` property.
- **Eloquent**: prefer `Model::query()` over `DB::`; relationship methods with return type hints; eager load to avoid N+1; Form Requests for validation (not inline).
- **Routes**: use named routes + `route()` helper. Frontend uses Wayfinder imports.
- **Tests**: every change needs a test. Use factories with custom states. Use specific assertion methods (`assertForbidden`, `assertNotFound`) not `assertStatus(403)`.
- **Vue**: single root element; `<Link>` / `router.visit()` not raw `<a>`; check existing components in `resources/js/components/` (especially `ui/`) before creating new. Number display: import `formatNumber` from `@/lib/utils` for any quantity/stock/count interpolation (`{{ formatNumber(item.current_stock) }}`) — never interpolate a raw numeric prop directly, it silently drops the thousand separator once the value crosses 1000. Several pages had accumulated raw `{{ item.current_stock }}`-style interpolations before this was centralized; `formatNumber` accepts `number | string | null | undefined` and an optional `decimals` (default `0`) for fractional quantities like material kg.
- **Tailwind v4**: CSS-first config via `@theme` (no `tailwind.config.js`); use `@import "tailwindcss"` not `@tailwind` directives; gap utilities for list spacing not margins.
- **Localization**: default Bahasa Indonesia — UI strings, error messages, email templates are all Indonesian.
- **Migrations**: when modifying a column, include ALL previously-defined attributes (Laravel 12 drops omitted ones).

## Laravel Boost MCP

This project has Laravel Boost (`laravel/boost`) installed. When available, prefer Boost MCP tools (`search-docs`, `tinker`, `database-query`, `browser-logs`, `list-artisan-commands`, `get-absolute-url`) over generic alternatives. `search-docs` returns version-pinned docs for installed packages — use it before guessing API shape.

## Reference docs

- `docs/01-business-requirements.md` through `docs/05-user-flows.md` — original business/architecture/schema/API/user-flow specs.
- `docs/current-status.md` — actual state of every module, gaps, what is NOT shipped.
- `docs/code-review.md` — severity-tagged findings (CRITICAL / HIGH / MEDIUM / LOW). Read before extending Sales, Inventory, or AI modules.
- `docs/plan.md` — sisa enhancement kategori `service` (laporan layanan khusus, staff assignment, consumable auto-deduct) + backlog lintas kategori. Plan retail, homemade, dan service inti sudah selesai dan dipindah ke `current-status.md`.
- `.github/copilot-instructions.md` — Laravel Boost guidelines (PHP/Eloquent/Inertia/Tailwind/Pest conventions). Authoritative for style.
- `docs/deployment.md` — manual production deploy: build/tag/push Docker image, SSH ke server, update + recreate compose, migrate, monitor.

## Demo accounts (dev)

Tenant users (`/login`): `admin@konveksi.com`, `admin@kuemama.com`, `admin@crafty.com`, `admin@glowbeauty.com`, `admin@tokoserbaada.com` (retail), `admin@homemade.com` (homemade/produksi rumahan), `admin@bengkel.com` (service/jasa) — all password `password`.
Super admin (`/admin/login`): `admin@fabriku.com` / `password`.
Demo data auto-resets hourly via scheduler.
