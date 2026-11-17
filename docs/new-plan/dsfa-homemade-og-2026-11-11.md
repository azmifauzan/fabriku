# OG image DSFA Homemade, 11 November 2026

Satu banner merek untuk seluruh halaman DSFA, mengikuti referensi pemilik: logo D’Sfa Homemade, krem, hijau zaitun, cheesecuit, takoyaki, jus, dan puding. Aset dibuat dengan ImageGen lalu diturunkan menjadi JPEG 1200×630, 269.454 byte. Sumber final ada di `dsfa-homemade/og-share-20261111.jpg`.

URL publik stabil, tanpa tanda tangan kedaluwarsa:

https://is3.cloudhost.id/fabriku/tenants/7/site/og-share-20261111.jpg

SHA-256: `17fdd506c453eefecbfd5f25491053d4167ef6d0a6bead0ddea415c92eb00781`. Content-Type `image/jpeg`, cache publik satu tahun dengan `immutable`; revisi berikutnya harus memakai nama berkas baru.

## Perilaku

`BusinessSite.seo.og_image_url` diprioritaskan oleh `ThemeRenderer` untuk OG dan Twitter. Hanya URL HTTP/HTTPS valid yang diterima; nilai kosong atau tidak valid memakai urutan fallback lama. Banner diaktifkan hanya pada site 1, tenant 7, domain `dsfahomemade.com`. Situs lain tetap memakai foto produk/jasa, logo, atau foto katalog yang tersedia.

Penyimpanan pengaturan website mempertahankan metadata SEO yang sudah ada sambil mengganti title/description yang divalidasi. URL banner tidak dapat ditimpa melalui field request yang tidak didukung. Foto katalog dan Product JSON-LD tetap berasal dari produk.

## Rilis dan rollback

Rilis akhir: `azmifauzan/fabriku:111126-2`, digest `sha256:9bd07baaafef731e645c8c3c0419e23ba348833f50d604a6bf61b2e5295809e2`.

Image dibangun dari digest produksi `091126-4`, dengan hanya dua berkas yang diganti: `ThemeRenderer.php` dan `WebsiteController.php`. Hash sebelum perubahan cocok dengan produksi; perubahan lokal lain tidak ikut dirilis. Tidak ada dependency atau migrasi baru.

Cadangan awal di server: `/home/ubuntu/fabriku/backups/111126-1/` berisi Compose sebelum OG, `seo-before.json`, dan `og-settings.php`. Cadangan Compose sebelum perbaikan penyimpanan ada di `backups/111126-2/`. Metadata diperiksa melalui transaksi update lalu rollback sebelum penerapan; penerapan memakai row lock dan menolak perubahan SEO yang terjadi sejak preview. Atribut situs selain SEO dan waktu perubahan diverifikasi tetap sama.

Untuk membatalkan seluruh pekerjaan: salin `og-settings.php` dan `seo-before.json` dari cadangan host ke container, dengan snapshot pada `/var/www/html/storage/app/private/dsfa-og-20261111.json`; jalankan skrip dengan argumen `rollback`. Skrip menolak rollback bila SEO telah berubah sejak penerapan. Pulihkan Compose dari `backups/111126-1/docker-compose.yml` lalu buat ulang service `fabriku`; pertahankan `.env` dan APP_KEY produksi. Objek gambar dapat tetap disimpan karena tidak lagi dirujuk.

## Verifikasi

- Suite lengkap sebelum perbaikan penyimpanan: 668 lulus, 6 dilewati, 4.794 assertions. Setelah penambahan tes penyimpanan: 36 tes OG dan pengelolaan website lulus, 368 assertions. Pint dan `git diff --check` lulus.
- 27 URL publik, termasuk halaman sitemap, privasi, keranjang, dan lapor: HTTP 200; OG/Twitter memakai banner; title, description, canonical, dan header robots sama dengan snapshot sebelum rilis. Keranjang dan lapor tetap `noindex, nofollow`.
- Gambar publik: HTTP 200, MIME/cache benar, isi cocok dengan checksum lokal. Ukuran dan komposisi JPEG diperiksa langsung.
- Renderer mempertahankan fallback tanpa banner dan menolak banner invalid; Product JSON-LD tetap memakai foto produk. Pengaturan website mempertahankan banner saat title/description diubah.

Cache preview milik WhatsApp/Facebook/Threads tidak diuji melalui pembagian pesan. Tautan yang pernah dibagikan dapat menampilkan cache lama sampai platform mengambil ulang metadata.
