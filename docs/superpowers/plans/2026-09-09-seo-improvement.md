# SEO Improvement Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Buat setiap halaman publik Fabriku terbaca lengkap oleh crawler yang tidak mengeksekusi JavaScript, hentikan sinyal crawl yang buruk (5xx, URL filter duplikat, `lang` salah), lalu isi celah konten long-tail per kategori bisnis.

**Architecture:** Empat fase berurutan. Fase 1 = perbaikan kecil berisiko rendah yang tidak saling bergantung (500 OAuth, `APP_LOCALE`, `noindex` filter blog, `lastmod` sitemap, canonical paginasi) — semuanya bisa merge sendiri-sendiri. Fase 2 = mengaktifkan Inertia SSR di image produksi, yang mengubah HTML server dari shell kosong menjadi halaman penuh dan dengan sendirinya menutup empat dari lima isu Ubersuggest. Fase 3 = verifikasi pasca-deploy terhadap sumber data yang sama dengan audit (curl, GSC, Ubersuggest). Fase 4 = konten: internal linking antar-artikel dan empat artikel baru dari daftar prioritas keyword.

**Tech Stack:** Laravel 12, Inertia.js v2 + `@inertiajs/vue3/server`, Vue 3.5 `<script setup>`, Vite 7 (`--ssr`), Pest 4, Docker multi-stage (php:8.4-apache + supervisord), Node 24.

**Spec:** `docs/seo-audit-2026-09-09.md` (temuan + bukti), `docs/seo-keyword-strategy.md` (daftar keyword Fase 4).

## Global Constraints

- Bahasa UI, meta, dan konten: **Bahasa Indonesia**. Komentar kode dan pesan commit: Inggris.
- Setiap perubahan wajib punya test Pest. Gunakan assertion spesifik (`assertRedirect`, `assertNotFound`), bukan `assertStatus(...)`.
- PHP: return type eksplisit selalu; kurung kurawal untuk semua struktur kontrol; PHPDoc, bukan komentar inline.
- Vue: satu root element; `<Link>` bukan `<a>`; halaman publik memakai `<SeoHead>` (`resources/js/components/SeoHead.vue`), bukan `<Head>` mentah — `canonical` selalu datang dari backend (`url()->current()` / `url(...)`), tidak pernah dari `window.location`.
- Jalankan `vendor/bin/pint --dirty --format agent` sebelum setiap commit yang menyentuh PHP.
- Jalankan `php artisan test --compact` sebelum setiap commit.
- Domain produksi: `https://fabriku.web.id`. Tag image Docker: `azmifauzan/fabriku:{ddMMyy}-{counter}` (lihat `docs/deployment.md`).
- Jangan menambah dependency baru. Semua yang dibutuhkan Fase 2 (`@inertiajs/vue3/server`, `vue/server-renderer`, `resources/js/ssr.ts`, entri `ssr` di `vite.config.ts`) sudah ada di repo.

---

## File Structure

**Fase 1**
- Modify: `app/Http/Controllers/Auth/GoogleAuthController.php` — guard callback tanpa `code`
- Modify: `public/robots.txt` — `Disallow: /auth/`
- Modify: `config/app.php` + `.env.example` — default locale `id`
- Modify: `app/Http/Controllers/BlogController.php` — prop `noindex` + canonical sadar-paginasi
- Modify: `resources/js/pages/Blog/Index.vue` — teruskan `noindex` ke `SeoHead`
- Modify: `app/Http/Controllers/SitemapController.php` — `lastmod` opsional
- Modify: `resources/views/sitemap.blade.php` — render `lastmod` hanya bila ada
- Test: `tests/Feature/GoogleAuthTest.php`, `tests/Feature/PublicBlogTest.php`, `tests/Feature/SitemapTest.php`

**Fase 2**
- Modify: `Dockerfile` — `npm run build:ssr`, salin `bootstrap/ssr`, sediakan binary `node` di stage runtime
- Modify: `docker/supervisord.conf` — `[program:inertia-ssr]`
- Modify: `docs/deployment.md` — langkah verifikasi SSR pasca-deploy

**Fase 4**
- Modify: `database/seeders/BlogSeeder.php` — internal link di post lama + 4 post baru
- Test: `tests/Feature/BlogInternalLinkTest.php` (baru)

---

## Fase 1 — Perbaikan sinyal crawl

### Task 1: `/auth/google/callback` berhenti mengembalikan 500 — SELESAI (7ad576b)

GSC melaporkan `https://fabriku.web.id/auth/google/callback` sebagai satu-satunya "Error server (5xx)". Terverifikasi: `curl` ke URL itu → 500. `Socialite::driver('google')->user()` melempar exception ketika Google tidak mengirim parameter `code` — yang selalu terjadi saat crawler membuka URL itu langsung.

**Files:**
- Modify: `app/Http/Controllers/Auth/GoogleAuthController.php:25-27`
- Modify: `public/robots.txt`
- Test: `tests/Feature/GoogleAuthTest.php`

**Interfaces:**
- Consumes: route bernama `google.callback` (`GET /auth/google/callback`) dan `login`, keduanya sudah ada di `routes/web.php`.
- Produces: tidak ada API baru. `callback()` tetap `RedirectResponse`.

- [x] **Step 1: Tulis test yang gagal**

Tambahkan di akhir `tests/Feature/GoogleAuthTest.php`:

```php
it('redirects to login instead of throwing when the google callback has no authorization code', function () {
    $response = $this->get(route('google.callback'));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors('email');
});
```

- [x] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --compact --filter="no authorization code"`
Expected: FAIL — exception dari Socialite (`InvalidStateException` atau HTTP client error), bukan redirect.

- [x] **Step 3: Tambahkan guard di controller**

Di `app/Http/Controllers/Auth/GoogleAuthController.php`, ubah awal `callback()`:

```php
    public function callback(Request $request): RedirectResponse
    {
        // Crawlers and stray visitors hit this URL with no ?code=, which used to throw
        // and surface as a 500 — GSC flagged it as the domain's only server error.
        if (! $request->filled('code')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan Google tidak selesai. Silakan coba lagi.',
            ]);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('login')->withErrors([
                'email' => 'Login dengan Google gagal. Silakan coba lagi.',
            ]);
        }

        $raw = $googleUser->getRaw();
```

Baris `$raw = $googleUser->getRaw();` dan seterusnya tidak berubah. Hapus baris lama `$googleUser = Socialite::driver('google')->user();` yang berdiri sendiri.

- [x] **Step 4: Jalankan test, pastikan lolos**

Run: `php artisan test --compact --filter=GoogleAuth`
Expected: PASS — test baru dan seluruh test GoogleAuth lama.

- [x] **Step 5: Tutup `/auth/` dari crawler**

Ganti isi `public/robots.txt` menjadi:

```
User-agent: *
Disallow: /auth/
Disallow: /login
Disallow: /register

Sitemap: https://fabriku.web.id/sitemap.xml
```

- [x] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/Auth/GoogleAuthController.php public/robots.txt tests/Feature/GoogleAuthTest.php
git commit -m "fix(seo): stop 500 on google callback without code, disallow /auth in robots"
```

---

### Task 2: `lang` dokumen jadi `id` — SELESAI (c4f461f)

`app.blade.php` merender `lang="{{ str_replace('_', '-', app()->getLocale()) }}"`. `config/app.php:81` memakai `env('APP_LOCALE', 'en')` dan `.env.example` menyetel `APP_LOCALE=en`, sehingga produksi menyajikan konten 100% Bahasa Indonesia dengan `<html lang="en">` (terverifikasi live).

Perbaikannya menyentuh default di `config/app.php`, bukan hanya `.env` — supaya server yang `.env`-nya tidak memuat kunci itu tetap benar, dan supaya perbaikan ini tidak bergantung pada seseorang mengingat menyunting `.env` produksi.

**Files:**
- Modify: `config/app.php:81`
- Modify: `.env.example`
- Test: `tests/Feature/LocaleTest.php` (baru)

**Interfaces:**
- Consumes: `app()->getLocale()`.
- Produces: tidak ada.

- [x] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/LocaleTest.php`:

```php
<?php

it('renders the document language as Indonesian', function () {
    $response = $this->get('/');

    $response->assertOk();
    expect($response->getContent())->toContain('<html lang="id"');
});
```

`phpunit.xml` tidak menyetel `APP_LOCALE`, jadi test ini membaca konfigurasi yang sama dengan aplikasi — `.env` lokal yang masih `en` akan membuatnya gagal, dan itu memang yang diinginkan.

- [x] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --compact --filter=LocaleTest`
Expected: FAIL — HTML berisi `<html lang="en"`.

- [x] **Step 3: Ubah default konfigurasi dan contoh env**

Di `config/app.php` baris 81:

```php
    'locale' => env('APP_LOCALE', 'id'),
```

Di `.env.example`, ganti `APP_LOCALE=en` menjadi `APP_LOCALE=id`.

`APP_FALLBACK_LOCALE` dan `APP_FAKER_LOCALE` biarkan apa adanya — factory tidak boleh ikut berubah.

- [x] **Step 4: Samakan `.env` lokal**

Ubah `APP_LOCALE=en` menjadi `APP_LOCALE=id` di `.env` lokal (file ini tidak masuk git).

- [x] **Step 5: Jalankan test, pastikan lolos**

Run: `php artisan test --compact`
Expected: PASS seluruhnya. Jalankan suite penuh, bukan hanya filter — perubahan locale bisa menggeser format tanggal/angka di test lain.

- [x] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add config/app.php .env.example tests/Feature/LocaleTest.php
git commit -m "fix(seo): serve Indonesian content as lang=id"
```

- [x] **Step 7: Catat langkah manual**

`.env` produksi tidak ada di repo dan saat ini memuat `APP_LOCALE=en`, yang menimpa default baru. Tambahkan ke catatan deploy: set `APP_LOCALE=id` di `.env` produksi sebelum recreate container, lalu `php artisan config:cache`.

---

### Task 3: View blog terfilter jadi `noindex`, canonical paginasi menunjuk ke dirinya sendiri — SELESAI (8a74e86)

Dari 17 URL tidak terindeks di GSC, 13 adalah URL filter blog (`/blog?tag=…&page=1`, `/blog?category=…&page=1`, `/blog?page=1`). Canonical sudah menunjuk ke `/blog` bare, tapi canonical tidak menghentikan crawl — Google terus menghabiskan anggaran crawl pada kombinasi tag × page sementara artikel asli menganggur di "crawled, currently not indexed".

Aturan yang dituju:
- Ada `category` atau `tag` → `noindex,follow`, canonical `/blog`.
- Hanya `page` (atau `page=1`) → tetap indexable. Canonical `/blog` untuk `page` kosong atau `1`; `/blog?page=N` untuk `N >= 2`, supaya paginasi asli tidak mengaku sebagai halaman 1.

**Files:**
- Modify: `app/Http/Controllers/BlogController.php:12-30`
- Modify: `resources/js/pages/Blog/Index.vue:5-30`
- Test: `tests/Feature/PublicBlogTest.php`

**Interfaces:**
- Consumes: prop Inertia `canonical` yang sudah ada di `Blog/Index.vue`, dan prop `noindex` di `SeoHead.vue` (sudah ada, `boolean`, default `false`).
- Produces: prop Inertia baru `noindex: bool` pada komponen `Blog/Index`.

- [x] **Step 1: Tulis test yang gagal**

Tambahkan di `tests/Feature/PublicBlogTest.php`:

```php
it('marks category-filtered blog listings as noindex', function () {
    $category = BlogCategory::factory()->create(['slug' => 'tips-umkm']);
    BlogPost::factory()->published()->create(['blog_category_id' => $category->id]);

    $this->get(route('blog.index', ['category' => 'tips-umkm']))
        ->assertInertia(fn ($page) => $page
            ->where('noindex', true)
            ->where('canonical', url('/blog'))
        );
});

it('marks tag-filtered blog listings as noindex', function () {
    $tag = BlogTag::factory()->create(['slug' => 'retail']);
    BlogPost::factory()->published()->create()->tags()->attach($tag->id);

    $this->get(route('blog.index', ['tag' => 'retail']))
        ->assertInertia(fn ($page) => $page->where('noindex', true));
});

it('keeps the unfiltered blog listing indexable', function () {
    BlogPost::factory()->published()->create();

    $this->get(route('blog.index'))
        ->assertInertia(fn ($page) => $page
            ->where('noindex', false)
            ->where('canonical', url('/blog'))
        );
});

it('canonicalizes page 1 to the bare listing but page 2 to itself', function () {
    BlogPost::factory()->published()->count(13)->create();

    $this->get(route('blog.index', ['page' => 1]))
        ->assertInertia(fn ($page) => $page->where('canonical', url('/blog')));

    $this->get(route('blog.index', ['page' => 2]))
        ->assertInertia(fn ($page) => $page
            ->where('canonical', url('/blog?page=2'))
            ->where('noindex', false)
        );
});
```

- [x] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --compact --filter=PublicBlogTest`
Expected: FAIL — prop `noindex` tidak ada; canonical untuk `page=2` masih `url('/blog')`.

- [x] **Step 3: Implementasi di controller**

Ganti method `index()` di `app/Http/Controllers/BlogController.php`:

```php
    public function index(Request $request)
    {
        $posts = BlogPost::where('status', 'published')
            ->with('category', 'tags')
            ->when($request->category, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($request->tag, fn ($q, $slug) => $q->whereHas('tags', fn ($q) => $q->where('slug', $slug)))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $isFiltered = $request->filled('category') || $request->filled('tag');
        $page = (int) $request->query('page', 1);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'categories' => BlogCategory::orderBy('name')->get(['name', 'slug']),
            'activeCategory' => $request->category,
            'activeTag' => $request->tag,
            // Filter views collapse onto the bare listing; real pagination pages own
            // their canonical so page 2+ never claims to be page 1.
            'canonical' => $isFiltered || $page <= 1 ? url('/blog') : url('/blog?page='.$page),
            // Canonical alone does not stop Google from spending crawl budget on the
            // unbounded tag x page combinations — 13 of 17 unindexed URLs were these.
            'noindex' => $isFiltered,
        ]);
    }
```

- [x] **Step 4: Teruskan prop ke `SeoHead`**

Di `resources/js/pages/Blog/Index.vue`, tambahkan `noindex: boolean;` ke `defineProps`, lalu ikat ke komponen:

```vue
defineProps<{
    posts: {
        data: Array<{
            slug: string;
            title: string;
            excerpt: string | null;
            featured_image_url: string | null;
            published_at: string | null;
            category: { name: string; slug: string } | null;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    categories: Array<{ name: string; slug: string }>;
    activeCategory: string | null;
    canonical: string;
    noindex: boolean;
}>();
```

```vue
    <SeoHead
        title="Blog — Tips Operasional UMKM | Fabriku"
        description="Tips dan panduan praktis untuk UMKM Indonesia: mengelola bahan baku, produksi, stok, penjualan, dan laporan keuangan."
        :canonical="canonical"
        :noindex="noindex"
    />
```

- [x] **Step 5: Jalankan test, pastikan lolos**

Run: `php artisan test --compact --filter=PublicBlogTest`
Expected: PASS, termasuk dua test canonical lama (`filters the index by category` / `by tag`) yang masih menuntut `canonical === url('/blog')`.

- [x] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
npm run lint
git add app/Http/Controllers/BlogController.php resources/js/pages/Blog/Index.vue tests/Feature/PublicBlogTest.php
git commit -m "fix(seo): noindex filtered blog listings, self-canonical paginated pages"
```

---

### Task 4: Sitemap berhenti mengklaim halaman statis berubah tiap detik

`SitemapController::index()` memakai `now()` sebagai `lastmod` untuk `/`, `/blog`, `/privasi`, `/syarat-ketentuan`. Setiap kali Google mengambil sitemap, keempat URL itu mengaku baru saja berubah. Google belajar mengabaikan `lastmod` untuk domain yang berperilaku begitu — termasuk `lastmod` artikel blog yang justru akurat (`updated_at`).

`lastmod` bersifat opsional dalam spesifikasi sitemap, jadi cara paling sederhana adalah tidak mengirimkannya untuk halaman statis.

**Files:**
- Modify: `app/Http/Controllers/SitemapController.php:11-25`
- Modify: `resources/views/sitemap.blade.php:6`
- Test: `tests/Feature/SitemapTest.php`

**Interfaces:**
- Consumes: view `sitemap` dengan variabel `$urls`.
- Produces: setiap elemen `$urls` kini `array{loc: string, priority: string, lastmod?: \Illuminate\Support\Carbon}` — kunci `lastmod` boleh tidak ada.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan di `tests/Feature/SitemapTest.php`:

```php
it('emits lastmod only for blog posts, never for static pages', function () {
    BlogPost::factory()->published()->count(2)->create();

    $body = $this->get('/sitemap.xml')->getContent();

    expect(substr_count($body, '<loc>'))->toBe(6);      // 4 static + 2 posts
    expect(substr_count($body, '<lastmod>'))->toBe(2);  // posts only
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --compact --filter=SitemapTest`
Expected: FAIL — `<lastmod>` ditemukan 6 kali, bukan 2.

- [ ] **Step 3: Hilangkan `lastmod` dari entri statis**

Di `app/Http/Controllers/SitemapController.php`, ganti collection statis:

```php
        $urls = collect([
            // No lastmod for static pages on purpose: emitting now() on every request
            // trains Google to ignore lastmod for this domain, including the accurate
            // updated_at values on blog posts below.
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('blog.index'), 'priority' => '0.8'],
            ['loc' => route('legal.privacy'), 'priority' => '0.3'],
            ['loc' => route('legal.terms'), 'priority' => '0.3'],
        ])->concat(
```

Bagian `BlogPost::where(...)` di bawahnya tidak berubah — tetap mengirim `'lastmod' => $post->updated_at`.

- [ ] **Step 4: Buat blade toleran terhadap `lastmod` yang hilang**

Di `resources/views/sitemap.blade.php`, ganti baris `<lastmod>`:

```blade
    <url>
        <loc>{{ $url['loc'] }}</loc>
@isset ($url['lastmod'])
        <lastmod>{{ $url['lastmod']->toAtomString() }}</lastmod>
@endisset
        <priority>{{ $url['priority'] }}</priority>
    </url>
```

- [ ] **Step 5: Jalankan test, pastikan lolos**

Run: `php artisan test --compact --filter=SitemapTest`
Expected: PASS, termasuk test lama yang memeriksa draft tidak ikut terdaftar.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Http/Controllers/SitemapController.php resources/views/sitemap.blade.php tests/Feature/SitemapTest.php
git commit -m "fix(seo): drop always-now lastmod from static sitemap entries"
```

---

## Fase 2 — Aktifkan SSR di produksi

### Task 5: Bangun dan jalankan bundle SSR di image Docker — SELESAI (8a93b78)

Ini akar dari empat dari lima isu Ubersuggest (19/19 halaman: duplicate `<title>`, no meta description, no H1, low word count) dan penyebab HTML server tidak memuat satu pun canonical/OG/JSON-LD meski `SeoHead.vue` benar.

Kondisi sekarang: `config/inertia.php` menyetel `ssr.enabled = true` dan `resources/js/ssr.ts` + entri `ssr` di `vite.config.ts:11` sudah ada, tapi `Dockerfile:72` menjalankan `npm run build` (bukan `build:ssr`), `bootstrap/ssr/ssr.mjs` tidak pernah ada di image, dan `docker/supervisord.conf` tidak punya program yang menjalankannya. Setiap request mencoba `http://127.0.0.1:13714`, gagal, lalu diam-diam fallback ke render klien.

Stage runtime (`php:8.4-apache`) tidak punya Node. `php artisan inertia:start-ssr` hanya butuh binary `node`, bukan npm — dan `php:8.4-apache` serta `node:24-bookworm-slim` sama-sama Debian bookworm, jadi menyalin binary-nya cukup dan jauh lebih ringan daripada memasang paket lewat apt.

Prasyarat: verifikasi lokal dulu (Step 1–3) sebelum menyentuh Dockerfile. Kalau ada komponen halaman yang menyentuh `window`/`document` saat setup, server SSR akan melemparkan error untuk halaman itu. Sudah dicek: `resources/js/composables/useDarkMode.ts` menjaga diri dengan `typeof window !== 'undefined'`, dan tidak ada akses browser API di `Welcome.vue`, `Blog/*`, `Legal/*`, `PublicLayout.vue`, atau `components/Landing/`.

**Files:**
- Modify: `Dockerfile:72` (perintah build) dan stage 3 (binary node + salin bundle)
- Modify: `docker/supervisord.conf`
- Modify: `docs/deployment.md`

**Interfaces:**
- Consumes: `resources/js/ssr.ts` (sudah ada), `config/inertia.php` `ssr.url = http://127.0.0.1:13714` (sudah ada).
- Produces: `bootstrap/ssr/ssr.mjs` di dalam image; proses supervisord bernama `inertia-ssr` yang listen di `127.0.0.1:13714`.

- [x] **Step 1: Bangun bundle SSR secara lokal**

Run:
```bash
npm run build:ssr
ls -la bootstrap/ssr/
```
Expected: `bootstrap/ssr/ssr.mjs` ada. Kalau build gagal, perbaiki dulu sebelum lanjut — jangan bawa kegagalan ini ke Dockerfile.

- [x] **Step 2: Jalankan server SSR dan buktikan HTML server sudah lengkap**

Di satu terminal:
```bash
php artisan inertia:start-ssr
```

Di terminal lain:
```bash
php artisan serve --port=8123 &
curl -s http://127.0.0.1:8123/ | grep -c '<h1'
curl -s http://127.0.0.1:8123/ | grep -o 'rel="canonical"\|application/ld+json\|property="og:title"\|name="description"' | sort | uniq -c
```
Expected: `<h1` ditemukan minimal 1 kali, dan keempat pola meta muncul. Bandingkan dengan produksi hari ini, yang mengembalikan nol untuk semuanya.

Kalau `inertia:start-ssr` melempar error untuk halaman tertentu, catat nama komponennya dan perbaiki akses browser API di komponen itu sebelum melanjutkan.

- [x] **Step 3: Verifikasi halaman blog juga penuh**

Run:
```bash
curl -s http://127.0.0.1:8123/blog | grep -c '<h1'
```
Expected: minimal 1. Hentikan `inertia:start-ssr` dan `php artisan serve` setelah ini.

- [x] **Step 4: Bangun bundle SSR di image**

Di `Dockerfile`, pada stage `node-builder`, ganti `&& npm run build` menjadi `&& npm run build:ssr` (baris 72):

```dockerfile
RUN cp .env.example .env && php artisan key:generate \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
    && touch database/database.sqlite \
    && php artisan wayfinder:generate --with-form \
    && npm run build:ssr
```

- [x] **Step 5: Sediakan binary node dan bundle di stage runtime**

Di `Dockerfile` stage 3, tepat setelah baris `COPY --from=composer:2 /usr/bin/composer /usr/bin/composer`, tambahkan:

```dockerfile
# Inertia SSR needs only the node binary, not npm. php:8.4-apache and
# node:24-bookworm-slim share the same Debian base, so copying the binary is
# smaller and simpler than installing nodejs from a package repo.
COPY --from=node:24-bookworm-slim /usr/local/bin/node /usr/local/bin/node
```

Lalu, tepat setelah baris `COPY --from=node-builder --chown=www-data:www-data /app/public/build ./public/build`, tambahkan:

```dockerfile
# SSR bundle consumed by `php artisan inertia:start-ssr` (see supervisord.conf)
COPY --from=node-builder --chown=www-data:www-data /app/bootstrap/ssr ./bootstrap/ssr
```

- [x] **Step 6: Jalankan server SSR lewat supervisord**

Tambahkan di akhir `docker/supervisord.conf`:

```ini
[program:inertia-ssr]
command=php /var/www/html/artisan inertia:start-ssr
autostart=true
autorestart=true
priority=20
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/inertia-ssr.log
stdout_logfile_maxbytes=10MB
startsecs=0
startretries=10
```

`priority=20` menempatkannya di antara apache (10) dan queue worker (30). Kalau proses ini mati, Laravel diam-diam kembali ke render klien — situs tetap hidup, hanya SEO-nya yang mundur ke kondisi hari ini. Karena itu `autorestart=true` wajib.

- [x] **Step 7: Build image dan verifikasi di dalam container**

```bash
TAG="$(date +%d%m%y)-ssr1"
docker build -t "azmifauzan/fabriku:${TAG}" .
docker run -d --name fabriku-ssr-check -p 8899:80 "azmifauzan/fabriku:${TAG}"
sleep 25
docker exec fabriku-ssr-check supervisorctl status inertia-ssr
curl -s http://127.0.0.1:8899/ | grep -c '<h1'
```
Expected: `inertia-ssr` berstatus `RUNNING`, dan `<h1` ditemukan minimal 1 kali.

Kalau `<h1` nol tapi status `RUNNING`, cek log: `docker exec fabriku-ssr-check tail -50 /var/log/supervisor/inertia-ssr.log`.

Bersihkan: `docker rm -f fabriku-ssr-check`.

- [x] **Step 8: Catat verifikasi di dokumen deploy**

Tambahkan bagian ini ke `docs/deployment.md`:

```markdown
## Verifikasi SSR pasca-deploy

SSR menyuplai seluruh HTML yang dibaca crawler non-JS. Kalau prosesnya mati, situs
tetap jalan (fallback ke render klien) tanpa error yang terlihat — jadi harus dicek
manual setiap deploy:

```bash
docker compose exec app supervisorctl status inertia-ssr   # harus RUNNING
curl -s https://fabriku.web.id/ | grep -c '<h1'            # harus >= 1
curl -s https://fabriku.web.id/blog/<slug> | grep -c 'rel="canonical"'  # harus 1
```

Nol pada perintah kedua atau ketiga berarti SSR mati dan seluruh meta SEO hilang
dari HTML server.
```

- [x] **Step 9: Commit**

```bash
git add Dockerfile docker/supervisord.conf docs/deployment.md
git commit -m "feat(seo): build and run the Inertia SSR bundle in the production image"
```

`bootstrap/ssr/` hasil build lokal tidak ikut ter-commit: `.gitignore:2` sudah memuat `/bootstrap/ssr`. Di dalam image, direktori itu datang dari stage `node-builder` lewat `COPY` eksplisit di Step 5, yang dijalankan setelah `COPY . .` sehingga tidak tertimpa.

---

## Fase 3 — Verifikasi terhadap sumber audit

### Task 6: Deploy dan konfirmasi terhadap curl, GSC, dan Ubersuggest

Fase ini tidak menulis kode. Fase ini membuktikan bahwa Fase 1–2 benar-benar mengubah apa yang dilihat crawler — pelajaran dari audit #1, yang fix-nya benar di kode tapi tak pernah sampai ke HTML.

**Files:** tidak ada perubahan kode.

- [ ] **Step 1: Deploy**

Ikuti `docs/deployment.md` (build, tag, push, SSH, update compose, recreate, migrate). Sebelum recreate, set `APP_LOCALE=id` di `.env` produksi (Task 2 Step 6).

- [ ] **Step 2: Verifikasi HTML server — perintah yang sama persis dengan audit**

```bash
curl -s https://fabriku.web.id/ | grep -o '<html lang="[a-z]*"'
curl -s -o /dev/null -w "%{http_code}\n" https://fabriku.web.id/auth/google/callback
curl -s https://fabriku.web.id/blog/cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi \
  | grep -o 'rel="canonical"\|application/ld+json\|property="og:[a-z]*"\|name="description"' | sort | uniq -c
curl -s https://fabriku.web.id/blog | grep -c '<h1'
curl -s https://fabriku.web.id/sitemap.xml | grep -c '<lastmod>'
```

Expected:
- `<html lang="id"`
- `302` (bukan `500`)
- canonical/ld+json/og/description masing-masing muncul (audit hari ini: nol semua)
- `<h1` minimal 1
- jumlah `<lastmod>` sama dengan jumlah artikel terbit, bukan jumlah itu + 4

- [ ] **Step 3: Verifikasi preview share**

Tempel satu URL artikel di chat WhatsApp ke diri sendiri. Preview harus menampilkan judul artikel, bukan "Fabriku — Operasional UMKM dalam satu alur".

- [ ] **Step 4: Tindakan manual di GSC**

1. Halaman → "Error server (5xx)" → **Validasi Perbaikan**.
2. Halaman → "Duplikat, tanpa ada versi kanonis pilihan pengguna" → **Validasi Perbaikan**.
3. Inspeksi URL untuk 2 artikel yang berstatus "Di-crawl - saat ini tidak diindeks" (`cara-mencatat-produksi-harian-umkm-kuliner-rumahan`, `cara-membuat-laporan-penjualan-umkm-sederhana`) → **Minta Pengindeksan**.

- [ ] **Step 5: Recrawl Ubersuggest**

Buka `app.neilpatel.com` → Site Audit → **Recrawl website**. Bandingkan dengan baseline audit: On-Page Score 40, dan 19/19 halaman kena low word count / duplicate title / no H1 / no meta description. Keempatnya harus turun tajam. Skor yang tidak bergerak berarti SSR tidak benar-benar jalan — kembali ke Task 5 Step 7.

- [ ] **Step 6: Catat hasil**

Tambahkan bagian "Hasil verifikasi (tanggal)" di `docs/seo-audit-2026-09-09.md` berisi angka before/after untuk setiap perintah di Step 2 dan skor Ubersuggest baru. Commit.

- [ ] **Step 7: Cek ulang tertunda (2–4 minggu)**

Setelah 2–4 minggu, catat di dokumen yang sama: jumlah URL terindeks/tidak terindeks, apakah "Tautan internal" GSC sudah bukan 0 lagi, dan apakah sudah muncul kueri non-brand. Sebelum SSR, tautan internal tercatat 0 setelah 3 bulan — angka inilah indikator paling jelas bahwa penemuan link sudah membaik.

---

## Fase 4 — Konten dan internal linking

### Task 7: Internal link antar-artikel

`docs/seo-keyword-strategy.md` mencatat internal linking sebagai nol: tidak ada satu pun artikel di `BlogSeeder.php` yang menautkan ke artikel lain. GSC juga melaporkan tautan internal 0. Internal link adalah sinyal otoritas topikal yang gratis dan sekaligus jalur penemuan crawl.

**Files:**
- Modify: `database/seeders/BlogSeeder.php`
- Test: `tests/Feature/BlogInternalLinkTest.php` (baru)

**Interfaces:**
- Consumes: `BlogPost::$content` (Markdown), dirender ke `content_html` oleh model.
- Produces: tidak ada API baru.

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/BlogInternalLinkTest.php`:

```php
<?php

use App\Models\BlogPost;
use Database\Seeders\BlogSeeder;

it('gives every seeded post at least one link to another post', function () {
    $this->seed(BlogSeeder::class);

    $missing = BlogPost::where('status', 'published')
        ->get()
        ->filter(fn (BlogPost $post) => ! str_contains($post->content, ']('.'/blog/'))
        ->pluck('slug')
        ->all();

    expect($missing)->toBe([]);
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --compact --filter=BlogInternalLink`
Expected: FAIL — daftar slug berisi semua artikel yang ada.

- [ ] **Step 3: Sisipkan 2–3 link per artikel**

Di `database/seeders/BlogSeeder.php`, sunting heredoc `content` setiap artikel dan tambahkan link Markdown ke artikel terkait secara alami di dalam kalimat, bukan sebagai daftar "baca juga" yang ditempel di akhir. Pasangan yang relevan:

- `cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi` → `kesalahan-mengelola-stok-bahan-baku-umkm-konveksi`, `cara-menghitung-hpp-produk-umkm`
- `kesalahan-mengelola-stok-bahan-baku-umkm-konveksi` → `cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi`, `aplikasi-pencatatan-produksi-usaha-kecil-menengah`
- `cara-menghitung-hpp-produk-umkm` → `cara-menentukan-harga-jual-produk-kerajinan-handmade`, `cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi`
- `cara-membuat-laporan-penjualan-umkm-sederhana` → `metrik-laporan-penjualan-wajib-dipantau-umkm`, `cara-mencatat-menagih-piutang-pelanggan-umkm`
- `metrik-laporan-penjualan-wajib-dipantau-umkm` → `cara-membuat-laporan-penjualan-umkm-sederhana`, `cara-menghitung-hpp-produk-umkm`
- `cara-mencatat-menagih-piutang-pelanggan-umkm` → `cara-mengelola-pesanan-online-offline-sekaligus`
- `cara-mengelola-pesanan-online-offline-sekaligus` → `tips-memilih-aplikasi-kasir-umkm-pemula`, `cara-kelola-stok-retur-barang-umkm-retail`
- `tips-memilih-aplikasi-kasir-umkm-pemula` → `aplikasi-pencatatan-produksi-usaha-kecil-menengah`
- `cara-mengelola-stok-kosmetik-agar-tidak-kadaluarsa` → `cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi`
- `cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi` → `cara-menghitung-hpp-produk-umkm`
- `cara-menentukan-harga-jual-produk-kerajinan-handmade` → `cara-mengelola-bahan-baku-kerajinan-tidak-standar`, `cara-menghitung-hpp-produk-umkm`
- `cara-mengelola-bahan-baku-kerajinan-tidak-standar` → `cara-menentukan-harga-jual-produk-kerajinan-handmade`
- `cara-kelola-stok-retur-barang-umkm-retail` → `cara-mengelola-pesanan-online-offline-sekaligus`
- `cara-mencatat-produksi-harian-umkm-kuliner-rumahan` → `cara-menghitung-hpp-produk-umkm`
- `aplikasi-pencatatan-produksi-usaha-kecil-menengah` → `cara-mencatat-produksi-harian-umkm-kuliner-rumahan`

Bentuk penulisan di dalam kalimat, contoh untuk artikel HPP:

```markdown
Angka HPP baru berguna kalau stok bahan bakunya sendiri tercatat rapi — kalau catatan bahan masih meleset, HPP-nya ikut meleset. Cara membenahinya ada di [panduan mengelola stok bahan baku UMKM](/blog/cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi).
```

Anchor text memakai frasa deskriptif, bukan "klik di sini" atau URL telanjang.

- [ ] **Step 4: Jalankan test, pastikan lolos**

Run: `php artisan test --compact --filter=BlogInternalLink`
Expected: PASS.

- [ ] **Step 5: Periksa hasil render**

```bash
php artisan db:seed --class=BlogSeeder
php artisan serve --port=8123 &
curl -s http://127.0.0.1:8123/blog/cara-menghitung-hpp-produk-umkm | grep -o 'href="/blog/[a-z-]*"'
```
Expected: minimal dua href artikel yang berbeda. Hentikan server setelahnya.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/seeders/BlogSeeder.php tests/Feature/BlogInternalLinkTest.php
git commit -m "feat(blog): cross-link related posts for topical authority and crawl discovery"
```

---

### Task 8: Empat artikel baru dari daftar prioritas keyword

`docs/seo-keyword-strategy.md` memberi peringkat 15 keyword. Nomor 1, 2, dan 3 sudah terbit (`cara-menentukan-harga-jual-produk-kerajinan-handmade`, `cara-mengelola-bahan-baku-kerajinan-tidak-standar`, `cara-kelola-stok-retur-barang-umkm-retail`). Batch berikutnya mengambil dua High yang tersisa dan dua bagian bawah funnel.

| # | Slug | Judul | Kategori | Tag |
|---|---|---|---|---|
| 4 | `cara-stok-opname-toko-retail-tanpa-tutup-toko` | Cara Stok Opname Toko Retail Tanpa Harus Tutup Toko | Manajemen Stok | Retail, Stok Opname, UMKM |
| 5 | `cara-mengelola-kontraktor-maklun-konveksi-agar-tidak-telat` | Cara Mengelola Kontraktor/Maklun Konveksi agar Pesanan Tidak Telat | Produksi | Konveksi, Maklun, UMKM |
| 9 | `kapan-usaha-rumahan-butuh-sistem-pencatatan` | Kapan Usaha Rumahan Butuh Sistem Pencatatan, Bukan Buku Lagi | Tips Bisnis | Produksi Rumahan, UMKM, Digitalisasi |
| 12 | `aplikasi-pencatatan-umkm-vs-excel-kapan-pindah` | Aplikasi Pencatatan UMKM vs Excel: Kapan Harus Pindah | Tips Bisnis | UMKM, Digitalisasi, Aplikasi UMKM |

**Files:**
- Modify: `database/seeders/BlogSeeder.php`
- Test: `tests/Feature/BlogInternalLinkTest.php` (yang sudah ada — ikut menjaga artikel baru)

**Interfaces:**
- Consumes: bentuk array dari `BlogSeeder::posts()` — `array{slug, title, category, tags, days_ago, excerpt, meta_title, meta_description, content}`.
- Produces: tidak ada API baru.

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan di `tests/Feature/BlogInternalLinkTest.php`:

```php
it('publishes the next keyword batch', function () {
    $this->seed(BlogSeeder::class);

    $slugs = [
        'cara-stok-opname-toko-retail-tanpa-tutup-toko',
        'cara-mengelola-kontraktor-maklun-konveksi-agar-tidak-telat',
        'kapan-usaha-rumahan-butuh-sistem-pencatatan',
        'aplikasi-pencatatan-umkm-vs-excel-kapan-pindah',
    ];

    foreach ($slugs as $slug) {
        $post = BlogPost::where('slug', $slug)->first();

        expect($post)->not->toBeNull("missing post: {$slug}");
        expect($post->status)->toBe('published');
        expect(str_word_count(strip_tags($post->content)))->toBeGreaterThan(500);
        expect($post->meta_description)->not->toBeNull();
        expect(strlen($post->meta_description))->toBeLessThanOrEqual(160);
    }
});
```

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --compact --filter="next keyword batch"`
Expected: FAIL — "missing post: cara-stok-opname-toko-retail-tanpa-tutup-toko".

- [ ] **Step 3: Tulis keempat artikel**

Tambahkan empat entri di array `BlogSeeder::posts()`, mengikuti persis bentuk entri yang sudah ada (heredoc `<<<'MD'`, `days_ago` menurun untuk artikel yang lebih baru — pakai 4, 3, 2, 1).

Aturan untuk setiap artikel, diturunkan dari isu yang ditemukan audit:
- Lebih dari 500 kata (Ubersuggest menandai low word count di seluruh halaman).
- Tepat satu `<h1>` — dihasilkan otomatis dari `title`, jadi **jangan** memulai `content` dengan `#`; mulai dari `##`.
- `meta_description` maksimal 160 karakter, memuat frasa keyword target.
- Minimal dua internal link ke artikel yang sudah ada (Task 7 mewajibkan lewat test).
- Menyebut modul Fabriku yang relevan di paragraf penutup, bukan di pembuka.
- Bahasa Indonesia percakapan-tapi-praktis, sama seperti artikel yang sudah ada. Tanpa klaim yang tidak bisa dibuktikan.

Sudut yang harus dibahas per artikel, supaya tidak berebut keyword dengan artikel lama:

- **`cara-stok-opname-toko-retail-tanpa-tutup-toko`** — opname bergulir per rak/kategori dan bukan sekaligus, jadwal jam sepi, membekukan pergerakan stok satu bagian saja, menangani selisih tanpa menyalahkan orang, kapan opname penuh tetap tidak terhindarkan. Tautkan ke `cara-kelola-stok-retur-barang-umkm-retail` dan `cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi`.
- **`cara-mengelola-kontraktor-maklun-konveksi-agar-tidak-telat`** — kesepakatan tertulis kapasitas dan tenggat, serah-terima bahan dengan hitungan tercatat, pemeriksaan titik tengah bukan hanya akhir, penanganan barang reject, jangan bergantung pada satu maklun. Tautkan ke `kesalahan-mengelola-stok-bahan-baku-umkm-konveksi` dan `aplikasi-pencatatan-produksi-usaha-kecil-menengah`.
- **`kapan-usaha-rumahan-butuh-sistem-pencatatan`** — gejala konkret (lebih dari satu orang mencatat, stok "hilang" berulang, pesanan lewat karena tercecer di chat, tidak bisa menjawab produk mana yang paling untung), bukan ukuran omzet. Tautkan ke `cara-mencatat-produksi-harian-umkm-kuliner-rumahan` dan `cara-menghitung-hpp-produk-umkm`.
- **`aplikasi-pencatatan-umkm-vs-excel-kapan-pindah`** — jujur soal di mana Excel masih menang (murah, fleksibel, tidak perlu belajar), di mana ia rontok (banyak pengguna sekaligus, riwayat perubahan, stok real-time, rumus yang rusak diam-diam), dan bagaimana pindah tanpa kehilangan data. Tautkan ke `tips-memilih-aplikasi-kasir-umkm-pemula` dan `kapan-usaha-rumahan-butuh-sistem-pencatatan`.

- [ ] **Step 4: Jalankan test, pastikan lolos**

Run: `php artisan test --compact --filter=BlogInternalLink`
Expected: PASS untuk kedua test — batch keyword baru dan syarat internal link.

- [ ] **Step 5: Periksa render dan sitemap**

```bash
php artisan db:seed --class=BlogSeeder
php artisan serve --port=8123 &
curl -s http://127.0.0.1:8123/sitemap.xml | grep -c 'cara-stok-opname-toko-retail'
curl -s http://127.0.0.1:8123/blog/kapan-usaha-rumahan-butuh-sistem-pencatatan | grep -c '<h1'
```
Expected: `1` untuk keduanya. Hentikan server setelahnya.

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint --dirty --format agent
git add database/seeders/BlogSeeder.php tests/Feature/BlogInternalLinkTest.php
git commit -m "feat(blog): add 4 posts for retail opname, maklun, and Excel-migration keywords"
```

- [ ] **Step 7: Perbarui dokumen strategi**

Di `docs/seo-keyword-strategy.md`, tandai keyword #4, #5, #9, #12 sebagai terbit dan catat tanggalnya. Commit bersama perubahan di atas.

---

## Ditangguhkan dengan sengaja

- **Interactivity 434,9 ms** (Ubersuggest desktop, ideal < 200 ms). Load time 0,77 s dan CLS 0,00 sudah bagus. SSR mengubah profil hidrasi secara mendasar, jadi mengoptimalkan sekarang berarti menebak. Ukur ulang di Task 6 Step 5; garap hanya kalau masih di atas 200 ms setelah SSR jalan.
- **Backlink** (GSC: 0, Ubersuggest: 8, DA 3). Di luar cakupan kode — roadmap ada di `docs/seo-keyword-strategy.md` bagian 5.
- **`featured_image_url` adalah signed URL S3 sementara** (TTL ~25 menit). Ditangguhkan di audit #1; belum ada bukti preview sosialnya rusak. Tinjau ulang kalau muncul laporan gambar preview kosong.
- **`cluster: true` di `resources/js/ssr.ts`** menjalankan satu worker SSR per CPU. Di VPS kecil ini bisa boros memori. Biarkan dulu; ubah ke `false` hanya kalau pemakaian memori container naik tajam setelah Task 5.


---

## Deviasi saat eksekusi (2026-09-09)

Task 1, 2, 3, dan 5 sudah dikerjakan. Empat hal berbeda dari rencana:

1. **Path test Google salah di plan.** File sebenarnya `tests/Feature/Auth/GoogleAuthTest.php`, bukan `tests/Feature/GoogleAuthTest.php`.
2. **Empat test callback lama ikut berubah.** Guard `! $request->filled('code')` membuat test lama yang memanggil `route('google.callback')` polos jadi ikut ter-redirect. Keempatnya kini mengirim `['code' => 'fake-oauth-code']`, sesuai perilaku redirect Google yang sebenarnya.
3. **`vite.config.ts` perlu `ssr: { noExternal: true }`** — tidak ada di rencana. Build SSR default meng-externalize dependency, jadi `bootstrap/ssr/ssr.js` mengimpor `@inertiajs/vue3` saat runtime dan proses SSR mati dengan `ERR_MODULE_NOT_FOUND` di container yang tidak membawa `node_modules`. Ketahuan di Task 5 Step 7 (build image pertama). Dengan `noExternal`, seluruh dependency masuk ke `ssr.js` (2,0 MB) dan runtime cukup binary `node`.
4. **`supervisorctl` tidak bisa dipakai** di image ini — `docker/supervisord.conf` tidak punya section `[unix_http_server]`/`[supervisorctl]` (kondisi lama, bukan akibat perubahan ini). Verifikasi di `docs/deployment.md` memakai `pgrep -af "inertia:start-ssr"`. Menambahkan section itu akan membuat `supervisorctl status/restart` bisa dipakai untuk keempat program — di luar cakupan perubahan ini, tapi layak digarap terpisah.

Belum dikerjakan: Task 4 (MEDIUM — `lastmod` sitemap), Task 6 (deploy + verifikasi), Task 7 dan 8 (konten).
