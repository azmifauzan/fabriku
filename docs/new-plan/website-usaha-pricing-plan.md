# Rencana paket, charge, dan migrasi pelanggan Fabriku

Status: usulan keputusan produk, 23 September 2026. **Belum merupakan harga atau hak paket yang berlaku.** Baca bersama [MVP Website Usaha](commerce-mvp-plan.md), [API Satsetui](satsetui-api-plan.md), dan [branding/SEO](brand-seo-expansion-plan.md).

## Prinsip yang dikunci

Pelanggan berhadapan hanya dengan akun, paket, tagihan, dan dukungan Fabriku. Nama Satsetui/Repliz boleh tampil sebagai `Powered by`, tetapi tidak ada pendaftaran, pembelian kredit, atau pembayaran langsung ke mereka. Fabriku menanggung biaya penyedia dan mengelola kegagalannya. Perizinan OAuth akun sosial dan pengaturan DNS domain sendiri tetap memerlukan tindakan pemilik; jangan menyebutnya otomatis sepenuhnya.

**Rekomendasi MVP: dua pilihan dalam satu langganan tenant**, bukan tiga langganan vendor atau tarif berbeda untuk produk dan jasa:

| Paket | Hak yang dijual setelah siap rilis | Cara bayar |
|---|---|---|
| `Core` (nama publik bisa tetap nama paket sekarang) | Seluruh fitur operasional Fabriku yang pelanggan lama sudah miliki | Harga dan siklus bulanan/tahunan lama tetap berlaku sampai masa berbayar berakhir; tidak dipotong karena peluncuran fitur baru |
| `Online` | Semua hak Core + satu Website Usaha mode produk/jasa/gabungan, subdomain, opsi domain sendiri, template/editor, permintaan masuk, kuota desain Satsetui, kuota Social Kit, akun sosial dan jadwal terbatas melalui Repliz | Satu harga Fabriku per tenant per periode. Batas masing-masing ditampilkan sebagai hak Fabriku, bukan saldo Satsetui/Repliz |

Mode website tidak boleh menjadi sumber charge terpisah: usaha jasa yang tidak butuh keranjang tidak seharusnya membayar tiga kali hanya karena memilih modul berbeda. Tidak ada biaya per lead, per pesanan, atau komisi transaksi pada MVP; pelanggan menangani pembayaran konsumennya sendiri. Pembelian domain di registrar dan layanan di luar cakupan Fabriku harus disebut terpisah dengan jelas. Domain sendiri berarti koneksi/sertifikat dikelola Fabriku, bukan Fabriku otomatis membelikan domain.

## Kuota dan biaya tambahan

Rumus harga Online yang harus diuji: `harga Core + cadangan biaya AI desain/Social Kit + alokasi Repliz (tier API, OAuth, akun, Storage, jadwal) + hosting/TLS/media/domain + payment fee + support + margin`. Hitung dari median **dan p95** pemakaian pilot per tenant per bulan, bukan dari harga vendor per kredit saja. Biaya tetap Repliz dibagi pada jumlah tenant aktif realistis, bukan asumsi seluruh pelanggan langsung upgrade. Jangan mencantumkan nominal publik sebelum hak resale/multi-merchant Repliz, tarif aktual, dan margin disetujui. Dokumentasi Repliz mencantumkan Schedule API pada Premium+, OAuth pada Gold+, dan Storage sebagai add-on; konfirmasi kontrak serta biaya aktual dengan penyedia. [Tier API Repliz](https://docs.repliz.com/api/install), [panduan Storage](https://docs.repliz.com/api/guides/storage-upload).

Kuota awal ditetapkan setelah pilot untuk **satu situs**, generasi tema, generasi Social Kit/slide, akun sosial terhubung, jadwal posting per bulan, serta penyimpanan media. Tampilkan unit yang dimengerti pelanggan, misalnya `X desain situs` dan `Y paket konten sosial`, bukan `N kredit Satsetui`. Tunjukkan sisa kuota dan kapan reset. Konten yang gagal dibuat karena kesalahan sistem/vendor harus dikembalikan satu kali; permintaan yang sukses tetapi tidak disukai pengguna mengikuti kebijakan regenerasi yang dipublikasikan. Kuota habis menghentikan permintaan baru, **tidak** menghapus website yang sudah terbit atau posting yang sudah berhasil terbit.

MVP tidak perlu marketplace add-on. Jika pilot menunjukkan kebutuhan, pembelian tambahan paling sederhana adalah **paket generasi sekali beli** yang ditagih Fabriku dan hanya menambah kuota desain/Social Kit, bukan saldo uang/kredit Satsetui yang dapat dipindah. Tambahan akun sosial atau jadwal baru dibuat setelah biaya Repliz dan dukungan operasionalnya terbukti. Kuota situs tidak bisa dibeli sebagai add-on sebelum multi-site benar-benar didukung.

## Perlakuan pelanggan eksisting

| Situasi | Kebijakan yang diusulkan |
|---|---|
| Pelanggan aktif bulanan/tahunan | Tetap menikmati Core sampai tanggal kedaluwarsa yang sudah dibayar. Tidak ada upgrade, perubahan harga, atau pemotongan masa aktif diam-diam. |
| Ingin mencoba | Beri pilot/trial Online terbatas yang tercatat sebagai entitlement terpisah; contoh kebijakan awal 30 hari, tanpa metode bayar vendor. Saat habis, minta pilihan eksplisit. Trial tidak mengubah masa aktif Core. |
| Ingin Online sekarang | Tampilkan biaya selisih prorata sampai akhir periode berjalan **sebelum** pelanggan membayar. Masa berlaku tidak direset. Setelah pembayaran terverifikasi, hak Online aktif; tagihan berikutnya satu harga Online. |
| Menunggu perpanjangan | Pilih Online saat memperpanjang; Core tetap berjalan sampai expiry, Online mulai pada periode baru. Tidak ada charge tengah periode. |
| Paket habis/menurun ke Core | Beri peringatan dan masa tenggang yang dipublikasikan; situs/data tetap dapat diakses pemilik untuk edit/ekspor, tetapi publikasi, formulir masuk, generasi AI, dan jadwal baru dihentikan setelah tenggang. Jadwal yang akan berjalan sesudah hak berakhir harus ditangani eksplisit (batalkan atau minta perpanjangan), bukan dibiarkan tak pasti. Jangan hapus domain, data, atau konten tanpa kebijakan retensi dan pemberitahuan. |

Contoh rumus prorata untuk pelanggan yang memiliki snapshot tarif periode: `max(0, harga Online periode - harga Core periode) × sisa hari / jumlah hari periode`, dibulatkan sekali dalam rupiah sesuai aturan billing; pajak/biaya pembayaran ditampilkan terpisah bila berlaku. Gunakan **selisih paket pada siklus yang sama** (bulanan dengan bulanan, tahunan dengan tahunan), bukan mengganti tarif lama secara retroaktif. Jika tarif historis/awal periode tidak dapat dibuktikan dari data, jangan menghitung otomatis: tampilkan penawaran manual yang disetujui pelanggan atau tunggu perpanjangan. Skema diskon warisan/grandfathering perlu aturan tertulis per cohort sebelum migrasi.

## Perubahan sistem billing yang memang diperlukan

Kode saat ini mengaktifkan `subscription_plan = full` dan menambah `subscription_expires_at` dari pembayaran bulanan/tahunan; `SubscriptionPayment` menyimpan jumlah, durasi, provider, dan waktu bayar, tetapi belum menjadi ledger entitlement Core/Online atau snapshot harga tiap periode. Karena itu **jangan mengganti string `full` menjadi `online` lalu menganggap migrasi selesai**. [SubscriptionService](../../app/Services/Subscription/SubscriptionService.php), [SubscriptionPayment](../../app/Models/SubscriptionPayment.php), [Tenant](../../app/Models/Tenant.php).

Perubahan minimum sebelum charge baru:

1. Pisahkan `plan_code`, `billing_cycle`, `period_start/end`, `price_snapshot`, `currency`, `entitlement_status`, dan sumber (`paid`, `trial`, `admin_grant`) dari status transaksi pembayaran. Simpan versi paket/kuota pada periode tersebut agar perubahan tarif mendatang tidak mengubah hak historis. Migrasikan `full` yang aktif menjadi Core tanpa mengubah expiry.
2. Buat satu fungsi pemeriksa hak tenant yang dipakai website, API generasi, dan jadwal sosial. Kunci/kuota per tenant dan periode; pencatatan konsumsi idempoten, atomik, dan punya reversal/refund ketika job gagal. Fabriku tidak mengungkap wallet Satsetui kepada tenant.
3. Alur upgrade dibuat sebagai invoice/pembayaran Fabriku dengan snapshot jumlah dan periode. Callback pembayaran tidak boleh memperpanjang expiry dua kali; perubahan hak aktif hanya sekali setelah verifikasi. Uji proration, pembaruan, downgrade, gagal bayar, refund, dan peralihan bulanan/tahunan.
4. Tampilkan di UI satu kartu paket: harga total, batas website/AI/akun/jadwal, pemakaian, tanggal perpanjangan, dan apa yang terjadi jika turun paket. Email perubahan paket memuat biaya selisih dan tanggal efektif. Support Fabriku tetap menjadi satu pintu.

## Gerbang harga dan keputusan pemilik produk

Sebelum mengumumkan nominal: (1) kontrak/izin Repliz untuk banyak merchant dan pembundelan biaya, (2) data pilot biaya dan penggunaan p95, (3) kuota yang masih masuk margin, (4) harga Core/Online bulanan-tahunan termasuk pajak/fee, (5) aturan legacy discount dan tenor trial, (6) durasi tenggang/retensi serta penanganan jadwal tertunda, (7) keputusan add-on bila ada. Semua angka dan kebijakan yang dipilih harus masuk syarat paket, halaman harga, email upgrade, dan tes billing. Tanpa gerbang ini, jalankan pilot tertutup tanpa charge Online baru.
