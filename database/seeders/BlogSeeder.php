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
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $author = AdminUser::where('email', 'admin@fabriku.web.id')->firstOrFail();

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
            $post = BlogPost::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'admin_user_id' => $author->id,
                    'blog_category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'status' => 'published',
                    'published_at' => now()->subDays($data['days_ago']),
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

Fabriku punya modul stok bahan baku dengan pencatatan FIFO/FEFO otomatis dan notifikasi saat stok mendekati batas minimum, jadi kamu tidak perlu lagi menghitung manual atau was-was lupa cek gudang.
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
MD,
            ],
            [
                'slug' => 'cara-membuat-laporan-penjualan-umkm-sederhana',
                'title' => 'Cara Membuat Laporan Penjualan UMKM Sederhana (Lengkap dengan Contoh Kolom)',
                'category' => 'Penjualan',
                'tags' => ['Laporan Penjualan', 'UMKM', 'Excel'],
                'days_ago' => 3,
                'excerpt' => 'Laporan penjualan tidak harus rumit untuk mulai berguna. Ini kolom-kolom wajib dan langkah membuat laporan penjualan sederhana yang bisa langsung dipakai UMKM.',
                'meta_title' => 'Cara Membuat Laporan Penjualan UMKM Sederhana',
                'meta_description' => 'Cara membuat laporan penjualan UMKM sederhana lengkap dengan kolom wajib dan langkah-langkahnya, cocok untuk usaha kecil yang baru mulai mencatat.',
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

## Berapa Sering Laporan Ini Perlu Dibuat

Untuk usaha yang baru mulai, rekap mingguan biasanya cukup untuk mulai melihat pola tanpa terasa membebani. Begitu transaksi harian mulai lebih dari 10-15 kali, rekap harian jadi lebih berguna karena pola penjualan bisa berubah cukup cepat, dan menunggu seminggu untuk melihat data berarti terlambat mengambil keputusan seperti menambah stok atau menyesuaikan harga.

Yang lebih penting dari frekuensi adalah konsistensi. Laporan yang dibuat rutin walau formatnya sederhana jauh lebih berguna dibanding laporan detail yang hanya dibuat sesekali saat sempat. Kebiasaan mencatat setiap transaksi di hari yang sama juga mengurangi risiko lupa atau salah ingat detail transaksi, yang sering terjadi kalau pencatatan ditunda sampai akhir hari atau akhir minggu.

Fabriku mencatat setiap pesanan langsung sebagai data penjualan, sehingga laporan seperti ini tidak perlu disusun manual — tinggal dilihat dan dianalisis.
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
MD,
            ],
            [
                'slug' => 'cara-menghitung-hpp-produk-umkm',
                'title' => 'Cara Menghitung HPP Produk UMKM agar Tidak Salah Tentukan Harga Jual',
                'category' => 'Keuangan',
                'tags' => ['HPP', 'Keuangan UMKM', 'UMKM'],
                'days_ago' => 14,
                'excerpt' => 'Harga jual yang asal tebak sering jadi penyebab UMKM rugi tanpa sadar. Ini cara menghitung HPP produk UMKM langkah demi langkah, lengkap dengan contoh perhitungan.',
                'meta_title' => 'Cara Menghitung HPP Produk UMKM (Lengkap dengan Contoh)',
                'meta_description' => 'Cara menghitung HPP (harga pokok produksi) produk UMKM langkah demi langkah, lengkap dengan rumus dan contoh perhitungan supaya harga jual tidak rugi.',
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

## Rumus Sederhana Menghitung HPP

Rumus paling dasar: **HPP per unit = Total Biaya Produksi ÷ Jumlah Unit yang Dihasilkan**

Contoh perhitungan: sebuah usaha membuat 50 pcs tas kain dalam satu batch produksi, dengan rincian biaya bahan kain dan aksesoris Rp1.500.000, upah jahit borongan Rp750.000, biaya listrik dan penyusutan mesin jahit diperkirakan Rp100.000, dan kemasan Rp150.000 (Rp3.000 × 50 pcs). Total biaya produksi: Rp2.500.000.

HPP per unit = Rp2.500.000 ÷ 50 pcs = **Rp50.000 per tas**.

Dari angka ini, kalau usaha ingin margin 40%, harga jual minimal yang masuk akal adalah Rp50.000 + (40% × Rp50.000) = Rp70.000. Kalau selama ini dijual di bawah angka itu — misalnya Rp55.000 karena "sudah biasa segitu" — margin sebenarnya hanya 10%, jauh dari target yang dikira sudah tercapai.

## Kesalahan Umum saat Menghitung HPP

Kesalahan paling sering: hanya menghitung biaya bahan baku dan lupa memasukkan tenaga kerja atau overhead, sehingga HPP terlihat lebih rendah dari kenyataan dan harga jual jadi terlalu murah. Kesalahan lain adalah menghitung HPP sekali saja di awal, lalu tidak pernah diperbarui meski harga bahan baku sudah naik — akibatnya margin tergerus pelan-pelan tanpa disadari.

## Kapan HPP Perlu Dihitung Ulang

Idealnya, HPP ditinjau ulang setiap kali ada perubahan signifikan pada harga bahan baku, upah, atau resep/formula produk. Untuk usaha dengan bahan baku yang harganya fluktuatif, meninjau HPP tiap bulan lebih aman daripada menunggu sampai margin terasa menipis baru disadari.

## Overhead Sering Terlewat, Padahal Nilainya Nyata

Dari empat komponen HPP, overhead paling sering diabaikan karena tidak sejelas biaya bahan baku yang langsung terlihat dari nota pembelian. Padahal listrik untuk oven atau mesin jahit, gas untuk produksi, sampai penyusutan alat yang dipakai berulang kali tetap merupakan biaya nyata yang menopang produksi.

Cara praktis mengestimasi overhead tanpa perlu hitungan akuntansi rumit: jumlahkan total biaya listrik, gas, dan penyusutan alat dalam sebulan, lalu bagi dengan jumlah total unit yang diproduksi bulan itu untuk dapat estimasi overhead per unit. Angka ini tidak perlu presisi sampai rupiah terakhir — yang penting ada, supaya HPP tidak terlihat lebih murah dari kenyataan hanya karena satu komponen biaya terlewat begitu saja.

Modul produksi dan stok bahan baku Fabriku mencatat biaya per bahan secara otomatis dari setiap pembelian, sehingga perhitungan HPP bisa mengikuti harga bahan yang aktual, bukan asumsi harga lama yang sudah berubah.
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

Fabriku mencatat tanggal kadaluarsa per item stok dan menandai status "expired" secara otomatis begitu tanggalnya lewat, jadi produk yang mendekati kadaluarsa bisa terdeteksi lebih awal sebelum jadi masalah di tangan pelanggan.
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
                'meta_description' => 'Cara menghitung harga jasa servis kecil (bengkel, laundry, reparasi elektronik) agar tidak rugi: komponen biaya waktu, bahan pendukung, dan overhead alat.',
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

## Contoh Perhitungan: Servis Ganti Oli dan Tune Up Motor

Misalnya sebuah bengkel menghitung harga jasa tune up motor: waktu kerja 45 menit dengan tarif tenaga kerja Rp40.000/jam (setara Rp30.000), bahan pendukung seperti cairan pembersih karburator dan majun Rp10.000, biaya listrik dan penyusutan alat kompresor diperkirakan Rp5.000. Total biaya: Rp45.000.

Kalau bengkel ingin margin 50%, harga jasa minimal yang masuk akal adalah Rp45.000 + (50% × Rp45.000) = Rp67.500, dibulatkan jadi Rp70.000 di luar harga sparepart yang diganti (oli, busi, dll dihitung terpisah sesuai harga beli). Kalau selama ini dipatok Rp50.000 rata dengan bengkel sebelah tanpa hitungan ini, marginnya jauh lebih tipis dari yang dikira, bahkan bisa nombok kalau servisnya ternyata makan waktu lebih lama dari biasanya.

## Kesalahan Umum yang Bikin Usaha Jasa Rugi Diam-Diam

Kesalahan paling sering: menyamakan tarif untuk semua tingkat kesulitan servis, padahal waktu dan bahan yang terpakai jauh berbeda. Kesalahan lain adalah tidak pernah meninjau ulang tarif meski harga bahan pendukung sudah naik, sehingga margin tergerus pelan-pelan tanpa disadari — mirip dengan kasus HPP produk yang tidak pernah dihitung ulang.

## Kapan Tarif Jasa Perlu Ditinjau Ulang

Tinjau ulang tarif setiap kali ada kenaikan signifikan pada harga bahan pendukung atau upah tenaga kerja. Untuk usaha yang bahan pendukungnya sering naik-turun harga, meninjau tarif tiap 2-3 bulan lebih aman daripada menunggu sampai terasa rugi baru disadari.

Untuk kategori usaha jasa, Fabriku menyediakan katalog layanan tanpa perlu modul material atau produksi, lengkap dengan pemetaan bahan pendukung per layanan yang otomatis terpotong dari stok setiap kali transaksi servis selesai — jadi biaya bahan pendukung tidak lagi terlewat dari perhitungan.
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

Kalau pengrajin ingin margin 40%, harga jual minimal adalah Rp25.000 + (40% × Rp25.000) = **Rp35.000**. Bandingkan dengan kalau harga hanya ditentukan dari "harga bahan plus sedikit untung" tanpa menghitung waktu kerja — misalnya dijual Rp15.000 karena dikira sudah untung dari modal bahan Rp8.000. Padahal setelah dihitung lengkap, harga segitu justru menombok waktu kerja sebesar Rp17.000 per gelang, yang berarti setiap gelang terjual justru mengurangi "gaji" yang seharusnya didapat pengrajin.

## Kerajinan Tangan Boleh Dihargai Lebih dari Sekadar HPP

Berbeda dari produk pabrikan yang harga jualnya cenderung mepet dengan HPP karena bersaing di volume, produk handmade punya nilai tambah dari keunikan, cerita di balik pembuatannya, dan edisi terbatas yang tidak bisa ditiru massal. Nilai tambah ini sah untuk dimasukkan ke margin yang lebih tinggi dari sekadar "HPP plus untung tipis" — asal tetap berangkat dari perhitungan biaya yang akurat, bukan tebakan.

## Kesalahan Umum Menentukan Harga Produk Handmade

Kesalahan paling sering adalah menghargai jam kerja sendiri sebagai Rp0 karena dianggap "hobi" atau "dikerjakan pas senggang", padahal waktu itu bisa dipakai untuk hal lain yang menghasilkan. Kesalahan lain adalah ikut-ikutan harga kompetitor tanpa tahu apakah kompetitor itu sendiri sudah menghitung biayanya dengan benar — bisa jadi kompetitor juga sama-sama rugi tanpa sadar, dan mengikuti harganya berarti ikut menjebak diri sendiri di harga yang tidak sehat.

Sebelum menentukan harga jual, ada baiknya juga menata dulu bagaimana [bahan baku kerajinan yang tidak standar](/blog/cara-mengelola-bahan-baku-kerajinan-tidak-standar) — seperti sisa kain, manik lepasan, atau potongan kayu — dicatat dan dihitung nilainya, karena tanpa itu komponen biaya bahan di atas hanya jadi perkiraan kasar. Untuk pembahasan HPP yang lebih mendalam di luar konteks kerajinan tangan, lihat juga [cara menghitung HPP produk UMKM](/blog/cara-menghitung-hpp-produk-umkm).

Fabriku mendukung pencatatan HPP per produk untuk usaha kerajinan, termasuk batch kecil dan produksi custom order, sehingga harga jual bisa ditentukan dari data biaya yang sebenarnya, bukan sekadar mengikuti harga pasar.
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

Retur yang dikelola rapi juga berkaitan dengan bagaimana [pesanan dari berbagai channel dikelola](/blog/cara-mengelola-pesanan-online-offline-sekaligus) — retur dari pembeli marketplace, misalnya, perlu jelas alurnya sampai kembali ke stok pusat toko, bukan hanya tercatat di sisi marketplace saja. Kalau toko masih mempertimbangkan sistem kasir yang bisa menangani ini, [tips memilih aplikasi kasir untuk UMKM pemula](/blog/tips-memilih-aplikasi-kasir-umkm-pemula) bisa jadi acuan awal.

Fabriku mencatat status inventory per item — termasuk status "rusak" yang terpisah dari stok yang tersedia untuk dijual — sehingga retur barang cacat tidak akan tercampur atau tidak sengaja ditawarkan lagi ke pelanggan berikutnya.
MD,
            ],
        ];
    }
}
