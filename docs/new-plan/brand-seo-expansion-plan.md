# Rencana branding dan SEO Fabriku untuk Website Usaha

Status: rancangan, 23 September 2026. Copy di bawah adalah usulan untuk diuji, bukan klaim bahwa fitur baru sudah aktif. Baca bersama [rencana MVP](commerce-mvp-plan.md), [paket dan migrasi pelanggan](website-usaha-pricing-plan.md), dan [API Satsetui](satsetui-api-plan.md).

## Perubahan posisi merek

Posisi saat ini di homepage adalah `Aplikasi Produksi dan Stok untuk UMKM`, dengan pesan utama tentang bahan baku, produksi, stok, penjualan, dan laporan. Posisi baru tidak perlu mengganti identitas Fabriku menjadi platform e-commerce generik. Fabriku menghubungkan kerja usaha dan kehadiran online: produsen menampilkan produk serta menerima pesanan; penyedia jasa menampilkan layanan serta menerima permintaan; usaha gabungan memakai keduanya.

Usulan kategori: **aplikasi operasional dan website usaha untuk UMKM**. Bahasa publik tetap sederhana: `produksi`, `stok`, `layanan`, `website usaha`, `pesanan`, `permintaan penawaran`, dan `konten sosial`. `Toko online` tetap subfitur produk, bukan payung untuk semua usaha. Hindari `omnichannel marketplace`, `booking otomatis`, atau `checkout otomatis` selama kemampuan itu belum ada. Jangan mengklaim satu harga untuk semua pelanggan sebelum paket Online diluncurkan; katakan `satu akun dan satu tagihan Fabriku`.

| Kebutuhan pemilik UMKM | Pesan yang boleh diucapkan setelah fitur aktif | Bukti produk yang harus ditunjukkan |
|---|---|---|
| Stok toko dan stok kerja sering berbeda | Produk toko memakai data stok Fabriku yang sama dengan operasional | Demo satu produk yang berubah ketersediaannya setelah order dikonfirmasi |
| Pesanan dari halaman sendiri tercecer di chat | Pesanan dari toko masuk ke daftar kerja staf, dengan email dan Telegram | Demo pembeli mengirim order dan staf menerimanya |
| Calon pelanggan jasa belum tahu layanan dan cara menghubungi | Website menampilkan layanan dan permintaan penawaran masuk ke staf | Demo landing page jasa, prospek masuk, staf menghubungi, lalu konversi ke sales order |
| Desain website usaha sulit dimulai | Buat tampilan awal produk atau jasa, lalu ubah isi dan warnanya di Fabriku | Editor nyata, bukan mockup atau HTML statis |
| Promosi sosial dikerjakan di aplikasi lain | Buat draf konten dari produk/layanan, tinjau, lalu jadwalkan dari Fabriku setelah akun sosial dihubungkan | Social Kit yang dapat diedit, akun terhubung, jadwal dan hasil terbit yang berhasil pada pilot |

Contoh headline homepage **setelah seluruh kemampuan yang disebut benar-benar rilis**: **`Kelola usaha dan tampil online dari satu tempat.`** Subteks: `Atur produksi, stok, dan layanan di Fabriku. Buat website untuk menerima pesanan produk atau permintaan jasa, lalu tindak lanjuti bersama staf Anda.` CTA pengguna login: `Buat website usaha`; CTA pengunjung: `Lihat cara kerja` atau ajakan trial yang sesuai kebijakan paket aktual. Di halaman produk jelaskan pembayaran manual; di halaman jasa jelaskan harga/lingkup dibahas bersama staf. Homepage tidak perlu mengesankan semua usaha wajib memproduksi barang.

Suara merek: lugas dan akrab tanpa berlebihan. Jelaskan apa yang dilakukan staf dan sistem; sebut layanan pihak ketiga hanya pada atribusi atau penjelasan izin data yang relevan. `Powered by Fabriku`, `Desain didukung Satsetui`, dan `Fitur sosial didukung Repliz` bisa dipakai sesuai konteks. Logo, warna, dan tipografi yang sudah ada tidak perlu diganti hanya karena cakupan produk bertambah. Audit visual dilakukan setelah posisi dan bukti produk disetujui; dokumen ini tidak menetapkan identitas visual baru.

Pekerjaan branding sebelum desain ulang: inventaris logo, warna, tipografi, ilustrasi, screenshot, dan nada copy yang sudah dipakai; tulis panduan suara merek dan hirarki pesan untuk homepage, dashboard, email order/prospek, serta demo website produk dan jasa. Setelah pilot menunjukkan posisi yang dipahami UMKM, pemilik merek memilih antara mempertahankan identitas visual saat ini atau melakukan penyegaran. Baru kemudian perbarui panduan merek, token UI, materi demo, OG image, dan aset sosial secara konsisten. Jangan membuat logo, foto pelanggan, atau statistik pengganti tanpa sumber dan persetujuan.

## Pembanding Indonesia dan ruang diferensiasi

[majoo](https://majoo.id/news/more/majoo-hadirkan-fitur-add-on-untuk-web-order-solusi-baru-untuk-efisiensi-dan-profitabilitas-bisnis-online) sudah menawarkan Web Order dengan stok, [Kasir Pintar/Olshopin](https://kasirpintar.co.id/online-shop) menghubungkan katalog dan stok ke kasir, [Olsera](https://www.olsera.com/id/pos/butik) menawarkan webstore terhubung POS, dan [Jubelio](https://jubelio.com/aplikasi-omnichannel-marketplace/) kuat pada banyak channel penjualan. [OrderOnline](https://help.orderonline.id/) memiliki Landing Page Builder dan Storefront, serta [menyebut produk maupun jasa](https://orderonline.id/home-google/) dalam materi mereka. Karena itu klaim `pertama` atau `satu-satunya` tidak layak. Hipotesis pembeda Fabriku ialah hubungan website produk/jasa dengan pekerjaan operasional, staf, dan sales order dalam satu akun; uji pada UMKM produk, jasa, dan gabungan. Jangan menyatakan pesaing tidak punya fitur tertentu tanpa audit terkini.

## Struktur situs marketing dan kapan dipublikasikan

Situs Fabriku dan website merchant memiliki tujuan SEO berbeda. Homepage Fabriku menjual perangkat lunak; domain merchant menjelaskan produk atau layanan usahanya. Jangan mengindeks halaman demo atau template massal seolah-olah itu usaha nyata.

| URL usulan | Maksud pencarian | Isi minimum sebelum indeks |
|---|---|---|
| `/` | Memahami Fabriku secara utuh | Jalur produk dan jasa yang nyata; harga/paket yang benar; CTA ke demo/registrasi |
| `/fitur/website-usaha` | Memahami website untuk produk/jasa | Demo tiga mode, domain, editor, notifikasi, batas paket, cara tindak lanjut |
| `/fitur/toko-online` | Mencari toko online terhubung stok | Demo storefront nyata, alur order manual, domain, editor, batasan paket |
| `/solusi/umkm-jasa` | Mencari landing page dan tindak lanjut jasa | Demo layanan → permintaan → staf → sales order, tanpa janji booking/pembayaran otomatis |
| `/solusi/umkm-produksi` | Mencari sistem kerja usaha produksi | Kasus operasional dari bahan hingga pesanan; tautan ke fitur relevan |
| `/blog/...` | Mencari jawaban masalah spesifik | Artikel hasil pengalaman produk/UMKM, contoh proses, tautan ke fitur yang benar-benar ada |

Halaman fitur baru dibuat `noindex` atau tidak dipublikasikan sampai fitur dan demo berfungsi. Jangan membuat banyak halaman kota/industri dengan teks hampir sama. Pengunjung harus mendapat informasi yang berguna walaupun belum mendaftar. [Panduan konten yang membantu dari Google](https://developers.google.com/search/docs/fundamentals/creating-helpful-content).

Contoh judul dan deskripsi, untuk diuji setelah peluncuran:

- Homepage title: `Fabriku | Operasional dan Website Usaha untuk UMKM`.
- Homepage description: `Kelola produksi, stok, dan layanan. Buat website untuk menerima pesanan produk atau permintaan jasa, lalu tindak lanjuti dari Fabriku.`
- Halaman fitur title: `Toko Online yang Terhubung ke Stok Fabriku | Fabriku`.
- H1 halaman fitur: `Terima pesanan dari toko online milik usaha Anda`.
- Halaman jasa title: `Landing Page Jasa dan Permintaan Pelanggan | Fabriku`.
- H1 halaman jasa: `Tampilkan layanan dan terima permintaan pelanggan`.

Gunakan judul dan H1 yang sesuai isi halaman, bukan variasi kata kunci yang dipaksakan. Riset kata kunci awal bersifat hipotesis tanpa angka volume: `aplikasi produksi stok dan toko online UMKM`, `toko online terhubung stok barang`, `landing page jasa UMKM`, `website jasa dengan formulir permintaan`, `cara menerima permintaan jasa dari website`, dan `aplikasi pesanan online untuk usaha rumahan`. Cocokkan dengan kueri Search Console setelah halaman hidup. Pertahankan strategi long-tail yang sudah ada di [`seo-keyword-strategy.md`](../seo-keyword-strategy.md), lalu tambah artikel dari pertanyaan pilot yang benar-benar sering muncul.

## SEO teknis situs Fabriku

Audit September 2026 mencatat SSR/meta dan tautan internal sebagai hambatan, lalu mencatat perbaikannya telah diverifikasi di produksi pada 10 September. Jangan menjadwalkan ulang pekerjaan itu tanpa cek regresi. Mulai dari inspeksi HTML awal homepage, blog, dan halaman fitur baru: title, description, H1, canonical, OG, dan tautan internal harus tersedia tanpa mengandalkan JS setelah halaman selesai dirilis. Halaman harga dan `SoftwareApplication` structured data harus mengikuti paket/harga aktual, bukan nilai default lama yang sudah berubah. Rujukan internal: [`seo-audit-2026-09-09.md`](../seo-audit-2026-09-09.md) dan [`Welcome.vue`](../../resources/js/pages/Welcome.vue).

Hostname resmi produk dan marketing sudah `fabriku.id` (migrasi dari `fabriku.web.id`, commit `2b6ccee`). Website merchant memakai `{slug}.fabriku.biz.id` dan domain sendiri lewat Cloudflare for SaaS (lihat [master plan](master-plan.md#58-domain-dan-seo-toko)). Konfigurasi canonical, sertifikat wildcard, tautan email, OG, dan Search Console harus mengikuti keputusan yang sama; jangan mencampur kedua domain dalam output publik.

Tambahkan tautan yang masuk akal dari homepage ke halaman fitur, dari artikel terkait ke fitur, dan dari halaman fitur ke contoh website yang memperoleh izin merchant untuk dipublikasikan. Jangan mengarang testimoni, angka penghematan, atau hasil konversi. Catat event `lihat fitur website`, `mulai registrasi`, `website dipublikasikan`, `checkout valid`, dan `prospek jasa valid` tanpa mengirim nama/nomor telepon pengunjung ke analytics.

## SEO tiap website merchant

- Setiap hostname yang telah diverifikasi mendapat HTML awal yang dapat dirayapi, title/H1/deskripsi usaha, dan URL produk/layanan stabil. Halaman draft, editor, checkout, formulir terima kasih, serta status pesanan/prospek diberi `noindex`; jangan memasukkan URL pribadi atau berparameter filter ke sitemap.
- Bila website memakai subdomain dan domain sendiri, pilih satu hostname utama. Arahkan hostname sekunder dengan 301 ke URL yang setara; canonical, tautan internal, OG URL, dan sitemap memakai hostname utama yang sama. Saat domain berpindah, pertahankan redirect dan perbarui sitemap. Google memperlakukan canonical sebagai sinyal, bukan jaminan. [Panduan canonical](https://developers.google.com/search/docs/crawling-indexing/canonicalization), [perpindahan situs](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes).
- Sediakan `robots.txt` dan sitemap per website/host yang hanya berisi halaman terbit dan produk/layanan aktif, dengan URL absolut. Hapus atau arahkan URL saat konten dihapus/slug berubah. Halaman kosong atau duplikat tidak perlu diindeks. [Panduan sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap), [struktur URL e-commerce](https://developers.google.com/search/docs/specialty/ecommerce/designing-a-url-structure-for-ecommerce-sites).
- `Product` structured data hanya pada halaman produk dengan nama, harga, mata uang, dan ketersediaan yang cocok dengan teks yang terlihat dan keadaan stok. Karena checkout meminta konfirmasi manual, jangan mengklaim harga akhir/ongkir pasti jika belum diketahui. Validasi dengan Rich Results Test; markup tidak menjamin tampilan khusus di hasil pencarian. [Panduan Product](https://developers.google.com/search/docs/appearance/structured-data/product-snippet).
- Halaman jasa boleh memakai markup `Service`/`LocalBusiness` hanya jika jenis layanan, penyedia, alamat, dan area layanan memang tersedia serta sama dengan konten terlihat; jangan mengarang harga, rating, atau jam buka. Jangan menjanjikan rich result khusus untuk setiap markup. [Dokumentasi data terstruktur Google](https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data).
- Desain Satsetui tidak boleh membuat banyak halaman dengan copy contoh identik. Merchant mengisi informasi usaha, produk/layanan, bukti kerja asli, dan kontak sebelum indeks diaktifkan. Foto memakai deskripsi alternatif yang benar; gambar contoh tidak dianggap produk atau portofolio nyata.
- Jika atribusi `Powered by Fabriku` berupa tautan di footer website, gunakan nama merek biasa, bukan anchor kata kunci seperti `aplikasi toko online UMKM terbaik`. Jangan menjadikan footer lintas ribuan situs sebagai taktik backlink. [Kebijakan link spam Google](https://developers.google.com/search/docs/essentials/spam-policies).

## Urutan publikasi dan pengukuran

1. Sebelum rilis: sepakati paket, kuota, perlakuan pelanggan lama, cek kebenaran copy, siapkan demo produk **dan jasa**, perbarui privasi/syarat terkait domain, pembeli/prospek, Satsetui, dan Repliz. Jangan mempublikasikan janji sosial bila gerbang Repliz belum lolos.
2. Pilot tertutup: uji headline dan pemahaman alur pembayaran manual/permintaan jasa dengan pemilik UMKM produk, jasa, serta gabungan; catat istilah yang mereka pakai. Perbaiki onboarding dan istilah sebelum membuat artikel SEO baru.
3. Rilis: perbarui homepage, halaman fitur, navigasi, sitemap, OG, dan tautan internal. Lakukan inspeksi HTML mentah, URL Inspection, Rich Results Test bila ada Product markup, serta uji share WhatsApp.
4. Setelah rilis: pantau indeks/kueri non-brand di Search Console dan event funnel per bulan. Ukur `kunjungan fitur → pendaftaran → website terbit → order/prospek valid → tindak lanjut`, bukan sekadar posisi kata kunci. Jika banyak permintaan masuk tetapi tidak direspons, perbaiki operasi/notifikasi sebelum menambah traffic.
