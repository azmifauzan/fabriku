# Google Analytics untuk website toko

Pengelola membuka **Website → Pengaturan → SEO Google**, mengisi ID pengukuran GA4 (`G-…`), lalu memilih **Simpan Google Analytics**. Kosongkan dan simpan untuk mematikan pelacakan. ID tersimpan pada `business_sites.ga_measurement_id`; nilai bawaan semua toko adalah `null`.

Endpoint `POST /website/analytics` hanya menerima ID atau nilai kosong, memerlukan pengelola tenant dengan fitur website, dan memperbarui situs milik tenant yang sedang masuk. Pengaturan identitas, SEO, kategori, workflow, desain, produk, stok, dan pesanan tidak diperbarui oleh endpoint ini.

Renderer menyisipkan tag GA resmi melalui Blade bawaan pada halaman publik. Middleware memberi nonce acak per respons dan membatasi sumber skrip ke tag Google. HTML tema tetap disanitasi; skrip dari tema dan handler inline tetap dibuang. Tanpa ID valid, `script-src 'none'` tetap berlaku. Preview desain tidak memuat tag karena tidak memperoleh nonce storefront. Halaman privasi menampilkan pemberitahuan Analytics hanya untuk toko yang mengaktifkannya. Personalisasi iklan dan Google Signals dimatikan pada tag.

Untuk DSFA, properti GA4 **DSFA Homemade** berada pada akun **Blog**, property ID `560891733`, web stream `16212340161`, measurement ID `G-1WSTF7EL88`, zona waktu Jakarta, mata uang Rupiah. Enhanced measurement dimatikan pada stream ini; pemasangan awal mengukur kunjungan dasar tanpa event formulir/pesanan.

Migrasi hanya menambahkan kolom nullable. Saat rollback image, biarkan kolom tambahan tetap ada agar ID tersimpan; menurunkan migrasi akan menghapus ID tersebut. Backup database dan Compose dibuat sebelum rilis. Verifikasi setelah aktivasi: request GA `page_view` berhasil, CSP tidak memblokir tag, toko tanpa ID tidak memuat GA, dan data tenant selain ID DSFA tetap sama.
