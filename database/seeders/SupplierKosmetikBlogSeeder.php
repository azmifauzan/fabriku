<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * One long-tail post for the reseller/cosmetics audience. Idempotent via slug.
 * Run on demand: php artisan db:seed --class=SupplierKosmetikBlogSeeder
 */
class SupplierKosmetikBlogSeeder extends Seeder
{
    public function run(): void
    {
        $author = AdminUser::where('email', 'admin@fabriku.id')->firstOrFail();
        $category = BlogCategory::firstOrCreate(['slug' => 'manajemen-stok'], ['name' => 'Manajemen Stok']);

        $slug = 'cara-memilih-supplier-kosmetik-skincare-reseller-cek-reputasi-merek';
        $publishedAt = BlogPost::where('slug', $slug)->value('published_at') ?? now();

        $post = BlogPost::updateOrCreate(['slug' => $slug], [
            'admin_user_id' => $author->id,
            'blog_category_id' => $category->id,
            'title' => 'Cara Memilih Supplier Kosmetik dan Skincare untuk Dijual Ulang: Cek Reputasi Merek Dulu',
            'excerpt' => 'Sebelum mengambil stok kosmetik atau skincare untuk dijual ulang, cek izin edar, reputasi merek, dan kebijakan retur supplier. Ini urutan pengecekannya.',
            'content' => $this->content(),
            'status' => 'published',
            'published_at' => $publishedAt,
            'meta_title' => 'Cara Memilih Supplier Kosmetik dan Skincare untuk Reseller',
            'meta_description' => 'Panduan memilih supplier kosmetik dan skincare untuk dijual ulang: cek izin BPOM, reputasi merek, kebijakan retur, dan catat stok dengan benar.',
        ]);

        $tagIds = collect(['UMKM', 'Kosmetik', 'Reseller'])->map(
            fn (string $name) => BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id
        );
        $post->tags()->sync($tagIds);
    }

    private function content(): string
    {
        return <<<'MD'
Banyak reseller kosmetik mulai dari satu pertanyaan: supplier mana yang kasih harga paling murah? Pertanyaan yang lebih berguna datang sebelum itu: merek apa yang aman dijual ulang, dan supplier mana yang mau menanggung barang bermasalah? Harga modal yang rendah tidak menolong kalau separuh stok diretur pembeli.

## Mulai dari izin edar, bukan dari harga

Kosmetik dan skincare yang beredar di Indonesia wajib punya nomor notifikasi BPOM. Minta nomor itu dari supplier, lalu cek sendiri di situs resmi BPOM. Prosesnya beberapa menit, dan hasilnya menunjukkan apakah nama produk, pemilik izin, dan kemasan yang kamu pegang sesuai dengan catatan resmi. Supplier yang enggan menyebut nomor izin biasanya punya alasan.

## Lihat apa yang dikatakan pembeli sungguhan

Klaim di kemasan ditulis oleh merek. Ulasan dan percakapan publik ditulis oleh pemakai. Cari pola yang berulang: kulit iritasi setelah dua minggu, kemasan bocor di pengiriman, atau wangi yang dikeluhkan banyak orang. Satu keluhan bisa kasuistik, tetapi lima keluhan yang sama dalam sebulan layak membuatmu berhenti.

Cara paling cepat adalah memakai indeks yang sudah merangkum percakapan itu. Di SuaraNetijen, kamu bisa membuka [ranking brand FMCG dengan sentimen netijen tertinggi](https://suaranetijen.id/top/fmcg?utm_source=fabriku.id&utm_medium=blog), lalu klik tiap merek untuk melihat jumlah opini, pembagian positif dan negatif, serta tema yang paling sering muncul. Skor itu menggambarkan suara netizen secara umum, bukan hasil uji laboratorium, jadi pakai sebagai penyaring awal sebelum memesan sampel.

## Tiga pertanyaan untuk supplier

1. **Bagaimana kebijakan retur untuk barang cacat atau rusak saat pengiriman?** Minta jawabannya tertulis. Supplier yang yakin dengan barangnya biasanya tidak keberatan.
2. **Berapa tanggal kedaluwarsa rata-rata barang yang dikirim?** Kosmetik yang sudah mendekati kedaluwarsa menyulitkan penjualan, dan risikonya jatuh ke kamu.
3. **Apakah ada sampel atau paket uji?** Paket kecil memungkinkan kamu menilai kualitas dan respons pembeli sebelum menaruh modal besar.

## Catat dari hari pertama barang masuk

Setelah barang datang, catat nama produk, nomor batch, tanggal kedaluwarsa, jumlah, dan harga modal. Tanpa catatan itu, kamu tidak tahu stok mana yang harus dijual lebih dulu dan berapa untung sebenarnya. Prinsip FIFO, yaitu barang yang masuk lebih dulu keluar lebih dulu, mencegah stok lama tertimbun di belakang. Baca juga [cara mengelola stok kosmetik agar tidak kedaluwarsa](/blog/cara-mengelola-stok-kosmetik-agar-tidak-kadaluarsa) untuk langkah yang lebih rinci.

Di Fabriku, kamu bisa mencatat stok masuk dan keluar per produk, menetapkan batas minimum stok, dan melihat laporan penjualan sederhana. Dengan begitu keputusan memesan ulang bersandar pada angka, bukan perasaan.

## Mulai kecil, lalu naikkan

Ambil paket uji dua sampai tiga merek, jual ke lingkaran terdekat, dan catat siapa yang memesan ulang. Pesanan ulang adalah sinyal paling jujur bahwa produknya cocok. Setelah dua atau tiga siklus, kamu punya data penjualan sendiri untuk dibandingkan dengan opini publik. Merek yang lolos di dua sisi itulah yang layak mendapat stok lebih besar.

Reseller yang bertahan lama biasanya bukan yang menemukan modal termurah. Mereka yang memilih merek dengan reputasi baik, supplier yang bertanggung jawab, dan stok yang tercatat rapi.
MD;
    }
}
