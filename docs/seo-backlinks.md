# Rencana backlink fabriku.id

Disusun 2026-10-27. Status awal: 3 referring domain (RankMySEO), authority 4. Tautan ke Fabriku yang sudah ada: `suaranetijen.id` (sponsor), `dsfahomemade.com` (atribusi toko).

Pembagian kerja: pencarian peluang dikerjakan agent, **pendaftaran akun dan submit dikerjakan pemilik**. Tandai "live" hanya setelah halaman listing benar-benar terbuka dan memuat tautan.

Catatan jujur tentang nilainya: kebanyakan direktori memberi tautan `nofollow` atau bobot kecil, dan ini belum saya verifikasi satu per satu. Yang paling bernilai adalah tautan editorial dari situs yang relevan dan tautan dari properti milik sendiri. Direktori tetap berguna untuk mendapat domain pengarah yang bervariasi dan pencarian merek.

## Urutan pengerjaan

| # | Peluang | Perlu akun | Perkiraan waktu | Prioritas |
|---|---|---|---|---|
| 1 | Tautan dari `satsetui.com` dan `satsetops.com` | Tidak, situs milikmu | 10 menit | Tinggi. Dicek 2026-10-27: keduanya belum menautkan ke fabriku.id. |
| 2 | Artikel tamu atau tulisan di Kompasiana, LinkedIn Article, atau Medium dengan tautan ke post blog | Ya | 1–2 jam per tulisan | Tinggi. Tautan editorial paling mirip nilai SEO nyata. |
| 3 | Product Hunt | Ya | 30 menit | Sedang |
| 4 | AlternativeTo | Ya (login Google/GitHub/Apple) | 15 menit | Sedang |
| 5 | Crunchbase, F6S, Indie Hackers | Ya | 15 menit masing-masing | Rendah–sedang |
| 6 | Grup komunitas UMKM (Facebook, Telegram, Kaskus UKM) | Akun pribadi | Berkelanjutan | Rendah untuk SEO, sedang untuk trafik |
| 7 | BetaList | Ya | 15 menit | Rendah. Antrean gratis bisa lebih dari 2 bulan. |

### Tidak dikerjakan
- **SaaSHub:** halaman submit-nya (dicek 2026-10-27) menolak produk yang bukan berbahasa Inggris.
- **Layanan submit otomatis ke 100+ direktori** (SubmitDirs, SubmitSaaS, dan sejenisnya): kualitas rendah dan berisiko dianggap link spam oleh Google.
- **Google Business Profile:** butuh alamat nyata yang bisa diverifikasi, dan bisnis software tanpa layanan lapangan belum tentu memenuhi syarat kategori. Daftarkan hanya bila ada alamat kantor sungguhan.
- **Footer "Powered by Fabriku" massal di situs tenant:** bisa dibaca sebagai skema tautan. Biarkan satu tautan merek biasa, tanpa anchor kata kunci.

## 1. Tautan dari properti sendiri

Tambahkan satu tautan teks biasa di footer atau halaman "Produk kami" di `satsetui.com` dan `satsetops.com`:

- URL: `https://fabriku.id`
- Anchor: `Fabriku`, atau `Fabriku, aplikasi produksi dan stok untuk UMKM`. Jangan memakai anchor yang diulang-ulang berisi kata kunci.

### Status tautan dari situs satu server (2026-10-27)

Footer menautkan ke `https://fabriku.id/?utm_source=<situs>&utm_medium=footer` dengan anchor "Fabriku". Sudah di-commit di repo masing-masing, **belum live** sampai di-deploy:

| Situs | Repo | Commit footer | Status |
|---|---|---|---|
| aspriai.web.id | aspri | `d3de800` | Live 2026-10-27 (image `azmifauzan/aspri:271026-1`) |
| yahoot.web.id | yahoot | `b0bf075` | Live 2026-10-27 (image `azmifauzan/yahoot:271026-1`) |
| santriq.web.id | santriq | `afdb9c5` | Belum live: compose server memakai `pull_policy: never` |
| satsetui.com | satsetui | `2873bff` | Belum live: compose server memakai `pull_policy: never` |

Footer aspri dan yahoot dirender di sisi klien (Vue) dan app-nya tidak memakai SSR, jadi tautannya tidak ada di HTML mentah. Googlebot merender JavaScript sehingga tetap terbaca, tapi lebih lemah dibanding tautan di HTML server.

Server menjalankan image `hermes-local/<app>:php-security-20261022` (hasil patch keamanan PHP). aspri dan yahoot sudah diganti ke image registry dengan versi PHP yang sama (8.4.27); backup compose ada di `docker-compose.yml.before-271026-1` di folder masing-masing. santriq dan satsetui sengaja hanya memakai image lokal (`pull_policy: never`), jadi belum diganti: butuh keputusan apakah image baru dimuat langsung ke server (`docker load`) atau pull_policy diubah.

`satsetops.com` dan `suaranetijen.id` punya source lokal tapi tidak berjalan di server Fabriku, dan belum diubah (suaranetijen sudah menautkan ke Fabriku lewat sponsor).

## 2. Tulisan di platform artikel

Gunakan topik yang sudah terbukti punya pencarian, dan tautkan ke post blog yang paling relevan, bukan ke homepage:

| Topik | Tautkan ke |
|---|---|
| Cara menghitung HPP dan harga jual | `/blog/cara-menghitung-hpp-produk-umkm` |
| Contoh kartu stok barang | `/blog/kartu-stok-barang-contoh-cara-mengisi-template-excel` |
| HPP makanan per porsi | `/blog/cara-menghitung-hpp-makanan-per-porsi` |

Tulis versi yang lebih ringkas dan ditulis ulang, bukan salinan penuh dari blog. Salinan identik bisa dianggap konten duplikat. Kompasiana menerima akun gratis; baca aturan penayangan dan tautan keluarnya sebelum mengirim.

## 3. Product Hunt (https://www.producthunt.com/posts/new)

Perlu akun. Isian berbahasa Inggris:

- **Name:** Fabriku
- **Tagline (60 karakter):** Production, stock and sales tracking for small businesses
- **Description:**
  > Fabriku helps Indonesian small businesses (garment, food, craft, cosmetics, retail, home production and service shops) record raw materials, production, stock and sales in one place, then export reports to Excel or PDF. It also builds a simple business website that takes product orders or service requests.
- **Topics:** Productivity, Small Business, Inventory Management
- **Link:** `https://fabriku.id?utm_source=producthunt&utm_medium=listing`

Sebelum submit, pastikan setiap kemampuan yang disebut benar-benar ada di produk saat itu.

## 4. AlternativeTo (https://alternativeto.net/login/?returnTo=%2Fmanage-item%2F)

Login dengan Google, GitHub, atau Apple, lalu tambahkan aplikasi baru.

- **Name:** Fabriku
- **Website:** `https://fabriku.id?utm_source=alternativeto&utm_medium=listing`
- **Short description (ID):**
  > Aplikasi produksi, stok, dan penjualan untuk UMKM. Catat bahan baku, produksi, stok barang jadi, pesanan, dan piutang, lalu ekspor laporan ke Excel atau PDF. Tersedia website usaha untuk menerima pesanan produk atau permintaan jasa.
- **Platform:** Web
- **Lisensi:** Berbayar dengan masa percobaan, sesuai kebijakan paket yang berlaku saat submit.
- **Alternatif yang dituju:** pilih hanya produk yang memang sebanding fiturnya. Jangan menyatakan Fabriku lebih unggul tanpa audit terkini.

## 5. Crunchbase, F6S, Indie Hackers

Teks pendek yang bisa dipakai di ketiganya:

> Fabriku adalah aplikasi produksi, stok, dan penjualan untuk UMKM Indonesia. Satu akun mengelola bahan baku, produksi, stok barang jadi, pesanan, piutang, dan laporan, dengan istilah yang menyesuaikan jenis usaha (garment, makanan, kerajinan, kosmetik, retail, produksi rumahan, jasa).

- Crunchbase dan F6S: profil perusahaan, kolom website `https://fabriku.id`.
- Indie Hackers: tulis cerita singkat pembuatan Fabriku dan satu pelajaran nyata, bukan iklan. Cantumkan tautan di akhir.

## 6. Komunitas UMKM

Aturan supaya tidak dianggap spam: bergabung dan aktif lebih dulu, jawab pertanyaan nyata, baca aturan grup, dan bagikan tautan hanya bila memang menjawab pertanyaannya. Gunakan tautan UTM, misalnya `?utm_source=facebook_group&utm_medium=community`, supaya trafiknya terlihat di GA4.

## Pelacakan

Setelah tiap listing live, catat di tabel ini dan cek RankMySEO "Backlink review" sebulan kemudian.

| Situs | Tanggal live | URL listing | Status |
|---|---|---|---|
| satsetui.com | | | Belum |
| satsetops.com | | | Belum |
| Product Hunt | | | Belum |
| AlternativeTo | | | Belum |
| Crunchbase | | | Belum |
| F6S | | | Belum |
| Indie Hackers | | | Belum |
| Kompasiana | | | Belum |
