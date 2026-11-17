# Perbaikan SEO DSFA Homemade, 9 November 2026

## Yang diterapkan

- Deskripsi meta produk berasal dari deskripsi produk, bukan beranda. Katalog dan privasi juga memakai deskripsi tersendiri.
- JSON-LD Organization, Product/Offer, dan BreadcrumbList dirender oleh Blade. Harga dan ketersediaan mengikuti katalog; open order memakai PreOrder. Tidak ada rating, ulasan, masa simpan, berat, atau alamat rumah yang dikarang.
- Produk menaut balik ke halaman terbit yang memasukkannya melalui slot katalog. Halaman tersembunyi dan produk tenant lain tetap tidak dapat diakses.
- GA4 mengirim `whatsapp_click`, `add_to_cart` setelah penambahan berhasil, dan `order_request` hanya setelah draft pesanan baru benar-benar dibuat. Event server diambil sekali dan dibatasi per situs. Klik WhatsApp tidak mengirim nomor admin, isi chat, atau parameter query tautan. Nama pelanggan, alamat, nomor kontak, dan catatan pesanan tidak dikirim.
- Di properti DSFA `560891733`, `whatsapp_click` ditandai key event sekali per sesi tanpa nilai uang default; `order_request` sekali per event tanpa nilai default. `purchase` bawaan tidak dipakai untuk draft atau klik.
- Tiga halaman menu diperkuat: cheesecuit Bandung, puding buah Bandung, dan puding ulang tahun Bandung. Empat deskripsi produk diperbarui: Oreo kecil dan tiga produk puding. Kemasan puding cup 150 ml berasal dari jawaban pemilik; bukan klaim berat isi atau ukuran puding besar.
- Footer semua halaman memuat Instagram `https://www.instagram.com/dsfa.homemade/` dan Threads `https://www.threads.com/@dsfa.homemade`, dengan ikon, teks, label navigasi, `noopener`, dan target sentuh 44 px. Ikon Threads mengikuti Simple Icons (`https://cdn.simpleicons.org/threads`).

## Bukti

- Suite akhir: 659 tes lulus, 6 dilewati, 4.749 assertions. Pint dan `git diff --check` lulus.
- Self-check JavaScript pada HTML hasil Blade: host WhatsApp benar, satu klik menghasilkan satu event, nomor kontak/isi chat/query tautan tidak dikirim.
- Seluruh 24 URL sitemap publik lulus HTTP, canonical, deskripsi, dan JSON-LD. Seluruh 14 produk memiliki Product/Offer dan tautan menu terkait. Nol deskripsi meta duplikat. Keranjang dan lapor tetap `noindex, nofollow`.
- RankMySEO memeriksa produk Oreo: 3 item JSON-LD, 0 error, 0 warning. Tombol pembacaan ulang situs dinonaktifkan; UI menjadwalkan pembacaan berikutnya 2 Desember. Skor audit lama bukan hasil crawl setelah perubahan.
- Browser live: event `whatsapp_click` dan `add_to_cart` teramati; request GA menerima HTTP 204. Produk uji hanya ditambahkan ke session keranjang lalu dihapus, tanpa checkout produksi. Tampilan 390 px dan 1440 px tidak overflow; ikon sosial tampil dan tautannya tepat.
- Google Search Console menerima permintaan indeks ulang untuk `/halaman/puding-ulang-tahun-bandung`, `/halaman/puding-buah-bandung`, dan `/produk/cheesecuit-kecil-oreo`. Ketiganya sudah berstatus indexed sebelum permintaan; antrean crawl ulang bukan jaminan perubahan ranking.
- Profil Google Maps yang diberikan pemilik (`/g/11z_cldz3p`) terverifikasi sebagai DSFA Homemade, kategori Dessert shop, website/menu dsfahomemade.com, telepon 0852-2015-0587. Alamat lengkap profil tidak disalin ke website.

## Rilis dan rollback

Kode SEO dirilis pada image `azmifauzan/fabriku:091126-3`; aksesibilitas SVG footer pada `091126-4`, digest `sha256:a7a3cb7c564fe4168d780eaf40238354d91fd15983e2a4df87a099755cad8e2e`. Build memakai salinan sumber tracked dan view baru, tanpa berkas lokal untracked yang tidak terkait. Tidak ada dependency atau migrasi baru.

Konten diperbarui pada site 1, tenant 7, sesudah simulasi rollback dan pemeriksaan identitas/hash. Versi tema ID 19 menyimpan perubahan halaman; ID 21 menambahkan sosial. Pemeriksaan transaksi memastikan stok, harga, pesanan, serta situs dan produk tenant lain tidak berubah.

Backup di server: `/home/ubuntu/fabriku/backups/091126-3/` (Compose dan konten sebelum SEO) dan `/home/ubuntu/fabriku/backups/091126-4/` (Compose, tema sebelum sosial, serta konfigurasi sebelum penetapan kunci persisten). Untuk rollback tampilan sosial, kembalikan `active_theme_version_id` ke 19. Untuk rollback kode, gunakan tag sebelumnya sambil mempertahankan APP_KEY produksi dan APP_PREVIOUS_KEYS.

Compose server lama belum meneruskan APP_KEY walaupun template repo sudah mendukungnya. Dipulihkan ke kunci image `091126-1` yang dipakai sebelum pekerjaan ini, disimpan privat di `.env` server, dan dua kunci build sementara disimpan sebagai previous keys. Nilai kunci tidak dicatat pada dokumen/log. Cache dibangun ulang setelah entrypoint selesai; kesalahan rename cache sementara akibat perlombaan proses sudah ditangani.

## Batas dan tindak lanjut

Bahan/alergen, masa simpan, berat isi, porsi puding besar, dan jadwal operasional lengkap belum dikonfirmasi pemilik. Tidak ditambahkan klaim tersebut, testimoni, rating, backlink berbayar, atau halaman wilayah massal. Tidak ada pesan WhatsApp maupun permintaan ulasan yang dikirim kepada orang lain.

Bandingkan 28 hari berikutnya dengan baseline GA4 12 Oktober–8 November: 56 pengguna aktif, 64 sesi, 1 sesi organic search. GSC saat pemeriksaan: 33 impresi dan 0 klik. Ukur impresi/klik halaman prioritas dan key event secara terpisah; klik WhatsApp dan permintaan pesanan belum berarti pembayaran.

## Pemeriksaan antislop untuk perubahan ini

- Hard Gate PASS: fakta kemasan berasal dari pemilik; harga dari katalog; noindex dipertahankan; layout 390/1440 px tidak overflow; SVG dekoratif tersembunyi dari pembaca layar dan tautan punya nama.
- Purpose Gate PASS: ikon mengidentifikasi dua akun sosial yang diminta; warna, font, dan footer mengikuti identitas situs yang ada.
- Liveliness PASS: arah lama ENERGY 2 / RHYTHM 2 / MOTION 1 dipertahankan; foto makanan dan palet zaitun-krem tetap menjadi identitas.
- Craftsmanship PASS: link sosial tepat, target 44 px, sanitasi script/handler tema tetap aktif, suite dan build lulus; tidak ada fitur atau aset pemasaran rekaan.
