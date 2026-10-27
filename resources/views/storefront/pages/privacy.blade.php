<article class="mx-auto max-w-3xl px-4 py-12 text-slate-800">
    <h1 class="mb-5 text-3xl font-bold">Privasi pengunjung</h1>
    <p class="mb-4">Website ini dikelola oleh {{ $site->profile['name'] ?? $site->tenant->name }} menggunakan Fabriku.</p>
    <h2 class="mt-8 mb-2 text-xl font-semibold">Data yang Anda berikan</h2>
    <p>Jika Anda mengirim pesanan atau pertanyaan, toko menerima nama, nomor kontak, dan keterangan yang Anda isi. Untuk pesanan produk, toko juga menerima alamat pengiriman bila Anda mengisinya.</p>
    <h2 class="mt-8 mb-2 text-xl font-semibold">Untuk apa data dipakai</h2>
    <p>Toko memakai data tersebut untuk menghubungi Anda, menyiapkan penawaran atau pesanan, dan menindaklanjuti pembayaran yang dilakukan di luar website. Fabriku memproses data ini untuk menjalankan layanan atas nama toko.</p>
    @if ($site->googleAnalyticsMeasurementId())
        <h2 class="mt-8 mb-2 text-xl font-semibold">Statistik kunjungan</h2>
        <p>Website ini memakai Google Analytics untuk memahami jumlah kunjungan, halaman yang dibuka, dan sumber pengunjung. Google Analytics menggunakan cookie dan memproses informasi perangkat serta aktivitas kunjungan. Isi formulir pesanan tidak dikirim ke Google Analytics. Personalisasi iklan tidak diaktifkan. Baca <a class="underline" href="https://policies.google.com/privacy">kebijakan privasi Google</a> untuk informasi lebih lanjut.</p>
    @endif
    <h2 class="mt-8 mb-2 text-xl font-semibold">Hubungi pengelola</h2>
    <p>Untuk pertanyaan atau permintaan terkait data Anda, hubungi toko melalui kontak yang tertera di website ini. Anda juga dapat membaca <a class="underline" href="https://fabriku.id/privasi">kebijakan privasi Fabriku</a>.</p>
</article>
