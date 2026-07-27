<script setup lang="ts">
import LegalLayout from '@/layouts/LegalLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Kebijakan Privasi — Fabriku" />

    <LegalLayout title="Kebijakan Privasi" updated="27 Juli 2026">
        <section>
            <h2>1. Pendahuluan</h2>
            <p>
                Kebijakan Privasi ini menjelaskan bagaimana Fabriku ("kami") mengumpulkan, menggunakan, menyimpan, dan melindungi data Anda saat
                menggunakan aplikasi manajemen produksi dan penjualan Fabriku di fabriku.web.id. Dengan menggunakan Fabriku, Anda menyetujui praktik
                yang dijelaskan di halaman ini.
            </p>
        </section>

        <section>
            <h2>2. Data yang Kami Kumpulkan</h2>
            <ul>
                <li><strong>Data akun</strong> — nama, email, nomor telepon, dan kata sandi (tersimpan dalam bentuk terenkripsi/hash).</li>
                <li>
                    <strong>Data bisnis (tenant)</strong> — data operasional yang Anda input: bahan baku, produksi, stok inventaris, pesanan
                    penjualan, pelanggan, staf, dan laporan keuangan terkait bisnis Anda.
                </li>
                <li><strong>Data teknis</strong> — alamat IP, jenis perangkat/browser, dan log aktivitas untuk keamanan dan audit.</li>
                <li>
                    <strong>Data integrasi opsional</strong> — chat ID Telegram (jika Anda menghubungkan bot Telegram) dan riwayat percakapan dengan
                    asisten AI (jika fitur ini digunakan).
                </li>
            </ul>
        </section>

        <section>
            <h2>3. Bagaimana Kami Menggunakan Data</h2>
            <ul>
                <li>Menjalankan dan memelihara fungsi inti aplikasi (produksi, inventaris, penjualan, laporan).</li>
                <li>Mengirim notifikasi transaksional (verifikasi email, reset password, pengingat masa uji coba/langganan).</li>
                <li>Menjaga keamanan akun dan mencegah penyalahgunaan.</li>
                <li>Meningkatkan kualitas layanan berdasarkan pola penggunaan (secara agregat, tanpa mengidentifikasi Anda secara personal).</li>
            </ul>
        </section>

        <section>
            <h2>4. Berbagi Data dengan Pihak Ketiga</h2>
            <p>Kami tidak menjual data Anda. Data hanya dibagikan ke pihak ketiga berikut sepanjang diperlukan untuk menjalankan layanan:</p>
            <ul>
                <li><strong>Penyedia email transaksional</strong> — untuk mengirim email verifikasi, reset password, dan pengingat.</li>
                <li><strong>Penyedia penyimpanan berkas (object storage)</strong> — untuk menyimpan foto produk dan lampiran yang Anda unggah.</li>
                <li>
                    <strong>OpenAI</strong> — jika Anda menggunakan fitur Asisten AI, sebagian data bisnis relevan dikirim ke OpenAI untuk
                    menghasilkan jawaban. Fitur ini bersifat opsional.
                </li>
                <li>
                    <strong>Telegram</strong> — jika Anda menghubungkan akun ke bot Telegram Fabriku, chat ID dan pesan yang Anda kirim ke bot
                    diproses untuk memberikan balasan. Fitur ini bersifat opsional dan dapat diputus kapan saja.
                </li>
            </ul>
        </section>

        <section>
            <h2>5. Keamanan Data</h2>
            <p>
                Setiap tenant (bisnis) terisolasi secara logis satu sama lain — data bisnis Anda tidak dapat diakses oleh tenant lain. Kata sandi
                disimpan dalam bentuk hash, bukan teks biasa. Koneksi ke aplikasi dienkripsi menggunakan HTTPS/TLS.
            </p>
        </section>

        <section>
            <h2>6. Retensi Data</h2>
            <p>
                Data bisnis Anda disimpan selama akun Anda aktif. Akun demo yang disediakan untuk keperluan uji coba publik akan direset otomatis
                secara berkala dan tidak digunakan untuk menyimpan data nyata. Jika Anda menutup akun, data dapat dihapus permanen atas permintaan
                sesuai ketentuan yang berlaku.
            </p>
        </section>

        <section>
            <h2>7. Hak Anda</h2>
            <p>Sesuai dengan Undang-Undang Perlindungan Data Pribadi (UU PDP No. 27 Tahun 2022), Anda berhak untuk:</p>
            <ul>
                <li>Mengakses dan meminta salinan data pribadi Anda.</li>
                <li>Meminta koreksi data yang tidak akurat.</li>
                <li>Meminta penghapusan akun beserta data terkait.</li>
                <li>Menarik persetujuan atas fitur opsional (misalnya integrasi Telegram atau Asisten AI) kapan saja.</li>
            </ul>
        </section>

        <section>
            <h2>8. Cookie & Sesi</h2>
            <p>
                Kami menggunakan cookie sesi untuk menjaga status login Anda tetap aman. Cookie ini bersifat esensial untuk berjalannya aplikasi
                dan tidak digunakan untuk pelacakan iklan pihak ketiga.
            </p>
        </section>

        <section>
            <h2>9. Perubahan Kebijakan</h2>
            <p>
                Kami dapat memperbarui kebijakan ini dari waktu ke waktu. Perubahan signifikan akan diinformasikan melalui email atau notifikasi di
                dalam aplikasi. Tanggal "Terakhir diperbarui" di atas menunjukkan versi kebijakan yang berlaku saat ini.
            </p>
        </section>

        <section>
            <h2>10. Kontak</h2>
            <p>
                Pertanyaan seputar privasi dan data Anda dapat disampaikan ke
                <a href="mailto:hello@fabriku.web.id">hello@fabriku.web.id</a>.
            </p>
        </section>
    </LegalLayout>
</template>
