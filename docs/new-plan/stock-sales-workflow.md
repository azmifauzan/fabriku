# Mode stok dan penjualan saja

Pengaturan ini tersedia di **Pengaturan → Alur usaha**, hanya untuk admin usaha. Aktifkan **Stok dan penjualan saja**, lalu simpan.

Kategori usaha tetap sama. Mode ini memakai alur tambah stok langsung di Persediaan dan penjualan stok tersedia, tanpa produksi, bahan baku, resep/pola, kontraktor, pembelian, layanan, dan pencatatan modal/HPP. Harga jual, reservasi stok, batas stok tersedia, serta aturan kategori tentang batch/kedaluwarsa tetap berlaku. Website dan katalog yang ada tidak diubah.

## Data dan kompatibilitas

- Migrasi `2026_10_27_000001_add_stock_sales_only_to_tenants_table` hanya menambah boolean `tenants.stock_sales_only`, default `false`. Tidak ada aktivasi massal atau perubahan kategori.
- Selama pengaturan nonaktif, konfigurasi dan workflow kategori mengikuti perilaku semula.
- Mengaktifkan atau mematikan pengaturan tidak menghapus atau menghitung ulang stok, modal, transaksi, maupun riwayat produksi lama.
- Edit stok dalam mode ini mengabaikan input modal, sumber produksi, dan target produksi; nilai lama tetap tersimpan. Produk baru memakai modal internal 0 dan tidak dimasukkan dalam perhitungan nilai persediaan.
- Dashboard, laporan persediaan, PDF, dan Excel tidak menampilkan nilai modal selama mode aktif. Fitur yang dinonaktifkan juga ditolak jika URL diakses langsung.
- Setelah mode dimatikan, nilai modal lama kembali digunakan. Modal produk yang dibuat saat mode aktif tetap 0 sampai diisi pemilik.

## Rilis

Deploy image dan jalankan migrasi sesuai `docs/deployment.md`. Sesudahnya, admin DSFA dapat mengaktifkan mode dari Pengaturan; kategori tetap `food`. Tidak ada perubahan otomatis ke akun DSFA atau tenant lain.

Rollback aplikasi dapat mempertahankan kolom tambahan ini; kode lama mengabaikannya. Jangan menghapus data operasional atau menjalankan reset/seeder tenant untuk mengubah workflow.

## Verifikasi lokal

Suite lengkap: 616 tes lulus, 6 dilewati. Tes khusus mencakup tujuh kategori default, isolasi tenant, akses admin, pemulihan workflow, data lama tetap utuh, tambah/edit stok tanpa modal, laporan/ekspor, dan penjualan dengan batas stok. Build frontend dan lint file Vue/TypeScript yang berubah lulus.

Uji Chromium lokal juga lulus: simpan/muat ulang pengaturan, checkbox dengan keyboard, kategori tetap Makanan & Kue, navigasi produksi dan input modal tersembunyi, harga jual tetap ada, laporan tanpa valuasi, dan pemulihan form modal. Tampilan 1440 px dan 390 px diperiksa; tidak ada overflow pada HP atau error JavaScript. Produksi belum diubah.


## Review lokal 27 Oktober 2026

Dua celah pada mode aktif diperbaiki: pilihan sumber pada edit stok kini disembunyikan karena riwayat sumber sengaja dipertahankan, dan konversi prospek layanan menjadi pesanan ditolak (tombolnya juga disembunyikan). Keduanya tidak mengubah perilaku tenant yang tidak mengaktifkan mode.

Tes tambahan memeriksa migrasi pada database berisi data, pemulihan nilai modal saat mode dimatikan, konversi prospek setelah mode dimatikan, dan checkout publik pada kedua mode. Suite akhir: 616 lulus, 6 dilewati; build, lint file frontend yang berubah, dan uji Chromium desktop/HP lulus.

Migrasi pengaturan diterapkan hanya ke PostgreSQL lokal (`127.0.0.1`, database `fabriku`). Checksum 57 tabel dengan 665 baris dibandingkan sebelum/sesudah migrasi di dalam transaksi: semua data lama sama, tidak ada tenant yang diaktifkan otomatis. Dua migrasi lama (`2026_10_13_000001_link_site_products_to_inventory_item_id` dan `2026_10_14_134058_add_sort_to_services_table`) sudah tertunda sebelum perubahan ini dan tidak dijalankan dalam review.

Typecheck penuh masih menghasilkan 362 error yang identik dengan snapshot HEAD sebelum perubahan; tidak ada diagnostik baru. Tidak ada deploy atau perubahan data produksi.

## Deploy produksi 27 Oktober 2026

- Kode rilis: commit `6c2bbd0a36ca558dacd3d26ae0b71cb110e7fa5d`, sudah di-push ke `origin/main`.
- Image: `azmifauzan/fabriku:271026-1`, digest `sha256:9d1e3ac093765aa3602a88e5612c8d1e911e390f3b28b627edb407b072a41cc9`. Dibangun dari arsip Git commit tersebut, termasuk bundle SSR.
- Backup database PostgreSQL (format custom) dan Compose lama disimpan dengan izin 0600 di `/home/ubuntu/fabriku/backups/271026-1/`. Image sebelumnya `azmifauzan/fabriku:261026-3` tetap tersedia untuk rollback.
- Migrasi dijalankan sebelum mengganti container, dalam transaksi dengan pembandingan checksum 57 tabel dan 659.361 baris. Seluruh data lama sama; tidak ada aktivasi mode otomatis.
- Container `healthy`; queue worker, scheduler, dan dua proses SSR berjalan. Tidak ada ERROR Laravel atau failed job saat verifikasi.
- Pemeriksaan read-only pada seluruh 21 tenant: semua mode tetap nonaktif, tidak ada perubahan konfigurasi default kategori. Komponen pengaturan DSFA mengembalikan `Settings/Index`, HTTP 200, `stockSalesOnly = false`.
- DSFA tetap kategori `food`, situs terbit, domain aktif, tema v10, 12 produk terlihat, dan dapat menerima pesanan. Seluruh 21 URL sitemap dan 17 gambar publik HTTP 200; tiga redirect 301 mempertahankan path/query; robots menunjuk sitemap DSFA. Beranda dan blog Fabriku HTTP 200 dengan H1, canonical, dan description hasil SSR.

Mode DSFA belum diaktifkan. Admin usaha dapat mengaktifkannya melalui **Pengaturan → Alur usaha**. Tidak ada pesanan percobaan dibuat di produksi. Jika rollback diperlukan, pulihkan Compose backup dan buat ulang hanya service Fabriku; kolom boolean tambahan dapat dipertahankan karena image lama mengabaikannya.
