# Long-tail Keyword Strategy

Companion to `docs/seo-audit.md`. That doc fixes the technical blockers (sitemap, canonical, indexing); this one is about what to actually rank for once Google can crawl the site properly.

## Methodology

GSC has no usable query data yet (domain is new, 9 impressions total, only the brand term tracked) — so this isn't backed by a keyword-volume tool. It's built from:

1. **What's already published and (barely) working.** The 12 existing blog posts (`database/seeders/BlogSeeder.php`) all follow the same pattern: `cara/tips/kesalahan + tugas operasional + UMKM + kualifier` (e.g. *"Cara Menghitung HPP Produk UMKM agar Tidak Salah Tentukan Harga Jual"*). That pattern is deliberately long-tail (5–9 words), which is the only realistic way to rank with zero domain authority and zero backlinks.
2. **Fabriku's actual category vocabulary** (`config/business.php`): Garment & Konveksi, Makanan & Kue, Kerajinan & Craft, Toko/Retail, Kosmetik & Skincare, Produksi Rumahan, Jasa & Layanan — each with its own terminology, so keyword targeting should mirror the exact words a business owner in that category would type.
3. **Gap analysis** — which categories/pain-points the 12 existing posts do *not* cover yet, so new content doesn't cannibalize existing posts' rankings.

## Why long-tail, specifically

Head terms like "aplikasi UMKM", "software manajemen produksi", "aplikasi kasir" are owned by funded incumbents (Accurate, Jubelio, Moka, Majoo, etc.) with years of backlinks — unwinnable for a brand-new domain with 0 backlinks. Long-tail phrases (low search volume individually, but many of them, low competition, high buyer-intent because they're specific) are the only path to any organic traffic in the next 6–12 months. This also matches how Fabriku's product itself is scoped: category-specific terminology per `business_category`, so category-specific long-tail content is a natural fit, not a stretch.

## Content gap map by category

| Category | Already covered (existing posts) | Gap — long-tail opportunity |
|---|---|---|
| Garment & Konveksi | Stok bahan baku (2 posts) | Produksi per size/warna (BOM per varian), manajemen kontraktor/maklun, jadwal produksi vs deadline pesanan |
| Makanan & Kue | Produksi harian rumahan, HPP | Manajemen kadaluarsa bahan basah/beku, kalkulasi porsi/resep untuk pesanan custom, sertifikasi/label (tangensial, low priority) |
| Kerajinan & Craft | *(none dedicated)* | Ini kategori paling kosong — stok bahan baku unik/tidak standar, produksi batch kecil custom order, pricing produk handmade |
| Toko/Retail | Aplikasi kasir (tips memilih) | Stok opname retail, produk hampir kadaluarsa/dead stock, split rak/lokasi, retur barang |
| Kosmetik & Skincare | Stok agar tidak kadaluarsa | Nomor batch & BPOM tracking (operasional, bukan legal), reseller/dropship stock split |
| Produksi Rumahan | Produksi harian kuliner | Skala dari rumahan ke semi-pabrik — kapan butuh sistem, multi-produk dalam satu dapur |
| Jasa & Layanan | Pricing jasa servis | Jadwal staf/teknisi, pelacakan spare part/consumable per servis, laporan omzet jasa vs produk |
| Lintas kategori | Laporan penjualan (2), pesanan online/offline, piutang | Perbandingan "Excel vs aplikasi" (transisi), multi-cabang/multi-outlet, forecast stok musiman (lebaran/nataru) |

## Priority keyword list (next content batch)

Ranked by ease-to-rank (long-tail specificity + zero existing competition from Fabriku's own content) × relevance to a module Fabriku actually has (so the post can convert, not just rank).

| # | Target keyword / title seed | Category | Intent | Priority |
|---|---|---|---|---|
| 1 | "cara menentukan harga jual produk kerajinan tangan handmade" | Craft | Informational → transactional | High |
| 2 | "cara mengelola bahan baku kerajinan yang tidak standar (sisa kain, manik, kayu)" | Craft | Informational | High |
| 3 | "cara kelola stok retur barang UMKM retail" | Retail | Informational | High |
| 4 | "cara stok opname toko kelontong/retail tanpa tutup toko" | Retail | Informational | High |
| 5 | "cara mengelola kontraktor/maklun konveksi agar tidak telat" | Garment | Informational → transactional | High |
| 6 | "cara membuat BOM (bill of material) produksi UMKM per varian ukuran/warna" | Garment | Informational | Medium |
| 7 | "cara mengatur jadwal staf servis/bengkel biar tidak bentrok" | Jasa | Informational | Medium |
| 8 | "cara melacak sparepart/bahan habis pakai jasa servis" | Jasa | Informational | Medium |
| 9 | "kapan usaha rumahan butuh sistem pencatatan, bukan buku/Excel lagi" | Cross-category | Consideration | High (funnel-relevant) |
| 10 | "cara kelola stok reseller dan dropship kosmetik tanpa selisih" | Kosmetik | Informational | Medium |
| 11 | "cara menyiapkan stok UMKM sebelum lebaran/nataru (forecast musiman)" | Cross-category | Informational | Medium (seasonal — schedule ~6 weeks before Ramadan/Desember) |
| 12 | "aplikasi pencatatan UMKM vs Excel: kapan harus pindah" | Cross-category | Consideration/transactional | High (bottom-funnel) |
| 13 | "cara kelola banyak cabang/outlet UMKM dengan stok terpusat" | Cross-category | Informational | Medium |
| 14 | "cara menghitung HPP produk handmade/kerajinan custom order" | Craft | Informational | Medium |
| 15 | "cara mencatat produksi multi-produk dalam satu dapur rumahan" | Homemade | Informational | Low |

Craft/Kerajinan is the most under-served category (zero dedicated posts) and directly maps to Fabriku's per-category config — start there.

## Free, non-blog wins

- **Internal linking is currently zero.** `BlogSeeder.php` content has no links between posts at all — a post about HPP never links to the pricing-related piutang post, etc. Cross-linking related posts (and linking blog → landing page sections like `/#fitur`) is free topical-authority signal and directly improves crawl discovery. Do this when writing/editing post content, not as a separate initiative.
- **FAQPage schema** was just added to the landing page (see audit doc) — monitor GSC's "Search appearance" report over the next few weeks for FAQ rich-result eligibility.
- **Category/tag pages are intentionally not separately indexed** (canonicalized to `/blog`) — this is a deliberate trade-off from the audit fix, not a gap to "fix" here.

## Roadmap

1. Submit sitemap + request indexing for existing posts (manual GSC steps, listed in `seo-audit.md`).
2. Publish from the priority list above, 2–4 posts/month, starting with Craft/Kerajinan (biggest gap) and the two bottom-funnel pieces (#9, #12).
3. Add 2–3 internal links per new post to existing related posts.
4. Re-check GSC Performance monthly once non-brand queries start appearing — use actual query data at that point to replace this pre-launch estimate-based list.
5. Backlinks (outside codebase scope): UMKM/business directories, komunitas UMKM Facebook/Telegram groups, guest posts on Indonesian SME/business blogs. Zero backlinks today is the single biggest gap this doc can't fix with content alone.
