<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Business Categories Configuration
    |--------------------------------------------------------------------------
    |
    | Define business categories yang didukung aplikasi. Setiap kategori
    | memiliki konfigurasi terminologi, product types, dan business rules.
    |
    | Untuk menambah kategori baru, tambahkan ke array 'categories' di bawah.
    |
    */

    'categories' => [
        'garment' => [
            'label' => 'Garment & Konveksi',
            'description' => 'Produksi pakaian jadi (mukena, daster, gamis, dll)',
            'icon' => '🧵',

            // Terminologi per kategori
            'terminology' => [
                'material' => 'Bahan Baku',
                'pattern' => 'Pattern/Pola',
                'preparation' => 'Preparation/Persiapan',
                'preparation_order' => 'Preparation Order',
                'production' => 'Jahit/Sewing',
                'production_order' => 'Sewing Order',
                'contractor' => 'Penjahit/Kontraktor',
            ],

            // Product types untuk kategori ini
            'product_types' => [
                'mukena' => 'Mukena',
                'daster' => 'Daster',
                'gamis' => 'Gamis',
                'jilbab' => 'Jilbab/Kerudung',
                'kemeja' => 'Kemeja',
                'celana' => 'Celana',
                'lainnya' => 'Lainnya',
            ],

            // Size options
            'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'all_size'],

            // Material types untuk kategori ini
            'material_types' => [
                'kain' => 'Kain',
                'benang' => 'Benang',
                'resleting' => 'Resleting',
                'kancing' => 'Kancing',
                'aksesoris' => 'Aksesoris',
                'lainnya' => 'Lainnya',
            ],

            // Default material attributes
            'material_attributes' => [
                'warna' => ['type' => 'text', 'label' => 'Warna'],
                'lebar_kain' => ['type' => 'text', 'label' => 'Lebar Kain (cm)'],
                'gramasi' => ['type' => 'text', 'label' => 'Gramasi (gsm)'],
                'batch_number' => ['type' => 'text', 'label' => 'Batch Number'],
            ],

            // Business rules
            'rules' => [
                'standard_waste_percentage' => [3, 10], // min, max
                'quality_grades' => ['Grade A', 'Grade B', 'Reject'],
                'track_batch_number' => true,
                'track_expired_date' => false,
                'require_storage_temp' => false,
            ],
        ],

        'food' => [
            'label' => 'Makanan & Kue',
            'description' => 'Produksi makanan, kue, dan bakery',
            'icon' => '🍰',

            'terminology' => [
                'material' => 'Bahan Mentah',
                'pattern' => 'Resep',
                'preparation' => 'Mixing/Persiapan',
                'preparation_order' => 'Prep Order',
                'production' => 'Baking/Cooking',
                'production_order' => 'Production Order',
                'contractor' => 'Dapur Sharing/Outsource',
            ],

            'product_types' => [
                'cake' => 'Cake',
                'brownies' => 'Brownies',
                'cookies' => 'Cookies/Kue Kering',
                'roti' => 'Roti',
                'pastry' => 'Pastry',
                'kue_basah' => 'Kue Basah',
                'lainnya' => 'Lainnya',
            ],

            'sizes' => ['Small', 'Medium', 'Large', '20cm', '24cm', '1 loyang', 'custom'],

            'material_types' => [
                'tepung' => 'Tepung',
                'gula' => 'Gula',
                'mentega' => 'Mentega/Margarin',
                'telur' => 'Telur',
                'susu' => 'Susu',
                'coklat' => 'Coklat',
                'topping' => 'Topping',
                'lainnya' => 'Lainnya',
            ],

            'material_attributes' => [
                'expired_date' => ['type' => 'date', 'label' => 'Tanggal Kadaluarsa', 'required' => true],
                'storage_temp' => ['type' => 'select', 'label' => 'Suhu Penyimpanan', 'options' => ['Room Temp', 'Chilled', 'Frozen']],
                'batch_number' => ['type' => 'text', 'label' => 'Batch Number'],
                'halal_certified' => ['type' => 'checkbox', 'label' => 'Sertifikat Halal'],
            ],

            'rules' => [
                'standard_waste_percentage' => [1, 5],
                'quality_grades' => ['Premium', 'Standar'],
                'track_batch_number' => true,
                'track_expired_date' => true, // CRITICAL untuk food!
                'require_storage_temp' => true,
                'shelf_life_alert_days' => 7, // Alert 7 hari sebelum expired
            ],
        ],

        'craft' => [
            'label' => 'Kerajinan & Craft',
            'description' => 'Produksi kerajinan tangan, souvenir, aksesoris',
            'icon' => '🎨',
            'enabled' => false, // Disable untuk MVP, aktifkan nanti

            'terminology' => [
                'material' => 'Bahan Baku',
                'pattern' => 'Desain/Pattern',
                'preparation' => 'Persiapan',
                'preparation_order' => 'Prep Order',
                'production' => 'Pembuatan',
                'production_order' => 'Production Order',
                'contractor' => 'Crafter/Pengrajin',
            ],

            'product_types' => [
                'souvenir' => 'Souvenir',
                'aksesoris' => 'Aksesoris',
                'dekorasi' => 'Dekorasi',
                'hampers' => 'Hampers',
                'lainnya' => 'Lainnya',
            ],

            'sizes' => ['Small', 'Medium', 'Large', 'custom'],

            'material_types' => [
                'kertas' => 'Kertas',
                'kayu' => 'Kayu',
                'kain' => 'Kain',
                'plastik' => 'Plastik',
                'logam' => 'Logam',
                'lainnya' => 'Lainnya',
            ],

            'material_attributes' => [
                'warna' => ['type' => 'text', 'label' => 'Warna'],
                'ukuran' => ['type' => 'text', 'label' => 'Ukuran'],
                'batch_number' => ['type' => 'text', 'label' => 'Batch Number'],
            ],

            'rules' => [
                'standard_waste_percentage' => [5, 15],
                'quality_grades' => ['Premium', 'Standar', 'Ekonomi'],
                'track_batch_number' => true,
                'track_expired_date' => false,
                'require_storage_temp' => false,
            ],
        ],

        'cosmetic' => [
            'label' => 'Kosmetik & Skincare',
            'description' => 'Produksi kosmetik, skincare, produk kecantikan',
            'icon' => '💄',
            'enabled' => false, // Disable untuk MVP

            'terminology' => [
                'material' => 'Bahan Baku',
                'pattern' => 'Formula/Resep',
                'preparation' => 'Mixing/Formulasi',
                'preparation_order' => 'Mixing Order',
                'production' => 'Produksi',
                'production_order' => 'Production Order',
                'contractor' => 'Maklon/Contract Manufacturer',
            ],

            'product_types' => [
                'skincare' => 'Skincare',
                'makeup' => 'Makeup',
                'haircare' => 'Haircare',
                'bodycare' => 'Body Care',
                'herbal' => 'Produk Herbal',
                'lainnya' => 'Lainnya',
            ],

            'sizes' => ['10ml', '30ml', '50ml', '100ml', '250ml', 'custom'],

            'material_types' => [
                'base' => 'Base/Dasar',
                'active' => 'Active Ingredient',
                'preservative' => 'Pengawet',
                'fragrance' => 'Pewangi',
                'packaging' => 'Kemasan',
                'lainnya' => 'Lainnya',
            ],

            'material_attributes' => [
                'expired_date' => ['type' => 'date', 'label' => 'Tanggal Kadaluarsa', 'required' => true],
                'batch_number' => ['type' => 'text', 'label' => 'Batch Number', 'required' => true],
                'bpom_number' => ['type' => 'text', 'label' => 'Nomor BPOM'],
                'storage_temp' => ['type' => 'select', 'label' => 'Suhu Penyimpanan', 'options' => ['Room Temp', 'Cool', 'Refrigerated']],
            ],

            'rules' => [
                'standard_waste_percentage' => [2, 8],
                'quality_grades' => ['Premium', 'Regular'],
                'track_batch_number' => true, // REQUIRED untuk cosmetic!
                'track_expired_date' => true,
                'require_storage_temp' => true,
                'shelf_life_alert_days' => 30,
                'require_bpom' => true, // Khusus Indonesia
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Category
    |--------------------------------------------------------------------------
    |
    | Kategori default untuk tenant baru jika tidak memilih kategori.
    |
    */
    'default_category' => 'garment',

    /*
    |--------------------------------------------------------------------------
    | Enabled Categories
    |--------------------------------------------------------------------------
    |
    | Daftar kategori yang aktif untuk MVP. Kategori lain bisa diaktifkan
    | dengan mengubah 'enabled' => true di konfigurasi kategori di atas.
    |
    */
    'enabled_categories' => ['garment', 'food'],
];
