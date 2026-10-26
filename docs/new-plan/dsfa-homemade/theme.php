<?php

// Tenant data for the existing draft-import lifecycle; no Satsetui generation or credits.
// Copy rule: buyer-facing text only. No prices, areas, claims, or owner notes until the owner supplies them.
$orderSteps = <<<'HTML'
<ol><li>Lihat varian, porsi, dan harga di <a href="/#menu">katalog menu</a>.</li><li>Tentukan jumlah dan tanggal kebutuhan Anda.</li><li>Kirim pesanan lewat kontak di halaman <a href="/halaman/cara-pesan">cara pesan</a>.</li></ol>
HTML;

$pages = [
    [
        'slug' => 'cheesecuit-bandung',
        'title' => 'Cheesecuit Bandung',
        'seo_title' => 'Cheesecuit Homemade Bandung | DSFA Homemade',
        'seo_description' => 'Pesan cheesecuit homemade di Bandung dari DSFA Homemade: dessert biskuit dan krim keju buatan rumah. Lihat menu dan cara pesannya.',
        'sort' => 1,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Cheesecuit homemade di Bandung</h1>
    <p class="dsfa-intro">Cheesecuit adalah dessert lapisan biskuit dan krim keju yang biasa disajikan dingin. Di DSFA Homemade, cheesecuit kami buat sendiri di dapur rumah di Bandung.</p>
    <section class="dsfa-page-section"><h2>Cheesecuit untuk penutup makan dan suguhan</h2><p>Cheesecuit pas sebagai penutup makan, teman minum teh sore, atau suguhan saat keluarga dan teman berkunjung. Pilihan rasa dan ukurannya tercantum di katalog menu.</p></section>
    <section class="dsfa-page-section"><h2>Cara pesan cheesecuit di Bandung</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang cheesecuit DSFA</h2>
        <h3>Apakah DSFA Homemade cabang toko cheesecuit lain?</h3><p>Bukan. DSFA Homemade adalah usaha rumahan sendiri di Bandung, dan cheesecuit adalah salah satu menu dessert kami.</p>
        <h3>Bisa diantar ke mana?</h3><p>Cheesecuit perlu tetap dingin di perjalanan. Wilayah pengantaran kami tulis di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</p>
        <h3>Ada menu lain?</h3><p>Ada. Kami juga menjual <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.</p>
    </section>
</article>
HTML,
    ],
    [
        'slug' => 'jual-takoyaki-bandung',
        'title' => 'Takoyaki Bandung',
        'seo_title' => 'Jual Takoyaki Bandung | DSFA Homemade',
        'seo_description' => 'Jual takoyaki homemade di Bandung. DSFA Homemade membuat takoyaki sendiri di dapur rumah. Lihat menu, cara pesan, dan area pengiriman.',
        'sort' => 2,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Jual takoyaki homemade di Bandung</h1>
    <p class="dsfa-intro">Takoyaki adalah bola adonan gurih khas Jepang yang dimasak di cetakan bulat, lalu disajikan dengan saus dan taburan. DSFA Homemade membuat takoyaki sendiri di dapur rumah kami di Bandung.</p>
    <section class="dsfa-page-section"><h2>Takoyaki rumahan untuk camilan</h2><p>Takoyaki paling enak dimakan hangat: untuk camilan sore, teman nonton di rumah, atau suguhan saat kumpul keluarga. Pilihan isian dan jumlah per porsi tercantum di katalog menu.</p></section>
    <section class="dsfa-page-section"><h2>Cara pesan takoyaki di Bandung</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang takoyaki DSFA</h2>
        <h3>Apakah takoyakinya buatan rumahan?</h3><p>Ya. Kami membuat adonan dan memasaknya sendiri di dapur DSFA Homemade di Bandung.</p>
        <h3>Bisa diantar ke mana?</h3><p>Wilayah pengantaran kami tulis di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</p>
        <h3>Ada menu lain?</h3><p>Ada. Pesan takoyaki bersama <a href="/halaman/jus-homemade-bandung">jus homemade</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, atau <a href="/halaman/cheesecuit-bandung">cheesecuit</a>.</p>
    </section>
</article>
HTML,
    ],
    [
        'slug' => 'puding-buah-bandung',
        'title' => 'Puding Buah Bandung',
        'seo_title' => 'Puding Buah Bandung | Pesan di DSFA Homemade',
        'seo_description' => 'Pesan puding buah homemade di Bandung dari DSFA Homemade. Puding lembut dengan potongan buah, dibuat di dapur rumah dan disajikan dingin.',
        'sort' => 3,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Puding buah homemade di Bandung</h1>
    <p class="dsfa-intro">Puding buah DSFA Homemade adalah puding lembut dengan potongan buah, kami buat di dapur rumah di Bandung dan disajikan dingin.</p>
    <section class="dsfa-page-section"><h2>Puding buah untuk keluarga dan tamu</h2><p>Puding buah cocok untuk hidangan penutup di rumah, camilan anak, atau suguhan saat ada tamu. Pilihan buah dan ukurannya tercantum di katalog menu.</p></section>
    <section class="dsfa-page-section"><h2>Cara pesan puding buah di Bandung</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang puding buah DSFA</h2>
        <h3>Apakah pudingnya buatan rumahan?</h3><p>Ya. Kami memasak dan mencetak puding buah sendiri di dapur DSFA Homemade di Bandung.</p>
        <h3>Bisa diantar ke mana?</h3><p>Puding perlu tetap dingin di perjalanan, jadi jarak ikut menentukan. Wilayahnya kami tulis di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</p>
        <h3>Ada menu lain?</h3><p>Ada. Kami juga menjual <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.</p>
    </section>
</article>
HTML,
    ],
    [
        'slug' => 'jus-homemade-bandung',
        'title' => 'Jus Homemade Bandung',
        'seo_title' => 'Jus Homemade Bandung | DSFA Homemade',
        'seo_description' => 'Jus buah homemade di Bandung dari DSFA Homemade. Pesan bersama takoyaki, cheesecuit, atau puding buah supaya camilan dan minuman datang sekaligus.',
        'sort' => 4,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Jus homemade di Bandung</h1>
    <p class="dsfa-intro">DSFA Homemade membuat jus buah sendiri di dapur rumah kami di Bandung, untuk diminum bersama camilan dan dessert kami.</p>
    <section class="dsfa-page-section"><h2>Jus untuk teman camilan</h2><p>Pesan jus bersama takoyaki, cheesecuit, atau puding buah supaya camilan dan minuman datang sekaligus. Pilihan buah dan ukurannya tercantum di katalog menu.</p></section>
    <section class="dsfa-page-section"><h2>Cara pesan jus di Bandung</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang jus DSFA</h2>
        <h3>Apakah jusnya dibuat sendiri?</h3><p>Ya. Kami membuat jus di dapur DSFA Homemade di Bandung.</p>
        <h3>Bisa diantar ke mana?</h3><p>Wilayah pengantaran kami tulis di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</p>
        <h3>Ada menu lain?</h3><p>Ada. Lihat <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, dan <a href="/halaman/puding-buah-bandung">puding buah</a>.</p>
    </section>
</article>
HTML,
    ],
    [
        'slug' => 'cara-pesan',
        'title' => 'Cara Pesan',
        'seo_title' => 'Cara Pesan DSFA Homemade Bandung',
        'seo_description' => 'Cara pesan cheesecuit, takoyaki, puding buah, dan jus homemade di DSFA Homemade Bandung, dan apa saja yang perlu disiapkan sebelum memesan.',
        'sort' => 5,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Cara pesan di DSFA Homemade</h1>
    <p class="dsfa-intro">Pesan cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung dalam tiga langkah.</p>
    <section class="dsfa-page-section"><h2>Langkah pemesanan</h2>{$orderSteps}</section>
    <section class="dsfa-page-section"><h2>Kontak pemesanan</h2><p>Nomor WhatsApp pemesanan akan kami cantumkan di halaman ini.</p></section>
    <section class="dsfa-page-section"><h2>Siapkan sebelum memesan</h2><ul><li>Menu dan jumlah yang ingin dipesan.</li><li>Tanggal dan jam kebutuhan.</li><li>Alamat tujuan di Bandung atau sekitarnya.</li><li>Pertanyaan soal bahan atau alergi, bila ada.</li></ul></section>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="/#pilihan-menu">Lihat menu</a><a class="dsfa-button" href="/halaman/area-pengiriman">Area pengiriman</a></div>
</article>
HTML,
    ],
    [
        'slug' => 'area-pengiriman',
        'title' => 'Area Pengiriman',
        'seo_title' => 'Area Pengiriman DSFA Homemade Bandung',
        'seo_description' => 'Area pengiriman DSFA Homemade di Bandung, termasuk info untuk pemesan dari Cimahi, Kabupaten Bandung, dan Bandung Barat.',
        'sort' => 6,
        'html' => <<<'HTML'
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Area pengiriman DSFA Homemade di Bandung</h1>
    <p class="dsfa-intro">DSFA Homemade berbasis di Bandung. Daftar kecamatan yang kami layani dan cara pengantarannya kami tulis di halaman ini.</p>
    <section class="dsfa-page-section"><h2>Pesan dari luar Kota Bandung</h2><p>Untuk alamat di Cimahi, Kabupaten Bandung, atau Bandung Barat, tanyakan dulu ketersediaan pengantaran saat memesan.</p></section>
    <section class="dsfa-page-section"><h2>Menu yang perlu tetap dingin</h2><p>Puding buah dan cheesecuit perlu tetap dingin di perjalanan, jadi jarak pengantaran ikut menentukan menu yang bisa dikirim.</p></section>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="/halaman/cara-pesan">Cara pesan</a><a class="dsfa-button" href="/#pilihan-menu">Lihat menu</a></div>
</article>
HTML,
    ],
];

return [
    'theme' => [
        'shell_html' => <<<'HTML'
<header data-fb-shell="header" class="dsfa-header">
    <div class="dsfa-wrap dsfa-header-inner">
        <a class="dsfa-brand" href="/"><img src="https://fabriku.id/storage/logos/dsfa-homemade.png" alt="" width="60" height="60"><span><strong>DSFA Homemade</strong><small>Camilan dan dessert rumahan, Bandung</small></span></a>
        <nav class="dsfa-nav"><a href="/#pilihan-menu">Menu</a><a href="/halaman/cara-pesan">Cara pesan</a><a href="/halaman/area-pengiriman">Area kirim</a><a href="/#kontak">Kontak</a></nav>
    </div>
</header>
<footer data-fb-shell="footer" class="dsfa-footer">
    <div class="dsfa-wrap dsfa-footer-grid">
        <div class="dsfa-footer-brand"><p class="dsfa-footer-name">DSFA Homemade</p><p>Cheesecuit, takoyaki, puding buah, dan jus homemade dari dapur rumah kami di Bandung.</p></div>
        <nav class="dsfa-footer-col"><p class="dsfa-footer-title">Menu</p><ul><li><a href="/halaman/cheesecuit-bandung">Cheesecuit Bandung</a></li><li><a href="/halaman/jual-takoyaki-bandung">Takoyaki Bandung</a></li><li><a href="/halaman/puding-buah-bandung">Puding buah Bandung</a></li><li><a href="/halaman/jus-homemade-bandung">Jus homemade Bandung</a></li></ul></nav>
        <nav class="dsfa-footer-col"><p class="dsfa-footer-title">Informasi</p><ul><li><a href="/halaman/cara-pesan">Cara pesan</a></li><li><a href="/halaman/area-pengiriman">Area pengiriman</a></li><li><a href="/#menu">Katalog dan harga</a></li><li><a href="/privasi">Privasi</a></li></ul></nav>
    </div>
    <div class="dsfa-wrap dsfa-footer-bottom"><p>&copy; DSFA Homemade, Bandung</p><nav><a href="/lapor">Laporkan situs</a><a href="https://fabriku.id" rel="noopener">Dibuat dengan Fabriku</a></nav></div>
</footer>
HTML,
        'satsetui_css' => file_get_contents(__DIR__.'/theme.css'),
        'satsetui_home_seo' => [
            'title' => 'DSFA Homemade Bandung | Cheesecuit, Takoyaki, Puding & Jus',
            'description' => 'DSFA Homemade menjual cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung. Lihat menu, cara pesan, dan area pengiriman.',
        ],
        'satsetui_pages' => $pages,
    ],
    'sections' => [
        [
            'id' => 'dsfa-hero', 'type' => 'hero', 'sort' => 10, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-wrap dsfa-section dsfa-hero" id="isi">
    <div class="dsfa-hero-copy"><p class="dsfa-kicker">Camilan dan dessert rumahan di Bandung</p><h1>Cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung</h1><p class="dsfa-intro">DSFA Homemade membuat camilan dan dessert di dapur rumah kami di Bandung. Pesan untuk teman santai di rumah, kumpul keluarga, atau suguhan tamu.</p><div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="/#menu">Lihat menu</a><a class="dsfa-button" href="/halaman/cara-pesan">Cara pesan</a></div></div>
    <div class="dsfa-hero-media"><figure data-fb-slot="featured-product"><svg class="dsfa-art" viewbox="50 60 300 225" xmlns="http://www.w3.org/2000/svg" width="400" height="300" fill="none"><rect width="400" height="300" fill="#f5efe0"/><rect x="60" y="205" width="280" height="22" rx="11" fill="#946d50"/><circle cx="150" cy="170" r="48" fill="#d9b777" stroke="#946d50" stroke-width="4"/><circle cx="250" cy="170" r="48" fill="#d9b777" stroke="#946d50" stroke-width="4"/><circle cx="200" cy="120" r="48" fill="#d9b777" stroke="#946d50" stroke-width="4"/><path d="M165 112q18-12 35 0t35 0" stroke="#424b34" stroke-width="5" stroke-linecap="round"/><path d="M118 162q16-10 32 0t32 0" stroke="#424b34" stroke-width="5" stroke-linecap="round"/><path d="M218 162q16-10 32 0t32 0" stroke="#424b34" stroke-width="5" stroke-linecap="round"/><circle cx="185" cy="98" r="4" fill="#747c60"/><circle cx="215" cy="136" r="4" fill="#747c60"/><circle cx="138" cy="186" r="4" fill="#747c60"/><circle cx="262" cy="190" r="4" fill="#747c60"/></svg></figure></div>
</section>
HTML,
        ],
        [
            'id' => 'dsfa-categories', 'type' => 'categories', 'sort' => 20, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-wrap dsfa-section dsfa-menu" id="pilihan-menu">
    <h2>Menu DSFA Homemade</h2><p class="dsfa-heading-copy">Empat menu rumahan yang bisa Anda pesan di Bandung.</p>
    <nav class="dsfa-menu-list">
        <a href="/halaman/jual-takoyaki-bandung"><svg class="dsfa-icon" viewbox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" stroke="#424b34" stroke-width="3"><circle cx="22" cy="42" r="12"/><circle cx="42" cy="42" r="12"/><circle cx="32" cy="24" r="12"/></svg><strong>Takoyaki</strong><span>Bola adonan gurih khas Jepang dengan saus dan taburan.</span></a>
        <a href="/halaman/cheesecuit-bandung"><svg class="dsfa-icon" viewbox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" stroke="#424b34" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10 26h44v24a4 4 0 0 1-4 4H14a4 4 0 0 1-4-4z"/><line x1="10" y1="36" x2="54" y2="36"/><line x1="10" y1="45" x2="54" y2="45"/><circle cx="20" cy="20" r="2"/><circle cx="32" cy="17" r="2"/><circle cx="44" cy="20" r="2"/></svg><strong>Cheesecuit</strong><span>Dessert biskuit dan krim keju, disajikan dingin.</span></a>
        <a href="/halaman/puding-buah-bandung"><svg class="dsfa-icon" viewbox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" stroke="#424b34" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M14 54L19 24h26l5 30z"/><line x1="17" y1="38" x2="47" y2="38"/><circle cx="32" cy="15" r="5"/></svg><strong>Puding buah</strong><span>Puding lembut dengan potongan buah, disajikan dingin.</span></a>
        <a href="/halaman/jus-homemade-bandung"><svg class="dsfa-icon" viewbox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" width="56" height="56" fill="none" stroke="#424b34" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M16 18h32l-4 38H20z"/><line x1="18" y1="30" x2="46" y2="30"/><line x1="38" y1="18" x2="44" y2="6"/></svg><strong>Jus homemade</strong><span>Jus buah buatan kami untuk teman camilan.</span></a>
    </nav>
</section>
HTML,
        ],
        [
            'id' => 'dsfa-products', 'type' => 'featured-products', 'sort' => 30, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-wrap dsfa-section" id="menu"><h2>Katalog dan harga</h2><p class="dsfa-heading-copy">Foto, varian, dan harga setiap menu tampil di sini.</p><div class="dsfa-catalog"><div data-fb-slot="products"></div></div></section>
HTML,
        ],
        [
            'id' => 'dsfa-steps', 'type' => 'steps', 'sort' => 40, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-wrap dsfa-section dsfa-steps"><h2>Pesan dalam 3 langkah</h2><ol class="dsfa-steps-list"><li><strong>Pilih menu</strong><span>Lihat menu dan harga di katalog.</span></li><li><strong>Tentukan jumlah dan tanggal</strong><span>Siapkan jumlah porsi, tanggal kebutuhan, dan alamat tujuan.</span></li><li><strong>Kirim pesanan</strong><span>Pesan lewat kontak di halaman <a href="/halaman/cara-pesan">cara pesan</a>.</span></li></ol></section>
HTML,
        ],
        [
            'id' => 'dsfa-story', 'type' => 'story', 'sort' => 50, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-wrap dsfa-section dsfa-story"><h2>Tentang DSFA Homemade</h2><p>DSFA Homemade adalah usaha camilan dan dessert rumahan di Bandung. Setiap menu kami buat sendiri di dapur rumah, dari adonan takoyaki sampai puding buah.</p></section>
HTML,
        ],
        [
            'id' => 'dsfa-contact', 'type' => 'contact', 'sort' => 60, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-contact dsfa-section" id="kontak"><div class="dsfa-wrap dsfa-contact-inner"><div><h2>Pesan camilan homemade di Bandung</h2><p>Langkah pemesanan dan kontak kami ada di halaman cara pesan.</p><div class="dsfa-actions"><a class="dsfa-button" href="/halaman/cara-pesan">Cara pesan</a></div></div><div><h2>Pengiriman di Bandung</h2><p>Cek wilayah pengantaran, termasuk untuk pemesan dari Cimahi dan Kabupaten Bandung.</p><div class="dsfa-actions"><a class="dsfa-button" href="/halaman/area-pengiriman">Area pengiriman</a></div></div></div></section>
HTML,
        ],
    ],
];
