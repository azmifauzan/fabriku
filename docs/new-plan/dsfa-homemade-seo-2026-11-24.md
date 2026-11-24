# Perbaikan SEO DSFA Homemade — 24 November 2026

**Status:** **Live di production & terverifikasi (`2026-11-24`)** — image `azmifauzan/fabriku:241126-3` (commit `1df0ec1`, digest `sha256:898f72dd3e448fb6bc6f40c2cc5ad4ad795e8828de61c3b7b14173f931eea401`), site 1 / tenant 7 diperbarui ke `active_theme_version_id = 22` (rollback tema: `21`, rollback image: `241126-2`, backup: `/home/ubuntu/fabriku/backups/241126-3/`). Seluruh 28 URL `dsfahomemade.com` dan SSR `fabriku.id` lulus verifikasi live. Siap untuk pengajuan indeks ulang oleh pemilik.

---

## 1. Ringkasan Perubahan (Change Log)

| Tanggal | Halaman / Berkas | Apa yang Diubah | Alasan (Bukti) | Efek yang Diharapkan | Metrik yang Dicek | Tanggal Cek Ulang | Hasil |
| --- | --- | --- | --- | --- | --- | --- | --- |
| 2026-11-24 | RankMySEO (`dsfahomemade.com`) | Menambahkan 4 keyword pelacakan baru (`cheesecuit oreo`, `harga cheesecuit`, `cheesecuit kecil`, `jus homemade bandung`, `id`/`id`) | `cheesecuit oreo` punya 17 impresi tertinggi di GSC (pos. 39 RankMySEO, vol. 720); `harga cheesecuit` (8.100) & `cheesecuit kecil` (480) adalah celah kompetitor non-merek; kategori jus belum terpantau | Visibilitas peringkat mingguan untuk 4 keyword niat beli utama | RankMySEO `list_keywords` (slot terpakai 47/50) | 2026-12-08 | |
| 2026-11-24 | `/produk`, `/privasi`, `/produk/{slug}` (`StorefrontController.php`, `products.blade.php`, `product-detail.blade.php`) | Perpanjang judul `/produk` (`Katalog Produk & Harga - DSFA Homemade`), `/privasi` (`Kebijakan Privasi - DSFA Homemade`), dan judul produk <32 karakter (`Jus Mix Berry - Katalog DSFA Homemade`); tambahkan intro katalog + tautan halaman terbit di `/produk` (mengatasi orphan page `/halaman/camilan-homemade-vs-kemasan-pabrik`); tambahkan paragraf rapi & tautan silang `"Pilihan varian lain"` di halaman produk | Audit RankMySEO 2026-11-20 menandai `title_short` di 3 halaman, `/produk` tipis (92 kata) & 0 tautan dari `/`, `/halaman/camilan-homemade-vs-kemasan-pabrik` 0 tautan masuk, dan varian terpisah butuh tautan silang | Hilangnya 3 warning `title_short`, terindeksnya artikel orphan, dan peningkatan sinyal internal antar-varian | RankMySEO `site_issues`, GSC impresi `/produk` & `/halaman/camilan-homemade-vs-kemasan-pabrik` | 2026-12-08 *(setelah deploy)* | |
| 2026-11-24 | 6 halaman `/halaman/*` + shell header/footer (`docs/new-plan/dsfa-homemade/theme.php`, `theme.css`) | Refresh `cheesecuit-bandung` (tabel 150 ml Rp 7.000 vs 250 ml Rp 10.000, daya tahan 12 jam/48 jam/7 hari), `jual-takoyaki-bandung` (tabel porsi isi 4/8/12 tanpa fokus `"kemasan"`), `puding-buah-bandung` (perbandingan cup 150 ml vs besar open order 3 hari), `jus-homemade-bandung` (tabel 3 varian), `cara-pesan` (format WA + beda siap antar vs open order), `area-pengiriman` (catatan antar & suhu ruang); tautkan `/produk` dari header, hero, & footer | 4 halaman `/halaman/*` ditandai `content_count_words` (112–181 kata), `cheesecuit-bandung` menarget keyword 12.100 vol/bln dengan 8 impresi & 0 klik, dan celah konten utama dibanding `bandungcheesecuit.com` adalah tabel harga + daya tahan | Seluruh halaman `/halaman/*` melewati 220+ kata faktual dan meningkatkan relevansi untuk pencarian harga/menu/daya tahan | GSC impresi & CTR 28 hari, RankMySEO `site_issues` & `list_keywords` | 2026-12-08 *(2 cek mingguan pasca-deploy)* | |
| 2026-11-24 | 14 deskripsi produk (`docs/new-plan/dsfa-homemade/seo-products.php`) | Memperbarui seluruh 14 deskripsi produk dengan fakta terkonfirmasi (cheesecuit 150 ml / 250 ml & daya tahan 12 jam/48 jam/7 hari, porsi takoyaki 4/8/12 dibedakan per kebutuhan, puding cup 150 ml vs open order 3 hari, 3 jus botol) dan ringkasan 155 karakter pertama untuk meta description | Semua 14 halaman produk ditandai `content_count_words` (40–124 kata), `/produk/cheesecuit-kecil-oreo` adalah halaman impresi tertinggi (17 impr) dan muncul di pos. 39 untuk `cheesecuit oreo` | Konten produk unik per varian tanpa duplikasi tipis dan meta description yang jelas | GSC impresi/klik halaman `/produk/*` & RankMySEO `cheesecuit oreo` | 2026-12-08 *(2 cek mingguan pasca-deploy)* | |

---

## 2. Rilis, Verifikasi Live, dan Rollback (`2026-11-24`)

- **Rilis kode:** `azmifauzan/fabriku:241126-3` (`Up (healthy)`, `NO_PENDING_MIGRATIONS`, proses `inertia:start-ssr`, `queue:work`, dan `schedule:work` aktif; `fabriku.id` `/` dan `/blog` keduanya merender `<h1>` dan `canonical` SSR).
- **Sinkronisasi tenant `dsfahomemade.com` (site 1, tenant 7):** `active_theme_version_id` naik dari `21` ke `22`, `8` halaman `/halaman/*` diperbarui (`9` total halaman terbit termasuk `camilan-homemade-vs-kemasan-pabrik`), `14` deskripsi produk diperbarui tanpa mengubah harga/stok/pesanan/tenant lain.
- **Verifikasi live 28 URL:** seluruh 25 URL sitemap + `/privasi`, `/keranjang`, `/lapor` menjawab HTTP `200` (0 error), seluruh 26 halaman terindeks memiliki judul 33–58 karakter, 1 `<h1>`, self-canonical, dan JSON-LD valid; `/` menaut ke `/produk`, `/produk` menaut ke `/halaman/camilan-homemade-vs-kemasan-pabrik`, produk varian saling tertaut, dan `/keranjang` + `/lapor` tetap `X-Robots-Tag: noindex, nofollow`.
- **Backup & rollback:** `/home/ubuntu/fabriku/backups/241126-3/docker-compose.yml.before` dan `dsfa-before.json`. Rollback kode ke `azmifauzan/fabriku:241126-2`; rollback tema DSFA ke `active_theme_version_id = 21`.
- **Langkah manual RankMySEO:** hapus keyword typo `cheseecuit homemade bandung` di dashboard RankMySEO untuk membebaskan 1 slot tambahan.

---

## 3. Handoff Indeksasi (`seo-indexing` — Jalankan Setelah Batch Live di Production)

**Sitemap:** `https://dsfahomemade.com/sitemap.xml`  
**Batch URL prioritas yang diperbarui (`10` halaman utama dari 22 halaman refresh):**
- `https://dsfahomemade.com/`
- `https://dsfahomemade.com/produk`
- `https://dsfahomemade.com/produk/cheesecuit-kecil-oreo`
- `https://dsfahomemade.com/produk/cheesecuit-besar-oreo`
- `https://dsfahomemade.com/halaman/cheesecuit-bandung`
- `https://dsfahomemade.com/halaman/jual-takoyaki-bandung`
- `https://dsfahomemade.com/halaman/puding-buah-bandung`
- `https://dsfahomemade.com/halaman/jus-homemade-bandung`
- `https://dsfahomemade.com/halaman/cara-pesan`
- `https://dsfahomemade.com/halaman/camilan-homemade-vs-kemasan-pabrik` *(kini tertaut dari `/produk`)*

### Daftar Kemampuan (Dicek 2026-11-24)

| Langkah | Bisa dilakukan agen? | Pelaksana |
| --- | --- | --- |
| Google Search Console: submit sitemap | Tidak (RankMySEO `search_console` hanya baca) | Pemilik |
| Google Search Console: Request indexing | Tidak (alasan yang sama) | Pemilik |
| Bing Webmaster Tools: submit sitemap | Tidak (tidak ada konektor Bing) | Pemilik |
| IndexNow: kirim batch URL | Ya, dengan kunci IndexNow di root situs (`/{key}.txt`) & persetujuan pemilik | Agen (atas izin) atau Pemilik |
| Google Business Profile | Tidak *(profil `/g/11z_cldz3p` sudah terverifikasi sebelumnya)* | Pemilik |

### Pengecekan Pra-Submit (Jalankan Begitu Deploy Live)
- Setiap URL batch menjawab HTTP `200` (tanpa redirect chain / 404).
- Tidak ada `noindex`, canonical menunjuk ke URL sendiri, dan diizinkan di `robots.txt`.
- Semua URL tercantum di `https://dsfahomemade.com/sitemap.xml` dan tertaut dari `/` atau `/produk`.
- Schema JSON-LD (`Organization`, `Product`/`Offer`, `BreadcrumbList`) valid tanpa error.

### Langkah Pemilik Setelah Live
1. **Google Search Console (`sc-domain:dsfahomemade.com`):** pastikan sitemap `https://dsfahomemade.com/sitemap.xml` terbaca di laporan **Sitemaps** (jangan submit berulang jika file belum berubah).
2. **URL Inspection (Google Search Console):** inspeksi dan klik **Request indexing** untuk beranda `/` dan 5–10 halaman prioritas di atas (jangan minta semua 28 URL sekaligus; Google memproses dalam hitungan hari hingga beberapa minggu dan tidak menjamin tanggal pasti).
3. **Bing Webmaster Tools:** pastikan situs terverifikasi dan sitemap `https://dsfahomemade.com/sitemap.xml` sudah dikirim.
4. *(Jangan gunakan Google Indexing API karena dibatasi hanya untuk lowongan kerja dan livestream.)*

### Status Pengajuan & Jadwal Cek Ulang (Tanggal Submit `T = 2026-11-24`)

| Layanan | Isi yang Diajukan | Pelaksana | Tanggal | Status | Cek Berikutnya |
| --- | --- | --- | --- | --- | --- |
| **Google Search Console** | Sitemap `https://dsfahomemade.com/sitemap.xml` & *Request indexing* batch 10 URL prioritas | Pemilik | 2026-11-24 | **implemented but unverified** | **2026-11-27** (Hari ke-3) |
| **Bing Webmaster Tools** | Sitemap `https://dsfahomemade.com/sitemap.xml` | Pemilik | — | **blocked** *(menunggu submit pemilik)* | Setelah submit pemilik |
| **IndexNow** | Batch 10 URL prioritas | Agen / Pemilik | — | **blocked** *(menunggu file kunci `/{key}.txt` & izin pemilik)* | Opsional |
| **Google Business Profile** | Penyesuaian mode Service-Area / alamat, verifikasi klaim, & ulasan (`/g/11z_cldz3p`) | Pemilik | — | **blocked** *(butuh akses pemilik di `business.google.com`)* | Bulanan |

- **Hari ke-3 (`2026-11-27`):** cek URL Inspection di Google Search Console untuk URL prioritas utama (`/` , `/produk`, `/produk/cheesecuit-kecil-oreo`, `/halaman/cheesecuit-bandung`, `/halaman/camilan-homemade-vs-kemasan-pabrik`).
- **Hari ke-7 (`2026-12-01`):** cek status laporan Sitemaps di GSC dan status indeks di Bing.
- **Hari ke-14 (`2026-12-08`):** jalankan `/seo check dsfahomemade.com` untuk mengevaluasi 4 keyword baru di RankMySEO (`cheesecuit oreo`, `harga cheesecuit`, `cheesecuit kecil`, `jus homemade bandung`), status `site_issues`, dan impresi Search Console (setelah 2 pemeriksaan mingguan).
- **Hari ke-21 (`2026-12-15`):** bila ada halaman yang belum terindeks, jalankan diagnosis `seo-go-live-check` tanpa mengulang submit dalam loop. Google menyatakan pengindeksan halaman baru/diperbarui memerlukan 1–2 hari hingga beberapa minggu.
