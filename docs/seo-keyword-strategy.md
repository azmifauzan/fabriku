# Long-tail Keyword Strategy

Companion to `docs/seo-audit.md`. That doc fixes the technical blockers (sitemap, canonical, indexing); this one is about what to actually rank for once Google can crawl the site properly.

> **Update 2026-10-27:** keywords now have volume data from RankMySEO Keyword planner. See [Riset volume 2026-10-27](#riset-volume-2026-10-27) at the end of this doc. The list below is the original pre-launch estimate (all items published) and is kept as history.

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

| # | Target keyword / title seed | Category | Intent | Priority | Status |
|---|---|---|---|---|---|
| 1 | "cara menentukan harga jual produk kerajinan tangan handmade" | Craft | Informational → transactional | High | Terbit 2026-09-05 |
| 2 | "cara mengelola bahan baku kerajinan yang tidak standar (sisa kain, manik, kayu)" | Craft | Informational | High | Terbit 2026-09-05 |
| 3 | "cara kelola stok retur barang UMKM retail" | Retail | Informational | High | Terbit 2026-09-05 |
| 4 | "cara stok opname toko kelontong/retail tanpa tutup toko" | Retail | Informational | High | Terbit 2026-09-10 |
| 5 | "cara mengelola kontraktor/maklun konveksi agar tidak telat" | Garment | Informational → transactional | High | Terbit 2026-09-10 |
| 6 | "cara membuat BOM (bill of material) produksi UMKM per varian ukuran/warna" | Garment | Informational | Medium | Terbit 2026-10-13 |
| 7 | "cara mengatur jadwal staf servis/bengkel biar tidak bentrok" | Jasa | Informational | Medium | Terbit 2026-10-13 |
| 8 | "cara melacak sparepart/bahan habis pakai jasa servis" | Jasa | Informational | Medium | Terbit 2026-10-13 |
| 9 | "kapan usaha rumahan butuh sistem pencatatan, bukan buku/Excel lagi" | Cross-category | Consideration | High (funnel-relevant) | Terbit 2026-09-10 |
| 10 | "cara kelola stok reseller dan dropship kosmetik tanpa selisih" | Kosmetik | Informational | Medium | Terbit 2026-10-13 |
| 11 | "cara menyiapkan stok UMKM sebelum lebaran/nataru (forecast musiman)" | Cross-category | Informational | Medium (seasonal — schedule ~6 weeks before Ramadan/Desember) | Terbit 2026-10-13 |
| 12 | "aplikasi pencatatan UMKM vs Excel: kapan harus pindah" | Cross-category | Consideration/transactional | High (bottom-funnel) | Terbit 2026-09-10 |
| 13 | "cara kelola banyak cabang/outlet UMKM dengan stok terpusat" | Cross-category | Informational | Medium | Terbit 2026-10-13 |
| 14 | "cara menghitung HPP produk handmade/kerajinan custom order" | Craft | Informational | Medium | Ditunda — tumpang tindih dengan post harga jual kerajinan (sudah memuat contoh custom order) |
| 15 | "cara mencatat produksi multi-produk dalam satu dapur rumahan" | Homemade | Informational | Low | Terbit 2026-10-13 |

Keyword #9 dan #12 aslinya diberi kategori "Cross-category" di riset ini, tapi `BlogSeeder.php` hanya punya 4 kategori nyata (Manajemen Stok/Produksi/Penjualan/Keuangan) — keduanya diterbitkan dengan kategori **Produksi**, bukan kategori baru "Tips Bisnis" yang sempat direncanakan di plan implementasi.

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

## Riset volume 2026-10-27

Sumber: RankMySEO Keyword planner (Indonesia, Bahasa Indonesia), dikumpulkan 27 Okt 2026, digabung dengan data GSC 90 hari. Rencana eksekusinya ada di [`seo-audit-2026-10-27.md`](seo-audit-2026-10-27.md).

Catatan membaca angka:
- **Volume** = estimasi pencarian per bulan dari RankMySEO.
- **KD** (difficulty) dari RankMySEO hampir semuanya rendah (4–39) untuk pasar Indonesia. Pakai hanya sebagai perbandingan relatif, jangan dibaca sebagai peluang mutlak.
- SERP belum dicek manual. Query di atas 1K/bulan kemungkinan dihuni blog besar (Jurnal/Mekari, Accurate, majoo), jadi target realistisnya varian 3+ kata di bawahnya.

Kriteria lolos:
1. Minimal 3 kata.
2. Ada fitur Fabriku yang benar-benar menjawab masalahnya, supaya pembaca bisa jadi pendaftar.
3. Intent informasional atau komersial dari pemilik usaha, bukan pelajar akuntansi atau pencari jasa pembuatan.
4. Tidak bertabrakan dengan post lama, atau justru memperkuat post lama yang sudah dapat impresi.

### A. Perkuat halaman yang sudah ada (paling cepat)

| Keyword | Vol | KD | Halaman target | Alasan cocok |
|---|---|---|---|---|
| cara menghitung hpp dan harga jual | 590 | 14 | `/blog/cara-menghitung-hpp-produk-umkm` | Post sudah dapat 23 impresi. HPP tersimpan per item stok. |
| cara menghitung hpp per unit | 480 | 14 | idem | Contoh per unit sudah ada di post. Frasa ini perlu masuk ke H2/FAQ. |
| cara menghitung hpp produksi | 390 | 15 | idem | Alur produksi Fabriku: bahan terpakai → hasil jadi. |
| contoh laporan penjualan harian | 260 | 14 | `/blog/cara-membuat-laporan-penjualan-umkm-sederhana` | Post sudah dapat 21 impresi. Laporan penjualan bisa diekspor ke Excel/PDF. |
| laporan penjualan harian excel | 210 | 17 | idem | Template Excel sudah ada di post. |
| contoh laporan penjualan sederhana | 140 | 15 | idem | Sesuai judul post. |
| contoh bill of material | 260 | 15 | `/blog/cara-membuat-bom-produksi-konveksi-per-ukuran-warna` | Tabel BOM per varian sudah ada. Perlu definisi umum di awal. |
| bill of material template | 110 | 6 | idem | KD terendah di kelompok BOM. |
| cara menghitung hpp jasa | 70 | 6 | `/blog/cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi` | Post sudah dapat 16 impresi. Ada katalog layanan dan consumable. |
| aplikasi bengkel online | 140 | 12 | `/aplikasi/bengkel-jasa-servis` | Intent komersial. Fabriku berbasis web, punya katalog layanan, sparepart/consumable, dan staf. |

### B. Post baru

| # | Keyword utama (+ varian) | Vol | KD | Kategori / landing tujuan | Prioritas |
|---|---|---|---|---|---|
| 1 | kartu stok barang (+ contoh kartu stok barang 720, contoh kartu stok barang excel 210, kartu stok gudang 210, contoh kartu stok barang harian 140) | 2,9K | 17 | Lintas kategori; modul stok dan riwayat penyesuaian | Tinggi. Volume terbesar yang sesuai fitur inti. Jadikan pillar. |
| 2 | cara menghitung hpp makanan (+ hpp makanan excel 170, hpp penjualan makanan 70, hpp kue 20, hpp roti 20) | 1,9K | 14 | `/aplikasi/usaha-makanan-kue` | Tinggi. Kategori makanan belum punya post HPP khusus. |
| 3 | cara menentukan harga jual makanan per pcs (+ harga jual makanan 480, harga jual di gofood 140, di shopee food 140) | 170 | 13 | `/aplikasi/usaha-makanan-kue` | Tinggi. Sudut pembeda: menghitung potongan komisi ojol. Bisa digabung dengan #2 bila ingin hemat tenaga. |
| 4 | contoh laporan penjualan harian toko sembako | 720 | 14 | `/aplikasi/toko-retail` | Tinggi. Bedakan dari post laporan lama: banyak SKU kecil, harga grosir vs ecer, kasir cepat. |
| 5 | cara menentukan harga jual produk (+ bagaimana cara menentukan harga jual suatu produk 140, harga jual barang 70) | 1,3K | 7 | Lintas kategori | Sedang. KD terendah di volume ini. Pillar markup vs margin yang menautkan ke post kerajinan, makanan, dan HPP. Jaga agar tidak tumpang tindih dengan post HPP. |
| 6 | contoh website toko online (+ website toko online terbaik 40) | 260 | 16 | Website Usaha | Sedang. Butuh izin merchant untuk menampilkan storefront nyata. Hindari varian "jasa pembuatan" dan "gratis": intent-nya salah. |
| 7 | contoh laporan penjualan produk makanan | 390 | 21 | `/aplikasi/usaha-makanan-kue` | Sedang. Bisa jadi bagian dari #2/#3 bila tidak ingin post terpisah. |
| 8 | contoh laporan penjualan harian toko baju | 140 | 18 | `/aplikasi/konveksi` | Rendah. Kandidat bagian dari #4 atau dari post laporan lama. |
| 9 | kartu persediaan metode fifo (+ contoh metode fifo 140) | 320 | 26 | Stok bahan (FIFO) | Rendah. Pencarinya kebanyakan pelajar akuntansi (contoh soal), jadi konversinya kecil. Kerjakan bila slot kosong. |

### C. Halaman komersial (jangka menengah)

| Keyword | Vol | KD | Catatan |
|---|---|---|---|
| aplikasi stok barang gudang | 2,4K | 13 | Sesuai fitur stok per rak/lokasi + QR. Kompetisi iklan rendah (6%), tapi SERP berisi vendor besar. |
| aplikasi stok barang sederhana | 140 | 11 | Sesuai posisi Fabriku untuk UMKM. |
| aplikasi stok barang berbasis web | 50 | 30 | Fakta produk: Fabriku berbasis web. |
| aplikasi stok barang online | 30 | 9 | KD terendah di kelompok ini. |

Usulan: satu landing `/aplikasi/stok-barang` yang menargetkan kelompok ini, dikerjakan setelah authority naik. Butuh keputusan pemilik.

### Ditolak (jangan dikejar)

- Head term: `aplikasi stok barang` (4,4K), `aplikasi toko`, `kartu stok`, `stok opname` (2,4K, KD 26), `laporan penjualan` (1,9K). Authority 4 tidak cukup.
- Varian `gratis` (`aplikasi stok barang gratis` 590, `website toko online gratis` 140): Fabriku berbayar dengan trial, jadi pembaca akan kecewa.
- Intent jasa: `jasa pembuatan website toko online` (320). Pencarinya mencari agensi.
- Di luar kategori: `kartu stok obat`/`apotek` (1,3K), `harga jual rumah`/`tanah`, `stok opname obat`.
- Volume ~0 untuk query niche yang diuji: `hpp konveksi`, `harga jasa servis`, `catatan piutang`, `aplikasi stok opname barang gratis`. Bukan berarti tidak ada pencari, tapi tidak bisa jadi target utama. Pakai sebagai sub-topik.

### Swap keyword RankMySEO (dijalankan 2026-10-27)

Hasil akhir 10 keyword terlacak: `fabriku`, `aplikasi konveksi`, `laporan penjualan sederhana`, `aplikasi stok barang dan penjualan` (masih ada, belum dihapus pemilik), dan 6 yang baru: `cara menghitung hpp dan harga jual`, `contoh laporan penjualan harian`, `kartu stok barang`, `cara menghitung hpp makanan`, `contoh bill of material`, `aplikasi bengkel online`. Dihapus: `aplikasi toko`, `stok barang dan penjualan`, `aplikasi stok barang dan penjualan gratis`, `aplikasi produksi`, `software konveksi`, `stok`. Tabel di bawah adalah usulan awal.

Plan Starter: 10 slot. Menghapus keyword ikut menghapus riwayatnya.

| Status | Keyword |
|---|---|
| Pertahankan | `fabriku`, `aplikasi konveksi`, `software konveksi` (dapat impresi GSC), `laporan penjualan sederhana` |
| Hapus | `aplikasi toko`, `aplikasi stok barang dan penjualan`, `aplikasi stok barang dan penjualan gratis`, `stok barang dan penjualan` |
| Tambah sekarang | `cara menghitung hpp dan harga jual`, `contoh laporan penjualan harian` |
| Tambah setelah post terbit | `kartu stok barang`, `cara menghitung hpp makanan` (ganti `aplikasi produksi` atau slot yang kosong) |
