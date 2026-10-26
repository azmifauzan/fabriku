<?php

// Tenant data for the existing draft-import lifecycle; no Satsetui generation or credits.
// Copy rule: buyer-facing text only. No prices, areas, claims, or owner notes until the owner supplies them.
$waNumbers = ['admin1' => '6285220150587', 'admin2' => '6281267822754'];
$waLink = fn (string $text, string $number = '6285220150587'): string => 'https://wa.me/'.$number.'?text='.rawurlencode($text);
$waGeneral = $waLink('Halo DSFA Homemade, saya mau pesan.');
$waAdmin1 = $waLink('Halo DSFA Homemade (admin 1), saya mau pesan.', $waNumbers['admin1']);
$waAdmin2 = $waLink('Halo DSFA Homemade (admin 2), saya mau pesan.', $waNumbers['admin2']);

$orderSteps = <<<'HTML'
<ol><li>Lihat varian, porsi, dan harga di <a href="/#menu">katalog menu</a>.</li><li>Tentukan jumlah, tanggal, dan alamat tujuan.</li><li>Kirim pesanan lewat WhatsApp admin kami.</li></ol>
HTML;

$deliveryAnswer = 'Kami mengantar ke Kota Bandung, Cimahi, dan sebagian Kabupaten Bandung. Waktu antar maksimal 1 jam dalam kondisi lalu lintas normal. Daftar kecamatan ada di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.';

$menuPage = fn (string $slug, string $title, string $seoTitle, string $seoDescription, int $sort, string $h1, string $intro, string $useHeading, string $useText, string $stepsHeading, string $waText, string $faqSubject, string $faqAnswer, string $related): array => [
    'slug' => $slug,
    'title' => $title,
    'seo_title' => $seoTitle,
    'seo_description' => $seoDescription,
    'sort' => $sort,
    'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>{$h1}</h1>
    <p class="dsfa-intro">{$intro}</p>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waLink($waText)}" rel="noopener" target="_blank">Pesan via WhatsApp</a><a class="dsfa-button" href="/#menu">Lihat harga di katalog</a></div>
    <section class="dsfa-page-section"><h2>{$useHeading}</h2><p>{$useText}</p></section>
    <section class="dsfa-page-section"><h2>{$stepsHeading}</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang {$faqSubject}</h2>
        {$faqAnswer}
        <h3>Bisa diantar ke mana?</h3><p>{$deliveryAnswer}</p>
        <h3>Ada menu lain?</h3><p>{$related}</p>
    </section>
</article>
HTML,
];

$pages = [
    $menuPage('cheesecuit-bandung', 'Cheesecuit Bandung', 'Cheesecuit Homemade Bandung | DSFA Homemade',
        'Pesan cheesecuit homemade di Bandung dari DSFA Homemade: dessert biskuit dan krim keju buatan rumah. Pesan via WhatsApp, diantar maks. 1 jam.', 1,
        'Cheesecuit homemade di Bandung',
        'Cheesecuit adalah dessert lapisan biskuit dan krim keju yang biasa disajikan dingin. Di DSFA Homemade, cheesecuit kami buat sendiri di dapur rumah di Bandung.',
        'Cheesecuit untuk penutup makan dan suguhan',
        'Cheesecuit pas sebagai penutup makan, teman minum teh sore, atau suguhan saat keluarga dan teman berkunjung. Pilihan rasa dan ukurannya tercantum di katalog menu.',
        'Cara pesan cheesecuit di Bandung', 'Halo DSFA Homemade, saya mau pesan cheesecuit.', 'cheesecuit DSFA',
        '<h3>Apakah DSFA Homemade cabang toko cheesecuit lain?</h3><p>Bukan. DSFA Homemade adalah usaha rumahan sendiri di Bandung, dan cheesecuit adalah salah satu menu dessert kami.</p>',
        'Ada. Kami juga menjual <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.'),
    $menuPage('jual-takoyaki-bandung', 'Takoyaki Bandung', 'Jual Takoyaki Bandung | DSFA Homemade',
        'Jual takoyaki homemade di Bandung. DSFA Homemade membuat takoyaki sendiri di dapur rumah. Pesan via WhatsApp, diantar maks. 1 jam.', 2,
        'Jual takoyaki homemade di Bandung',
        'Takoyaki adalah bola adonan gurih khas Jepang yang dimasak di cetakan bulat, lalu disajikan dengan saus dan taburan. DSFA Homemade membuat takoyaki sendiri di dapur rumah kami di Bandung.',
        'Takoyaki rumahan untuk camilan',
        'Takoyaki paling enak dimakan hangat: untuk camilan sore, teman nonton di rumah, atau suguhan saat kumpul keluarga. Pilihan isian dan jumlah per porsi tercantum di katalog menu.',
        'Cara pesan takoyaki di Bandung', 'Halo DSFA Homemade, saya mau pesan takoyaki.', 'takoyaki DSFA',
        '<h3>Apakah takoyakinya buatan rumahan?</h3><p>Ya. Kami membuat adonan dan memasaknya sendiri di dapur DSFA Homemade di Bandung.</p>',
        'Ada. Pesan takoyaki bersama <a href="/halaman/jus-homemade-bandung">jus homemade</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, atau <a href="/halaman/cheesecuit-bandung">cheesecuit</a>.'),
    $menuPage('puding-buah-bandung', 'Puding Buah Bandung', 'Puding Buah Bandung | Pesan di DSFA Homemade',
        'Pesan puding buah homemade di Bandung dari DSFA Homemade. Puding lembut dengan potongan buah, disajikan dingin. Pesan via WhatsApp.', 3,
        'Puding buah homemade di Bandung',
        'Puding buah DSFA Homemade adalah puding lembut dengan potongan buah, kami buat di dapur rumah di Bandung dan disajikan dingin.',
        'Puding buah untuk keluarga dan tamu',
        'Puding buah cocok untuk hidangan penutup di rumah, camilan anak, atau suguhan saat ada tamu. Pilihan buah dan ukurannya tercantum di katalog menu.',
        'Cara pesan puding buah di Bandung', 'Halo DSFA Homemade, saya mau pesan puding buah.', 'puding buah DSFA',
        '<h3>Apakah pudingnya buatan rumahan?</h3><p>Ya. Kami memasak dan mencetak puding buah sendiri di dapur DSFA Homemade di Bandung.</p>',
        'Ada. Kami juga menjual <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.'),
    $menuPage('jus-homemade-bandung', 'Jus Homemade Bandung', 'Jus Homemade Bandung | DSFA Homemade',
        'Jus buah homemade di Bandung dari DSFA Homemade. Pesan bersama takoyaki, cheesecuit, atau puding buah lewat WhatsApp, diantar maks. 1 jam.', 4,
        'Jus homemade di Bandung',
        'DSFA Homemade membuat jus buah sendiri di dapur rumah kami di Bandung, untuk diminum bersama camilan dan dessert kami.',
        'Jus untuk teman camilan',
        'Pesan jus bersama takoyaki, cheesecuit, atau puding buah supaya camilan dan minuman datang sekaligus. Pilihan buah dan ukurannya tercantum di katalog menu.',
        'Cara pesan jus di Bandung', 'Halo DSFA Homemade, saya mau pesan jus.', 'jus DSFA',
        '<h3>Apakah jusnya dibuat sendiri?</h3><p>Ya. Kami membuat jus di dapur DSFA Homemade di Bandung.</p>',
        'Ada. Lihat <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, dan <a href="/halaman/puding-buah-bandung">puding buah</a>.'),
    [
        'slug' => 'cara-pesan',
        'title' => 'Cara Pesan',
        'seo_title' => 'Cara Pesan via WhatsApp | DSFA Homemade Bandung',
        'seo_description' => 'Cara pesan cheesecuit, takoyaki, puding buah, dan jus homemade di DSFA Homemade Bandung lewat WhatsApp admin. Diantar maksimal 1 jam.',
        'sort' => 5,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Cara pesan di DSFA Homemade</h1>
    <p class="dsfa-intro">Pesan cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung lewat WhatsApp admin kami.</p>
    <section class="dsfa-page-section"><h2>Pesan lewat WhatsApp</h2><p>Hubungi salah satu admin kami:</p><div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waAdmin1}" rel="noopener" target="_blank">WhatsApp admin 1: 0852-2015-0587</a><a class="dsfa-button" href="{$waAdmin2}" rel="noopener" target="_blank">WhatsApp admin 2: 0812-6782-2754</a></div></section>
    <section class="dsfa-page-section"><h2>Langkah pemesanan</h2>{$orderSteps}</section>
    <section class="dsfa-page-section"><h2>Siapkan sebelum memesan</h2><ul><li>Menu dan jumlah yang ingin dipesan.</li><li>Tanggal dan jam kebutuhan.</li><li>Alamat tujuan di Bandung atau sekitarnya.</li><li>Pertanyaan soal bahan atau alergi, bila ada.</li></ul></section>
    <section class="dsfa-page-section"><h2>Waktu antar</h2><p>Pesanan diantar maksimal 1 jam dalam kondisi lalu lintas normal. Cek wilayah yang kami layani di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</p></section>
    <div class="dsfa-actions"><a class="dsfa-button" href="/#pilihan-menu">Lihat menu</a><a class="dsfa-button" href="/halaman/area-pengiriman">Area pengiriman</a></div>
</article>
HTML,
    ],
    [
        'slug' => 'area-pengiriman',
        'title' => 'Area Pengiriman',
        'seo_title' => 'Pengiriman Dessert & Takoyaki Bandung | DSFA Homemade',
        'seo_description' => 'DSFA Homemade mengantar cheesecuit, takoyaki, puding buah, dan jus ke Kota Bandung, Cimahi, dan sekitarnya. Antar maksimal 1 jam kondisi normal.',
        'sort' => 6,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Area pengiriman DSFA Homemade di Bandung</h1>
    <p class="dsfa-intro">Kami mengantar dari dapur DSFA Homemade di Kecamatan Bojongsoang, Kabupaten Bandung. Waktu antar maksimal 1 jam dalam kondisi lalu lintas normal.</p>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waLink('Halo DSFA Homemade, apakah bisa diantar ke alamat saya?')}" rel="noopener" target="_blank">Tanya pengantaran via WhatsApp</a></div>
    <section class="dsfa-page-section dsfa-faq"><h2>Wilayah yang kami layani</h2>
        <h3>Kota Bandung</h3><p>Seluruh kecamatan: Andir, Antapani, Arcamanik, Astanaanyar, Babakan Ciparay, Bandung Kidul, Bandung Kulon, Bandung Wetan, Batununggal, Bojongloa Kaler, Bojongloa Kidul, Buahbatu, Cibeunying Kaler, Cibeunying Kidul, Cibiru, Cicendo, Cidadap, Cinambo, Coblong, Gedebage, Kiaracondong, Lengkong, Mandalajati, Panyileukan, Rancasari, Regol, Sukajadi, Sukasari, Sumur Bandung, dan Ujung Berung.</p>
        <h3>Kota Cimahi</h3><p>Cimahi Selatan, Cimahi Tengah, dan Cimahi Utara.</p>
        <h3>Kabupaten Bandung</h3><p>Baleendah, Banjaran, Bojongsoang, Cangkuang, Cicalengka, Cikancung, Cileunyi, Cimenyan, Ciparay, Dayeuhkolot, Katapang, Majalaya, Margaasih, Margahayu, Nagreg, Pameungpeuk, Rancaekek, dan Solokanjeruk.</p>
        <h3>Kabupaten Sumedang</h3><p>Jatinangor dan Cimanggung.</p>
    </section>
    <section class="dsfa-page-section"><h2>Alamat di luar daftar</h2><p>Untuk wilayah lain, misalnya Soreang, Lembang, atau Ngamprah, tanyakan dulu ketersediaan pengantaran lewat WhatsApp sebelum memesan.</p></section>
    <section class="dsfa-page-section"><h2>Biaya antar</h2><p>Tanyakan biaya antar ke admin saat memesan. Sebutkan kecamatan tujuan Anda.</p></section>
    <div class="dsfa-actions"><a class="dsfa-button" href="/halaman/cara-pesan">Cara pesan</a><a class="dsfa-button" href="/#pilihan-menu">Lihat menu</a></div>
</article>
HTML,
    ],
];

return [
    'theme' => [
        'shell_html' => <<<HTML
<header data-fb-shell="header" class="dsfa-header">
    <div class="dsfa-wrap dsfa-header-inner">
        <a class="dsfa-brand" href="/"><img src="https://fabriku.id/storage/logos/dsfa-homemade.png" alt="" width="60" height="60"><span><strong>DSFA Homemade</strong><small>Camilan dan dessert rumahan, Bandung</small></span></a>
        <nav class="dsfa-nav"><a href="/#pilihan-menu">Menu</a><a href="/halaman/cara-pesan">Cara pesan</a><a href="/halaman/area-pengiriman">Area kirim</a><a href="/#kontak">Kontak</a></nav>
    </div>
</header>
<footer data-fb-shell="footer" class="dsfa-footer">
    <div class="dsfa-wrap dsfa-footer-grid">
        <div class="dsfa-footer-brand"><p class="dsfa-footer-name">DSFA Homemade</p><p>Cheesecuit, takoyaki, puding buah, dan jus homemade dari dapur rumah kami di Bandung.</p><p class="dsfa-footer-wa"><a href="{$waAdmin1}" rel="noopener" target="_blank">WhatsApp 0852-2015-0587</a><a href="{$waAdmin2}" rel="noopener" target="_blank">WhatsApp 0812-6782-2754</a></p></div>
        <nav class="dsfa-footer-col"><p class="dsfa-footer-title">Menu</p><ul><li><a href="/halaman/cheesecuit-bandung">Cheesecuit Bandung</a></li><li><a href="/halaman/jual-takoyaki-bandung">Takoyaki Bandung</a></li><li><a href="/halaman/puding-buah-bandung">Puding buah Bandung</a></li><li><a href="/halaman/jus-homemade-bandung">Jus homemade Bandung</a></li></ul></nav>
        <nav class="dsfa-footer-col"><p class="dsfa-footer-title">Informasi</p><ul><li><a href="/halaman/cara-pesan">Cara pesan</a></li><li><a href="/halaman/area-pengiriman">Area pengiriman</a></li><li><a href="/#menu">Katalog dan harga</a></li><li><a href="/privasi">Privasi</a></li></ul></nav>
    </div>
    <div class="dsfa-wrap dsfa-footer-bottom"><p>&copy; DSFA Homemade, Bandung</p><nav><a href="/lapor">Laporkan situs</a><a href="https://fabriku.id" rel="noopener">Dibuat dengan Fabriku</a></nav></div>
</footer>
HTML,
        'satsetui_css' => file_get_contents(__DIR__.'/theme.css'),
        'satsetui_home_seo' => [
            'title' => 'DSFA Homemade Bandung | Cheesecuit, Takoyaki, Puding & Jus',
            'description' => 'DSFA Homemade menjual cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung. Pesan via WhatsApp, diantar maks. 1 jam.',
        ],
        'satsetui_pages' => $pages,
    ],
    'sections' => [
        [
            'id' => 'dsfa-hero', 'type' => 'hero', 'sort' => 10, 'is_visible' => true,
            'html' => <<<HTML
<section class="dsfa-wrap dsfa-section dsfa-hero" id="isi">
    <div class="dsfa-hero-copy"><p class="dsfa-kicker">Camilan dan dessert rumahan di Bandung</p><h1>Cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung</h1><p class="dsfa-intro">DSFA Homemade membuat camilan dan dessert di dapur rumah kami di Bandung. Pesan lewat WhatsApp, kami antar ke Bandung dan sekitarnya, maksimal 1 jam dalam kondisi lalu lintas normal.</p><div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waGeneral}" rel="noopener" target="_blank">Pesan via WhatsApp</a><a class="dsfa-button" href="/#menu">Lihat menu</a></div></div>
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
<section class="dsfa-wrap dsfa-section dsfa-steps"><h2>Pesan dalam 3 langkah</h2><ol class="dsfa-steps-list"><li><strong>Pilih menu</strong><span>Lihat menu dan harga di katalog.</span></li><li><strong>Tentukan jumlah dan tanggal</strong><span>Siapkan jumlah porsi, tanggal kebutuhan, dan alamat tujuan.</span></li><li><strong>Kirim pesanan</strong><span>Pesan lewat <a href="/halaman/cara-pesan">WhatsApp admin kami</a>.</span></li></ol></section>
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
            'html' => <<<HTML
<section class="dsfa-contact dsfa-section" id="kontak"><div class="dsfa-wrap dsfa-contact-inner"><div><h2>Pesan lewat WhatsApp</h2><p>Hubungi salah satu admin kami untuk memesan cheesecuit, takoyaki, puding buah, atau jus homemade.</p><div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waAdmin1}" rel="noopener" target="_blank">Admin 1: 0852-2015-0587</a><a class="dsfa-button" href="{$waAdmin2}" rel="noopener" target="_blank">Admin 2: 0812-6782-2754</a></div></div><div><h2>Antar maksimal 1 jam</h2><p>Kami mengantar ke Kota Bandung, Cimahi, dan sebagian Kabupaten Bandung dalam kondisi lalu lintas normal.</p><div class="dsfa-actions"><a class="dsfa-button" href="/halaman/area-pengiriman">Area pengiriman</a></div></div></div></section>
HTML,
        ],
    ],
];
