<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import LegalLayout from '@/layouts/LegalLayout.vue';

defineProps<{ canonical: string }>();
</script>

<template>
    <SeoHead
        title="Kebijakan Privasi Fabriku"
        description="Pelajari data akun, data bisnis, dan data permintaan yang dikumpulkan melalui website usaha Fabriku serta cara penggunaannya."
        :canonical="canonical"
    />

    <LegalLayout title="Kebijakan Privasi" updated="1 Oktober 2026">
        <section>
            <h2>1. Pendahuluan</h2>
            <p>
                Kebijakan Privasi ini menjelaskan bagaimana Fabriku ("kami") mengumpulkan, menggunakan, menyimpan, dan melindungi data Anda saat
                menggunakan aplikasi operasional dan website usaha Fabriku di fabriku.id. Halaman ini menjelaskan data yang diproses ketika Anda
                memakai aplikasi atau formulir pada website usaha yang dibuat dengan Fabriku.
            </p>
        </section>

        <section>
            <h2>2. Data yang Kami Kumpulkan</h2>
            <ul>
                <li><strong>Data akun:</strong> nama, email, nomor telepon, dan kata sandi (tersimpan dalam bentuk terenkripsi/hash).</li>
                <li>
                    <strong>Data bisnis (tenant):</strong> data operasional yang Anda input, seperti bahan baku, produksi, stok inventaris, pesanan
                    penjualan, pelanggan, staf, dan laporan keuangan terkait bisnis Anda.
                </li>
                <li>
                    <strong>Data permintaan dari website usaha:</strong> nama dan nomor WhatsApp atau telepon yang dikirim melalui formulir permintaan
                    layanan; isi pesan atau layanan yang dipilih jika tersedia; serta nama, nomor WhatsApp atau telepon, item pesanan, dan data
                    opsional seperti email, alamat pengiriman, atau catatan saat pengunjung mengirim permintaan pesanan produk. Data ini tersimpan
                    sebagai catatan bisnis pada akun usaha terkait.
                </li>
                <li><strong>Data teknis:</strong> alamat IP, jenis perangkat/browser, dan log aktivitas untuk keamanan dan audit.</li>
                <li>
                    <strong>Data integrasi opsional:</strong> chat ID Telegram (jika Anda menghubungkan bot Telegram) dan riwayat percakapan dengan
                    asisten AI (jika fitur ini digunakan).
                </li>
            </ul>
        </section>

        <section>
            <h2>3. Bagaimana Kami Menggunakan Data</h2>
            <ul>
                <li>Menjalankan dan memelihara fungsi inti aplikasi (produksi, inventaris, penjualan, laporan).</li>
                <li>Mengirim notifikasi transaksional (verifikasi email, reset password, pengingat masa uji coba/langganan).</li>
                <li>
                    Menampilkan produk, layanan, dan halaman informasi usaha, lalu mencatat permintaan yang dikirim melalui website agar usaha terkait
                    dapat melihat dan menindaklanjutinya.
                </li>
                <li>Menjaga keamanan akun dan mencegah penyalahgunaan.</li>
                <li>Meningkatkan kualitas layanan berdasarkan pola penggunaan (secara agregat, tanpa mengidentifikasi Anda secara personal).</li>
            </ul>
        </section>

        <section>
            <h2>4. Berbagi Data dengan Pihak Ketiga</h2>
            <p>Kami tidak menjual data Anda. Data hanya dibagikan ke pihak ketiga berikut sepanjang diperlukan untuk menjalankan layanan:</p>
            <ul>
                <li><strong>Penyedia email transaksional:</strong> mengirim email verifikasi, reset password, dan pengingat.</li>
                <li><strong>Penyedia penyimpanan berkas (object storage):</strong> menyimpan foto produk dan lampiran yang Anda unggah.</li>
                <li>
                    <strong>SatsetUI:</strong> sistem terpisah yang kami operasikan untuk generator desain. Saat Anda memulai desain khusus, Fabriku
                    mengirim ID pengguna Fabriku, ID website, nama, dan email untuk menautkan sesi dengan akun serta website terkait. Saat Anda
                    mengekspor hasilnya, Fabriku menerima template dan menyimpannya sebagai draf desain. Kredit, top-up kredit, dan penyuntingan
                    berlangsung di SatsetUI.
                </li>
                <li>
                    <strong>OpenAI:</strong> jika Anda menggunakan fitur Asisten AI, sebagian data bisnis relevan dikirim ke OpenAI untuk menghasilkan
                    jawaban. Fitur ini bersifat opsional.
                </li>
                <li>
                    <strong>Email dan Telegram:</strong> notifikasi permintaan dari website dapat dikirim kepada staf usaha yang ditunjuk melalui
                    email dan, jika chat Telegram staf telah dihubungkan, Telegram. Notifikasi berisi jenis dan nomor referensi permintaan serta
                    tautan ke Fabriku, bukan rincian kontak pengunjung. Jika Anda memakai bot Telegram Fabriku, chat ID dan pesan yang dikirim ke bot
                    diproses untuk memberikan balasan. Penggunaan bot ini bersifat opsional.
                </li>
            </ul>
        </section>

        <section>
            <h2>5. Keamanan Data</h2>
            <p>
                Setiap tenant (bisnis) terisolasi secara logis satu sama lain. Tenant lain tidak dapat mengakses data bisnis Anda. Kata sandi disimpan
                dalam bentuk hash, bukan teks biasa. Koneksi ke aplikasi dienkripsi menggunakan HTTPS/TLS.
            </p>
        </section>

        <section>
            <h2>6. Retensi Data</h2>
            <p>
                Data bisnis Anda, termasuk catatan permintaan dari website usaha, disimpan selama akun Anda aktif. Akun demo yang disediakan untuk
                keperluan uji coba publik akan direset otomatis secara berkala dan tidak digunakan untuk menyimpan data nyata. Jika Anda menutup akun,
                data dapat dihapus permanen atas permintaan sesuai ketentuan yang berlaku.
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
                Kami menggunakan cookie sesi untuk menjaga status login Anda tetap aman. Cookie ini bersifat esensial untuk berjalannya aplikasi dan
                tidak digunakan untuk pelacakan iklan pihak ketiga.
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
                <a href="mailto:hello@fabriku.id">hello@fabriku.id</a>.
            </p>
        </section>
    </LegalLayout>
</template>
