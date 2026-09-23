# Tinjauan ulang rencana Website Usaha: temuan riset dan rekomendasi

Status: tinjauan, 23 September 2026. **Catatan:** beberapa rekomendasi di bawah tidak dipakai (tema Satsetui tetap dipakai, upgrade prorata bukan konversi sisa hari, domain toko `*.fabriku.biz.id`, seluruh cakupan rilis 31 Oktober tanpa pemecahan). Keputusan final ada di [master-plan.md](master-plan.md#4-daftar-keputusan). Dokumen ini menguji ulang empat rencana di folder ini terhadap kode Fabriku, kode Satsetui (`/home/fauzan/dev/satsetui`), dokumentasi publik Repliz, dan harga pembanding. Bagian **Rekomendasi** adalah pendapat, bukan keputusan yang sudah disetujui.

## Ringkasan pendapat

1. **Arah benar, cakupan terlalu besar untuk satu MVP.** Lima tahap dengan tiga pihak (Fabriku, Satsetui, Repliz) dan migrasi billing sekaligus. Pecah menjadi tiga rilis yang masing-masing bisa dijual sendiri (lihat [Urutan rilis yang diusulkan](#urutan-rilis-yang-diusulkan)).
2. **Rilis pertama cukup Website Usaha native + WhatsApp.** Tanpa Satsetui, tanpa Repliz, tanpa domain sendiri. Pesanan/prospek masuk inbox Fabriku dan pengunjung diarahkan ke WhatsApp toko dengan pesan berisi nomor pesanan. Ini inti nilai: stok dan pesanan satu tempat.
3. **Tunda `business-site-theme-v1` di Satsetui, mungkin tidak perlu sama sekali.** Kontrak tema JSON (warna, font, urutan section, copy) bisa dipenuhi 3–5 template native plus copy dari `OpenAIService` Fabriku yang sudah ada. Kekuatan Satsetui adalah HTML bebas, justru yang rencana ini larang dipakai di storefront. Membangun generator JSON tema baru di Satsetui berarti membuat produk kedua untuk nilai yang tipis.
4. **Satsetui paling berguna untuk Social Kit**, karena pipeline JSON deck → `SocialLayoutEngine` → PNG via Browsershot sudah jalan. Tetapi ada tiga celah yang rencana belum tangkap: tidak ada input foto produk, keluaran PNG (Instagram via Repliz menerima JPEG/WebP), dan caption posting belum ada.
5. **Repliz murah tetapi muda.** Biaya bukan masalahnya; risikonya kematangan vendor. Rancang data sosial agar vendor bisa diganti, jual penjadwalan sebagai fitur pilot/beta dulu.
6. **Billing: sederhanakan.** Dengan harga Core Rp25.000/bulan, mesin prorata dan ledger entitlement penuh terlalu mahal untuk selisih puluhan ribu rupiah. Cukup `plan_code` + konversi sisa hari (rumus di bawah).
7. **Validasi permintaan sebelum membangun.** Belum ada bukti pelanggan Fabriku meminta website. Pasang tombol "Buat website usaha" (fake door) dan wawancara 5–10 tenant aktif dulu.

## Temuan riset

### Repliz

| Temuan | Sumber | Dampak ke rencana |
|---|---|---|
| Paket: Standard Rp18rb/bln (20 akun), Premium Rp29rb (75 akun, Schedule API), Gold Rp49rb (200 akun, akses API penuh termasuk OAuth). Storage add-on Rp39rb/5 GB | [Plans](https://docs.repliz.com/tutorial/plans) | Biaya per tenant hampir nol (Gold ÷ ~100 tenant × 2 akun ≈ Rp500/tenant/bulan). **Batas nyata adalah 200 akun per workspace**, bukan harga. Rencana "biaya tetap Repliz dibagi pada tenant aktif" bisa diganti dengan batas kapasitas: 1 workspace Gold ≈ 100 tenant (IG+FB). |
| API publik diluncurkan **1 Juni 2026**; changelog mingguan | [Changelog](https://docs.repliz.com/api/changelog.html) | Umur API ±4 bulan. Harapkan perubahan breaking; pin perilaku lewat tes kontrak ringan. |
| Dikembangkan perorangan di Indonesia; kontak dukungan alamat Gmail | [Google Play](https://play.google.com/store/apps/details?id=com.repliz.app&hl=en_US), [ToS](https://repliz.com/term-of-service) | Risiko bus factor. Kontrak tertulis (sudah jadi gerbang di rencana) makin penting; siapkan jalan keluar. |
| Tidak ada endpoint webhook; status jadwal perlu `GET /public/schedule/{id}`; ada `retry-schedule` | [Referensi API](https://docs.repliz.com/api/schedule/create-schedule.html) | Fabriku butuh job polling terjadwal untuk status `terbit/gagal`. Masukkan ke Tahap 4. |
| `accountId` tidak bisa diberi referensi eksternal | [OAuth flow](https://docs.repliz.com/api/oauth/oauth-flow) | Pemetaan tenant ↔ akun sepenuhnya di tabel Fabriku (sudah sesuai rencana). Simpan `state` OAuth bertanda tangan berisi tenant agar callback tidak bisa ditukar antar-tenant. |
| Instagram: hanya akun **Business/Creator**, gambar **JPEG/WebP** ≤8 MB, rasio 4:5–1.91:1, caption ≤2.200 karakter, ≤30 hashtag, album 2–10 | [Spesifikasi Instagram](https://docs.repliz.com/tutorial/specification/instagram.html) | Banyak UMKM memakai akun IG pribadi: onboarding harus memeriksa dan menjelaskan konversi ke akun profesional. PNG Satsetui wajib dikonversi. Preset `ig-story` (9:16) tidak valid untuk feed. |
| Schedule API menerima array `medias[].url`; contoh memakai URL storage Repliz, tidak ditegaskan apakah URL eksternal diterima | [Storage upload](https://docs.repliz.com/api/guides/storage-upload) | Uji di pilot. Jika URL eksternal (signed URL S3 Fabriku) diterima, add-on Storage tidak perlu. |
| Ada Chat API, Comment automation, koneksi Shopee/TikTok/Threads | [Instalasi API](https://docs.repliz.com/api/install) | Peluang upsell nanti (balas komentar/DM otomatis), bukan MVP. |

### Pembanding: eksis.io

Diperiksa 23 September 2026 dari [eksis.io](https://eksis.io/), [OpenAPI v1](https://api.eksis.io/v1/openapi.json), dan [ToS](https://eksis.io/terms).

| Aspek | eksis | Repliz |
|---|---|---|
| Operator | PT Eksis Karya Digital, Bogor; `admin@eksis.io`; hukum RI | Perorangan; kontak Gmail |
| Harga | Free (1 akun, 15 posting/bln, tanpa API key), Starter Rp35rb (25 akun), Premium Rp264rb (75), Agency Rp687rb (200) | Gold Rp49rb (200 akun, API penuh) |
| Kualitas API | OpenAPI 3.1, Bearer key hash dengan role, rate limit terdokumentasi (Starter 1.000 req/hari), `GET /v1/capabilities` berisi aturan per platform, satu posting ke banyak target, webhook bertanda tangan untuk hasil publish/komentar/DM | Basic auth, tanpa webhook, tanpa OpenAPI |
| **Menghubungkan akun sosial lewat API** | **Tidak ada.** Endpoint hanya `GET /v1/channels`; channel `needs_reconnect` harus "reconnected in the app" | **Ada** (OAuth connect/reconnect, Gold) |
| Platform tambahan | Google Business Profile, Pinterest, Bluesky | Shopee, TikTok, Threads (keduanya punya IG/FB/TikTok/Threads/Shopee) |

Kesimpulan: API eksis lebih matang dan operatornya badan hukum, tetapi **tidak cocok untuk model "merchant tidak menyentuh vendor"**. Tanpa API OAuth, akun sosial merchant hanya bisa dihubungkan dengan login ke aplikasi eksis. Pada satu workspace bersama, merchant yang login akan melihat channel tenant lain. Pada workspace per merchant, merchant mendaftar dan membayar eksis sendiri (Free tidak punya API key), sehingga prinsip satu tagihan batal.

Posisi eksis dalam rencana:

- **Opsi integrasi "bawa akun sendiri" (BYO)**: merchant yang sudah/ingin memakai eksis menempelkan API key Starter-nya di Fabriku. Isolasi aman karena key terikat satu workspace, tanpa biaya vendor bagi Fabriku, dan webhook menghapus kebutuhan polling. Cocok sebagai fallback bila kontrak Repliz gagal.
- **Kandidat negosiasi**: tanyakan ke eksis apakah ada/akan ada API *connect link* atau sub-workspace untuk partner. Jika ada, eksis layak menggantikan Repliz sebagai penyedia utama.
- **Jangan pakai MCP OAuth eksis sebagai jalur integrasi.** Ia dirancang untuk asisten AI, bukan kontrak integrasi yang stabil.
- Catatan: struktur paket kedua layanan identik (75 akun/10 seat dan 200 akun/30 seat). Pastikan keduanya memang independen sebelum menganggap satu sebagai cadangan yang lain.

### Pembanding: Postiz (self-host)

Diperiksa 23 September 2026 dari [repo](https://github.com/gitroomhq/postiz-app) (AGPL-3.0, ±36rb bintang, aktif), [Public API](https://docs.postiz.com/public-api), dan [panduan provider Facebook](https://docs.postiz.com/providers/facebook).

| Aspek | Temuan | Dampak |
|---|---|---|
| Menghubungkan akun lewat API | Tidak ada. Integrasi harus dihubungkan dari UI Postiz, API hanya mereferensikan ID integrasi | Masalah yang sama dengan eksis. Mengatasinya berarti memodifikasi Postiz |
| Aplikasi developer | Self-host wajib membuat aplikasi Meta sendiri, verifikasi bisnis, App Review dengan screencast, lalu mode Live. Hal serupa untuk TikTok/LinkedIn | Beban terberat dan tidak bisa dihindari. Repliz/eksis sudah menanggungnya |
| Infrastruktur | Postiz + 2 Postgres + Redis + Temporal + Elasticsearch (compose resmi) | Elasticsearch saja butuh RAM gigabyte. Terlalu berat untuk layanan berharga Rp25rb/bulan |
| Batas API | Default 90 create-post/jam per instance (diatur via `API_LIMIT`) | Bisa diatur, tetapi menunjukkan fokusnya pada alat internal, bukan backend multi-tenant |
| Lisensi | AGPL-3.0: pemakaian lewat API tanpa modifikasi aman; bila dimodifikasi (misalnya menambah API connect) dan dipakai pengguna lewat jaringan, source modifikasi wajib dibuka | Bisa diterima, tetapi menambah kewajiban |

Kesimpulan: Postiz **tidak menyelesaikan** masalah utama (connect akun tanpa UI vendor) dan justru menambah dua beban terbesar: App Review Meta dan infrastruktur berat. Jika Fabriku memang harus melewati App Review Meta sendiri, jalur yang lebih ringan untuk cakupan IG/FB adalah **Meta Graph API langsung**: Facebook Login for Business → token Page → IG `media` container + `media_publish`, FB `/{page-id}/photos`. Penjadwalan cukup dengan job tertunda di queue Laravel yang sudah ada, status terbit langsung diketahui, dan tidak ada vendor. Postiz baru masuk akal bila cakupan melebar ke banyak platform sekaligus dan ada kapasitas ops.

### Satsetui

| Temuan (kode) | Dampak |
|---|---|
| Social Kit: LLM menghasilkan JSON deck, `SocialLayoutEngine` merender HTML, `SocialImageService` membuat PNG via Browsershot. Preset `ig-square` 1080², `ig-portrait` 1080×1350, `ig-story`, `linkedin`, `twitter` | Aset nyata yang layak dipakai ulang. Untuk IG/FB feed pakai `ig-portrait` atau `ig-square`. |
| `StoreSocialKitRequest` tidak menerima gambar; slide `image-focus` memakai placeholder | **Celah terbesar untuk UMKM:** promosi produk tanpa foto produk asli nyaris tidak berguna. `social-kit-v1` harus menerima 1–N URL gambar bertanda tangan dari Fabriku sebagai prioritas P0, bukan "jika diizinkan". |
| Deck punya `note` per slide ("Caption/script note"), bukan caption posting | Buat caption di Fabriku lewat `OpenAIService` yang sudah ada; jangan menambah kontrak Satsetui untuk caption. |
| Social Kit/CV/presentasi berjalan berurutan; kelanjutan dipicu SSE atau `continueInBackground` → `ProcessTemplateGeneration` | Klaim rencana benar: API headless butuh dispatch job saat create, bukan membuka controller web. |
| Satsetui punya kategori link-in-bio, menu digital, undangan; "opsi terbitkan permanen" masih keputusan terbuka di `docs/plan.md` Satsetui | **Risiko kanibalisasi antar-produk sendiri.** Putuskan: Fabriku memiliki situs usaha yang terhubung data (stok, order); Satsetui tetap alat desain. Jika Satsetui menerbitkan link-in-bio permanen, posisikan untuk non-UMKM-operasional. |

### Fabriku (kode)

| Temuan | Dampak |
|---|---|
| `SalesOrder::generateOrderNumber()` membaca `auth()->user()->tenant_id` dan memakai `latest('id') + 1` tanpa lock | Checkout publik perlu parameter tenant eksplisit **dan** retry saat `UniqueConstraintViolationException`; order publik bersamaan lebih mungkin bentrok daripada input staf. |
| `Customer::booted()` mengisi `tenant_id` hanya bila `auth()->check()` | Checkout publik wajib set `tenant_id` sendiri; tambahkan dedupe pelanggan per tenant berdasarkan nomor HP yang dinormalisasi (`08…` → `628…`). |
| `TenantScope` tidak menyaring apa pun tanpa user login | Sesuai catatan rencana. Rekomendasi: controller storefront memakai satu helper `forStorefront($tenant)` agar lupa filter menjadi mustahil secara struktur, plus tes kebocoran dua tenant per endpoint. |
| SSR Inertia sudah hidup di produksi, tetapi `docs/deployment.md` mencatat bila proses SSR mati, situs tetap jalan tanpa HTML awal | Storefront merchant sebaiknya **Blade** (HTML server murni + sedikit JS), bukan halaman Inertia. Ringan di HP murah, SEO tidak bergantung proses Node, dan terisolasi dari bundle aplikasi admin. |
| Harga Core default Rp25.000/bulan, Rp250.000/tahun (`SubscriptionService`) | Pembanding: OrderOnline Personal Rp149rb/bln ([pricing](https://orderonline.id/pricing/)), add-on majoo Rp499rb/outlet/bln per integrasi ([sumber](https://www.hashmicro.com/id/blog/aplikasi-majoo/)), Kasir Pintar Rp55,5rb dengan Olshopin gratis ([Olshopin](https://kasirpintar.co.id/olshopin)). Online di kisaran **Rp69–99rb/bulan** masih jauh di bawah pembanding dan memberi ruang margin; angka final tetap menunggu pilot. |
| Domain produksi sudah pindah ke `fabriku.id` (commit `2b6ccee`) | Pertanyaan "fabriku.id atau fabriku.web.id" di rencana branding sudah terjawab. Subdomain merchant: `*.fabriku.id` atau domain terpisah (lihat di bawah). |

## Rekomendasi per area

### Produk: WhatsApp adalah jalur penutupan transaksi

UMKM Indonesia menutup transaksi di WhatsApp. Rencana menolak "WhatsApp API berbayar" (benar), tetapi melewatkan versi gratisnya: setelah `Kirim pesanan`/`Kirim permintaan`, tampilkan tombol `Lanjut ke WhatsApp` berisi tautan `wa.me/<nomor toko>?text=<ringkasan + nomor pesanan>`. Biaya nol, tanpa API, dan merchant langsung menerima notifikasi di aplikasi yang memang dipakai. Order tetap tercatat di Fabriku lebih dulu, sehingga tidak ada order yang hanya hidup di chat. Email/Telegram tetap sebagai notifikasi staf, bukan satu-satunya.

### Katalog ke stok: aturan alokasi paling sederhana

Katalog publik dikelompokkan per `product_code` (satu kartu produk walau stok terpecah per rak/batch). Saat checkout, sistem memilih `inventory_item` dengan aturan tetap: FEFO bila ada `expired_date` (kategori food), selain itu `available_stock` terbesar. Order dibuat `draft` tanpa reservasi (sesuai observer yang ada); reservasi terjadi saat staf konfirmasi. Tampilkan stok sebagai "Tersedia/Habis", bukan angka, agar selisih kecil antara draft dan konfirmasi tidak menjadi janji.

### Domain dan TLS

- Rilis 1: hanya subdomain. Pertimbangkan domain terpisah untuk situs merchant (pola `*.myshopify.com`) agar cookie sesi, reputasi, dan kebijakan keamanan `fabriku.id` terisolasi dari konten merchant. Jika tetap `*.fabriku.id`, pastikan cookie sesi aplikasi tidak di-scope ke `.fabriku.id`.
- Rilis 2: domain sendiri lewat Caddy on-demand TLS dengan endpoint `ask` yang hanya menyetujui hostname terverifikasi, atau Cloudflare for SaaS (custom hostnames). Pilih sesuai reverse proxy yang dipakai produksi saat itu.

### Satsetui API: internal dulu, publik nanti

Rencana API sudah rapi, tetapi membuka API publik untuk integrator lain adalah produk tersendiri (OpenAPI, quickstart tiga bahasa, webhook, estimasi, kebijakan deprecation). Untuk Fabriku cukup:

- Satu akun layanan + kunci hash dengan scope `generation:create/read`, idempotensi persisten, dispatch job saat create, `GET` status/artefak dibatasi pemilik kunci.
- Hanya profil `social-kit-v1` dengan input foto dan keluaran per slide (JPEG atau PNG + konversi di Fabriku).
- Tunda `POST /estimate`, webhook, `website-html-v1`, dokumentasi publik, sampai ada integrator kedua yang nyata. Desain tabel kunci API sudah cukup umum untuk dibuka nanti tanpa migrasi ulang.

Audit penguncian saldo di jalur refund `CreditService` (sudah disebut rencana) tetap wajib sebelum kunci pertama dibuat.

### Repliz: pakai, tetapi siap ganti

- Tabel `social_accounts` dan `social_posts` Fabriku memakai kolom `provider` + `provider_account_id`/`provider_schedule_id`, bukan kolom bernama `repliz_*`. Tidak perlu interface/adapter dulu; cukup data yang tidak mengunci vendor.
- Jalan keluar bila Repliz berhenti atau kontrak gagal, berurutan: (1) integrasi BYO eksis untuk merchant yang mau ([pembanding](#pembanding-eksisio)); (2) Meta Graph API langsung untuk IG/FB. Mulai proses verifikasi bisnis Meta paralel sejak pilot karena memakan waktu minggu. Postiz tidak direkomendasikan ([pembanding](#pembanding-postiz-self-host)). Catat sebagai rencana darurat, jangan dibangun sekarang.
- Satu workspace Gold untuk pilot, alarm saat mendekati 180 akun.
- Pasarkan penjadwalan sebagai "beta" selama pilot. Tetap tawarkan unduhan gambar + caption untuk posting manual: berguna untuk akun IG pribadi yang tidak bisa dihubungkan, dan tetap berjalan bila Repliz gangguan.

### Billing: konversi sisa hari, bukan invoice prorata

Rencana mengusulkan invoice selisih prorata dan ledger entitlement dengan snapshot harga. Untuk selisih Rp44–74rb per bulan, itu terlalu banyak mesin. Alternatif yang cukup:

- Tambah `tenants.plan_code` (`core`/`online`), migrasikan `full` → `core` tanpa mengubah expiry. `SubscriptionPayment` sudah menyimpan jumlah dan durasi per transaksi, jadi snapshot harga historis sudah ada.
- Upgrade di tengah periode: pelanggan membayar satu periode Online penuh; sisa nilai Core dikonversi menjadi hari Online tambahan.
  `hari_bonus = floor(sisa_hari_core × harga_harian_core / harga_harian_online)`; `expires_at_baru = sekarang + periode_online + hari_bonus`.
  Tidak ada invoice parsial, tidak ada nilai negatif, dan mudah dijelaskan di satu kalimat pada halaman bayar.
- Kuota (generasi Social Kit, jadwal/bulan, akun sosial) dari `config/plans.php` per `plan_code`; konsumsi dicatat di satu tabel `usage_events` (tenant, jenis, periode, `idempotency_key` unik, `reversed_at`). Tidak perlu tabel entitlement terpisah sampai ada paket ketiga.
- Downgrade/habis: situs tetap tampil dengan banner "toko sedang tidak menerima pesanan" selama masa tenggang, lalu formulir ditutup. Jadwal Repliz yang jatuh sesudah tanggal habis dibatalkan otomatis H-1 dengan email pemberitahuan.

### SEO dan branding

- Rencana branding sudah hati-hati; perbaikan faktualnya hanya domain (sudah `fabriku.id`).
- Tambahkan satu keputusan: situs merchant di subdomain berbagi domain induk dengan situs marketing. Konten tipis merchant dapat memengaruhi persepsi kualitas domain. Ini alasan kedua (selain keamanan) untuk domain storefront terpisah, atau `noindex` default sampai konten minimum terisi, yang sudah disebut di rencana.
- Setiap situs merchant yang terbit dengan izin dapat menjadi halaman "contoh" di `/fitur/website-usaha`. Itu satu-satunya bukti sosial yang tidak dikarang.

## Urutan rilis yang diusulkan

| Rilis | Isi | Tidak termasuk | Estimasi kasar* |
|---|---|---|---|
| **0. Validasi** (1–2 minggu) | Tombol "Buat website usaha" di dashboard yang mencatat klik dan membuka form minat; wawancara 5–10 tenant (produk, jasa, gabungan) | Kode fitur | 1–2 hari kerja |
| **1. Website Usaha** | Subdomain, storefront Blade 3–5 template, mode produk/jasa/gabungan, katalog `product_code`, checkout → order `draft`, prospek jasa, inbox, email/Telegram staf, tombol WhatsApp, `plan_code` + konversi hari, halaman fitur `noindex` sampai demo siap | Satsetui, Repliz, domain sendiri, AI | 4–6 minggu |
| **2. Online penuh** | Domain sendiri + TLS, copy/section dari `OpenAIService`, SEO merchant (sitemap per host, `Product` markup), kuota `usage_events` | Social | 3–4 minggu |
| **3. Social (beta)** | Satsetui API internal `social-kit-v1` dengan foto produk; caption di Fabriku; unduh manual; koneksi IG/FB via Repliz; jadwal + polling status | API publik Satsetui, auto-post, inbox komentar | 4–6 minggu (dua repo) |

\* Estimasi satu developer dengan asisten AI, berdasarkan ukuran fitur serupa di riwayat repo (modul service, split rak). Kalibrasi ulang setelah rilis 1.

Gerbang harga Online dipindah: nominal diumumkan di akhir Rilis 1 berdasarkan biaya hosting + support nyata; tambahan biaya AI/Repliz dihitung ulang di Rilis 3. Rilis 1 sudah layak ditagih karena tidak bergantung pada vendor.

## Keputusan yang perlu diambil pemilik produk

1. Setuju memecah menjadi tiga rilis dan mengeluarkan Satsetui/Repliz dari rilis pertama?
2. Drop atau tunda `business-site-theme-v1`?
3. Domain storefront: `*.fabriku.id` atau domain terpisah?
4. Kisaran harga Online untuk diuji di wawancara (usulan Rp69–99rb/bulan)?
5. Satsetui API: internal-only dulu atau langsung publik?
6. Siapa pemilik produk "halaman usaha yang di-hosting" antara Fabriku dan Satsetui?
