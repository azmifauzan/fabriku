<?php

// Tenant data for the existing draft-import lifecycle; no Satsetui generation or credits.
// Copy rule: buyer-facing text only. No prices, areas, claims, or owner notes until the owner supplies them.
$waNumbers = ['admin1' => '6285220150587', 'admin2' => '6281267822754'];
$waLink = fn (string $text, string $number = '6285220150587'): string => 'https://wa.me/'.$number.'?text='.rawurlencode($text);
$waGeneral = $waLink('Halo DSFA Homemade, saya mau pesan.');
$waAdmin1 = $waLink('Halo DSFA Homemade (admin 1), saya mau pesan.', $waNumbers['admin1']);
$waAdmin2 = $waLink('Halo DSFA Homemade (admin 2), saya mau pesan.', $waNumbers['admin2']);

$orderSteps = <<<'HTML'
<ol><li>Lihat varian, porsi, dan harga di <a href="/produk">katalog produk</a> atau <a href="/#menu">beranda</a>.</li><li>Tentukan jumlah kemasan, tanggal serta jam kebutuhan, dan kecamatan tujuan.</li><li>Kirim pesanan lewat WhatsApp admin kami untuk konfirmasi stok dan ongkos kirim.</li></ol>
HTML;

$deliveryAnswer = 'Kami mengantar dari dapur rumah di Kecamatan Bojongsoang ke Kota Bandung, Kota Cimahi, sebagian Kabupaten Bandung, serta Jatinangor dan Cimanggung. Waktu antar maksimal 1 jam dalam kondisi lalu lintas normal. Daftar lengkap kecamatan ada di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.';

$catalog = fn (string $slugs): string => $slugs === '' ? '' : '<section class="dsfa-page-section"><h2>Pilihan di katalog</h2><div data-fb-slot="products" data-fb-products="'.$slugs.'"></div></section>';

$menuPage = fn (string $slug, string $title, string $seoTitle, string $seoDescription, int $sort, string $h1, string $intro, string $useHeading, string $useHtml, string $stepsHeading, string $waText, string $faqSubject, string $faqAnswer, string $related, string $slugs = '', string $extraSections = ''): array => [
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
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waLink($waText)}" rel="noopener" target="_blank">Pesan via WhatsApp</a><a class="dsfa-button" href="/produk">Lihat katalog lengkap</a></div>
    <section class="dsfa-page-section"><h2>{$useHeading}</h2>{$useHtml}</section>
    {$extraSections}
    {$catalog($slugs)}
    <section class="dsfa-page-section"><h2>{$stepsHeading}</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang {$faqSubject}</h2>
        {$faqAnswer}
        <h3>Bisa diantar ke mana?</h3><p>{$deliveryAnswer}</p>
        <h3>Ada menu lain?</h3><p>{$related}</p>
    </section>
</article>
HTML,
];

$cheesecuitExtra = <<<'HTML'
<section class="dsfa-page-section"><h2>Daftar menu, ukuran, dan harga cheesecuit</h2>
    <div class="dsfa-table-wrap"><table class="dsfa-table"><thead><tr><th>Varian</th><th>Ukuran</th><th>Harga</th><th>Cocok untuk</th></tr></thead><tbody>
        <tr><td>Cheesecuit Kecil Oreo</td><td>Cup 150 ml</td><td>Rp 7.000</td><td>Porsi sendiri, isian snack box</td></tr>
        <tr><td>Cheesecuit Kecil Coklat</td><td>Cup 150 ml</td><td>Rp 7.000</td><td>Porsi sendiri, camilan sore</td></tr>
        <tr><td>Cheesecuit Kecil Matcha</td><td>Cup 150 ml</td><td>Rp 7.000</td><td>Pencinta rasa teh hijau</td></tr>
        <tr><td>Cheesecuit Besar Oreo</td><td>Wadah 250 ml</td><td>Rp 10.000</td><td>Porsi lebih penuh atau dimakan berdua</td></tr>
        <tr><td>Cheesecuit Besar Coklat</td><td>Wadah 250 ml</td><td>Rp 10.000</td><td>Porsi lebih penuh atau suguhan keluarga</td></tr>
    </tbody></table></div>
</section>
<section class="dsfa-page-section"><h2>Daya tahan dan cara menyimpan cheesecuit</h2>
    <p>Cheesecuit homemade paling enak disajikan dingin dan memiliki tiga batas waktu simpan sesuai tempat penyimpanannya:</p>
    <ul><li>Di suhu ruang bertahan hingga 12 jam.</li><li>Di dalam kulkas bertahan hingga 48 jam.</li><li>Di dalam freezer bertahan hingga 7 hari dalam wadah tertutup rapat.</li></ul>
    <p>Jika disimpan di freezer, pindahkan ke kulkas biasa terlebih dahulu sebelum disantap agar tekstur krim kejunya kembali lembut.</p>
</section>
HTML;

$takoyakiExtra = <<<'HTML'
<section class="dsfa-page-section"><h2>Pilihan porsi dan harga takoyaki</h2>
    <div class="dsfa-table-wrap"><table class="dsfa-table"><thead><tr><th>Pilihan Porsi</th><th>Isi</th><th>Harga</th><th>Cocok untuk</th></tr></thead><tbody>
        <tr><td>Takoyaki Porsi Kecil</td><td>4 buah</td><td>Rp 5.000</td><td>Camilan sendiri atau teman minum jus</td></tr>
        <tr><td>Takoyaki Porsi Besar (isi 8)</td><td>8 buah</td><td>Rp 10.000</td><td>Dimakan berdua atau camilan sore lebih puas</td></tr>
        <tr><td>Takoyaki Porsi Besar (isi 12)</td><td>12 buah</td><td>Rp 15.000</td><td>Suguhan keluarga, rapat, atau kumpul bersama</td></tr>
    </tbody></table></div>
    <p>Pesan takoyaki hangat bersama minuman dingin atau dessert dari katalog yang sama agar seluruh pesanan tiba dalam satu kali pengantaran.</p>
</section>
HTML;

$pudingExtra = <<<'HTML'
<section class="dsfa-page-section"><h2>Perbandingan puding cup harian dan puding besar</h2>
    <div class="dsfa-table-wrap"><table class="dsfa-table"><thead><tr><th>Varian Puding</th><th>Harga</th><th>Sistem Pesan</th><th>Keterangan</th></tr></thead><tbody>
        <tr><td>Puding Buah Coklat (cup 150 ml)</td><td>Rp 7.000</td><td>Siap antar maks. 1 jam</td><td>Porsi personal disajikan dingin</td></tr>
        <tr><td>Puding Buah Coklat Besar</td><td>Rp 200.000</td><td>Open order 3 hari</td><td>Hiasan stroberi dan anggur, kemasan berpita</td></tr>
    </tbody></table></div>
    <p>Untuk kebutuhan kue perayaan dengan hiasan topper, lihat halaman <a href="/halaman/puding-ulang-tahun-bandung">puding ulang tahun Bandung</a>.</p>
</section>
HTML;

$jusExtra = <<<'HTML'
<section class="dsfa-page-section"><h2>Varian rasa dan harga jus homemade</h2>
    <div class="dsfa-table-wrap"><table class="dsfa-table"><thead><tr><th>Varian Jus</th><th>Kemasan</th><th>Harga</th></tr></thead><tbody>
        <tr><td>Jus Strawberry</td><td>Botol</td><td>Rp 10.000</td></tr>
        <tr><td>Jus Blackberry</td><td>Botol</td><td>Rp 10.000</td></tr>
        <tr><td>Jus Mix Berry</td><td>Botol</td><td>Rp 10.000</td></tr>
    </tbody></table></div>
    <p>Untuk ukuran botol dalam ml, ketersediaan harian, tingkat kemanisan, serta petunjuk penyimpanan, tanyakan kepada admin lewat WhatsApp sebelum memesan.</p>
</section>
HTML;

$pages = [
    $menuPage('cheesecuit-bandung', 'Cheesecuit Bandung', 'Cheesecuit Bandung Homemade: Harga & Menu | DSFA Homemade',
        'Daftar menu dan harga cheesecuit homemade Bandung di DSFA Homemade: cup 150 ml Rp 7.000 dan 250 ml Rp 10.000. Pesan via WhatsApp, antar maks. 1 jam.', 1,
        'Cheesecuit homemade di Bandung: menu, rasa, dan harga',
        'Cheesecuit adalah dessert dingin berupa lapisan biskuit dan krim keju. DSFA Homemade membuat cheesecuit sendiri di dapur rumah kami di Bojongsoang, Kabupaten Bandung, mulai Rp 7.000 untuk cup 150 ml dan Rp 10.000 untuk wadah 250 ml.',
        'Beda cheesecuit kecil 150 ml dan besar 250 ml',
        '<p>Cheesecuit kecil dikemas dalam cup 150 ml seharga Rp 7.000 dengan tiga pilihan rasa: Oreo, Coklat, dan Matcha. Ukuran ini praktis untuk camilan sendiri, arisan, atau isian snack box karena setiap tamu mendapat satu cup sendiri.</p><p>Cheesecuit besar dikemas dalam wadah 250 ml seharga Rp 10.000 dengan pilihan rasa Oreo dan Coklat untuk porsi lebih penuh atau dinikmati berdua. Sebutkan jumlah cup atau wadah saat memesan; untuk informasi bahan atau alergen, tanyakan kepada admin sebelum membeli.</p>',
        'Cara pesan cheesecuit di Bandung', 'Halo DSFA Homemade, saya mau pesan cheesecuit.', 'cheesecuit DSFA',
        '<h3>Apakah DSFA Homemade cabang toko cheesecuit lain?</h3><p>Bukan. DSFA Homemade adalah usaha rumahan mandiri di Bojongsoang, Bandung, dan cheesecuit adalah salah satu menu dessert kami.</p><h3>Berapa lama cheesecuit tahan saat disimpan?</h3><p>Di suhu ruang cheesecuit tahan hingga 12 jam, di dalam kulkas tahan hingga 48 jam, dan di freezer tahan hingga 7 hari dalam wadah tertutup.</p>',
        'Ada. Kami juga menjual <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.',
        'cheesecuit-besar-coklat,cheesecuit-besar-oreo,cheesecuit-kecil-coklat,cheesecuit-kecil-matcha,cheesecuit-kecil-oreo',
        $cheesecuitExtra),
    $menuPage('jual-takoyaki-bandung', 'Takoyaki Bandung', 'Jual Takoyaki Bandung Homemade Mulai Rp 5.000 | DSFA',
        'Pesan takoyaki homemade di Bandung dari DSFA Homemade: porsi isi 4 Rp 5.000, isi 8 Rp 10.000, dan isi 12 Rp 15.000. Pesan via WhatsApp, antar maks. 1 jam.', 2,
        'Jual takoyaki homemade di Bandung',
        'Takoyaki adalah bola adonan gurih khas Jepang yang dimasak di cetakan bulat, lalu disajikan hangat dengan saus dan taburan. DSFA Homemade membuat takoyaki sendiri di dapur rumah kami di Bojongsoang, Bandung, mulai Rp 5.000 per porsi.',
        'Takoyaki rumahan untuk camilan harian dan kumpul keluarga',
        '<p>Takoyaki paling enak disantap selagi hangat untuk camilan sore, teman nonton di rumah, atau suguhan saat kumpul bersama keluarga. Anda bisa memilih porsi isi 4 untuk sendiri, isi 8 untuk berdua, atau isi 12 untuk dimakan ramai-ramai.</p><p>Foto, harga, dan tombol pemesanan setiap porsi ada di bagian katalog di bawah. Untuk pertanyaan soal pilihan isian, bahan, alergen, dan ongkos kirim ke kecamatan tujuan, tanyakan kepada admin lewat WhatsApp sebelum memesan.</p>',
        'Cara pesan takoyaki di Bandung', 'Halo DSFA Homemade, saya mau pesan takoyaki.', 'takoyaki DSFA',
        '<h3>Apakah takoyakinya buatan rumahan?</h3><p>Ya. Kami membuat adonan dan memasaknya sendiri di dapur DSFA Homemade di Bojongsoang, Bandung.</p><h3>Porsi mana yang cocok untuk keluarga atau acara?</h3><p>Porsi isi 12 seharga Rp 15.000 paling pas untuk dinikmati bersama keluarga, sedangkan porsi isi 4 dan isi 8 cocok untuk camilan personal atau berdua.</p>',
        'Ada. Pesan takoyaki bersama <a href="/halaman/jus-homemade-bandung">jus homemade</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, atau <a href="/halaman/cheesecuit-bandung">cheesecuit</a> dalam satu pengiriman.',
        'takoyaki-kemasan-kecil-isi-4,takoyaki-kemasan-besar-isi-8,takoyaki-kemasan-besar-isi-12',
        $takoyakiExtra),
    $menuPage('puding-buah-bandung', 'Puding Buah Bandung', 'Puding Buah Bandung Homemade Mulai Rp 7.000 | DSFA',
        'Pesan puding buah homemade di Bandung dari DSFA Homemade: cup 150 ml Rp 7.000 siap antar dan loyang besar berpita Rp 200.000 open order 3 hari via WhatsApp.', 3,
        'Puding buah homemade di Bandung',
        'Puding buah DSFA Homemade adalah puding lembut dengan potongan buah yang dibuat di dapur rumah kami di Bojongsoang, Bandung, dan disajikan dingin. Tersedia pilihan cup 150 ml seharga Rp 7.000 untuk pesanan harian serta ukuran besar berpita seharga Rp 200.000 untuk hantaran dan acara.',
        'Puding buah untuk keluarga, hantaran, dan tamu',
        '<p>Pilih Puding Buah Coklat dengan kemasan cup berkapasitas 150 ml (bukan ukuran berat isi) jika Anda ingin camilan dingin siap antar maksimal 1 jam. Untuk hantaran atau acara keluarga, pilih Puding Buah Coklat Besar dengan hiasan stroberi dan anggur serta kemasan berpita.</p><p>Versi besar dibuat setelah pesanan masuk melalui open order dengan estimasi pengerjaan 3 hari, terpisah dari waktu pengantaran. Tanyakan ukuran loyang, jumlah porsi, bahan, alergen, dan cara penyimpanan kepada admin sebelum memesan.</p>',
        'Cara pesan puding buah di Bandung', 'Halo DSFA Homemade, saya mau pesan puding buah.', 'puding buah DSFA',
        '<h3>Apakah pudingnya buatan rumahan?</h3><p>Ya. Kami memasak dan mencetak puding buah sendiri di dapur DSFA Homemade di Bandung.</p><h3>Apakah Puding Buah Coklat Besar bisa dipesan mendadak?</h3><p>Tidak. Puding Buah Coklat Besar dibuat lewat sistem open order dengan estimasi pengerjaan 3 hari, jadi pesan minimal 3 hari sebelum tanggal acara.</p>',
        'Ada. Kami juga menjual <a href="/halaman/puding-ulang-tahun-bandung">puding ulang tahun</a>, <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.',
        'puding-buah-coklat,puding-buah-coklat-besar',
        $pudingExtra),
    $menuPage('jus-homemade-bandung', 'Jus Homemade Bandung', 'Jus Homemade Bandung: Strawberry & Berry | DSFA Homemade',
        'Pesan jus buah homemade Bandung dari DSFA Homemade: varian Strawberry, Blackberry, dan Mix Berry Rp 10.000 per botol. Kirim maks. 1 jam lewat WhatsApp.', 4,
        'Jus homemade di Bandung',
        'DSFA Homemade membuat jus buah sendiri di dapur rumah kami di Bojongsoang, Bandung, dalam kemasan botol seharga Rp 10.000. Tersedia tiga varian buah beri: Strawberry, Blackberry, dan Mix Berry yang bisa dipesan terpisah maupun bersama menu camilan lain.',
        'Jus buah segar untuk teman camilan dan dessert',
        '<p>Memesan jus bersama takoyaki hangat, cheesecuit, atau puding buah memudahkan Anda menerima makanan dan minuman sekaligus dalam satu kali pengantaran dari Bojongsoang. Setiap varian dikemas dalam botol tertutup sehingga praktis diminum di rumah maupun di kantor.</p><p>Pilih rasa yang Anda sukai pada katalog di bawah ini, lalu sebutkan jumlah botol dan kecamatan tujuan saat menghubungi admin WhatsApp.</p>',
        'Cara pesan jus di Bandung', 'Halo DSFA Homemade, saya mau pesan jus.', 'jus DSFA',
        '<h3>Apakah jusnya dibuat sendiri?</h3><p>Ya. Kami membuat jus di dapur DSFA Homemade di Bojongsoang, Bandung.</p><h3>Bisa pesan beberapa rasa sekaligus dalam satu pengiriman?</h3><p>Bisa. Anda bebas menggabungkan Jus Strawberry, Jus Blackberry, Jus Mix Berry, dan menu makanan lain dalam satu pesanan.</p>',
        'Ada. Lihat <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, dan <a href="/halaman/puding-buah-bandung">puding buah</a>.',
        'jus-strawberry,jus-blackberry,jus-mix-berry',
        $jusExtra),
    [
        'slug' => 'cara-pesan',
        'title' => 'Cara Pesan',
        'seo_title' => 'Cara Pesan via WhatsApp | DSFA Homemade Bandung',
        'seo_description' => 'Panduan cara pesan cheesecuit, takoyaki, puding buah, dan jus homemade di DSFA Homemade Bandung via WhatsApp. Menu harian antar maks. 1 jam, puding besar H-3.',
        'sort' => 5,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Cara pesan di DSFA Homemade</h1>
    <p class="dsfa-intro">Pesan cheesecuit, takoyaki, puding buah, puding ulang tahun, dan jus homemade di Bandung lewat keranjang situs atau langsung melalui WhatsApp admin kami.</p>
    <section class="dsfa-page-section"><h2>Pesan lewat WhatsApp</h2><p>Hubungi salah satu admin kami untuk mengecek ketersediaan stok harian, jadwal pembuatan open order, serta ongkos kirim sesuai kecamatan tujuan:</p><div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waAdmin1}" rel="noopener" target="_blank">WhatsApp admin 1: 0852-2015-0587</a><a class="dsfa-button" href="{$waAdmin2}" rel="noopener" target="_blank">WhatsApp admin 2: 0812-6782-2754</a></div></section>
    <section class="dsfa-page-section"><h2>Langkah pemesanan</h2>{$orderSteps}</section>
    <section class="dsfa-page-section"><h2>Format pesan yang memudahkan konfirmasi</h2><p>Salin dan lengkapi rincian berikut saat mengirim pesan ke WhatsApp admin:</p><ul><li>Nama pemesan dan nomor yang bisa dihubungi.</li><li>Daftar menu, ukuran, dan jumlah (misalnya Cheesecuit Kecil Oreo 150 ml 3 cup, Takoyaki isi 8 1 porsi).</li><li>Tanggal dan jam kebutuhan pesanan.</li><li>Alamat lengkap beserta kecamatan tujuan di Bandung atau sekitarnya.</li><li>Catatan tambahan soal bahan atau alergi bila ada.</li></ul></section>
    <section class="dsfa-page-section"><h2>Beda menu siap antar dan open order</h2><p>Menu harian seperti cheesecuit, takoyaki, puding buah cup 150 ml, dan jus homemade diantar maksimal 1 jam dalam kondisi lalu lintas normal setelah pesanan dikonfirmasi. Untuk Puding Buah Coklat Besar dan Puding Ulang Tahun, pemesanan memakai sistem open order dengan estimasi pengerjaan 3 hari sehingga sebaiknya dipesan minimal 3 hari sebelum tanggal acara.</p><p>Cek daftar kecamatan yang kami layani di halaman <a href="/halaman/area-pengiriman">area pengiriman</a> atau buka <a href="/produk">katalog semua produk</a>.</p></section>
    <div class="dsfa-actions"><a class="dsfa-button" href="/produk">Lihat katalog produk</a><a class="dsfa-button" href="/halaman/area-pengiriman">Area pengiriman</a></div>
</article>
HTML,
    ],
    [
        'slug' => 'area-pengiriman',
        'title' => 'Area Pengiriman',
        'seo_title' => 'Area Pengiriman Dessert & Takoyaki Bandung | DSFA Homemade',
        'seo_description' => 'DSFA Homemade mengantar dari Bojongsoang ke Kota Bandung, Cimahi, sebagian Kab. Bandung, dan Jatinangor. Estimasi antar maks. 1 jam kondisi lalu lintas normal.',
        'sort' => 6,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Area pengiriman DSFA Homemade di Bandung</h1>
    <p class="dsfa-intro">Kami mengantar dari dapur DSFA Homemade di Kecamatan Bojongsoang, Kabupaten Bandung. Waktu antar maksimal 1 jam dalam kondisi lalu lintas normal untuk menu harian yang sudah dikonfirmasi.</p>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waLink('Halo DSFA Homemade, apakah bisa diantar ke alamat saya?')}" rel="noopener" target="_blank">Tanya pengantaran via WhatsApp</a></div>
    <section class="dsfa-page-section dsfa-faq"><h2>Wilayah yang kami layani</h2>
        <h3>Kota Bandung</h3><p>Seluruh kecamatan: Andir, Antapani, Arcamanik, Astanaanyar, Babakan Ciparay, Bandung Kidul, Bandung Kulon, Bandung Wetan, Batununggal, Bojongloa Kaler, Bojongloa Kidul, Buahbatu, Cibeunying Kaler, Cibeunying Kidul, Cibiru, Cicendo, Cidadap, Cinambo, Coblong, Gedebage, Kiaracondong, Lengkong, Mandalajati, Panyileukan, Rancasari, Regol, Sukajadi, Sukasari, Sumur Bandung, dan Ujung Berung.</p>
        <h3>Kota Cimahi</h3><p>Cimahi Selatan, Cimahi Tengah, dan Cimahi Utara.</p>
        <h3>Kabupaten Bandung</h3><p>Baleendah, Banjaran, Bojongsoang, Cangkuang, Cicalengka, Cikancung, Cileunyi, Cimenyan, Ciparay, Dayeuhkolot, Katapang, Majalaya, Margaasih, Margahayu, Nagreg, Pameungpeuk, Rancaekek, dan Solokanjeruk.</p>
        <h3>Kabupaten Sumedang</h3><p>Jatinangor dan Cimanggung.</p>
    </section>
    <section class="dsfa-page-section"><h2>Catatan waktu antar dan produk open order</h2><p>Estimasi antar maksimal 1 jam berlaku sejak pesanan selesai disiapkan dan berangkat dari dapur Bojongsoang dalam kondisi lalu lintas normal. Untuk menu open order seperti Puding Ulang Tahun dan Puding Buah Coklat Besar, estimasi pengerjaan 3 hari dihitung terpisah dari waktu pengantaran. Menu dingin seperti cheesecuit tahan hingga 12 jam di suhu ruang sehingga aman selama perjalanan dalam kota.</p></section>
    <section class="dsfa-page-section"><h2>Dekat dapur kami</h2><p>Pemesan di sekitar Bojongsoang bisa membaca halaman <a href="/halaman/dessert-takoyaki-bojongsoang">takoyaki dan dessert di Bojongsoang</a>.</p></section>
    <section class="dsfa-page-section"><h2>Alamat di luar daftar</h2><p>Untuk wilayah lain, misalnya Soreang, Lembang, atau Ngamprah, tanyakan dulu ketersediaan pengantaran lewat WhatsApp sebelum memesan.</p></section>
    <section class="dsfa-page-section"><h2>Biaya antar</h2><p>Ongkos kirim menyesuaikan jarak dari Bojongsoang ke kecamatan tujuan Anda. Sebutkan kecamatan tujuan saat menghubungi admin agar biaya antar dikonfirmasi sebelum pembayaran.</p></section>
    <div class="dsfa-actions"><a class="dsfa-button" href="/halaman/cara-pesan">Cara pesan</a><a class="dsfa-button" href="/produk">Lihat katalog produk</a></div>
</article>
HTML,
    ],
    [
        'slug' => 'dessert-takoyaki-bojongsoang',
        'title' => 'Takoyaki dan Dessert Bojongsoang',
        'seo_title' => 'Takoyaki & Dessert Homemade Bojongsoang | DSFA Homemade',
        'seo_description' => 'DSFA Homemade di Bojongsoang, Kabupaten Bandung menjual takoyaki, cheesecuit, puding buah, dan jus homemade. Pesan via WhatsApp, diantar maks. 1 jam.',
        'sort' => 7,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Takoyaki dan dessert homemade di Bojongsoang</h1>
    <p class="dsfa-intro">Dapur DSFA Homemade ada di Kecamatan Bojongsoang, Kabupaten Bandung. Dari sini kami membuat takoyaki, cheesecuit, puding buah, dan jus homemade, lalu mengantarnya ke rumah Anda.</p>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waLink('Halo DSFA Homemade, saya di Bojongsoang dan mau pesan.')}" rel="noopener" target="_blank">Pesan via WhatsApp</a><a class="dsfa-button" href="/produk">Lihat katalog lengkap</a></div>
    <section class="dsfa-page-section"><h2>Menu dari dapur Bojongsoang</h2><ul><li><a href="/halaman/jual-takoyaki-bandung">Takoyaki</a>: bola adonan gurih khas Jepang dengan saus dan taburan.</li><li><a href="/halaman/cheesecuit-bandung">Cheesecuit</a>: dessert biskuit dan krim keju dalam cup 150 ml dan wadah 250 ml, disajikan dingin.</li><li><a href="/halaman/puding-buah-bandung">Puding buah</a>: puding lembut dengan potongan buah.</li><li><a href="/halaman/jus-homemade-bandung">Jus homemade</a>: jus buah buatan kami untuk teman camilan.</li></ul></section>
    <section class="dsfa-page-section"><h2>Diantar ke Bojongsoang dan sekitarnya</h2><p>Pesanan ke Bojongsoang dan kecamatan terdekat kami antar maksimal 1 jam dalam kondisi lalu lintas normal. Kecamatan terdekat dari dapur kami antara lain Dayeuhkolot, Baleendah, Rancaekek, dan Cileunyi di Kabupaten Bandung, serta Buahbatu, Gedebage, dan Rancasari di Kota Bandung. Daftar lengkap ada di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</p></section>
    <section class="dsfa-page-section"><h2>Cara pesan dari Bojongsoang</h2>{$orderSteps}</section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan dari pemesan di Bojongsoang</h2>
        <h3>Apakah DSFA Homemade punya toko untuk dikunjungi?</h3><p>DSFA Homemade adalah usaha rumahan. Pesanan diterima lewat WhatsApp dan diantar ke alamat Anda.</p>
        <h3>Berapa lama pesanan sampai?</h3><p>Waktu antar maksimal 1 jam dalam kondisi lalu lintas normal. Untuk tanggal tertentu, sebutkan tanggal dan jam kebutuhan Anda saat memesan.</p>
        <h3>Berapa biaya antarnya?</h3><p>Tanyakan biaya antar ke admin saat memesan. Sebutkan kecamatan tujuan Anda.</p>
    </section>
</article>
HTML,
    ],
    [
        'slug' => 'puding-ulang-tahun-bandung',
        'title' => 'Puding Ulang Tahun Bandung',
        'seo_title' => 'Puding Ulang Tahun Bandung | Pesan di DSFA Homemade',
        'seo_description' => 'Pesan puding ulang tahun homemade di Bandung dari DSFA Homemade. Open order, estimasi pengerjaan 3 hari. Pesan via WhatsApp.',
        'sort' => 8,
        'html' => <<<HTML
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Puding ulang tahun di Bandung</h1>
    <p class="dsfa-intro">DSFA Homemade membuat puding ulang tahun di dapur rumah kami di Bandung. Puding dibuat setelah pesanan masuk (open order) dengan estimasi pengerjaan 3 hari.</p>
    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waLink('Halo DSFA Homemade, saya mau pesan puding ulang tahun.')}" rel="noopener" target="_blank">Pesan via WhatsApp</a><a class="dsfa-button" href="/produk">Lihat katalog lengkap</a></div>
    <section class="dsfa-page-section"><h2>Pilih puding untuk perayaan</h2><p>Puding Ulang Tahun memakai hiasan topper. Pilihan lainnya adalah Puding Buah Coklat Besar dengan hiasan stroberi dan anggur serta kemasan berpita. Kedua produk dibuat melalui open order dengan estimasi pengerjaan 3 hari. Bandingkan foto dan harga di katalog berikut, lalu tanyakan ukuran, jumlah porsi, bahan, alergen, dan pilihan hiasan kepada admin sebelum memesan.</p></section>
    {$catalog('puding-ulang-tahun,puding-buah-coklat-besar')}
    <section class="dsfa-page-section"><h2>Cara pesan puding ulang tahun di Bandung</h2><ol><li>Pilih puding di katalog di atas atau tanyakan lewat WhatsApp.</li><li>Tentukan tanggal acara dan alamat tujuan. Pesan minimal 3 hari sebelum tanggal yang Anda butuhkan.</li><li>Admin kami mengonfirmasi pesanan dan pembayaran lewat WhatsApp.</li></ol></section>
    <section class="dsfa-page-section"><h2>Jadwal pembuatan dan pengiriman</h2><p>Estimasi pengerjaan 3 hari adalah waktu membuat pesanan. Waktu antar maksimal 1 jam dalam kondisi lalu lintas normal dihitung terpisah. Sampaikan tanggal dan jam acara, jumlah puding, serta kecamatan tujuan saat menghubungi admin. Periksa <a href="/halaman/area-pengiriman">area pengiriman</a> dan <a href="/halaman/cara-pesan">cara pesan DSFA Homemade</a> sebelum mengonfirmasi pesanan.</p></section>
    <section class="dsfa-page-section dsfa-faq"><h2>Pertanyaan tentang puding ulang tahun DSFA</h2>
        <h3>Kenapa harus pesan dulu?</h3><p>Puding ulang tahun kami dibuat setelah pesanan masuk (open order), bukan stok siap ambil. Estimasi pengerjaannya 3 hari.</p>
        <h3>Bisa diantar ke mana?</h3><p>{$deliveryAnswer}</p>
        <h3>Ada puding lain?</h3><p>Ada. Lihat <a href="/halaman/puding-buah-bandung">puding buah Bandung</a>, atau menu lain seperti <a href="/halaman/cheesecuit-bandung">cheesecuit</a> dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>.</p>
    </section>
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
        <nav class="dsfa-nav"><a href="/produk">Katalog</a><a href="/halaman/cara-pesan">Cara pesan</a><a href="/halaman/area-pengiriman">Area kirim</a><a href="/#kontak">Kontak</a></nav>
    </div>
</header>
<footer data-fb-shell="footer" class="dsfa-footer">
    <div class="dsfa-wrap dsfa-footer-grid">
        <div class="dsfa-footer-brand"><p class="dsfa-footer-name">DSFA Homemade</p><p>Cheesecuit, takoyaki, puding buah, dan jus homemade dari dapur rumah kami di Bandung.</p><p class="dsfa-footer-wa"><a href="{$waAdmin1}" rel="noopener" target="_blank">WhatsApp 0852-2015-0587</a><a href="{$waAdmin2}" rel="noopener" target="_blank">WhatsApp 0812-6782-2754</a></p>
            <nav class="dsfa-footer-social" aria-label="Media sosial DSFA Homemade">
                <a href="https://www.instagram.com/dsfa.homemade/" target="_blank" rel="me noopener noreferrer"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"></circle></svg><span>Instagram</span></a>
                <a href="https://www.threads.com/@dsfa.homemade" target="_blank" rel="me noopener noreferrer"><svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false"><path d="M18.263 11.097c-.03-3.486-1.92-5.586-5.111-5.586-2.13 0-3.922.963-4.863 2.499l2.062 1.438c.535-.843 1.272-1.543 2.628-1.543 1.528 0 2.318.85 2.544 2.431a15 15 0 0 0-2.236-.173c-4.125 0-6.068 1.867-6.068 4.336s1.943 3.99 4.804 3.99c3.139 0 5.013-2.115 5.781-4.735.798.361 1.348 1.204 1.348 2.47 0 3.387-3.907 5.232-7.22 5.232-4.885 0-8.077-3.207-8.077-8.424 0-6.392 4.223-10.487 9.9-10.487 3.808 0 5.69 1.671 6.97 3.914l2.108-1.475C21.44 2.078 18.331 0 13.663 0 6.227 0 1.168 5.277 1.168 12.934c0 7 4.953 11.066 10.856 11.066 4.878 0 9.809-2.846 9.809-7.716 0-2.545-1.46-4.231-3.569-5.187m-6.33 4.855c-1.077 0-2.026-.512-2.026-1.453 0-1.483 1.822-1.934 3.606-1.934.678 0 1.34.045 1.927.173-.422 1.927-1.671 3.215-3.508 3.214Z"></path></svg><span>Threads</span></a>
            </nav>
        </div>
        <nav class="dsfa-footer-col"><p class="dsfa-footer-title">Menu</p><ul><li><a href="/halaman/cheesecuit-bandung">Cheesecuit Bandung</a></li><li><a href="/halaman/jual-takoyaki-bandung">Takoyaki Bandung</a></li><li><a href="/halaman/puding-buah-bandung">Puding buah Bandung</a></li><li><a href="/halaman/puding-ulang-tahun-bandung">Puding ulang tahun Bandung</a></li><li><a href="/halaman/jus-homemade-bandung">Jus homemade Bandung</a></li></ul></nav>
        <nav class="dsfa-footer-col"><p class="dsfa-footer-title">Informasi</p><ul><li><a href="/produk">Semua produk</a></li><li><a href="/halaman/cara-pesan">Cara pesan</a></li><li><a href="/halaman/area-pengiriman">Area pengiriman</a></li><li><a href="/halaman/dessert-takoyaki-bojongsoang">Dessert Bojongsoang</a></li><li><a href="/privasi">Privasi</a></li></ul></nav>
    </div>
    <div class="dsfa-wrap dsfa-footer-bottom"><p>&copy; DSFA Homemade, Bandung</p><nav><a href="/lapor">Laporkan situs</a><a href="https://fabriku.id" rel="noopener">Dibuat dengan Fabriku</a></nav></div>
    <div class="dsfa-floating-controls" aria-label="Pemesanan">
        <div data-fb-slot="cart-button"></div>
        <div data-fb-slot="whatsapp-button"></div>
    </div>
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
    <div class="dsfa-hero-copy"><p class="dsfa-kicker">Camilan dan dessert rumahan di Bandung</p><h1>Cheesecuit, takoyaki, puding buah, dan jus homemade di Bandung</h1><p class="dsfa-intro">DSFA Homemade membuat camilan dan dessert di dapur rumah kami di Bandung. Pesan lewat WhatsApp, kami antar ke Bandung dan sekitarnya, maksimal 1 jam dalam kondisi lalu lintas normal.</p><div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="{$waGeneral}" rel="noopener" target="_blank">Pesan via WhatsApp</a><a class="dsfa-button" href="/produk">Lihat semua menu</a></div></div>
    <div class="dsfa-hero-media"><div class="dsfa-slides"><img src="https://is3.cloudhost.id/fabriku/tenants/7/site/hero-1.jpg" alt="Takoyaki dalam wadah plastik dengan saus, mayones, dan serutan katsuobushi" width="1200" height="900"><img src="https://is3.cloudhost.id/fabriku/tenants/7/site/hero-2.jpg" alt="Dua wadah cheesecuit, satu bertabur remah Oreo dan satu bertabur bubuk cokelat" width="1200" height="900"><img src="https://is3.cloudhost.id/fabriku/tenants/7/site/hero-3.jpg" alt="Dua wadah cheesecuit, satu rasa matcha dan satu dengan stroberi segar" width="1200" height="900"><img src="https://is3.cloudhost.id/fabriku/tenants/7/site/hero-4.jpg" alt="Cheesecuit dalam cup kecil bertopping Oreo dan matcha" width="1200" height="900"><img src="https://is3.cloudhost.id/fabriku/tenants/7/site/hero-5.jpg" alt="Empat puding dalam gelas kaca dengan topping matcha, cokelat, dan stroberi" width="1200" height="900"><img src="https://is3.cloudhost.id/fabriku/tenants/7/site/hero-6.jpg" alt="Dua botol jus, strawberry dan blackberry" width="1200" height="900"><span class="dsfa-dots"><i></i><i></i><i></i><i></i><i></i><i></i></span></div></div>
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
    <p class="dsfa-heading-copy">Untuk perayaan: <a href="/halaman/puding-ulang-tahun-bandung">puding ulang tahun Bandung</a>, open order dengan estimasi pengerjaan 3 hari.</p>
</section>
HTML,
        ],
        [
            'id' => 'dsfa-products', 'type' => 'featured-products', 'sort' => 30, 'is_visible' => true,
            'html' => <<<'HTML'
<section class="dsfa-wrap dsfa-section" id="menu"><h2>Katalog dan harga</h2><p class="dsfa-heading-copy">Foto, varian, dan harga setiap menu tampil di sini, atau buka <a href="/produk">halaman katalog lengkap</a>.</p><div class="dsfa-catalog"><div data-fb-slot="products"></div></div></section>
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
