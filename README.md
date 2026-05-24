# Fabriku

Platform SaaS multi-tenant untuk manajemen produksi dan penjualan UMKM Indonesia. Satu codebase melayani beberapa kategori bisnis lewat terminologi dinamis dan business rules per-kategori.

**Status**: production-ready (per Mei 2026). Lihat [`docs/current-status.md`](docs/current-status.md) untuk detail modul yang aktif.

## Kategori Bisnis yang Didukung

| Kategori | Workflow | Contoh Produk |
|---|---|---|
| Garment & Konveksi | Pattern → Cutting → Sewing | Mukena, daster, gamis |
| Makanan & Kue | Resep → Mixing → Baking | Cake, brownies, cookies |
| Kerajinan | Desain → Persiapan → Pembuatan | Souvenir, aksesoris |
| Kosmetik | Formula → Mixing → Produksi | Skincare, herbal |
| **Toko / Retail** | Pembelian → Stock → Quick Checkout | Kelontong, dropship, reseller |
| **Produksi Rumahan** | Bahan Baku → Catatan Produksi → Quick Checkout | Toko kue rumahan, frozen food |

Workflow penuh: **Bahan Baku → Pattern/Resep → Persiapan → Produksi → Inventory → Penjualan**. Terminologi UI menyesuaikan kategori tenant. Kategori `retail` dan `homemade` melewati production flow dan langsung ke inventory + quick checkout.

## Stack

- **Backend**: Laravel 12.47 / PHP 8.4
- **Frontend**: Vue 3.5 (Composition API + `<script setup>`) via Inertia.js v2
- **Styling**: Tailwind CSS v4 (CSS-first config)
- **Type-safe routing**: Laravel Wayfinder
- **Database**: PostgreSQL 16 (recommended) atau MySQL 8
- **Cache/Queue**: Redis 7
- **Tests**: Pest 4 (feature + browser testing)
- **AI**: OpenAI Chat Completions *(tidak aktif di UI — tersedia di backend)*
- **Notifikasi**: Telegram Bot SDK, email SMTP
- **PDF/Excel**: DomPDF, Maatwebsite Excel
- **Storage**: AWS S3 (via flysystem)

## Prerequisites

- PHP 8.4+
- Composer 2.x
- Node.js 18+
- PostgreSQL 14+ atau MySQL 8+
- Redis 6+

## Quick Start (Local)

```bash
git clone <repo> fabriku
cd fabriku

composer install
npm install

cp .env.example .env
php artisan key:generate

# konfigurasi DB & Redis di .env, lalu:
php artisan migrate --seed

# dev server (concurrent: serve + queue + vite)
composer dev
```

Akses: http://localhost:8000

### Akun Demo

Tenant (URL `/login`):
- `admin@konveksi.com` (garment) / `password`
- `admin@kuemama.com` (food) / `password`
- `admin@crafty.com` (craft) / `password`
- `admin@glowbeauty.com` (cosmetic) / `password`
- `admin@tokoserbaada.com` (retail) / `password`
- `admin@homemade.com` (homemade) / `password`

Admin platform (URL `/admin/login`):
- `admin@fabriku.com` / `password`

Data demo otomatis di-reset tiap jam oleh scheduler.

## Docker

Lihat `docker-compose.yml` (production, asumsi PostgreSQL & Redis di host) dan `docker-compose.dev.yml` (semua di container).

```bash
# Development
cp .env.dev.example .env
docker compose -f docker-compose.dev.yml run --rm app php artisan key:generate
docker compose -f docker-compose.dev.yml up -d
docker compose -f docker-compose.dev.yml exec app php artisan migrate --seed
```

Container app bundling Apache + Supervisor + Cron + Queue Worker dalam satu image.

## Testing

```bash
php artisan test --compact                                  # semua
php artisan test --compact tests/Feature/MaterialTest.php   # file
php artisan test --compact --filter=testName                # by name
php artisan test --parallel                                 # parallel
```

Setup: feature tests otomatis pakai `RefreshDatabase` via `tests/Pest.php`.

## Code Quality

```bash
vendor/bin/pint --dirty --format agent      # PHP format
npm run lint                                # ESLint --fix
npm run format                              # Prettier
```

## Domain Commands

```bash
php artisan demo:reset                      # reset + reseed semua tenant demo (hourly via scheduler)
php artisan demo:reset --tenant=1 --no-reseed
php artisan material:recalculate-stock      # rebuild stok material dari receipts
php artisan trial:send-reminders            # email trial expiry (daily 09:00 via scheduler)
```

## Multi-Tenancy

- Isolasi tenant di level model lewat `App\Models\Scopes\TenantScope` (global scope).
- Setiap tenant pilih satu `business_category` saat register; menentukan terminologi & rules.
- Auth pakai dua guard: `web` (User tenant) dan `admin` (AdminUser platform).
- Middleware stack tenant: `auth → verified → tenant → subscription.check → permission:<slug>`.

## Dokumentasi

| Dokumen | Isi |
|---|---|
| [`docs/01-business-requirements.md`](docs/01-business-requirements.md) | Business requirements multi-kategori |
| [`docs/02-system-architecture.md`](docs/02-system-architecture.md) | Arsitektur sistem & design pattern |
| [`docs/03-database-schema.md`](docs/03-database-schema.md) | Skema database |
| [`docs/04-api-endpoints.md`](docs/04-api-endpoints.md) | Daftar endpoint |
| [`docs/05-user-flows.md`](docs/05-user-flows.md) | Alur user per kategori |
| [`docs/current-status.md`](docs/current-status.md) | **Status aktual modul, dependency, gap** |
| [`docs/code-review.md`](docs/code-review.md) | **Code review findings — severity-tagged** |
| [`docs/plan.md`](docs/plan.md) | **Enhancement plans: retail (sisa) + kategori homemade** |
| `CLAUDE.md` | Pedoman kerja untuk Claude Code |

## Konvensi Singkat

- PHP: explicit return type, constructor property promotion, curly braces, casts via `casts()` method.
- Eloquent: prefer `Model::query()` daripada `DB::`, eager loading wajib untuk hindari N+1, Form Request untuk validasi.
- Vue: single root element, `<Link>` / `router.visit()` untuk navigasi, cek `resources/js/components/ui/` sebelum bikin baru.
- Tailwind v4: CSS-first lewat `@theme`, `@import "tailwindcss"`, gap utility untuk list spacing.
- Lokalisasi: default Bahasa Indonesia untuk semua UI, email, error.
- Test: setiap perubahan harus ada test. Hindari `assertStatus(403)` — pakai `assertForbidden`.

Detail lengkap konvensi: `CLAUDE.md` dan `.github/copilot-instructions.md`.

## Roadmap Singkat

Semua modul aktif (auth, RBAC, audit log, semua workflow produksi, sales, reports, dashboard, mode retail, mode homemade, Purchase Report, Quick Checkout POS, Telegram bot, email system). Yang belum:

- Payment gateway terintegrasi (Midtrans/Xendit) — masih manual upload bukti.
- Mobile app native.
- Shipping API (JNE/JNT/SiCepat).
- Multi-warehouse.
- Multi-bahasa.

## License

MIT.
