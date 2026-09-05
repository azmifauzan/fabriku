# SEO Audit — 2026-09-05

Audit source: Google Search Console (`fabriku.web.id`, domain property) + codebase review. All fixes below were implemented in the same session; see git log around this date for the commit.

## GSC snapshot at time of audit

- **Traffic**: last 3 months — 1 click, 9 impressions, average position 17.1 (page 2). Only query with any tracked volume: the brand term "fabriku" itself — zero non-brand visibility.
- **Indexing**: 13 URLs known to Google total, 10 indexed / 3 not indexed. Only **1 of ~12 published blog posts** was actually indexed.
- **Sitemaps**: 0 submitted. No `sitemap.xml` existed anywhere in the codebase.
- **Links**: 0 external backlinks, 0 internal links reported (crawl too shallow to populate this report).
- **Indexed-but-shouldn't-be**: `/blog?category=...` and `/blog?tag=...` were indexed as separate pages, flagged by GSC as *"Duplicate without user-selected canonical"*. `/login` and `/register` were also indexed with zero SEO value.
- **Core Web Vitals**: no field data yet (site/traffic too new) — recheck once traffic grows.

## Findings and fixes

| # | Finding | Root cause | Fix |
|---|---|---|---|
| 1 | No sitemap ever existed | No route, no file, no package | New `SitemapController@index` → `/sitemap.xml`, lists home/blog index/legal pages + every published `BlogPost` (excludes drafts and category/tag filter URLs on purpose). Route `sitemap.xml`. Referenced from `robots.txt`. |
| 2 | `robots.txt` had no `Sitemap:` directive | Never added | Added `Sitemap: https://fabriku.web.id/sitemap.xml` line. |
| 3 | `/blog?category=x`, `/blog?tag=y` indexed as duplicate content | No canonical tag anywhere in the app | `BlogController::index()` now passes `canonical` = `url('/blog')` (bare, no query string) regardless of active filter; new `SeoHead` component renders it as `<link rel="canonical">` on every public page. |
| 4 | No canonical tags anywhere (`grep -r canonical resources/` = 0 hits) | Never implemented | `SeoHead.vue` (new shared component) renders canonical on Welcome, Blog Index, Blog Show, Legal/Privacy, Legal/Terms — backend supplies the URL (`url()->current()` / `url(...)`), so it's correct with or without the SSR bundle. |
| 5 | `/login`, `/register`, `/complete-google-registration` indexed with no SEO value | No `noindex` anywhere | `SeoHead :noindex="true"` on `Auth/Login.vue`, `Auth/Register.vue`, `Auth/CompleteGoogleRegistration.vue` → `<meta name="robots" content="noindex,follow">`. |
| 6 | Blog index page had generic `<Head title="Blog" />`, no description | Minimal implementation | Real title (`Blog — Tips Operasional UMKM \| Fabriku`) + meta description + canonical via `SeoHead`. |
| 7 | Blog post pages had title/description but no Open Graph tags or structured data | Never implemented | `SeoHead` adds `og:title/description/type/url/image` + `twitter:card`. `Blog/Show.vue` now emits `Article` and `BreadcrumbList` JSON-LD (headline, image, dates, author, publisher, breadcrumb Home → Blog → Post). |
| 8 | Landing page had no `og:image`, no structured data | Never implemented | `Welcome.vue` adds `og:image` (`/images/fabriku-word.png`, resolved absolute), plus `Organization`, `SoftwareApplication`, and `FAQPage` (from the existing on-page FAQ accordion) JSON-LD. |
| 9 | `app.blade.php` had one static `<meta name="description">`/OG set for *every* route | Static blade tags, not per-page | Removed from `app.blade.php`; every public page now supplies its own via `SeoHead`, avoiding duplicate/conflicting meta tags in the rendered HTML. |
| 10 | FAQ content lived only inside `FAQ.vue`, unusable for schema | Data trapped in one component | Extracted to `resources/js/data/faq.ts`, imported by both `FAQ.vue` and `Welcome.vue` (schema). |

## Files changed

- `app/Http/Controllers/SitemapController.php` (new)
- `app/Http/Controllers/BlogController.php` — `canonical` prop on index/show
- `routes/web.php` — `/sitemap.xml` route, `canonical` prop on `/`, `/privasi`, `/syarat-ketentuan`
- `resources/views/sitemap.blade.php` (new)
- `resources/views/app.blade.php` — removed static description/OG tags
- `resources/js/components/SeoHead.vue` (new, shared)
- `resources/js/data/faq.ts` (new, shared FAQ content)
- `resources/js/components/Landing/FAQ.vue` — imports shared FAQ data
- `resources/js/pages/Welcome.vue`, `Blog/Index.vue`, `Blog/Show.vue`, `Legal/Privacy.vue`, `Legal/Terms.vue`, `Auth/Login.vue`, `Auth/Register.vue`, `Auth/CompleteGoogleRegistration.vue`
- `public/robots.txt`
- Tests: `tests/Feature/SitemapTest.php` (new), `tests/Feature/PublicBlogTest.php` (canonical assertions added)

## Deferred / not fixed here

- **`featured_image_url` is a temporary signed S3 URL** (~25 min TTL, `BlogPost::getFeaturedImageUrlAttribute()`). Fine for the moment a link is shared (WhatsApp/Facebook fetch the image immediately and cache the *image itself*), but not a stable long-lived URL. Revisit only if social-preview images start showing broken after cache expiry.
- **Core Web Vitals** — not enough real-user data yet; nothing to fix, just needs traffic.
- **Backlinks** — zero, and outside code scope. Addressed as a roadmap item in `docs/seo-keyword-strategy.md`.

## Manual follow-up (cannot be automated from the codebase)

1. Submit `https://fabriku.web.id/sitemap.xml` in GSC → Indexing → Sitemaps.
2. Use URL Inspection → "Request indexing" for the previously-unindexed blog posts to speed up discovery (sitemap alone will get there, just slower).
3. Re-check GSC "Page indexing" and "Links" reports in ~2–4 weeks to confirm the duplicate-canonical and login/register-indexed issues clear out.
