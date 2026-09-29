# Checklist rilis Website Usaha

Status 29 September 2026: kode storefront dan image `290926-2` sudah teruji di produksi. DNS wildcard, target CNAME, dan fallback origin sudah aktif. Image lama mengembalikan halaman utama untuk host acak, bukan 404, sehingga rollback harus memakai backup Compose yang disimpan di server.

## Urutan aman

1. Build dan deploy image baru, teruskan `STOREFRONT_DOMAIN`, `CLOUDFLARE_TOKEN`, `CLOUDFLARE_ZONE_ID`, dan `CLOUDFLARE_CNAME_TARGET` ke container. Simpan token di `.env` server dengan izin file terbatas. Jalankan migrasi dan pastikan queue worker aktif.
2. Uji origin langsung memakai `Host: nama-acak.fabriku.biz.id`: harus 404 dari Fabriku, bukan halaman Aspri atau halaman utama Fabriku. Uji juga `fabriku.id` dan vhost lama tetap normal. `nginx -t` harus lulus.
3. Buat record proxied `*.fabriku.biz.id` ke origin dan `sites.fabriku.biz.id` sebagai target CNAME. Uji subdomain acak melalui Cloudflare tetap 404 dan subdomain situs terbit menampilkan situs merchant.
4. Atur `sites.fabriku.biz.id` sebagai fallback origin Cloudflare for SaaS, tunggu status Active. Verifikasi mode SSL/TLS dan TLS origin sebelum mengaktifkan domain pelanggan. Cloudflare meneruskan Host/SNI domain pelanggan ke fallback origin; sertifikat Origin CA wildcard Fabriku saja tidak otomatis mencakup domain pelanggan. Jangan turunkan mode TLS diam-diam.
5. Uji satu domain pilot: pendaftaran, record verifikasi TXT, CNAME, status custom hostname Active, status sertifikat Active, HTTP 200 situs merchant, dan redirect 301 dari subdomain Fabriku. Uji pula pemesanan dan prospek, email/Telegram staf, WhatsApp, situs dijeda, serta suspend/laporan.

## Batas MVP

- Pembeli mengajukan pesanan; admin/staf mengonfirmasi dan menerima pembayaran di luar Fabriku. Tidak ada payment gateway checkout publik.
- Tiga preset desain lokal tersedia. API generasi desain dan Social Kit Satsetui, editor section bebas, Pusat Sosial/Repliz, serta perubahan paket/harga belum tersedia.
- Domain mandiri baru dianggap aktif jika Cloudflare hostname, sertifikat, dan DNS CNAME semuanya siap. Domain apex pelanggan yang tidak mendukung CNAME memerlukan layanan DNS yang mendukung flattening/ALIAS atau pilihan `www`.
- Verifikasi DNS MVP membaca CNAME publik. Bila DNS pelanggan memakai Cloudflare, pilih `DNS only` pada CNAME agar target dapat dibaca; O2O/proxied CNAME belum didukung oleh pemeriksa Fabriku.

Rujukan operasional: [setup Cloudflare for SaaS](https://developers.cloudflare.com/cloudflare-for-platforms/cloudflare-for-saas/start/getting-started/), [detail Host dan SNI ke origin](https://developers.cloudflare.com/cloudflare-for-platforms/cloudflare-for-saas/reference/connection-details/), [Origin CA](https://developers.cloudflare.com/ssl/origin-configuration/origin-ca/).
