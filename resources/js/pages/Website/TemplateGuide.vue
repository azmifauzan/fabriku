<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const slots = [
    ['logo', 'Logo dan nama usaha'],
    ['nav', 'Navigasi toko'],
    ['products', 'Daftar produk dan tombol pembelian'],
    ['services', 'Daftar layanan'],
    ['featured-product', 'Produk unggulan'],
    ['lead-form', 'Formulir permintaan layanan'],
    ['cart-button', 'Tombol keranjang'],
    ['whatsapp-button', 'Tombol WhatsApp'],
    ['map', 'Peta lokasi'],
    ['footer-links', 'Tautan halaman tambahan'],
];
</script>

<template>
    <AppLayout>
        <Head title="Panduan Template Website" />
        <main class="mx-auto box-content max-w-5xl space-y-6 px-6 py-6 text-slate-800 dark:text-slate-200">
            <Link href="/website?tab=desain" class="text-sm font-semibold text-teal-700 underline dark:text-teal-400">← Kembali ke Desain</Link>
            <header>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white">Panduan Template Website</h1>
                <p class="mt-2">
                    Buat desain dalam HTML dan CSS, lalu unggah sebagai ZIP di menu Desain. Setiap template disimpan sebelum Anda memilih untuk
                    mengaktifkannya.
                </p>
                <a
                    href="/template-website-contoh.zip"
                    download
                    class="mt-4 inline-block rounded-lg bg-teal-800 px-4 py-3 text-sm font-semibold text-white"
                    >Unduh contoh ZIP</a
                >
            </header>
            <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-bold">1. Siapkan file</h2>
                <p>
                    Letakkan <code>index.html</code> di root ZIP sebagai halaman depan. File HTML lain menjadi halaman tambahan:
                    <code>tentang.html</code> tersedia di <code>/halaman/tentang</code>. Tautkan halaman menggunakan alamat tersebut.
                </p>
                <p>
                    Nama file menggunakan huruf kecil, angka dan tanda hubung. Maksimal 30 file, ZIP 2 MB dan total isi 2 MB; setiap file maksimal 500
                    KB. Nama halaman tidak boleh sama dengan halaman tambahan yang sudah ada.
                </p>
                <p>
                    File CSS di root ZIP diterapkan ke semua halaman secara otomatis; CSS juga dapat ditulis dalam elemen <code>style</code>. Gabungan
                    CSS tiap halaman maksimal 100 KB. Gambar lokal dan subfolder dalam ZIP belum didukung; gunakan URL gambar HTTPS.
                </p>
                <pre class="overflow-x-auto rounded-lg bg-slate-100 p-4 text-sm dark:bg-slate-800">
index.html
tentang.html
gaya.css</pre
                >
            </section>
            <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-bold">2. Pasang fungsi Fabriku</h2>
                <p>
                    Gunakan elemen <code>div</code> atau <code>span</code> kosong dengan atribut slot. Fabriku menggantinya dengan fungsi yang
                    terhubung ke data usaha.
                </p>
                <pre class="overflow-x-auto rounded-lg bg-slate-100 p-4 text-sm dark:bg-slate-800">
&lt;div data-fb-slot="products"&gt;&lt;/div&gt;</pre
                >
                <dl class="grid gap-2 sm:grid-cols-2">
                    <div v-for="slot in slots" :key="slot[0]">
                        <dt class="font-mono text-sm font-semibold">{{ slot[0] }}</dt>
                        <dd class="text-sm">{{ slot[1] }}</dd>
                    </div>
                </dl>
                <p>
                    Untuk header atau footer khusus, gunakan <code>&lt;header data-fb-shell="header"&gt;</code> dan
                    <code>&lt;footer data-fb-shell="footer"&gt;</code> langsung di dalam body pada index.html. Jika tidak ada, desain memakai header
                    atau footer bawaan.
                </p>
            </section>
            <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-bold">3. Periksa dan aktifkan</h2>
                <p>
                    Unggah ZIP, perbaiki pesan validasi bila ada, lalu buka thumbnail atau pratinjau pada daftar template. Pilih Aktifkan untuk
                    menerapkannya ke website. Hasil Satsetui juga tersedia pada daftar yang sama.
                </p>
                <p>
                    HTML diperiksa dan disaring. Skrip, formulir sendiri, event handler, style inline, stylesheet eksternal, CSS <code>url()</code>,
                    <code>@import</code>, dan escape CSS tidak diterima. Gunakan slot untuk fitur interaktif.
                </p>
            </section>
            <section class="space-y-3 rounded-xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
                <h2 class="text-lg font-bold">4. Edit langsung di Fabriku</h2>
                <p>Klik Edit HTML pada template. Pilih file yang akan diubah, atau tambahkan file HTML/CSS baru. index.html harus tetap ada.</p>
                <p>
                    Pilih halaman pada bagian pratinjau lalu klik Perbarui pratinjau. Pratinjau memeriksa kode tanpa menyimpan atau mengubah website.
                    CSS di index.html ikut digunakan pada halaman tambahan agar header dan footer tetap konsisten.
                </p>
                <p>
                    Klik Simpan template untuk memperbarui template yang sama. Jika template sedang aktif, perubahan langsung tampil di website
                    setelah konfirmasi. Jika template telah diubah di tab lain, sistem menolak penyimpanan agar perubahan tidak tertimpa.
                </p>
                <p>
                    Ruang, Etalase, dan Studio selalu tersedia. Mengedit desain bawaan menyimpan template kustom milik usaha Anda; Anda dapat kembali
                    memilih desain bawaan kapan saja.
                </p>
            </section>
        </main>
    </AppLayout>
</template>
