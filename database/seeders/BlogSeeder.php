<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Seeds/updates blog posts covering researched Indonesian long-tail keywords
     * around stok bahan baku, pencatatan produksi, laporan penjualan, HPP, dan
     * pengelolaan pesanan multi-channel. Keyed by slug/email, so re-running this
     * seeder syncs post content to whatever is defined in posts() below — treat
     * this file as the source of truth for these article bodies.
     *
     * Posts are authored by the existing admin@fabriku.id account. This seeder
     * never creates it: AdminUserSeeder (run by DatabaseSeeder) seeds the
     * documented default password, which must not be re-created on production.
     */
    public function run(): void
    {
        $author = AdminUser::where('email', 'admin@fabriku.id')->firstOrFail();

        $categories = collect([
            'Manajemen Stok' => 'manajemen-stok',
            'Produksi' => 'produksi',
            'Penjualan' => 'penjualan',
            'Keuangan' => 'keuangan',
        ])->map(fn (string $slug, string $name) => BlogCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        ));

        $posts = $this->posts();

        foreach ($posts as $data) {
            // Keep the original publish date on re-seed; re-dating every run
            // reshuffles the blog order and lies to crawlers about freshness.
            $publishedAt = BlogPost::where('slug', $data['slug'])->value('published_at') ?? now()->subDays($data['days_ago']);

            $post = BlogPost::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'admin_user_id' => $author->id,
                    'blog_category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'status' => 'published',
                    'published_at' => $publishedAt,
                    'meta_title' => $data['meta_title'],
                    'meta_description' => $data['meta_description'],
                ]
            );

            $tagIds = collect($data['tags'])->map(
                fn (string $name) => BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id
            );

            $post->tags()->sync($tagIds);
        }

        $this->command->info('Blog seeded: '.count($posts).' posts across '.$categories->count().' categories.');
    }

    /**
     * @return array<int, array{slug: string, title: string, category: string, tags: array<int, string>, days_ago: int, excerpt: string, meta_title: string, meta_description: string, content: string}>
     */
    private function posts(): array
    {
        return [
            [
                'slug' => 'cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi',
                'title' => 'Cara Mengelola Stok Bahan Baku UMKM agar Tidak Rugi (Panduan Lengkap)',
                'category' => 'Manajemen Stok',
                'tags' => ['UMKM', 'Stok Bahan Baku', 'Tips Bisnis'],
                'days_ago' => 12,
                'excerpt' => 'Stok bahan baku yang berantakan adalah sumber kerugian tersembunyi paling umum di UMKM. Ini 5 cara praktis mengelolanya, dari minimum stock sampai stock opname rutin.',
                'meta_title' => 'Cara Mengelola Stok Bahan Baku UMKM agar Tidak Rugi',
                'meta_description' => 'Panduan lengkap cara mengelola stok bahan baku UMKM agar tidak rugi: minimum stock, FIFO, stock opname rutin, dan forecast berbasis data penjualan.',
                'content' => <<<'MD'
Banyak pemilik UMKM baru sadar bisnisnya rugi bukan karena penjualan sepi, tapi karena stok bahan baku yang berantakan: bahan menumpuk sampai rusak, kehabisan pas order lagi ramai-ramainya, atau catatan stok di kepala yang ternyata meleset jauh dari kenyataan.

## Kenali Dulu Penyebab Kerugian dari Stok Bahan Baku

Sebelum masuk ke solusi, kenali dulu pola kerugian yang paling sering terjadi:

- **Pembelian berlebihan** karena tidak ada data yang jadi acuan, akhirnya beli "perasaan aman" yang berujung bahan menumpuk.
- **Tidak ada batas minimum stok**, sehingga produksi atau penjualan baru berhenti setelah bahan benar-benar habis.
- **Tidak ada pencatatan keluar-masuk** yang konsisten, jadi tidak ada yang tahu pasti berapa sisa stok sebenarnya.
- **Bahan rusak atau kadaluarsa** karena stok lama tertimbun stok baru dan tidak pernah terpakai duluan.

## 5 Cara Mengelola Stok Bahan Baku agar Tidak Rugi

### 1. Tentukan Minimum Stock per Bahan

Setiap bahan baku punya titik aman yang berbeda tergantung kecepatan pemakaian dan lama waktu pemesanan ulang. Tetapkan angka minimum untuk masing-masing bahan, lalu jadikan itu sebagai sinyal otomatis untuk memesan ulang — bukan menunggu sampai benar-benar habis.

### 2. Terapkan FIFO, Terutama untuk Bahan yang Bisa Rusak

FIFO (First In, First Out) berarti bahan yang masuk lebih dulu, dipakai lebih dulu. Ini penting terutama untuk bahan segar, bahan dengan masa simpan terbatas, atau bahan yang kualitasnya menurun seiring waktu. Tanpa FIFO, bahan lama gampang "tersembunyi" di belakang rak dan baru ketahuan rusak saat sudah terlambat.

### 3. Rutin Stock Opname Mingguan, Jangan Tunggu Akhir Bulan

Stock opname (cek fisik stok dibanding catatan) sebaiknya dilakukan minimal seminggu sekali, bukan menunggu akhir bulan atau akhir tahun. Semakin jarang dicek, semakin besar kemungkinan selisih menumpuk dan semakin sulit dilacak penyebabnya.

### 4. Buat Forecast Berdasarkan Data Penjualan, Bukan Perkiraan

Alih-alih menebak kebutuhan bahan baku, gunakan data penjualan dan produksi periode sebelumnya sebagai acuan. Perhatikan juga pola musiman — misalnya lonjakan permintaan menjelang hari besar — supaya jumlah stok yang disiapkan lebih realistis.

### 5. Catat Setiap Transaksi Masuk-Keluar, Sekecil Apa pun

Sekecil apa pun jumlahnya, setiap bahan yang masuk (pembelian, retur) dan keluar (produksi, kerusakan) perlu tercatat. Tanpa catatan yang konsisten, angka stok di atas kertas akan terus meleset dari kondisi nyata di lapangan.

## Contoh Sederhana Menghitung Minimum Stock

Supaya tidak abstrak, begini cara menghitungnya dalam praktik. Misalnya sebuah usaha kue rumahan memakai rata-rata 5 kg tepung terigu per hari, dan waktu dari pesan ke supplier sampai bahan diterima biasanya 3 hari. Kebutuhan selama masa tunggu itu adalah 5 kg × 3 hari = 15 kg. Tambahkan buffer untuk jaga-jaga kalau ada lonjakan pesanan mendadak, misalnya 5 kg, sehingga minimum stock yang aman adalah sekitar 20 kg.

Artinya, begitu stok tepung tersisa 20 kg, itu sinyal untuk langsung memesan ulang — bukan menunggu sampai tersisa 5 kg atau bahkan habis sama sekali. Angka ini tentu berbeda-beda untuk tiap bahan, tergantung kecepatan pemakaian dan seberapa cepat supplier bisa mengirim. Bahan yang sering dipakai dan lambat dikirim butuh buffer lebih besar dibanding bahan yang jarang dipakai atau bisa didapat dalam hitungan jam.

Cara paling praktis untuk mulai: lihat catatan pemakaian 1-2 bulan terakhir, hitung rata-rata pemakaian harian per bahan, lalu kalikan dengan lama waktu pemesanan ulang plus buffer secukupnya. Tidak perlu rumus rumit — yang penting angka ini ditinjau ulang secara berkala, karena kecepatan pemakaian bisa berubah seiring order naik atau turun.

## Kapan Harus Pindah dari Catatan Manual ke Sistem Digital

Selama jumlah bahan masih sedikit dan transaksinya jarang, catatan manual di buku atau spreadsheet masih bisa jalan. Tapi begitu jenis bahan mulai banyak, ada lebih dari satu orang yang mencatat, atau sudah beberapa kali kejadian stok "hilang" tanpa sebab jelas, itu tanda saatnya pindah ke sistem yang mencatat otomatis setiap pergerakan stok.

Fabriku punya modul stok bahan baku dengan pemakaian FIFO otomatis dan daftar stok menipis di dashboard saat stok menyentuh batas minimum, jadi kamu tidak perlu lagi menghitung manual atau was-was lupa cek gudang.

Kalau usahamu bergerak di konveksi, pola kerugian dari stok bahan baku biasanya lebih spesifik lagi — lihat [5 kesalahan mengelola stok bahan baku yang bikin UMKM konveksi rugi](/blog/kesalahan-mengelola-stok-bahan-baku-umkm-konveksi). Dan begitu stok bahan sudah tercatat rapi, langkah berikutnya adalah memastikan harga jual produk juga dihitung dari angka yang benar, bukan tebakan — baca [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm).
MD,
            ],
            [
                'slug' => 'kesalahan-mengelola-stok-bahan-baku-umkm-konveksi',
                'title' => '5 Kesalahan Mengelola Stok Bahan Baku yang Bikin UMKM Konveksi Rugi',
                'category' => 'Manajemen Stok',
                'tags' => ['Konveksi', 'Stok Bahan Baku', 'UMKM'],
                'days_ago' => 9,
                'excerpt' => 'Konveksi rugi sering bukan karena order sepi, tapi karena kain, benang, dan aksesoris yang tidak tercatat rapi. Ini 5 kesalahan yang paling sering terjadi.',
                'meta_title' => '5 Kesalahan Mengelola Stok Bahan Baku UMKM Konveksi',
                'meta_description' => '5 kesalahan umum mengelola stok kain, benang, dan aksesoris di UMKM konveksi yang bikin rugi tanpa disadari, plus cara memperbaikinya.',
                'content' => <<<'MD'
Di usaha konveksi, kerugian dari stok bahan baku sering tidak terlihat langsung. Beda dengan kehabisan bahan yang langsung ketahuan karena produksi berhenti, kesalahan-kesalahan berikut ini biasanya baru terasa setelah menumpuk berbulan-bulan.

## 1. Beli Kain Tanpa Hitung Kebutuhan Pola Dulu

Membeli kain hanya berdasarkan perkiraan jumlah pesanan, tanpa menghitung kebutuhan aktual per pola/desain, sering berujung dua skenario: kain kurang di tengah produksi, atau justru sisa banyak dan menumpuk di gudang tanpa rencana pemakaian.

## 2. Tidak Ada Catatan Sisa Kain per Gulungan (Roll)

Setiap gulungan kain biasanya punya sisa panjang yang berbeda-beda setelah dipotong. Tanpa catatan sisa per roll, sisa-sisa kecil ini gampang terlupakan, tercampur dengan roll lain, atau bahkan dianggap "habis" padahal sebenarnya masih ada.

## 3. Mencampur Kain Lama dan Baru Tanpa FIFO

Kain yang dibeli belakangan sering dipakai duluan karena posisinya lebih mudah dijangkau, sementara kain lama terus tertumpuk di bagian belakang rak. Akibatnya, kain lama bisa mengalami perubahan warna atau kualitas sebelum sempat terpakai — kerugian yang baru ketahuan saat kain itu akhirnya diambil.

## 4. Tidak Ada Standar Minimum Stock untuk Benang dan Aksesoris

Kain sering jadi fokus utama, sementara benang, kancing, resleting, dan aksesoris lain kurang diperhatikan. Padahal produksi bisa berhenti mendadak hanya karena kehabisan satu jenis kancing atau benang warna tertentu, walau stok kain masih banyak.

## 5. Data Stok Hanya Ada di Kepala Kepala Produksi

Ini kesalahan yang paling berisiko: informasi stok yang sebenarnya hanya diketahui satu orang, biasanya kepala produksi atau penjahit senior. Begitu orang itu cuti, sakit, atau resign, informasi stok yang akurat ikut hilang bersamanya.

## Dampak Finansial dari Kesalahan Ini kalau Dibiarkan

Kelima kesalahan di atas jarang terasa langsung di kas harian, tapi dampaknya nyata kalau dihitung dalam sebulan. Kain yang rusak karena kelamaan tersimpan tanpa FIFO berarti modal yang sudah dikeluarkan untuk kain itu hilang begitu saja — tidak bisa dipakai untuk produksi, tidak bisa dijual. Sisa kain per roll yang tidak tercatat juga sering berujung pembelian ulang padahal sebenarnya stoknya masih ada, hanya tidak ketahuan di mana.

Kekurangan kancing atau benang di tengah produksi juga punya biaya tersembunyi: line produksi berhenti menunggu bahan datang, jadwal kirim ke pelanggan mundur, dan kadang harus beli darurat dengan harga lebih mahal karena buru-buru. Kalau ini terjadi beberapa kali sebulan, akumulasinya bisa lebih besar dari yang disadari pemilik usaha, karena masing-masing kejadian terlihat "kecil" secara terpisah.

Risiko paling besar justru dari kesalahan kelima: data stok yang hanya ada di kepala satu orang. Kalau orang itu tidak masuk kerja mendadak, seluruh tim jadi tidak tahu harus ambil bahan dari mana, berapa sisanya, atau mana yang harus dipakai duluan. Produksi bisa berhenti total bukan karena bahan benar-benar habis, tapi karena tidak ada yang tahu di mana bahannya.

## Cara Memperbaikinya

Kelima kesalahan di atas punya akar masalah yang sama: stok tidak dicatat secara sistematis per item, per lokasi, dan per orang yang bertanggung jawab. Solusinya bukan sekadar rajin mencatat, tapi mencatat dengan cara yang bisa diakses dan dipahami siapa saja di tim — bukan hanya satu orang.

Fitur multi-rack di Fabriku memungkinkan setiap gulungan kain atau batch aksesoris dicatat sebagai item terpisah dengan lokasi rak masing-masing, jadi sisa stok per roll dan riwayat pemakaiannya tetap jelas meski dicek oleh siapa pun, kapan pun.

Kelima kesalahan di atas berlaku juga untuk bahan baku UMKM di luar konveksi — pola dasarnya sama, lihat [cara mengelola stok bahan baku UMKM agar tidak rugi](/blog/cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi). Kalau kesalahan kelima (data stok hanya ada di kepala satu orang) terasa familiar, itu biasanya tanda sudah waktunya lihat [kapan usaha butuh aplikasi pencatatan produksi](/blog/aplikasi-pencatatan-produksi-usaha-kecil-menengah), bukan cuma stoknya saja yang dibenahi.
MD,
            ],
            [
                'slug' => 'aplikasi-pencatatan-produksi-usaha-kecil-menengah',
                'title' => 'Aplikasi Pencatatan Produksi Usaha Kecil Menengah: Kapan Harus Pindah dari Manual ke Digital?',
                'category' => 'Produksi',
                'tags' => ['Produksi', 'Aplikasi UMKM', 'Digitalisasi'],
                'days_ago' => 7,
                'excerpt' => 'Catatan produksi di buku atau grup WhatsApp cukup untuk skala kecil, tapi ada titik di mana cara ini mulai berisiko. Kenali tandanya dan apa yang harus ada di aplikasi pengganti.',
                'meta_title' => 'Aplikasi Pencatatan Produksi Usaha Kecil Menengah',
                'meta_description' => 'Kapan UMKM perlu aplikasi pencatatan produksi? Kenali tanda-tandanya dan fitur wajib yang harus ada di aplikasi pencatatan produksi usaha kecil menengah.',
                'content' => <<<'MD'
Banyak UMKM memulai pencatatan produksi dengan cara paling sederhana: buku tulis, spreadsheet, atau grup WhatsApp untuk koordinasi tim. Cara ini efektif di awal, tapi seiring bisnis berkembang, ada titik di mana cara manual justru menghambat.

## Tanda-Tanda UMKM Sudah Butuh Aplikasi Pencatatan Produksi

- **Skala order mulai naik** dan sulit dipantau satu per satu secara manual.
- **Sering salah hitung kebutuhan bahan baku** karena perhitungan manual rawan human error.
- **Tidak tahu progress produksi secara real-time** — pemilik harus tanya langsung ke lapangan setiap kali ingin tahu status pesanan.
- **Sulit melacak siapa mengerjakan apa**, terutama saat ada beberapa tim atau kontraktor yang terlibat dalam satu alur produksi.

Kalau satu atau lebih tanda di atas sudah terasa, itu sinyal cara pencatatan lama mulai jadi bottleneck, bukan lagi solusi.

## Apa yang Harus Ada di Aplikasi Pencatatan Produksi UMKM

Tidak semua aplikasi pencatatan cocok untuk kebutuhan produksi UMKM. Beberapa hal ini sebaiknya jadi standar minimal:

1. **Tracking status per tahapan produksi** — dari bahan disiapkan, dikerjakan, sampai selesai dan siap jual, bukan cuma status "selesai/belum".
2. **Otomatis memotong stok bahan baku** saat produksi berjalan, supaya stok bahan dan progress produksi selalu sinkron tanpa pencatatan ganda.
3. **Riwayat siapa dan kapan** setiap tahapan dikerjakan, penting untuk melacak kalau ada masalah kualitas atau keterlambatan.
4. **Terhubung langsung ke stok barang jadi**, sehingga begitu produksi selesai, stok yang siap dijual otomatis bertambah tanpa input manual dua kali.

## Manual vs Digital: Perbandingan Singkat

| Aspek | Manual (buku/WA) | Aplikasi Digital |
|---|---|---|
| Kecepatan cek status | Harus tanya langsung | Bisa dicek kapan saja |
| Risiko human error | Tinggi, terutama hitung bahan | Lebih rendah, terhitung otomatis |
| Ketergantungan satu orang | Tinggi | Rendah, data tersimpan terpusat |
| Riwayat produksi | Sulit ditelusuri | Tercatat otomatis |

Perbandingan ini bukan berarti cara manual selalu buruk — untuk usaha yang masih sangat kecil, manual masih masuk akal. Tapi begitu kompleksitas bertambah, biaya "tetap manual" biasanya lebih mahal daripada biaya pindah ke sistem digital.

## Contoh Skenario: Berapa Waktu yang Hilang karena Pencatatan Manual

Bayangkan sebuah UMKM konveksi dengan 3 kontraktor jahit yang mengerjakan pesanan berbeda-beda tiap minggu. Setiap kali pemilik ingin tahu progress, dia harus telepon atau chat satu per satu, menunggu balasan, lalu mencatat ulang di buku. Kalau proses ini butuh 10-15 menit per kontraktor dan dilakukan setiap hari, itu sudah 30-45 menit sehari hanya untuk mengumpulkan informasi yang seharusnya bisa dilihat sekali klik.

Belum lagi risiko salah catat: progress yang disampaikan lewat chat gampang campur dengan obrolan lain, atau informasi jumlah bahan yang dipakai kontraktor tidak selalu dilaporkan akurat karena dihitung dari ingatan, bukan dicatat langsung saat produksi berjalan. Selisih kecil di setiap laporan, kalau diakumulasi selama sebulan, bisa membuat perhitungan stok bahan baku meleset cukup jauh dari kondisi nyata.

Dengan aplikasi yang mencatat status produksi secara terpusat, waktu yang tadinya habis untuk "menagih laporan" bisa dipakai untuk hal lain yang lebih produktif — dan datanya pun lebih bisa diandalkan karena dicatat langsung di sumbernya, bukan diteruskan berlapis-lapis lewat obrolan.

Modul produksi Fabriku dirancang khusus untuk alur ini: setiap tahapan produksi tercatat, stok bahan baku terpotong otomatis, dan hasil produksi langsung masuk ke stok barang jadi tanpa input berulang.

Untuk usaha kuliner rumahan yang skalanya masih kecil, kebutuhan pencatatan produksinya sedikit berbeda dan lebih sederhana — lihat [cara mencatat produksi harian UMKM kuliner rumahan](/blog/cara-mencatat-produksi-harian-umkm-kuliner-rumahan) untuk pendekatan yang lebih ringan.
MD,
            ],
            [
                'slug' => 'cara-mencatat-produksi-harian-umkm-kuliner-rumahan',
                'title' => 'Cara Mencatat Produksi Harian UMKM Kuliner Rumahan Tanpa Ribet',
                'category' => 'Produksi',
                'tags' => ['Kuliner Rumahan', 'Produksi', 'UMKM'],
                'days_ago' => 5,
                'excerpt' => 'Usaha kuliner rumahan sering terlalu sibuk untuk mencatat produksi. Padahal tanpa catatan, sulit tahu resep mana yang paling untung. Ini cara mencatatnya secara simpel.',
                'meta_title' => 'Cara Mencatat Produksi Harian UMKM Kuliner Rumahan',
                'meta_description' => 'Cara simpel mencatat produksi harian untuk usaha kuliner rumahan: dari resep, bahan yang keluar, sampai hasil jadi, tanpa bikin repot rutinitas dapur.',
                'content' => <<<'MD'
Usaha kuliner rumahan — katering, kue, atau snack — punya tantangan khusus: pemiliknya biasanya merangkap jadi juru masak, jadi pencatatan sering terlewat karena tangan sudah penuh dengan adonan atau bumbu.

## Kenapa Pencatatan Produksi Tetap Penting Buat Usaha Kuliner Rumahan

Meski terasa merepotkan, pencatatan produksi punya manfaat yang langsung terasa di dompet:

- **Kontrol HPP (harga pokok produksi)** — tanpa catatan bahan yang dipakai, sulit tahu apakah harga jual sudah menutup biaya produksi atau justru rugi diam-diam.
- **Tahu kapan bahan mendekati kadaluarsa**, terutama untuk bahan basah atau setengah jadi yang mudah rusak.
- **Tahu resep mana yang paling untung**, karena bisa dibandingkan biaya bahan versus harga jual per produk.

## Cara Simpel Mencatat Produksi Harian

Tidak perlu sistem rumit untuk mulai. Lima langkah ini cukup untuk usaha rumahan skala kecil-menengah:

1. **Catat resep dan takaran per produk** sekali di awal, supaya setiap kali produksi tinggal pakai acuan yang sama, tidak menghitung ulang dari nol.
2. **Catat bahan yang keluar setiap kali produksi**, walau hanya beberapa item — ini yang jadi dasar hitung HPP.
3. **Catat jumlah hasil jadi**, termasuk kalau ada hasil yang gagal atau reject, supaya efisiensi produksi juga ikut terlihat.
4. **Catat tanggal produksi dan kadaluarsa** kalau produknya punya masa simpan terbatas.
5. **Review catatan seminggu sekali**, bukan setiap hari — cukup untuk melihat pola tanpa menyita waktu produksi harian.

## Tools yang Bisa Dipakai

Untuk mulai, catatan di notes HP atau buku kecil di dapur sudah cukup. Yang penting konsisten dicatat, bukan alat yang dipakai. Begitu skala order mulai naik dan pencatatan manual mulai terasa membebani — misalnya sering lupa catat atau sulit menghitung HPP total dalam sebulan — itu saatnya mempertimbangkan aplikasi yang bisa merangkum semua catatan ini secara otomatis.

## Kesalahan yang Sering Terjadi di Usaha Rumahan

Beberapa kesalahan ini paling sering bikin pencatatan produksi berantakan di usaha kuliner rumahan. Pertama, mencampur bahan untuk keperluan pribadi (misalnya masak untuk keluarga) dengan bahan untuk produksi tanpa dipisah — akibatnya perhitungan HPP jadi tidak akurat karena bahan yang sebenarnya bukan untuk dijual ikut terhitung sebagai biaya produksi. Kedua, tidak mencatat hasil yang gagal atau reject, padahal bahan untuk hasil gagal itu tetap keluar biaya dan seharusnya tetap masuk perhitungan.

Ketiga, menyamaratakan takaran resep padahal dalam praktiknya sering ada penyesuaian kecil setiap produksi — kalau penyesuaian ini tidak dicatat, resep acuan lama-lama meleset dari kenyataan dan HPP yang dihitung jadi tidak mencerminkan biaya yang sebenarnya dikeluarkan.

## Contoh Sederhana Menghitung HPP dari Catatan Produksi Harian

Misalnya dalam satu kali produksi, seorang pemilik usaha kue kering membuat 20 toples nastar dengan rincian bahan: tepung terigu Rp30.000, mentega Rp45.000, gula halus Rp15.000, kacang mede Rp60.000, dan kemasan toples Rp50.000 (Rp2.500 × 20 toples). Total biaya bahan untuk satu batch produksi ini adalah Rp200.000.

Dari total tersebut, HPP per toples dihitung dengan membagi total biaya dengan jumlah hasil jadi: Rp200.000 ÷ 20 toples = Rp10.000 per toples. Angka ini yang jadi dasar menentukan harga jual — kalau dijual Rp15.000 per toples, marginnya Rp5.000 per toples sebelum dikurangi biaya tenaga kerja dan operasional lain.

Tanpa catatan seperti ini, angka HPP biasanya hanya ditebak berdasarkan "kira-kira", yang rawan meleset terutama saat harga bahan baku naik-turun. Dengan catatan bahan yang konsisten di setiap produksi, pemilik usaha bisa dengan cepat tahu kapan harga jual perlu disesuaikan supaya margin tetap aman, tanpa harus menunggu sampai akhir bulan baru sadar untung tipis.

Untuk kategori usaha rumahan, Fabriku menyediakan alur pencatatan produksi sederhana yang tidak mengharuskan pencatatan berlapis seperti pabrik besar, cukup input bahan dan hasil jadi, dan laporan HPP-nya terhitung sendiri.

Kalau ingin memahami perhitungan HPP lebih dalam di luar konteks dapur rumahan — termasuk komponen overhead dan tenaga kerja yang lebih kompleks — lihat juga [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm). Kalau dapurmu menjual musiman, baca juga [cara menyiapkan stok UMKM menjelang Ramadan dan Lebaran](/blog/cara-menyiapkan-stok-umkm-menjelang-ramadan-lebaran).
MD,
            ],
            [
                'slug' => 'cara-membuat-laporan-penjualan-umkm-sederhana',
                'title' => 'Cara Membuat Laporan Penjualan UMKM Sederhana: Contoh Harian, Mingguan, dan Template Excel',
                'category' => 'Penjualan',
                'tags' => ['Laporan Penjualan', 'UMKM', 'Excel'],
                'days_ago' => 3,
                'excerpt' => 'Laporan penjualan tidak harus rumit untuk mulai berguna. Ini kolom wajib, contoh laporan penjualan harian dan mingguan, serta rumus Excel yang bisa langsung dipakai UMKM.',
                'meta_title' => 'Contoh Laporan Penjualan Harian & Sederhana UMKM (Excel)',
                'meta_description' => 'Contoh laporan penjualan harian dan sederhana untuk UMKM: kolom wajib, tabel siap salin ke Excel, rumus SUMIF, dan template rekap bulanan.',
                'content' => <<<'MD'
Banyak UMKM menunda membuat laporan penjualan karena mengira harus rumit dan butuh latar belakang akuntansi. Padahal laporan penjualan yang berguna bisa dimulai dari struktur yang sangat sederhana.

## Kolom Wajib Ada di Laporan Penjualan UMKM

Sebelum mulai mencatat, siapkan struktur kolom berikut — baik di buku, Excel, atau aplikasi kasir:

- **Tanggal Penjualan** — kapan transaksi terjadi.
- **Nama Produk/Jasa** — apa yang terjual.
- **Jumlah Unit Terjual** — berapa banyak.
- **Harga per Unit** — harga jual satuan.
- **Total Penjualan** — hasil kali jumlah dan harga.
- **Metode Pembayaran** — tunai, transfer, atau lainnya.
- **Status Pembayaran** — lunas atau masih ada piutang, sering terlewat padahal penting untuk arus kas.

## Langkah Membuat Laporan Penjualan Sederhana

1. **Tentukan format** — mau di buku, Excel, atau aplikasi, pilih satu dan konsisten dipakai.
2. **Catat setiap transaksi** sesuai kolom di atas, sebaiknya di hari yang sama saat transaksi terjadi supaya tidak ada yang terlewat.
3. **Hitung total pendapatan** secara berkala, misalnya mingguan, untuk melihat tren tanpa menunggu akhir bulan.
4. **Analisis data** — produk apa yang paling laku, hari apa paling ramai, ini yang jadi dasar keputusan bisnis selanjutnya.
5. **Simpan laporan** dalam format yang mudah diakses ulang, baik file Excel, PDF, atau backup di aplikasi.

## Excel vs Aplikasi Penjualan Digital

Excel cocok untuk mulai karena gratis dan fleksibel. Tapi begitu transaksi harian mulai puluhan, mengisi Excel manual jadi rawan salah ketik dan lambat untuk sekadar tahu "berapa omzet hari ini". Aplikasi penjualan digital menghitung otomatis dari transaksi yang sudah tercatat di sistem, tanpa perlu input dobel.

## Contoh Laporan Penjualan Harian (Siap Salin ke Excel)

Laporan penjualan harian mencatat setiap transaksi dalam satu hari, satu baris per transaksi. Contoh berikut dari usaha kue rumahan yang menjual lewat toko, WhatsApp, dan reseller pada 12 Oktober:

| No | Produk | Channel | Qty | Harga/Unit | Total | Pembayaran |
|---|---|---|---|---|---|---|
| 1 | Brownies potong | Toko | 12 | Rp8.000 | Rp96.000 | Tunai |
| 2 | Brownies potong | WhatsApp | 20 | Rp8.000 | Rp160.000 | Transfer |
| 3 | Bolu pisang | Toko | 5 | Rp25.000 | Rp125.000 | Tunai |
| 4 | Bolu pisang | Reseller | 10 | Rp20.000 | Rp200.000 | Belum lunas |
| | **Total** | | **47** | | **Rp581.000** | |

Di bawah tabel, tambahkan tiga baris ringkasan supaya laporan harian langsung bisa dicocokkan dengan uang di laci dan rekening:

- **Tunai**: Rp221.000 → cocokkan dengan isi laci kas.
- **Transfer**: Rp160.000 → cocokkan dengan mutasi rekening.
- **Belum lunas**: Rp200.000 → masuk daftar piutang reseller.

Kalau tabel di atas disalin ke Excel dengan kolom A = No sampai G = Pembayaran, rumus ringkasannya:

- Total omzet hari ini: `=SUM(F2:F5)`
- Total tunai: `=SUMIF(G2:G5;"Tunai";F2:F5)`
- Omzet per channel, misalnya reseller: `=SUMIF(C2:C5;"Reseller";F2:F5)`

Untuk laporan penjualan harian di Excel, buat satu sheet per bulan dan tambahkan kolom tanggal di depan, bukan satu file per hari. Satu sheet panjang lebih mudah direkap dengan SUMIFS atau PivotTable di akhir bulan.

Toko yang menjual ratusan barang kecil dengan harga grosir dan eceran butuh kolom tambahan. Lihat [contoh laporan penjualan harian toko sembako](/blog/contoh-laporan-penjualan-harian-toko-sembako).

## Contoh Laporan Penjualan Mingguan Sederhana

Supaya lebih konkret, begini contoh laporan penjualan seminggu untuk usaha kecil yang menjual satu jenis produk:

| Tanggal | Produk | Qty | Harga/Unit | Total | Metode Bayar | Status |
|---|---|---|---|---|---|---|
| 1 Agt | Kaos Polos | 12 | Rp75.000 | Rp900.000 | Tunai | Lunas |
| 2 Agt | Kaos Polos | 8 | Rp75.000 | Rp600.000 | Transfer | Lunas |
| 3 Agt | Kaos Polos | 15 | Rp75.000 | Rp1.125.000 | Transfer | Lunas |
| 4 Agt | Kaos Polos | 5 | Rp75.000 | Rp375.000 | Tunai | Belum Lunas |

Dari tabel sederhana ini saja sudah kelihatan beberapa hal: total omzet minggu itu, hari mana yang paling ramai (3 Agustus), metode pembayaran yang paling sering dipakai (transfer), dan ada satu transaksi yang belum lunas senilai Rp375.000 yang perlu ditagih. Tanpa kolom status pembayaran, transaksi belum lunas ini gampang terlewat dan dianggap sudah masuk sebagai omzet padahal uangnya belum diterima.

Laporan sesederhana ini sudah cukup untuk mulai mengambil keputusan — misalnya menyiapkan stok lebih banyak menjelang hari yang biasanya ramai, atau mengingatkan pelanggan yang belum melunasi pembayaran sebelum piutang menumpuk.

## Template Laporan Penjualan Bulanan

Setelah catatan harian berjalan, buat ringkasan bulanan di sheet terpisah. Tiga tabel ini sudah cukup untuk usaha kecil:

**1. Ringkasan per minggu**

| Minggu | Jumlah transaksi | Omzet | Sudah dibayar | Belum dibayar |
|---|---|---|---|---|
| 1–7 Agt | 42 | Rp3.150.000 | Rp2.775.000 | Rp375.000 |
| 8–14 Agt | 38 | Rp2.850.000 | Rp2.850.000 | Rp0 |
| 15–21 Agt | 51 | Rp3.825.000 | Rp3.450.000 | Rp375.000 |
| 22–31 Agt | 60 | Rp4.500.000 | Rp4.125.000 | Rp375.000 |
| **Total** | **191** | **Rp14.325.000** | **Rp13.200.000** | **Rp1.125.000** |

**2. Penjualan per produk** — kolom: produk, qty terjual, omzet, persentase dari total omzet. Urutkan dari omzet terbesar untuk melihat produk andalan.

**3. Penjualan per channel** — kolom: offline/toko, WhatsApp, marketplace, reseller. Ini membantu menentukan channel mana yang layak diberi waktu dan modal lebih.

## Rumus Excel yang Paling Berguna

Jika catatan harian ada di sheet *Transaksi* dengan kolom A = Tanggal, B = Produk, C = Qty, D = Harga, E = Total, F = Metode, G = Status:

- **Total kolom E**: `=C2*D2` lalu tarik ke bawah.
- **Omzet satu produk**: `=SUMIF(Transaksi!B:B;"Kaos Polos";Transaksi!E:E)`
- **Omzet rentang tanggal**: `=SUMIFS(Transaksi!E:E;Transaksi!A:A;">="&DATE(2026;8;1);Transaksi!A:A;"<="&DATE(2026;8;7))`
- **Total belum dibayar**: `=SUMIF(Transaksi!G:G;"Belum Lunas";Transaksi!E:E)`
- **Jumlah transaksi**: `=COUNTA(Transaksi!A:A)-1`

Pemisah argumen `;` atau `,` tergantung pengaturan bahasa Excel Anda. Untuk ringkasan per produk tanpa menulis rumus satu per satu, gunakan **PivotTable** dengan *Produk* di baris dan *Sum of Total* di nilai.

## Kesalahan yang Membuat Laporan Tidak Bisa Dipakai

- **Nama produk ditulis berbeda-beda.** "Kaos polos", "kaos Polos", dan "kaos plos" dihitung sebagai tiga produk di rumus SUMIF. Buat daftar nama produk baku dan pilih dari daftar itu (Data Validation di Excel).
- **Retur dan diskon tidak dicatat.** Omzet terlihat lebih besar dari uang yang benar-benar masuk.
- **Penjualan pribadi dicampur.** Barang yang diambil untuk dipakai sendiri atau diberikan gratis harus dicatat terpisah agar stok dan omzet tetap cocok.
- **Tanggal ditulis sebagai teks.** Rumus rentang tanggal tidak akan berjalan jika kolom tanggal berformat teks.

## Berapa Sering Laporan Ini Perlu Dibuat

Untuk usaha yang baru mulai, rekap mingguan biasanya cukup untuk mulai melihat pola tanpa terasa membebani. Begitu transaksi harian mulai lebih dari 10-15 kali, rekap harian jadi lebih berguna karena pola penjualan bisa berubah cukup cepat, dan menunggu seminggu untuk melihat data berarti terlambat mengambil keputusan seperti menambah stok atau menyesuaikan harga.

Yang lebih penting dari frekuensi adalah konsistensi. Laporan yang dibuat rutin walau formatnya sederhana jauh lebih berguna dibanding laporan detail yang hanya dibuat sesekali saat sempat. Kebiasaan mencatat setiap transaksi di hari yang sama juga mengurangi risiko lupa atau salah ingat detail transaksi, yang sering terjadi kalau pencatatan ditunda sampai akhir hari atau akhir minggu.

## Pertanyaan yang Sering Muncul

**Apa bedanya laporan penjualan dengan laporan laba rugi?**
Laporan penjualan mencatat omzet (uang dari penjualan). Laporan laba rugi mengurangi omzet dengan HPP dan biaya operasional untuk mengetahui untung bersih. Laporan penjualan adalah bahan dasar laporan laba rugi.

**Apakah penjualan yang belum dibayar dihitung sebagai omzet?**
Dicatat sebagai penjualan, tapi tandai statusnya. Uang yang belum diterima adalah piutang dan belum bisa dipakai untuk belanja bahan.

**Apakah cukup memakai buku tulis?**
Cukup untuk awal, selama kolomnya sama setiap hari. Kendalanya muncul saat ingin merekap per produk atau per bulan, karena semuanya harus dihitung ulang manual.

Fabriku mencatat setiap pesanan langsung sebagai data penjualan, sehingga laporan seperti ini tidak perlu disusun manual — rekap per periode, per produk, dan status pembayaran bisa dilihat dan diekspor ke Excel atau PDF. Untuk usaha yang terutama menjual barang jadi, lihat [Fabriku untuk toko retail](/aplikasi/toko-retail). Kalau pencatatan masih memakai Excel, [kapan usaha perlu pindah dari Excel ke aplikasi pencatatan](/blog/aplikasi-pencatatan-umkm-vs-excel-kapan-pindah) menjelaskan tanda-tandanya.

Setelah laporan dasar ini rutin dibuat, langkah selanjutnya adalah membacanya lebih dalam — lihat [5 metrik laporan penjualan yang wajib dipantau UMKM setiap bulan](/blog/metrik-laporan-penjualan-wajib-dipantau-umkm). Kolom status pembayaran di atas juga berkaitan erat dengan [cara mencatat dan menagih piutang pelanggan UMKM](/blog/cara-mencatat-menagih-piutang-pelanggan-umkm) — transaksi yang belum lunas hari ini adalah piutang yang perlu ditagih besok.
MD,
            ],
            [
                'slug' => 'metrik-laporan-penjualan-wajib-dipantau-umkm',
                'title' => '5 Metrik Laporan Penjualan yang Wajib Dipantau Pemilik UMKM Setiap Bulan',
                'category' => 'Penjualan',
                'tags' => ['Laporan Penjualan', 'Analisis Bisnis', 'UMKM'],
                'days_ago' => 1,
                'excerpt' => 'Punya laporan penjualan saja tidak cukup kalau tidak dianalisis. Ini 5 metrik yang wajib dipantau pemilik UMKM setiap bulan untuk ambil keputusan yang lebih tepat.',
                'meta_title' => '5 Metrik Laporan Penjualan Wajib Dipantau UMKM',
                'meta_description' => '5 metrik penting dalam laporan penjualan yang wajib dipantau pemilik UMKM setiap bulan: omzet, produk terlaris, AOV, piutang, dan channel penjualan.',
                'content' => <<<'MD'
Banyak UMKM sudah rajin membuat laporan penjualan, tapi berhenti di tahap mencatat — jarang benar-benar dianalisis untuk ambil keputusan. Padahal laporan yang sama bisa menunjukkan banyak hal kalau dibaca dengan metrik yang tepat.

## 1. Total Omzet vs Bulan Lalu

Angka omzet saja kurang bermakna tanpa pembanding. Selalu lihat omzet bulan ini dibanding bulan sebelumnya untuk tahu apakah bisnis sedang tumbuh, stagnan, atau menurun — dan cari tahu penyebabnya lebih awal, bukan setelah beberapa bulan berturut-turut turun.

## 2. Produk Terlaris vs Produk yang Tidak Bergerak

Selain tahu produk terlaris, penting juga mengenali produk yang jarang atau tidak pernah terjual (slow-moving). Produk seperti ini menahan modal dalam bentuk stok yang tidak berputar, dan sering jadi kandidat pertama untuk dihentikan atau dipromosikan ulang.

## 3. Rata-Rata Nilai Transaksi (Average Order Value)

Rata-rata nilai transaksi menunjukkan seberapa besar belanja pelanggan dalam satu kali transaksi. Metrik ini berguna untuk mengevaluasi strategi seperti bundling produk atau minimum pembelian — apakah strategi itu berhasil menaikkan nilai transaksi atau tidak.

## 4. Piutang atau Pembayaran yang Belum Lunas

UMKM yang menerima pembayaran cicilan, DP, atau tempo sering lupa memantau total piutang yang belum masuk. Tanpa dipantau rutin, piutang bisa menumpuk dan mengganggu arus kas meski di atas kertas omzet terlihat baik.

## 5. Channel Penjualan Paling Untung

Kalau berjualan lewat lebih dari satu channel — offline, online, marketplace, atau reseller — bandingkan performanya. Channel dengan volume tinggi belum tentu paling menguntungkan setelah dikurangi biaya seperti komisi marketplace atau ongkos kirim.

## Cara Membaca Metrik Ini untuk Ambil Keputusan

Kelima metrik di atas paling berguna kalau dilihat bersamaan, bukan satu-satu. Omzet naik tapi piutang juga menumpuk, misalnya, berarti pertumbuhan itu belum tentu sehat untuk arus kas. Produk terlaris di satu channel belum tentu sama menguntungkannya di channel lain.

Kebiasaan meninjau kelima metrik ini bersamaan, bukan cuma total omzet, adalah yang membedakan pemilik usaha yang sekadar mencatat dengan yang benar-benar mengelola bisnisnya berdasarkan data. Tanpa kebiasaan ini, laporan penjualan hanya jadi arsip yang jarang dibuka lagi setelah dicatat.

## Contoh Kasus: Membaca Kelima Metrik Bersamaan

Bayangkan sebuah UMKM fesyen dengan laporan bulan ini: omzet naik 20% dibanding bulan lalu, terdengar bagus di permukaan. Tapi begitu ditelusuri lebih dalam, kenaikan itu didominasi oleh satu channel — reseller — yang marginnya lebih tipis karena harga khusus reseller, sementara channel offline yang marginnya lebih besar justru stagnan. Rata-rata nilai transaksi juga turun, artinya kenaikan omzet lebih banyak disumbang volume, bukan nilai per transaksi yang lebih tinggi.

Di saat yang sama, piutang dari pembayaran cicilan reseller ikut naik sebanding dengan kenaikan omzet dari channel itu. Kalau hanya melihat angka omzet, pemilik usaha bisa salah simpul bahwa bisnis sedang tumbuh sehat. Tapi kalau kelima metrik dibaca bersamaan, gambaran sebenarnya adalah: pertumbuhan volume yang justru menekan margin dan menambah risiko piutang macet.

Dari sini, keputusan yang lebih tepat bukan sekadar "kejar omzet lebih tinggi lagi", tapi mengevaluasi ulang harga atau syarat pembayaran untuk channel reseller, sambil mendorong channel offline yang marginnya lebih sehat. Keputusan seperti ini hanya bisa diambil kalau laporan penjualan dibaca lebih dalam dari sekadar angka total di baris paling bawah.

Laporan Fabriku sudah memecah data penjualan berdasarkan channel dan status pembayaran secara otomatis, jadi metrik-metrik ini bisa langsung dilihat tanpa harus menyusun ulang dari data mentah setiap bulan.

Kelima metrik ini baru bisa dipantau kalau laporan dasarnya sudah rapi sejak awal — lihat [cara membuat laporan penjualan UMKM sederhana](/blog/cara-membuat-laporan-penjualan-umkm-sederhana) kalau belum punya strukturnya. Metrik pertama soal omzet juga akan lebih bermakna kalau dibandingkan dengan biaya produksi sebenarnya — baca [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm).
MD,
            ],
            [
                'slug' => 'cara-menghitung-hpp-produk-umkm',
                'title' => 'Cara Menghitung HPP dan Harga Jual Produk UMKM (Rumus per Unit, Contoh, Template Excel)',
                'category' => 'Keuangan',
                'tags' => ['HPP', 'Keuangan UMKM', 'UMKM'],
                'days_ago' => 14,
                'excerpt' => 'Harga jual yang asal tebak sering jadi penyebab UMKM rugi tanpa sadar. Ini cara menghitung HPP per unit dan harga jual langkah demi langkah, lengkap dengan contoh perhitungan produksi.',
                'meta_title' => 'Cara Menghitung HPP dan Harga Jual per Unit (Contoh UMKM)',
                'meta_description' => 'Cara menghitung HPP produksi per unit dan harga jual untuk UMKM: rumus, komponen biaya, contoh tabel, markup vs margin, dan template Excel.',
                'content' => <<<'MD'
Salah satu penyebab UMKM merasa "ramai order tapi untungnya tipis" adalah harga jual yang ditentukan dari perkiraan, bukan dari perhitungan HPP (harga pokok produksi) yang sebenarnya. Tanpa tahu HPP, mustahil tahu pasti apakah harga jual sudah menutup semua biaya atau justru menggerus modal pelan-pelan.

## Apa Itu HPP dan Kenapa Penting

HPP adalah total biaya yang dikeluarkan untuk menghasilkan satu unit produk sampai siap dijual. Ini beda dengan harga jual — HPP adalah biaya, harga jual adalah HPP ditambah margin keuntungan yang diinginkan. Tanpa tahu HPP secara akurat, menentukan margin jadi sekadar tebakan, dan tebakan yang meleset bisa berarti setiap produk yang terjual justru menambah kerugian, bukan keuntungan.

## Komponen yang Harus Dihitung dalam HPP

Empat komponen ini wajib masuk hitungan, sekecil apa pun nilainya:

1. **Biaya bahan baku** — semua bahan yang benar-benar terpakai untuk satu unit produk, bukan harga beli borongan yang belum dibagi per unit.
2. **Biaya tenaga kerja langsung** — upah untuk waktu yang benar-benar dipakai mengerjakan produk, baik dihitung per jam maupun per hasil (upah borongan).
3. **Biaya overhead produksi** — listrik, gas, penyusutan alat, dan biaya operasional lain yang terpakai selama proses produksi, meski tidak selalu terlihat langsung per unit.
4. **Biaya kemasan** — sering terlewat padahal untuk sebagian usaha, biaya kemasan bisa cukup signifikan dibanding biaya bahan baku utamanya.

## Cara Menghitung HPP per Unit: Rumus Dasar

Rumus paling dasar: **HPP per unit = Total Biaya Produksi ÷ Jumlah Unit yang Dihasilkan**

Contoh perhitungan: sebuah usaha membuat 50 pcs tas kain dalam satu batch produksi, dengan rincian biaya bahan kain dan aksesoris Rp1.500.000, upah jahit borongan Rp750.000, biaya listrik dan penyusutan mesin jahit diperkirakan Rp100.000, dan kemasan Rp150.000 (Rp3.000 × 50 pcs). Total biaya produksi: Rp2.500.000.

HPP per unit = Rp2.500.000 ÷ 50 pcs = **Rp50.000 per tas**.

Dari angka ini, kalau usaha ingin markup 40% di atas HPP, harga jual minimal yang masuk akal adalah Rp50.000 + (40% × Rp50.000) = Rp70.000. Kalau selama ini dijual di bawah angka itu — misalnya Rp55.000 karena "sudah biasa segitu" — markup sebenarnya hanya 10%, jauh dari target yang dikira sudah tercapai.

## Rincian Perhitungan dalam Tabel

Supaya mudah ditiru, berikut rincian contoh tas kain di atas dalam bentuk tabel. Format seperti ini bisa langsung dipakai di Excel atau buku catatan:

| Komponen | Rincian | Total Batch | Per Unit (÷50) |
|---|---|---|---|
| Bahan baku | Kain kanvas 25 m × Rp45.000 + aksesoris Rp375.000 | Rp1.500.000 | Rp30.000 |
| Tenaga kerja langsung | Upah jahit borongan Rp15.000/pcs | Rp750.000 | Rp15.000 |
| Overhead | Listrik, penyusutan mesin jahit | Rp100.000 | Rp2.000 |
| Kemasan | Plastik + label Rp3.000/pcs | Rp150.000 | Rp3.000 |
| **HPP** | | **Rp2.500.000** | **Rp50.000** |

Kolom "per unit" penting karena memperlihatkan komponen mana yang paling berat. Di contoh ini bahan baku menyumbang 60% HPP, jadi kenaikan harga bahan baku 10% saja langsung menaikkan HPP Rp3.000 per tas.

## Cara Menghitung HPP dan Harga Jual: Markup vs Margin

Setelah HPP per unit diketahui, harga jual dihitung dari HPP dengan salah satu dari dua cara. Dua istilah ini sering dipakai bergantian padahal hasilnya berbeda:

- **Markup** dihitung dari HPP. Markup 40% atas HPP Rp50.000 berarti harga jual Rp70.000.
- **Margin** dihitung dari harga jual. Dengan harga jual Rp70.000 dan HPP Rp50.000, keuntungannya Rp20.000, atau margin **28,6%** dari harga jual.

Kalau target Anda adalah margin 40% dari harga jual, rumusnya berbeda: **Harga Jual = HPP ÷ (1 − 40%)** = Rp50.000 ÷ 0,6 = **Rp83.333**. Selisih Rp13.000 per unit ini sering jadi sumber salah paham ketika pemilik usaha membaca "margin" di laporan keuangan tapi menghitung harga dengan rumus markup.

## Contoh Kedua: HPP Produk Makanan per Resep

Untuk usaha makanan, HPP lebih mudah dihitung per resep atau per adonan, lalu dibagi jumlah porsi yang dihasilkan. Contoh satu adonan brownies panggang ukuran 20×20 cm yang dipotong 16 bagian:

| Bahan | Pemakaian | Biaya |
|---|---|---|
| Dark chocolate compound | 200 g | Rp18.000 |
| Mentega | 150 g | Rp12.000 |
| Gula pasir | 200 g | Rp3.200 |
| Telur | 4 butir | Rp8.000 |
| Tepung terigu & kakao | 120 g | Rp4.000 |
| Gas oven (estimasi) | 45 menit | Rp3.000 |
| Kotak + mika | 16 pcs | Rp16.000 |
| **Total** | | **Rp64.200** |

HPP per potong = Rp64.200 ÷ 16 = **Rp4.013**, dibulatkan Rp4.000. Belum termasuk upah membuat. Jika satu adonan dikerjakan 1 jam dengan upah Rp15.000/jam, tambahkan Rp940 per potong sehingga HPP menjadi sekitar Rp5.000. Dengan harga jual Rp8.000 per potong, margin terhadap harga jual adalah 37,5%.

Satu hal yang sering terlewat di usaha makanan adalah **susut dan gagal produksi**. Kalau rata-rata 1 dari 16 potong pecah atau tidak layak jual, HPP sebenarnya dibagi 15, bukan 16 — naik sekitar 7%.

Untuk usaha kuliner yang menjual banyak menu, termasuk minuman dan pesanan lewat aplikasi ojek online, baca panduan khusus [cara menghitung HPP makanan per porsi](/blog/cara-menghitung-hpp-makanan-per-porsi).

## Cara Menghitung HPP Produksi untuk Beberapa Produk Sekaligus

Satu batch produksi sering menghasilkan lebih dari satu jenis produk, misalnya satu gulung kain dipotong menjadi kaos dan totebag, atau satu adonan dibagi menjadi bolu dan muffin. Biaya yang bisa ditelusuri langsung (kain untuk kaos, cup muffin) dibebankan ke produknya masing-masing. Biaya bersama seperti upah harian, listrik, dan gas dibagi berdasarkan porsi pemakaian, biasanya jumlah unit atau lama pengerjaan.

Contoh: upah harian Rp300.000 dipakai untuk membuat 40 kaos (6 jam) dan 30 totebag (2 jam). Pembagian berdasarkan waktu memberi kaos Rp225.000 (Rp5.625 per kaos) dan totebag Rp75.000 (Rp2.500 per totebag). Pembagian per unit akan memberi angka yang sama untuk keduanya (Rp4.286), padahal kaos jauh lebih lama dikerjakan. Pilih dasar pembagian yang paling mendekati kenyataan, lalu pakai cara yang sama setiap bulan.

## Template HPP Sederhana di Excel

Buat satu sheet per produk dengan kolom: *Komponen, Satuan, Pemakaian per batch, Harga per satuan, Total, Per unit*. Lalu:

1. Isi semua bahan berdasarkan nota pembelian terakhir, bukan harga yang diingat.
2. Masukkan upah dan overhead per batch.
3. Rumus kolom *Total* = Pemakaian × Harga per satuan.
4. Rumus *HPP per unit* = jumlah kolom Total ÷ jumlah unit yang layak jual.
5. Tambahkan sel *Harga jual* dan hitung margin = (Harga jual − HPP) ÷ Harga jual.

Perbarui kolom harga setiap kali belanja bahan dengan harga berbeda. Begitu jumlah produk sudah lebih dari lima, memperbarui banyak sheet ini mulai memakan waktu dan rawan lupa.

## Kesalahan Umum saat Menghitung HPP

Kesalahan paling sering: hanya menghitung biaya bahan baku dan lupa memasukkan tenaga kerja atau overhead, sehingga HPP terlihat lebih rendah dari kenyataan dan harga jual jadi terlalu murah. Kesalahan lain adalah menghitung HPP sekali saja di awal, lalu tidak pernah diperbarui meski harga bahan baku sudah naik — akibatnya margin tergerus pelan-pelan tanpa disadari.

## Kapan HPP Perlu Dihitung Ulang

Idealnya, HPP ditinjau ulang setiap kali ada perubahan signifikan pada harga bahan baku, upah, atau resep/formula produk. Untuk usaha dengan bahan baku yang harganya fluktuatif, meninjau HPP tiap bulan lebih aman daripada menunggu sampai margin terasa menipis baru disadari.

## Overhead Sering Terlewat, Padahal Nilainya Nyata

Dari empat komponen HPP, overhead paling sering diabaikan karena tidak sejelas biaya bahan baku yang langsung terlihat dari nota pembelian. Padahal listrik untuk oven atau mesin jahit, gas untuk produksi, sampai penyusutan alat yang dipakai berulang kali tetap merupakan biaya nyata yang menopang produksi.

Cara praktis mengestimasi overhead tanpa perlu hitungan akuntansi rumit: jumlahkan total biaya listrik, gas, dan penyusutan alat dalam sebulan, lalu bagi dengan jumlah total unit yang diproduksi bulan itu untuk dapat estimasi overhead per unit. Angka ini tidak perlu presisi sampai rupiah terakhir — yang penting ada, supaya HPP tidak terlihat lebih murah dari kenyataan hanya karena satu komponen biaya terlewat begitu saja.

## Pertanyaan yang Sering Muncul

**Apakah gaji pemilik usaha masuk HPP?**
Masuk, sebesar waktu yang dipakai untuk produksi. Jika pemilik ikut menjahit atau memasak, hitung waktunya dengan upah yang wajar. Waktu untuk promosi atau administrasi masuk biaya operasional, bukan HPP.

**Apa bedanya HPP dengan biaya operasional?**
HPP hanya biaya yang melekat pada pembuatan produk. Sewa toko, ongkos iklan, dan gaji admin adalah biaya operasional yang ditutup dari laba kotor (harga jual − HPP).

**Seberapa sering harga bahan perlu diperbarui?**
Setiap kali membeli bahan dengan harga berbeda. Minimal tinjau sebulan sekali bila harga bahan sering berubah.

Modul produksi dan stok bahan baku Fabriku mencatat biaya per bahan secara otomatis dari setiap penerimaan bahan, sehingga perhitungan HPP bisa mengikuti harga bahan yang aktual, bukan asumsi harga lama yang sudah berubah. Lihat cara kerjanya untuk [usaha konveksi](/aplikasi/konveksi) atau [usaha makanan dan kue](/aplikasi/usaha-makanan-kue).

Untuk produk yang lebih personal seperti kerajinan tangan, komponen biayanya sedikit berbeda dan skill jadi faktor tambahan — lihat [cara menentukan harga jual produk kerajinan handmade](/blog/cara-menentukan-harga-jual-produk-kerajinan-handmade). Untuk usaha jasa yang tidak punya produk fisik sama sekali, logikanya mirip tapi komponennya berbeda — baca [cara menghitung harga jasa servis kecil agar tidak rugi](/blog/cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi). Setelah HPP diketahui, langkah memilih markup atau margin dan menyesuaikan harga per channel dibahas di [cara menentukan harga jual produk](/blog/cara-menentukan-harga-jual-produk-markup-vs-margin).
MD,
            ],
            [
                'slug' => 'cara-mengelola-pesanan-online-offline-sekaligus',
                'title' => 'Cara Mengelola Pesanan Online dan Offline Sekaligus Tanpa Ribet',
                'category' => 'Penjualan',
                'tags' => ['Omnichannel', 'Penjualan', 'UMKM'],
                'days_ago' => 16,
                'excerpt' => 'Jualan di toko, marketplace, dan reseller sekaligus sering bikin stok dan pesanan berantakan. Ini cara mengelola pesanan online dan offline dari satu alur tanpa dobel kerja.',
                'meta_title' => 'Cara Mengelola Pesanan Online dan Offline Sekaligus',
                'meta_description' => 'Cara mengelola pesanan online dan offline sekaligus untuk UMKM tanpa stok bentrok atau dobel input, dari toko fisik, marketplace, sampai reseller.',
                'content' => <<<'MD'
Begitu UMKM mulai jualan di lebih dari satu tempat — toko fisik, marketplace, dan lewat reseller — masalah baru muncul: stok yang sama harus dipantau dari beberapa sumber sekaligus, dan gampang terjadi barang yang sudah terjual online ternyata juga ditawarkan ke pembeli offline karena datanya tidak sinkron.

## Kenapa Mengelola Banyak Channel Itu Sulit Tanpa Sistem yang Tepat

Tantangan utamanya bukan soal jumlah channel, tapi soal sinkronisasi data. Kalau tiap channel dicatat terpisah — buku untuk toko fisik, aplikasi marketplace untuk online, chat WhatsApp untuk reseller — pemilik usaha harus menjumlahkan manual dari beberapa sumber hanya untuk tahu total penjualan hari itu, dan risiko stok bentrok (oversell) jadi tinggi karena tidak ada satu angka stok yang jadi acuan bersama.

## Cara Mengelola Pesanan dari Banyak Channel Tanpa Ribet

### 1. Satukan Semua Pesanan ke Satu Sumber Data

Sebisa mungkin, semua pesanan — dari mana pun asalnya — dicatat di satu tempat yang sama. Ini tidak berarti harus menutup channel lain, tapi setiap kali ada pesanan masuk dari marketplace atau reseller, catat juga di sistem pusat supaya stok yang terpotong konsisten di semua tempat.

### 2. Bedakan Channel di Setiap Pesanan

Jangan hanya mencatat "terjual", tapi catat juga dari channel mana. Ini penting bukan cuma untuk laporan, tapi supaya nanti bisa dibandingkan channel mana yang paling menguntungkan — volume tinggi di satu channel belum tentu untung lebih besar kalau marginnya lebih tipis, misalnya karena komisi marketplace atau harga khusus reseller.

### 3. Tetapkan Siapa yang Bertanggung Jawab Update Stok

Kalau ada lebih dari satu orang yang menangani penjualan (misalnya satu orang pegang toko fisik, satu lagi pegang marketplace), tetapkan alur yang jelas: begitu ada penjualan di channel mana pun, siapa yang bertanggung jawab mengurangi stok di sumber data pusat, dan seberapa cepat itu harus dilakukan. Delay update stok adalah penyebab paling umum barang oversell.

### 4. Prioritaskan Channel dengan Margin Terbaik saat Stok Terbatas

Kalau stok sebuah produk sedang menipis dan permintaan datang dari beberapa channel sekaligus, punya data channel mana yang paling menguntungkan membantu memutuskan mana yang diprioritaskan, alih-alih asal memenuhi siapa yang order duluan.

## Tanda Sudah Saatnya Pakai Sistem Terpusat

Kalau proses di atas masih terasa berat dilakukan manual — misalnya sering terjadi oversell, atau butuh waktu lama untuk tahu channel mana yang paling untung — itu tanda sudah waktunya beralih ke sistem yang bisa mencatat pesanan dari berbagai channel dalam satu alur, dengan stok yang otomatis sinkron setiap kali ada transaksi baru.

## Kesalahan yang Sering Terjadi saat Baru Mulai Multi-Channel

Kesalahan paling umum adalah membuka channel baru tanpa lebih dulu memastikan alur update stoknya jelas. Banyak UMKM tergoda langsung buka toko di beberapa marketplace sekaligus karena terlihat mudah dan gratis, tapi begitu order mulai masuk dari semua arah, ternyata belum ada kesepakatan siapa yang update stok dan seberapa cepat. Akibatnya oversell terjadi di minggu-minggu pertama, yang justru merusak kepercayaan pembeli baru.

Kesalahan lain adalah tidak membedakan harga atau ongkos antar channel dalam pencatatan, sehingga semua penjualan terlihat setara padahal marginnya berbeda jauh. Baru disadari belakangan, biasanya setelah beberapa bulan, bahwa channel yang paling ramai justru bukan yang paling menguntungkan.

Fabriku mencatat channel penjualan (offline, online, marketplace, reseller) di setiap pesanan, dengan stok yang sama-sama terpotong dari satu sumber data pusat — jadi tidak ada lagi risiko barang yang sama ditawarkan dua kali ke pembeli berbeda.

Kalau belum punya sistem kasir yang bisa menangani banyak channel sekaligus, [7 tips memilih aplikasi kasir untuk UMKM pemula](/blog/tips-memilih-aplikasi-kasir-umkm-pemula) bisa jadi acuan sebelum memutuskan. Multi-channel juga berarti lebih banyak kemungkinan retur dari berbagai sumber — lihat [cara mengelola stok retur barang UMKM retail](/blog/cara-kelola-stok-retur-barang-umkm-retail) supaya retur dari channel mana pun tetap tercatat rapi. Bila ingin menambah website sendiri sebagai channel, lihat [contoh website toko online UMKM](/blog/contoh-website-toko-online-umkm) yang sudah berjalan.
MD,
            ],
            [
                'slug' => 'tips-memilih-aplikasi-kasir-umkm-pemula',
                'title' => '7 Tips Memilih Aplikasi Kasir untuk UMKM Pemula',
                'category' => 'Penjualan',
                'tags' => ['Aplikasi Kasir', 'UMKM', 'Tips Bisnis'],
                'days_ago' => 18,
                'excerpt' => 'Aplikasi kasir yang salah pilih justru bikin repot, bukan mempermudah. Ini 7 hal yang perlu dicek sebelum UMKM pemula memutuskan pakai aplikasi kasir yang mana.',
                'meta_title' => '7 Tips Memilih Aplikasi Kasir untuk UMKM Pemula',
                'meta_description' => '7 tips memilih aplikasi kasir untuk UMKM pemula: dari identifikasi kebutuhan, fitur manajemen stok, sampai kemudahan uji coba sebelum berlangganan.',
                'content' => <<<'MD'
Banyaknya pilihan aplikasi kasir di pasaran justru sering bikin UMKM pemula bingung, bukannya terbantu. Salah pilih aplikasi — misalnya terlalu rumit untuk kebutuhan sederhana, atau justru terlalu terbatas begitu usaha berkembang — berarti harus pindah lagi dan mengulang proses migrasi data dari awal.

## 1. Kenali Dulu Kebutuhan Bisnis, Bukan Ikut Tren

Sebelum membandingkan aplikasi, tentukan dulu kebutuhan spesifik: apakah cuma butuh catat transaksi harian, atau juga butuh kelola stok, produksi, sampai laporan keuangan? Usaha yang jualan produk fisik dengan stok terbatas punya kebutuhan berbeda dari usaha jasa yang tidak punya stok barang sama sekali. Menulis daftar kebutuhan ini di atas kertas sebelum mulai membandingkan aplikasi membantu menghindari godaan memilih berdasarkan fitur yang terlihat menarik tapi sebenarnya tidak relevan dengan cara usaha kamu berjalan.

## 2. Pastikan Ada Fitur Manajemen Stok Otomatis

Kalau bisnis punya stok barang, pastikan aplikasi kasir bisa mengurangi stok otomatis setiap kali ada transaksi, bukan hanya mencatat uang masuk. Tanpa ini, pencatatan stok dan pencatatan penjualan jadi dua pekerjaan terpisah yang gampang tidak sinkron.

## 3. Cek Kesesuaian Harga dengan Skala Usaha

Bandingkan beberapa opsi dan cari yang sepadan dengan anggaran. Harga langganan yang mahal tidak otomatis berarti fitur yang didapat lebih relevan buat usaha kecil — sebaliknya, aplikasi gratis yang terlalu terbatas juga bisa menghambat begitu transaksi mulai naik.

## 4. Pilih Tampilan yang Sederhana dan Mudah Dipelajari Tim

Aplikasi kasir dipakai setiap hari, sering oleh lebih dari satu orang termasuk karyawan baru. Tampilan yang rumit berarti waktu training lebih lama dan risiko salah input lebih tinggi. Prioritaskan aplikasi dengan alur input transaksi yang bisa dipahami dalam hitungan menit.

## 5. Pastikan Ada Dukungan Bahasa Indonesia dan Panduan Penggunaan

Untuk UMKM yang baru pertama kali digitalisasi, dukungan Bahasa Indonesia dan panduan yang jelas sangat membantu mempercepat adaptasi, dibanding aplikasi berbahasa asing yang butuh usaha ekstra untuk dipahami tim.

## 6. Cek Ketersediaan Dukungan Pelanggan

Saat ada masalah teknis — misalnya transaksi gagal tersimpan atau laporan tidak sesuai — kecepatan mendapat bantuan itu penting, terutama di jam sibuk operasional. Cek dulu lewat kanal apa dukungan pelanggan bisa dihubungi dan seberapa responsif sebelum memutuskan berlangganan jangka panjang.

## 7. Coba Dulu Sebelum Berkomitmen Penuh

Sebelum membayar paket tahunan atau memindahkan semua data, manfaatkan versi trial untuk menguji apakah aplikasi benar-benar cocok dengan alur kerja sehari-hari. Uji dengan skenario nyata — input transaksi, cek laporan, kelola stok — bukan hanya melihat demo atau fitur di brosur.

## Pertimbangkan Juga Kemudahan Pindah Data di Masa Depan

Satu hal yang jarang dipikirkan di awal: bagaimana kalau nanti usaha berkembang dan butuh pindah ke sistem lain? Aplikasi yang mengunci data pelanggan atau riwayat transaksi sehingga sulit diekspor bisa jadi masalah besar di kemudian hari. Sebelum berlangganan, cek apakah data transaksi dan stok bisa diunduh atau diekspor kapan saja — ini jaminan kecil tapi penting supaya usaha tidak "tersandera" satu aplikasi tertentu.

## Kesimpulan

Aplikasi kasir yang tepat bukan yang paling banyak fiturnya, tapi yang paling pas dengan cara usaha kamu berjalan sehari-hari. Mulai dari kebutuhan riil, bukan dari daftar fitur yang terlihat keren tapi belum tentu dipakai.

Fabriku menyediakan pencatatan transaksi, stok, produksi, sampai laporan dalam satu sistem yang menyesuaikan kategori usaha — jadi UMKM pemula tidak perlu berlangganan beberapa aplikasi terpisah hanya untuk mencatat hal-hal dasar ini.

Kalau usahamu juga punya proses produksi, bukan cuma jual-beli barang jadi, pertimbangan yang perlu dicek sedikit berbeda — lihat [aplikasi pencatatan produksi usaha kecil menengah](/blog/aplikasi-pencatatan-produksi-usaha-kecil-menengah) untuk kebutuhan yang lebih spesifik itu.
MD,
            ],
            [
                'slug' => 'cara-mengelola-stok-kosmetik-agar-tidak-kadaluarsa',
                'title' => 'Cara Mengelola Stok Produk Kosmetik agar Tidak Kadaluarsa Sebelum Terjual',
                'category' => 'Manajemen Stok',
                'tags' => ['Kosmetik', 'Stok Bahan Baku', 'UMKM'],
                'days_ago' => 4,
                'excerpt' => 'Kosmetik kadaluarsa bukan cuma kerugian modal, tapi juga risiko keamanan buat pelanggan. Ini cara mengelola stok kosmetik rumahan supaya tidak ada yang tertinggal sampai lewat tanggal aman pakai.',
                'meta_title' => 'Cara Mengelola Stok Produk Kosmetik agar Tidak Kadaluarsa',
                'meta_description' => 'Cara mengelola stok produk kosmetik rumahan agar tidak kadaluarsa: FEFO per batch, minimum stock per varian, dan cara memantau tanggal expired secara rutin.',
                'content' => <<<'MD'
Usaha kosmetik dan skincare rumahan punya satu risiko yang tidak dimiliki kebanyakan produk lain: barangnya dipakai langsung ke kulit dan wajah pelanggan. Kalau produk fashion yang telat terjual paling banter cuma ketinggalan tren, kosmetik yang terjual lewat tanggal aman pakai bisa berarti risiko iritasi, komplain, sampai masalah reputasi yang jauh lebih mahal daripada nilai stoknya sendiri.

## Kenapa Stok Kosmetik Butuh Perhatian Ekstra Dibanding Produk Lain

Kosmetik biasanya punya masa simpan yang jauh lebih pendek dibanding produk fashion atau kerajinan, apalagi untuk formula tanpa pengawet berat seperti skincare organik atau produk racikan sendiri. Selain itu, satu produk sering punya banyak varian — shade, ukuran kemasan, batch produksi — yang masing-masing punya tanggal kadaluarsa berbeda meski nama produknya sama. Kalau semua varian ini dianggap satu stok besar tanpa dipisah per batch, sangat mudah kehilangan jejak batch mana yang paling dekat kadaluarsa.

## Cara Mengelola Stok Kosmetik agar Tidak Kadaluarsa

### 1. Terapkan FEFO, Bukan Sekadar FIFO

FEFO (First Expired, First Out) berarti produk dengan tanggal kadaluarsa paling dekat dikeluarkan lebih dulu, terlepas dari kapan produk itu masuk gudang. Ini beda dengan FIFO biasa yang hanya berdasarkan urutan masuk — untuk kosmetik, dua batch yang masuk di waktu berbeda bisa saja punya umur simpan yang berbeda jauh tergantung tanggal produksinya, jadi acuan yang benar adalah tanggal kadaluarsa, bukan tanggal terima.

### 2. Catat Tanggal Kadaluarsa per Batch, Bukan per Produk

Simpan tanggal kadaluarsa di level batch atau item, bukan hanya di level nama produk. Dua batch lipstik warna yang sama bisa punya tanggal kadaluarsa berbeda tergantung kapan diproduksi atau diterima dari supplier — kalau dicatat satu angka saja untuk semua stok, batch yang lebih tua gampang tersembunyi di balik batch baru.

### 3. Buat Minimum Stock per Varian, Bukan per Kategori Produk

Karena kosmetik biasanya punya banyak varian shade dan ukuran, tetapkan minimum stock untuk tiap varian secara terpisah. Varian warna yang kurang laku tidak perlu stok sebanyak varian best-seller — menyamaratakan minimum stock untuk semua varian sering berujung varian yang jarang laku justru menumpuk sampai kadaluarsa duluan.

### 4. Review Tanggal Kadaluarsa Secara Rutin, Bukan Cuma Saat Stock Opname Tahunan

Idealnya, produk dengan sisa masa simpan di bawah 2-3 bulan ditandai untuk diprioritaskan dijual, misalnya lewat promo atau bundling, sebelum benar-benar mendekati tanggal kadaluarsa. Kalau baru disadari saat sudah H-1 minggu dari expired, pilihan yang tersisa biasanya cuma dua: jual rugi besar-besaran atau buang total.

## Contoh Sederhana Melacak Batch Kosmetik

| Produk | Kode Batch | Tanggal Masuk | Kadaluarsa | Sisa Stok |
|---|---|---|---|---|
| Serum Vitamin C 30ml | SV-0124 | 2 Jan 2026 | 2 Jan 2027 | 15 |
| Serum Vitamin C 30ml | SV-0524 | 10 Mei 2026 | 10 Mei 2027 | 40 |
| Lipstik Matte No. 12 | LM-0324 | 5 Mar 2026 | 5 Sep 2026 | 8 |

Dari tabel ini kelihatan jelas: meski total stok Serum Vitamin C ada 55 pcs, batch SV-0124 harus dijual duluan karena kadaluarsa lebih dekat. Lipstik Matte No. 12 malah butuh perhatian paling mendesak karena masa simpannya jauh lebih pendek dan sisa stoknya sedikit — kalau tidak diprioritaskan terjual bulan-bulan ini, risiko kadaluarsanya paling tinggi dari ketiga baris di atas.

## Apa yang Terjadi kalau Kosmetik Kadaluarsa Tetap Terjual

Selain risiko komplain dan iritasi pelanggan, menjual kosmetik yang sudah lewat tanggal aman pakai juga berisiko dari sisi legal, terutama untuk produk yang terdaftar BPOM. Reputasi yang rusak karena satu kejadian seperti ini biasanya jauh lebih sulit dipulihkan dibanding sekadar menanggung rugi modal dari stok yang harus dimusnahkan lebih awal.

Fabriku mencatat tanggal kadaluarsa per item stok dan menandai item yang tanggalnya sudah lewat dengan status "expired", jadi produk yang mendekati kadaluarsa bisa dipantau lebih awal sebelum jadi masalah di tangan pelanggan. Lihat juga [Fabriku untuk kosmetik dan skincare](/aplikasi/kosmetik-skincare).

Prinsip dasar pengelolaan stok di atas — minimum stock per varian, pencatatan yang konsisten, stock opname rutin — berlaku juga untuk bahan baku UMKM secara umum. Kalau ingin melihat gambaran lebih lengkap, baca [cara mengelola stok bahan baku UMKM agar tidak rugi](/blog/cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi). Untuk reseller dan dropshipper, ada [cara kelola stok reseller dan dropship kosmetik](/blog/cara-kelola-stok-reseller-dropship-kosmetik).
MD,
            ],
            [
                'slug' => 'cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi',
                'title' => 'Cara Menghitung Harga Jasa Servis Kecil agar Tidak Rugi (Bengkel, Laundry, Reparasi)',
                'category' => 'Keuangan',
                'tags' => ['Jasa Servis', 'Keuangan UMKM', 'UMKM'],
                'days_ago' => 2,
                'excerpt' => 'Usaha jasa seperti bengkel atau reparasi elektronik sering asal pasang tarif tanpa hitung biaya sebenarnya. Ini cara menghitung harga jasa servis kecil supaya untung, bukan sekadar ramai order.',
                'meta_title' => 'Cara Menghitung Harga Jasa Servis Kecil agar Tidak Rugi',
                'meta_description' => 'Cara menghitung HPP jasa dan harga jasa servis kecil (bengkel, laundry, reparasi elektronik) agar tidak rugi: rumus, komponen biaya, dan contoh tarif.',
                'content' => <<<'MD'
Usaha jasa seperti bengkel motor, reparasi elektronik, atau laundry punya tantangan hitung-hitungan yang beda dari usaha yang jual barang. Tidak ada bahan baku yang diubah jadi produk, tidak ada stok barang jadi — yang ada cuma waktu kerja, bahan pendukung yang habis pakai, dan alat yang dipakai berulang kali. Karena tidak ada "HPP produk" yang jelas seperti usaha manufaktur, banyak pemilik usaha jasa menentukan tarif dari kebiasaan atau menyamakan dengan kompetitor, tanpa benar-benar tahu apakah tarif itu menutup biaya sebenarnya.

## Kenapa Usaha Jasa Sering Salah Hitung Harga

Kesalahan paling umum adalah hanya menghitung harga sparepart atau bahan pendukung yang terpakai, lalu menambahkan angka "ongkos jasa" yang sebenarnya ditebak, bukan dihitung dari waktu kerja dan biaya operasional yang sesungguhnya. Akibatnya, servis yang makan waktu lama justru dihargai sama dengan servis cepat, padahal biaya waktu kerjanya jauh berbeda.

## Komponen Biaya yang Wajib Dihitung dalam Harga Jasa

### 1. Waktu Kerja

Hitung berapa lama rata-rata satu jenis servis dikerjakan, lalu kalikan dengan tarif per jam tenaga kerja (upah sendiri atau karyawan). Servis yang butuh 30 menit dan yang butuh 3 jam jelas tidak bisa dihargai dengan ongkos jasa yang sama.

### 2. Bahan Pendukung atau Consumable

Selain sparepart utama yang jelas terlihat di nota, banyak servis juga memakai bahan pendukung yang habis terpakai — pelumas, lem, deterjen, bahan pembersih. Bahan-bahan ini sering terlewat dari perhitungan karena nilainya kecil per transaksi, tapi kalau diakumulasi dalam sebulan jumlahnya bisa cukup berarti.

### 3. Biaya Alat dan Overhead

Alat yang dipakai berulang kali — kompresor, mesin cuci, solder — tetap mengalami penyusutan dan makan listrik setiap dipakai. Biaya ini perlu dialokasikan ke tiap transaksi servis, meski nilainya kecil per satu kali pakai.

### 4. Margin Keuntungan

Setelah tiga komponen di atas dijumlahkan, tambahkan margin yang jadi keuntungan usaha. Tanpa margin yang jelas, usaha jasa bisa terus beroperasi hanya menutup biaya, tanpa benar-benar menghasilkan laba yang bisa dipakai berkembang.

## Cara Menghitung HPP Jasa

HPP jasa adalah biaya langsung untuk mengerjakan satu layanan, sebelum ditambah keuntungan. Tidak ada bahan baku yang berubah jadi produk, jadi komponennya adalah tiga yang pertama di atas:

**HPP jasa = (waktu kerja × tarif tenaga kerja per jam) + bahan pendukung + biaya alat**

**Harga jasa = HPP jasa × (1 + markup)**

Sparepart atau barang yang ikut diganti tidak masuk HPP jasa. Barang itu punya HPP sendiri (harga beli) dan ditagih terpisah. Memisahkan keduanya membuat Anda tahu apakah usaha untung dari jasanya atau hanya dari jualan sparepart. Contoh di bawah memakai rumus ini.

## Contoh Perhitungan: Servis Ganti Oli dan Tune Up Motor

Misalnya sebuah bengkel menghitung harga jasa tune up motor: waktu kerja 45 menit dengan tarif tenaga kerja Rp40.000/jam (setara Rp30.000), bahan pendukung seperti cairan pembersih karburator dan majun Rp10.000, biaya listrik dan penyusutan alat kompresor diperkirakan Rp5.000. HPP jasa tune up: Rp45.000.

Kalau bengkel ingin markup 50% di atas biaya, harga jasa minimal yang masuk akal adalah Rp45.000 + (50% × Rp45.000) = Rp67.500, dibulatkan jadi Rp70.000 di luar harga sparepart yang diganti (oli, busi, dll dihitung terpisah sesuai harga beli). Kalau selama ini dipatok Rp50.000 rata dengan bengkel sebelah tanpa hitungan ini, marginnya jauh lebih tipis dari yang dikira, bahkan bisa nombok kalau servisnya ternyata makan waktu lebih lama dari biasanya.

## Daftar Tarif Berdasarkan Tingkat Kesulitan

Cara paling praktis adalah membuat daftar tarif per jenis layanan, bukan satu tarif rata. Contoh untuk bengkel motor dengan tarif tenaga kerja Rp40.000/jam dan markup 50%:

| Layanan | Waktu | Bahan pendukung | Biaya alat | Total biaya | Tarif (dibulatkan) |
|---|---|---|---|---|---|
| Ganti oli | 15 menit | Rp2.000 | Rp1.000 | Rp13.000 | Rp20.000 |
| Tune up | 45 menit | Rp10.000 | Rp5.000 | Rp45.000 | Rp70.000 |
| Servis CVT | 60 menit | Rp15.000 | Rp5.000 | Rp60.000 | Rp90.000 |
| Turun mesin | 6 jam | Rp40.000 | Rp20.000 | Rp300.000 | Rp450.000 |

Harga sparepart yang diganti (oli, busi, kampas) ditagih terpisah sesuai harga jual sparepart. Pemisahan ini membuat pelanggan tahu apa yang mereka bayar, dan membuat Anda tahu berapa pendapatan dari jasa dan berapa dari sparepart.

## Contoh Lain: Laundry Kiloan dan Reparasi HP

**Laundry kiloan (per kg):** deterjen dan pewangi Rp1.200, listrik mesin cuci dan pengering Rp1.500, air Rp300, plastik Rp500, waktu kerja 6 menit per kg (upah Rp15.000/jam) Rp1.500, penyusutan mesin Rp500. Total Rp5.500/kg. Dengan markup 45%, tarif menjadi sekitar Rp8.000/kg.

**Reparasi HP ganti LCD:** waktu kerja 1 jam dengan tarif teknisi Rp35.000, lem dan alat habis pakai Rp8.000, biaya alat Rp4.000. Total biaya jasa Rp47.000, tarif jasa Rp70.000 di luar harga LCD. Untuk pekerjaan berisiko seperti membuka HP dengan bodi lem, tambahkan cadangan risiko kerusakan sekitar 5–10% dari tarif.

## Jangan Lupa Waktu Tidak Produktif

Teknisi tidak bekerja 8 jam penuh setiap hari. Ada waktu menunggu pelanggan, membersihkan tempat kerja, dan membeli sparepart. Jika rata-rata hanya 6 dari 8 jam yang benar-benar dipakai untuk servis, tarif per jam harus dihitung dari 6 jam tersebut: upah harian Rp240.000 ÷ 6 jam = Rp40.000/jam, bukan Rp30.000/jam.

## Kesalahan Umum yang Bikin Usaha Jasa Rugi Diam-Diam

Kesalahan paling sering: menyamakan tarif untuk semua tingkat kesulitan servis, padahal waktu dan bahan yang terpakai jauh berbeda. Kesalahan lain adalah tidak pernah meninjau ulang tarif meski harga bahan pendukung sudah naik, sehingga margin tergerus pelan-pelan tanpa disadari — mirip dengan kasus HPP produk yang tidak pernah dihitung ulang.

## Kapan Tarif Jasa Perlu Ditinjau Ulang

Tinjau ulang tarif setiap kali ada kenaikan signifikan pada harga bahan pendukung atau upah tenaga kerja. Untuk usaha yang bahan pendukungnya sering naik-turun harga, meninjau tarif tiap 2-3 bulan lebih aman daripada menunggu sampai terasa rugi baru disadari.

## Pertanyaan yang Sering Muncul

**Apakah tarif boleh berbeda dengan bengkel sebelah?**
Boleh. Tarif yang lebih tinggi bisa dijelaskan dengan garansi servis, kecepatan, atau kualitas pengerjaan. Yang berbahaya justru tarif lebih murah yang tidak menutup biaya.

**Bagaimana menghitung tarif jika teknisi dibayar komisi?**
Masukkan komisi sebagai biaya tenaga kerja per layanan. Misalnya komisi 30% dari tarif jasa, maka tarif harus cukup untuk menutup komisi, bahan, alat, dan tetap menyisakan laba.

**Perlukah tarif dicantumkan di tempat usaha?**
Sangat disarankan. Daftar tarif yang jelas mengurangi tawar-menawar dan membuat semua staf memasang harga yang sama.

Untuk kategori usaha jasa, Fabriku menyediakan [katalog layanan untuk bengkel dan jasa servis](/aplikasi/bengkel-jasa-servis) tanpa perlu modul material atau produksi, lengkap dengan pemetaan bahan pendukung per layanan yang otomatis terpotong dari stok setiap kali transaksi kasir cepat disimpan — jadi biaya bahan pendukung tidak lagi terlewat dari perhitungan. Bahan pendukungnya sendiri bisa dilacak lewat [cara melacak sparepart dan bahan habis pakai jasa servis](/blog/cara-melacak-sparepart-bahan-habis-pakai-jasa-servis).

Logika menghitung komponen biaya di atas sebenarnya sama dengan cara menghitung HPP produk fisik, hanya komponennya yang berbeda — lihat [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm) untuk pembahasan yang lebih umum. Kalau tarifnya dihitung per jam kerja, atur dulu [jadwal teknisi supaya tidak bentrok](/blog/cara-mengatur-jadwal-teknisi-bengkel-servis-agar-tidak-bentrok).
MD,
            ],
            [
                'slug' => 'cara-mencatat-menagih-piutang-pelanggan-umkm',
                'title' => 'Cara Mencatat dan Menagih Piutang Pelanggan UMKM agar Tidak Macet',
                'category' => 'Keuangan',
                'tags' => ['Piutang', 'Keuangan UMKM', 'UMKM'],
                'days_ago' => 0,
                'excerpt' => 'Omzet terlihat bagus di atas kertas, tapi kas menipis? Bisa jadi piutang yang menumpuk tanpa dipantau. Ini cara mencatat dan menagih piutang pelanggan UMKM tanpa merusak hubungan baik.',
                'meta_title' => 'Cara Mencatat dan Menagih Piutang Pelanggan UMKM',
                'meta_description' => 'Cara mencatat dan menagih piutang pelanggan UMKM agar tidak macet: kolom wajib pencatatan, jadwal tagih, dan cara menagih tanpa merusak hubungan pelanggan.',
                'content' => <<<'MD'
Salah satu jebakan paling umum di UMKM adalah merasa penjualan sedang bagus karena omzet tercatat tinggi, padahal sebagian besar dari angka itu masih berupa piutang yang belum benar-benar masuk ke kas. Semakin longgar syarat pembayaran diberikan ke pelanggan — DP, cicilan, tempo — semakin penting piutang ini dipantau ketat, karena di atas kertas terlihat untung, tapi uang tunainya belum tentu ada saat dibutuhkan.

## Kenapa Piutang yang Tidak Dipantau Berbahaya buat Arus Kas

Piutang pada dasarnya adalah uang usaha yang "dipinjamkan" ke pelanggan dalam bentuk barang atau jasa yang sudah diserahkan. Selama piutang belum tertagih, usaha tetap harus menanggung biaya operasional dari kas yang ada, meski sebagian pendapatan sudah "tercatat" sebagai omzet. Kalau piutang menumpuk tanpa jadwal tagih yang jelas, usaha bisa terjebak kekurangan kas justru di saat penjualan sedang ramai-ramainya.

## Cara Mencatat Piutang Pelanggan dengan Rapi

Kolom minimal yang wajib ada dalam catatan piutang:

- **Nama pelanggan** — supaya jelas piutang siapa yang harus ditagih.
- **Tanggal transaksi** — kapan barang/jasa diserahkan.
- **Total tagihan** — nilai transaksi keseluruhan.
- **Jumlah yang sudah dibayar** — termasuk DP atau cicilan yang sudah masuk.
- **Sisa piutang** — total tagihan dikurangi yang sudah dibayar.
- **Tanggal jatuh tempo** — batas waktu pelunasan yang disepakati.
- **Status** — lunas, sebagian (partial), atau belum dibayar sama sekali.

Kolom tanggal jatuh tempo dan status ini yang paling sering terlewat, padahal justru dua kolom inilah yang menentukan kapan sebuah piutang harus mulai ditagih.

## Cara Menagih Piutang Tanpa Merusak Hubungan dengan Pelanggan

### 1. Ingatkan Sebelum Jatuh Tempo, Bukan Sesudah

Kirim pengingat 2-3 hari sebelum tanggal jatuh tempo, bukan menunggu sampai lewat baru menghubungi. Pengingat sebelum jatuh tempo terasa sebagai informasi biasa, sementara penagihan setelah telat cenderung terasa seperti komplain.

### 2. Kelompokkan Piutang Berdasarkan Umur

Pisahkan piutang yang baru jatuh tempo minggu ini, yang sudah telat 1-2 minggu, dan yang sudah telat lebih dari sebulan. Piutang yang sudah lama telat butuh pendekatan berbeda dan prioritas lebih tinggi dibanding yang baru saja lewat tanggal.

### 3. Buat Kesepakatan Tertulis di Awal, Bukan Lisan Saja

Kesepakatan syarat pembayaran — berapa DP, kapan pelunasan, konsekuensi kalau telat — sebaiknya dicatat tertulis (bisa lewat chat) sejak transaksi awal. Ini mengurangi kesalahpahaman saat proses penagihan berlangsung.

## Contoh Kasus: Membaca Daftar Piutang untuk Prioritas Tagih

| Pelanggan | Total Tagihan | Sudah Dibayar | Sisa Piutang | Jatuh Tempo | Status |
|---|---|---|---|---|---|
| Toko Mawar | Rp3.000.000 | Rp1.000.000 | Rp2.000.000 | 5 Agt | Sebagian |
| Bu Sari | Rp750.000 | Rp0 | Rp750.000 | 20 Jul | Belum Bayar |
| CV Berkah | Rp5.000.000 | Rp5.000.000 | Rp0 | 1 Agt | Lunas |

Dari tabel ini, piutang Bu Sari yang paling mendesak ditagih karena sudah lewat jatuh tempo cukup lama, meski nilainya lebih kecil dari piutang Toko Mawar. Kalau hanya melihat nilai piutang terbesar tanpa memperhatikan tanggal jatuh tempo, penagihan bisa salah prioritas dan piutang yang sudah lama macet malah terlewat.

## Kesalahan yang Sering Terjadi

Kesalahan paling umum adalah tidak mencatat piutang sama sekali secara terpisah, melainkan mencampurnya dengan catatan omzet biasa — sehingga pemilik usaha baru sadar ada piutang menumpuk setelah kas benar-benar terasa seret. Kesalahan lain adalah menunda penagihan karena sungkan, padahal semakin lama ditunda, semakin besar kemungkinan piutang itu berubah jadi piutang macet yang sulit ditagih sama sekali.

Fabriku mencatat setiap pembayaran sebagai entri di ledger pesanan, sehingga status pembayaran (lunas, sebagian, belum bayar) dan sisa piutang tiap pelanggan otomatis terlihat tanpa perlu rekap manual terpisah.

Piutang yang menumpuk sering lebih terasa di usaha yang jualan lewat banyak channel sekaligus, karena syarat pembayaran tiap channel bisa berbeda — lihat [cara mengelola pesanan online dan offline sekaligus](/blog/cara-mengelola-pesanan-online-offline-sekaligus) untuk gambaran lebih lengkap soal ini.
MD,
            ],
            [
                'slug' => 'cara-menentukan-harga-jual-produk-kerajinan-handmade',
                'title' => 'Cara Menentukan Harga Jual Produk Kerajinan Tangan Handmade agar Untung',
                'category' => 'Keuangan',
                'tags' => ['Kerajinan', 'Harga Jual', 'UMKM'],
                'days_ago' => 2,
                'excerpt' => 'Harga jual produk handmade yang cuma "ikut-ikutan" harga kompetitor sering bikin pengrajin rugi tanpa sadar, karena jam kerja dan skill tidak pernah dihitung sebagai biaya.',
                'meta_title' => 'Cara Menentukan Harga Jual Produk Kerajinan Handmade',
                'meta_description' => 'Cara menentukan harga jual produk kerajinan tangan handmade agar untung: komponen biaya yang wajib dihitung, rumus sederhana, dan contoh perhitungannya.',
                'content' => <<<'MD'
Banyak pengrajin menentukan harga jual produk handmade dengan cara yang sama: lihat harga produk sejenis di marketplace, lalu pasang harga sedikit di bawahnya supaya "lebih laku". Cara ini terasa aman di awal, tapi sering berujung rugi diam-diam karena biaya yang sebenarnya dikeluarkan — terutama jam kerja dan skill — tidak pernah masuk hitungan.

## Kenapa Produk Handmade Sering Salah Harga

Beda dari produk pabrikan yang biaya produksinya relatif tetap dan bisa dihitung per unit dengan mudah, produk kerajinan tangan punya variabel yang lebih personal: waktu pengerjaan berbeda-beda tergantung kerumitan desain, bahan yang dipakai kadang sisa atau tidak standar, dan skill yang dibutuhkan tidak selalu bisa disamakan dengan upah buruh pabrik biasa. Karena sulit dihitung, banyak pengrajin akhirnya melewatkan komponen ini sama sekali dan hanya menghitung harga bahan baku saja.

## Komponen Biaya yang Wajib Dihitung

1. **Biaya bahan baku** — termasuk bahan sisa yang dipakai, karena bahan sisa tetap punya nilai meski didapat gratis atau murah dari sisa produksi lain.
2. **Waktu pengerjaan (jam kerja)** — hitung berapa jam yang dibutuhkan untuk satu produk, lalu kalikan dengan upah per jam yang wajar, bukan Rp0 hanya karena "dikerjakan sendiri".
3. **Tingkat kerumitan/skill** — desain yang butuh keahlian lebih tinggi atau proses lebih rumit pantas dihargai lebih dari produk yang pengerjaannya sederhana.
4. **Biaya alat dan penyusutan** — jarum, lem tembak, mesin jahit kecil, atau alat lain yang aus dipakai berulang kali tetap merupakan biaya, meski tidak terasa langsung per produk.
5. **Kemasan dan biaya kegagalan** — termasuk produk yang gagal di tengah proses dan bahannya tidak bisa dipakai lagi, karena biaya itu sebenarnya ditanggung oleh produk yang berhasil terjual.

## Rumus Sederhana Menghitung Harga Jual

**Harga Jual = (Biaya Bahan + Upah Waktu Kerja + Overhead) + Margin Keuntungan**

Contoh: seorang pengrajin membuat satu gelang manik-manik dengan biaya bahan Rp8.000, waktu pengerjaan 45 menit dengan upah yang ditetapkan Rp20.000/jam (setara Rp15.000 untuk 45 menit), dan overhead alat diperkirakan Rp2.000 per produk. Total biaya produksi: Rp8.000 + Rp15.000 + Rp2.000 = Rp25.000.

Kalau pengrajin ingin markup 40% di atas biaya, harga jual minimal adalah Rp25.000 + (40% × Rp25.000) = **Rp35.000** (margin terhadap harga jual sekitar 28,6%). Bandingkan dengan kalau harga hanya ditentukan dari "harga bahan plus sedikit untung" tanpa menghitung waktu kerja — misalnya dijual Rp15.000 karena dikira sudah untung dari modal bahan Rp8.000. Padahal setelah dihitung lengkap, harga segitu justru menombok waktu kerja sebesar Rp17.000 per gelang, yang berarti setiap gelang terjual justru mengurangi "gaji" yang seharusnya didapat pengrajin.

## Contoh Lengkap: Tas Rajut Custom

Contoh kedua dengan produk yang lebih rumit — tas rajut custom dengan motif pesanan pelanggan:

| Komponen | Rincian | Biaya |
|---|---|---|
| Benang katun | 4 gulung × Rp22.000 | Rp88.000 |
| Furing, resleting, label | — | Rp27.000 |
| Waktu kerja | 10 jam × Rp20.000 | Rp200.000 |
| Waktu desain motif custom | 1,5 jam × Rp30.000 | Rp45.000 |
| Penyusutan alat & listrik | — | Rp5.000 |
| Kemasan & kartu ucapan | — | Rp10.000 |
| **Total biaya** | | **Rp375.000** |

Dengan markup 50% untuk nilai custom dan skill, harga jualnya **Rp562.500**, dibulatkan Rp560.000. Angka ini mungkin terasa mahal dibanding tas rajut massal di marketplace, tapi produknya memang berbeda: dibuat satu per satu sesuai pesanan. Jika pelanggan menawar, rincian di atas membantu memutuskan bagian mana yang bisa disesuaikan — misalnya mengganti benang yang lebih murah — tanpa memotong upah sendiri.

## Hitung Waktu Kerja dengan Jujur

Cara paling mudah: catat jam mulai dan selesai untuk lima produk pertama, lalu ambil rata-ratanya. Jangan hanya menghitung waktu merangkai atau menjahit. Waktu memilih bahan, memotong, finishing, foto produk, dan mengemas juga bagian dari pembuatan produk.

Untuk menentukan upah per jam, gunakan acuan upah minimum daerah Anda dibagi jam kerja sebulan (sekitar 173 jam), lalu sesuaikan dengan tingkat keahlian. Pengrajin yang sudah bertahun-tahun pantas memasang tarif di atas upah minimum.

## Harga untuk Reseller dan Bazar

Jika produk juga dijual lewat reseller, harga jual eceran harus menyisakan ruang diskon untuk reseller (biasanya 20–30%) **tanpa** turun di bawah biaya. Dari contoh gelang di atas: harga eceran Rp35.000 dengan diskon reseller 25% berarti reseller membeli Rp26.250. Pengrajin masih untung Rp1.250 per gelang — tipis sekali. Jika ingin aktif menjual lewat reseller, naikkan markup eceran, misalnya ke 70% (Rp42.500), agar setelah diskon reseller pengrajin tetap mendapat sekitar Rp6.900 per gelang.

Untuk bazar atau pameran, masukkan biaya sewa stan dibagi perkiraan jumlah produk terjual sebagai biaya tambahan per unit selama acara tersebut.

## Kerajinan Tangan Boleh Dihargai Lebih dari Sekadar HPP

Berbeda dari produk pabrikan yang harga jualnya cenderung mepet dengan HPP karena bersaing di volume, produk handmade punya nilai tambah dari keunikan, cerita di balik pembuatannya, dan edisi terbatas yang tidak bisa ditiru massal. Nilai tambah ini sah untuk dimasukkan ke margin yang lebih tinggi dari sekadar "HPP plus untung tipis" — asal tetap berangkat dari perhitungan biaya yang akurat, bukan tebakan.

## Kesalahan Umum Menentukan Harga Produk Handmade

Kesalahan paling sering adalah menghargai jam kerja sendiri sebagai Rp0 karena dianggap "hobi" atau "dikerjakan pas senggang", padahal waktu itu bisa dipakai untuk hal lain yang menghasilkan. Kesalahan lain adalah ikut-ikutan harga kompetitor tanpa tahu apakah kompetitor itu sendiri sudah menghitung biayanya dengan benar — bisa jadi kompetitor juga sama-sama rugi tanpa sadar, dan mengikuti harganya berarti ikut menjebak diri sendiri di harga yang tidak sehat.

Sebelum menentukan harga jual, ada baiknya juga menata dulu bagaimana [bahan baku kerajinan yang tidak standar](/blog/cara-mengelola-bahan-baku-kerajinan-tidak-standar) — seperti sisa kain, manik lepasan, atau potongan kayu — dicatat dan dihitung nilainya, karena tanpa itu komponen biaya bahan di atas hanya jadi perkiraan kasar. Untuk pembahasan HPP yang lebih mendalam di luar konteks kerajinan tangan, lihat juga [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm). Perbedaan markup dan margin dijelaskan di [cara menentukan harga jual produk](/blog/cara-menentukan-harga-jual-produk-markup-vs-margin).

## Pertanyaan yang Sering Muncul

**Berapa markup yang wajar untuk produk handmade?**
Tidak ada angka baku, tapi banyak pengrajin memakai markup 50–100% di atas total biaya (termasuk upah). Produk custom dan edisi terbatas wajar di atas itu.

**Bagaimana jika harga hasil hitungan jauh di atas kompetitor?**
Periksa dulu apakah ada komponen yang bisa dibuat lebih efisien, misalnya membeli bahan dalam jumlah lebih besar. Jika tidak, jelaskan nilai produk Anda (bahan, proses, kustomisasi) alih-alih menurunkan harga di bawah biaya.

**Apakah bahan sisa perlu dihitung?**
Perlu. Bahan sisa yang dipakai ulang tetap punya nilai sebesar harga belinya dulu.

Fabriku mendukung pencatatan biaya per produk untuk [usaha kerajinan dan handmade](/aplikasi/kerajinan-handmade), termasuk batch kecil dan produksi custom order, sehingga harga jual bisa ditentukan dari data biaya yang sebenarnya, bukan sekadar mengikuti harga pasar.
MD,
            ],
            [
                'slug' => 'cara-mengelola-bahan-baku-kerajinan-tidak-standar',
                'title' => 'Cara Mengelola Bahan Baku Kerajinan yang Tidak Standar (Sisa Kain, Manik, Kayu)',
                'category' => 'Manajemen Stok',
                'tags' => ['Kerajinan', 'Stok Bahan Baku', 'UMKM'],
                'days_ago' => 4,
                'excerpt' => 'Bahan baku kerajinan jarang seragam seperti di pabrik — sisa kain, manik lepasan, potongan kayu sering bikin bingung dicatat pakai sistem stok biasa. Ini cara mengelolanya.',
                'meta_title' => 'Cara Mengelola Bahan Baku Kerajinan yang Tidak Standar',
                'meta_description' => 'Cara mengelola bahan baku kerajinan yang tidak standar seperti sisa kain, manik lepasan, dan potongan kayu, supaya tidak hilang atau tercecer di gudang.',
                'content' => <<<'MD'
Usaha kerajinan tangan punya masalah stok yang khas: bahan bakunya sering tidak seragam. Sisa kain dari proyek sebelumnya, manik-manik lepasan dengan warna campur, potongan kayu sisa gergaji — semua ini sulit dicatat dengan cara stok biasa yang biasanya mengasumsikan satu bahan punya satu satuan dan satu harga yang konsisten.

## Kenapa Bahan Kerajinan Susah Dicatat dengan Cara Biasa

Sistem pencatatan stok konvensional biasanya berasumsi: satu jenis bahan, satu satuan ukur, kuantitas yang jelas. Masalahnya, bahan kerajinan sering melanggar semua asumsi itu sekaligus. Sisa kain bisa berukuran tidak beraturan, manik-manik lepasan datang dalam campuran warna dan ukuran dalam satu wadah, dan potongan kayu sisa punya bentuk yang tidak identik satu sama lain. Kalau dipaksa dicatat dengan cara yang sama seperti bahan baku pabrik biasa, hasilnya justru pencatatan yang tidak akurat dan akhirnya ditinggalkan karena dianggap merepotkan.

## Cara Praktis Mengelola Bahan Baku Tidak Standar

### 1. Kelompokkan per Jenis dengan Satuan yang Masuk Akal

Jangan paksa semua bahan pakai satuan yang sama. Sisa kain bisa dicatat per lembar atau per perkiraan ukuran (kecil/sedang/besar), manik-manik bisa dicatat per wadah atau per gram, potongan kayu per pcs dengan catatan ukuran perkiraan. Yang penting satuannya konsisten untuk jenis bahan yang sama, bukan dipaksa seragam antar jenis bahan yang berbeda karakter.

### 2. Catat Bahan Sisa sebagai Item Terpisah, Jangan Dibuang ke "Sisa Gudang"

Sisa produksi sebelumnya sering ditumpuk begitu saja di sudut gudang tanpa dicatat, dengan anggapan "nanti juga kepakai". Padahal untuk usaha kerajinan, sisa ini justru sering jadi bahan baku produk lain — potongan kain sisa jadi bahan tempel untuk produk campuran, misalnya. Catat sisa ini sebagai item stok sendiri, lengkap dengan perkiraan jumlah dan dari proyek mana asalnya.

### 3. Beri Kode atau Label per Batch Warna/Motif

Karena bahan kerajinan sering tidak bisa di-restock identik — warna manik dari supplier bisa sedikit berbeda tiap kedatangan, motif kain bisa habis dan tidak tersedia lagi — beri label batch yang jelas. Ini penting supaya saat membuat produk yang butuh kombinasi warna/motif konsisten, pengrajin tahu persis batch mana yang tersisa dan cukup untuk berapa produk.

### 4. Pisahkan Bahan "Siap Pakai" dari Bahan "Perlu Diproses Dulu"

Kayu mentah yang belum dipotong beda statusnya dari kayu yang sudah jadi potongan siap rakit. Kalau dicampur dalam satu catatan stok yang sama, sulit tahu berapa sebenarnya bahan yang benar-benar siap dipakai untuk produksi hari itu.

### 5. Lakukan Stock Opname Lebih Sering dari Bahan Standar

Karena ukurannya kecil dan mudah tercecer atau terselip, bahan seperti manik-manik dan potongan kecil kayu lebih rawan "hilang" tanpa disadari dibanding bahan baku dalam ukuran besar. Cek fisik lebih sering — misalnya tiap minggu — membantu menangkap selisih sebelum menumpuk jadi besar.

## Contoh Kasus: Pengrajin Aksesoris Manik-Manik

Seorang pengrajin gelang dan kalung biasanya menerima manik dalam bungkusan campuran warna dari supplier. Tanpa pencatatan per batch, begitu satu bungkusan habis dan diganti bungkusan baru dengan corak warna sedikit berbeda, pengrajin baru sadar setelah produk selesai dibuat bahwa warnanya tidak konsisten dengan produk sebelumnya — sesuatu yang bisa jadi masalah kalau pelanggan memesan produk kembar atau set yang seragam.

Dengan pencatatan per batch, pengrajin bisa langsung tahu batch mana yang masih tersisa dan cukup dipakai untuk pesanan yang butuh keseragaman warna, tanpa harus membongkar semua stok manik secara fisik satu per satu.

## Kesalahan yang Sering Terjadi

Kesalahan paling umum: sisa bahan kecil dianggap "tidak berharga" dan dibuang atau dicampur begitu saja tanpa dicatat, padahal justru sisa-sisa inilah yang sering jadi bahan produk kombinasi atau edisi terbatas yang punya nilai jual sendiri. Kesalahan lain adalah menyamaratakan semua bahan dalam satu catatan umum "bahan kerajinan" tanpa rincian jenis, sehingga sulit tahu stok riil per jenis bahan saat dibutuhkan mendadak.

Sebelum bahan-bahan ini sampai jadi produk jadi, ada baiknya juga membaca [cara menentukan harga jual produk kerajinan handmade](/blog/cara-menentukan-harga-jual-produk-kerajinan-handmade) supaya biaya bahan yang sudah dicatat rapi ini benar-benar dipakai saat menghitung harga jual, bukan sekadar arsip stok yang tidak pernah dianalisis lagi.

Fabriku memungkinkan kategori bisnis kerajinan mencatat bahan baku dengan atribut fleksibel seperti warna, ukuran, dan nomor batch per item, jadi bahan yang tidak standar tetap bisa dilacak dengan rapi tanpa dipaksa masuk format pencatatan pabrik biasa.
MD,
            ],
            [
                'slug' => 'cara-kelola-stok-retur-barang-umkm-retail',
                'title' => 'Cara Mengelola Stok Retur Barang UMKM Retail agar Tidak Bikin Selisih',
                'category' => 'Manajemen Stok',
                'tags' => ['Retail', 'Retur Barang', 'UMKM'],
                'days_ago' => 6,
                'excerpt' => 'Retur barang sering dianggap urusan kecil dan dicatat asal-asalan, padahal ini salah satu penyebab paling umum selisih stok fisik dan stok di sistem untuk UMKM retail.',
                'meta_title' => 'Cara Mengelola Stok Retur Barang UMKM Retail',
                'meta_description' => 'Cara mengelola stok retur barang UMKM retail agar tidak bikin selisih: jenis retur yang perlu dibedakan, langkah pencatatan, dan kesalahan yang sering terjadi.',
                'content' => <<<'MD'
Di toko retail, retur barang dari pelanggan hampir pasti terjadi — entah karena salah ukuran, produk cacat, atau sekadar berubah pikiran. Masalahnya, retur sering dianggap urusan administratif kecil yang dicatat seadanya, atau bahkan tidak dicatat sama sekali. Padahal retur yang tidak tercatat rapi adalah salah satu penyebab paling umum selisih antara stok fisik di rak dan stok yang tertulis di sistem.

## Kenapa Retur Sering Bikin Stok Berantakan

Masalah utamanya bukan retur itu sendiri, tapi bagaimana retur diperlakukan setelah diterima. Barang yang diretur sering langsung dikembalikan ke rak tanpa dicek ulang kondisinya, atau sebaliknya, disisihkan begitu saja tanpa update apa pun di catatan stok — sehingga sistem masih menganggap barang itu "terjual" padahal sebenarnya sudah kembali ke tangan toko.

## Jenis Retur yang Perlu Dibedakan

Tidak semua retur sama, dan perlakuannya terhadap stok pun harus berbeda:

- **Retur barang baik (salah pesan/ukuran)** — barang masih dalam kondisi layak jual, bisa dikembalikan ke stok jual seperti biasa.
- **Retur barang rusak/cacat** — barang tidak layak dijual kembali dalam kondisi normal, harus dipisahkan ke kategori stok berbeda, bukan dicampur dengan stok jual.
- **Retur tukar ukuran/varian** — sebenarnya dua transaksi sekaligus: barang lama masuk sebagai retur, barang baru keluar sebagai penjualan pengganti. Kalau dicatat sebagai satu transaksi campur aduk, sulit dilacak riwayatnya.

## Cara Mencatat Retur agar Stok Tetap Akurat

1. **Catat alasan retur** setiap kali terjadi — ini bukan cuma untuk stok, tapi juga data berharga untuk tahu produk mana yang paling sering bermasalah.
2. **Cek kondisi fisik barang saat retur diterima**, jangan asumsikan otomatis masih baik hanya karena pelanggan bilang begitu.
3. **Kembalikan ke stok jual hanya kalau kondisinya benar-benar layak**, dengan pengecekan yang sama seperti barang baru datang dari supplier.
4. **Pisahkan barang rusak ke kategori stok tersendiri** (misalnya status "rusak" atau "damaged"), supaya tidak ikut ditawarkan ke pelanggan berikutnya secara tidak sengaja.
5. **Kaitkan retur dengan pesanan asalnya**, bukan dicatat sebagai transaksi stok berdiri sendiri, supaya riwayat lengkap satu pesanan — dari terjual sampai retur — tetap bisa ditelusuri.

## Contoh Kasus: Retur yang Tidak Dipisahkan Kondisinya

Sebuah toko pakaian menerima retur kaos yang kancingnya lepas. Karena buru-buru, staf langsung menaruhnya kembali ke rak stok normal tanpa mencatat kondisi rusaknya. Dua minggu kemudian, kaos itu terjual lagi ke pelanggan baru, yang kemudian komplain karena barang cacat — dan toko harus menerima retur kedua kalinya, kali ini disertai keluhan yang lebih besar karena pelanggan merasa dikirimi barang rusak.

Kalau sejak awal retur kaos itu dipisahkan ke kategori "rusak" dan tidak ikut ditawarkan sebagai stok jual biasa, masalah ini bisa dihindari sepenuhnya — cukup dengan satu langkah tambahan saat retur pertama diterima.

## Kesalahan yang Sering Terjadi

Kesalahan paling umum adalah retur barang rusak langsung masuk lagi ke rak jual tanpa pengecekan, seperti contoh di atas. Kesalahan lain adalah tidak mengaitkan retur dengan pesanan asalnya, sehingga toko kehilangan data penting: produk apa yang paling sering diretur, dan apakah polanya berkaitan dengan kualitas produk, kesalahan deskripsi, atau masalah lain yang bisa diperbaiki dari sumbernya.

Retur yang dikelola rapi juga berkaitan dengan bagaimana [pesanan dari berbagai channel dikelola](/blog/cara-mengelola-pesanan-online-offline-sekaligus) — retur dari pembeli marketplace, misalnya, perlu jelas alurnya sampai kembali ke stok pusat toko, bukan hanya tercatat di sisi marketplace saja. Kalau toko masih mempertimbangkan sistem kasir yang bisa menangani ini, [tips memilih aplikasi kasir untuk UMKM pemula](/blog/tips-memilih-aplikasi-kasir-umkm-pemula) bisa jadi acuan awal. Retur dari reseller dan dropship punya alur sendiri; lihat [cara kelola stok reseller dan dropship kosmetik](/blog/cara-kelola-stok-reseller-dropship-kosmetik).

Fabriku mencatat status inventory per item — termasuk status "rusak" yang terpisah dari stok yang tersedia untuk dijual — sehingga retur barang cacat tidak akan tercampur atau tidak sengaja ditawarkan lagi ke pelanggan berikutnya.
MD,
            ],
            [
                'slug' => 'cara-stok-opname-toko-retail-tanpa-tutup-toko',
                'title' => 'Cara Stok Opname Toko Retail Tanpa Harus Tutup Toko',
                'category' => 'Manajemen Stok',
                'tags' => ['Retail', 'Stok Opname', 'UMKM'],
                'days_ago' => 4,
                'excerpt' => 'Stok opname sering dianggap harus tutup toko sehari penuh. Padahal dengan opname bergulir per rak, toko bisa tetap buka tanpa kehilangan satu pun jam operasional.',
                'meta_title' => 'Cara Stok Opname Toko Retail Tanpa Tutup Toko',
                'meta_description' => 'Cara stok opname toko retail tanpa tutup toko: opname bergulir per rak, jadwal jam sepi, dan cara menangani selisih tanpa menghentikan operasional.',
                'content' => <<<'MD'
Banyak pemilik toko retail menunda stok opname berbulan-bulan karena membayangkan harus tutup toko sehari penuh, kehilangan omzet hari itu, dan merepotkan pelanggan yang datang. Padahal opname tidak harus dilakukan sekaligus untuk seluruh toko — cara bergulir per rak atau kategori memungkinkan opname tetap jalan tanpa mengorbankan satu pun jam operasional.

## Kenapa Opname Sekaligus Bikin Toko Harus Tutup

Opname yang dilakukan untuk seluruh toko dalam satu waktu memang butuh semua transaksi berhenti sementara — kalau ada barang yang terjual di tengah proses hitung, angka stok yang dicatat langsung tidak akurat. Cara ini masuk akal untuk toko kecil dengan sedikit SKU, tapi begitu jumlah produk dan rak bertambah, waktu tutup yang dibutuhkan ikut membengkak, dan kerugian dari hari tanpa penjualan mulai terasa signifikan.

## Cara Opname Bergulir per Rak atau Kategori

Alih-alih menghitung semua barang sekaligus, bagi toko menjadi beberapa zona — per rak, per kategori produk, atau per lokasi penyimpanan — lalu opname satu zona per hari atau per minggu secara bergiliran. Zona yang sedang diopname bisa ditutup sementara dari transaksi, sementara zona lain tetap beroperasi normal. Dalam beberapa minggu, seluruh toko sudah tercakup tanpa pernah menutup toko secara penuh dalam satu hari.

## Pilih Jam Sepi, Bukan Hari Libur Penuh

Selain membagi per zona, pilih juga waktu yang trafficnya paling rendah — misalnya jam buka pertama sebelum pelanggan ramai, atau menjelang tutup. Opname di jam sepi mengurangi gangguan terhadap pelanggan dan staf kasir, dibanding memaksakan opname di jam ramai yang justru membuat proses hitung tergesa-gesa dan rawan salah catat.

## Bekukan Pergerakan Stok Hanya di Bagian yang Diopname

Kunci supaya opname bergulir tetap akurat adalah membekukan pergerakan stok hanya di zona yang sedang dihitung, bukan seluruh toko. Selama proses opname satu rak berlangsung, jangan izinkan pengambilan atau penambahan barang di rak itu — tapi rak lain tetap bebas bertransaksi seperti biasa. Kalau ini tidak dijaga, angka yang dicatat saat opname bisa berubah lagi begitu penghitungan selesai, dan hasilnya tidak mencerminkan kondisi sebenarnya.

## Menangani Selisih Tanpa Menyalahkan Staf

Selisih antara stok fisik dan stok di sistem hampir pasti ditemukan setiap opname, besar atau kecil. Cara menyikapinya penting: fokus dulu pada mencari pola penyebab — apakah selisih terjadi di kategori produk tertentu, di jam tertentu, atau berulang di zona yang sama — sebelum buru-buru mencari siapa yang salah. Opname yang berujung mencari kambing hitam biasanya membuat staf menyembunyikan selisih kecil di opname berikutnya, alih-alih melaporkannya secara jujur.

## Contoh Jadwal Opname Bergulir Mingguan

| Hari | Zona yang Diopname | Estimasi Waktu |
|---|---|---|
| Senin | Rak Pakaian Atasan | 45 menit |
| Rabu | Rak Pakaian Bawahan | 45 menit |
| Jumat | Rak Aksesoris | 30 menit |
| Minggu | Rak Sepatu & Tas | 45 menit |

Dengan jadwal seperti ini, toko tetap buka penuh setiap hari, dan dalam satu bulan seluruh zona sudah terhitung minimal sekali — jauh lebih sering dibanding opname tahunan yang selama ini jadi kebiasaan banyak toko kecil.

## Kapan Opname Penuh Tetap Tidak Terhindarkan

Opname bergulir cocok untuk rutinitas bulanan, tapi ada momen tertentu yang tetap membutuhkan opname penuh serentak — misalnya audit tahunan untuk laporan keuangan, pergantian penanggung jawab toko, atau setelah insiden seperti kebocoran atau kerusakan kecil yang berpotensi memengaruhi banyak zona sekaligus. Untuk momen-momen ini, menutup toko sementara memang jadi pilihan yang lebih masuk akal dibanding memaksakan opname bergulir yang tidak dirancang untuk situasi darurat.

## Kesalahan yang Sering Terjadi

Kesalahan paling umum adalah menjadwalkan opname bergulir tapi tidak konsisten menjalankannya — zona yang "kelihatan aman" terus ditunda, sementara zona yang sering bermasalah malah jarang dicek karena stafnya menghindar. Kesalahan lain adalah tidak mendokumentasikan hasil opname tiap zona secara terpisah, sehingga sulit melihat pola selisih dari waktu ke waktu.

Sebelum mulai opname bergulir, ada baiknya proses [pengelolaan retur barang](/blog/cara-kelola-stok-retur-barang-umkm-retail) di toko juga sudah rapi — retur yang tidak tercatat benar adalah salah satu penyebab paling umum selisih stok yang baru ketahuan saat opname. Prinsip dasar pencatatan stok yang konsisten ini juga berlaku untuk bahan baku, bukan cuma barang jadi retail — lihat [cara mengelola stok bahan baku UMKM agar tidak rugi](/blog/cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi) untuk gambaran yang lebih luas.

Opname bergulir paling mudah dijalankan bila setiap barang punya catatan keluar-masuk yang rapi; formatnya bisa dilihat di [contoh kartu stok barang](/blog/kartu-stok-barang-contoh-cara-mengisi-template-excel). Kalau tokomu punya lebih dari satu cabang, lihat [cara kelola stok banyak cabang dan outlet UMKM](/blog/cara-kelola-stok-banyak-cabang-outlet-umkm).

Fabriku mencatat pergerakan stok per item dan lokasi secara real-time, sehingga opname bergulir bisa dibandingkan langsung dengan catatan sistem per zona, tanpa harus menghentikan seluruh operasional toko untuk mencocokkan angka.
MD,
            ],
            [
                'slug' => 'cara-mengelola-kontraktor-maklun-konveksi-agar-tidak-telat',
                'title' => 'Cara Mengelola Kontraktor/Maklun Konveksi agar Pesanan Tidak Telat',
                'category' => 'Produksi',
                'tags' => ['Konveksi', 'Maklun', 'UMKM'],
                'days_ago' => 3,
                'excerpt' => 'Ketergantungan pada maklun tanpa kesepakatan yang jelas sering berujung pesanan telat. Ini cara mengelola kerja sama dengan kontraktor jahit supaya tenggat tetap terjaga.',
                'meta_title' => 'Cara Mengelola Kontraktor/Maklun Konveksi agar Tidak Telat',
                'meta_description' => 'Cara mengelola kontraktor atau maklun konveksi agar pesanan tidak telat: kesepakatan tertulis, serah terima bahan tercatat, dan cek progress tepat waktu.',
                'content' => <<<'MD'
Banyak usaha konveksi mengandalkan kontraktor jahit atau maklun untuk memenuhi kapasitas produksi yang tidak bisa ditangani sendiri. Masalahnya, ketergantungan ini sering tidak diimbangi kesepakatan yang jelas, sehingga keterlambatan pesanan jadi kejutan yang berulang, bukan sesuatu yang bisa diantisipasi lebih awal.

## Kenapa Kerja Sama dengan Maklun Sering Berujung Telat

Keterlambatan biasanya bukan karena maklun sengaja lalai, tapi karena tidak ada kesepakatan tertulis yang jelas soal kapasitas dan tenggat waktu sejak awal. Tanpa itu, ekspektasi pemilik usaha dan kapasitas riil maklun bisa jauh berbeda, dan ketidaksesuaian ini baru ketahuan saat tenggat sudah dekat — waktu yang sudah terlalu mepet untuk mencari solusi.

## Buat Kesepakatan Tertulis soal Kapasitas dan Tenggat

Sebelum menyerahkan pesanan, sepakati secara tertulis (cukup lewat chat yang terdokumentasi) berapa maksimal unit yang bisa dikerjakan maklun dalam periode tertentu, dan tenggat realistis untuk jumlah itu. Kesepakatan lisan mudah berubah interpretasinya di kedua pihak begitu ada tekanan waktu — kesepakatan tertulis memberi acuan yang sama untuk keduanya kalau terjadi perbedaan pemahaman di tengah jalan.

## Serah Terima Bahan dengan Hitungan yang Tercatat

Setiap kali bahan (kain, benang, aksesoris) diserahkan ke maklun, catat jumlah persisnya — jangan hanya estimasi "cukup untuk sekian potong". Pencatatan ini penting untuk dua hal: memastikan maklun punya bahan yang cukup untuk menyelesaikan pesanan sesuai jumlah yang disepakati, dan menjadi acuan kalau nanti ada selisih antara bahan yang diserahkan dan hasil jadi yang diterima kembali.

## Cek Progress di Titik Tengah, Bukan Hanya di Akhir

Kesalahan paling umum adalah menyerahkan pesanan lalu baru menghubungi maklun mendekati tenggat untuk menanyakan progress. Kalau ternyata pengerjaan tertinggal jauh, waktu yang tersisa sudah tidak cukup untuk mencari solusi seperti menambah tenaga atau memindahkan sebagian pekerjaan ke maklun lain. Cek progress di titik tengah periode pengerjaan memberi ruang untuk bertindak lebih awal kalau ada tanda-tanda keterlambatan.

## Punya Alur yang Jelas untuk Barang Reject

Barang hasil jahitan yang tidak sesuai standar (reject) perlu alur yang disepakati sejak awal: apakah dikembalikan untuk diperbaiki, dihitung sebagai potongan pembayaran, atau ditanggung bersama. Tanpa kesepakatan ini, setiap kali ada reject jadi perdebatan baru yang menunda penyelesaian pesanan, bukan sekadar masalah kualitas yang seharusnya bisa diselesaikan cepat.

## Jangan Bergantung pada Satu Maklun Saja

Mengandalkan satu maklun untuk semua kapasitas produksi tambahan berarti satu masalah di pihak mereka — sakit, alat rusak, atau kelebihan order dari klien lain — langsung berdampak ke seluruh pesanan yang sedang berjalan. Membangun hubungan dengan lebih dari satu maklun, meski salah satunya jadi mitra utama, memberi opsi cadangan saat kapasitas mendadak dibutuhkan atau ada masalah di maklun utama.

## Contoh Kasus: Kesepakatan yang Menyelamatkan Tenggat

Sebuah usaha konveksi kecil menyepakati dengan maklun bahwa 200 potong kemeja harus selesai dalam 10 hari, dengan cek progress di hari kelima. Saat pengecekan hari kelima, ternyata baru 60 potong yang selesai — jauh dari target separuh jalan (100 potong). Karena kesepakatan cek progress ini ada, pemilik usaha masih punya waktu 5 hari untuk memindahkan sisa pekerjaan ke maklun kedua, dan pesanan tetap selesai tepat waktu. Tanpa titik cek di tengah, keterlambatan ini baru diketahui di hari kesembilan atau kesepuluh, saat sudah tidak ada waktu untuk mencari solusi apa pun.

## Kesalahan yang Sering Terjadi

Kesalahan paling sering adalah menyerahkan seluruh pesanan ke satu maklun tanpa kesepakatan tenggat tertulis, lalu berharap semuanya selesai sesuai perkiraan sendiri. Kesalahan lain adalah tidak mencatat jumlah bahan yang diserahkan secara presisi, sehingga saat hasil jadi diterima, tidak ada cara memastikan apakah semua bahan sudah dipakai dengan benar atau ada yang hilang di tengah proses.

Masalah stok bahan yang sering muncul dalam kerja sama dengan maklun ini sebenarnya berakar dari kebiasaan pencatatan yang sama dengan [kesalahan mengelola stok bahan baku yang bikin UMKM konveksi rugi](/blog/kesalahan-mengelola-stok-bahan-baku-umkm-konveksi) — kalau kesalahan itu terasa familiar, pembenahannya bisa dimulai dari sana. Untuk melacak progress produksi dari beberapa maklun sekaligus tanpa harus menelepon satu per satu, lihat juga [aplikasi pencatatan produksi usaha kecil menengah](/blog/aplikasi-pencatatan-produksi-usaha-kecil-menengah).

Fabriku mencatat pesanan produksi dan kontraktor sebagai data terpisah namun terhubung, sehingga progress tiap maklun, bahan yang diserahkan, dan tenggat masing-masing pesanan bisa dipantau dari satu tempat tanpa harus menghubungi satu per satu untuk sekadar tahu statusnya.
MD,
            ],
            [
                'slug' => 'kapan-usaha-rumahan-butuh-sistem-pencatatan',
                'title' => 'Kapan Usaha Rumahan Butuh Sistem Pencatatan, Bukan Buku Lagi',
                'category' => 'Produksi',
                'tags' => ['Produksi Rumahan', 'UMKM', 'Digitalisasi'],
                'days_ago' => 2,
                'excerpt' => 'Patokan "kapan pindah ke sistem pencatatan" bukan soal omzet, tapi gejala operasional sehari-hari. Kenali empat tandanya sebelum masalahnya membesar.',
                'meta_title' => 'Kapan Usaha Rumahan Butuh Sistem Pencatatan',
                'meta_description' => 'Kapan usaha rumahan butuh sistem pencatatan? Kenali gejalanya: banyak pencatat, stok sering hilang, pesanan tercecer, dan sulit tahu produk paling untung.',
                'content' => <<<'MD'
Pertanyaan "kapan usaha rumahan butuh sistem pencatatan digital" sering dijawab dengan patokan omzet — misalnya "kalau sudah tembus sekian juta sebulan". Padahal ukuran omzet kurang relevan dibanding gejala operasional yang sebenarnya lebih mudah dikenali sehari-hari.

## Kenapa Patokan Omzet Kurang Tepat

Dua usaha rumahan dengan omzet yang sama bisa punya kompleksitas operasional yang jauh berbeda — satu dijalankan sendirian dengan produk tunggal, satu lagi dikerjakan bersama beberapa orang dengan puluhan varian produk. Yang kedua jelas lebih butuh sistem pencatatan meski omzetnya sama, karena kompleksitas koordinasinya jauh lebih tinggi. Jadi gejala operasional, bukan angka omzet, yang jadi indikator lebih akurat.

## Gejala 1: Lebih dari Satu Orang Mulai Ikut Mencatat

Selama pencatatan dipegang satu orang, catatan di buku atau memori kepala masih bisa konsisten. Begitu ada orang kedua yang ikut mencatat — pasangan, anak, karyawan pertama — mulai muncul celah: dua orang mencatat dengan format berbeda, atau salah satu lupa mencatat transaksi yang ditanganinya sendiri. Inkonsistensi ini adalah tanda paling awal bahwa cara pencatatan lama mulai kewalahan.

## Gejala 2: Stok "Hilang" Berulang Tanpa Penjelasan Jelas

Kalau sudah beberapa kali terjadi selisih stok yang tidak bisa dijelaskan — bahan yang menurut catatan masih ada tapi ternyata sudah habis, atau sebaliknya — dan ini terjadi berulang, bukan sesekali, itu tanda pencatatan manual sudah tidak bisa mengikuti kecepatan transaksi yang sebenarnya terjadi.

## Gejala 3: Pesanan Terlewat karena Tercecer di Chat

Usaha rumahan yang menerima pesanan lewat WhatsApp atau media sosial rawan kehilangan jejak pesanan di tengah obrolan lain. Kalau sudah pernah terjadi pesanan yang terlewat diproses karena chatnya "tenggelam" di antara obrolan pribadi atau pesan pelanggan lain, ini bukan kesalahan orang yang mencatat, tapi tanda bahwa medium pencatatannya sudah tidak cocok lagi dengan volume pesanan yang masuk.

## Gejala 4: Tidak Bisa Menjawab "Produk Mana yang Paling Untung"

Ini gejala yang paling sering diabaikan karena tidak terasa mendesak secara harian. Tapi kalau ditanya "dari semua produk yang dijual, mana yang sebenarnya paling menguntungkan setelah dikurangi biaya bahan dan waktu kerja", dan jawabannya cuma perkiraan kasar, itu tanda catatan yang ada selama ini tidak dirancang untuk menjawab pertanyaan bisnis yang sebenarnya penting — hanya mencatat transaksi tanpa bisa dianalisis lebih jauh.

## Bukan Berarti Harus Langsung Pakai Sistem yang Rumit

Menyadari gejala-gejala di atas bukan berarti harus langsung berlangganan sistem yang canggih dan mahal. Langkah awal yang realistis adalah memastikan pencatatan produksi harian sudah konsisten dan terstruktur — [cara mencatat produksi harian UMKM kuliner rumahan](/blog/cara-mencatat-produksi-harian-umkm-kuliner-rumahan) menunjukkan versi paling sederhana dari ini. Begitu pencatatan dasar sudah rutin, barulah pindah ke sistem yang bisa merangkum semuanya jadi lebih mudah dilihat. Kalau sudah memakai Excel dan ragu apakah saatnya pindah, baca [aplikasi pencatatan UMKM vs Excel: kapan harus pindah](/blog/aplikasi-pencatatan-umkm-vs-excel-kapan-pindah).

## Contoh Kasus: Dua Gejala yang Muncul Bersamaan

Sebuah usaha kue rumahan yang tadinya dikerjakan sendiri mulai dibantu dua orang keluarga. Dalam sebulan, pemilik usaha menyadari dua hal: pertama, catatan stok tepung dan gula sering tidak cocok karena masing-masing orang mencatat pembelian dan pemakaian dengan caranya sendiri. Kedua, saat ditanya varian kue mana yang paling untung, jawabannya hanya "kayaknya yang cokelat, soalnya paling laku" — padahal laku belum tentu berarti paling untung kalau biaya bahannya juga lebih mahal. Kedua gejala ini muncul bersamaan justru karena akar masalahnya sama: tidak ada satu sumber catatan yang konsisten dan bisa dianalisis, bukan cuma dicatat.

## Kesalahan yang Sering Terjadi

Kesalahan paling umum adalah menunggu sampai masalahnya terasa sangat mengganggu baru mempertimbangkan pindah ke sistem pencatatan, padahal gejala-gejala di atas biasanya sudah muncul jauh lebih awal dalam bentuk kecil. Kesalahan lain adalah berpikir sistem pencatatan hanya soal mencatat transaksi, padahal manfaat sebenarnya baru terasa saat data itu dianalisis — misalnya untuk menjawab produk mana yang paling untung, sesuatu yang berkaitan erat dengan [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm) yang sudah dibahas lebih detail di artikel lain.

Untuk kategori usaha rumahan, Fabriku menyediakan pencatatan produksi dan stok yang bisa diakses lebih dari satu orang sekaligus, dengan riwayat siapa mencatat apa dan kapan — jadi gejala-gejala di atas bisa dicegah sebelum benar-benar mengganggu operasional harian.
MD,
            ],
            [
                'slug' => 'aplikasi-pencatatan-umkm-vs-excel-kapan-pindah',
                'title' => 'Aplikasi Pencatatan UMKM vs Excel: Kapan Harus Pindah',
                'category' => 'Produksi',
                'tags' => ['UMKM', 'Digitalisasi', 'Aplikasi UMKM'],
                'days_ago' => 1,
                'excerpt' => 'Excel murah dan fleksibel, tapi ada titik di mana kelebihan itu berbalik jadi kelemahan. Ini cara jujur menilai kapan waktunya pindah ke aplikasi pencatatan.',
                'meta_title' => 'Aplikasi Pencatatan UMKM vs Excel: Kapan Harus Pindah',
                'meta_description' => 'Aplikasi pencatatan UMKM vs Excel, kapan harus pindah? Kenali di mana Excel masih unggul, di mana mulai rontok, dan cara migrasi tanpa kehilangan data.',
                'content' => <<<'MD'
Excel atau Google Sheets adalah titik awal yang masuk akal untuk hampir semua UMKM — gratis atau murah, fleksibel dibentuk sesuai kebutuhan, dan tidak butuh waktu belajar lama. Tapi ada titik di mana kelebihan itu justru berbalik jadi kelemahan, dan pertanyaannya bukan "apakah harus pindah ke aplikasi", tapi "kapan".

## Di Mana Excel Masih Unggul

Untuk usaha yang masih dikerjakan sendiri atau berdua, dengan transaksi harian yang masih bisa dihitung jari, Excel punya keunggulan nyata: tidak ada biaya langganan, bisa dibentuk sesuai kebutuhan spesifik tanpa menunggu fitur dari vendor, dan hampir semua orang sudah familiar dasarnya sehingga tidak perlu training khusus. Untuk skala ini, memaksakan pindah ke aplikasi justru menambah kompleksitas yang belum dibutuhkan.

## Di Mana Excel Mulai Rontok

### 1. Dipakai Lebih dari Satu Orang Sekaligus

Excel atau Sheets yang diakses beberapa orang bersamaan rawan konflik — perubahan satu orang bisa tertimpa perubahan orang lain, terutama kalau file dikirim bolak-balik lewat WhatsApp alih-alih dibuka bersama secara online. Google Sheets sedikit membantu untuk kolaborasi real-time, tapi masalah berikutnya tetap muncul begitu skalanya bertambah.

### 2. Tidak Ada Riwayat Perubahan yang Jelas

Kalau ada angka yang berubah atau terhapus tidak sengaja, Excel biasa tidak punya cara mudah untuk melacak siapa yang mengubah, kapan, dan apa nilai sebelumnya. Riwayat versi di Google Sheets ada, tapi tidak dirancang untuk audit bisnis — mencari perubahan spesifik di tengah ratusan baris data tetap merepotkan.

### 3. Stok Tidak Bisa Real-Time Sinkron Antar Fungsi

Di Excel, stok yang berkurang karena penjualan biasanya harus diinput manual terpisah dari catatan transaksi penjualan itu sendiri. Kalau salah satu lupa diperbarui, stok yang tertulis di spreadsheet dan stok fisik mulai berbeda tanpa ada yang sadar sampai ketahuan saat stok opname atau saat pelanggan komplain barang kosong padahal "masih ada" di catatan.

### 4. Rumus yang Rusak Diam-Diam

Spreadsheet yang sudah berkembang jadi rumit — dengan banyak rumus antar-sheet — rawan rusak tanpa peringatan. Satu baris yang salah dihapus atau satu sel yang tidak sengaja diisi manual bisa merusak rumus di sel lain, dan kerusakan ini sering baru ketahuan berminggu-minggu kemudian saat angka yang keluar terlihat janggal, bukan saat kerusakannya terjadi.

## Cara Pindah Tanpa Kehilangan Data

Pindah dari Excel ke aplikasi tidak harus dilakukan sekaligus dan langsung menghapus semua kebiasaan lama. Langkah yang lebih aman:

1. **Ekspor data terakhir dari Excel** — stok saat ini, daftar pelanggan, daftar produk — sebagai titik awal di sistem baru, bukan mulai dari nol.
2. **Jalankan keduanya paralel dulu** selama 1-2 minggu, catat di kedua tempat, untuk memastikan angka di aplikasi baru cocok dengan yang biasa dihasilkan Excel sebelum benar-benar melepas yang lama.
3. **Pindahkan fungsi satu per satu**, misalnya mulai dari pencatatan stok dulu, baru penjualan, baru laporan — bukan memindahkan semua fungsi sekaligus yang berisiko membuat tim kewalahan belajar semuanya di waktu yang sama.
4. **Simpan file Excel lama sebagai arsip**, jangan langsung dihapus, untuk referensi kalau ada pertanyaan soal data historis sebelum pindah.

## Kapan Waktu yang Tepat untuk Mulai Pindah

Waktu paling tepat bukan menunggu sampai Excel benar-benar tidak sanggup menangani beban, karena di titik itu proses pindah jadi terburu-buru dan berisiko kehilangan data yang belum sempat dirapikan. Momen yang lebih ideal adalah begitu mulai muncul dua atau lebih gejala rontok di atas secara bersamaan — misalnya sudah dipakai lebih dari satu orang dan mulai ada kejadian stok yang tidak sinkron. Menunggu sampai kedua gejala itu jadi krisis besar biasanya membuat proses migrasi jauh lebih menyakitkan dibanding memulainya lebih awal.

## Kesalahan yang Sering Terjadi saat Pindah

Kesalahan paling umum adalah memindahkan semua data dan fungsi sekaligus dalam satu hari, lalu berharap tim langsung terbiasa tanpa masa transisi. Kesalahan lain adalah tidak menguji aplikasi baru dengan skenario nyata sebelum benar-benar berkomitmen — baru menyadari ada fitur penting yang ternyata tidak ada setelah semua data dipindahkan, bukan sebelum memutuskan.

Kalau kebutuhan utamanya soal pencatatan transaksi penjualan, [7 tips memilih aplikasi kasir untuk UMKM pemula](/blog/tips-memilih-aplikasi-kasir-umkm-pemula) bisa membantu menyaring opsi sebelum memutuskan. Dan kalau masih ragu apakah sudah waktunya pindah sama sekali, gejala-gejala yang lebih spesifik untuk usaha rumahan sudah dibahas di [kapan usaha rumahan butuh sistem pencatatan](/blog/kapan-usaha-rumahan-butuh-sistem-pencatatan).

Fabriku bisa dipakai bertahap seperti ini — mulai dari mencatat stok awal (opening balance) dan penjualan, lalu menambah modul lain saat sudah terbiasa. Data yang sudah tercatat tetap bisa diekspor ke Excel kapan saja.
MD,
            ],
            [
                'slug' => 'cara-membuat-bom-produksi-konveksi-per-ukuran-warna',
                'title' => 'Cara Membuat BOM (Bill of Material) Produksi Konveksi per Ukuran dan Warna',
                'category' => 'Produksi',
                'tags' => ['Konveksi', 'Produksi', 'BOM'],
                'days_ago' => 0,
                'excerpt' => 'Tanpa BOM per varian, konveksi menghitung kebutuhan kain dengan kira-kira dan baru sadar kurang saat potong. Ini cara menyusun BOM per ukuran dan warna, lengkap dengan contoh.',
                'meta_title' => 'Contoh Bill of Material (BOM) Konveksi + Template Excel',
                'meta_description' => 'Apa itu bill of material (BOM), contoh BOM konveksi per ukuran dan warna, rumus kebutuhan kain dengan toleransi susut, dan template BOM sederhana di Excel.',
                'content' => <<<'MD'
Order 300 kaos dengan tiga warna dan lima ukuran terdengar sederhana sampai kain yang dibeli ternyata kurang untuk ukuran XL warna hitam, sementara kain putih tersisa dua roll. Masalahnya hampir selalu sama: kebutuhan bahan dihitung dari rata-rata, bukan dari daftar bahan per varian. Daftar inilah yang disebut **BOM (bill of material)**.

## Apa Itu Bill of Material (BOM)

Bill of material (BOM) adalah daftar lengkap bahan, komponen, dan jumlahnya yang dibutuhkan untuk membuat satu unit produk jadi. Istilah ini dipakai di semua jenis produksi, dari pabrik elektronik sampai dapur kue: resep dengan takaran juga merupakan BOM. Bedanya dengan daftar belanja, BOM ditulis per unit produk, sehingga kebutuhan untuk berapa pun jumlah order bisa dihitung dengan perkalian.

## Apa Itu BOM dalam Produksi Konveksi

BOM adalah daftar semua bahan dan jumlahnya yang dibutuhkan untuk membuat **satu unit** produk. Untuk konveksi, satu unit berarti satu potong pakaian dengan ukuran dan warna tertentu. BOM kaos ukuran S tidak sama dengan BOM kaos ukuran XXL, karena konsumsi kainnya berbeda.

BOM yang rapi dipakai untuk tiga hal: menghitung belanja bahan sebelum produksi, menghitung HPP per varian, dan mengecek apakah bahan yang keluar dari gudang sesuai dengan jumlah produk yang dihasilkan.

## Komponen BOM Konveksi

Untuk satu potong kaos, komponennya biasanya:

1. **Kain utama** — dalam meter atau kilogram, tergantung cara Anda membeli kain.
2. **Kain tambahan** — rib leher, kain kontras, atau furing.
3. **Benang** — dalam meter atau persentase cone.
4. **Aksesoris** — label merek, label ukuran, hangtag, kancing, resleting.
5. **Sablon/bordir** — jika dikerjakan sendiri, catat tinta atau benang bordir; jika maklun, catat sebagai biaya jasa per potong.
6. **Kemasan** — plastik, stiker, karton.

## Menghitung Konsumsi Kain per Ukuran

Cara paling akurat adalah membuat marker (susunan pola di atas kain) untuk tiap ukuran, lalu mengukur panjang kain yang terpakai. Jika belum memakai marker, timbang atau ukur kain sebelum dan sesudah memotong 10 potong per ukuran, lalu ambil rata-ratanya.

Contoh hasil pengukuran untuk kaos cotton combed 30s, kain lebar 180 cm, dibeli per kilogram:

| Ukuran | Kain per potong | Rib leher | Label & hangtag | Plastik |
|---|---|---|---|---|
| S | 0,20 kg | 0,010 kg | 1 set | 1 pcs |
| M | 0,22 kg | 0,011 kg | 1 set | 1 pcs |
| L | 0,24 kg | 0,012 kg | 1 set | 1 pcs |
| XL | 0,27 kg | 0,013 kg | 1 set | 1 pcs |
| XXL | 0,30 kg | 0,014 kg | 1 set | 1 pcs |

Selisih S ke XXL di contoh ini 50%. Menghitung semua ukuran dengan angka rata-rata (misalnya 0,24 kg) membuat order dengan banyak ukuran besar pasti kekurangan kain.

## Tambahkan Toleransi Susut dan Sisa Potong

Kain tidak terpakai 100%. Ada sisa potongan, kain cacat, dan susut setelah dicuci. Tambahkan toleransi 3–8% tergantung jenis kain dan kerumitan pola. Kaos polos biasanya 3–5%, kemeja dengan banyak potongan bisa sampai 8%.

**Kebutuhan kain = konsumsi per potong × jumlah potong × (1 + toleransi)**

## Contoh Bill of Material: Order 300 Kaos, Tiga Warna

Order dari pelanggan:

| Warna | S | M | L | XL | XXL | Total |
|---|---|---|---|---|---|---|
| Hitam | 10 | 30 | 40 | 30 | 10 | 120 |
| Putih | 10 | 25 | 30 | 20 | 5 | 90 |
| Navy | 10 | 25 | 30 | 20 | 5 | 90 |

Kebutuhan kain hitam: (10×0,20) + (30×0,22) + (40×0,24) + (30×0,27) + (10×0,30) = 2 + 6,6 + 9,6 + 8,1 + 3 = **29,3 kg**. Dengan toleransi 5%, belanja **30,8 kg**, dibulatkan 31 kg.

Kain putih: (10×0,20) + (25×0,22) + (30×0,24) + (20×0,27) + (5×0,30) = 2 + 5,5 + 7,2 + 5,4 + 1,5 = 21,6 kg, dengan toleransi menjadi 22,7 kg. Kain navy sama dengan putih karena komposisi ukurannya sama.

Bandingkan jika dihitung rata-rata 0,24 kg × 120 potong = 28,8 kg untuk warna hitam. Setelah toleransi menjadi 30,2 kg — kurang sekitar 0,5 kg, setara dua potong XL yang tidak bisa dipotong.

## Template Bill of Material Sederhana di Excel

BOM yang hanya ada di kepala kepala produksi akan hilang saat orangnya cuti atau berhenti. Simpan dalam tabel, satu baris per bahan per varian:

| Produk | Ukuran | Warna | Bahan | Jumlah per unit | Satuan | Toleransi |
|---|---|---|---|---|---|---|
| Kaos basic | M | Hitam | Kain cotton combed 30s | 0,22 | kg | 5% |
| Kaos basic | M | Hitam | Rib leher | 0,02 | kg | 5% |
| Kaos basic | M | Hitam | Benang jahit (1 cone ≈ 40 kaos) | 0,025 | cone | 0% |
| Kaos basic | M | Hitam | Label + hangtag | 1 | pcs | 2% |
| Kaos basic | M | Hitam | Plastik kemasan | 1 | pcs | 2% |

Di sheet kedua, tulis order per varian. Kebutuhan tiap bahan = *Jumlah per unit × qty order × (1 + Toleransi)*, lalu jumlahkan per bahan dengan `SUMIF` atau PivotTable untuk mendapat daftar belanja. Perbarui baris BOM setiap kali pola berubah atau ganti supplier kain dengan gramasi berbeda.

Setelah produksi selesai, bandingkan kain yang benar-benar keluar gudang dengan kebutuhan menurut BOM. Selisih di atas toleransi adalah tanda ada pemborosan di proses potong, atau ada kain yang tidak tercatat keluar.

## Kesalahan Umum Saat Menyusun BOM

- **Satu BOM untuk semua ukuran.** Penyebab paling sering kain kurang di tengah produksi.
- **Lupa aksesoris kecil.** Label ukuran dan hangtag sering habis duluan karena tidak masuk hitungan belanja.
- **Tidak membedakan warna.** Kain warna gelap kadang punya gramasi berbeda dari warna terang di supplier yang sama.
- **Tidak memperbarui setelah ganti supplier.** Lebar kain 180 cm dan 160 cm menghasilkan konsumsi berbeda untuk pola yang sama.

## Pertanyaan yang Sering Muncul

**Apakah BOM sama dengan HPP?**
Bukan. BOM berisi jumlah bahan. HPP adalah biaya, yaitu jumlah bahan di BOM dikalikan harga bahan, ditambah upah dan overhead. Lihat [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm).

**Bagaimana BOM untuk produksi yang dimaklunkan?**
Tetap susun BOM yang sama. Bahan yang diserahkan ke maklun dihitung dari BOM, sehingga Anda bisa mengecek apakah sisa bahan yang kembali masuk akal. Baca juga [cara mengelola kontraktor maklun konveksi](/blog/cara-mengelola-kontraktor-maklun-konveksi-agar-tidak-telat).

Di Fabriku, kain masuk dicatat per penerimaan lengkap dengan warna, lebar, dan gramasi, lalu bahan yang dipakai di order persiapan (potong) mengurangi stok secara FIFO. Dengan begitu selisih antara kebutuhan menurut BOM dan kain yang benar-benar keluar bisa dicek per order. Lihat [Fabriku untuk konveksi](/aplikasi/konveksi).
MD,
            ],
            [
                'slug' => 'cara-mengatur-jadwal-teknisi-bengkel-servis-agar-tidak-bentrok',
                'title' => 'Cara Mengatur Jadwal Teknisi Bengkel dan Jasa Servis agar Tidak Bentrok',
                'category' => 'Produksi',
                'tags' => ['Jasa Servis', 'Jadwal Staf', 'UMKM'],
                'days_ago' => 1,
                'excerpt' => 'Pelanggan menunggu lama sementara teknisi lain menganggur adalah tanda jadwal kerja tidak diatur. Ini cara menyusun jadwal teknisi bengkel dan jasa servis yang sederhana tapi jalan.',
                'meta_title' => 'Cara Mengatur Jadwal Teknisi Bengkel & Jasa Servis',
                'meta_description' => 'Cara mengatur jadwal teknisi bengkel dan jasa servis agar tidak bentrok: durasi layanan, slot booking, pembagian keahlian, dan contoh papan jadwal.',
                'content' => <<<'MD'
Di bengkel atau usaha servis kecil, jadwal biasanya diatur dengan prinsip siapa datang duluan dilayani duluan. Cara ini berjalan saat pelanggan sedikit. Begitu ramai, muncul masalah: tiga motor menunggu teknisi senior, sementara teknisi junior tidak ada pekerjaan; pelanggan yang sudah janji datang jam 10 masih menunggu jam 12; servis berat menumpuk di sore hari dan harus lembur.

## Mulai dari Durasi Setiap Layanan

Jadwal tidak bisa disusun tanpa tahu berapa lama setiap layanan. Catat waktu pengerjaan nyata selama dua minggu, lalu ambil rata-ratanya. Contoh untuk bengkel motor:

| Layanan | Durasi rata-rata | Siapa yang bisa |
|---|---|---|
| Ganti oli | 15 menit | Semua teknisi |
| Servis ringan / tune up | 45 menit | Semua teknisi |
| Servis CVT | 60 menit | Teknisi senior & madya |
| Kelistrikan | 30–90 menit | Teknisi kelistrikan |
| Turun mesin | 4–6 jam | Teknisi senior |

Kolom "siapa yang bisa" sama pentingnya dengan durasi. Bentrok sering terjadi bukan karena kurang orang, tapi karena pekerjaan yang hanya bisa dikerjakan satu orang menumpuk di jam yang sama.

## Bagi Hari Menjadi Slot

Bagi jam buka menjadi slot 30 menit. Bengkel yang buka 08.00–17.00 dengan istirahat satu jam punya 16 slot per teknisi per hari. Tiga teknisi berarti 48 slot.

Lalu tentukan aturan sederhana:

1. **Sisakan 20–30% slot untuk pelanggan tanpa janji.** Jangan habiskan semua slot untuk booking.
2. **Taruh pekerjaan panjang di pagi hari.** Turun mesin yang dimulai sore hampir pasti mundur ke hari berikutnya.
3. **Satu teknisi senior, maksimal satu pekerjaan berat per hari.** Sisanya untuk layanan cepat atau membantu teknisi lain.
4. **Beri jeda 15 menit antar pekerjaan panjang** untuk membersihkan area dan mengambil sparepart.

## Contoh Papan Jadwal Harian

| Jam | Teknisi A (senior) | Teknisi B (madya) | Teknisi C (junior) |
|---|---|---|---|
| 08.00 | Turun mesin – Booking #12 | Servis CVT – Booking #13 | Walk-in |
| 09.00 | Turun mesin | Tune up – Booking #14 | Ganti oli – Walk-in |
| 10.00 | Turun mesin | Walk-in | Tune up – Booking #15 |
| 11.00 | Turun mesin | Kelistrikan – Booking #16 | Walk-in |
| 13.00 | Pengecekan akhir | Walk-in | Walk-in |
| 14.00 | Servis CVT – Booking #17 | Tune up – Booking #18 | Walk-in |
| 15.00 | Walk-in | Walk-in | Ganti oli – Booking #19 |
| 16.00 | Walk-in | Walk-in | Walk-in |

Papan seperti ini cukup ditulis di papan tulis atau spreadsheet yang dibuka di HP. Yang penting semua orang melihat versi yang sama.

## Konfirmasi Booking Sehari Sebelumnya

Pelanggan yang tidak datang membuat slot kosong yang tidak bisa diisi. Kirim pesan konfirmasi sehari sebelumnya: jam datang, layanan, dan perkiraan selesai. Jika pelanggan membatalkan, slotnya bisa dibuka untuk walk-in.

## Catat Siapa Mengerjakan Apa

Jadwal yang rapi baru separuh pekerjaan. Separuh lainnya adalah mencatat siapa yang benar-benar mengerjakan setiap layanan. Dari catatan ini Anda bisa tahu:

- teknisi mana yang paling sering menangani layanan tertentu,
- berapa omzet jasa yang dihasilkan setiap teknisi,
- apakah durasi nyata jauh berbeda dari estimasi, sehingga jadwal perlu disesuaikan,
- dasar menghitung komisi atau bonus secara adil.

Tanpa catatan ini, komisi dihitung dari ingatan dan sering jadi sumber selisih paham.

## Tinjau Setiap Bulan

Setiap akhir bulan, lihat tiga hal: jam berapa antrean paling panjang, layanan apa yang paling sering molor dari estimasi, dan teknisi mana yang paling sering lembur. Gunakan jawabannya untuk menggeser jam istirahat, menambah teknisi di jam sibuk, atau melatih teknisi junior untuk layanan yang selama ini hanya bisa dikerjakan senior.

## Pertanyaan yang Sering Muncul

**Perlukah aplikasi booking khusus?**
Untuk bengkel dengan 2–4 teknisi, papan jadwal di spreadsheet bersama dan konfirmasi WhatsApp biasanya cukup. Aplikasi booking baru terasa manfaatnya saat booking per hari sudah puluhan.

**Bagaimana jika pekerjaan molor dari jadwal?**
Beri kabar ke pelanggan berikutnya sedini mungkin dan tawarkan geser jam. Catat penyebabnya; jika sering terjadi pada layanan yang sama, perbarui estimasi durasinya.

**Bagaimana menentukan tarif setiap layanan?**
Durasi yang sudah Anda catat untuk jadwal adalah bahan utama menghitung tarif. Lihat [cara menghitung harga jasa servis kecil agar tidak rugi](/blog/cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi). Bahan yang terpakai di tiap pekerjaan dibahas di [cara melacak sparepart dan bahan habis pakai jasa servis](/blog/cara-melacak-sparepart-bahan-habis-pakai-jasa-servis).

Fabriku belum menyediakan kalender booking, tapi setiap baris layanan di transaksi bisa diisi staf yang mengerjakannya. Laporan layanan kemudian merekap omzet per layanan dan per staf, sehingga beban kerja dan dasar komisi tiap teknisi terlihat tanpa rekap manual. Lihat [Fabriku untuk bengkel dan jasa servis](/aplikasi/bengkel-jasa-servis).
MD,
            ],
            [
                'slug' => 'cara-melacak-sparepart-bahan-habis-pakai-jasa-servis',
                'title' => 'Cara Melacak Sparepart dan Bahan Habis Pakai di Usaha Jasa Servis',
                'category' => 'Manajemen Stok',
                'tags' => ['Jasa Servis', 'Sparepart', 'UMKM'],
                'days_ago' => 2,
                'excerpt' => 'Oli, lem, cairan pembersih, dan sparepart kecil berkurang setiap hari tanpa tercatat. Ini cara melacak sparepart dan bahan habis pakai di bengkel, laundry, atau reparasi agar stok dan biaya tidak bocor.',
                'meta_title' => 'Cara Melacak Sparepart & Bahan Habis Pakai Jasa Servis',
                'meta_description' => 'Cara melacak sparepart dan bahan habis pakai di usaha jasa servis: pisahkan jenis stok, tetapkan pemakaian standar per layanan, dan cek selisih mingguan.',
                'content' => <<<'MD'
Usaha jasa jarang menganggap dirinya punya masalah stok. Yang dijual adalah keahlian dan waktu. Padahal setiap servis memakai sesuatu: oli dan busi di bengkel, deterjen dan plastik di laundry, timah solder dan thermal paste di reparasi elektronik. Karena nilainya kecil per transaksi, pemakaian ini jarang dicatat — sampai tiba-tiba stok habis di tengah hari atau pengeluaran belanja bahan bulan ini dua kali lipat tanpa tahu sebabnya.

## Pisahkan Dua Jenis Stok

Langkah pertama adalah membedakan dua jenis barang:

1. **Sparepart yang dijual ke pelanggan** — oli, busi, kampas rem, LCD, baterai. Barang ini muncul di nota dengan harga sendiri.
2. **Bahan habis pakai (consumable)** — cairan pembersih karburator, majun, lem, pelumas semprot, deterjen. Barang ini tidak ditagih terpisah, tapi biayanya sudah termasuk di tarif jasa.

Sparepart relatif mudah dilacak karena tercatat di nota. Bahan habis pakai yang sering bocor, karena tidak ada dokumen yang mencatat keluarnya.

## Tetapkan Pemakaian Standar per Layanan

Cara paling praktis melacak bahan habis pakai adalah menetapkan berapa yang biasanya dipakai untuk setiap layanan. Contoh untuk bengkel motor:

| Layanan | Bahan habis pakai | Pemakaian standar |
|---|---|---|
| Tune up | Cairan pembersih karburator | 50 ml |
| Tune up | Majun | 1 lembar |
| Servis CVT | Grease | 10 g |
| Servis CVT | Cairan pembersih | 30 ml |
| Ganti oli | Majun | 1 lembar |

Untuk laundry kiloan:

| Layanan | Bahan | Pemakaian per kg |
|---|---|---|
| Cuci kering | Deterjen cair | 20 ml |
| Cuci kering | Pewangi | 10 ml |
| Cuci setrika | Plastik kemasan | 0,25 lembar |

Angka ini tidak harus presisi. Tujuannya adalah punya patokan untuk dibandingkan dengan pemakaian nyata.

## Hitung Pemakaian Teoritis vs Nyata

Setiap minggu, hitung pemakaian teoritis dari jumlah layanan yang dikerjakan. Misalnya minggu ini ada 60 tune up: pemakaian teoritis cairan pembersih karburator = 60 × 50 ml = 3 liter.

Lalu hitung pemakaian nyata dari stok: stok awal minggu 6 liter, belanja 2 liter, stok akhir 3,8 liter. Pemakaian nyata = 6 + 2 − 3,8 = **4,2 liter**.

Selisih 1,2 liter (40%) terlalu besar untuk disebut wajar. Kemungkinan penyebabnya:

- standar pemakaian terlalu kecil dan perlu diperbarui,
- bahan dipakai untuk pekerjaan yang tidak dicatat (misalnya servis kendaraan sendiri),
- tumpah atau terbuang karena wadah besar sulit dituang,
- ada yang mengambil.

Selisih 5–10% biasanya masih wajar. Di atas itu, cari penyebabnya sebelum menyesuaikan standar.

## Simpan di Satu Tempat dengan Satu Penanggung Jawab

Bahan habis pakai yang tersebar di tiap meja kerja tidak bisa dihitung. Simpan stok utama di satu rak atau lemari, dan keluarkan dalam jumlah kecil ke area kerja. Tunjuk satu orang yang mencatat setiap kali stok utama diambil. Cara sederhana ini saja sudah memangkas banyak kebocoran.

## Tentukan Titik Pesan Ulang

Untuk setiap bahan, tentukan stok minimum sebelum harus belanja lagi:

**Stok minimum = pemakaian rata-rata per hari × waktu tunggu belanja (hari) + cadangan**

Jika rata-rata pemakaian deterjen 1,5 liter/hari, supplier butuh 2 hari untuk mengirim, dan Anda ingin cadangan 2 hari, maka stok minimum = 1,5 × 2 + 1,5 × 2 = **6 liter**. Begitu stok menyentuh 6 liter, pesan lagi.

## Masukkan ke Perhitungan Tarif

Setelah pemakaian standar per layanan diketahui, biayanya bisa dimasukkan ke tarif jasa dengan akurat. Jika cairan pembersih Rp60.000/liter, maka 50 ml per tune up = Rp3.000. Angka ini sering hilang dari hitungan tarif. Lihat [cara menghitung harga jasa servis kecil agar tidak rugi](/blog/cara-menghitung-harga-jasa-servis-kecil-agar-tidak-rugi). Supaya pekerjaan tidak menumpuk di satu teknisi, baca juga [cara mengatur jadwal teknisi bengkel agar tidak bentrok](/blog/cara-mengatur-jadwal-teknisi-bengkel-servis-agar-tidak-bentrok).

## Pertanyaan yang Sering Muncul

**Apakah majun dan lap perlu dicatat juga?**
Jika belanjanya rutin dan nilainya berarti dalam sebulan, ya. Jika sangat kecil, cukup catat total belanja bulanan sebagai overhead.

**Bagaimana dengan sparepart yang dipakai untuk garansi?**
Catat sebagai pengeluaran sparepart tanpa penjualan, dengan keterangan garansi. Jangan dibiarkan tidak tercatat karena akan terlihat seperti barang hilang saat stok opname.

**Seberapa sering stok perlu dihitung?**
Bahan yang paling sering dipakai cukup dihitung mingguan. Sparepart bernilai tinggi lebih baik dicek setiap kali keluar.

Di Fabriku, bahan habis pakai bisa dipetakan ke setiap layanan beserta jumlah pemakaiannya. Saat transaksi melalui kasir cepat disimpan, stok bahan tersebut berkurang otomatis, dan transaksi ditolak bila stok tidak cukup. Sparepart yang dijual bisa masuk ke transaksi yang sama sebagai baris produk. Lihat [Fabriku untuk bengkel dan jasa servis](/aplikasi/bengkel-jasa-servis).
MD,
            ],
            [
                'slug' => 'cara-kelola-stok-reseller-dropship-kosmetik',
                'title' => 'Cara Kelola Stok Reseller dan Dropship Kosmetik tanpa Selisih',
                'category' => 'Manajemen Stok',
                'tags' => ['Kosmetik', 'Reseller', 'UMKM'],
                'days_ago' => 3,
                'excerpt' => 'Brand kosmetik lokal yang menjual lewat reseller dan dropshipper sering kehilangan jejak stok: barang sudah dijanjikan ke dropshipper, ternyata habis untuk reseller. Ini cara mengelolanya.',
                'meta_title' => 'Cara Kelola Stok Reseller & Dropship Kosmetik',
                'meta_description' => 'Cara kelola stok reseller dan dropship untuk brand kosmetik dan skincare lokal: bedakan model penjualan, alokasi stok, catat batch, dan rekonsiliasi bulanan.',
                'content' => <<<'MD'
Banyak brand kosmetik dan skincare lokal tumbuh lewat jaringan reseller dan dropshipper. Penjualan naik cepat, tapi pencatatan stok sering tertinggal. Gejalanya mudah dikenali: dropshipper sudah menerima pesanan, ternyata stok habis karena baru diambil reseller; reseller mengaku stoknya sudah habis terjual, tapi tidak jelas berapa yang masih ada di tangannya; dan saat ada keluhan produk, batch yang dikirim ke reseller tertentu tidak bisa ditelusuri.

## Bedakan Tiga Model Penjualan

Sebelum mengatur stok, pastikan jelas model mana yang dipakai, karena perlakuan stoknya berbeda:

| Model | Barang pindah tangan | Stok tercatat di |
|---|---|---|
| **Reseller beli putus** | Saat reseller membayar | Keluar dari gudang Anda, selesai |
| **Reseller titip jual (konsinyasi)** | Saat reseller menjual ke pembeli | Masih milik Anda sampai terjual |
| **Dropship** | Anda mengirim langsung ke pembeli dropshipper | Gudang Anda, keluar saat dikirim |

Masalah paling sering muncul pada konsinyasi: barang sudah keluar gudang, tapi masih milik Anda. Jika dicatat sebagai "terjual", omzet terlihat lebih besar dari kenyataan. Jika tidak dicatat sama sekali, stok di gudang terlihat hilang.

## Catat Stok Titipan sebagai Lokasi Terpisah

Cara paling mudah: perlakukan setiap reseller konsinyasi sebagai **lokasi stok** tersendiri. Saat mengirim 50 serum ke Reseller A, catat sebagai perpindahan stok dari *Gudang* ke *Titipan Reseller A*, bukan sebagai penjualan. Stok total Anda tidak berubah; yang berubah hanya lokasinya.

Penjualan baru dicatat saat reseller melaporkan barang terjual dan membayar. Saat itu stok di lokasi *Titipan Reseller A* berkurang.

Dengan cara ini Anda selalu bisa menjawab: berapa stok di gudang yang bisa dijual sekarang, dan berapa yang ada di tangan setiap reseller.

## Alokasikan Stok untuk Dropship

Dropship dan penjualan reseller mengambil dari gudang yang sama. Agar tidak saling menghabiskan, tentukan alokasi. Misalnya dari 500 unit toner:

- 300 unit untuk reseller beli putus,
- 150 unit untuk dropship,
- 50 unit cadangan untuk penjualan langsung dan penggantian produk rusak.

Alokasi ini tidak harus kaku. Tinjau setiap minggu berdasarkan channel mana yang paling cepat habis. Yang penting, dropshipper tidak menjanjikan barang yang sebenarnya sudah habis.

## Selalu Catat Batch dan Tanggal Kadaluarsa

Untuk kosmetik, setiap pengiriman ke reseller sebaiknya mencatat **nomor batch** dan **tanggal kadaluarsa**. Manfaatnya:

1. Jika ada keluhan iritasi atau produk berubah warna, Anda tahu batch mana yang perlu ditarik dan reseller mana yang menerimanya.
2. Anda bisa memastikan reseller menerima batch yang lebih lama lebih dulu, sehingga stok di gudang tidak tertinggal sampai kedaluwarsa.
3. Reseller yang menyimpan stok lama bisa diingatkan sebelum produknya mendekati kadaluarsa.

## Rekonsiliasi Bulanan dengan Reseller

Setiap akhir bulan, cocokkan data dengan setiap reseller konsinyasi:

| Item | Jumlah |
|---|---|
| Stok titipan awal bulan | 40 |
| Kiriman baru bulan ini | 50 |
| Dilaporkan terjual | 62 |
| Retur ke gudang | 3 |
| **Seharusnya tersisa** | **25** |
| Hasil hitung fisik reseller | 23 |
| **Selisih** | **−2** |

Selisih harus dibicarakan: apakah ada penjualan yang belum dilaporkan, barang rusak, atau hilang. Tetapkan aturan siapa yang menanggung selisih sejak awal kerja sama.

## Atur Harga per Level

Harga untuk reseller, dropshipper, dan pembeli langsung biasanya berbeda. Pastikan setiap pesanan tercatat dengan channel-nya, sehingga laporan bisa menunjukkan margin per channel. Sering kali channel yang omzetnya paling besar ternyata bukan yang paling menguntungkan setelah dipotong diskon reseller dan ongkos kirim.

## Pertanyaan yang Sering Muncul

**Lebih baik beli putus atau konsinyasi?**
Beli putus lebih sederhana dan arus kas lebih aman. Konsinyasi membantu menarik reseller baru, tapi butuh pencatatan dan rekonsiliasi lebih disiplin.

**Bagaimana jika reseller menjual di bawah harga yang disepakati?**
Itu urusan kebijakan harga, bukan stok, tapi data penjualan per channel membantu mendeteksinya: reseller yang tiba-tiba menjual jauh lebih banyak dari biasanya patut ditanya.

**Apakah dropshipper perlu melihat stok?**
Sebaiknya ya, minimal stok yang dialokasikan untuk dropship. Ini mengurangi pesanan yang harus dibatalkan.

Fabriku mencatat batch dan tanggal kadaluarsa wajib untuk kategori kosmetik, mendukung banyak lokasi stok beserta perpindahan antar lokasi, dan setiap sales order punya channel (offline, online, marketplace, atau reseller). Untuk pemantauan masa simpan, baca juga [cara mengelola stok kosmetik agar tidak kadaluarsa](/blog/cara-mengelola-stok-kosmetik-agar-tidak-kadaluarsa) dan lihat [Fabriku untuk kosmetik dan skincare](/aplikasi/kosmetik-skincare).
MD,
            ],
            [
                'slug' => 'cara-menyiapkan-stok-umkm-menjelang-ramadan-lebaran',
                'title' => 'Cara Menyiapkan Stok UMKM Menjelang Ramadan dan Lebaran',
                'category' => 'Manajemen Stok',
                'tags' => ['Stok Musiman', 'Retail', 'UMKM'],
                'days_ago' => 4,
                'excerpt' => 'Permintaan saat Ramadan dan Lebaran bisa naik berkali lipat, tapi supplier juga sibuk dan harga bahan naik. Ini cara memperkirakan dan menyiapkan stok musiman UMKM dari data tahun lalu.',
                'meta_title' => 'Cara Menyiapkan Stok UMKM Menjelang Ramadan & Lebaran',
                'meta_description' => 'Cara menyiapkan stok UMKM menjelang Ramadan dan Lebaran: data penjualan tahun lalu, hitung kebutuhan, jadwal belanja bahan, dan sisa stok.',
                'content' => <<<'MD'
Bagi banyak UMKM, Ramadan dan Lebaran adalah musim penjualan terbesar dalam setahun. Konveksi kebanjiran pesanan baju muslim, usaha kue menerima pesanan hampers dan kue kering, toko kelontong menjual sirup dan biskuit kaleng berlipat-lipat. Masalahnya, persiapan sering dilakukan terlambat: bahan baku dibeli saat harga sudah naik, supplier kehabisan stok, dan produksi kejar tayang di minggu terakhir.

Ramadan 2027 diperkirakan dimulai sekitar awal Februari, dengan Lebaran sekitar awal Maret. Artinya persiapan stok idealnya dimulai **Desember hingga awal Januari**.

## Langkah 1: Buka Data Penjualan Tahun Lalu

Perkiraan terbaik datang dari data sendiri. Ambil penjualan per produk untuk tiga periode tahun lalu:

1. **Bulan normal** sebelum Ramadan (sebagai pembanding).
2. **Ramadan** — biasanya terbagi dua: minggu 1–2 masih normal, minggu 3–4 mulai naik tajam.
3. **Seminggu sebelum Lebaran** — puncak permintaan.

Contoh dari toko kue kering:

| Produk | Rata-rata per minggu (normal) | Minggu 3–4 Ramadan | Seminggu sebelum Lebaran |
|---|---|---|---|
| Nastar toples 500 g | 15 | 80 | 140 |
| Kastengel toples 500 g | 10 | 55 | 95 |
| Putri salju toples 500 g | 8 | 40 | 60 |

Dari sini terlihat kenaikan 5–9 kali lipat, dan setiap produk naik dengan pola berbeda.

## Langkah 2: Sesuaikan dengan Kondisi Tahun Ini

Data tahun lalu bukan ramalan pasti. Sesuaikan dengan:

- **Pertumbuhan usaha.** Jika penjualan bulan normal tahun ini sudah 30% lebih tinggi, naikkan perkiraan dengan proporsi yang mirip, tapi lebih konservatif (misalnya 20%).
- **Produk baru atau yang dihentikan.**
- **Kapasitas produksi.** Perkiraan penjualan tidak berguna jika dapur atau penjahit hanya mampu memproduksi setengahnya.
- **Pesanan yang sudah pasti**, seperti pesanan hampers dari kantor atau reseller yang sudah membayar DP.

## Langkah 3: Hitung Kebutuhan Bahan Baku

Ubah perkiraan penjualan menjadi kebutuhan bahan dengan resep atau BOM. Untuk nastar 140 toples di minggu puncak, jika satu toples membutuhkan 250 g tepung, 150 g mentega, dan 120 g selai nanas, maka minggu itu saja butuh 35 kg tepung, 21 kg mentega, dan 16,8 kg selai nanas.

Jumlahkan untuk seluruh periode Ramadan, tambahkan cadangan 10–15% untuk produk gagal dan pesanan mendadak. Untuk konveksi, gunakan BOM per ukuran seperti di [cara membuat BOM produksi konveksi](/blog/cara-membuat-bom-produksi-konveksi-per-ukuran-warna).

## Langkah 4: Jadwalkan Belanja Bertahap

Membeli semua bahan sekaligus di awal butuh modal besar dan tempat penyimpanan. Membeli semuanya mendekati Lebaran berisiko harga naik dan barang kosong. Jalan tengahnya adalah belanja bertahap:

| Waktu | Yang dibeli |
|---|---|
| 6–8 minggu sebelum Ramadan | Bahan tahan lama: kemasan, toples, label, kain, aksesoris |
| 3–4 minggu sebelum Ramadan | Bahan kering: tepung, gula, bahan setengah jadi |
| Selama Ramadan, per minggu | Bahan cepat rusak: mentega, telur, susu, bahan segar |

Konfirmasi ketersediaan dengan supplier sejak awal. Supplier kemasan dan kain biasanya penuh pesanan menjelang Ramadan.

## Langkah 5: Perhatikan Tanggal Kadaluarsa

Stok besar membawa risiko besar untuk bahan yang punya masa simpan. Saat menerima bahan, catat tanggal kadaluarsa dan susun rak agar bahan yang lebih dulu kedaluwarsa dipakai lebih dulu. Untuk produk jadi seperti kue kering, hitung mundur dari tanggal kadaluarsa: kue yang dibuat terlalu awal mungkin sudah lewat masa terbaiknya saat Lebaran.

## Langkah 6: Rencanakan Sisa Stok Setelah Lebaran

Setelah Lebaran, permintaan turun drastis. Rencanakan sejak awal:

- Hentikan produksi produk musiman 5–7 hari sebelum Lebaran, lalu jual dari stok.
- Siapkan promo pasca-Lebaran untuk produk yang masih tersisa.
- Bahan baku yang tersisa dan masih aman dipakai bisa dialihkan ke produk reguler.

## Langkah 7: Catat untuk Tahun Depan

Setelah musim selesai, catat: perkiraan vs penjualan nyata per produk, bahan yang kehabisan atau berlebih, dan supplier yang terlambat. Catatan ini membuat perkiraan tahun depan jauh lebih akurat.

## Pertanyaan yang Sering Muncul

**Bagaimana jika belum punya data tahun lalu?**
Gunakan penjualan bulan biasa sebagai dasar dan tanyakan pola kenaikan ke sesama pelaku usaha di bidang yang sama. Mulai catat tahun ini agar tahun depan punya data sendiri.

**Apakah perlu pinjaman modal untuk stok musiman?**
Hitung dulu perkiraan omzet dan margin. Jika pesanan sudah ada DP, gunakan DP untuk belanja bahan sebelum mengambil pinjaman.

**Kapan sebaiknya berhenti menerima pesanan?**
Saat kapasitas produksi sudah penuh. Lebih baik menolak pesanan daripada terlambat mengirim pesanan yang sudah dibayar.

Fabriku menyimpan riwayat penjualan per periode dan per produk, sehingga data Ramadan tahun lalu bisa dibuka dan diekspor ke Excel untuk perkiraan. Bahan dicatat dengan tanggal kadaluarsa untuk kategori makanan dan kosmetik. Lihat [Fabriku untuk usaha makanan dan kue](/aplikasi/usaha-makanan-kue) atau [Fabriku untuk toko retail](/aplikasi/toko-retail).
MD,
            ],
            [
                'slug' => 'cara-kelola-stok-banyak-cabang-outlet-umkm',
                'title' => 'Cara Kelola Stok Banyak Cabang atau Outlet UMKM dari Satu Tempat',
                'category' => 'Manajemen Stok',
                'tags' => ['Multi Cabang', 'Retail', 'UMKM'],
                'days_ago' => 5,
                'excerpt' => 'Membuka cabang kedua sering membuat stok kacau: cabang A kehabisan barang yang menumpuk di cabang B. Ini cara mengelola stok banyak outlet UMKM dari satu pusat data.',
                'meta_title' => 'Cara Kelola Stok Banyak Cabang/Outlet UMKM',
                'meta_description' => 'Cara kelola stok banyak cabang atau outlet UMKM: satu sumber data, kode barang seragam, transfer antar cabang, stok minimum, dan opname bergilir.',
                'content' => <<<'MD'
Membuka cabang kedua adalah tanda usaha tumbuh. Tapi bagi banyak UMKM, cabang kedua juga awal kekacauan stok. Setiap cabang punya buku catatan sendiri, pemilik harus menelepon satu per satu untuk tahu stok, dan barang yang habis di satu cabang ternyata menumpuk di cabang lain. Semakin banyak cabang, semakin besar modal yang tertahan di barang yang tidak bergerak.

## Prinsip Utama: Satu Sumber Data

Kesalahan paling umum adalah membiarkan setiap cabang mencatat dengan caranya sendiri. Satu cabang memakai buku, satu lagi Excel, satu lagi hanya mengandalkan ingatan kepala toko. Hasilnya tidak bisa digabung.

Apa pun alatnya, semua cabang harus mencatat ke **satu sumber data yang sama**, dengan format yang sama. Setiap cabang menjadi satu **lokasi** di dalam data stok, bukan pembukuan terpisah.

## Seragamkan Kode dan Nama Barang

Sebelum menggabungkan data, pastikan setiap barang punya satu kode (SKU) dan satu nama yang sama di semua cabang. "Sirup Melon 460ml" di cabang A tidak boleh tercatat sebagai "Sirup Mlon kecil" di cabang B. Tanpa ini, laporan gabungan akan menghitungnya sebagai dua barang berbeda.

Buat satu daftar barang induk. Cabang tidak boleh menambah barang baru sendiri; permintaan barang baru diajukan ke pusat.

## Tetapkan Stok Minimum per Cabang

Setiap cabang punya pola penjualan berbeda. Cabang dekat sekolah mungkin menjual banyak alat tulis, cabang dekat perumahan menjual banyak kebutuhan dapur. Jadi stok minimum tidak bisa disamaratakan.

Hitung stok minimum per barang per cabang dari penjualan rata-rata:

**Stok minimum = penjualan rata-rata per hari × waktu kirim dari gudang/supplier (hari) + cadangan**

| Barang | Cabang | Rata-rata terjual/hari | Waktu kirim | Cadangan | Stok minimum |
|---|---|---|---|---|---|
| Minyak goreng 2 L | Pasar | 12 | 2 hari | 12 | 36 |
| Minyak goreng 2 L | Perumahan | 5 | 2 hari | 5 | 15 |

## Gunakan Transfer Stok, Bukan Belanja Baru

Saat satu cabang kekurangan, cek dulu apakah cabang lain kelebihan. Memindahkan stok antar cabang hampir selalu lebih murah daripada belanja baru, apalagi untuk barang yang perputarannya lambat.

Setiap perpindahan harus tercatat sebagai **transfer**: stok berkurang di cabang asal dan bertambah di cabang tujuan pada saat yang sama. Jika hanya dicatat di salah satu sisi, stok total akan terlihat bertambah atau hilang.

Tetapkan aturan sederhana: barang yang tidak terjual dalam 30 hari di satu cabang tapi laku di cabang lain, dipindahkan setiap minggu.

## Pisahkan Gudang Pusat jika Perlu

Jika belanja dilakukan terpusat, buat satu lokasi *Gudang Pusat*. Semua barang dari supplier masuk ke gudang pusat dulu, lalu didistribusikan ke cabang dengan transfer. Ini membuat harga modal dan penerimaan barang tercatat di satu tempat, dan cabang hanya fokus menjual.

Untuk usaha dengan 2–3 cabang kecil, belanja langsung ke cabang masih masuk akal, asal penerimaan dicatat ke lokasi cabang yang benar.

## Batasi Akses per Peran

Kepala cabang perlu mencatat penjualan dan penerimaan, tapi tidak perlu mengubah harga atau menghapus barang. Beri hak akses sesuai peran: kasir mencatat penjualan, kepala cabang menerima barang dan mengajukan transfer, pemilik atau admin pusat mengatur harga dan daftar barang.

## Opname Bergilir per Cabang

Menghitung stok semua cabang sekaligus melelahkan dan mengganggu operasional. Lakukan opname bergilir: minggu pertama cabang A, minggu kedua cabang B, dan seterusnya. Di dalam setiap cabang, hitung per rak atau per kategori. Caranya dijelaskan di [cara stok opname toko retail tanpa tutup toko](/blog/cara-stok-opname-toko-retail-tanpa-tutup-toko).

Bandingkan selisih antar cabang. Cabang yang selisihnya selalu lebih besar dari yang lain perlu diperiksa prosedurnya.

## Laporan yang Perlu Dilihat Pemilik

Setiap minggu, cukup lihat empat hal:

1. Penjualan per cabang dan per kategori barang.
2. Barang yang di bawah stok minimum per cabang.
3. Barang yang tidak terjual dalam 30 hari per cabang.
4. Selisih opname per cabang.

## Pertanyaan yang Sering Muncul

**Apakah harga jual harus sama di semua cabang?**
Tidak harus, tapi harga modal sebaiknya dihitung terpusat. Jika harga jual berbeda, catat alasannya agar tidak membingungkan pelanggan yang datang ke dua cabang.

**Kapan perlu gudang pusat?**
Biasanya saat cabang sudah tiga atau lebih, atau saat belanja dalam jumlah besar memberi diskon yang berarti dari supplier.

**Bagaimana jika satu cabang belum ada internet stabil?**
Catat di buku dengan format kolom yang sama, lalu masukkan ke sistem setiap hari sebelum tutup. Yang penting datanya tetap masuk ke satu tempat.

Di Fabriku, setiap cabang atau gudang bisa dibuat sebagai lokasi stok tersendiri. Stok bisa dipindahkan antar lokasi dengan pencatatan di kedua sisi, setiap item dan lokasi punya QR code untuk dicek cepat, dan hak akses staf diatur per peran. Lihat [Fabriku untuk toko retail](/aplikasi/toko-retail).
MD,
            ],
            [
                'slug' => 'cara-mencatat-produksi-banyak-produk-satu-dapur-rumahan',
                'title' => 'Cara Mencatat Produksi Banyak Produk dalam Satu Dapur Rumahan',
                'category' => 'Produksi',
                'tags' => ['Produksi Rumahan', 'Kuliner Rumahan', 'UMKM'],
                'days_ago' => 6,
                'excerpt' => 'Satu dapur, satu stok bahan, lima produk berbeda. Tanpa pencatatan yang tepat, tidak jelas produk mana yang menghabiskan bahan dan mana yang benar-benar menguntungkan.',
                'meta_title' => 'Cara Mencatat Produksi Banyak Produk di Dapur Rumahan',
                'meta_description' => 'Cara mencatat produksi banyak produk dalam satu dapur rumahan: resep standar, catatan produksi harian, pembagian bahan bersama, dan untung per produk.',
                'content' => <<<'MD'
Usaha rumahan jarang hanya membuat satu produk. Dapur yang sama dipakai untuk brownies, bolu, cookies, dan kadang pesanan nasi kotak. Bahan seperti tepung, gula, telur, dan mentega dipakai bersama. Di akhir bulan, yang terlihat hanya total belanja bahan dan total uang masuk. Pertanyaan penting tidak bisa dijawab: produk mana yang paling menguntungkan, dan produk mana yang diam-diam menghabiskan bahan paling banyak?

## Mulai dari Resep Standar

Langkah pertama adalah menuliskan resep standar untuk setiap produk, lengkap dengan takaran dan jumlah hasil. Resep "secukupnya" atau "kira-kira dua gelas" tidak bisa dihitung.

Contoh:

| Produk | Hasil per adonan | Tepung | Gula | Telur | Mentega |
|---|---|---|---|---|---|
| Brownies 20×20 | 16 potong | 120 g | 200 g | 4 butir | 150 g |
| Bolu pandan | 1 loyang (12 potong) | 200 g | 180 g | 6 butir | 100 g |
| Cookies coklat | 40 keping | 250 g | 150 g | 2 butir | 175 g |

Timbang bahan saat membuat, minimal untuk beberapa kali produksi pertama, agar takaran di resep sesuai kenyataan.

## Catat Produksi Harian per Produk

Setiap kali selesai produksi, catat satu baris per produk:

| Tanggal | Produk | Jumlah adonan | Hasil layak jual | Gagal/pecah |
|---|---|---|---|---|
| 10 Okt | Brownies | 3 | 46 potong | 2 |
| 10 Okt | Cookies | 2 | 78 keping | 2 |
| 11 Okt | Bolu pandan | 2 | 24 potong | 0 |

Dari jumlah adonan dan resep standar, pemakaian bahan bisa dihitung tanpa harus menimbang ulang. Tiga adonan brownies berarti 360 g tepung, 600 g gula, 12 butir telur, dan 450 g mentega.

Kolom **gagal/pecah** penting. Jika satu produk sering gagal, biaya sebenarnya lebih tinggi dari hitungan resep.

## Bagi Bahan Bersama Berdasarkan Pemakaian

Tepung dan gula dibeli satu karung untuk semua produk. Untuk tahu biaya per produk, kalikan pemakaian dari catatan produksi dengan harga bahan per gram. Jika tepung Rp12.000/kg, maka 360 g tepung untuk brownies hari itu bernilai Rp4.320.

Untuk biaya yang tidak bisa dihitung per produk, seperti gas dan listrik, bagi berdasarkan jumlah adonan atau lama pemakaian oven dalam sebulan. Tidak perlu presisi; yang penting dibagi dengan cara yang konsisten.

## Cocokkan dengan Stok Bahan

Setiap minggu, bandingkan pemakaian bahan menurut catatan produksi dengan bahan yang benar-benar berkurang di dapur:

**Pemakaian nyata = stok awal + belanja − stok akhir**

Jika pemakaian nyata jauh lebih besar dari catatan, ada produksi yang tidak dicatat, takaran resep yang sudah bergeser, atau bahan terbuang. Selisih kecil wajar; selisih besar perlu dicari penyebabnya.

## Hitung Untung per Produk

Setelah biaya bahan per produk diketahui, gabungkan dengan data penjualan:

| Produk | Terjual sebulan | Harga jual | Omzet | Biaya bahan + overhead | Untung kotor | Margin |
|---|---|---|---|---|---|---|
| Brownies | 480 potong | Rp8.000 | Rp3.840.000 | Rp2.400.000 | Rp1.440.000 | 37,5% |
| Cookies | 900 keping | Rp2.500 | Rp2.250.000 | Rp1.170.000 | Rp1.080.000 | 48% |
| Bolu pandan | 200 potong | Rp6.000 | Rp1.200.000 | Rp900.000 | Rp300.000 | 25% |

Dari contoh ini, bolu pandan omzetnya paling kecil dan marginnya juga paling tipis. Pilihannya: naikkan harga, cari bahan yang lebih efisien, atau kurangi produksinya dan alihkan waktu dapur ke cookies yang marginnya lebih besar.

## Jadwalkan Produksi agar Dapur Tidak Rebutan

Dengan beberapa produk dalam satu dapur, urutan produksi memengaruhi efisiensi. Kelompokkan produk yang memakai suhu oven sama di hari yang sama, buat adonan yang perlu didiamkan lebih dulu, dan hindari membuat produk bertekstur basah dan kering bergantian tanpa jeda membersihkan peralatan.

## Pertanyaan yang Sering Muncul

**Perlukah menimbang bahan setiap kali produksi?**
Tidak, jika resep standar sudah akurat. Cukup timbang ulang sesekali, misalnya sebulan sekali, untuk memastikan takaran tidak bergeser.

**Bagaimana dengan pesanan custom yang resepnya berbeda?**
Catat sebagai produk tersendiri atau tambahkan baris catatan bahan tambahan untuk pesanan tersebut.

**Kapan pencatatan manual ini sudah tidak cukup?**
Biasanya saat produk sudah lebih dari lima, pesanan datang setiap hari, atau ada lebih dari satu orang yang memproduksi. Baca [kapan usaha rumahan butuh sistem pencatatan](/blog/kapan-usaha-rumahan-butuh-sistem-pencatatan).

Fabriku menyediakan pencatatan produksi sederhana untuk usaha rumahan: pilih bahan yang dipakai dan jumlah hasil jadi dalam satu form, lalu stok bahan berkurang dan stok produk bertambah otomatis, dengan biaya per unit dihitung dari bahan yang dipakai. Lihat [Fabriku untuk produksi rumahan](/aplikasi/produksi-rumahan). Untuk menjual hasil produksi lewat website sendiri, lihat [contoh website toko online UMKM](/blog/contoh-website-toko-online-umkm) dari usaha camilan rumahan di Bandung.
MD,
            ],
            [
                'slug' => 'kartu-stok-barang-contoh-cara-mengisi-template-excel',
                'title' => 'Kartu Stok Barang: Contoh, Cara Mengisi, dan Template Excel untuk Toko dan Gudang',
                'category' => 'Manajemen Stok',
                'tags' => ['Stok Barang', 'Retail', 'Excel'],
                'days_ago' => 0,
                'excerpt' => 'Kartu stok barang adalah catatan keluar-masuk satu barang yang menunjukkan sisa stok kapan saja. Ini contoh kartu stok barang, cara mengisinya, versi gudang, dan template Excel yang bisa langsung dipakai.',
                'meta_title' => 'Contoh Kartu Stok Barang + Cara Mengisi & Template Excel',
                'meta_description' => 'Contoh kartu stok barang untuk toko dan gudang, fungsi kartu stok, cara mengisi kolom masuk-keluar-sisa, dan template kartu stok barang di Excel.',
                'content' => <<<'MD'
Stok di catatan bilang masih 20, di rak tinggal 12. Selisih seperti ini hampir selalu terjadi karena barang masuk dan keluar tidak dicatat di satu tempat yang sama. Alat paling sederhana untuk mencegahnya adalah **kartu stok barang**.

## Apa Itu Kartu Stok Barang

Kartu stok barang adalah catatan semua pergerakan satu jenis barang: kapan masuk, kapan keluar, berapa jumlahnya, dan berapa sisanya setelah setiap transaksi. Satu barang punya satu kartu. Toko dengan 200 jenis barang berarti punya 200 kartu stok, baik berupa lembar kertas, sheet Excel, maupun data di aplikasi.

Fungsi kartu stok:

1. **Mengetahui sisa stok kapan saja** tanpa menghitung fisik setiap hari.
2. **Menelusuri selisih.** Saat hitungan fisik berbeda dengan kartu, Anda bisa melihat transaksi mana yang mencurigakan.
3. **Dasar belanja ulang.** Kecepatan barang keluar terlihat dari kolom keluar, jadi Anda tahu kapan harus pesan lagi.
4. **Bukti untuk stok opname.** Hasil hitung fisik dicocokkan dengan sisa di kartu.

Kartu stok hanya mencatat **jumlah**. Bila setiap baris juga mencatat nilai rupiah (harga beli per unit dan nilai sisa), namanya kartu persediaan, yang dipakai untuk menghitung nilai stok di laporan keuangan.

## Kolom Wajib di Kartu Stok

Bagian atas kartu berisi identitas barang: nama barang, kode/SKU, satuan, lokasi rak, dan stok minimum. Bagian tabelnya berisi:

- **Tanggal** transaksi.
- **No. bukti**: nomor nota pembelian, nomor pesanan, atau keterangan "rekap kasir".
- **Keterangan**: dari supplier mana, untuk siapa, atau alasan keluar (terjual, rusak, retur, pindah gudang).
- **Masuk**: jumlah barang yang bertambah.
- **Keluar**: jumlah barang yang berkurang.
- **Sisa**: sisa stok setelah transaksi = sisa sebelumnya + masuk − keluar.
- **Paraf** petugas, bila lebih dari satu orang yang mengisi.

## Contoh Kartu Stok Barang

Contoh kartu stok untuk minyak goreng 2 liter di sebuah toko, satu minggu pertama Oktober:

| Tanggal | No. Bukti | Keterangan | Masuk | Keluar | Sisa |
|---|---|---|---|---|---|
| 1 Okt | — | Saldo awal | | | 24 |
| 2 Okt | PB-0012 | Pembelian dari CV Sumber Rejeki | 36 | | 60 |
| 2 Okt | Rekap kasir | Penjualan hari ini | | 14 | 46 |
| 3 Okt | Rekap kasir | Penjualan hari ini | | 18 | 28 |
| 4 Okt | — | Rusak, kemasan bocor | | 2 | 26 |
| 5 Okt | Rekap kasir | Penjualan hari ini | | 11 | 15 |
| 5 Okt | Opname | Hitung fisik 14, selisih −1 | | 1 | 14 |

Dari satu kartu ini terlihat: rata-rata terjual sekitar 14 pouch per hari, stok 14 hanya cukup untuk sekitar satu hari, dan ada satu pouch hilang yang perlu dicari penyebabnya. Tanpa kartu stok, yang diketahui hanya "minyak tinggal sedikit".

## Cara Mengisi Kartu Stok Barang

1. **Mulai dari saldo awal hasil hitung fisik**, bukan dari perkiraan. Kartu yang dimulai dari angka salah akan salah terus.
2. **Catat setiap barang masuk saat diterima**, dengan nomor nota supplier. Barang yang datang tapi belum dicatat adalah penyebab paling umum stok terlihat kurang.
3. **Catat barang keluar dengan alasannya.** Terjual, rusak, kedaluwarsa, retur ke supplier, dipakai sendiri, atau dipindah ke gudang lain dicatat sebagai baris terpisah. Alasan ini yang membuat selisih bisa ditelusuri.
4. **Hitung sisa di setiap baris**, bukan hanya di akhir hari.
5. **Cocokkan dengan hitung fisik secara berkala.** Selisih dicatat sebagai baris koreksi dengan keterangan "opname", bukan dengan menimpa angka sisa.

Untuk toko dengan puluhan transaksi sehari, mencatat setiap struk ke kartu stok tidak realistis. Cukup tulis satu baris **rekap penjualan per barang** di akhir hari, seperti contoh di atas. Hasilnya tetap bisa dipakai, asalkan barang masuk dan barang rusak tetap dicatat per kejadian.

## Kartu Stok Gudang

Bila barang disimpan di lebih dari satu tempat, misalnya gudang belakang dan rak toko, buat **satu kartu per barang per lokasi**. Perpindahan barang dicatat dua kali: keluar di kartu gudang dan masuk di kartu rak toko, dengan nomor bukti yang sama. Dengan begitu stok per lokasi selalu jelas, dan barang yang "hilang di jalan" antara gudang dan toko langsung ketahuan.

Tambahkan kolom **lokasi rak** di identitas kartu. Petugas yang mencari barang tidak perlu bertanya, dan hitung fisik bisa dilakukan per rak.

## Template Kartu Stok Barang di Excel

Ada dua cara menyusun kartu stok di Excel.

**Cara 1: satu sheet per barang.** Kolom A = Tanggal, B = No. Bukti, C = Keterangan, D = Masuk, E = Keluar, F = Sisa. Isi saldo awal di F2, lalu di F3 tulis `=F2+D3-E3` dan tarik ke bawah. Cocok untuk barang yang sedikit jenisnya.

**Cara 2: satu sheet transaksi untuk semua barang.** Kolom A = Tanggal, B = Kode Barang, C = Keterangan, D = Masuk, E = Keluar. Di sheet lain, buat daftar barang dan hitung sisa dengan:

`=SUMIFS(Transaksi!D:D;Transaksi!B:B;A2)-SUMIFS(Transaksi!E:E;Transaksi!B:B;A2)`

dengan A2 berisi kode barang. Tambahkan kolom stok minimum dan format bersyarat (merah bila sisa ≤ minimum) untuk melihat barang yang harus dipesan. Cara kedua lebih tahan lama karena menambah barang baru cukup menambah satu baris di daftar.

Pemisah argumen `;` atau `,` tergantung pengaturan bahasa Excel Anda.

## Kesalahan yang Membuat Kartu Stok Tidak Bisa Dipercaya

- **Sisa ditimpa saat opname.** Angka lama hilang dan penyebab selisih tidak pernah ketahuan. Selalu catat koreksi sebagai baris baru.
- **Barang rusak dan retur tidak dicatat.** Kartu terlihat benar, tapi barang di rak berkurang tanpa jejak. Baca juga [cara kelola stok retur barang](/blog/cara-kelola-stok-retur-barang-umkm-retail).
- **Satuan tercampur.** Masuk dicatat per dus, keluar per pcs. Tetapkan satu satuan terkecil per kartu.
- **Pengisian ditunda.** Kartu yang diisi seminggu sekali dari ingatan hampir pasti meleset.

## Pertanyaan yang Sering Muncul

**Apa bedanya kartu stok dengan stok opname?**
Kartu stok adalah catatan berjalan setiap hari. Stok opname adalah kegiatan menghitung fisik barang untuk dicocokkan dengan kartu stok. Keduanya saling melengkapi; lihat [cara stok opname toko tanpa tutup toko](/blog/cara-stok-opname-toko-retail-tanpa-tutup-toko). Untuk toko dengan beberapa cabang, lihat [cara kelola stok banyak cabang dan outlet UMKM](/blog/cara-kelola-stok-banyak-cabang-outlet-umkm).

**Apakah usaha kecil perlu kartu stok untuk semua barang?**
Mulailah dari barang yang paling laku dan paling mahal, biasanya 20–30 barang yang menyumbang sebagian besar omzet. Barang lain cukup dicek saat opname rutin.

**Kartu stok bahan baku sama dengan kartu stok barang jadi?**
Formatnya sama. Untuk bahan baku, kolom keluar biasanya berisi pemakaian produksi. Bahan yang punya tanggal kedaluwarsa sebaiknya dicatat per penerimaan agar yang lama dipakai lebih dulu; lihat [cara mengelola stok bahan baku UMKM](/blog/cara-mengelola-stok-bahan-baku-umkm-agar-tidak-rugi). Untuk persiapan stok musiman, lihat [cara menyiapkan stok UMKM menjelang Ramadan dan Lebaran](/blog/cara-menyiapkan-stok-umkm-menjelang-ramadan-lebaran).

## Kapan Kartu Stok Manual Mulai Merepotkan

Kartu stok kertas atau Excel bekerja baik selama jenis barang sedikit dan satu orang yang mengisi. Begitu barang ratusan, ada beberapa lokasi, dan penjualan datang dari toko serta online sekaligus, menjumlahkan semua kartu menjadi pekerjaan harian tersendiri.

Di Fabriku, setiap item stok punya lokasi rak dan riwayat pergerakannya sendiri: pembelian dari supplier, hasil produksi, koreksi, barang rusak, hilang, retur, dan pindah rak tercatat sebagai baris riwayat, sementara stok berkurang otomatis saat pesanan selesai. Sisa stok per lokasi bisa dilihat tanpa menjumlahkan kartu satu per satu, dan laporan stok bisa diekspor ke Excel atau PDF. Lihat [Fabriku untuk toko retail](/aplikasi/toko-retail).
MD,
            ],
            [
                'slug' => 'cara-menghitung-hpp-makanan-per-porsi',
                'title' => 'Cara Menghitung HPP Makanan per Porsi: Contoh Nasi Ayam, Minuman, dan Template Excel',
                'category' => 'Keuangan',
                'tags' => ['HPP', 'Kuliner Rumahan', 'UMKM'],
                'days_ago' => 0,
                'excerpt' => 'HPP makanan dihitung dari harga bahan per gram dikali takaran resep, ditambah kemasan, gas, tenaga kerja, dan susut. Ini cara menghitung HPP makanan per porsi lengkap dengan contoh nasi ayam dan es kopi susu.',
                'meta_title' => 'Cara Menghitung HPP Makanan per Porsi (Contoh & Excel)',
                'meta_description' => 'Cara menghitung HPP makanan dan minuman per porsi: harga bahan per gram, contoh nasi ayam geprek dan es kopi susu, overhead, susut, dan template Excel.',
                'content' => <<<'MD'
Usaha makanan sering terlihat ramai tapi uangnya tidak bertambah. Penyebab yang paling sering ditemukan: harga jual ditentukan dari harga warung sebelah, bukan dari **HPP (harga pokok penjualan) per porsi**. Artikel ini membahas cara menghitungnya khusus untuk makanan dan minuman, di mana bahan dibeli per kilo tapi dijual per porsi.

## Komponen HPP Makanan

1. **Bahan utama**: beras, ayam, daging, tepung, telur, susu, kopi.
2. **Bumbu, minyak, dan pelengkap**: sering dianggap "sedikit" sehingga tidak dihitung, padahal bisa mencapai 10–20% dari biaya bahan.
3. **Kemasan**: box, cup, sendok, plastik, stiker. Untuk pesanan antar, kemasan bisa lebih mahal dari bumbunya.
4. **Gas dan listrik**: kompor, oven, freezer, blender.
5. **Tenaga kerja**: termasuk waktu pemilik bila ikut memasak.
6. **Susut dan gagal**: bahan terbuang, porsi gagal, sisa yang tidak terjual.

## Langkah 1: Ubah Harga Beli Menjadi Harga per Gram atau per Butir

Resep memakai gram dan mililiter, sedangkan nota belanja memakai kilo dan liter. Ubah semua ke satuan resep:

| Bahan | Harga beli | Isi | Harga per satuan resep |
|---|---|---|---|
| Beras | Rp14.000 | 1 kg | Rp14/g |
| Ayam potong | Rp40.000 | 1 kg | Rp40/g |
| Tepung bumbu | Rp10.000 | 500 g | Rp20/g |
| Minyak goreng | Rp36.000 | 2 L | Rp18/ml |
| Susu UHT | Rp18.000 | 1 L | Rp18/ml |

Pakai harga dari nota belanja terakhir, bukan harga yang diingat. Untuk ayam dan daging, hitung dari berat yang benar-benar dipakai setelah dibersihkan bila tulang dan kulit dibuang.

## Contoh 1: HPP Nasi Ayam Geprek per Porsi

| Komponen | Takaran per porsi | Biaya |
|---|---|---|
| Nasi | 100 g beras | Rp1.400 |
| Ayam | 100 g | Rp4.000 |
| Tepung bumbu | 30 g | Rp600 |
| Minyak terserap | 30 ml | Rp540 |
| Sambal (cabai, bawang, garam) | 1 porsi | Rp1.500 |
| Lalapan | 1 porsi | Rp500 |
| Kemasan (box, sendok, plastik) | 1 set | Rp1.800 |
| Gas (estimasi) | — | Rp400 |
| **Subtotal bahan + kemasan + gas** | | **Rp10.740** |

Biaya sambal dihitung dari satu kali membuat sambal dibagi jumlah porsi. Misalnya cabai, bawang, dan garam Rp45.000 untuk 30 porsi berarti Rp1.500 per porsi.

Tambahkan tenaga kerja. Bila satu orang dibayar Rp100.000 per hari dan menyiapkan 80 porsi, tenaga kerja per porsi Rp1.250. **HPP nasi ayam geprek = Rp10.740 + Rp1.250 = Rp11.990**, dibulatkan Rp12.000.

## Contoh 2: HPP Minuman per Cup (Es Kopi Susu Gula Aren)

| Komponen | Takaran per cup | Biaya |
|---|---|---|
| Kopi bubuk (Rp200.000/kg) | 18 g | Rp3.600 |
| Susu UHT | 150 ml | Rp2.700 |
| Gula aren cair (Rp40/ml) | 25 ml | Rp1.000 |
| Es batu | 1 cup | Rp300 |
| Cup, tutup, sedotan | 1 set | Rp1.500 |
| **HPP bahan + kemasan** | | **Rp9.100** |

Untuk minuman, kemasan sering menjadi komponen terbesar kedua setelah bahan utama. Ganti cup ke ukuran lebih besar dan HPP langsung naik, walaupun resepnya sama.

## Contoh 3: Kue dan Roti per Loyang

Untuk kue dan roti, hitung HPP per adonan atau per loyang, lalu bagi dengan jumlah potong yang **layak jual**. Contoh lengkap brownies per loyang ada di [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm). Bila satu dapur membuat beberapa jenis kue dengan bahan yang sama, baca [cara mencatat produksi banyak produk dalam satu dapur](/blog/cara-mencatat-produksi-banyak-produk-satu-dapur-rumahan).

## Langkah 2: Bagi Gas, Listrik, dan Tenaga Kerja

Gas dan listrik sulit dihitung per porsi secara langsung. Cara praktis: jumlahkan biaya gas dan listrik untuk produksi selama sebulan, lalu bagi dengan jumlah porsi yang terjual bulan itu. Bila gas dan listrik Rp600.000 sebulan dan terjual 1.500 porsi, overhead per porsi Rp400. Angka ini cukup ditinjau ulang setiap beberapa bulan.

## Langkah 3: Hitung Susut dan Sisa Tidak Terjual

Bila dari 80 porsi yang disiapkan rata-rata 5 tidak terjual dan dibuang, biaya 80 porsi harus ditanggung oleh 75 porsi yang terjual. HPP Rp12.000 menjadi Rp12.000 × 80 ÷ 75 = **Rp12.800**. Usaha makanan dengan sisa harian tinggi sering salah menyimpulkan menunya untung, padahal susutnya memakan margin.

## Template HPP Makanan di Excel

Buat satu sheet daftar bahan dengan kolom *Bahan, Harga beli, Isi, Satuan, Harga per satuan* (rumus `=Harga beli/Isi`). Lalu satu sheet per menu dengan kolom *Bahan, Takaran, Harga per satuan, Biaya*. Ambil harga per satuan dari sheet bahan dengan `VLOOKUP` atau `XLOOKUP`, sehingga saat harga ayam naik Anda cukup mengubah satu sel dan HPP semua menu ikut berubah.

## Pertanyaan yang Sering Muncul

**Berapa persen HPP makanan yang ideal dari harga jual?**
Patokan yang sering dipakai usaha kuliner adalah HPP sekitar 30–40% dari harga jual, tapi ini bukan aturan. Usaha dengan sewa tempat mahal butuh persentase lebih kecil, sementara usaha rumahan tanpa sewa bisa lebih longgar. Cara mengubah HPP menjadi harga jual dibahas di [cara menentukan harga jual makanan per pcs](/blog/cara-menentukan-harga-jual-makanan-per-pcs).

**Perlukah menghitung HPP setiap menu?**
Ya, terutama menu terlaris. Menu dengan omzet besar tapi HPP tinggi bisa lebih sedikit menyumbang untung daripada menu kecil dengan HPP rendah.

**Seberapa sering HPP makanan perlu dihitung ulang?**
Setiap kali harga bahan utama berubah berarti, misalnya ayam, telur, atau cabai. Untuk bahan yang harganya sering naik-turun, tinjau bulanan.

## Mencatat Bahan dan Produksi Tanpa Hitung Ulang Manual

Menghitung HPP sekali itu mudah. Yang sulit adalah menjaganya tetap benar saat harga bahan berubah setiap minggu dan menu bertambah. Fabriku mencatat penerimaan bahan beserta harganya, mengurangi stok bahan dari penerimaan paling lama lebih dulu, dan menyimpan harga pokok di setiap item stok produk jadi. Untuk usaha rumahan, satu form produksi sederhana mencatat bahan yang dipakai dan jumlah hasil jadi, lalu biaya per unit dihitung dari bahan tersebut. Lihat [Fabriku untuk usaha makanan dan kue](/aplikasi/usaha-makanan-kue) dan panduan [mencatat produksi harian usaha kuliner rumahan](/blog/cara-mencatat-produksi-harian-umkm-kuliner-rumahan).
MD,
            ],
            [
                'slug' => 'cara-menentukan-harga-jual-makanan-per-pcs',
                'title' => 'Cara Menentukan Harga Jual Makanan per Pcs, Termasuk di GoFood dan ShopeeFood',
                'category' => 'Keuangan',
                'tags' => ['Harga Jual', 'Kuliner Rumahan', 'UMKM'],
                'days_ago' => 0,
                'excerpt' => 'Harga jual makanan ditentukan dari HPP per pcs, target persentase HPP atau markup, lalu disesuaikan untuk titip warung, reseller, dan komisi aplikasi ojek online. Ini rumus dan contoh hitungannya.',
                'meta_title' => 'Cara Menentukan Harga Jual Makanan per Pcs (+ GoFood)',
                'meta_description' => 'Cara menentukan harga jual makanan per pcs dari HPP: rumus markup, harga titip warung dan reseller, serta harga di GoFood dan ShopeeFood setelah komisi.',
                'content' => <<<'MD'
Setelah HPP per porsi atau per pcs diketahui, langkah berikutnya adalah menentukan harga jual. Banyak usaha makanan berhenti di "HPP Rp3.000, jual Rp5.000 saja" tanpa memperhitungkan potongan warung, reseller, atau aplikasi ojek online. Hasilnya, harga yang terlihat untung di dapur ternyata tipis atau rugi di channel tertentu.

Artikel ini melanjutkan [cara menghitung HPP makanan per porsi](/blog/cara-menghitung-hpp-makanan-per-porsi). Pastikan HPP sudah dihitung lengkap, termasuk kemasan, gas, tenaga kerja, dan susut.

## Dua Rumus Dasar Harga Jual Makanan

**1. Markup dari HPP**

Harga jual = HPP × (1 + markup)

HPP Rp12.000 dengan markup 100% berarti harga jual Rp24.000.

**2. Target persentase HPP (food cost)**

Harga jual = HPP ÷ target persentase HPP

HPP Rp12.000 dengan target HPP 40% dari harga jual berarti harga jual Rp12.000 ÷ 0,4 = Rp30.000. Dengan target 50%, harga jual Rp24.000.

Keduanya menghasilkan angka yang sama bila dikonversi dengan benar (markup 100% = HPP 50% dari harga jual). Usaha kuliner lebih sering memakai cara kedua karena langsung terlihat berapa bagian harga jual yang habis untuk biaya bahan. Penjelasan lebih lengkap tentang perbedaan markup dan margin ada di [cara menghitung HPP dan harga jual](/blog/cara-menghitung-hpp-produk-umkm).

## Contoh: Harga Jual Kue per Pcs

Risoles dibuat 40 pcs per adonan. Bahan, minyak, dan mika Rp88.000, sehingga bahan per pcs Rp2.200. Tenaga kerja Rp30.000 per adonan menambah Rp750 per pcs. **HPP risoles ≈ Rp2.950**, dibulatkan Rp3.000.

| Target HPP dari harga jual | Harga jual per pcs | Untung kotor per pcs |
|---|---|---|
| 60% | Rp5.000 | Rp2.000 |
| 50% | Rp6.000 | Rp3.000 |
| 40% | Rp7.500 | Rp4.500 |

Angka ini kemudian dicek dengan harga pasar. Bila produk serupa di sekitar Anda dijual Rp5.000, harga Rp7.500 perlu alasan yang jelas, misalnya isi lebih banyak atau ukuran lebih besar. Harga pasar dipakai untuk mengecek, bukan sebagai dasar menghitung.

## Harga Titip Warung dan Reseller

Saat produk dititipkan ke warung atau dijual lewat reseller, harga yang Anda terima lebih kecil dari harga yang dibayar pembeli. Hitung dari harga yang **Anda terima**, bukan harga di etalase.

Contoh risoles di atas dijual Rp5.000 di warung, dan warung mengambil Rp1.000 per pcs. Anda menerima Rp4.000, untung Rp1.000 per pcs, bukan Rp2.000. Bila juga ada produk yang tidak laku dan dikembalikan, hitung sebagai susut. Tetapkan harga reseller dan titip warung sebagai daftar terpisah, dan pastikan setiap harga tetap di atas HPP.

## Harga di GoFood dan ShopeeFood

Aplikasi pesan-antar memotong komisi dari harga menu. Besarnya berbeda per platform dan program, dan bisa berubah, jadi pakai angka dari dashboard mitra Anda. Contoh di bawah memakai komisi 20%.

Rumus agar uang yang diterima sama dengan penjualan langsung:

**Harga di aplikasi = harga offline ÷ (1 − komisi)**

Nasi ayam geprek dengan HPP Rp12.000 dijual Rp25.000 di tempat:

| | Offline | Online, harga sama | Online, harga disesuaikan |
|---|---|---|---|
| Harga menu | Rp25.000 | Rp25.000 | Rp31.500 |
| Komisi 20% | Rp0 | Rp5.000 | Rp6.300 |
| Diterima | Rp25.000 | Rp20.000 | Rp25.200 |
| HPP | Rp12.000 | Rp12.000 | Rp12.000 |
| Untung kotor | Rp13.000 | Rp8.000 | Rp13.200 |

Tanpa penyesuaian, setiap porsi online kehilangan Rp5.000 dibanding penjualan langsung. Perhatikan juga:

- **Kemasan antar** biasanya lebih mahal (box tertutup, segel, kantong). Masukkan selisihnya ke HPP versi online.
- **Promo yang ditanggung merchant** mengurangi uang yang diterima. Hitung harga setelah promo, bukan sebelum.
- **Pajak atau biaya tambahan** dari platform, bila ada, ikut dikurangkan dari uang yang diterima.

Sebelum menetapkan harga online yang berbeda, baca ketentuan mitra platform yang Anda pakai.

## Pembulatan dan Harga Psikologis

Setelah dihitung, bulatkan ke atas ke angka yang mudah: Rp31.250 menjadi Rp31.500 atau Rp32.000. Jangan membulatkan ke bawah hanya agar terlihat murah; selisih Rp500 per porsi pada 1.000 porsi sebulan adalah Rp500.000.

## Kesalahan Umum Menentukan Harga Jual Makanan

- **Satu harga untuk semua channel.** Warung, reseller, dan aplikasi punya potongan berbeda.
- **HPP tidak lengkap.** Kemasan, minyak, dan gas tidak dihitung, sehingga harga terlihat untung padahal tidak.
- **Tidak menaikkan harga saat bahan naik.** Harga cabai, ayam, dan telur berubah; HPP dan harga perlu ditinjau ulang.
- **Mengejar harga termurah.** Usaha kecil jarang menang perang harga melawan usaha yang membeli bahan jauh lebih banyak.

## Pertanyaan yang Sering Muncul

**Berapa markup yang wajar untuk makanan?**
Tidak ada angka baku. Banyak usaha kuliner menargetkan HPP 30–40% dari harga jual (markup sekitar 150–230%), tapi usaha rumahan tanpa sewa tempat sering memakai target lebih longgar. Yang penting, setelah semua potongan channel, masih ada untung.

**Lebih baik jual per pcs atau per box?**
Sediakan keduanya bila memungkinkan. Harga per box biasanya sedikit lebih murah per pcs, tapi mengurangi biaya kemasan satuan dan waktu melayani.

**Bagaimana menghitung harga untuk pesanan custom atau hampers?**
Hitung HPP setiap isi ditambah kemasan khusus dan waktu merangkai, lalu terapkan target yang sama. Pesanan custom layak diberi target untung lebih tinggi karena butuh waktu lebih banyak.

## Melihat Untung per Channel

Harga yang tepat baru terbukti setelah penjualan berjalan. Catat setiap penjualan dengan channel-nya, karena untung dari 100 porsi di tempat berbeda dengan 100 porsi lewat aplikasi. Fabriku mencatat setiap pesanan beserta channel-nya (offline, online, marketplace, atau reseller), sehingga omzet per channel bisa dibandingkan di laporan penjualan. Baca juga [cara mengelola pesanan online dan offline sekaligus](/blog/cara-mengelola-pesanan-online-offline-sekaligus) dan lihat [Fabriku untuk usaha makanan dan kue](/aplikasi/usaha-makanan-kue).
MD,
            ],
            [
                'slug' => 'contoh-laporan-penjualan-harian-toko-sembako',
                'title' => 'Contoh Laporan Penjualan Harian Toko Sembako (Rekap per Barang dan Cek Uang Laci)',
                'category' => 'Penjualan',
                'tags' => ['Laporan Penjualan', 'Retail', 'Excel'],
                'days_ago' => 0,
                'excerpt' => 'Toko sembako punya ratusan transaksi kecil sehari, jadi laporan penjualan hariannya disusun dari rekap stok per barang lalu dicocokkan dengan uang laci, transfer, dan kasbon. Ini contoh lengkapnya.',
                'meta_title' => 'Contoh Laporan Penjualan Harian Toko Sembako (Excel)',
                'meta_description' => 'Contoh laporan penjualan harian toko sembako: rekap terjual per barang dari stok, koreksi harga grosir dan eceran, cocokkan dengan uang laci, QRIS, dan kasbon.',
                'content' => <<<'MD'
Toko sembako bisa melayani ratusan pembeli sehari dengan belanjaan kecil-kecil: satu bungkus mi, setengah kilo gula, satu tabung gas. Mencatat setiap transaksi dengan tangan hampir mustahil saat toko ramai. Akibatnya banyak pemilik toko hanya menghitung uang di laci, tanpa tahu barang apa yang laku dan apakah uangnya cocok dengan barang yang keluar.

Artikel ini memberi contoh laporan penjualan harian yang realistis untuk toko sembako tanpa mesin kasir, dan cara mencocokkannya dengan uang yang masuk.

## Dua Cara Mencatat Penjualan Harian Toko Sembako

1. **Per transaksi**, setiap pembeli dicatat. Paling akurat, tapi hanya praktis bila memakai aplikasi kasir.
2. **Rekap per barang dari stok**, dihitung sekali di akhir hari: *terjual = stok awal + barang masuk − stok akhir*. Cocok untuk toko tanpa kasir, asalkan dipakai untuk barang yang laku setiap hari.

Kebanyakan toko sembako kecil memakai cara kedua untuk 20–30 barang terlaris, lalu mengecek barang lain saat stok opname mingguan atau bulanan.

## Contoh Laporan Penjualan Harian Toko Sembako

Rekap tanggal 14 Oktober untuk enam barang terlaris:

| Barang | Satuan | Stok awal | Masuk | Stok akhir | Terjual | Harga ecer | Omzet |
|---|---|---|---|---|---|---|---|
| Beras 5 kg | karung | 20 | 10 | 22 | 8 | Rp75.000 | Rp600.000 |
| Minyak goreng 2 L | pouch | 30 | 0 | 18 | 12 | Rp36.000 | Rp432.000 |
| Gula pasir 1 kg | pak | 25 | 0 | 15 | 10 | Rp18.000 | Rp180.000 |
| Telur | kg | 15 | 10 | 13 | 12 | Rp29.000 | Rp348.000 |
| Mi instan | bungkus | 120 | 40 | 85 | 75 | Rp3.500 | Rp262.500 |
| Gas 3 kg | tabung | 10 | 10 | 8 | 12 | Rp22.000 | Rp264.000 |
| **Total** | | | | | | | **Rp2.086.500** |

Kolom *masuk* diisi dari barang yang datang hari itu (sesuai nota supplier). *Stok akhir* dihitung fisik saat toko tutup. Untuk telur dan barang curah lain, pakai satuan timbang yang sama setiap hari.

## Koreksi Harga Grosir dan Eceran

Banyak toko sembako menjual barang yang sama dengan dua harga. Mi instan misalnya dijual Rp3.500 per bungkus atau Rp115.000 per dus isi 40.

Dari 75 bungkus mi yang terjual, ternyata 40 bungkus keluar sebagai 1 dus. Omzet sebenarnya:

- 1 dus × Rp115.000 = Rp115.000
- 35 bungkus × Rp3.500 = Rp122.500
- **Total Rp237.500**, bukan Rp262.500

Selisih Rp25.000 ini sering membuat pemilik toko mengira uangnya hilang, padahal penyebabnya harga grosir. Solusinya: catat penjualan grosir sebagai baris terpisah (cukup tulis di buku kecil setiap ada pembeli per dus atau per karung), sementara stok tetap dihitung dalam satuan terkecil.

Omzet hari itu setelah koreksi: Rp2.086.500 − Rp25.000 = **Rp2.061.500**.

## Cocokkan dengan Uang yang Masuk

Langkah terpenting dari laporan penjualan harian adalah mencocokkan omzet hitungan dengan uang:

| Sumber | Jumlah |
|---|---|
| Uang laci (setelah dikurangi modal kembalian awal) | Rp1.650.000 |
| QRIS dan transfer | Rp320.000 |
| Kasbon pelanggan hari ini | Rp70.000 |
| **Total uang + kasbon** | **Rp2.040.000** |
| Omzet hitungan | Rp2.061.500 |
| **Selisih** | **−Rp21.500** |

Selisih kecil seperti ini biasanya berasal dari salah hitung stok akhir, barang yang diambil untuk keperluan rumah, kembalian yang salah, atau harga yang diberi diskon tanpa dicatat. Catat selisih setiap hari. Bila selalu minus di barang yang sama, periksa barang itu lebih dulu. Bila selisih membesar tiba-tiba, cek siapa yang menjaga toko hari itu.

**Kasbon** (pelanggan mengambil barang dan membayar nanti) wajib dicatat dengan nama dan jumlahnya. Kasbon yang tidak dicatat terlihat seperti uang hilang di laporan hari ini, dan sering tidak tertagih. Baca [cara mencatat dan menagih piutang pelanggan](/blog/cara-mencatat-menagih-piutang-pelanggan-umkm).

## Template Laporan Penjualan Harian Toko Sembako di Excel

Satu sheet per bulan, satu baris per barang per hari:

- Kolom: A = Tanggal, B = Barang, C = Stok awal, D = Masuk, E = Stok akhir, F = Terjual, G = Harga, H = Omzet.
- Rumus terjual: `=C2+D2-E2`
- Rumus omzet: `=F2*G2`
- Stok awal hari berikutnya = stok akhir hari ini.

Di bagian bawah, tambahkan tabel kecil cocok uang seperti di atas. Di akhir bulan, PivotTable dengan *Barang* di baris dan *Sum of Terjual* di nilai langsung menunjukkan barang mana yang paling laku dan perlu stok lebih banyak.

Untuk format laporan yang lebih umum (per transaksi, mingguan, bulanan), lihat [cara membuat laporan penjualan UMKM sederhana](/blog/cara-membuat-laporan-penjualan-umkm-sederhana). Catatan stok per barang yang lebih rapi bisa disusun dengan [kartu stok barang](/blog/kartu-stok-barang-contoh-cara-mengisi-template-excel).

## Kesalahan yang Sering Terjadi

- **Barang masuk tidak dicatat**, sehingga terjual terlihat lebih sedikit dan omzet seolah kurang.
- **Barang diambil untuk rumah** tanpa dicatat. Tulis sebagai pemakaian pribadi, jangan dicampur dengan penjualan.
- **Stok akhir dihitung sambil melayani pembeli**, sehingga angkanya meleset. Hitung saat toko sudah tutup atau saat sepi.
- **Hanya menghitung uang laci.** Tanpa rekap barang, Anda tidak tahu apakah uang itu sesuai dengan barang yang keluar.

## Pertanyaan yang Sering Muncul

**Apakah semua barang perlu direkap setiap hari?**
Tidak. Rekap harian cukup untuk barang yang laku setiap hari dan nilainya besar. Barang lain dicek lewat [stok opname bergulir](/blog/cara-stok-opname-toko-retail-tanpa-tutup-toko).

**Bagaimana jika ada barang yang dijual per gram, seperti gula curah?**
Timbang sisa stok di akhir hari dan pakai satuan kilogram dengan satu desimal. Selisih timbangan kecil wajar; yang perlu diperhatikan adalah selisih yang terus membesar.

**Kapan sebaiknya mulai pakai aplikasi kasir?**
Saat pembeli antre dan mencari harga memperlambat pelayanan, atau saat selisih harian sulit dijelaskan. Lihat [tips memilih aplikasi kasir untuk UMKM pemula](/blog/tips-memilih-aplikasi-kasir-umkm-pemula).

## Laporan Harian Tanpa Hitung Stok Manual

Di Fabriku, penjualan langsung di toko dicatat lewat halaman kasir cepat, dan stok barang berkurang saat transaksi selesai. Barang masuk dari supplier dicatat lewat penerimaan pembelian beserta nomor faktur, dan barang rusak, hilang, atau retur dicatat sebagai penyesuaian stok. Rekap penjualan harian dan per produk bisa dilihat dan diekspor ke Excel atau PDF, tanpa menghitung terjual dari selisih stok setiap malam. Lihat [Fabriku untuk toko retail](/aplikasi/toko-retail).
MD,
            ],
            [
                'slug' => 'cara-menentukan-harga-jual-produk-markup-vs-margin',
                'title' => 'Cara Menentukan Harga Jual Produk: Markup vs Margin, Lengkap dengan Contoh',
                'category' => 'Keuangan',
                'tags' => ['Harga Jual', 'HPP', 'UMKM'],
                'days_ago' => 0,
                'excerpt' => 'Markup dan margin sama-sama persen keuntungan, tapi dihitung dari angka yang berbeda. Salah pakai rumus membuat harga jual lebih murah dari target. Ini cara menentukan harga jual produk langkah demi langkah.',
                'meta_title' => 'Cara Menentukan Harga Jual Produk: Markup vs Margin',
                'meta_description' => 'Cara menentukan harga jual produk UMKM: hitung HPP, pilih markup atau margin, cek harga pasar dan potongan channel. Rumus, tabel konversi, dan contoh angka.',
                'content' => <<<'MD'
Banyak pemilik usaha menentukan harga jual dengan satu kalimat: "modal ditambah 30%". Masalahnya, 30% itu sering dibaca sebagai margin di laporan, padahal dihitung sebagai markup saat membuat harga. Selisihnya kecil per produk, tapi terasa besar setelah ratusan penjualan.

Artikel ini membahas cara menentukan harga jual suatu produk dalam lima langkah, dengan fokus pada perbedaan markup dan margin.

## Langkah 1: Hitung HPP per Unit

Harga jual selalu dimulai dari HPP (harga pokok produksi): bahan, tenaga kerja, kemasan, dan bagian dari biaya overhead untuk satu unit produk. Tanpa HPP, persen keuntungan apa pun hanya tebakan.

Cara menghitung HPP per unit, lengkap dengan tabel dan template Excel, ada di [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm). Untuk makanan per porsi, lihat [cara menghitung HPP makanan per porsi](/blog/cara-menghitung-hpp-makanan-per-porsi).

Contoh di artikel ini memakai HPP **Rp20.000** per unit.

## Langkah 2: Pahami Beda Markup dan Margin

| | Markup | Margin |
|---|---|---|
| Dihitung dari | HPP | Harga jual |
| Rumus persen | (Harga jual − HPP) ÷ HPP | (Harga jual − HPP) ÷ Harga jual |
| Rumus harga jual | HPP × (1 + markup) | HPP ÷ (1 − margin) |
| Contoh 30% dari HPP Rp20.000 | Rp26.000 | Rp28.571 |

Dengan markup 30%, untung per unit Rp6.000. Tapi kalau dilihat dari harga jual, Rp6.000 ÷ Rp26.000 hanya **23%**. Jadi kalau target Anda "untung 30% dari omzet", rumus markup membuat harga terlalu murah sekitar Rp2.600 per unit.

Aturan praktis: **pakai margin bila target keuntungan dibandingkan dengan omzet** (misalnya laporan laba rugi), **pakai markup bila Anda berpikir dari modal**. Keduanya benar, asalkan tidak dicampur.

## Tabel Konversi Markup ke Margin

| Markup | Margin setara |
|---|---|
| 25% | 20% |
| 50% | 33,3% |
| 67% | 40% |
| 100% | 50% |
| 150% | 60% |

Rumusnya: margin = markup ÷ (1 + markup). Sebaliknya, markup = margin ÷ (1 − margin).

## Langkah 3: Tambahkan Potongan Channel

Harga jual yang sama bisa menghasilkan untung berbeda di setiap channel:

- Marketplace dan aplikasi pesan antar memotong komisi dari harga jual.
- Reseller mendapat harga khusus.
- Penjualan langsung tidak terpotong, tapi mungkin ada biaya kemasan atau ongkir yang Anda tanggung.

Karena komisi dipotong dari harga jual, hitung dengan cara margin: **Harga jual = HPP ÷ (1 − margin − komisi)**.

Contoh: HPP Rp20.000, target margin 30%, komisi 20%. Harga jual = Rp20.000 ÷ (1 − 0,3 − 0,2) = **Rp40.000**. Angka ini terasa tinggi, dan itulah alasan banyak penjual menetapkan harga berbeda di aplikasi pesan antar. Contoh lengkapnya untuk makanan ada di [cara menentukan harga jual makanan per pcs](/blog/cara-menentukan-harga-jual-makanan-per-pcs).

## Langkah 4: Bandingkan dengan Harga Pasar

Hasil hitungan adalah harga minimum yang masih sehat, bukan harga final. Bandingkan dengan 3–5 produk sejenis:

- **Harga Anda jauh di atas pasar:** cari komponen HPP yang bisa ditekan (beli bahan lebih banyak, kemasan lebih sederhana), atau tonjolkan nilai yang membedakan produk Anda.
- **Harga Anda jauh di bawah pasar:** jangan buru-buru senang. Periksa apakah ada biaya yang lupa dihitung, terutama upah sendiri dan overhead.
- **Mirip pasar:** pakai hasil hitungan, lalu bulatkan ke angka yang mudah dibayar (Rp27.500 jadi Rp28.000, bukan Rp27.000).

Produk handmade dan custom punya pertimbangan tambahan, misalnya tingkat kesulitan dan waktu pengerjaan. Lihat [cara menentukan harga jual produk kerajinan handmade](/blog/cara-menentukan-harga-jual-produk-kerajinan-handmade).

## Langkah 5: Tinjau Ulang Secara Berkala

Harga jual yang tepat hari ini bisa merugi tiga bulan lagi bila harga bahan naik. Tinjau ulang HPP dan harga jual setiap kali harga bahan utama berubah, atau minimal sebulan sekali untuk bahan yang harganya sering naik turun.

## Contoh Lengkap

Sebuah usaha sambal kemasan punya HPP Rp15.000 per botol dan menjual di tiga tempat dengan target margin 35%:

| Channel | Potongan | Rumus | Harga minimum | Harga dipasang |
|---|---|---|---|---|
| Langsung (WhatsApp, bazar) | 0% | 15.000 ÷ 0,65 | Rp23.077 | Rp24.000 |
| Reseller (diskon 15%) | 15% | 15.000 ÷ 0,50 | Rp30.000 harga katalog | Rp30.000, reseller bayar Rp25.500 |
| Marketplace (komisi 10%) | 10% | 15.000 ÷ 0,55 | Rp27.273 | Rp28.000 |

Perhatikan bahwa harga katalog reseller Rp30.000 lebih tinggi dari harga langsung. Bila harga langsung dibuat sama dengan harga katalog reseller, pembeli langsung membayar lebih mahal dari yang perlu; bila diskon reseller dihitung dari harga langsung Rp24.000, reseller hanya membayar Rp20.400 dan untung Anda turun dari Rp10.500 menjadi Rp5.400 per botol. Putuskan dulu struktur harga mana yang dipakai, lalu hitung mundur.

## Pertanyaan yang Sering Muncul

**Berapa margin yang wajar untuk UMKM?**
Tidak ada angka baku karena tergantung jenis produk dan biaya tetap. Yang penting, margin kotor harus cukup menutup biaya tetap bulanan (sewa, listrik, gaji) pada volume penjualan normal, lalu masih menyisakan laba.

**Apakah ongkos kirim masuk HPP?**
Ongkir yang Anda tanggung (gratis ongkir) masuk sebagai biaya per pesanan. Ongkir yang dibayar pembeli tidak perlu dimasukkan.

**Lebih baik untung tipis tapi laku banyak, atau sebaliknya?**
Untung tipis hanya aman bila volume benar-benar tinggi dan stabil. Untuk usaha kecil dengan kapasitas produksi terbatas, margin yang cukup biasanya lebih aman daripada mengejar volume.

## Menjaga Harga Tetap Sehat

Fabriku mencatat biaya bahan dari setiap penerimaan dan harga jual per produk, serta channel setiap pesanan (offline, online, marketplace, atau reseller). Dengan begitu omzet per channel bisa dibandingkan di laporan penjualan, dan harga bisa disesuaikan saat biaya bahan berubah. Lihat cara kerjanya untuk [usaha makanan dan kue](/aplikasi/usaha-makanan-kue) atau [kerajinan dan handmade](/aplikasi/kerajinan-handmade).
MD,
            ],
            [
                'slug' => 'contoh-website-toko-online-umkm',
                'title' => 'Contoh Website Toko Online UMKM: Bedah Website Usaha Camilan Rumahan',
                'category' => 'Penjualan',
                'tags' => ['Website Usaha', 'Produksi Rumahan', 'Kuliner Rumahan'],
                'days_ago' => 0,
                'excerpt' => 'Contoh website toko online UMKM yang sudah berjalan: DSFA Homemade, usaha camilan rumahan di Bandung. Bagian demi bagian, dari judul halaman sampai cara pesan, dan apa yang bisa Anda tiru.',
                'meta_title' => 'Contoh Website Toko Online UMKM yang Sudah Berjalan',
                'meta_description' => 'Contoh website toko online UMKM nyata: DSFA Homemade Bandung. Bedah beranda, katalog, cara pesan, area kirim, dan halaman SEO, plus checklist untuk website Anda.',
                'content' => <<<'MD'
Saat mencari contoh website toko online, kebanyakan yang muncul adalah toko besar dengan tim desain sendiri. Contohnya bagus, tapi sulit ditiru oleh usaha rumahan yang dikelola satu atau dua orang.

Artikel ini membedah satu website usaha kecil yang sudah berjalan: [dsfahomemade.com](https://dsfahomemade.com), milik DSFA Homemade, usaha camilan dan dessert rumahan di Bandung yang menjual cheesecuit, takoyaki, puding buah, dan jus. Websitenya dibuat dengan fitur Website Usaha di Fabriku. Isi dan harga di bawah dicatat pada akhir Oktober 2026 dan bisa berubah.

## 1. Judul Halaman yang Menjawab "Apa dan di Mana"

Judul halaman (yang tampil di tab browser dan hasil pencarian Google) berbunyi *DSFA Homemade Bandung | Cheesecuit, Takoyaki, Puding & Jus*. Dalam satu baris, calon pembeli tahu nama usaha, kotanya, dan menunya.

**Yang bisa ditiru:** sebutkan nama usaha, kota, dan 2–4 produk utama. Hindari judul seperti "Beranda" atau "Selamat Datang", yang tidak membantu pembeli maupun Google.

## 2. Bagian Atas: Satu Kalimat Penjelasan dan Satu Tombol

Bagian paling atas menjelaskan siapa mereka (dapur rumah di Bandung), cara memesan (WhatsApp), dan janji pengantaran (maksimal 1 jam dalam kondisi lalu lintas normal). Di bawahnya ada dua tombol: pesan via WhatsApp dan lihat menu. Foto produk tampil bergantian di samping teks.

**Yang bisa ditiru:** jawab tiga pertanyaan pertama pembeli sebelum mereka menggulir: jual apa, di mana, dan bagaimana cara pesannya.

## 3. Katalog dengan Harga dan Status yang Jelas

Katalog menampilkan setiap varian sebagai produk tersendiri dengan foto dan harga: takoyaki isi 4 seharga Rp5.000, isi 8 seharga Rp10.000, cheesecuit kecil dalam tiga rasa, jus dalam tiga rasa, dan seterusnya. Setiap kartu punya label status: *Tersedia* untuk barang yang siap, atau *Open Order · estimasi 3 hari* untuk puding ulang tahun yang dibuat sesuai pesanan.

**Yang bisa ditiru:**
- Cantumkan harga. Pembeli yang harus bertanya harga lewat chat sering tidak jadi bertanya.
- Pisahkan varian ukuran dan rasa, jangan satu produk dengan keterangan "harga mulai dari".
- Bedakan barang siap kirim dan barang pesanan khusus, sekaligus waktu pengerjaannya.

## 4. Cara Pesan dalam Tiga Langkah

DSFA menulis alur pemesanan sebagai tiga langkah singkat: pilih menu, tentukan jumlah dan tanggal, lalu kirim pesanan. Pembeli juga bisa memasukkan produk ke keranjang dan mengirim pesanan langsung dari website.

**Yang bisa ditiru:** tulis alur pemesanan maksimal tiga sampai empat langkah. Bila pembayaran dikonfirmasi manual (transfer atau QRIS setelah dihubungi admin), katakan dengan jelas supaya pembeli tidak menunggu halaman pembayaran yang tidak ada.

## 5. Area Pengiriman dan Kontak yang Mudah Ditemukan

Ada halaman khusus area pengiriman (Kota Bandung, Cimahi, dan sebagian Kabupaten Bandung) dan dua nomor WhatsApp admin di bagian bawah setiap halaman.

**Yang bisa ditiru:** untuk makanan dan produk yang diantar sendiri, area kirim adalah pertanyaan kedua setelah harga. Jawab di website, bukan satu per satu di chat.

## 6. Halaman Khusus untuk Setiap Pencarian Lokal

Selain beranda, DSFA punya halaman terpisah untuk setiap menu dan lokasi, misalnya *cheesecuit Bandung*, *jual takoyaki Bandung*, *puding buah Bandung*, *puding ulang tahun Bandung*, dan *dessert takoyaki Bojongsoang*. Setiap halaman membahas satu menu, menampilkan kartu produk yang terkait, dan menautkan kembali ke beranda.

**Yang bisa ditiru:** orang mencari "puding ulang tahun Bandung", bukan "DSFA Homemade". Satu halaman per menu utama per kota memberi peluang muncul di pencarian lokal. Isi setiap halaman dengan informasi yang berbeda (varian, harga, waktu pengerjaan, cara pesan), bukan teks yang sama dengan nama kota diganti.

## Checklist Website Toko Online UMKM

Sebelum website Anda diterbitkan, pastikan:

- [ ] Judul halaman memuat nama usaha, kota, dan produk utama
- [ ] Bagian atas menjawab: jual apa, di mana, cara pesan
- [ ] Setiap produk punya foto asli, harga, dan status ketersediaan
- [ ] Alur pemesanan dan cara bayar ditulis jelas
- [ ] Area pengiriman dan kontak mudah ditemukan
- [ ] Ada halaman kebijakan privasi
- [ ] Halaman terbuka dengan cepat dan rapi di ponsel

## Pertanyaan yang Sering Muncul

**Apakah usaha kecil perlu website kalau sudah punya Instagram dan WhatsApp?**
Instagram bagus untuk dilihat, tapi sulit dicari lewat Google dan katalognya cepat tenggelam. Website memberi satu alamat tetap yang memuat menu, harga, dan cara pesan, lalu bisa ditautkan dari bio Instagram dan status WhatsApp.

**Apakah harus memakai domain sendiri?**
Tidak wajib di awal, tapi domain sendiri (seperti dsfahomemade.com) lebih mudah diingat dan terlihat lebih meyakinkan saat dibagikan.

**Bagaimana kalau stok sering habis?**
Website yang terhubung dengan catatan stok akan menyembunyikan atau menandai barang yang habis. Website yang diperbarui manual sering masih menampilkan barang yang sudah tidak ada, dan itu membuat pembeli kecewa.

## Website yang Terhubung dengan Stok dan Pesanan

Website DSFA dibuat dengan fitur Website Usaha di Fabriku. Katalognya diambil dari data barang di Fabriku, sehingga foto, harga, dan status ketersediaan mengikuti stok. Barang yang dibuat sesuai pesanan bisa ditandai open order beserta estimasi pengerjaannya. Pesanan dari keranjang website masuk sebagai pesanan baru di Fabriku, pemilik mendapat notifikasi, lalu admin menghubungi pembeli untuk konfirmasi dan pembayaran. Halaman tambahan seperti area pengiriman dan halaman menu per kota bisa dibuat dari dashboard yang sama.

Untuk menyiapkan stok dan catatan produksinya, baca [cara mencatat produksi banyak produk dalam satu dapur rumahan](/blog/cara-mencatat-produksi-banyak-produk-satu-dapur-rumahan) dan lihat [Fabriku untuk produksi rumahan](/aplikasi/produksi-rumahan). Bila Anda juga menjual lewat marketplace atau toko langsung, baca [cara mengelola pesanan online dan offline sekaligus](/blog/cara-mengelola-pesanan-online-offline-sekaligus).
MD,
            ],
        ];
    }
}
