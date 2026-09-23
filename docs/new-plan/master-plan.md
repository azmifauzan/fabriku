# Master Plan: Website Usaha dan Pusat Sosial Fabriku

Status: rencana kerja final, diputuskan bersama pemilik produk pada 23 September 2026. Target rilis: **31 Oktober 2026**. Dokumen lain di folder ini adalah lampiran riset/detail; bila bertentangan, dokumen ini yang berlaku.

## 1. Ringkasan

Fabriku menambah dua kemampuan untuk tenant:

1. **Website Usaha**: storefront untuk usaha produk, landing page untuk usaha jasa, atau gabungan keduanya, di `namatoko.fabriku.biz.id` atau domain sendiri. Tampilan dibuat dengan Satsetui (katalog template khusus Fabriku atau desain AI per tenant) dan bisa diedit lagi di Fabriku. Produk, stok, dan layanan diambil langsung dari data Fabriku. Pesanan dan permintaan jasa masuk ke Fabriku, lalu dilanjutkan lewat WhatsApp.
2. **Pusat Sosial**: menghubungkan Instagram dan Facebook usaha lewat Repliz, lalu memantau statistik, konten, komentar, dan DM di satu tempat. Komentar atau DM bisa diubah menjadi pesanan atau prospek. Tenant juga bisa menjadwalkan posting dan membuat konten visual dengan AI.

Website Usaha dari template katalog termasuk langganan Core. Desain unik (tema AI), domain sendiri, Tulis dengan AI, dan seluruh Pusat Sosial dijual sebagai **add-on Fabriku Pro**. Tenant hanya berurusan dengan Fabriku: satu akun, satu tagihan. Satsetui dan Repliz bekerja di belakang layar.

## 2. Tujuan

| # | Tujuan | Ukuran |
|---|---|---|
| G1 | Tenant punya halaman online yang terhubung ke data operasionalnya dengan cepat | Median waktu aktivasi sampai terbit < 30 menit |
| G2 | Pesanan dan permintaan jasa dari luar tidak tercecer | ≥ 90% pesanan/prospek ditindaklanjuti staf dalam 24 jam |
| G3 | Pemilik usaha memantau akun sosialnya tanpa berpindah aplikasi | Tenant membuka inbox sosial Fabriku ≥ 3 hari per minggu |
| G4 | Minat beli di sosial media menjadi transaksi tercatat | Jumlah pesanan/prospek dari komentar/DM per tenant per bulan |
| G5 | Add-on Fabriku Pro menambah pendapatan dengan margin sehat | Persentase tenant Core yang membeli Pro dan margin per tenant setelah biaya Repliz/AI/hosting |

**North star:** jumlah permintaan valid (pesanan website, prospek jasa, pesanan dari komentar/DM) yang ditindaklanjuti per tenant per bulan.

### Di luar cakupan

- Pembayaran online/payment gateway untuk pembeli. Pembayaran di luar sistem, dicatat staf.
- Ongkir otomatis, sinkron stok marketplace, multi-toko per tenant.
- Booking kalender jasa otomatis.
- Chatbot, balasan otomatis, dan automasi komentar/DM.
- Editor HTML/CSS bebas; pengeditan hanya pada elemen bertanda (lihat 6.2).
- Platform sosial selain Instagram dan Facebook.
- API Satsetui publik untuk integrator lain.
- WhatsApp Business API berbayar.

## 3. Target pengguna

| Segmen | Kategori Fabriku | Masalah | Yang didapat |
|---|---|---|---|
| Usaha produk | garment, food, craft, cosmetic, retail, homemade | Katalog di chat tidak sinkron dengan stok; pesanan tercecer di DM | Storefront dengan stok dari Fabriku; pesanan tercatat sebagai sales order |
| Usaha jasa | service | Calon pelanggan tidak tahu layanan; permintaan masuk dari banyak chat | Landing page layanan; permintaan menjadi prospek berstatus |
| Usaha gabungan | mis. konveksi + jasa jahit, bengkel + sparepart | Dua alur bercampur | Satu situs, dua jalur terpisah |
| Semua | – | Bolak-balik IG, FB, Fabriku; komentar "harga berapa kak?" terlewat | Inbox komentar/DM terpusat yang bisa diubah jadi pesanan/prospek |

## 4. Daftar keputusan

| ID | Area | Keputusan |
|---|---|---|
| K1 | Tujuan | Storefront/landing page untuk tenant + pemantauan sosial media usaha di satu tempat |
| K2 | Jadwal | Seluruh cakupan selesai dan rilis 31 Oktober 2026, tanpa pemangkasan |
| K3 | Akun | Satu akun dan satu tagihan Fabriku; tenant tidak mendaftar/membayar ke Satsetui atau Repliz |
| K4 | Domain toko | Subdomain `{slug}.fabriku.biz.id` (domain dibeli di Minggu 0). Root `fabriku.biz.id` dan `www` → 301 ke `fabriku.id` |
| K5 | Domain sendiri | Didukung, TLS lewat Cloudflare for SaaS (custom hostnames) |
| K6 | Render | Storefront dirender Blade (HTML server), bukan Inertia |
| K7 | Tema | Dibuat dengan Satsetui dan bisa diedit di Fabriku: katalog 12 template khusus Fabriku (4 gaya × 3 mode) + desain AI per tenant lewat API, keduanya format `fabriku-site-v1` |
| K8 | API Satsetui | Internal untuk Fabriku (satu akun layanan). Kredit diisi admin Satsetui (grant), pemakaian tercatat di ledger |
| K9 | Pesanan | Pesanan produk = sales order `draft`; permintaan jasa = prospek terpisah, dikonversi ke sales order setelah deal |
| K10 | Lanjutan transaksi | Tombol WhatsApp `wa.me` dengan pesan berisi nomor pesanan; staf dinotifikasi lewat email + Telegram |
| K11 | Sosial | Repliz, platform Instagram + Facebook |
| K12 | Kontrak Repliz | Bila belum ada kontrak tertulis per 31 Okt, Pusat Sosial rilis berlabel **Beta** tetap di Fabriku Pro |
| K13 | Data sosial | Komentar disimpan 90 hari; DM tidak disimpan (dibaca langsung dari Repliz) |
| K14 | Skema data | Netral vendor: kolom `provider` + `provider_*_id` |
| K15 | Paket | Core Rp25.000/bulan, Rp250.000/tahun (tetap) termasuk Website Usaha dari template katalog. Add-on **Fabriku Pro** Rp49.000/bulan, Rp490.000/tahun untuk desain AI unik, domain sendiri, Tulis dengan AI, dan Pusat Sosial |
| K16 | Kuota Pro/bulan | 2 akun sosial, 60 posting terjadwal, 50 teks AI, 3 desain tema AI + 20 regenerasi section, 8 Social Kit. Statistik, komentar, DM tanpa batas |
| K17 | Tagihan Pro | Ikut periode Core; beli di tengah periode bayar prorata sampai tanggal habis Core |
| K18 | Trial | Hanya Core (30 hari, termasuk website template). Tidak ada trial Pro |
| K19 | Berhenti | Tenggang 7 hari, data Pro disimpan 90 hari. Pro berhenti: desain AI tetap tampil tanpa bisa diedit. Core habis: website tampil, pesanan ditutup |
| K20 | Kepemilikan produk | Website Usaha Fabriku untuk UMKM yang datanya di Fabriku. Link-in-bio/menu digital Satsetui tetap untuk pengguna Satsetui umum dan mengarahkan UMKM operasional ke Fabriku |
| K21 | Rilis | Pilot 5–10 tenant mulai 13 Okt **hanya untuk fitur Core** (Website Usaha template). Fitur Pro diuji tim internal sampai 31 Okt. Semua dibuka 31 Okt |
| K22 | Syarat Pro | Pro hanya bisa dibeli di atas Core berbayar yang aktif; tenant trial membayar Core dulu (boleh Core + Pro dalam satu tagihan) |

## 5. Fitur: Website Usaha

### 5.1 Aktivasi dan pengaturan

- Menu **Website Usaha** di sidebar, gated rule `enable_business_site` di `config/business.php` (diset eksplisit di semua kategori, karena `isModuleEnabled()` men-default `true`) dan langganan Core aktif/trial. Fitur bertanda **Pro** di bawah hanya aktif bila `Tenant::hasFeature()` mengizinkan.
- Wizard 4 langkah:
  1. Mode `produk` / `jasa` / `gabungan` (default: `service` → jasa, lainnya → produk).
  2. Profil: nama, slug subdomain, logo, deskripsi, alamat/area layanan, jam buka, nomor WhatsApp, tautan sosial.
  3. Produk dan/atau layanan yang tampil.
  4. Tema: pilih dari katalog, atau **Buat desain khusus dengan AI** (**Pro**, kuota desain tema), lalu pratinjau. Tenant Core melihat tombol ini dengan ajakan membeli Pro.
- **Pro:** tombol **Tulis dengan AI** mengisi teks kosong (headline, tentang usaha, deskripsi produk/layanan) lewat `OpenAIService` (kuota teks AI). `OPENAI_MODEL` di produksi wajib diset ke model valid (default `gpt-5-nano` di `config/services.php` bukan ID valid).
- Penerima notifikasi: pilih user staf; pemilik usaha menjadi cadangan.
- Aksi: **Terbitkan**, **Jeda pesanan**, **Nonaktifkan**. Pemeriksaan sebelum terbit: WhatsApp valid, ≥ 1 produk/layanan, penerima valid, tidak ada teks contoh template yang tersisa.
- Slug dicadangkan: `www`, `app`, `admin`, `api`, `mail`, `smtp`, `ftp`, `blog`, `help`, `status`, `toko`, `cdn`, `static`, `assets`, dan nama merek Fabriku/Satsetui/Repliz.

### 5.2 Tema dari Satsetui

**Sumber tema:**

| Sumber | Siapa membuat | Kapan tersedia | Kuota |
|---|---|---|---|
| Katalog (12 template: 4 gaya × mode produk/jasa/gabungan) | Tim Fabriku lewat Satsetui, dikurasi, lalu diimpor sebagai template global | Langsung saat aktivasi, untuk Core dan Pro | Tidak memakai kuota |
| Desain AI per tenant (**Pro**) | Tenant Pro, dari wizard atau pengaturan tema | ± beberapa menit (job) | 3 desain penuh + 20 regenerasi section/bulan |

**Format `fabriku-site-v1`** (dihasilkan Satsetui, dirender dan diedit di Fabriku):

- Artifact berisi `theme.json` (metadata, daftar section, nilai default teks/gambar, nilai CSS variables), `shell.html` (header + footer), `sections/{id}.html` per section, dan `theme.css` yang sudah dikompilasi (Tailwind tidak dikompilasi ulang di Fabriku).
- Penanda:
  - `data-fb-shell="header|footer"`: kerangka halaman.
  - `data-fb-section="{tipe}"` dengan tipe `hero`, `value-props`, `featured-products`, `service-list`, `about`, `testimonials`, `gallery`, `cta`, `contact`: blok yang bisa diurutkan dan disembunyikan.
  - `data-fb-text="{kunci}"`, `data-fb-image="{kunci}"`, `data-fb-link="{kunci}"`: elemen yang bisa diedit.
  - `data-fb-slot="{nama}"` dengan nama `logo`, `nav`, `products`, `services`, `lead-form`, `whatsapp-button`, `cart-button`, `map`: tempat Fabriku menyisipkan komponen native. Harga, stok, keranjang, dan formulir tidak pernah berasal dari HTML hasil AI.
- Warna dan tipografi lewat CSS variables: `--fb-primary`, `--fb-accent`, `--fb-bg`, `--fb-surface`, `--fb-text`, `--fb-font-heading`, `--fb-font-body`, `--fb-radius`.
- Dilarang: `<script>`, `<iframe>`, `<object>`, `<embed>`, `<form>` (formulir hanya lewat slot), `<meta http-equiv>`, atribut `on*`, URL `javascript:`/`data:` (kecuali gambar), dan URL aset di luar host yang diizinkan.
- Halaman selain beranda (daftar produk, detail produk, layanan, keranjang, terima kasih, kebijakan privasi) adalah komponen Blade native Fabriku yang memakai `shell.html` dan CSS variables tema, sehingga tetap seragam dengan desain.

**Validasi dua lapis:**

1. Satsetui memvalidasi keluaran model terhadap format di atas; bila gagal, diperbaiki otomatis satu kali, lalu ditandai gagal dan kredit di-refund.
2. Fabriku menyanitasi ulang saat impor dengan `symfony/html-sanitizer` (allowlist tag, atribut, dan host aset). Sanitizer regex milik Satsetui (`sanitizeSavedHtml`) tidak dipakai untuk storefront publik.

**Editor tema di Fabriku:**

- Memakai ulang `LivePreview.vue` dan `PropertiesPanel.vue` dari Satsetui (sama-sama Vue 3), dibatasi pada elemen bertanda.
- Edit teks/gambar/tautan bertanda, urutkan/sembunyikan section, ubah CSS variables (warna, font, radius), unggah logo dan gambar ke `fabriku_s3`.
- **Pro:** **Regenerasi section dengan AI** memanggil API Satsetui untuk satu section (kuota regenerasi).
- Editor yang sama dipakai tenant Core (untuk template katalog) dan Pro (katalog + desain AI). Desain AI tanpa Pro aktif bersifat baca-saja (7.5).
- Setiap simpan membuat versi tema baru; tenant bisa kembali ke versi sebelumnya. Hanya versi yang diterbitkan yang tampil publik.

### 5.3 Katalog produk

- Satu kartu per `product_code`, walau stok terpecah di beberapa baris `inventory_items` (rak/batch).
- Merchant bisa mengganti judul, deskripsi, foto, urutan, dan visibilitas. Foto default dari `inventory_items.image_path`.
- Harga dari `selling_price`; bila berbeda antar-baris, tampil harga terendah dengan label `mulai`.
- Ketersediaan ditampilkan `Tersedia` / `Stok terbatas` (≤ `minimum_stock`) / `Habis`, tanpa angka.
- URL stabil `/produk/{slug}`.

### 5.4 Pesanan produk

1. Pengunjung menambah produk ke keranjang (disimpan di browser), lalu mengisi nama, nomor WhatsApp, alamat (wajib/opsional sesuai pengaturan toko), dan catatan.
2. **Kirim pesanan** (bukan "Bayar"). Server menghitung ulang harga dan ketersediaan, lalu memilih baris `inventory_items`: FEFO bila ada `expired_date`, selain itu `available_stock` terbesar.
3. Server membuat/menemukan `customer` (dedupe per tenant berdasarkan nomor HP ternormalisasi `62…`) dan `sales_order` `draft`, `channel = online`, `source = website`, `payment_status = unpaid`. Belum ada reservasi stok.
4. Halaman terima kasih: nomor pesanan + tombol **Lanjut ke WhatsApp** (`wa.me/{nomor toko}?text=…nomor pesanan…`).
5. Staf mendapat notifikasi, menghubungi pembeli, menentukan ongkir dan cara bayar, lalu **Konfirmasi** (observer mereservasi stok) atau **Batalkan**.
6. Pembayaran dicatat lewat alur `updatePayment()` yang ada.

### 5.5 Layanan dan prospek jasa

- Layanan dari `services` aktif yang ditandai publik; kolom tambahan: foto, deskripsi publik, label harga (`tetap` / `mulai dari` / `hubungi kami`), slug.
- Formulir **Minta penawaran**: layanan (opsional), nama, WhatsApp, kebutuhan, persetujuan dihubungi (wajib, UU No. 27/2022).
- Membuat **prospek** berstatus `baru` → `dihubungi` → `dikonversi` / `ditutup` (dengan alasan).
- **Konversi ke pesanan** membuat sales order jasa lewat alur yang ada; `leads.sales_order_id` unik mencegah konversi ganda.
- Halaman terima kasih + tombol WhatsApp.

### 5.6 Kotak masuk permintaan

- Halaman **Permintaan Masuk**: tab Pesanan Website (`source = website`, status `draft`) dan Prospek. Badge jumlah baru di sidebar.
- Per baris: waktu, nama, tombol WhatsApp ke pembeli, status, staf penangan.
- Permintaan > 24 jam tanpa tindakan disorot; email ringkasan harian untuk permintaan tertunda.

### 5.7 Notifikasi staf

- Job setelah commit: email ke penerima terpilih; Telegram lewat `TelegramService::sendMessage()` ke penerima yang punya `telegram_chat_id`.
- Isi tanpa data pribadi lengkap, berisi tautan yang butuh login.
- Retry 3× dengan backoff; kegagalan dicatat di pesanan/prospek (`notification_failed_at`) dan tampil di pengaturan Website Usaha.

### 5.8 Domain dan SEO toko

- Subdomain `{slug}.fabriku.biz.id` dengan sertifikat wildcard Cloudflare.
- **Pro:** domain sendiri. Tenant menambah CNAME ke target Fabriku; Fabriku mendaftarkan custom hostname di Cloudflare for SaaS, memantau status verifikasi/sertifikat, lalu mengaktifkan domain. Subdomain dialihkan 301 ke domain sendiri begitu aktif. 100 hostname pertama gratis, selanjutnya ± $0,10/hostname/bulan.
- Proxy server menerima semua `Host` yang lewat Cloudflare dan meneruskannya ke container Fabriku.
- Per situs: title/description/H1 dari profil, canonical, OG image, `robots.txt`, `sitemap.xml` (halaman terbit saja).
- `noindex` untuk keranjang, terima kasih, dan situs yang belum memenuhi konten minimum.
- Structured data `Product` (harga IDR, ketersediaan) dan `LocalBusiness` hanya dari data yang diisi merchant.
- Footer: `Dibuat dengan Fabriku` (tautan nama merek biasa) dan tautan **Laporkan situs**.

## 6. Fitur: Pusat Sosial

Seluruh Pusat Sosial adalah fitur **Pro**. Kemampuan Repliz yang dipakai ([dokumentasi](https://docs.repliz.com/api/install)); workspace **Gold** karena OAuth, konten, dan DM memerlukan Gold:

| Kemampuan | Endpoint Repliz |
|---|---|
| Hubungkan/hubungkan ulang/putuskan akun | `GET /public/account/{platform}/authorize`, `POST .../connect`, `POST .../connect/{accountId}`, `DELETE` account |
| Statistik akun | `GET /public/account/{accountId}/statistic` |
| Konten + statistik konten | `GET /public/content?accountId=`, `GET /public/content/{id}/statistic` |
| Komentar | `GET /public/comment` (`pending`/`resolved`/`ignored`), reply, update status |
| DM | `GET /public/chat` (`unread`/`unreplied`), get message, send message, read |
| Jadwal | `POST /public/schedule`, get, update, remove, retry |
| Media | Storage init → PUT presigned → complete (dipakai bila URL eksternal ditolak) |

Repliz tidak punya webhook; data diperbarui lewat sinkronisasi berkala (9.3).

### 6.1 Hubungkan akun

- **Pusat Sosial → Akun**: tombol **Hubungkan Instagram** dan **Hubungkan Facebook**.
- Sebelum redirect: penjelasan bahwa Instagram harus akun **Bisnis/Kreator** dan terhubung ke Halaman Facebook, dengan tautan panduan.
- Fabriku meminta URL otorisasi Repliz dengan `redirect` ke callback Fabriku dan `state` terenkripsi (`tenant_id`, `user_id`, nonce, kedaluwarsa 10 menit). Callback memverifikasi `state` sebelum `connect`. Untuk Facebook, tenant memilih Halaman sebelum `connect`.
- `accountId` disimpan di `social_accounts` (unik per provider: satu akun hanya milik satu tenant).
- Status: `aktif`, `perlu dihubungkan ulang`, `terputus`. **Hubungkan ulang** memakai endpoint reconnect. **Putuskan** memanggil `DELETE` di Repliz dan menghapus cache komentar akun itu.
- Batas: 2 akun (kuota K16).

### 6.2 Dasbor statistik

- Kartu per akun: pengikut, jangkauan, interaksi, jumlah posting, perbandingan periode sebelumnya.
- Grafik 30 hari dari snapshot harian yang disimpan Fabriku.
- 10 konten terakhir dengan like, komentar, jangkauan, simpan; bisa diurutkan menurut performa.
- Widget Dashboard Fabriku: komentar belum ditangani, DM belum dibaca, posting terjadwal berikutnya.

### 6.3 Inbox komentar

- Komentar lintas akun; filter belum ditangani/selesai/diabaikan, akun, teks.
- Tiap komentar menampilkan konten induk (thumbnail + caption singkat) dan tautan ke posting asli.
- Aksi: **Balas** (dengan balasan tersimpan per tenant), **Tandai selesai**, **Abaikan**, **Buat pesanan**, **Buat prospek**.
- Pesanan/prospek dari komentar menyimpan `source = social_comment` + `social_comment_id`.
- Badge komentar `pending` di sidebar.

### 6.4 Inbox DM

- Percakapan dengan filter belum dibaca/belum dibalas; baca dan balas teks.
- **Buat pesanan/prospek** dengan `source = social_dm` dan ID percakapan Repliz.
- Batas platform (jendela balasan 24 jam Meta) ditampilkan sebagai pesan yang jelas.

### 6.5 Posting dan jadwal

- Composer: pilih akun (IG, FB, atau keduanya), 1–10 gambar JPEG/WebP ≤ 8 MB, rasio 4:5–1.91:1, caption ≤ 2.200 karakter dan ≤ 30 hashtag (IG), waktu terbit.
- Pintasan **Posting produk ini** / **Posting layanan ini**: foto, deskripsi, dan tautan ke halaman toko terisi otomatis.
- **Tulis caption dengan AI** lewat `OpenAIService` (kuota teks AI).
- Validasi server sesuai aturan platform sebelum dikirim; aturan paling ketat berlaku bila memilih beberapa akun. Gambar PNG dikonversi ke JPEG.
- Kalender + daftar: `draf`, `terjadwal`, `terbit`, `gagal` (alasan + **Coba lagi**), `dibatalkan`.
- **Unduh untuk posting manual** (gambar + caption tersalin).
- Kuota: 60 posting terjadwal/bulan.

### 6.6 Konten AI (Social Kit)

- Dari produk/layanan: tujuan (promosi, edukasi, tips, cerita), jumlah slide, template, nada.
- Fabriku mengirim brief + 1–5 foto produk (URL bertanda tangan) ke Satsetui profil `social-kit-v1`; hasil slide JPEG masuk ke composer sebagai draf.
- Caption dibuat di Fabriku; tidak ada posting tanpa persetujuan tenant.
- Kuota: 8 Social Kit/bulan.

## 7. Paket, kuota, dan billing

### 7.1 Core dan add-on Fabriku Pro

Fitur baru dijual sebagai **add-on Fabriku Pro** di atas langganan Core. Tanpa Pro, tenant tetap punya website usaha dari template katalog, tetapi tidak bisa punya desain unik dan tidak bisa memantau sosial media.

| | Core | + Fabriku Pro |
|---|---|---|
| Fitur operasional Fabriku | ✓ | ✓ |
| Website Usaha (1 situs di `{slug}.fabriku.biz.id`) | ✓ | ✓ |
| Pilih dan edit template katalog (teks, gambar, warna, urutan section) | ✓ | ✓ |
| Pesanan produk, prospek jasa, Permintaan Masuk, notifikasi staf, WhatsApp | ✓ | ✓ |
| Desain tema AI unik | – | 3 desain + 20 regenerasi section/bulan |
| Domain sendiri | – | ✓ |
| Tulis dengan AI (teks website + caption) | – | 50/bulan |
| Pusat Sosial: akun terhubung | – | 2 |
| Statistik, inbox komentar, DM | – | Tanpa batas |
| Posting terjadwal | – | 60/bulan |
| Social Kit | – | 8/bulan |
| Harga bulanan / tahunan | Rp25.000 / Rp250.000 | + Rp49.000 / + Rp490.000 |

Kuota Pro reset setiap awal periode tagihan. Kuota habis menghentikan aksi baru saja; situs dan posting yang sudah terbit tetap berjalan. Generasi yang gagal karena sistem/vendor mengembalikan kuota.

### 7.2 Aturan add-on

- Pro hanya bisa dibeli di atas Core berbayar yang aktif, dengan siklus yang sama (Core bulanan → Pro bulanan, Core tahunan → Pro tahunan).
- Periode Pro **ikut periode Core**: berakhir di tanggal yang sama dengan `subscription_expires_at`.
- Beli Pro di tengah periode: bayar prorata sampai tanggal habis Core (7.4). Beli bersamaan dengan Core baru/perpanjangan: bayar harga Pro penuh dalam satu tagihan.
- Perpanjangan: satu tagihan Core + Pro. Tenant bisa mematikan Pro sebelum perpanjangan; Pro tetap aktif sampai akhir periode yang sudah dibayar, tanpa refund.
- **Tidak ada trial Pro.** Trial 30 hari tenant baru hanya berisi Core (termasuk website dari template katalog).

### 7.3 Perubahan billing

- `tenants.plan_code`: `trial`, `core`. Migrasi: `subscription_plan = full` → `plan_code = core`, `subscription_expires_at` tidak berubah.
- `tenants` ditambah `pro_expires_at` (sama dengan `subscription_expires_at` saat Pro aktif) dan `pro_auto_renew`.
- `subscription_payments` ditambah `kind` (`new`, `renewal`, `pro_addon`), `billing_cycle`, `period_start`, `period_end`, `core_amount`, `pro_amount`. Nominal yang dibayar per periode menjadi snapshot harga.
- Hak dan kuota di `config/plans.php`; satu pintu `Tenant::hasFeature()` / `Tenant::quotaRemaining()`.
- Konsumsi di `usage_events` (`tenant_id`, `type`, `period_start`, `quantity`, `idempotency_key` unik, `reversed_at`).
- Harga Pro di `SystemSetting` (`pro_price_monthly`, `pro_price_yearly`) seperti harga Core sekarang.

### 7.4 Prorata beli Pro di tengah periode

- Rumus: `tagihan = harga Pro siklus × sisa hari ÷ jumlah hari periode Core`, dibulatkan ke rupiah terdekat.
- Contoh: bulanan, sisa 15 dari 30 hari → 49.000 × 15/30 = Rp24.500. Tahunan, sisa 200 dari 365 hari → 490.000 × 200/365 = Rp268.493.
- Tagihan dibuat sebagai `subscription_payments` `kind = pro_addon` lewat gateway Sumopod yang ada. Webhook pembayaran hanya mengisi `pro_expires_at = subscription_expires_at`; **tidak** memperpanjang Core. Webhook ganda tidak mengubah apa pun.

### 7.5 Pro berhenti

- Hari 0–7 (tenggang): semua fitur Pro berjalan, banner dan email peringatan di hari 0, 3, 6.
- Setelah hari 7:
  - Desain AI yang sedang terbit **tetap tampil, tetapi tidak bisa diedit atau diregenerasi**. Tenant bisa beralih ke template katalog kapan saja.
  - Domain sendiri dinonaktifkan; website kembali ke `{slug}.fabriku.biz.id` dan domain sendiri dialihkan 301 selama custom hostname masih ada.
  - Sinkronisasi, inbox, dan jadwal Pusat Sosial berhenti; jadwal yang jatuh setelah tanggal habis dibatalkan di Repliz H-1 dengan email pemberitahuan. Akun sosial tetap terhubung.
- Hari 90: email pemberitahuan terakhir, lalu data Pro dihapus: versi desain AI yang tidak sedang terbit, komentar tersimpan, snapshot statistik, custom hostname Cloudflare, dan akun di Repliz. Website, sales order, dan prospek tetap.
- Mengaktifkan Pro lagi sebelum hari 90 memulihkan semuanya.

### 7.6 Core habis

Sesuai mode read-only Fabriku yang ada: setelah tenggang 7 hari, website tetap tampil dengan pesan "Toko sedang tidak menerima pesanan" dan formulir ditutup. Pro ikut berhenti karena periodenya sama. Data website tidak dihapus selama tenant masih ada.

### 7.7 Model biaya per tenant Pro

| Komponen | Cara hitung |
|---|---|
| Repliz Gold Rp49.000 ÷ tenant Pro (2 akun/tenant, 1 workspace = ±90 tenant) | ± Rp550 |
| Repliz Storage (bila dipakai) Rp39.000/5 GB | Dibagi rata, dipantau di panel admin |
| LLM teks (`OpenAIService`) | Dicatat per `usage_events` × tarif model |
| Satsetui (tema AI + Social Kit) | Kredit dari ledger akun layanan × nilai kredit |
| Cloudflare for SaaS | Rp0 sampai 100 domain sendiri |
| Hosting website (juga untuk tenant Core) | Dipantau per bulan |

Laporan margin per tenant ada di panel admin sejak rilis.

## 8. Integrasi Satsetui

Pekerjaan di repo `/home/fauzan/dev/satsetui`.

### 8.1 API internal

- Base `/api/v1`, Bearer key milik akun layanan Fabriku (disimpan hash, bisa dirotasi/dicabut, scope `generation:create`, `generation:read`).
- `POST /generations` dengan header `Idempotency-Key` (sama + payload sama → generasi yang sama tanpa charge ulang; payload berbeda → `409`).
- `GET /generations/{id}` → `queued` / `processing` / `completed` / `failed`.
- `GET /generations/{id}/artifacts` → daftar file dengan URL unduh bertanda tangan berumur pendek.
- Job di-dispatch saat create (tanpa SSE/browser), memakai `GenerationService` dan `CreditService` yang ada; refund tepat sekali saat gagal. Audit penguncian saldo di jalur refund sebelum kunci pertama dibuat.
- Kredit: admin Satsetui melakukan grant berkala ke akun layanan Fabriku; alarm di Fabriku bila saldo < kebutuhan 7 hari.

### 8.2 Profil `fabriku-site-v1`

- Brief: nama usaha, kategori, mode, deskripsi, warna/logo (opsional), gaya, daftar section, contoh nama produk/layanan (tanpa harga/stok), `locale = id-ID`.
- Keluaran sesuai format 5.2; `McpPromptBuilder` mendapat builder baru dengan aturan penanda; validator menolak/memperbaiki keluaran yang melanggar.
- Mode regenerasi satu section: input `theme.json` + tipe section + instruksi singkat.
- Katalog 12 template dibuat tim lewat profil yang sama, lalu diimpor ke Fabriku sebagai template global.

### 8.3 Profil `social-kit-v1`

- Brief sesuai `StoreSocialKitRequest` + `images[]` (1–5 URL foto bertanda tangan) yang dipakai di slide `image-focus`.
- Preset `ig-portrait` (1080×1350) dan `ig-square` (1080×1080).
- Keluaran per slide: JPEG ≤ 8 MB + dimensi + urutan (bukan hanya ZIP).

## 9. Arsitektur dan data

### 9.1 Routing storefront

- Middleware `ResolveStorefront` memetakan `Host` → `business_sites` (slug di `*.fabriku.biz.id` atau `custom_domain` aktif). Host tidak dikenal → 404; `fabriku.biz.id` dan `www.fabriku.biz.id` → 301 ke `fabriku.id`.
- `routes/storefront.php` terpisah, tanpa sesi aplikasi admin; cookie sesi Fabriku tetap host-only (`SESSION_DOMAIN=null`).
- Semua query storefront lewat helper yang mewajibkan tenant (`Storefront::for($tenant)`), karena `TenantScope` tidak menyaring tanpa user login.
- Cache HTML halaman publik 60 detik, dibersihkan saat tema/produk/layanan berubah.

### 9.2 Perubahan kode yang wajib

- `SalesOrder::generateOrderNumber()` menerima `tenantId` eksplisit; pembuatan order publik dibungkus retry saat `UniqueConstraintViolationException`.
- `Customer`, `SalesOrder`, `SalesOrderItem`, `Lead` diisi `tenant_id` eksplisit di jalur publik.
- Order publik tidak memakai `quickCheckoutStore`; observer tetap satu-satunya sumber transisi stok.
- Tambah dependensi `symfony/html-sanitizer`.

### 9.3 Sinkronisasi Repliz

| Data | Frekuensi |
|---|---|
| Komentar `pending` | 10 menit untuk tenant yang login dalam 7 hari terakhir, 60 menit untuk lainnya, dan saat inbox dibuka |
| Hitungan DM belum dibaca | 15 menit; isi DM dibaca saat inbox dibuka |
| Status jadwal yang lewat waktu dan belum final | 5 menit |
| Konten + statistik 20 konten terakhir | 6 jam |
| Statistik akun (snapshot) | Harian 01:00 |
| Status koneksi akun | Harian 02:00; email ke pemilik bila perlu dihubungkan ulang |

Semua lewat queue dengan batas konkurensi; frekuensi diturunkan otomatis saat Repliz membalas `429`.

### 9.4 Tabel

| Tabel | Kolom utama |
|---|---|
| `business_sites` | `tenant_id` (unik), `mode`, `slug` (unik global), `custom_domain` (unik, nullable), `cloudflare_hostname_id`, `domain_status`, `active_theme_version_id`, `profile` (json), `seo` (json), `status` (`draft`/`published`/`paused`/`suspended`), `published_at` |
| `business_site_recipients` | `business_site_id`, `user_id` |
| `site_templates` | katalog global: `code`, `name`, `style`, `mode`, `artifact` (json), `css_path`, `preview_image`, `is_active` |
| `site_theme_versions` | `tenant_id`, `business_site_id`, `source` (`catalog`/`ai`), `site_template_id`, `satsetui_generation_id`, `version`, `theme` (json), `sections` (json), `css_path`, `created_by` |
| `site_products` | `tenant_id`, `product_code`, `slug`, `title`, `description`, `image_path`, `sort`, `is_visible`; unik `(tenant_id, product_code)`, `(tenant_id, slug)` |
| `services` (ubah) | + `is_public`, `public_description`, `image_path`, `price_label`, `slug` |
| `sales_orders` (ubah) | + `source` (`website`, `social_comment`, `social_dm`), `source_ref`, `idempotency_key` (unik per tenant), `notification_failed_at` |
| `leads` | `tenant_id`, `business_site_id`, `service_id`, `source`, `source_ref`, `name`, `phone`, `message`, `consent_at`, `consent_text`, `status`, `assigned_user_id`, `contacted_at`, `closed_reason`, `sales_order_id` (unik), `idempotency_key`, `notification_failed_at` |
| `social_accounts` | `tenant_id`, `provider`, `provider_account_id` (unik per provider), `platform`, `display_name`, `avatar_url`, `status`, `connected_by`, `connected_at`, `disconnected_at`, `last_synced_at` |
| `social_account_snapshots` | `social_account_id`, `date`, `followers`, `reach`, `interactions`, `posts_count` |
| `social_comments` | `tenant_id`, `social_account_id`, `provider_comment_id`, `provider_content_id`, `author_name`, `text`, `status`, `commented_at`, `replied_at`, `replied_by`; dihapus 90 hari setelah selesai/diabaikan |
| `social_saved_replies` | `tenant_id`, `title`, `body` |
| `social_posts` | `tenant_id`, `caption`, `media` (json), `status`, `scheduled_at`, `source_type`/`source_id`, `satsetui_generation_id`, `created_by`, `approved_by` |
| `social_post_targets` | `social_post_id`, `social_account_id`, `provider_schedule_id`, `status`, `error`, `published_at` |
| `usage_events` | `tenant_id`, `type`, `period_start`, `quantity`, `idempotency_key` (unik), `reversed_at` |
| `tenants` (ubah) | + `plan_code`, `pro_expires_at`, `pro_auto_renew` |
| `subscription_payments` (ubah) | + `kind`, `billing_cycle`, `period_start`, `period_end`, `core_amount`, `pro_amount` |

Semua tabel tenant memakai `TenantScope`, `HasAuditLogs`, dan unique index yang di-scope `tenant_id` sesuai konvensi repo.

### 9.5 Keamanan

- Rate limit per IP + honeypot pada checkout dan formulir prospek.
- `Idempotency-Key` UUID dari browser mencegah pesanan ganda.
- Harga, `tenant_id`, `payment_status` tidak pernah dari input publik.
- HTML tema melewati sanitizer allowlist; teks merchant di-escape; Content-Security-Policy storefront tanpa `script-src` pihak ketiga.
- **Laporkan situs** di footer; **Suspend** di panel admin.
- Kredensial Repliz, Satsetui, Cloudflare hanya di `.env` server. Setiap panggilan Repliz memeriksa `accountId` milik tenant yang login.
- Tes isolasi dua tenant untuk setiap endpoint storefront, Pusat Sosial, dan callback OAuth.

## 10. Integrasi Repliz

- Satu workspace **Gold** atas nama badan usaha Fabriku; API key di `.env`.
- Email permintaan kontrak dikirim 24 Sep: model multi-merchant, batas request, retensi data, SLA/kontak, pemberitahuan perubahan API, jalan keluar bila workspace dipecah. Tanpa kontrak per 31 Okt → label Beta (K12).
- Kelas `ReplizClient` (HTTP Laravel, timeout 15 dtk, retry pada 5xx/429, log request ID) + tes kontrak dengan respons yang direkam.
- Alarm di panel admin saat akun terhubung ≥ 180/200; workspace kedua dibuat manual dan `social_accounts` menyimpan `provider_workspace`.
- Cadangan bila Repliz berhenti/menolak: integrasi eksis "bawa akun sendiri" untuk tenant yang mau, dan Meta Graph API langsung untuk IG/FB. Pengajuan verifikasi bisnis Meta dimulai 24 Sep agar cadangan siap. Detail di [tinjauan](review-dan-rekomendasi.md).

## 11. Jadwal

Dua jalur kerja paralel: **F** (repo Fabriku) dan **S** (repo Satsetui).

### Minggu 0 — 24–26 Sep: persiapan

- Kirim permintaan kontrak Repliz; berlangganan Gold; uji manual OAuth IG/FB, komentar, DM, jadwal dengan URL media S3 Fabriku.
- Domain: beli `fabriku.biz.id`, arahkan nameserver ke Cloudflare, buat wildcard `*.fabriku.biz.id`, aktifkan Cloudflare for SaaS, konfigurasi proxy server catch-all ke container Fabriku.
- Finalkan spesifikasi `fabriku-site-v1` (bagian 5.2) sebagai dokumen bersama kedua repo.
- Set `OPENAI_MODEL` valid di produksi.
- Rekrut 5–10 tenant pilot (produk, jasa, gabungan).
- Ajukan verifikasi bisnis Meta (cadangan).

### Minggu 1 — 29 Sep–3 Okt: fondasi

- **F:** billing fondasi (`plan_code`, `config/plans.php`, `usage_events`, kolom baru `subscription_payments`, migrasi `full` → `core`, kolom Pro di `tenants`, gating `hasFeature()` Core vs Pro); `business_sites`, `ResolveStorefront`, `routes/storefront.php`; katalog produk/layanan (`site_products`, kolom `services`); renderer tema (shell + section + slot) + sanitizer.
- **S:** API internal (kunci, idempotensi, job headless, kepemilikan); profil `fabriku-site-v1` + validator + CSS terkompilasi; mode regenerasi section.

### Minggu 2 — 6–10 Okt: Website Usaha lengkap

- **F:** checkout (dedupe customer, nomor order per tenant + retry, idempotensi), prospek + konversi, Permintaan Masuk, notifikasi, WhatsApp, wizard, editor tema (port `LivePreview`/`PropertiesPanel`), versi tema, Tulis dengan AI, SEO dasar per host, panel admin (daftar situs, suspend, laporan situs).
- **S:** buat 12 template katalog, kurasi, impor ke Fabriku; profil `social-kit-v1` (foto, JPEG per slide).
- **Selesai minggu ini:** demo produk, jasa, gabungan terbit di subdomain.

### Minggu 3 — 13–17 Okt: pilot Website Usaha (Core) + Pusat Sosial dasar

- Pilot Website Usaha (fitur Core) mulai **13 Okt**. Fitur Pro di minggu ini diuji tim internal memakai tenant dan akun IG/FB milik Fabriku.
- **F:** desain tema AI per tenant + regenerasi section via API; hubungkan akun (OAuth + `state`), sinkronisasi, dasbor statistik + snapshot, inbox komentar (balas, status, balasan tersimpan, buat pesanan/prospek), alarm kapasitas.
- **S:** perbaikan dari hasil pilot template katalog dan uji internal desain AI.

### Minggu 4 — 20–24 Okt: Pusat Sosial lengkap (uji internal)

- Uji internal end-to-end Pusat Sosial dengan akun IG Bisnis dan Halaman FB milik Fabriku mulai **20 Okt**.
- **F:** inbox DM; composer, jadwal, validasi/konversi media, polling status, kalender, unduh manual; pintasan posting produk/layanan; caption AI; Social Kit di composer; domain sendiri (Cloudflare for SaaS); structured data; widget Dashboard.

### Minggu 5 — 27–31 Okt: billing, peluncuran

- **F:** pembelian Pro (prorata, tagihan gabungan Core + Pro saat perpanjangan, matikan perpanjangan Pro) + halaman bayar; job tenggang/retensi/pembatalan jadwal, kunci desain AI saat Pro berhenti; laporan margin di panel admin; halaman harga, `/fitur/website-usaha`, `/fitur/pusat-sosial`, copy homepage; kebijakan privasi, syarat layanan, halaman privasi per toko; panduan bantuan + runbook; tes isolasi menyeluruh; perbaikan masukan pilot.
- **31 Okt:** deploy dan buka untuk semua tenant.

### Kriteria rilis 31 Oktober

- Pesanan produk berjalan dari keranjang → draft → konfirmasi (stok ter-reservasi) → bayar → selesai; prospek jasa sampai dikonversi. Submit ganda tidak menggandakan data.
- Tenant bisa memilih template katalog, membuat desain AI, mengedit, kembali ke versi lama, dan menerbitkan; HTML berbahaya dalam artifact ditolak sanitizer (tes).
- Domain sendiri aktif dengan TLS; subdomain 301 ke domain sendiri.
- Tenant menghubungkan IG Bisnis dan Halaman FB tanpa login ke Repliz; `state` palsu/tenant lain ditolak.
- Komentar baru muncul ≤ 15 menit; balasan dari Fabriku muncul di IG/FB; komentar/DM bisa jadi pesanan/prospek.
- Posting tunggal dan carousel terbit sesuai jadwal; kegagalan terlihat dan bisa dicoba ulang.
- Kuota berkurang tepat sekali dan kembali saat gagal; beli Pro prorata tidak mengubah tanggal habis Core; webhook ganda aman; tenant Core tidak bisa memakai fitur Pro di semua endpoint (tes); desain AI terkunci baca-saja setelah Pro berhenti.
- Tidak ada data tenant lain yang bisa diakses di semua endpoint baru (tes dua tenant).
- Seluruh suite test hijau; pint dan lint bersih.

## 12. Metrik

| Tahap | Event (tanpa data pribadi pengunjung) |
|---|---|
| Aktivasi | `website_dibuat`, `website_terbit`, `tema_ai_dibuat`, `akun_sosial_terhubung` |
| Pemakaian | `pesanan_website_masuk`, `prospek_masuk`, `komentar_dibalas`, `dm_dibalas`, `posting_terbit`, `posting_gagal` |
| Nilai | `permintaan_ditindaklanjuti_24j`, `prospek_dikonversi`, `pesanan_dari_sosial`, `pesanan_website_selesai` |
| Bisnis | `beli_pro`, `pro_diperpanjang`, `pro_berhenti`, biaya vendor per tenant |

Laporan bulanan di panel admin.

## 13. Risiko dan penanganan

| Risiko | Penanganan |
|---|---|
| Jadwal 5,5 minggu untuk dua repo dan tiga layanan | Dua jalur paralel F/S; spesifikasi `fabriku-site-v1` dikunci di Minggu 0 agar kedua jalur tidak saling menunggu; deploy bertahap setiap akhir minggu |
| Repliz berubah/berhenti (API sejak Juni 2026, dikelola perorangan) | Kontrak, skema netral vendor, cadangan eksis BYO + Meta Graph API, label Beta bila belum ada kontrak |
| Batas request Repliz tidak didokumentasikan | Frekuensi bertingkat, backoff otomatis saat `429`, pengukuran saat uji internal dan pantauan ketat 2 minggu pertama setelah rilis |
| Keluaran AI tidak patuh format penanda | Validator + perbaikan otomatis di Satsetui, sanitizer di Fabriku, katalog template sebagai pilihan yang selalu tersedia |
| Akun IG tenant masih pribadi | Panduan konversi di UI, unduh untuk posting manual |
| Kebocoran antar-tenant di workspace bersama | Pemeriksaan kepemilikan setiap `accountId`, tes dua tenant, audit log |
| Situs toko dipakai menipu | Laporkan situs, suspend admin, domain toko terpisah dari `fabriku.id` |
| Pesanan tanpa tindak lanjut | Notifikasi, WhatsApp, sorotan > 24 jam, ringkasan harian |
| Oversell karena draft belum reservasi | Label ketersediaan non-angka, konfirmasi staf wajib |
| Biaya AI melebihi perkiraan | Kuota, laporan margin per tenant sejak rilis |

## 14. Legal dan privasi

- Kebijakan privasi Fabriku: data pembeli/prospek tenant (Fabriku sebagai pemroses atas nama tenant), data akun sosial, Repliz/Satsetui/Cloudflare/OpenAI sebagai subprosesor, retensi 90 hari.
- Syarat layanan: tenant bertanggung jawab atas konten dan produk; larangan barang terlarang; hak suspend.
- Formulir prospek menyimpan `consent_at` + `consent_text` (UU No. 27/2022).
- Halaman kebijakan privasi per toko dibuat otomatis dengan identitas tenant.

## 15. Branding dan pemasaran

- Posisi: **aplikasi operasional, website usaha, dan pusat sosial untuk UMKM**. Headline homepage mulai 31 Okt: *Kelola usaha, terima pesanan, dan pantau sosial media dari satu tempat.*
- Halaman `/fitur/website-usaha` (website gratis dalam Core) dan `/fitur/pusat-sosial` (Fabriku Pro) terbit 31 Okt dengan contoh toko pilot yang sudah memberi izin. Halaman harga menampilkan Core + add-on Pro.
- Pesan utama: *Semua pelanggan Fabriku kini punya website usaha. Tambah Fabriku Pro untuk desain unik dan pantau sosial media.*
- Atribusi: `Dibuat dengan Fabriku` di toko; nama Satsetui/Repliz hanya di kebijakan privasi dan layar izin akun sosial.
- Artikel blog baru dari pertanyaan pilot. Detail SEO di [brand-seo-expansion-plan.md](brand-seo-expansion-plan.md).

## 16. Operasional

- Panel admin: daftar situs + suspend, laporan situs, kapasitas workspace Repliz, saldo kredit Satsetui, akun sosial per tenant, kegagalan notifikasi/sinkron/jadwal, pemakaian kuota, margin per tenant.
- Panduan bantuan: ubah IG ke akun Bisnis, hubungkan akun, domain sendiri, alur pesanan manual, upgrade.
- Runbook: Repliz down (banner di Pusat Sosial, jadwal ditahan, retry setelah pulih), token akun kedaluwarsa massal, lonjakan spam formulir, saldo kredit Satsetui habis.

## 17. Referensi

| Dokumen | Isi |
|---|---|
| [review-dan-rekomendasi.md](review-dan-rekomendasi.md) | Riset Repliz, eksis, Postiz, Satsetui, kode Fabriku, harga pembanding |
| [commerce-mvp-plan.md](commerce-mvp-plan.md) | Rancangan awal alur pesanan/prospek dan batas data |
| [website-usaha-pricing-plan.md](website-usaha-pricing-plan.md) | Analisis billing awal dan pelanggan lama |
| [satsetui-api-plan.md](satsetui-api-plan.md) | Kontrak API Satsetui versi publik (acuan bila kelak dibuka) |
| [brand-seo-expansion-plan.md](brand-seo-expansion-plan.md) | Posisi merek, pembanding, SEO |
| [Dokumentasi API Repliz](https://docs.repliz.com/api/install) | Tier, OAuth, komentar, chat, konten, jadwal, storage |
