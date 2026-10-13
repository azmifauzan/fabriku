<?php

/*
|--------------------------------------------------------------------------
| Category landing pages (/aplikasi/{slug})
|--------------------------------------------------------------------------
|
| One commercial-intent page per business_category. Copy must only claim
| features that exist for that category (see config/business.php rules).
| `blog_tags` pulls related published posts by tag slug.
|
*/

return [
    'konveksi' => [
        'category' => 'garment',
        'meta_title' => 'Aplikasi Produksi & Stok Konveksi untuk UMKM | Fabriku',
        'meta_description' => 'Aplikasi konveksi untuk mencatat stok kain, pola, produksi internal maupun maklun, stok barang jadi per ukuran, dan penjualan dalam satu alur.',
        'eyebrow' => 'Garment & Konveksi',
        'headline' => 'Aplikasi konveksi untuk stok kain, produksi, dan maklun.',
        'intro' => 'Catat kain masuk, potong sesuai pola, kirim ke penjahit internal atau maklun, lalu terima barang jadi ke gudang. Semua tercatat tanpa rekap ulang di Excel.',
        'pains' => [
            ['title' => 'Stok kain tidak cocok dengan catatan', 'body' => 'Kain dipakai untuk beberapa order sekaligus dan sisa potongan tidak pernah dicatat, sehingga stok di buku selalu lebih banyak dari di rak.'],
            ['title' => 'Maklun telat tanpa ketahuan', 'body' => 'Barang sudah dikirim ke penjahit luar, tapi tidak ada catatan kapan dikirim, berapa potong, dan kapan harus kembali.'],
            ['title' => 'Stok per ukuran berantakan', 'body' => 'Kaos ukuran M habis padahal di catatan masih ada, karena barang jadi tidak dicatat per ukuran dan per lokasi rak.'],
        ],
        'features' => [
            ['title' => 'Penerimaan & stok bahan baku', 'body' => 'Catat kain masuk per penerimaan, lengkap dengan warna, lebar, dan gramasi. Stok berkurang otomatis saat bahan dipakai di order persiapan (potong).'],
            ['title' => 'Pola dan order persiapan', 'body' => 'Simpan pola beserta jumlah hasil dan estimasi ongkos kerja, lalu buat order persiapan (potong) yang mencatat kain terpakai sebelum masuk produksi.'],
            ['title' => 'Produksi internal & maklun', 'body' => 'Pilih dikerjakan tim sendiri atau kontraktor/maklun. Status, jumlah, dan tenggat setiap order produksi terlihat di satu daftar.'],
            ['title' => 'Stok barang jadi per rak', 'body' => 'Hasil produksi masuk ke inventaris dengan SKU dan lokasi rak sendiri, bisa dipindah atau dipecah antar rak, dan dicetak QR code-nya.'],
            ['title' => 'Sales order & pembayaran', 'body' => 'Pesanan reseller atau online mencadangkan stok saat dikonfirmasi, lalu stok berkurang saat order selesai. Pembayaran sebagian dan piutang tercatat per order.'],
            ['title' => 'Laporan siap ekspor', 'body' => 'Laporan bahan baku, produksi, dan penjualan bisa diekspor ke Excel atau PDF.'],
        ],
        'faqs' => [
            ['question' => 'Apakah Fabriku bisa mencatat penjahit maklun?', 'answer' => 'Bisa. Daftarkan penjahit atau konveksi luar sebagai kontraktor, lalu pilih kontraktor tersebut saat membuat order produksi. Jumlah yang dikirim dan tenggatnya tercatat di order produksi.'],
            ['question' => 'Apakah stok kain berkurang otomatis?', 'answer' => 'Ya. Bahan yang dipakai di order persiapan mengurangi stok bahan baku, dengan urutan pemakaian FIFO dari penerimaan paling lama.'],
            ['question' => 'Apakah cocok untuk konveksi kecil dengan 2-3 orang?', 'answer' => 'Cocok. Anda bisa mulai dari stok bahan dan penjualan dulu, lalu memakai modul produksi saat alurnya sudah rapi. Masa uji coba gratis tersedia tanpa kartu kredit.'],
        ],
        'blog_tags' => ['konveksi', 'maklun', 'bom'],
    ],

    'usaha-makanan-kue' => [
        'category' => 'food',
        'meta_title' => 'Aplikasi Usaha Makanan & Kue: Stok, Produksi, Kadaluarsa | Fabriku',
        'meta_description' => 'Aplikasi untuk usaha makanan dan kue: catat bahan baku dengan tanggal kadaluarsa, produksi harian, stok produk jadi, dan pesanan pelanggan.',
        'eyebrow' => 'Makanan & Kue',
        'headline' => 'Aplikasi usaha makanan dan kue yang memperhatikan tanggal kadaluarsa.',
        'intro' => 'Bahan baku pangan punya umur pendek. Fabriku mencatat batch, suhu simpan, dan tanggal kadaluarsa sejak bahan masuk, jadi stok yang hampir kedaluwarsa bisa dipakai lebih dulu.',
        'pains' => [
            ['title' => 'Bahan terbuang karena kedaluwarsa', 'body' => 'Mentega dan susu baru ketahuan kedaluwarsa saat mau dipakai, karena stok lama tertumpuk di belakang stok baru.'],
            ['title' => 'Produksi harian tidak tercatat', 'body' => 'Berapa loyang yang dibuat hari ini dan berapa bahan yang terpakai hanya diingat, tidak pernah ditulis.'],
            ['title' => 'Pesanan custom tercecer', 'body' => 'Pesanan kue ulang tahun dan hampers datang dari WhatsApp, Instagram, dan titip lisan, lalu ada yang terlewat.'],
        ],
        'features' => [
            ['title' => 'Tanggal kadaluarsa & batch', 'body' => 'Setiap bahan menyimpan nomor batch, suhu simpan, dan tanggal kadaluarsa (wajib untuk kategori makanan). Pemakaian bahan mengurangi stok dari penerimaan paling lama lebih dulu.'],
            ['title' => 'Persiapan & produksi', 'body' => 'Bahan yang dipakai tercatat di order persiapan, lalu order produksi mencatat jumlah hasil jadi yang masuk ke stok.'],
            ['title' => 'Stok produk jadi', 'body' => 'Produk jadi tercatat dengan tanggal kadaluarsa sendiri, sehingga stok yang hampir habis masa simpannya terlihat lebih dulu.'],
            ['title' => 'Pesanan & pembayaran', 'body' => 'Semua pesanan masuk ke satu daftar sales order dengan status, tanggal kirim, dan DP atau pelunasan.'],
            ['title' => 'Website usaha', 'body' => 'Tampilkan menu atau katalog di website usaha sendiri. Pesanan dari website masuk ke Fabriku untuk ditindaklanjuti.'],
            ['title' => 'Laporan penjualan', 'body' => 'Rekap penjualan per periode dan per produk, siap diekspor ke Excel atau PDF.'],
        ],
        'faqs' => [
            ['question' => 'Apakah tanggal kadaluarsa bahan wajib diisi?', 'answer' => 'Ya, untuk kategori Makanan & Kue tanggal kadaluarsa bahan wajib diisi. Pemakaian bahan mengurangi stok dari penerimaan paling lama lebih dulu (FIFO).'],
            ['question' => 'Apakah bisa untuk usaha kue rumahan?', 'answer' => 'Bisa. Untuk usaha rumahan yang alurnya lebih sederhana, kategori Produksi Rumahan tersedia dengan pencatatan produksi yang lebih ringkas.'],
            ['question' => 'Apakah bisa mencatat sertifikat halal?', 'answer' => 'Bahan baku kategori makanan punya kolom penanda sertifikat halal, sehingga Anda bisa memfilter bahan yang sudah bersertifikat.'],
        ],
        'blog_tags' => ['kuliner-rumahan', 'produksi-rumahan', 'hpp'],
    ],

    'kerajinan-handmade' => [
        'category' => 'craft',
        'meta_title' => 'Aplikasi Usaha Kerajinan & Handmade: Bahan, HPP, Pesanan | Fabriku',
        'meta_description' => 'Aplikasi untuk usaha kerajinan tangan dan handmade: catat bahan baku tidak standar, produksi batch kecil dan custom order, HPP, dan penjualan.',
        'eyebrow' => 'Kerajinan & Craft',
        'headline' => 'Aplikasi usaha kerajinan untuk bahan tidak standar dan custom order.',
        'intro' => 'Manik lepasan, sisa kain, potongan kayu. Bahan kerajinan jarang seragam. Fabriku mencatatnya tetap sebagai stok bernilai, sehingga HPP dan harga jual dihitung dari data.',
        'pains' => [
            ['title' => 'Bahan sisa tidak pernah dihitung', 'body' => 'Sisa bahan dianggap gratis, padahal dipakai lagi di produk berikutnya. Akibatnya HPP terlihat lebih kecil dari kenyataan.'],
            ['title' => 'Harga jual ikut kompetitor', 'body' => 'Tanpa catatan biaya per produk, harga ditentukan dari marketplace. Jam kerja pengrajin ikut tidak terhitung.'],
            ['title' => 'Custom order sulit dilacak', 'body' => 'Setiap pesanan punya warna dan ukuran berbeda, dan progresnya hanya ada di kepala pemilik.'],
        ],
        'features' => [
            ['title' => 'Stok bahan dengan atribut bebas', 'body' => 'Catat bahan dengan warna, ukuran, dan nomor batch, termasuk bahan sisa yang masih bisa dipakai.'],
            ['title' => 'Produksi batch kecil', 'body' => 'Bahan terpakai dicatat di order persiapan dan hasil jadi di order produksi, cocok untuk batch kecil maupun satuan.'],
            ['title' => 'Biaya per produk', 'body' => 'Harga pokok tersimpan di setiap item stok, menjadi dasar menentukan harga jual dan margin.'],
            ['title' => 'Sales order custom', 'body' => 'Pesanan custom dicatat dengan catatan detail, status pengerjaan, dan pembayaran DP.'],
            ['title' => 'Katalog di website usaha', 'body' => 'Tampilkan karya di website usaha dengan foto produk dari stok Fabriku.'],
            ['title' => 'Ekspor laporan', 'body' => 'Laporan bahan, produksi, dan penjualan bisa diekspor ke Excel atau PDF.'],
        ],
        'faqs' => [
            ['question' => 'Bagaimana mencatat bahan yang ukurannya tidak seragam?', 'answer' => 'Catat dalam satuan yang paling sering dipakai (gram, lembar, batang), lalu gunakan atribut warna atau ukuran untuk membedakan. Yang penting nilainya tercatat.'],
            ['question' => 'Apakah Fabriku menghitung harga jual otomatis?', 'answer' => 'Fabriku menyimpan biaya per item dan harga jual. Penentuan margin tetap di tangan Anda; panduan perhitungannya ada di blog Fabriku.'],
            ['question' => 'Apakah perlu produksi untuk setiap pesanan?', 'answer' => 'Tidak wajib. Anda bisa langsung menambah stok barang jadi bila tidak perlu mencatat proses produksinya.'],
        ],
        'blog_tags' => ['kerajinan', 'harga-jual', 'hpp'],
    ],

    'kosmetik-skincare' => [
        'category' => 'cosmetic',
        'meta_title' => 'Aplikasi Stok Kosmetik & Skincare: Batch, BPOM, Kadaluarsa | Fabriku',
        'meta_description' => 'Aplikasi stok kosmetik dan skincare untuk mencatat nomor batch, nomor BPOM, tanggal kadaluarsa, stok per lokasi, dan penjualan ke reseller.',
        'eyebrow' => 'Kosmetik & Skincare',
        'headline' => 'Aplikasi stok kosmetik dengan batch, BPOM, dan tanggal kadaluarsa.',
        'intro' => 'Produk kosmetik wajib bisa dilacak per batch. Fabriku menyimpan batch, nomor BPOM, dan tanggal kadaluarsa di setiap bahan dan produk, sehingga stok bisa ditelusuri dan dipantau masa simpannya.',
        'pains' => [
            ['title' => 'Produk kedaluwarsa masih di rak', 'body' => 'Stok baru ditaruh di depan dan stok lama tertinggal sampai lewat tanggal kadaluarsa.'],
            ['title' => 'Batch tidak tercatat', 'body' => 'Saat ada keluhan pelanggan, batch produk yang terjual tidak bisa ditelusuri.'],
            ['title' => 'Stok reseller selisih', 'body' => 'Stok yang dititipkan atau dijual ke reseller tidak tercatat terpisah sehingga angka gudang tidak cocok.'],
        ],
        'features' => [
            ['title' => 'Batch, BPOM, kadaluarsa', 'body' => 'Batch dan tanggal kadaluarsa wajib diisi untuk kategori kosmetik. Nomor BPOM dan suhu simpan bisa dicatat per bahan.'],
            ['title' => 'Pemakaian bahan FIFO', 'body' => 'Saat produksi, stok bahan berkurang dari penerimaan paling lama lebih dulu, sehingga bahan lama tidak tertinggal di gudang.'],
            ['title' => 'Stok per lokasi', 'body' => 'Pisahkan stok per gudang atau rak, pindahkan antar lokasi, dan cetak QR code untuk cek cepat.'],
            ['title' => 'Produksi & maklon', 'body' => 'Catat produksi oleh tim sendiri atau pihak ketiga (maklon), dari bahan yang dipakai di order persiapan sampai hasil jadi masuk stok.'],
            ['title' => 'Penjualan per channel', 'body' => 'Sales order dicatat per channel: offline, online, marketplace, atau reseller.'],
            ['title' => 'Laporan & ekspor', 'body' => 'Laporan stok dan penjualan siap diekspor ke Excel atau PDF.'],
        ],
        'faqs' => [
            ['question' => 'Apakah Fabriku mengurus izin BPOM?', 'answer' => 'Tidak. Fabriku hanya mencatat nomor BPOM yang sudah Anda miliki sebagai data stok, bukan layanan perizinan.'],
            ['question' => 'Apakah bisa membedakan penjualan reseller?', 'answer' => 'Bisa. Setiap sales order memiliki channel, salah satunya reseller, sehingga laporan bisa dipisah per channel.'],
            ['question' => 'Apakah stok produk yang hampir kedaluwarsa terlihat?', 'answer' => 'Ya. Tanggal kadaluarsa tersimpan di setiap item stok sehingga bisa diurutkan dan dipantau.'],
        ],
        'blog_tags' => ['kosmetik'],
    ],

    'toko-retail' => [
        'category' => 'retail',
        'meta_title' => 'Aplikasi Stok Toko & Kasir Sederhana untuk UMKM Retail | Fabriku',
        'meta_description' => 'Aplikasi toko untuk UMKM retail: catat pembelian dari supplier, stok per rak dengan QR code, penyesuaian stok, kasir cepat, dan laporan penjualan.',
        'eyebrow' => 'Toko / Retail',
        'headline' => 'Aplikasi stok toko untuk pembelian, rak, dan kasir cepat.',
        'intro' => 'Untuk toko yang membeli barang jadi lalu menjualnya kembali. Tidak ada modul produksi yang mengganggu: cukup pembelian, stok, dan penjualan.',
        'pains' => [
            ['title' => 'Stok sistem beda dengan rak', 'body' => 'Barang rusak, hilang, atau retur tidak pernah dicatat, sehingga angka stok makin jauh dari kenyataan.'],
            ['title' => 'Pembelian supplier tidak terdokumentasi', 'body' => 'Faktur pembelian disimpan di laci dan harga modal tidak diperbarui.'],
            ['title' => 'Antrian kasir lambat', 'body' => 'Mencari harga dan mencatat penjualan manual membuat pelanggan menunggu.'],
        ],
        'features' => [
            ['title' => 'Penerimaan pembelian', 'body' => 'Catat barang masuk dari supplier beserta nomor faktur, sekaligus beberapa item dalam satu transaksi.'],
            ['title' => 'Penyesuaian stok', 'body' => 'Catat koreksi, barang rusak, hilang, ditemukan, dan retur, dengan riwayat lengkap per item.'],
            ['title' => 'Stok per rak & QR code', 'body' => 'Setiap item punya lokasi rak dan QR code. Scan untuk melihat stok tanpa mencari di daftar.'],
            ['title' => 'Kasir cepat', 'body' => 'Halaman quick checkout untuk penjualan langsung di toko, stok berkurang saat transaksi selesai.'],
            ['title' => 'Website usaha', 'body' => 'Tampilkan katalog toko secara online dan terima pesanan dari website.'],
            ['title' => 'Rekap penjualan', 'body' => 'Rekap penjualan harian dan per produk, siap diekspor ke Excel atau PDF.'],
        ],
        'faqs' => [
            ['question' => 'Apakah Fabriku pengganti mesin kasir?', 'answer' => 'Fabriku menyediakan kasir cepat berbasis web untuk mencatat penjualan dan mengurangi stok. Fabriku tidak terhubung ke laci uang atau printer struk thermal khusus.'],
            ['question' => 'Bagaimana cara stok opname?', 'answer' => 'Hitung fisik per rak, lalu catat selisihnya sebagai penyesuaian stok (koreksi, rusak, atau hilang). Riwayatnya tersimpan per item.'],
            ['question' => 'Apakah perlu modul produksi?', 'answer' => 'Tidak. Kategori Toko/Retail menyembunyikan modul produksi, sehingga menu hanya berisi pembelian, stok, dan penjualan.'],
        ],
        'blog_tags' => ['retail', 'stok-opname', 'aplikasi-kasir', 'retur-barang'],
    ],

    'produksi-rumahan' => [
        'category' => 'homemade',
        'meta_title' => 'Aplikasi Pencatatan Usaha Rumahan: Produksi & Stok Sederhana | Fabriku',
        'meta_description' => 'Aplikasi pencatatan untuk usaha rumahan: produksi sederhana, stok bahan dan produk jadi, pesanan pelanggan, dan laporan penjualan tanpa ribet.',
        'eyebrow' => 'Produksi Rumahan',
        'headline' => 'Aplikasi pencatatan usaha rumahan yang tidak ribet.',
        'intro' => 'Untuk usaha yang diproduksi sendiri di rumah. Alur produksi dibuat ringkas: catat bahan yang dipakai dan hasil jadi dalam satu form, tanpa modul kontraktor/maklun.',
        'pains' => [
            ['title' => 'Catatan di buku dan chat', 'body' => 'Pesanan ada di WhatsApp, bahan di buku tulis, uang masuk di rekening. Rekap akhir bulan memakan waktu.'],
            ['title' => 'Tidak tahu untung sebenarnya', 'body' => 'Omzet terlihat besar, tapi biaya bahan tidak tercatat per produksi.'],
            ['title' => 'Aplikasi bisnis terlalu rumit', 'body' => 'Software pabrik punya terlalu banyak menu untuk usaha yang dikerjakan satu-dua orang.'],
        ],
        'features' => [
            ['title' => 'Produksi sederhana', 'body' => 'Satu form untuk mencatat bahan yang dipakai dan jumlah produk jadi yang masuk ke stok.'],
            ['title' => 'Stok bahan & produk jadi', 'body' => 'Stok bahan berkurang dan stok produk bertambah otomatis dari setiap pencatatan produksi.'],
            ['title' => 'Pesanan pelanggan', 'body' => 'Pesanan dari berbagai chat dicatat di satu daftar dengan status dan pembayarannya.'],
            ['title' => 'Data pelanggan', 'body' => 'Simpan kontak pelanggan langganan untuk pesanan berikutnya.'],
            ['title' => 'Website usaha', 'body' => 'Halaman katalog sederhana agar pelanggan bisa melihat produk dan mengirim pesanan.'],
            ['title' => 'Laporan penjualan', 'body' => 'Lihat penjualan per periode dan ekspor ke Excel bila perlu.'],
        ],
        'faqs' => [
            ['question' => 'Apa bedanya dengan kategori Makanan & Kue?', 'answer' => 'Produksi Rumahan memakai alur produksi ringkas dan menyembunyikan modul kontraktor. Cocok untuk usaha yang dikerjakan sendiri. Kategori Makanan & Kue memakai alur produksi lengkap.'],
            ['question' => 'Kapan usaha rumahan perlu sistem pencatatan?', 'answer' => 'Biasanya saat pesanan mulai sering tertukar, stok bahan sering habis mendadak, atau rekap bulanan memakan lebih dari satu hari. Panduannya ada di blog Fabriku.'],
            ['question' => 'Apakah bisa dipakai dari HP?', 'answer' => 'Bisa. Fabriku berbasis web dan tampilannya menyesuaikan layar HP.'],
        ],
        'blog_tags' => ['produksi-rumahan', 'kuliner-rumahan', 'excel'],
    ],

    'bengkel-jasa-servis' => [
        'category' => 'service',
        'meta_title' => 'Aplikasi Bengkel & Jasa Servis: Layanan, Teknisi, Sparepart | Fabriku',
        'meta_description' => 'Aplikasi untuk bengkel dan usaha jasa servis: katalog layanan, teknisi yang mengerjakan, pemakaian sparepart otomatis, pembayaran, dan laporan layanan.',
        'eyebrow' => 'Jasa & Layanan',
        'headline' => 'Aplikasi bengkel dan jasa servis untuk layanan, teknisi, dan sparepart.',
        'intro' => 'Catat layanan yang dikerjakan, siapa teknisinya, dan sparepart atau bahan habis pakai yang terpakai. Stok berkurang otomatis saat transaksi selesai.',
        'pains' => [
            ['title' => 'Sparepart habis tanpa ketahuan', 'body' => 'Oli, filter, dan bahan habis pakai berkurang setiap servis, tapi tidak pernah dicatat per transaksi.'],
            ['title' => 'Kinerja teknisi tidak terukur', 'body' => 'Tidak ada catatan siapa mengerjakan apa, sehingga bonus atau komisi dihitung dari ingatan.'],
            ['title' => 'Harga jasa tidak konsisten', 'body' => 'Harga layanan berbeda tergantung siapa yang melayani, karena tidak ada daftar harga baku.'],
        ],
        'features' => [
            ['title' => 'Katalog layanan', 'body' => 'Daftar layanan dengan harga baku, dipakai saat membuat transaksi maupun di website usaha.'],
            ['title' => 'Bahan habis pakai otomatis', 'body' => 'Petakan sparepart atau bahan ke layanan. Saat transaksi kasir cepat selesai, stoknya berkurang otomatis.'],
            ['title' => 'Teknisi per layanan', 'body' => 'Pilih staf yang mengerjakan setiap baris layanan untuk rekap kinerja.'],
            ['title' => 'Gabung jasa & produk', 'body' => 'Satu transaksi bisa berisi layanan dan penjualan sparepart sekaligus.'],
            ['title' => 'Permintaan dari website', 'body' => 'Pelanggan mengirim permintaan layanan dari website usaha, lalu staf menindaklanjutinya menjadi sales order.'],
            ['title' => 'Laporan layanan', 'body' => 'Laporan omzet per layanan dan per teknisi, siap diekspor ke Excel atau PDF.'],
        ],
        'faqs' => [
            ['question' => 'Apakah stok sparepart berkurang otomatis?', 'answer' => 'Ya, untuk transaksi melalui kasir cepat. Sparepart yang dipetakan ke layanan sebagai bahan habis pakai berkurang saat transaksi disimpan.'],
            ['question' => 'Apakah bisa untuk jasa selain bengkel?', 'answer' => 'Bisa. Kategori Jasa & Layanan cocok untuk servis elektronik, laundry, salon, atau jasa lain yang memakai daftar layanan dan bahan habis pakai.'],
            ['question' => 'Apakah bisa mencatat siapa yang mengerjakan?', 'answer' => 'Bisa. Setiap baris layanan punya kolom staf yang melayani, dan laporan layanan bisa dilihat per staf.'],
        ],
        'blog_tags' => ['jasa-servis'],
    ],
];
