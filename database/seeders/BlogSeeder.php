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
     * Seeds 6 initial blog posts covering 3 researched long-tail keywords:
     * - "cara mengelola stok bahan baku UMKM agar tidak rugi"
     * - "aplikasi pencatatan produksi usaha kecil menengah"
     * - "cara membuat laporan penjualan UMKM sederhana"
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        $author = AdminUser::where('email', 'admin@fabriku.web.id')->firstOrFail();

        $categories = collect([
            'Manajemen Stok' => 'manajemen-stok',
            'Produksi' => 'produksi',
            'Penjualan' => 'penjualan',
        ])->map(fn (string $slug, string $name) => BlogCategory::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        ));

        foreach ($this->posts() as $data) {
            $post = BlogPost::firstOrCreate(
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

        $this->command->info('Blog seeded: 6 posts across 3 categories.');
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

Laporan Fabriku sudah memecah data penjualan berdasarkan channel dan status pembayaran secara otomatis, jadi metrik-metrik ini bisa langsung dilihat tanpa harus menyusun ulang dari data mentah setiap bulan.
MD,
            ],
        ];
    }
}
