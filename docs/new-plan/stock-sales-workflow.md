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
