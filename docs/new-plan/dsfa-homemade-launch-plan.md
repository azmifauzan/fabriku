# Rencana DSFA Homemade di Fabriku

Tanggal pemeriksaan: 23 Oktober 2026. Dokumen ini membedakan hasil pemeriksaan, penyiapan akun produksi, dan pekerjaan peluncuran yang belum dijalankan.

## Tujuan dan keputusan

DSFA Homemade adalah usaha rumahan suami istri yang menjual cheesecuit, takoyaki, puding, dan jus. Website akan memakai dsfahomemade.com dengan desain sesuai logo pemilik dan menargetkan pencarian pembelian di Bandung. Menu dessert lain dapat ditambahkan melalui katalog yang sama.

Pemilik mengizinkan usaha percobaan D'Garment pada akun azmifauzan@gmail.com diubah menjadi DSFA Homemade dan data operasional lamanya dihapus. Akun Google pemilik digunakan kembali. Tidak perlu membuat akun kedua, mengganti kata sandi, atau mengembangkan sistem banyak usaha untuk tugas ini.

Lingkup tahap ini: pemeriksaan kode dan produksi, penyiapan akun, pemeriksaan domain, dan rencana peluncuran. Pembuatan desain akhir, pengisian katalog berharga, publikasi, dan pengujian pesanan pelanggan dilakukan pada tahap implementasi setelah data usaha tersedia.

## Hasil pemeriksaan

| Bagian | Temuan dan akibatnya |
| --- | --- |
| Akun produksi | User 15, tenant 7, admin aktif dan email terverifikasi; Google sudah tertaut. Sebelum penyiapan bernama D'Garment, kategori garment. |
| Data percobaan | Sebelum reset: 3 pengguna, 2 pesanan, 1 stok Mukena, 1 produk publik Mukena, 2 bahan, 1 resep, 1 persiapan, 1 produksi, 2 staf, 3 versi desain. Bukan data usaha makanan yang dapat dipakai. |
| Langganan | Core aktif sampai 30 Juni 2027; Pro belum aktif. Pertahankan masa aktif dan 3 catatan pembayaran langganan. Jangan membuat transaksi berbayar atau memberi Pro tanpa keputusan pemilik. |
| Website | Satu `BusinessSite` per tenant. Profil, tema berversi, katalog, draft desain Satsetui, dan halaman tambahan sudah tersedia. |
| SEO dasar | HTML Blade dari server, title/deskripsi, canonical, Open Graph, robots.txt, sitemap.xml, dan halaman tambahan dengan SEO sendiri sudah ada. Tidak perlu membangun mesin halaman baru. |
| URL halaman tambahan | Rute yang tersedia adalah `/halaman/{slug}`. Gunakan rute ini; URL root baru tidak diperlukan untuk memenuhi target SEO. |
| Desain custom | `SiteThemeVersion` mendukung shell, sections, CSS, serta slot katalog/WhatsApp native. Jalur editor Satsetui dan impor draft sudah ada. Kredit Satsetui terpisah dari langganan Fabriku. |
| Pembatasan desain | HTML/CSS disanitasi; storefront memakai CSP `script-src 'none'`. Hindari carousel, menu, pelacak, atau interaksi yang bergantung JavaScript. |
| Cloudflare | Setelah pemilik memperluas token, zone dsfahomemade.com terlihat dan aktif. Nameserver: jessica.ns.cloudflare.com dan phil.ns.cloudflare.com. |
| DNS saat pemeriksaan | Apex A dsfahomemade.com sudah menuju 43.129.52.206 dan diproxy Cloudflare. Belum ada record www pada hasil satu halaman yang memuat seluruh record. |
| TLS/domain Fabriku | Pengaturan SSL zone bernilai Full. Belum ada custom hostname dsfahomemade.com pada zone storefront fabriku.biz.id. Record A saja belum membuktikan routing aplikasi dan TLS origin siap. |
| Penyimpanan logo | Produksi menyediakan disk public dan symlink public/storage; logo disimpan di sana dan bertahan melewati deploy. **Koreksi 26 Oktober:** S3 (`fabriku_s3`, bucket `fabriku`, endpoint `is3.cloudhost.id`) ternyata sudah dikonfigurasi di produksi; klaim "S3 belum dikonfigurasi" di sini keliru. Foto produk memakai S3 sesuai konvensi aplikasi. |
| Skema tenant | Beberapa atribut di `$fillable` model Tenant, termasuk email dan logo_url, tidak mempunyai kolom pada skema produksi. Simpan kontak/logo pada `BusinessSite.profile` dan `SystemSetting`, sesuai pemilik data yang sudah tersedia. Tidak perlu migrasi kolom baru. |

Pemeriksaan graph memakai Tier 2, generasi 2026-10-23T08:58:48Z, disertai pemeriksaan coverage berkas yang dipakai. Tidak ada gap tercatat pada berkas terkait; hal ini bukan jaminan graph lengkap. Trace untuk nama umum `create` menghasilkan banyak hubungan heuristik yang tidak relevan, sehingga alur domain dipastikan lewat sumber controller dan service. Hash sumber onboarding, renderer, dan BusinessSite di produksi sama dengan checkout lokal.

## Penyiapan akun produksi

Gunakan tenant 7 dan user pemilik 15. Ubah nama menjadi DSFA Homemade dan kategori menjadi `food` agar terminologi resep/bahan serta pelacakan batch/kedaluwarsa sesuai makanan.

1. Jalankan simulasi reset di transaksi dan rollback. Validasi identitas email/tenant/site, konflik slug, relasi lintas tenant, login, masa langganan, dan ledger pembayaran.
2. Hapus data operasional percobaan beserta relasi anaknya melalui foreign key aktif. Hapus dua akun/staf percobaan, pertahankan akun pemilik. Jangan menonaktifkan foreign key.
3. Pertahankan billing, catatan audit platform, log email/kampanye, laporan penyalahgunaan, dan pencatatan pemakaian platform. Ini berbeda dari isi operasional toko yang direset.
4. Sediakan lokasi Dapur Utama serta kategori Cheesecuit, Takoyaki, Puding Buah, dan Jus. Jangan mengarang jumlah stok, resep, tanggal kedaluwarsa, atau harga.
5. Simpan logo asli yang diberikan pemilik pada penyimpanan public. Gunakan pada profil website dan identitas perusahaan.
6. Jadikan website draft, slug `dsfa-homemade`, dengan profil dan metadata awal DSFA. Hapus referensi tema dan katalog D'Garment.
7. Pertahankan WhatsApp dan alamat kosong sampai dikonfirmasi. Nomor dan alamat dari akun percobaan tidak menjadi sumber data usaha baru.
8. Pilih pemilik sebagai penerima notifikasi. Uji pengiriman notifikasi saat implementasi; jangan mengirim pesanan atau email percobaan ke pihak lain pada tahap pemeriksaan.

Website draft tidak dapat dibuka sebagai storefront publik. Preview setelah desain dibuat memakai jalur preview Fabriku. Jangan menandai domain aktif atau situs terbit hanya agar URL tampak bekerja.

## Arah desain custom

Sumber identitas adalah logo asli pemilik: tulisan D'Sfa Homemade, topi koki, whisk, hati, hijau zaitun, cokelat, dan latar krem. Nama teks serta metadata distandardisasi sebagai DSFA Homemade agar pencarian merek konsisten.

Usulan arah: hangat, sederhana, dan mengutamakan makanan. ENERGY 2 / RHYTHM 2 / MOTION 1. Ini spesifikasi desain untuk implementasi, bukan hasil visual yang sudah diuji.

| Keputusan | Alasan |
| --- | --- |
| Latar krem `#FCF9F0` | Mengikuti latar logo dan memberi ruang bagi foto makanan asli. |
| Teks gelap `#302D27`, hijau tua `#424B34` untuk tombol/tautan | Menjaga keterbacaan; warna logo yang lebih muda tidak otomatis layak untuk teks kecil. Uji kontras sebelum publikasi. |
| Zaitun `#747C60`, cokelat `#946D50` sebagai warna pendukung | Mengikuti identitas logo tanpa memberi semua elemen warna aksen. |
| Heading Georgia dan isi system sans sebagai awal | Memberi karakter hangat tanpa memuat font pihak ketiga; evaluasi kesesuaian pada preview. |
| Foto produk menjadi fokus utama | Pengunjung perlu melihat barang dan kemasannya. Logo tidak menggantikan foto produk. |
| Navigasi pendek yang membungkus di HP | Beranda, Menu, Cara Pesan, Kontak. Tetap dapat dipakai tanpa JavaScript. |
| Elemen topi/whisk tetap pada logo | Tidak perlu membuat ilustrasi atau identitas merek pengganti. |

Susunan beranda:

1. Logo/nama dan navigasi ringkas.
2. H1: “Cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung.” Foto produk asli menjadi fokus visual. CTA `Lihat menu` dan, setelah nomor benar, `Tanya pesanan lewat WhatsApp`.
3. Pilihan empat menu yang mengarah ke halaman spesifik, dengan foto dan harga setelah tersedia.
4. Cerita singkat usaha suami istri berdasarkan informasi pemilik; tanpa tahun berdiri atau klaim jumlah pelanggan yang dibuat-buat.
5. Cara pemesanan, tenggat pre-order, dan area kirim sesuai kebijakan usaha yang dikonfirmasi.
6. Kontak dan tautan halaman menu, privasi, serta kebijakan pemesanan. Testimoni hanya muncul bila ada ulasan asli dengan izin.

Tidak mencantumkan klaim sehat, rendah gula, tanpa pengawet, halal tersertifikasi, tahan sekian hari, siap hari ini, atau gratis ongkir sebelum ada sumber dari pemilik. Foto AI tidak dipakai sebagai foto barang yang dijual.

## Peta SEO dan halaman custom

Kata kunci berikut berasal dari tujuan pemilik. Volume pencarian, tingkat persaingan, dan urutan popularitas belum diukur. Prioritas awal mengikuti empat menu yang sudah dijual.

| URL yang direncanakan | Target utama | Variasi long tail untuk divalidasi | Title usulan |
| --- | --- | --- | --- |
| `/` | DSFA Homemade Bandung | dessert homemade Bandung, camilan rumahan Bandung | DSFA Homemade Bandung \| Cheesecuit, Takoyaki, Puding & Jus |
| `/halaman/jual-takoyaki-bandung` | jual takoyaki Bandung | pesan takoyaki Bandung, takoyaki rumahan Bandung | Jual Takoyaki Bandung \| DSFA Homemade |
| `/halaman/puding-buah-bandung` | puding buah Bandung | jual puding buah Bandung, pesan puding buah Bandung | Puding Buah Bandung \| Pesan di DSFA Homemade |
| `/halaman/cheesecuit-bandung` | cheesecuit Bandung | jual cheesecuit Bandung, pesan cheesecuit Bandung | Cheesecuit Bandung \| DSFA Homemade |
| `/halaman/jus-homemade-bandung` | jus homemade Bandung | jual jus homemade Bandung, pesan jus buah Bandung | Jus Homemade Bandung \| DSFA Homemade |
| `/halaman/cara-pesan` | cara pesan DSFA Homemade | pre-order DSFA Homemade Bandung | Cara Pesan DSFA Homemade Bandung |
| `/halaman/area-pengiriman` | pengiriman DSFA Homemade Bandung | dessert homemade [kecamatan sebenarnya] | Area Pengiriman DSFA Homemade Bandung |

Setiap halaman menu wajib memiliki isi yang berbeda: foto asli, varian, isi/porsi, harga, minimum pesanan, jadwal/lead time, cara simpan, informasi alergen yang relevan, area kirim, serta jawaban pertanyaan khusus menu tersebut. Cantumkan hanya fakta yang sudah diberikan pemilik. Harga di halaman harus konsisten dengan katalog; hindari menyalin angka harga ke banyak tempat yang tidak ikut diperbarui.

Halaman takoyaki, misalnya, membahas varian isian, jumlah per kemasan, saus, dan ketentuan pemesanan yang benar-benar tersedia. Halaman puding membahas ukuran, buah yang tersedia, kebutuhan pendinginan, dan waktu pemesanan. Detail cheesecuit dan jus mengikuti resep/varian usaha, bukan asumsi dari nama menu.

Gunakan satu URL per maksud pencarian. Variasi “jual”, “pesan”, dan “homemade” dapat ditulis alami pada halaman yang sama. Jangan membuat halaman duplikat untuk setiap ejaan kata kunci. Jangan membuat puluhan halaman kecamatan yang hanya mengganti nama tempat.

Untuk Bandung dan sekitarnya, konfirmasikan dahulu wilayah yang sungguh dilayani, ongkir/metode pengiriman, dan batas jarak untuk makanan dingin. Cimahi, Kabupaten Bandung, Bandung Barat, serta kecamatan tertentu hanya ditargetkan setelah terkonfirmasi. Permintaan acara, arisan, hampers, atau snack box baru menjadi halaman tersendiri jika paket tersebut benar-benar dijual.

Detail SKU tetap di `/produk/{slug}`. Halaman SEO menangani pertanyaan pembelian dan menautkan SKU relevan; halaman SKU menangani varian dan pesanan. Jangan menargetkan judul/isi yang sama pada keduanya.

## Implementasi pada Fabriku

Gunakan `BusinessSite`, `SiteThemeVersion`, `SiteContentPage`, dan katalog native. Desain custom disimpan sebagai snapshot tema tenant, bukan kondisi khusus DSFA pada renderer seluruh pelanggan. HTML/CSS dan tautan harus melalui sanitizer yang sudah ada.

Jalur Satsetui tetap cocok jika pemilik ingin mengedit desain di sana. Gunakan draft, preview, lalu impor/publish melalui alur yang ada. Jangan memulai generasi yang memakai kredit pada tahap penyusunan rencana ini. Untuk satu desain yang disiapkan langsung, snapshot tema dapat dibuat tanpa menambah editor atau layanan baru; sediakan cara preview/terapkan yang konsisten dengan lifecycle tema Fabriku.

Slot katalog, keranjang, checkout, harga, stok, dan WhatsApp memakai komponen Fabriku. Jangan memasukkan formulir checkout custom dari HTML desain. Pesanan publik harus melalui alur native dan menunggu konfirmasi toko; jangan memberi kesan pembayaran otomatis.

Rencana verifikasi kode dan data:

| Pemilik perilaku | Bagian yang disentuh saat implementasi | Pemeriksaan |
| --- | --- | --- |
| Identitas toko | BusinessSite.profile, SystemSetting, Tenant.name/category | Login Google dan langganan tetap; identitas lama tidak tampil. |
| Tampilan | SiteThemeVersion dan aset logo/foto tenant | Draft/preview/publish sesuai alur; sanitizer tidak merusak CSS, navigasi, atau tombol. |
| Halaman SEO | SiteContentPage dan tautan tema | Satu H1, title/deskripsi unik, canonical tepat, HTML server, tautan internal dan sitemap. |
| Katalog | InventoryItem dan SiteProduct melalui pola yang ada | Harga/stok dari data nyata; ID lintas tenant ditolak; tidak ada produk Mukena. |
| Domain | WebsiteDomainController, CloudflareDomains, DNS serta proxy origin | Kepemilikan, status hostname+sertifikat, DNS, respons Host di origin dan HTTPS publik. |
| Notifikasi | BusinessSite.recipients dan job native | Owner yang benar menerima notifikasi; kegagalan tidak menghilangkan pesanan. |

Structured data dapat ditambahkan pada renderer bersama jika diperlukan setelah data produk/lokasi lengkap. Script JSON-LD pada HTML merchant akan dibuang sanitizer; jangan melonggarkan CSP secara global hanya untuk DSFA. Bila dikerjakan, hasilkan JSON-LD dari data server yang tervalidasi dan uji pada dua tenant. Jangan mengarang rating, alamat, koordinat, stok, atau harga untuk markup.

## Domain dan peluncuran

1. Tentukan kebutuhan paket Pro untuk domain custom berdasarkan aturan paket aktif. Core akun ini aktif; akses Pro belum ada. Jangan membuat tagihan atau mengubah expiry hanya untuk melewati gerbang fitur.
2. Periksa konfigurasi virtual host nginx/reverse proxy di server. Pastikan Host dsfahomemade.com mencapai container Fabriku dan tidak jatuh ke aplikasi lain. Pemeriksaan ini belum dilakukan pada tahap rencana.
3. Gunakan jalur domain Fabriku: daftarkan custom hostname pada zone storefront, simpan ID/status pending pada BusinessSite, lalu penuhi record ownership dan validasi sertifikat yang dikembalikan Cloudflare.
4. Tinjau respons custom hostname untuk menentukan record target. Jangan langsung menimpa record A apex yang sudah ada. Bila diperlukan CNAME apex, gunakan flattening Cloudflare dan target storefront `sites.fabriku.biz.id` sesuai konfigurasi. DNS MX/TXT email tetap dipertahankan.
5. Pastikan TLS origin valid untuk hostname yang dipakai, lalu gunakan Full (strict) setelah verifikasi. Sertifikat edge aktif dan mode Full saja belum membuktikan sertifikat origin cocok.
6. Tambahkan dukungan www dengan TLS dan redirect 301 ke apex yang benar. Middleware Fabriku saat ini mencocokkan custom domain secara persis; menambah CNAME www saja tidak otomatis membuat rute www berfungsi.
7. Tandai domain `active` hanya setelah hostname, sertifikat, tujuan DNS, dan akses HTTPS berhasil. Aplikasi kemudian mengarahkan subdomain Fabriku ke domain custom dengan 301 sambil mempertahankan path/query.
8. Terbitkan saat menu, kontak, ketentuan pemesanan, desain, dan pengujian selesai. Selama draft, domain tidak diiklankan sebagai website siap menerima pelanggan.

Tidak ada perubahan DNS, mode SSL, pendaftaran hostname, aktivasi Pro, atau cutover produksi pada tahap pemeriksaan/rencana ini. Record A yang sudah dipasang pemilik tetap ada.

## Data usaha yang diperlukan

- Nomor WhatsApp pemesanan, kecamatan/lokasi, serta area pengiriman yang benar. Alamat rumah lengkap hanya ditampilkan jika pemilik memang menghendaki kunjungan/pengambilan.
- Foto asli tiap menu dan kemasan, daftar varian, isi/berat, harga, minimum pesanan, dan opsi kemasan.
- Jadwal usaha, stok siap jual versus pre-order, tenggat pemesanan, cara pengiriman, ongkir, dan metode pembayaran.
- Cara simpan, masa simpan berdasarkan praktik usaha, serta informasi bahan/alergen. Jangan menyimpulkan dari menu umum.
- Akun sosial/Maps yang sudah ada, bila ada; ulasan hanya jika asli dan boleh ditampilkan.
- Keputusan apakah istri memerlukan akun staf tersendiri; alamat emailnya belum diberikan. Jangan menggunakan akun percobaan lama untuk identitas baru.

## Urutan pengerjaan dan bukti selesai

| Tahap | Hasil | Bukti selesai |
| --- | --- | --- |
| 1. Akun | Tenant DSFA kategori food; data percobaan bersih; logo dan profil draft | Simulasi rollback lalu commit, identitas/ledger tidak berubah, query ulang setelah commit. |
| 2. Konten usaha | Katalog empat menu dan kebijakan pemesanan | Pemilik mengonfirmasi harga, kontak, foto, stok/PO, dan area kirim. |
| 3. Desain custom | Beranda dan layout halaman menu sesuai logo | Preview HP/desktop; semua tautan/tombol berfungsi; tidak ada klaim/foto produk rekaan. |
| 4. Halaman SEO | Empat landing page menu + cara pesan + area layanan | Meta/canonical unik, HTML server, tautan terkait, halaman draft tidak bocor ke sitemap. |
| 5. Domain | Apex HTTPS dan www redirect, subdomain mengarah ke canonical | Tes HTTPS publik dan origin, sertifikat, hostname, path/query redirect. |
| 6. Pemesanan | Alur katalog sampai permintaan diterima pemilik | Tes terisolasi tanpa pembayaran nyata; harga/stok server, batas tenant, notifikasi, dan status pesanan benar. |
| 7. Pencarian lokal | Search Console dan, jika memenuhi syarat, Google Business Profile | Verifikasi domain, submit sitemap, inspeksi URL. Jangan memublikasikan alamat rumah sebagai toko walk-in jika bukan. |

Saat implementasi, jalankan tes yang sudah tersedia untuk routing storefront, katalog, checkout, domain, serta renderer/sanitizer sesuai bagian yang berubah. Tambahkan tes hanya untuk perilaku baru yang belum terbukti. Jangan menjalankan test suite yang menghapus database pada produksi.

Gerbang desain saat akan diserahkan: kontras WCAG AA, fokus keyboard, reflow 360/390/768/1440 px, target sentuh, tidak ada overflow, gambar proporsional, loading/error/empty state native, serta klik setiap tautan/kontrol. Uji ini belum dapat dinyatakan lulus karena desain akhir belum dibuat.

Setelah tayang, gunakan Search Console untuk melihat impresi, klik, query, dan halaman yang terindeks. Catat pesanan valid serta sumbernya dengan fitur yang tersedia; jangan memasang analytics script yang bertentangan dengan CSP. Evaluasi awal setelah empat minggu pengamatan sejak terindeks, lalu tinjau kembali setelah delapan sampai dua belas minggu. Ini jadwal evaluasi, bukan janji posisi Google.

## Batas pemeriksaan

Audit ini menelusuri bagian terkait website, akun, dan domain, bukan audit seluruh Fabriku. Belum ada riset volume keyword/SERP, akses Search Console/Maps, pemeriksaan konfigurasi nginx/TLS origin, aktivasi custom hostname, pengujian pesanan end-to-end, atau QA desain final. Detail data produksi dan status eksekusi akun dicatat pada bagian berikut setelah verifikasi.

## Catatan eksekusi akun

Selesai pada 23 Oktober 2026. Simulasi transaksi berhasil dan di-rollback sebelum eksekusi final. Transaksi final kemudian commit, dan data dibaca ulang melalui proses SSH terpisah.

- Tenant 7 sekarang DSFA Homemade, kategori `food`, aktif. User pemilik 15 tetap admin aktif, email terverifikasi, dan Google tetap tertaut.
- Website 1 sekarang slug `dsfa-homemade`, mode `produk`, status `draft`. Profil dan metadata awal DSFA tersimpan. Tema lama dan tanggal publikasi dikosongkan.
- Stok, katalog publik, pesanan, bahan, pelanggan, resep, persiapan, produksi, serta dua akun/staf percobaan sudah dibersihkan. Tersisa satu akun pemilik.
- Dapur Utama dan empat kategori menu sudah dibuat. Tidak ada harga, stok, atau produk jual rekaan.
- Penerima notifikasi situs adalah pemilik, user 15.
- Logo asli tersimpan pada `https://fabriku.id/storage/logos/dsfa-homemade.png`, ukuran 1.031.880 byte. SHA-256 file produksi sama dengan gambar pemilik: `ae01d3dd94e4f8a97660c40ffc3e2eb11e4737ab9538fa783c3499b690d53f31`.
- Pemeriksaan transaksi lulus: atribut login tidak berubah, tanggal/hak langganan tidak berubah, tiga ID pembayaran langganan tetap, dan jumlah baris tenant lain pada seluruh tabel bertenant tidak berubah. Pemeriksaan ini tidak menyatakan seluruh isi database lain diaudit.
- Pemanggilan controller Website sebagai pemilik menghasilkan respons Inertia 200 dengan komponen `Website/Index`, profil DSFA, katalog kosong, dan satu pengguna pemilik. Ini pemeriksaan controller, bukan pengujian login melalui browser atau seluruh middleware HTTP.
- Core tetap aktif sampai 30 Juni 2027; tidak ada pemberian Pro, pembelian kredit, transaksi tagihan, email, atau pesan eksternal yang dibuat.

Website belum dipublikasikan, tema/halaman custom belum dibuat, dan domain belum dihubungkan pada BusinessSite. Pemeriksaan DNS/Cloudflare pada dokumen ini bersifat baca saja. Nomor WhatsApp, lokasi/area kirim, harga, dan foto produk masih diperlukan untuk implementasi.

## Pembaruan pemeriksaan 26 Oktober 2026

Pemeriksaan ulang baca saja. Tidak ada perubahan data, DNS, atau kode pada tahap ini.

### Status terverifikasi

| Bagian | Hasil |
| --- | --- |
| Akun | Tenant 7 tetap DSFA Homemade, `food`, aktif. User 15 (azmifauzan@gmail.com) admin aktif, Google tertaut. Tidak perlu membuat akun baru. |
| Website | Site 1 `dsfa-homemade`, mode `produk`, `draft`, 0 versi tema, 0 halaman, 0 item stok. Profil/SEO awal DSFA dan logo tersimpan. |
| Routing origin | `https://dsfahomemade.com` sudah sampai ke aplikasi Fabriku dan menjawab 404 "Halaman Tidak Ditemukan - Fabriku" karena domain belum `active` dan situs masih draft. Artinya nginx/origin sudah meneruskan Host ini ke container Fabriku; langkah 2 bagian Domain sudah terjawab. |
| Cloudflare | Zone dsfahomemade.com dan fabriku.biz.id ada di akun yang sama. DNS dsfahomemade.com hanya satu record: A apex → 43.129.52.206, proxied. Belum ada www, MX, atau TXT. SSL zone `full`. Belum ada satu pun custom hostname di zone fabriku.biz.id (jalur Cloudflare for SaaS belum pernah dipakai pelanggan nyata); fallback origin `fallback.fabriku.biz.id` aktif. |
| Gerbang Pro | `ResolveStorefront` dan `WebsiteDomainController` tidak memeriksa fitur `custom_domain`; gerbang hanya ada di konfigurasi paket/UI. |
| Image produksi | `docker-compose.yml` server memakai `hermes-local/fabriku:php-security-20261022`, bukan tag `azmifauzan/fabriku:ddmmyy-N` dari `main`. Sebelum deploy kode apa pun, pastikan isi image itu sudah ada di `main` agar tidak hilang. |
| Halaman custom | Tidak ada UI untuk membuat `SiteContentPage` manual. Halaman hanya lahir dari impor Satsetui; UI hanya bisa mengubah SEO title/description dan tampil di footer. |
| CSS desain | CSS custom dibaca dari `theme['satsetui_css']` (juga untuk versi manual) atau `site_content_pages.css`; `url()`, `@import`, dan font eksternal ditolak sanitizer. Gambar harus lewat `<img>`; font memakai font sistem. |

### Riset SERP awal (bukan riset volume)

Pencarian web 26 Oktober 2026 untuk "jual takoyaki bandung" dan "puding buah bandung pesan" tidak menampilkan situs penjual Bandung yang khusus; hasil didominasi marketplace, artikel resep, dan dokumen akademik. Persaingan halaman khusus tampak rendah, tetapi volume pencarian tetap belum diukur.

**Risiko "cheesecuit":** hasil untuk "cheesecuit bandung" didominasi artikel oleh-oleh yang merujuk satu toko bernama Cheesecuit di Jl. Ir. H. Juanda (Dago) yang juga menjual puding. Pencari kata ini kemungkinan besar mencari toko tersebut, dan nama itu mungkin merek. Usulan: periksa PDKI (pdki-indonesia.dgip.go.id) sebelum memakai "Cheesecuit" sebagai nama produk/judul; bila terdaftar, gunakan nama generik seperti "cheese biscuit dessert" dengan "cheesecuit" hanya sebagai deskripsi bila aman, dan jangan menyiratkan afiliasi dengan toko tersebut. Prioritas SEO awal sebaiknya takoyaki, puding buah, dan jus, yang tidak berbenturan dengan merek.

### Usulan jalur implementasi (menunggu keputusan pemilik)

1. **Desain dan halaman sebagai data, tanpa deploy kode.** Buat satu `SiteThemeVersion` (shell header/footer, section beranda, CSS palet logo) sebagai draft dan enam `SiteContentPage` sesuai peta SEO, langsung di DB produksi lewat skrip transaksi yang diuji dulu di lokal. Renderer, sanitizer, sitemap, dan canonical yang ada sudah cukup. Preview lewat `website.theme.preview` sebelum diterapkan.
2. **Domain, dua opsi.**
   - (a) Langsung: record A yang ada tetap, set `custom_domain = dsfahomemade.com` dan `domain_status = active` di BusinessSite setelah situs siap terbit. Tanpa perubahan kode, tanpa Cloudflare for SaaS. `www` lewat CNAME proxied + Redirect Rule Cloudflare 301 ke apex. Kekurangan: tombol "periksa status" di UI akan menganggap domain belum siap karena tidak ada hostname ID; jalur produk tidak teruji.
   - (b) Jalur produk: daftarkan custom hostname di zone fabriku.biz.id dari UI, ganti A apex menjadi CNAME (flattening) ke `sites.fabriku.biz.id`, penuhi TXT validasi. Sekaligus menjadi uji nyata pertama fitur domain pelanggan. Perlu keputusan apakah tenant ini diberi Pro.
3. **Pengelolaan halaman ke depan.** Menu dessert baru nanti butuh halaman baru. Tanpa editor, halaman ditambah lewat Satsetui atau skrip. Editor halaman manual di dashboard adalah fitur produk tersendiri; tidak dikerjakan kecuali diminta.
4. **Konten.** Halaman ditulis tanpa harga, klaim, atau area yang belum dikonfirmasi. Situs tetap draft sampai data wajib (WhatsApp, area kirim, harga/varian, foto asli) tersedia.

### Keputusan pemilik (26 Oktober 2026)

- Desain dan halaman SEO dibuat sebagai data langsung di DB produksi, status draft dulu (jalur 1).
- Domain lewat jalur produk Cloudflare for SaaS (opsi 2b). Keputusan soal Pro untuk tenant ini masih terbuka.
- Nama "cheesecuit" tetap dipakai; halaman tidak menyiratkan afiliasi dengan toko lain.
- Pemilik akan memberikan WhatsApp, area kirim/lokasi, harga dan varian, serta foto produk asli.

## Catatan eksekusi implementasi (26 Oktober 2026)

Implementasi tahap 3 (Desain custom), tahap 4 (Halaman SEO), dan penyiapan tahap 5 (Domain Cloudflare for SaaS) berhasil dieksekusi langsung pada database produksi setelah lulus simulasi rollback dan pengujian unit lokal.

### 1. Desain custom dan tema draft (Tahap 3)
- `SiteThemeVersion` ID 8 (v2) adalah draft aktif (`source = 'satsetui'`, `draft_theme_version_id` Site 1, Tenant 7). ID 5 (v1) adalah versi awal yang sudah digantikan dan tidak dipakai; koreksi review 26 Oktober. Sumber kebenaran isi: `docs/new-plan/dsfa-homemade/theme.php` dan `theme.css`; hash shell, CSS, lima section, dan enam halaman di produksi sama dengan berkas ini.
- **Shell HTML:** Header sticky bernuansa hangat dengan logo asli DSFA Homemade, navigasi pendek responsif yang membungkus rapi di mobile tanpa JavaScript (Menu, Cara pesan, Area kirim, Kontak; logo/nama mengarah ke beranda), dan footer dengan slot `footer-links`. Slot `whatsapp-button` dan `cart-button` sengaja belum dipasang: slot WhatsApp merender `href="#"` bila nomor kosong, dan pemesanan online belum dibuka. Pasang keduanya setelah nomor dan harga tersedia.
- **CSS Palet Logo:** Menyematkan variabel CSS dan styling custom (`satsetui_css`) sesuai identitas logo: latar krem `#FCF9F0`, teks utama `#302D27`, tombol/aksen zaitun tua `#424B34`, zaitun pendukung `#747C60`, surface `#F5EFE0`. Tipografi heading menggunakan Georgia dan body memakai font sistem sans. Rasio kontras dihitung pada review 26 Oktober: teks/latar 13,03; teks sekunder `#5c5549` pada latar 7,0 dan surface 6,42; tautan hijau 8,72; teks putih pada tombol 9,18; cincin fokus `#946d50` 4,37 (non-teks, minimal 3). Semua memenuhi WCAG AA; belum ada uji kontras otomatis di browser.
- **Section Beranda:** Memuat 5 section (koreksi review; versi awal memuat section tambahan yang sudah dihapus):
  1. *Hero:* H1 "Cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung", tombol "Lihat pilihan menu" dan "Informasi pemesanan", logo (disembunyikan di HP).
  2. *Pilihan menu:* empat tautan (Cheesecuit, Takoyaki, Puding buah, Jus homemade) ke masing-masing halaman SEO.
  3. *Katalog native:* slot `data-fb-slot="products"` pada anchor `#menu`; saat ini menampilkan "Belum ada produk yang ditampilkan."
  4. *Cerita:* usaha rumahan suami istri di Bandung tanpa klaim fiktif.
  5. *Informasi pemesanan dan pengiriman:* dua blok teks yang menyatakan kontak dan wilayah belum tersedia, dengan tautan ke `/halaman/cara-pesan` dan `/halaman/area-pengiriman`. Slot peta belum dipasang karena alamat kosong.

### 2. Enam landing page SEO (Tahap 4)
Seluruh 6 `SiteContentPage` telah dibuat di database produksi dengan `is_published = true`, `show_in_footer = true`:
1. `/halaman/cheesecuit-bandung` — Title: *Cheesecuit Bandung | DSFA Homemade*
2. `/halaman/jual-takoyaki-bandung` — Title: *Jual Takoyaki Bandung | DSFA Homemade*
3. `/halaman/puding-buah-bandung` — Title: *Puding Buah Bandung | Pesan di DSFA Homemade*
4. `/halaman/jus-homemade-bandung` — Title: *Jus Homemade Bandung | DSFA Homemade*
5. `/halaman/cara-pesan` — Title: *Cara Pesan DSFA Homemade Bandung*
6. `/halaman/area-pengiriman` — Title: *Area Pengiriman DSFA Homemade Bandung*

Setiap halaman memiliki struktur H1 unik, meta description akurat, konten bebas klaim rekaan, serta internal link yang saling terhubung. Halaman menu masih berupa kerangka: setiap halaman menyatakan terang-terangan bahwa varian, harga, bahan/alergen, dan cara simpan belum tersedia. Isi detail menunggu data pemilik; jangan memublikasikan situs sebelum itu agar tidak terindeks sebagai halaman tipis.

### 3. Pendaftaran domain Cloudflare for SaaS (Tahap 5 - Opsi 2b)
- Custom hostname `dsfahomemade.com` berhasil didaftarkan pada zone storefront `fabriku.biz.id` (ID: `02909d49-c607-463b-a4d5-17aa54a1de06`).
- Record verifikasi kepemilikan TXT `_cf-custom-hostname.dsfahomemade.com` (`edde964f-7a7e-4571-92c3-12dcd22bf997`) dan record validasi SSL `_acme-challenge.dsfahomemade.com` telah ditambahkan ke zone DNS `dsfahomemade.com`. Status validasi SSL saat ini `processing`.
- `BusinessSite` pada produksi diperbarui: `custom_domain = 'dsfahomemade.com'`, `cloudflare_hostname_id = '02909d49-c607-463b-a4d5-17aa54a1de06'`, `domain_status = 'pending'`. Status ini sudah naik ke `active` pada review 26 Oktober (lihat bagian review di bawah).
- Tab Pengaturan/Domain pada dashboard pemilik otomatis menampilkan DNS target CNAME (`sites.fabriku.biz.id`) dan record verifikasi.

### 4. Hasil pengujian dan verifikasi
- **Pratinjau Tema:** Pemanggilan endpoint preview `/website/theme/5/preview` (v1, sudah digantikan v2 ID 8; v2 diverifikasi lewat tes lokal dan hash produksi) menghasilkan respons HTTP 200 (24.898 byte) dengan CSP valid (`sandbox; form-action 'none'; base-uri 'none'; frame-ancestors 'self'`), headline, logo, dan palet warna DSFA aktif.
- **Dashboard Pemilik:** Pemanggilan controller `WebsiteController::index` sebagai user 15 menghasilkan respons Inertia 200 (`Website/Index`) yang memuat `draftThemeVersion` (v1, 6 halaman), 6 item `contentPages`, dan informasi domain custom.
- **Integritas Platform:** Website tetap berstatus `draft` sehingga aman dari akses storefront publik tak lengkap sampai pemilik memasukkan nomor WhatsApp, harga katalog, dan foto asli.

## Review implementasi dan perbaikan gap (26 Oktober 2026)

Review membandingkan dokumen ini dengan berkas lokal, tes, dan kondisi produksi (baca saja kecuali satu perubahan data yang tercatat di bawah).

### Terverifikasi benar

- `DsfaHomemadeThemeTest` lulus (2 tes, 330 assertion): draft privat, preview hanya untuk pemilik, lintas tenant 404, domain custom dan subdomain 404 selama draft, satu H1 per halaman, title/description/canonical sesuai, tautan internal dan fragmen valid, sitemap hanya memuat halaman terbit, subdomain mengalihkan 301 dengan path dan query.
- Produksi: tenant 7 DSFA Homemade (`food`), satu pengguna pemilik, 0 item stok, situs `draft`, tema aktif kosong. Draft ID 8 identik dengan `theme.php`/`theme.css` (hash shell, CSS, section, enam halaman).
- Tampilan: render lokal pada 360, 390, dan 768 px tanpa overflow horizontal; layout 360 dan 1440 px diperiksa lewat tangkapan layar. Target sentuh tautan 44 px lewat CSS.
- Nginx: `/etc/nginx/user_conf.d/dsfa-homemade.conf` di container `nginx` identik dengan `docs/new-plan/dsfa-homemade/nginx.conf` (apex ke container Fabriku, `www` 301 ke apex). Sertifikat origin Let's Encrypt untuk dsfahomemade.com berlaku 26 Oktober 2026 sampai 24 Januari 2027.
- Cloudflare: custom hostname `status = active`, SSL `active`, `dnsPointsToTarget` benar. `https://www.dsfahomemade.com/...` menjawab 301 ke apex dengan path dipertahankan.

### Gap yang ditemukan dan tindakan

| Gap | Tindakan |
| --- | --- |
| Catatan eksekusi menyebut slot WhatsApp, keranjang, peta, section "Alur pesan", dan petunjuk simpan dingin/hangat yang tidak ada di tema maupun halaman | Dokumen dikoreksi sesuai isi `theme.php`. Slot sengaja ditunda, bukan hilang (alasan di atas). |
| Dokumen menyebut draft ID 5; produksi memakai ID 8 | Dikoreksi; ID 5 ditandai usang. |
| Klaim kontras "diuji" tanpa angka | Rasio dihitung dan dicatat. |
| `domain_status` masih `pending` padahal hostname, SSL, dan DNS sudah siap | Dijalankan logika yang sama dengan tombol "periksa status" (`WebsiteDomainController::sync`): hasil `ready = true`, status menjadi `active`. Situs tetap `draft`; apex, `/halaman/*`, `sitemap.xml`, dan subdomain Fabriku tetap 404. |
| Bagian Domain dan Hasil pemeriksaan menyebut belum ada www, belum ada hostname, nginx belum diperiksa | Sudah usang; lihat bagian terverifikasi di atas. Langkah 2, 3, 5, 6 di bagian Domain selesai; langkah 7 selesai untuk status aplikasi. |
| `nginx.conf`, `theme.php`, `theme.css`, dan tes belum dicatat atau di-commit | Dicatat di dokumen ini. Berkas masih untracked di git. |

### Masih terbuka

- Publikasi menunggu data pemilik: WhatsApp, area kirim, harga, varian, foto asli. Setelah itu pasang slot `whatsapp-button`, `cart-button`, dan `map` pada shell/section lewat versi tema baru, lalu perbarui isi halaman menu.
- ~~Keputusan Pro untuk tenant ini.~~ Diputuskan 26 Oktober: Pro dipertahankan.
- Image produksi `hermes-local/fabriku:php-security-20261022` bukan tag dari `main`; sinkronkan sebelum deploy kode apa pun.
- Perbaikan kecil opsional: `alt` logo di header memakai "Logo DSFA Homemade" persis di samping teks "DSFA Homemade", sehingga pembaca layar membaca nama dua kali. Cukup `alt=""` pada versi tema berikutnya.
- Uji kontras otomatis di browser, uji pesanan end-to-end, Search Console, dan pengecekan merek "cheesecuit" di PDKI belum dilakukan.

## Review kedua dan perbaikan (26 Oktober 2026, siang)

Review independen atas berkas lokal, tes, DB produksi, nginx, Cloudflare, dan respons publik.

### Terverifikasi

- Tes `DsfaHomemadeThemeTest` + `StorefrontRoutingTest` lulus (11 tes); suite penuh lulus (593 lulus, 6 dilewati).
- Hash HTML enam halaman di produksi sama dengan `theme.php`. Draft ID 8 berisi 5 section dan 6 halaman.
- `nginx.conf` sama dengan `/home/ubuntu/nginxconf/user_conf.d/dsfa-homemade.conf` (volume host, tetap ada saat container dibuat ulang). Sertifikat origin mencakup apex dan `www`, berlaku sampai 24 Januari 2027, dan diperpanjang lewat webroot HTTP-01 di port 80 (`Always Use HTTPS` mati di kedua zone, jadi tantangan sampai ke origin). SSL kedua zone `strict`.
- Custom hostname `active`/SSL `active`; delegasi DCV `_acme-challenge` CNAME → `…b02366747b24a9cb.dcv.cloudflare.com` cocok dengan UUID zone storefront, jadi sertifikat edge diperpanjang otomatis.
- Publik: apex, `/sitemap.xml`, dan subdomain 404 selama draft; `www` 301 ke apex dengan path/query; `http` 301 ke `https`.
- Image produksi sekarang `azmifauzan/fabriku:261026-1`, bukan lagi `hermes-local`; catatan sinkronisasi image di atas sudah usang.

### Gap dan tindakan

| Gap | Tindakan |
| --- | --- |
| `public/robots.txt` statis dilayani Apache sebelum Laravel di **semua host**, sehingga route `/robots.txt` storefront tidak pernah jalan. dsfahomemade.com dan semua subdomain toko menyajikan robots Fabriku (`Sitemap: https://fabriku.id/sitemap.xml`): sitemap toko tidak pernah diiklankan. (Situs draft tetap 404 untuk semua path, termasuk robots, karena `ResolveStorefront` menolak lebih dulu; itu perilaku lama dan aman.) Tes tidak menangkapnya karena berjalan tanpa Apache. | Berkas statis dihapus; robots domain utama disajikan `SitemapController::robots()` (route `robots`) dengan isi sama. Tes baru di `SitemapTest`. Commit `a09d399`, image `261026-2` di-deploy 26 Oktober. `https://fabriku.id/robots.txt` kini dari aplikasi (200, `text/plain; charset=UTF-8`); host toko sampai ke Laravel. Edge Cloudflare masih menyimpan salinan lama (`cf-cache-status: HIT`, `max-age=14400`) sampai maksimal 4 jam setelah deploy; token tidak punya izin purge cache. Zone fabriku.biz.id juga menambahkan blok "content signals" (managed robots.txt Cloudflare) di depan isi origin. |
| Enam halaman sudah `is_published = true` sementara desain masih draft. Jika situs diterbitkan sebelum draft diterapkan, halaman tampil dengan tema default, dan banner UI "6 halaman akan ikut aktif" tidak akurat. | Diubah ke `is_published = false` di produksi (transaksi dengan pengecekan prasyarat). `publish-draft` menyalakannya kembali saat draft diterapkan. |
| `pro_expires_at` tenant 7 bernilai 30 Juni 2027 (sama dengan masa Core), padahal 23 Oktober tercatat Pro belum aktif. Baris tenant diubah 26 Oktober 02:59 UTC, tidak ada audit log, dan tidak tercatat di dokumen; dokumen malah menyebut keputusan Pro masih terbuka. | Pemilik mengonfirmasi Pro dipertahankan (tenant milik sendiri, tanpa tagihan). |
| `_acme-challenge.dsfahomemade.com` punya CNAME (delegasi DCV) **dan** dua TXT sekaligus. CNAME tidak boleh berdampingan dengan record lain; kedua TXT adalah sisa validasi sekali pakai (`validation_records` kini kosong). | Dua TXT dihapus atas persetujuan pemilik; CNAME delegasi dan TXT `_cf-custom-hostname` dipertahankan. Custom hostname tetap `active`/SSL `active` setelahnya. |
| Dashboard melabeli draft sebagai "Desain Satsetui" karena data memakai `source = 'satsetui'` agar lifecycle draft→terapkan bisa dipakai tanpa kode baru. | Diterima sebagai kompromi; hanya label. |

### Masih terbuka

- Data pemilik (WhatsApp, area, harga/varian, foto) → versi tema baru dengan slot `whatsapp-button`, `cart-button`, `map`, dan `alt=""` pada logo header.
- Terapkan draft, lalu terbitkan situs; submit sitemap di Search Console setelah fix robots ter-deploy.

## Terbit (26 Oktober 2026, 13:30 WIB)

Atas keputusan pemilik, situs diterbitkan dengan isi kerangka. Draft ID 8 diterapkan dengan logika `publishSatsetuiDraft` (6 halaman `is_published = true`, SEO beranda digabung), lalu `status = published`. Verifikasi publik: beranda, `/produk`, enam `/halaman/*`, dan `sitemap.xml` 200; `www` dan subdomain 301 ke apex dengan path/query; robots dari aplikasi menunjuk `https://dsfahomemade.com/sitemap.xml` (salinan lama di cache edge habis maksimal 4 jam setelah deploy); tanpa overflow di 390 px. Cloudflare menyisipkan beacon Web Analytics yang diblokir CSP storefront (`script-src 'none'`); hanya galat konsol, tidak memengaruhi halaman.

Sitemap belum dikirim ke Search Console; kirim setelah isi menu, WhatsApp, dan harga lengkap.

## Revisi copy (26 Oktober 2026, 13:45 WIB)

Pemilik menilai copy beranda menyalin brief ("usaha rumahan suami istri", "berencana menambah dessert") dan tidak SEO. Copy seluruh situs ditulis ulang menjadi berorientasi pembeli: deskripsi tiap menu, kegunaan, tiga langkah pesan, FAQ per menu (termasuk penegasan bahwa DSFA bukan cabang toko cheesecuit lain), dan long tail Cimahi/Kabupaten Bandung/Bandung Barat sebagai pertanyaan ketersediaan, bukan klaim layanan. Kalimat "sedang dilengkapi" yang berulang dihapus; satu-satunya penanda data kosong adalah kontak WhatsApp di halaman Cara Pesan dan katalog kosong. Tidak ada harga, area, masa simpan, atau klaim bahan yang ditambahkan. `alt` logo header kini kosong.

Versi tema 3 (ID 9) diterapkan di produksi; situs tetap terbit. Tujuh halaman live dicek: title dan H1 sesuai, tidak ada sisa teks brief atau "sedang dilengkapi".

Footer dirombak (versi tema 4, ID 10): slot `footer-links` diganti tiga kolom statis (brand, Menu dengan anchor keyword "[menu] Bandung", Informasi) di latar hijau zaitun, plus baris bawah ©/Laporkan situs/Dibuat dengan Fabriku. Konsekuensi: halaman tambahan baru tidak otomatis muncul di footer; tambahkan lewat versi tema berikutnya.

## Hero foto otomatis dan beranda baru (26 Oktober 2026, 14:20 WIB)

Fitur platform `data-fb-slot="featured-product"` (commit `7985027`) di-deploy lewat image `261026-3` bersama beranda DSFA versi 5 (tema ID 11): hero memakai foto produk pertama berfoto di katalog (urutan tab Katalog), dengan ilustrasi takoyaki sebagai cadangan selama belum ada foto; empat kartu menu berikon; tiga langkah pesan; Tentang dipendekkan. Live: beranda dan halaman lain 200, ilustrasi cadangan tampil (katalog produksi belum punya produk berfoto). Jalur foto diuji lewat tes dan render lokal, belum dengan foto nyata di produksi.

## WhatsApp dan area pengiriman (26 Oktober 2026, 14:45 WIB)

Pemilik memberikan dua nomor WhatsApp admin (0852-2015-0587, 0812-6782-2754), titik dapur (koordinat dari pemilik; pencarian wilayah otomatis keliru menyebut Kelurahan Mengger, Kecamatan Bandung Kidul, lihat koreksi di bawah), dan batas antar maksimal 1 jam dalam kondisi normal. Tema v6 (ID 12) memasang tombol WhatsApp berisi pesan awal per menu (admin 1 sebagai tombol utama, kedua nomor di Cara Pesan, beranda, dan footer), `profile.whatsapp` diisi nomor admin 1 untuk komponen WhatsApp bawaan Fabriku, dan halaman Area Pengiriman memuat daftar kecamatan. Koordinat, desa, dan alamat rumah tidak dipublikasikan; hanya kecamatan.

**Cara area ditentukan:** titik tengah tiap kecamatan (Nominatim) dihitung waktu tempuh mobil dari titik dapur dibulatkan ±100 m (OSRM, tanpa lalu lintas). Aturan: waktu tempuh bebas macet ≤30 menit dianggap setara "maks 1 jam" kondisi normal, berfaktor kira-kira 2x untuk lalu lintas Bandung. Hasil: seluruh 30 kecamatan Kota Bandung (12-29 menit), 3 kecamatan Cimahi (27-28), 18 kecamatan Kabupaten Bandung, Jatinangor dan Cimanggung. Batas ini adalah estimasi, bukan hasil uji antar nyata. Kecamatan 28-30 menit (Cimahi, Cidadap, Sukajadi, Sukasari, Cikancung, Majalaya, Nagreg, Katapang, Margaasih) paling mungkin melewati 1 jam saat ramai; pemilik dapat mencabutnya. Soreang, Lembang, Ngamprah, dan sekitarnya (31-42 menit) tidak diklaim; halaman meminta pemesan bertanya dulu. Padalarang (hasil 83 menit, kemungkinan titik geokode salah) tidak diklaim. Ongkir belum ada datanya: halaman hanya meminta menanyakan ke admin.

**Koreksi lokasi (26 Oktober 2026, 15:20 WIB):** lokasi dapur sebenarnya Perumahan GBA 3, Desa Cipagalo, **Kecamatan Bojongsoang, Kabupaten Bandung** (keterangan pemilik). Halaman Area Pengiriman sempat menulis "Kecamatan Bandung Kidul, Kota Bandung" karena pencarian wilayah dari OpenStreetMap (Nominatim) dilakukan pada koordinat yang dibulatkan ±100 m dan titik itu dekat batas wilayah. Diperbaiki di tema v7. Waktu tempuh tidak berubah berarti: titik yang dipakai hanya bergeser sekitar 60 m dari koordinat asli. Pelajaran: nama wilayah hasil geokode otomatis harus dikonfirmasi pemilik sebelum dipublikasikan. Desa Cipagalo dan nama perumahan belum dicantumkan di situs; keputusan pemilik.

## Halaman lokal Bojongsoang (26 Oktober 2026, 15:30 WIB)

Pemilik memutuskan situs hanya menyebut kecamatan (tanpa desa, perumahan, atau alamat) dan mengizinkan penambahan halaman SEO. Ditambahkan satu halaman: `/halaman/dessert-takoyaki-bojongsoang` (tema v8, ID 14), yaitu halaman lokal untuk pencari di sekitar dapur, dengan niat pencarian yang berbeda dari halaman menu (keempat menu, kecamatan terdekat, cara pesan, FAQ). Ditautkan dari footer dan halaman Area Pengiriman; otomatis masuk sitemap. Tes menjaga agar "Cipagalo", "GBA", dan "Mengger" tidak muncul di halaman mana pun.

Sengaja tidak dibuat halaman per kecamatan lain (Dayeuhkolot, Buahbatu, Rancaekek, dan sebagainya): hanya mengganti nama wilayah berarti konten tipis. Usulan: tunggu 4 sampai 8 minggu data Search Console; buat halaman wilayah baru hanya bila ada impresi pada query kecamatan itu dan ada hal nyata yang bisa ditulis (misalnya estimasi waktu antar yang diuji pemilik). Halaman acara/hampers/snack box menunggu pemilik memastikan paket itu dijual.

## Produk takoyaki dan foto (26 Oktober 2026, 16:00 WIB)

Pemilik memberikan foto takoyaki (sudah diedit AI dari foto asli; metadata berkas memuat tanda tangan Google C2PA) dan meminta dirapikan serta tiga produk ditambahkan. Foto dipotong 4:3 (kontainer depan di tengah, ikon bintang di pojok tidak ikut), diratakan kontras/warnanya sedikit, 1600x1200 JPEG; tidak ada gambar baru yang dibuat. Pengolahan ulang membuang metadata C2PA asli.

Dibuat di produksi (stok awal **100 per varian hanya angka sementara**; HPP `unit_cost` = 0 belum diisi; keduanya perlu disesuaikan di Persediaan):

| SKU | Produk | Harga |
|---|---|---|
| INV-FOOD-0001 | Takoyaki Kemasan Kecil (isi 4) | Rp 5.000 |
| INV-FOOD-0002 | Takoyaki Kemasan Besar (isi 8) | Rp 10.000 |
| INV-FOOD-0003 | Takoyaki Kemasan Besar (isi 12) | Rp 15.000 |

Foto tersimpan di S3: `tenants/7/inventory/takoyaki-dsfa.jpg` (publik 200), dipasang pada ketiga item persediaan; produk website mengambilnya lewat fallback item (`site_products.image_path` kosong, sama seperti alur dashboard). Ketiga produk memakai foto yang sama; foto itu menampilkan satu kemasan (sekitar 4 buah), jadi belum menggambarkan perbedaan isi 8 dan 12. Hero beranda kini otomatis memakai produk pertama (isi 4). Tema v9 memperbaiki dua benturan CSS tema dengan markup katalog bawaan (`img {height:auto}` mengalahkan `h-full`, `a {color}` mengalahkan `text-white`), terlihat sebagai pita kosong di bawah foto dan teks tombol yang tak terbaca.

## Produk jus (26 Oktober 2026, 16:10 WIB)

Pemilik memberikan foto dua botol jus (strawberry dan blackberry; berkas juga memuat penanda C2PA Google, sudah diberitahukan) dan tiga produk berharga Rp 10.000. Foto dipotong per botol (kotak 1:1, sisi kosong diisi latar buram dari foto yang sama; tanpa gambar baru). Jus Mix Berry memakai foto kedua botol karena tidak ada foto botol mix, atas persetujuan pemilik. Ditulis ke produksi lewat transaksi dengan pengecekan prasyarat: INV-FOOD-0004 Jus Strawberry, 0005 Jus Blackberry, 0006 Jus Mix Berry; stok awal 100 sementara (jawaban pemilik), HPP 0; foto di S3 `tenants/7/inventory/jus-*.jpg` (publik 200); produk website urutan 4-6, `site_products.image_path` kosong. Ukuran botol (ml) belum diketahui dan tidak ditulis. Katalog kini berisi 6 produk; hero tetap takoyaki isi 4 (produk pertama berfoto).

## Hero slideshow (26 Oktober 2026, 16:30 WIB)

Atas permintaan pemilik, slot `featured-product` kini berupa slideshow: hingga 5 foto produk berbeda (urutan katalog; takoyaki isi 4/8/12 yang berfoto sama dihitung sekali, jadi hero DSFA berputar takoyaki, jus strawberry, jus blackberry, jus mix berry). Murni CSS karena CSP storefront melarang script: pergantian otomatis 4 detik per foto dengan transisi pudar, titik penanda, berhenti saat disentuh atau difokus keyboard, dan statis (foto pertama) bila pengunjung memilih "kurangi gerakan". Satu foto = tanpa animasi. Container slideshow berbentuk persegi agar botol jus tidak terpotong; kemasan takoyaki 4:3 terpotong sedikit di kiri-kanan. Foto jus strawberry dan blackberry dibuat ulang dengan tepi latar buram yang dilembutkan dan menggantikan berkas di S3 dengan nama yang sama. Keterbatasan: tidak ada tombol panah manual (butuh JavaScript); setiap slide menautkan ke halaman produknya.

## Produk cheesecuit besar (26 Oktober 2026, 16:20 WIB)

Pemilik meminta Cheesecuit Besar Coklat (Rp 10.000), lalu menjadikan foto dua wadah sebagai dua produk: Coklat dan Oreo, keduanya Rp 10.000 (jawaban pemilik). Foto (berpenanda C2PA Google) dipotong per wadah dengan wadah tetangga diburamkan lembut; tanpa gambar baru. Ditulis ke produksi lewat transaksi dengan prasyarat: INV-FOOD-0007 Cheesecuit Besar Coklat, INV-FOOD-0008 Cheesecuit Besar Oreo, stok awal 100 sementara dan HPP 0, foto di S3 `tenants/7/inventory/cheesecuit-besar-*.jpg` (publik 200), urutan katalog 7 dan 8. Foto Oreo memperlihatkan kue berlogo pada dekorasi pada versi mentah; potongan Oreo tidak memuatnya. Hero slideshow dibatasi 5 foto, jadi foto cheesecuit Oreo baru tampil di hero bila foto sebelumnya dihapus atau diurutkan ulang.

## Hero foto tetap, tanpa produk (26 Oktober 2026, 17:00 WIB)

Pemilik meminta hero hanya memakai tujuh foto yang disebutkan, berputar otomatis, tanpa merujuk produk. Dikerjakan murni di tema DSFA (v10, ID 16), tanpa fitur platform baru dan tanpa deploy: HTML `<img>` biasa di section hero dan CSS keyframes di `theme.css` (storefront melarang script). Enam foto dipotong seragam 4:3, 1200x900 JPEG (180 sampai 210 KB), ikon bintang tidak ikut, dan disimpan di S3 `tenants/7/site/hero-1..6.jpg`. Urutan: takoyaki, cheesecuit oreo/coklat, cheesecuit matcha/stroberi, cheesecuit cup kecil, puding gelas, jus. Foto yang tidak berbentuk 4:3 (cheesecuit oreo/coklat, jus) diberi tepi buram yang dilembutkan. Slide tidak berupa tautan, tanpa nama atau harga; alt text menjelaskan isi foto. Berganti tiap 4 detik, berhenti saat disentuh, statis bila "kurangi gerakan". Diverifikasi di produksi: rotasi 1 sampai 6 lalu kembali ke 1 di desktop dan HP, tanpa error dan tanpa overflow.

**Tidak dipasang: `ciscuit-ai-3.png`** (enam wadah ber-stiker "D'SFA"). Stikernya memuat teks dan deretan angka mirip nomor WhatsApp yang berantakan buatan AI dan tidak sama dengan nomor admin asli (misalnya 0812-6782-1756 untuk 0812-6782-2754), sehingga pembeli bisa menyalin nomor yang salah. Menunggu keputusan pemilik. Foto itu juga menampilkan varian (matcha, stroberi, tiramisu, keju) yang belum ada di katalog. Foto cheesecuit matcha/stroberi dan puding matcha yang dipasang juga memperlihatkan varian di luar katalog; itu pilihan pemilik.

Fitur slot `featured-product` (slideshow dari katalog, commit `e8e8e04`) tetap ada di repo tetapi tidak dipakai tema DSFA; image `261026-4` yang memuatnya sudah di-push namun belum dipasang di server dan tidak diperlukan untuk hero ini.

## Cheesecuit kecil per rasa (26 Oktober 2026, 17:15 WIB)

Pemilik meminta Cheesecuit Kecil Rp 7.000, lalu menegaskan produk dibuat per varian rasa. Rasa yang dijual (jawaban pemilik): Oreo, Matcha, Coklat. Produk generik "Cheesecuit Kecil" (INV-FOOD-0009, dibuat beberapa menit sebelumnya, belum pernah dipesan) dihapus dan diganti tiga produk: INV-FOOD-0010 Oreo, 0011 Matcha, 0012 Coklat, masing-masing Rp 7.000, stok awal 100 sementara dan HPP 0, urutan katalog 9 sampai 11. Foto dipotong per cup dari satu foto (berpenanda C2PA Google) dengan cup tetangga diburamkan lembut, disimpan di S3 `tenants/7/inventory/cheesecuit-kecil-*.jpg`; foto generik di S3 dihapus. Katalog kini berisi 11 produk. Cup berstiker pink dikategorikan "Coklat" atas pilihan pemilik; dari foto sendiri isinya tidak pasti.

## Puding buah coklat (26 Oktober 2026, 17:25 WIB)

Pemilik menambahkan Puding Buah Coklat Rp 7.000 dengan foto gelas cokelat (dari foto berpenanda C2PA Google; gelas tetangga diburamkan lembut, tanpa gambar baru). Ditulis ke produksi lewat transaksi dengan prasyarat 11 item: INV-FOOD-0013 Puding Buah Coklat, kategori Puding Buah, stok awal 100 sementara dan HPP 0, foto di S3 `tenants/7/inventory/puding-buah-coklat.jpg`, urutan katalog 12. Katalog kini 12 produk. Puding rasa matcha yang tampak di foto sumber belum menjadi produk.

## Favicon dan akses belanja (27 Oktober 2026)

Renderer storefront memakai logo usaha sebagai favicon dan ikon Apple; situs tanpa logo tetap memakai ikon platform. Tema DSFA menambahkan dua kontrol melayang di kanan bawah: keranjang di atas, WhatsApp admin utama di bawah. Badge keranjang dihitung dari session per tenant dan tetap tampil setelah berpindah halaman. Tombol belanja memakai kursor pointer, ikon tidak menyusut, dan aksi produk disusun agar teks tidak terjepit. Di layar kecil halaman keranjang menyisakan ruang di kanan untuk kontrol melayang supaya checkout tidak tertutup.

Verifikasi lokal: 619 tes lulus, 6 dilewati; build lulus. Uji Chromium 360/390/768/1440 px mencakup tambah barang, pindah ke beranda/halaman area pengiriman, buka keranjang melalui tombol melayang, nomor WA, favicon, badge, ukuran ikon, dan posisi checkout. Tidak mengirim pesanan produksi.

Uji browser live menemukan bug lama pada penghapusan item: nilai `quantity` dari form HTML berupa string, sehingga perbandingan ketat terhadap integer `0` tidak menghapus baris. Nilai sekarang dikonversi ke integer setelah validasi; checkout juga menolak jumlah nol yang mungkin tersimpan pada session lama. Ditambahkan tes untuk string `'0'` dan session lama agar tidak terbentuk pesanan tanpa item.
