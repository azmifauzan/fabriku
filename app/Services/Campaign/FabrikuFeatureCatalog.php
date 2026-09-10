<?php

namespace App\Services\Campaign;

use App\Models\CampaignLog;
use App\Models\Tenant;

class FabrikuFeatureCatalog
{
    /**
     * Get all features supported by Fabriku grouped by category.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function all(): array
    {
        return [
            'garment' => [
                [
                    'key' => 'pattern_bom',
                    'name' => 'Pattern & Bill of Materials (BOM) Pakaian',
                    'category' => 'garment',
                    'category_label' => 'Garment & Konveksi',
                    'cta_path' => '/patterns',
                    'description' => 'Standarisasi pola pakaian, rincian kebutuhan bahan baku (kain, benang, kancing, resleting) per potong baju untuk cegah boncos dan over-budget.',
                    'pain_point' => 'Sering kekurangan atau kelebihan kain saat potong baju massal? Estimasi manual sering membuat modal membengkak.',
                    'benefit' => 'Hitung otomatis kebutuhan bahan per lusin/pcs, estimasi HPP akurat, dan kontrol sisa kain produksi.',
                    'steps' => [
                        'Buka menu "Master Data" > "Pattern"',
                        'Buat pola baru dan masukkan rumus kebutuhan kain & aksesoris',
                        'Gunakan pola ini saat membuat Preparation & Production Order',
                    ],
                    'pro_tip' => 'Tambahkan estimasi waste percentage kain (3-10%) pada pola agar kalkulasi pembelian bahan selalu aman.',
                ],
                [
                    'key' => 'prep_cutting_waste',
                    'name' => 'Preparation Order & Kontrol Waste Pemotongan Kain',
                    'category' => 'garment',
                    'category_label' => 'Garment & Konveksi',
                    'cta_path' => '/preparation-orders',
                    'description' => 'Alur kerja potong kain (cutting) terstruktur sebelum kain dikirim ke penjahit borongan atau CMT.',
                    'pain_point' => 'Kain sering hilang atau sisa perca terbuang sia-sia tanpa tercatat di buku kerja penjahit.',
                    'benefit' => 'Pantau output potongan kain per lembar, catat gramasi sisa potongan, dan lacak tanggung jawab staff cutting.',
                    'steps' => [
                        'Pilih menu "Preparation" di sidebar',
                        'Buat SPK Potong Kain baru berdasarkan Pola yang sudah terdaftar',
                        'Catat hasil jadi lembaran pola yang siap masuk ke lini jahit',
                    ],
                    'pro_tip' => 'Kain perca hasil cutting bisa dicatat sebagai bahan limbah atau dimanfaatkan untuk aksesoris tambahan.',
                ],
                [
                    'key' => 'production_contractors',
                    'name' => 'Manajemen Penjahit / Kontraktor CMT & Hitung Ongkos Otomatis',
                    'category' => 'garment',
                    'category_label' => 'Garment & Konveksi',
                    'cta_path' => '/contractors',
                    'description' => 'Monitoring distribusi pengerjaan jahit ke penjahit internal maupun makloon/kontraktor luar beserta ongkos jahit per pcs.',
                    'pain_point' => 'Pusing menghitung upah borongan penjahit secara manual di buku nota yang rentan hilang?',
                    'benefit' => 'Perhitungan ongkos jahit transparan dan otomatis, pantau barang di tangan penjahit, dan kontrol kualitas (QC).',
                    'steps' => [
                        'Daftarkan penjahit atau vendor CMT di menu "Kontraktor"',
                        'Tentukan tarif ongkos jahit per jenis model pakaian',
                        'Terbitkan SPK Jahit (Production Order) dan pantau status progresnya',
                    ],
                    'pro_tip' => 'Cetak form serah terima bahan langsung dari Fabriku sebagai bukti resmi saat makloon mengambil bahan potongan.',
                ],
                [
                    'key' => 'inventory_fabric_variants',
                    'name' => 'Manajemen Stok Bahan Kain & Varian Ukuran (XS - XXXL)',
                    'category' => 'garment',
                    'category_label' => 'Garment & Konveksi',
                    'cta_path' => '/inventory/items',
                    'description' => 'Pencatatan multi-lokasi gudang kain, warna, lebar kain, gramasi (gsm), serta stok pakaian jadi per ukuran.',
                    'pain_point' => 'Kain di gudang menumpuk tapi tidak tahu pasti roll mana yang siap dipakai untuk model baju terbaru?',
                    'benefit' => 'Identifikasi kain cepat dengan data warna & lebar, alert stok kain menipis, dan ringkasan stok pakaian jadi per varian ukuran.',
                    'steps' => [
                        'Buka menu "Inventory" > "Items"',
                        'Input stok kain dengan detail lebar, warna, dan lokasi rak gudang',
                        'Cek status stok siap pakai kapan saja dari HP atau laptop',
                    ],
                    'pro_tip' => 'Manfaatkan fitur Visualisasi Rak untuk menemukan lokasi roll kain dalam hitungan detik.',
                ],
            ],

            'food' => [
                [
                    'key' => 'recipe_bom_hpp',
                    'name' => 'Standarisasi Resep & Kalkulasi HPP Makanan Otomatis',
                    'category' => 'food',
                    'category_label' => 'Makanan & Bakery',
                    'cta_path' => '/patterns',
                    'description' => 'Rumus resep baku (BOM) tepung, telur, gula, butter per batch atau per loyang agar rasa konsisten dan HPP terpantau pasti.',
                    'pain_point' => 'Harga telur atau butter sering naik turun, bingung menghitung berapa margin keuntungan per box kue?',
                    'benefit' => 'HPP terupdate otomatis saat harga beli bahan berubah, rasa produk konsisten siapapun koki atau bakernya.',
                    'steps' => [
                        'Buka menu "Master Data" > "Resep"',
                        'Masukkan takaran gramasi bahan baku per loyang atau per porsi',
                        'Lihat estimasi HPP dan tentukan harga jual dengan margin sehat',
                    ],
                    'pro_tip' => 'Update harga beli bahan baku secara rutin di menu Bahan Baku agar kalkulasi HPP resep selalu akurat.',
                ],
                [
                    'key' => 'shelf_life_expiry_alert',
                    'name' => 'Alert Tanggal Kadaluarsa (Expired Date) & Shelf-Life Bahan',
                    'category' => 'food',
                    'category_label' => 'Makanan & Bakery',
                    'cta_path' => '/materials',
                    'description' => 'Pencatatan tanggal kadaluarsa bahan mentah (susu, ragi, cream cheese) dengan notifikasi dini sebelum basi.',
                    'pain_point' => 'Bahan makanan basi tersembunyi di sudut kulkas/chiller dan terpaksa dibuang menjadi kerugian fatal.',
                    'benefit' => 'Terapkan metode FEFO (First Expired First Out), cegah kerugian bahan terbuang, dan jaga higienitas standar makanan.',
                    'steps' => [
                        'Input tanggal expired saat menerima pasokan bahan mentah baru',
                        'Cek dashboard alert stok yang mendekati tanggal kadaluarsa',
                        'Gunakan bahan yang tanggal expired-nya paling dekat terlebih dahulu',
                    ],
                    'pro_tip' => 'Atur alert 7 hari sebelum expired agar tim dapur sempat memprioritaskan pemakaian bahan tersebut.',
                ],
                [
                    'key' => 'storage_temp_batch',
                    'name' => 'Manajemen Suhu Penyimpanan (Room Temp, Chilled, Frozen)',
                    'category' => 'food',
                    'category_label' => 'Makanan & Bakery',
                    'cta_path' => '/inventory/locations',
                    'description' => 'Kelola lokasi penyimpanan bahan makanan terpisah antara suhu ruang, lemari pendingin (chilled), dan freezer.',
                    'pain_point' => 'Bahan yang seharusnya beku salah taruh di suhu ruang sehingga rusak sebelum sempat diolah.',
                    'benefit' => 'Klasifikasi penyimpanan rapi, staff dapur tahu persis tempat mengambil bahan mentah dengan benar.',
                    'steps' => [
                        'Buat lokasi inventori khusus (Dry Storage, Chiller 1, Deep Freezer)',
                        'Kelompokkan bahan berdasarkan label suhu penyimpanannya',
                        'Lakukan transfer antar ruang simpan saat bahan dipindahkan',
                    ],
                    'pro_tip' => 'Lakukan stock opname rutin per area suhu untuk memastikan pendingin bekerja optimal dan stok cocok.',
                ],
                [
                    'key' => 'prep_baking_order',
                    'name' => 'Jadwal Mixing & Baking Order (Antrean Dapur Teratur)',
                    'category' => 'food',
                    'category_label' => 'Makanan & Bakery',
                    'cta_path' => '/production-orders',
                    'description' => 'SPK produksi harian dapur untuk membagi tugas mixing adonan dan pemanggangan oven tepat waktu.',
                    'pain_point' => 'Pesanan kue menumpuk di akhir pekan tapi dapur bingung mana adonan yang harus disiapkan duluan.',
                    'benefit' => 'Pesanan selesai tepat waktu, kapasitas oven termaksimalkan, dan kepuasan pelanggan meningkat.',
                    'steps' => [
                        'Buat Order Produksi harian berdasarkan daftar pesanan pelanggan',
                        'Tugaskan tim persiapan (mixing) dan tim memasak (baking)',
                        'Ubah status order saat kue sudah matang dan siap dikemas',
                    ],
                    'pro_tip' => 'Kombinasikan pesanan kue dengan jenis loyang serupa dalam satu batch baking untuk menghemat pemakaian listrik/gas.',
                ],
            ],

            'craft' => [
                [
                    'key' => 'craft_bom_accessories',
                    'name' => 'Desain & BOM Produk Kerajinan (Souvenir, Hampers, Aksesoris)',
                    'category' => 'craft',
                    'category_label' => 'Kerajinan & Craft',
                    'cta_path' => '/patterns',
                    'description' => 'Pencatatan rincian pernak-pernik, lem, pita, kemasan box, dan bahan dekorasi per paket souvenir.',
                    'pain_point' => 'Pernak-pernik kecil sering tercecer dan biaya kemasan lupa dihitung ke dalam harga jual souvenir.',
                    'benefit' => 'Seluruh komponen kecil terhitung presisi, HPP jelas, dan margin keuntungan proyek souvenir aman.',
                    'steps' => [
                        'Buka menu "Master Data" > "Desain"',
                        'Rinci semua komponen bahan baku dan aksesoris kemasan',
                        'Tentukan HPP dan buat penawaran harga terbaik untuk klien',
                    ],
                    'pro_tip' => 'Simpan foto referensi desain produk di Fabriku agar pengerjaan oleh tim selalu sesuai contoh awal.',
                ],
                [
                    'key' => 'crafter_management',
                    'name' => 'Manajemen Pengrajin & Borongan Souvenir Eksternal',
                    'category' => 'craft',
                    'category_label' => 'Kerajinan & Craft',
                    'cta_path' => '/contractors',
                    'description' => 'Monitoring distribusi pengerjaan souvenir pernikahan atau hampers ke mitra pengrajin rumahan.',
                    'pain_point' => 'Sulit melacak siapa pengrajin yang sedang memegang bahan baku berharga tinggi dan kapan deadline setorannya.',
                    'benefit' => 'Lacak posisi bahan baku di luar workshop, kontrol deadline setoran, dan rekap ongkos borongan rapi.',
                    'steps' => [
                        'Daftarkan mitra pengrajin di menu "Pengrajin / Kontraktor"',
                        'Keluarkan SPK pembuatan souvenir dengan tanggal batas selesai',
                        'Verifikasi hasil jadi saat pengrajin menyetorkan barang',
                    ],
                    'pro_tip' => 'Terapkan sistem grading kualitas (Grade A, Reject) saat penerimaan barang dari pengrajin.',
                ],
            ],

            'retail' => [
                [
                    'key' => 'quick_checkout_pos',
                    'name' => 'Kasir Kilat (Quick Checkout POS) & Barcode Scanner',
                    'category' => 'retail',
                    'category_label' => 'Toko / Retail',
                    'cta_path' => '/sales-orders/quick-checkout',
                    'description' => 'Layanan kasir super cepat dengan scanner kamera HP atau barcode scanner fisik, hitung kembalian, dan cetak struk belanja.',
                    'pain_point' => 'Antrean kasir panjang karena input manual satu per satu, kasir rawan salah input harga barang.',
                    'benefit' => 'Transaksi hitungan detik, stok barang otomatis terpotong seketika, dan cetak struk thermal profesional.',
                    'steps' => [
                        'Buka menu "Penjualan" > "Quick Checkout"',
                        'Scan barcode produk atau ketik nama barang',
                        'Pilih metode pembayaran (Tunai/Transfer) dan cetak struk untuk pelanggan',
                    ],
                    'pro_tip' => 'Tekan tombol shortcut atau scan langsung menggunakan kamera laptop/smartphone Anda tanpa perlu alat scanner mahal.',
                ],
                [
                    'key' => 'stock_opname_visual',
                    'name' => 'Stock Opname Cepat & Visualisasi Lokasi Rak Toko',
                    'category' => 'retail',
                    'category_label' => 'Toko / Retail',
                    'cta_path' => '/inventory/visualization',
                    'description' => 'Petakan posisi barang di rak etalase maupun gudang belakang, serta sesuaikan selisih fisik barang dengan cepat.',
                    'pain_point' => 'Staff sering bingung mencari barang yang ditanyakan pembeli padahal di data tercatat masih ada stok.',
                    'benefit' => 'Pelayanan pelanggan cepat memuaskan, deteksi kebocoran stok atau barang hilang lebih awal.',
                    'steps' => [
                        'Buka "Inventory" > "Visualisasi"',
                        'Lihat persebaran barang di rak toko dan gudang penyimpanan',
                        'Lakukan penyesuaian stok jika ditemukan selisih fisik',
                    ],
                    'pro_tip' => 'Jadwalkan stock opname parsial mingguan untuk kategori barang terlaris (fast moving item).',
                ],
                [
                    'key' => 'sales_recap_profit',
                    'name' => 'Rekap Penjualan Harian & Laba Kotor Otomatis',
                    'category' => 'retail',
                    'category_label' => 'Toko / Retail',
                    'cta_path' => '/reports/sales',
                    'description' => 'Laporan omzet toko, barang paling laris (best seller), dan total keuntungan kotor tanpa perlu pembukuan manual malam hari.',
                    'pain_point' => 'Toko ramai tapi saat tutup kasir di malam hari sering pusing menghitung sisa uang dan laba bersih yang didapat.',
                    'benefit' => 'Rekap omzet otomatis per kasir atau per hari, pantau tren barang laku, dan ekspor laporan ke Excel/PDF dalam 1 klik.',
                    'steps' => [
                        'Pilih menu "Reports" > "Penjualan"',
                        'Filter tanggal hari ini atau periode minggu ini',
                        'Lihat grafik performa omzet dan daftar produk terlaris Anda',
                    ],
                    'pro_tip' => 'Bandingkan penjualan antar hari untuk menentukan promo atau diskon khusus di hari yang biasanya sepi pengunjung.',
                ],
            ],

            'service' => [
                [
                    'key' => 'service_order_tracking',
                    'name' => 'Tracking Order Layanan / Jasa & Progres Pekerjaan',
                    'category' => 'service',
                    'category_label' => 'Layanan & Jasa',
                    'cta_path' => '/services',
                    'description' => 'Pencatatan nomor tiket perbaikan/servis, catatan keluhan pelanggan, staff penanggung jawab, dan status progres pengerjaan.',
                    'pain_point' => 'Pelanggan sering menelpon menanyakan status servis dan staff saling lempar tanggung jawab.',
                    'benefit' => 'Riwayat pengerjaan transparan, pelanggan puas dengan update yang jelas, dan performa teknisi terukur.',
                    'steps' => [
                        'Buka menu "Layanan" di sidebar',
                        'Catat order layanan baru beserta estimasi biaya dan tanggal selesai',
                        'Update status menjadi Selesai saat pekerjaan siap diserahkan',
                    ],
                    'pro_tip' => 'Kombinasikan pesanan layanan dengan pemakaian suku cadang / bahan habis pakai dari inventori.',
                ],
            ],

            'general' => [
                [
                    'key' => 'telegram_bot_alert',
                    'name' => 'Notifikasi Otomatis via Bot Telegram Langsung ke HP Anda',
                    'category' => 'general',
                    'category_label' => 'Semua Bisnis',
                    'cta_path' => '/settings',
                    'description' => 'Terima notifikasi instan saat ada penjualan baru, stok bahan menipis di gudang, atau reminder harian di aplikasi Telegram Anda.',
                    'pain_point' => 'Sedang berada di luar bengkel/toko tapi ingin tetap tahu apakah kasir mencatat transaksi dan bahan masih cukup.',
                    'benefit' => 'Pemilik bisnis bisa memantau jalannya operasional bisnis secara realtime dari mana saja tanpa perlu buka laptop.',
                    'steps' => [
                        'Buka menu "Settings" di pojok kiri bawah',
                        'Klik tab Telegram dan ikuti petunjuk sambungkan bot',
                        'Dapatkan notifikasi otomatis setiap ada aktivitas penting',
                    ],
                    'pro_tip' => 'Hubungkan juga akun Telegram manajer atau staff kunci agar koordinasi tim semakin responsif.',
                ],
                [
                    'key' => 'multi_staff_permissions',
                    'name' => 'Multi-Staff & Pembagian Hak Akses (Role & Izin)',
                    'category' => 'general',
                    'category_label' => 'Semua Bisnis',
                    'cta_path' => '/staff',
                    'description' => 'Berikan akun khusus untuk Kasir, Staff Gudang, dan Operator Produksi dengan akses yang dibatasi sesuai tugasnya.',
                    'pain_point' => 'Khawatir data harga modal atau laba rahasia bisnis diintip oleh sembarang karyawan toko?',
                    'benefit' => 'Data sensitif terlindungi, tiap karyawan fokus pada tugasnya, dan audit log mencatat siapa yang melakukan perubahan.',
                    'steps' => [
                        'Buka menu "Master Data" > "Staff"',
                        'Tambahkan anggota tim baru dan pilih peran (Kasir, Gudang, atau Manager)',
                        'Staff dapat langsung login menggunakan email mereka masing-masing',
                    ],
                    'pro_tip' => 'Jangan pernah membagikan password admin utama Anda; selalu gunakan akun staff terpisah untuk keamanan maksimal.',
                ],
                [
                    'key' => 'export_pdf_excel',
                    'name' => 'Laporan Bisnis Siap Print (Format PDF & Excel)',
                    'category' => 'general',
                    'category_label' => 'Semua Bisnis',
                    'cta_path' => '/reports/sales',
                    'description' => 'Cetak laporan stok material, rekap penjualan, dan kinerja produksi dalam format dokumen resmi berlogo bisnis Anda.',
                    'pain_point' => 'Butuh laporan rapi untuk evaluasi mitra bisnis, investor, atau bank tapi memakan waktu seharian untuk merapikan data.',
                    'benefit' => 'Laporan profesional siap cetak dengan logo dan identitas bisnis Anda hanya dalam satu kali klik.',
                    'steps' => [
                        'Buka menu "Reports" dan pilih laporan yang ingin dilihat',
                        'Tentukan rentang tanggal yang diinginkan',
                        'Klik tombol "Export PDF" atau "Export Excel"',
                    ],
                    'pro_tip' => 'Pastikan Anda telah mengunggah logo bisnis di Pengaturan agar tercetak otomatis di kop surat laporan.',
                ],
            ],
        ];
    }

    /**
     * Get features suitable for a specific tenant business category.
     * Combines category-specific features with general features.
     *
     * @return list<array<string, mixed>>
     */
    public static function getFeaturesForCategory(string $category): array
    {
        $all = self::all();
        $categoryFeatures = $all[$category] ?? $all['garment'];
        $generalFeatures = $all['general'] ?? [];

        return array_merge($categoryFeatures, $generalFeatures);
    }

    /**
     * Pick an optimal feature to highlight for a given tenant,
     * taking into account past campaign history to rotate topics.
     *
     * @return array<string, mixed>
     */
    public static function pickFeatureForTenant(Tenant $tenant): array
    {
        $category = $tenant->business_category ?? 'garment';
        $availableFeatures = self::getFeaturesForCategory($category);

        // Check features already sent to this tenant in the last 90 days
        $recentlySentKeys = CampaignLog::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', now()->subDays(90))
            ->pluck('feature_key')
            ->filter()
            ->all();

        // Find features that haven't been sent recently
        $unsentFeatures = array_filter($availableFeatures, function ($feature) use ($recentlySentKeys) {
            return ! in_array($feature['key'], $recentlySentKeys, true);
        });

        if (! empty($unsentFeatures)) {
            // Pick the first unsent feature (consistent rotation)
            return array_values($unsentFeatures)[0];
        }

        // If all features have been sent, pick randomly or the oldest sent
        $shuffled = $availableFeatures;
        shuffle($shuffled);

        return $shuffled[0];
    }
}
