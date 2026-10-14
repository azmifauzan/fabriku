# Plan: Revamp Website Usaha

Tanggal: 2026-10-14
Status: selesai diimplementasikan

## Masalah

1. **Terkunci di desain Satsetui.** Setelah desain Satsetui dipakai, form desain Fabriku hilang dan sistem menolak menyimpan desain Fabriku. Satu-satunya jalan kembali adalah memulihkan versi Fabriku lama. User yang sejak awal memakai Satsetui tidak punya versi itu, jadi tidak pernah bisa kembali.
2. **Katalog tanpa daftar.** Produk yang sudah dipilih untuk tampil di website tidak terlihat. Tidak bisa diedit, disembunyikan, dihapus, atau diurutkan.
3. **UI/UX kurang rapi.** Status tampil dalam bahasa Inggris mentah, menutup prospek memakai popup browser, tab hilang saat halaman di-refresh, tab Pengaturan menumpuk empat bagian sekaligus.

## Keputusan yang sudah disepakati

- Halaman tambahan buatan Satsetui **tetap terbit** saat pindah ke desain Fabriku.
- Mengganti mode usaha (produk/jasa/gabungan) **menampilkan konfirmasi**, lalu mereset desain aktif.
- **Urutan layanan ikut dikerjakan.**

## Fase 1 — Desain bisa berpindah bebas (prioritas)

Hasil yang dituju:
- Desain Fabriku selalu bisa dipilih, apa pun desain yang sedang aktif.
- Desain Fabriku dan Satsetui tampil sebagai dua pilihan setara.
- Kartu "Desain aktif" menunjukkan sumber, versi, tanggal, dan pembuatnya.
- Form desain Fabriku terisi dari desain Fabriku terakhir milik user, bukan nilai default.
- Draf Satsetui bisa dilihat, diterapkan, atau dibuang. Versi yang dibuang tetap ada di riwayat.
- Riwayat versi memberi label sumber dan pembuat. Satu aksi "Gunakan versi ini" untuk semua versi.
- Setiap penggantian desain aktif meminta konfirmasi lebih dulu, karena website yang sudah terbit langsung berubah.
- Bug ikut diperbaiki: desain Fabriku yang disimpan saat Satsetui aktif tidak boleh membawa isi template Satsetui.

Selesai bila: user yang hanya pernah memakai Satsetui bisa beralih ke desain Fabriku dan kembali lagi ke Satsetui tanpa kehilangan versi apa pun.

## Fase 2 — Katalog produk dan layanan

Hasil yang dituju:
- Daftar produk yang tampil di website: foto, judul, nama barang, SKU, harga, stok, dan status.
- Status produk: Tampil, Disembunyikan, Stok habis, atau Barang tidak tersedia. Pemilik usaha jadi tahu kenapa suatu produk tidak muncul di website.
- Aksi per produk: edit, sembunyikan/tampilkan, naik/turun urutan, dan hapus dari website (dengan konfirmasi; stok tidak berubah).
- Tambah produk lewat pemilih bergambar yang bisa dicari. Produk yang sudah dipilih tidak muncul lagi di pemilih.
- Saat diedit, barang yang terhubung tidak bisa diganti. Untuk mengganti, hapus lalu tambah baru.
- Daftar layanan: atur tampil/tidak, alamat, dan urutan. Urutan ini dipakai juga di website publik.
- Urutan diatur dengan tombol naik/turun, bukan seret, supaya bisa dipakai lewat keyboard dan layar sentuh.

Selesai bila: pemilik usaha bisa melihat dan mengatur semua produk dan layanan yang tampil dari satu tab, dan website publik mengikuti urutan itu.

## Fase 3 — Revamp tab lainnya

Hasil yang dituju:
- **Header situs selalu terlihat:** status dalam bahasa Indonesia (Draf, Terbit, Dijeda, Ditangguhkan), tombol Terbitkan/Jeda, dan Lihat website.
- **Navigasi tab:** tetap di tab yang sama setelah refresh atau saat berbagi link, ada badge jumlah prospek baru, dan bisa dipakai lewat keyboard.
- **Permintaan:** status dalam bahasa Indonesia berwarna, filter status, dan modal alasan saat menutup prospek (menggantikan popup browser).
- **Identitas:** pratinjau alamat website, dan konfirmasi saat mengganti mode usaha.
- **Pengaturan:** sub-menu Domain, SEO, Halaman tambahan, dan Tim. Satu bagian tampil dalam satu waktu.
- **Wizard setup:** ada tombol Kembali. Langkah Desain memakai dua pilihan yang sama dengan tab Desain, plus opsi "pakai desain bawaan".
- **Konsisten di semua tab:** bahasa Indonesia, dark mode, format angka, tombol yang nyaman disentuh di HP, konfirmasi memakai modal aplikasi.
- **Kode halaman dipecah per tab** supaya mudah dirawat. Perilaku tidak berubah.

Selesai bila: kelima tab dan wizard memenuhi poin di atas, dicek di light/dark mode, di layar HP dan desktop, dan dengan keyboard.

## Urutan dan rilis

Fase 1 → Fase 2 → Fase 3. Setiap fase punya test otomatis sendiri, dicek di browser, dan bisa di-deploy terpisah. Fase 1 didahulukan karena saat ini ada user yang terkunci.

## Di luar cakupan

- Alur pembuatan desain di Satsetui.
- Tampilan website publik dan checkout (kecuali urutan layanan).
- Paginasi permintaan. Ditunda sampai ada usaha dengan lebih dari 50 permintaan.

## Risiko

- **Ganti desain langsung berdampak ke website yang sudah terbit.** Mitigasi: konfirmasi, dan riwayat versi selalu bisa dipulihkan.
- **Memecah kode halaman bisa merusak perilaku yang sudah jalan.** Mitigasi: dikerjakan terakhir (Fase 3), setelah Fase 1–2 terkunci oleh test.
- **Perubahan database untuk urutan layanan.** Hanya menambah kolom baru tanpa mengubah data yang ada. Aman.
