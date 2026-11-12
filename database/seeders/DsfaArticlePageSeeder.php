<?php

namespace Database\Seeders;

use App\Models\BusinessSite;
use App\Models\SiteContentPage;
use Illuminate\Database\Seeder;

/**
 * One article-style content page for the DSFA Homemade tenant site. Idempotent via slug.
 * Run on demand: php artisan db:seed --class=DsfaArticlePageSeeder
 */
class DsfaArticlePageSeeder extends Seeder
{
    public function run(): void
    {
        $site = BusinessSite::where('custom_domain', 'dsfahomemade.com')->firstOrFail();

        SiteContentPage::updateOrCreate(
            ['business_site_id' => $site->id, 'slug' => 'camilan-homemade-vs-kemasan-pabrik'],
            [
                'tenant_id' => $site->tenant_id,
                'title' => 'Camilan Homemade vs Kemasan Pabrik: Cara Menimbang untuk Hantaran dan Acara',
                'seo_title' => 'Camilan Homemade vs Kemasan Pabrik untuk Acara',
                'seo_description' => 'Cara menimbang camilan homemade dan kemasan pabrik untuk hantaran, arisan, dan acara keluarga: bahan, daya tahan, izin edar, dan cara memesan.',
                'html' => $this->html(),
                'css' => null,
                'show_in_footer' => true,
                'is_published' => true,
                'sort' => 20,
            ]
        );
    }

    private function html(): string
    {
        return <<<'HTML'
<article class="dsfa-page" id="isi">
    <nav class="dsfa-breadcrumb"><a href="/">Beranda</a></nav>
    <h1>Camilan homemade vs kemasan pabrik: cara menimbang untuk hantaran dan acara</h1>
    <p class="dsfa-intro">Saat menyiapkan arisan, hantaran, atau acara keluarga, pilihan camilan biasanya terbagi dua: pesan yang dibuat rumahan atau beli yang sudah dikemas pabrik. Keduanya punya tempat. Berikut cara menimbangnya supaya tamu senang dan Anda tidak repot di hari acara.</p>

    <section class="dsfa-page-section"><h2>Apa bedanya, dalam praktik</h2>
        <p>Camilan homemade dibuat dalam jumlah terbatas, biasanya setelah ada pesanan, sehingga rasanya cenderung lebih segar dan bisa disesuaikan, misalnya tingkat manis atau ukuran porsi. Kemasan pabrik unggul di keseragaman, daya tahan, dan kemudahan: ada di toko, tahan lama, dan informasinya tercetak di bungkus.</p>
        <p>Karena itu pertanyaannya bukan mana yang lebih baik, tetapi mana yang cocok untuk kebutuhan Anda hari itu.</p>
    </section>

    <section class="dsfa-page-section"><h2>Lima hal yang dicek sebelum memesan camilan homemade</h2>
        <ul>
            <li><strong>Bahan dan alergen.</strong> Tanyakan bahan utama dan apakah ada susu, telur, kacang, atau gluten. Tamu dengan alergi perlu tahu sebelum makan.</li>
            <li><strong>Cara penyimpanan.</strong> Puding, cheesecuit, dan jus perlu suhu dingin. Tanyakan berapa lama aman disimpan dan di mana sebaiknya ditaruh selama acara.</li>
            <li><strong>Foto asli.</strong> Lihat foto produk yang benar-benar dibuat penjualnya, bukan gambar yang diambil dari tempat lain.</li>
            <li><strong>Waktu pemesanan.</strong> Pesanan dalam jumlah besar butuh jeda persiapan. Tanyakan kapan paling lambat memesan.</li>
            <li><strong>Waktu dan area antar.</strong> Pastikan alamat Anda masuk area antar. Daftarnya ada di halaman <a href="/halaman/area-pengiriman">area pengiriman</a>.</li>
        </ul>
    </section>

    <section class="dsfa-page-section"><h2>Kapan kemasan pabrik lebih masuk akal</h2>
        <p>Kemasan pabrik cocok bila acara berlangsung lama tanpa pendingin, bila Anda butuh jumlah sangat besar dengan rasa seragam, atau bila camilan dibawa ke luar kota. Produk berkemasan juga punya nomor izin edar yang bisa Anda periksa sendiri di situs resmi BPOM.</p>
        <p>Untuk memilih mereknya, jangan hanya melihat harga. Lihat dulu apa yang dibicarakan pembeli lain. Anda bisa membuka <a href="https://suaranetijen.id/top/fmcg?utm_source=dsfahomemade.com&amp;utm_medium=blog" rel="noopener" target="_blank">ranking brand FMCG dengan sentimen netijen tertinggi</a> di SuaraNetijen, lalu membaca tema yang sering dibahas pada tiap merek. Skor di sana merangkum opini publik, bukan hasil uji rasa, jadi anggap sebagai bahan pembanding awal.</p>
    </section>

    <section class="dsfa-page-section"><h2>Campuran yang sering berhasil</h2>
        <p>Banyak penyelenggara acara memadukan keduanya: camilan kering berkemasan sebagai penahan lapar, lalu satu atau dua sajian homemade sebagai sajian utama yang diingat tamu. Dengan begitu risiko terbagi, dan anggaran lebih terkendali.</p>
    </section>

    <section class="dsfa-page-section"><h2>Pilihan dari dapur DSFA</h2>
        <p>Untuk sajian homemade, lihat halaman <a href="/halaman/cheesecuit-bandung">cheesecuit</a>, <a href="/halaman/jual-takoyaki-bandung">takoyaki</a>, <a href="/halaman/puding-buah-bandung">puding buah</a>, <a href="/halaman/puding-ulang-tahun-bandung">puding ulang tahun</a>, dan <a href="/halaman/jus-homemade-bandung">jus homemade</a>. Cara memesan dijelaskan di <a href="/halaman/cara-pesan">halaman cara pesan</a>.</p>
    </section>

    <div class="dsfa-actions"><a class="dsfa-button dsfa-button-primary" href="https://wa.me/6285220150587?text=Halo%20DSFA%20Homemade%2C%20saya%20mau%20tanya%20pesanan%20untuk%20acara" rel="noopener" target="_blank">Tanya pesanan via WhatsApp</a><a class="dsfa-button" href="/#pilihan-menu">Lihat menu</a></div>
</article>
HTML;
    }
}
