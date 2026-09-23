# Rencana MVP Fabriku: operasional sampai website usaha

Status: rancangan, 23 September 2026. Ini bukan daftar fitur yang sudah tersedia.

Dokumen terkait: [paket dan migrasi pelanggan](website-usaha-pricing-plan.md), [API partner Satsetui](satsetui-api-plan.md), dan [branding serta SEO](brand-seo-expansion-plan.md).

## Keputusan produk

Fabriku menjadi aplikasi, identitas, tagihan, dan sumber data utama bagi merchant. Produk baru bernama kerja **Website Usaha**: satu situs per tenant dengan mode `produk`, `jasa`, atau `gabungan`. Situs bisa berupa toko dengan permintaan pesanan, landing page jasa dengan permintaan penawaran, atau keduanya. Pilihan mode mengubah konten dan alur pengunjung, bukan jumlah langganan.

Satu akun dan satu tagihan Fabriku mencakup fitur paket yang dipilih. Satsetui dan Repliz adalah penyedia kemampuan di belakang Fabriku; atribusi `Powered by` boleh tampil. Merchant tidak membuat akun atau membeli kredit pada kedua layanan itu. Pemilik akun sosial tetap perlu memberikan izin OAuth melalui platform terkait.

Satu langganan tidak berarti pemakaian tanpa batas atau harga lama otomatis mencakup biaya baru. Rekomendasi paket adalah `Core` (kemampuan Fabriku saat ini) dan `Online` (Core + Website Usaha + Social Kit + penjadwalan terbatas), dengan satu tagihan Fabriku. Batas generasi desain, Social Kit, akun sosial, penyimpanan, dan jadwal harus terlihat jelas. Harga dan kuota final menunggu pengukuran pilot serta izin komersial Repliz; kebijakan pelanggan lama dirinci dalam [rencana harga](website-usaha-pricing-plan.md). Halaman harga tidak boleh menjanjikan fitur yang belum aktif.

Target awal: UMKM Indonesia yang menjual produk buatan/kelolaannya, menyediakan jasa, atau keduanya. Fabriku sudah memiliki katalog layanan dan sales order untuk jasa; website menambah pintu masuk permintaan pelanggan. Ini bukan integrasi marketplace, payment gateway, layanan pengiriman, atau booking otomatis pada MVP.

## Irisan fitur yang harus selesai bersama

| Area | MVP yang dapat dipakai | Belum masuk MVP |
|---|---|---|
| Website usaha | Satu situs per tenant; mode produk/jasa/gabungan, subdomain Fabriku, opsi domain sendiri, preview dan publikasi | Multi-site, multi-bahasa, marketplace sinkron |
| Produk | Katalog, halaman produk, keranjang sederhana, formulir permintaan pesanan | Promosi kompleks, ongkir otomatis, pembayaran online |
| Jasa | Daftar layanan, detail/manfaat, profil/bukti kerja asli, CTA `Minta penawaran`/`Hubungi kami`, formulir prospek | Booking kalender, harga otomatis untuk pekerjaan kustom, pembayaran online |
| Desain | Template dasar atau desain awal Satsetui; edit logo, warna, teks, gambar, urutan bagian, dan SEO dasar di Fabriku | Editor HTML/CSS bebas, kode khusus per merchant, regenerasi tanpa batas |
| Tindak lanjut | Inbox pesanan dan prospek terpisah; staf konfirmasi langsung; pembayaran dicatat setelah transaksi di luar sistem | Chatbot, auto-quote, akun pengunjung |
| Notifikasi | Penerima staf yang dipilih, email dan Telegram, tautan aman ke pesanan/prospek, retry jika gagal | WhatsApp API berbayar, automasi percakapan |
| Konten sosial | Brief dari produk/layanan, Social Kit Satsetui sebagai draf visual, caption yang bisa diedit, persetujuan merchant, lalu jadwal IG/FB melalui Repliz | Auto-post tanpa review, unified inbox, semua platform, automasi komentar/DM |

Definisi `checkout` harus ditulis jelas di mode produk: `Kirim pesanan`, bukan `Bayar sekarang`. Pembeli melihat nomor pesanan dan pesan bahwa toko akan mengonfirmasi stok, ongkir, dan cara pembayaran. Di mode jasa, `Kirim permintaan` tidak membuat pesanan berbayar; pengunjung melihat bahwa staf akan menghubungi untuk membahas lingkup dan harga. Tidak ada klaim bahwa transaksi telah dibayar atau pasti diterima.

## Perjalanan merchant dan pengunjung

1. Pemilik usaha mengaktifkan Website Usaha dari Fabriku, memilih mode, mengisi profil/kontak, memilih produk dan/atau layanan yang ditampilkan, lalu menentukan penerima notifikasi staf. Sebelum publikasi, sistem memeriksa konten minimum sesuai mode dan penerima yang valid; mode jasa tidak diwajibkan mengisi stok atau harga tetap.
2. Fabriku menyediakan subdomain. Untuk domain sendiri, merchant tetap perlu mengubah DNS pada registrar; Fabriku memberi petunjuk, memverifikasi kepemilikan dan tujuan DNS, lalu menerbitkan TLS sebelum domain aktif. Tidak ada janji bahwa proses DNS bisa terjadi tanpa tindakan pemilik domain.
3. Pengunjung mode produk memilih barang, mengisi nama, nomor WhatsApp/telepon, alamat bila perlu, serta catatan. Server menentukan tenant dari hostname terverifikasi, menghitung ulang harga dan stok dari database, lalu membuat pesanan `draft` dengan sumber `website` dan status pembayaran `unpaid`. Harga, nama produk, dan kontak tersimpan sebagai snapshot order.
4. Pengunjung mode jasa memilih layanan (opsional jika konsultasi umum), mengisi nama, kontak, kebutuhan singkat, dan persetujuan dihubungi. Fabriku membuat **prospek**, bukan sales order atau status pembayaran. Untuk mode gabungan, kedua jalur tetap dibedakan; jangan mengubah permintaan penawaran menjadi checkout produk secara diam-diam.
5. Pesanan dan prospek langsung terlihat di kotak masuk Fabriku. Setelah transaksi database berhasil, job mengirim email dan Telegram ke staf terpilih; pemilik usaha menjadi cadangan jika penerima tidak valid. Kegagalan notifikasi tidak menghapus data. Notifikasi tidak perlu memuat detail pribadi; tautkan ke halaman yang memerlukan login.
6. Untuk produk, staf memeriksa ketersediaan, menghubungi pembeli, menentukan ongkir dan cara bayar, lalu mengonfirmasi atau membatalkan pesanan. Konfirmasi mereservasi stok. Bila stok berubah, staf menyesuaikan/membatalkan sebelum konfirmasi.
7. Untuk jasa, staf menghubungi prospek, menyepakati lingkup, jadwal, dan harga di luar sistem. Prospek memiliki status minimum `baru`, `dihubungi`, `dikonversi`, `ditutup`. Hanya setelah ada kesepakatan, staf membuat sales order jasa memakai alur Fabriku yang ada dan menautkannya ke prospek; cegah konversi ganda.
8. Pembayaran produk maupun jasa berlangsung di luar Fabriku, misalnya transfer, QRIS milik usaha, COD, atau tunai. Staf mencatat penerimaan yang benar-benar terverifikasi di ledger pembayaran order. Bukti kirim dari pelanggan bukan perubahan otomatis menjadi `paid`.

Order produk dan order jasa yang sudah disepakati dapat memakai state machine sales order yang ada: `draft` → `confirmed` → `processing`/`shipped` → `completed`, atau `cancelled`. Untuk pembeli produk, label `draft` menjadi `Menunggu konfirmasi toko`. Pembayaran tetap dimensi terpisah (`unpaid`, `partial`, `paid`). Stok produk direservasi ketika order dikonfirmasi dan dikurangi saat selesai; jangan memakai quick checkout POS untuk order publik karena jalur itu menyelesaikan dan membayar order langsung. Prospek jasa tidak ikut state machine order.

## Batas data dan pekerjaan Fabriku

- Tambah entitas website usaha (mode, hostname terverifikasi, tema terbit), katalog publik produk/layanan, serta prospek jasa. Katalog produk adalah barang jual, bukan setiap baris stok fisik: stok Fabriku dapat terpecah per batch atau rak. Sebelum membuat sales order, tentukan pemetaan katalog ke `inventory_items` dan alokasi stok deterministik. Layanan publik memakai data `services` yang aktif; harga boleh `mulai dari` hanya jika benar-benar dikelola merchant, dan permintaan penawaran tidak menjanjikan harga final.
- Semua query publik wajib diberi `tenant_id` secara eksplisit setelah hostname dipetakan. `TenantScope` sekarang hanya membatasi query saat ada pengguna login. Validasi tenant juga berlaku untuk gambar, ID produk/layanan, penerima notifikasi, prospek, dan seluruh langkah checkout.
- Ubah pembuatan nomor order agar menerima tenant secara eksplisit; implementasi sekarang membaca `auth()->user()->tenant_id`, yang tidak tersedia pada checkout publik. Tambahkan kunci idempotensi untuk checkout dan permintaan jasa, rate limit, validasi data, serta perlindungan spam yang tidak menghalangi pengunjung normal.
- Simpan pesanan dan item secara atomik. Harga, diskon, stok, dan ongkir final hanya berasal dari aturan server/staf. Jangan mempercayai `unit_price`, `tenant_id`, atau `payment_status` dari browser. Pertahankan audit siapa yang mengonfirmasi dan siapa yang mencatat pembayaran.
- Gunakan `User.email` dan `User.telegram_chat_id` penerima usaha. Metode `TelegramService::sendMessage()` menargetkan chat staf; `sendAdminNotification()` yang ada mengarah ke admin platform, bukan merchant. Pengiriman via queue setelah commit dengan retry dan status kegagalan yang dapat dilihat admin usaha.
- Templat Satsetui hanya memasok presentasi yang dapat diedit. Grid produk, daftar layanan, formulir prospek, harga, stok, keranjang, checkout, dan order tetap komponen native Fabriku. Jangan memasukkan HTML/JS hasil generasi sebagai aplikasi checkout/formulir.
- Prospek menyimpan persetujuan kontak, dibatasi akses staf tenant, dapat dihapus sesuai kebijakan retensi, dan tidak dipakai untuk kampanye tanpa persetujuan terpisah. Setelah konversi, hubungan ke sales order dapat diaudit.
- Simpan kredensial Satsetui dan Repliz hanya di backend Fabriku. Pemetaan `tenant_id` ke generation/theme Satsetui dan `accountId` Repliz harus divalidasi pada setiap read/write. Audit koneksi, putus akses, dan hapus data saat merchant berhenti memakai fitur.

## Alur Social Kit ke jadwal posting

Merchant memilih produk atau layanan, tujuan posting, platform, dan nada singkat. Fabriku membentuk brief tanpa mengirim data pelanggan/prospek ke Satsetui. Satsetui mengembalikan gambar per slide dan metadata; caption dapat dihasilkan/diedit di Fabriku, tetapi **caption siap pakai adalah pengembangan kontrak API**, bukan kemampuan yang diasumsikan sudah tersedia. Fabriku menyimpan draf dan versi yang disetujui merchant sebagai sumber kebenaran.

Sesudah review, merchant memilih akun sosial dan waktu. Backend memvalidasi format, ukuran, rasio, caption, dan jumlah gambar per platform sebelum mengunggah media atau memberikan URL aman ke Repliz, lalu menyimpan `schedule_id`, status, dan error per tenant. IG/FB menjadi cakupan awal. Satu gambar adalah jalur termudah; carousel hanya jika hasil Social Kit memenuhi aturan platform (IG/FB umumnya 2–10 gambar). Status `terjadwal` tidak sama dengan `terbit`; tampilkan kegagalan, retry yang aman, dan permintaan reconnect. Jangan mem-post otomatis segera setelah AI selesai. [Spesifikasi jadwal Repliz](https://docs.repliz.com/tutorial/specification/), [alur upload Storage API](https://docs.repliz.com/api/guides/storage-upload).

## Repliz sebagai layanan yang dikelola Fabriku

Pilihan pilot: satu akun/workspace Repliz milik Fabriku; merchant menghubungkan akun sosial melalui tombol di Fabriku. Setiap `accountId` hasil OAuth dipetakan ke tepat satu tenant. Merchant tidak login ke Repliz, tetapi pemilik akun sosial tetap perlu memberi izin pada layar otorisasi platform. API key Repliz berhak atas seluruh workspace, sehingga isolasi antar-tenant sepenuhnya tanggung jawab backend Fabriku. Jangan pernah memberikan API key atau akses langsung ke workspace bersama kepada merchant.

Sebelum menjual penjadwalan sosial sebagai fitur paket, minta konfirmasi tertulis kepada Repliz mengenai model banyak merchant dalam satu workspace, pembundelan biaya, atribusi, batas akun/jadwal, kepemilikan data, serta jalan keluar bila workspace perlu dipecah. Dokumentasi publik menyediakan OAuth akun dan Schedule API, tetapi daftar endpoint yang ditinjau belum menunjukkan provisioning workspace otomatis. Bila model bersama tidak disetujui atau risiko isolasi tidak dapat diterima, Fabriku menyiapkan workspace terpisah secara internal; pengalaman merchant tetap satu akun dan satu tagihan. [API dan tier Repliz](https://docs.repliz.com/api/install), [OAuth](https://docs.repliz.com/api/oauth/oauth-flow), [kapasitas paket](https://docs.repliz.com/tutorial/plans).

`Powered by Repliz` dan `Powered by Satsetui` dapat ditampilkan pada bagian yang memakai layanan tersebut. `Powered by Fabriku` dapat tampil pada website usaha. Atribusi bukan pengganti persetujuan akses akun sosial atau penjelasan pemrosesan data dalam kebijakan privasi.

## Urutan kerja dan gerbang rilis

| Tahap | Keluaran | Bukti selesai |
|---|---|---|
| 0. Validasi komersial | Keputusan tertulis Repliz; biaya Satsetui/Repliz/domain/penyimpanan; paket, kuota, dan migrasi pelanggan lama | Tidak ada fitur yang dijual tanpa hak penggunaan, margin masuk akal, dan kebijakan pelanggan lama yang jelas |
| 1. Fondasi website | Hostname dan sertifikat; katalog produk/layanan; editor bagian terbatas; publikasi/preview | Demo produk, jasa, dan gabungan terbuka lewat subdomain/domain sendiri tanpa kebocoran tenant |
| 2. Permintaan masuk | Checkout produk dan prospek jasa, inbox, email/Telegram, tindak lanjut staf, pencatatan bayar pada order | Satu permintaan produk dan satu prospek jasa sampai order selesai; notifikasi gagal tetap muncul; submit ulang tidak menggandakan data |
| 3. Desain Satsetui | API server-to-server, tema terstruktur untuk semua mode, status generasi, impor dan edit di Fabriku | Merchant tidak mempunyai akun Satsetui; desain dapat diedit tanpa menyentuh HTML hasil generasi |
| 4. Sosial terbatas | Social Kit → review draf → koneksi IG/FB → jadwal → status terbit/gagal | Dua tenant tidak dapat mengubah akun/jadwal satu sama lain; izin OAuth, batas media, dan pencabutan diuji |
| 5. Peluncuran | Copy/harga/privasi diperbarui, halaman fitur terindeks, bantuan staf, observabilitas | Uji end-to-end dan SEO lolos; staf support mampu menjelaskan pembayaran manual dan domain |

Tahap 1 dan 2 dapat dikerjakan paralel setelah model katalog diputuskan. Tahap 3 dan 4 dapat diuji pada pilot tertutup, tetapi tidak diumumkan sebagai fitur paket sebelum gerbangnya lolos. Fitur yang belum lolos gerbang tidak ditagih sebagai kemampuan aktif.

Ukur keberhasilan pilot per mode: `website dipublikasikan`, `checkout valid`, `prospek jasa valid`, `waktu staf menghubungi`, `prospek dikonversi`, `pesanan terkonfirmasi/dibayar/selesai`, `draf Social Kit disetujui`, `posting terbit/gagal`, `gagal notifikasi`, serta biaya AI/Repliz/hosting per tenant. Jangan memakai traffic saja sebagai ukuran nilai produk.

## Dasar kode yang diperiksa

- Fabriku: [`SalesOrderController`](../../app/Http/Controllers/SalesOrderController.php), [`SalesOrderObserver`](../../app/Observers/SalesOrderObserver.php), [`SalesOrder`](../../app/Models/SalesOrder.php), [`Service`](../../app/Models/Service.php), [`TenantScope`](../../app/Models/Scopes/TenantScope.php), [`TelegramService`](../../app/Services/Telegram/TelegramService.php).
- Satsetui: `/home/fauzan/dev/satsetui/app/Http/Controllers/GenerationController.php`, `/home/fauzan/dev/satsetui/app/Http/Requests/StoreSocialKitRequest.php`, `/home/fauzan/dev/satsetui/app/Services/GenerationService.php`, dan `/home/fauzan/dev/satsetui/app/Services/CreditService.php` (repo saudara, dibaca tanpa mengubahnya).
