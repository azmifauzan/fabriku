# Rencana API Satsetui untuk Fabriku dan integrator lain

Status: rancangan kontrak, 23 September 2026. Endpoint di bawah **belum tersedia**. Dokumen ini adalah handoff implementasi untuk repo `/home/fauzan/dev/satsetui`; spesifikasi OpenAPI dan panduan publik nantinya menjadi milik repo Satsetui.

Dokumen terkait: [MVP Fabriku](commerce-mvp-plan.md), [paket dan migrasi pelanggan](website-usaha-pricing-plan.md), dan [branding serta SEO Fabriku](brand-seo-expansion-plan.md).

## Tujuan dan batas produk

Satsetui membuka kemampuan generasi desain melalui API yang sama untuk Fabriku dan integrator eksternal. Fabriku adalah satu pelanggan API dengan satu akun layanan, satu wallet kredit Satsetui, dan satu atau lebih kunci server-to-server. Pengguna Fabriku tidak perlu akun Satsetui. Integrator lain memakai akun Satsetui dan kunci API masing-masing, lalu mengelola pengguna akhir mereka sendiri.

Satsetui tidak perlu mengetahui `tenant_id` internal Fabriku, menerima password pengguna akhir, atau menjadi mesin toko/pembayaran. Integrator mengirim `client_reference` untuk korelasi internalnya; Satsetui hanya mengembalikan artefak desain. Billing kredit tetap terjadi di Satsetui pada akun pemilik kunci. Fabriku boleh membungkus biaya itu dalam satu langganannya.

## Kondisi kode dan celah yang harus ditutup

`GenerationController::generate()` sekarang mengambil `Auth::user()` dari sesi web dan memanggil `GenerationService::startGeneration()`. `startGeneration()` membuat proyek/generasi serta menagih kredit, tetapi alur website HTML/CSS tidak selesai hanya dengan POST tersebut: UI memicu kelanjutannya lewat stream atau endpoint background. Ada job `ProcessTemplateGeneration` yang dapat menjadi dasar eksekusi tanpa browser. Route generasi yang diperiksa berada di grup `auth` dan `verified` pada `routes/web.php`. Karena itu API partner tidak boleh sekadar mengekspos controller web yang ada dengan autentikasi baru; ia perlu membuat dan menjadwalkan pekerjaan secara atomik, lalu memberi status yang bisa dipoll.

Request wizard yang ada mendukung kategori `e-commerce` dan `landing-page`, tetapi `blueprint.outputFormat` hanya menerima `html-css`. Satsetui juga sudah memiliki jalur `generateSocialKit` dengan parameter topik/platform/jumlah slide dan ekspor visual, tetapi belum menjadi API partner headless untuk aset per posting. HTML/CSS visual tidak otomatis menjadi tema yang aman dan dapat diedit di Fabriku. API perlu tiga profil artefak:

| Profil | Pengguna | Hasil |
|---|---|---|
| `website-html-v1` | Integrator yang ingin desain HTML/CSS | Paket HTML/CSS tersanitasi dan metadata halaman; tidak berisi checkout fungsional |
| `business-site-theme-v1` | Fabriku dan integrator website usaha | Manifest JSON tervalidasi berisi token desain, section terurut, copy, referensi aset, dan slot komponen produk/jasa; tidak berisi JS sewenang-wenang |
| `social-kit-v1` | Integrator pembuat konten sosial | Aset gambar individual, urutan slide, dimensi/format, metadata dan caption draf bila generator sudah mendukungnya; bukan unggahan otomatis |

Kedua profil terstruktur merupakan pengembangan baru, bukan klaim bahwa generator saat ini sudah mengeluarkan JSON tema atau caption yang siap dipakai. Kontrak section awal cukup `hero`, `value-props`, `featured-products`, `service-list`, `about`, `contact`, dan `footer`; hanya section yang dibutuhkan brief yang muncul. `featured-products` dan `service-list` adalah slot data integrator, bukan daftar harga/stok dari prompt. Checkout produk dan formulir prospek jasa dibangun oleh aplikasi pemakai API. Profil tema menerima `site_mode: products|services|mixed` agar landing page jasa dan toko berbagi renderer/kontrak.

## Kontrak HTTP v1 yang diusulkan

Base path: `/api/v1`. JSON UTF-8, HTTPS, `Authorization: Bearer <api_key>`, `X-Request-Id` opsional untuk pelacakan. Kunci dibuat di dashboard Satsetui dan hanya ditampilkan sekali. Kunci disimpan sebagai hash, dapat dirotasi/dicabut, terkait satu akun layanan, dan tidak pernah dikirim ke browser merchant.

| Endpoint | Peran | Respons |
|---|---|---|
| `POST /generations/estimate` | Validasi brief dan hitung estimasi kredit dari tarif server | `200`, estimasi dan batasan; tidak menagih |
| `POST /generations` | Buat job generasi dengan header `Idempotency-Key` | `202`, ID, status awal, URL status |
| `GET /generations/{id}` | Ambil status, progres ringkas, kredit final, error aman | `200`, hanya milik pemilik kunci |
| `GET /generations/{id}/artifacts` | Ambil manifest/daftar artefak setelah selesai | `200` atau `409` jika belum selesai; URL unduh bertanda tangan dan pendek umur bila diperlukan |

Tidak perlu endpoint create-user-per-merchant pada v1. Satu akun layanan Fabriku cukup, dan pembukuan per tenant berlangsung di Fabriku. Jika kelak integrator memerlukan sub-wallet atau sub-account, desainlah setelah ada kebutuhan nyata, bukan sebagai prasyarat peluncuran API.

Contoh permintaan untuk profil website usaha (bentuk akhir harus dibakukan dalam OpenAPI):

```http
POST /api/v1/generations
Authorization: Bearer <api_key>
Idempotency-Key: business-site-theme-tenant-42-v1
Content-Type: application/json

{
  "profile": "business-site-theme-v1",
  "client_reference": "tenant-42:theme-v1",
  "locale": "id-ID",
  "brief": {
    "business_name": "Nama usaha",
    "business_type": "konveksi",
    "site_mode": "mixed",
    "description": "Produk jahitan sesuai pesanan",
    "preferred_colors": ["#163761", "#4f46e5"],
    "sections": ["hero", "featured-products", "service-list", "about", "contact"]
  }
}
```

```http
HTTP/1.1 202 Accepted
Location: /api/v1/generations/gen_123

{
  "id": "gen_123",
  "client_reference": "tenant-42:theme-v1",
  "status": "queued",
  "status_url": "/api/v1/generations/gen_123"
}
```

Contoh respons artefak tema setelah selesai:

```json
{
  "profile": "business-site-theme-v1",
  "schema_version": 1,
  "theme": { "primary": "#163761", "accent": "#4f46e5", "font_family": "sans" },
  "sections": [
    { "type": "hero", "props": { "headline": "Produk jahitan sesuai pesanan", "image_asset_id": null } },
    { "type": "featured-products", "props": { "title": "Produk pilihan", "source": "merchant-catalog" } },
    { "type": "service-list", "props": { "title": "Layanan kami", "source": "merchant-services" } }
  ]
}
```

Semua contoh adalah usulan, bukan hasil API yang sudah berjalan. Skema JSON harus dibatasi, diuji, dan kompatibel versi. Satsetui dapat menghasilkan desain, sedangkan editor/renderer integrator memutuskan bagaimana section tampil dan data dinamis dipasang.

### Profil Social Kit untuk Fabriku dan pihak lain

Gunakan endpoint `POST /generations` yang sama dengan `profile: social-kit-v1`; tidak perlu API khusus Fabriku. Brief minimum: `topic`, `locale`, `platform`, `content_type`, `slide_count`, `brand_name`, `cta`, `extra_context` yang dibatasi ukuran, serta referensi aset yang aman jika diizinkan. Harga kredit dihitung server dari jumlah slide/model; hasil generasi dapat dipoll seperti profil website. Data pelanggan akhir, daftar prospek, atau token Repliz tidak boleh masuk brief.

Kontrak artefak `social-kit-v1` perlu memberi daftar `slides[]` berurutan dengan URL unduh bertanda tangan, MIME type, dimensi, ukuran, dan `alt_text` opsional; `caption_draft` serta `hashtags` hanya dijanjikan setelah generator dan tes kualitasnya ditambahkan. Arsip ZIP/PDF yang sudah ada boleh tersedia sebagai bonus, tetapi tidak cukup untuk alur jadwal otomatis karena integrator perlu berkas per slide. Satsetui **tidak** memanggil Repliz; Fabriku memvalidasi media, meminta persetujuan merchant, lalu menjadwalkannya. Periksa batas per platform sebelum menjanjikan carousel lintas IG/FB. [Spesifikasi Repliz](https://docs.repliz.com/tutorial/specification/).

## Aturan kontrak yang tidak boleh terlewat

- `Idempotency-Key` unik per kunci API dan disimpan bersama hash payload. Pengulangan identik mengembalikan ID generasi yang sama tanpa menagih lagi; kunci sama dengan payload berbeda mengembalikan `409`.
- `POST /generations` memvalidasi brief, profil, ukuran, dan estimasi biaya di server. `user_id`, tarif, dan jumlah kredit dari payload klien diabaikan. Charge dan pencatatan generasi mengikuti transaksi/ledger yang ada; queue job dipicu setelah commit. Kegagalan yang berhak refund harus mengembalikan kredit tepat sekali, termasuk ketika beberapa worker atau callback gagal bersamaan. Audit penguncian saldo pada jalur refund sebelum API dibuka luas.
- Status publik stabil: `queued`, `processing`, `completed`, `failed`. Mapping ke status internal Satsetui dilakukan di boundary API. Progres tidak boleh memuat prompt mentah, rahasia provider, atau data pelanggan akhir.
- `GET` selalu dibatasi ke akun pemilik kunci, termasuk artefak, file, dan error. ID yang valid milik akun lain tetap tidak boleh terbaca. Batasi ukuran brief/aset, rate per kunci, jumlah job paralel, dan masa simpan artefak; publikasikan limit serta kode `429`.
- `business-site-theme-v1` wajib lolos validator schema, allowlist URL/aset, sanitasi teks/HTML, dan preview terisolasi. Tidak boleh ada script, event handler, atau URL berbahaya dari output model. `social-kit-v1` wajib membatasi format/dimensi/ukuran aset dan masa hidup URL unduh.
- Webhook terminal (`generation.completed` atau `generation.failed`) boleh ditambahkan setelah polling stabil. Jika disediakan, kirim setidaknya sekali, tanda tangani dengan HMAC, sertakan event ID dan timestamp, retry dengan backoff, serta dokumentasikan deduplikasi di sisi penerima. URL webhook harus HTTPS dan dibatasi terhadap SSRF. Polling tetap tersedia sebagai cadangan.
- API versioning ada di path dan `schema_version` artefak. Perubahan breaking membutuhkan versi baru dan masa deprecation yang diumumkan; tambah field kompatibel tidak boleh mematahkan klien lama.

Format error awal yang konsisten:

```json
{
  "error": {
    "code": "insufficient_credits",
    "message": "Kredit akun API tidak cukup untuk permintaan ini.",
    "request_id": "req_123"
  }
}
```

Daftar minimum: `invalid_request` (422), `unauthorized` (401), `forbidden` (403), `not_found` (404), `idempotency_conflict` (409), `insufficient_credits` (402 atau 409, pilih satu dalam OpenAPI), `rate_limited` (429), `generation_failed` pada status job, dan `internal_error` (500). Jangan mengembalikan stack trace atau error LLM mentah.

## Rencana implementasi di repo Satsetui

1. Jadikan `ProcessTemplateGeneration` atau service orkestrasi yang ada sebagai jalur headless resmi untuk website satu dan banyak halaman, dan hubungkan pipeline Social Kit yang ada ke runner headless serupa. Uji start → queue → terminal tanpa membuka stream browser, termasuk kegagalan layout, timeout, ekspor gambar, dan refund. Hindari pipeline kedua yang berbeda perilaku dari UI.
2. Tambahkan model/kunci API milik akun Satsetui dengan scope minimum `generation:create` dan `generation:read`, hash kunci, rotasi, pencabutan, rate limit, serta audit `client_reference` dan request ID. Akun layanan Fabriku adalah pemilik kredit; integrator lain punya akun/kunci sendiri.
3. Buat controller dan request API v1 yang memanggil service domain yang sama dengan web, bukan menyalin logika charge dan generasi. Tambahkan idempotensi persisten sebelum charge.
4. Tambahkan generator/validator `business-site-theme-v1` untuk produk, jasa, dan gabungan; simpan manifest sebagai artefak versi. Uji section tidak dikenal dan kode aktif ditolak. Tambahkan adapter `social-kit-v1` yang mengekspos gambar per slide; caption draf hanya jika benar-benar diimplementasikan. `website-html-v1` dapat memakai hasil HTML/CSS yang ada setelah endpoint artefaknya aman.
5. Tambahkan dokumentasi publik di repo Satsetui: `docs/api/README.md`, `docs/api/openapi.yaml`, panduan autentikasi/rotasi kunci, quickstart cURL/PHP/JavaScript, ketiga profil output dan skema tema/Social Kit, billing/refund, idempotensi, polling/webhook, status/error, batas pemakaian, changelog, dan kebijakan versi. Contoh harus memakai kredensial palsu yang jelas.
6. Jalankan pilot Fabriku lebih dulu, lalu satu integrator eksternal non-Fabriku untuk membuktikan API tidak mengandung asumsi khusus Fabriku. Baru setelah itu buka pendaftaran API lebih luas.

## Kriteria penerimaan

- Dua klien API dengan akun berbeda tidak dapat melihat generation, kredit, atau artefak satu sama lain.
- Sepuluh retry `POST` dengan kunci idempotensi yang sama menghasilkan satu generation dan satu charge.
- Website satu halaman dan banyak halaman mencapai status terminal melalui worker tanpa SSE/browser; gagal/timeout tidak meninggalkan saldo terpotong tanpa status yang dapat diaudit.
- Fabriku dapat mengimpor `business-site-theme-v1`, mengeditnya secara lokal, dan menerbitkan website mode produk, jasa, maupun gabungan tanpa dependensi runtime ke Satsetui untuk setiap page view.
- Fabriku dapat menerima slide individual `social-kit-v1`, menampilkannya sebagai draf, dan mengekspor/menjadwalkannya setelah review; retry generasi tidak memotong kredit dua kali.
- OpenAPI dan contoh quickstart lolos contract test; status dan error yang terdokumentasi cocok dengan respons nyata.

## Dasar kode yang diperiksa

Repo Satsetui: `app/Http/Controllers/GenerationController.php` (termasuk `generateSocialKit`), `app/Http/Requests/GenerateBlueprintRequest.php`, `app/Http/Requests/StoreSocialKitRequest.php`, `app/Services/GenerationService.php`, `app/Services/CreditService.php`, `app/Jobs/ProcessTemplateGeneration.php`, `app/Models/Generation.php`, dan `routes/web.php`. Dokumen arsitektur yang sudah ada: `/home/fauzan/dev/satsetui/docs/architecture.md`.
