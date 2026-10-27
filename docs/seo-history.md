# Riwayat SEO Fabriku: apa yang sudah dikerjakan dan kenapa belum ada hasil

Disusun 2026-10-27 dari git log, `.remember/`, dokumen audit lama, GSC, dan RankMySEO. **Baca ini sebelum membuat plan SEO baru.**

## Timeline

| Tanggal | Kejadian | Catatan |
|---|---|---|
| 27 Jul | **Pindah domain #1**: `fabriku.my.id` → `fabriku.web.id` | |
| 30 Jul–3 Agu | Blog dibuka di `fabriku.web.id`, 6 post lalu jadi 12 | |
| 5 Sep | Audit #1: sitemap, canonical, noindex auth, JSON-LD, OG; post jadi 15 | GSC: 9 impresi/3 bulan, 0 backlink |
| 9–10 Sep | Audit #2: SSR dinyalakan (sebelumnya meta tag hanya ada di client), fix 500 OAuth, `lang=id`, internal link; post jadi 19 | 22 URL terindeks di `fabriku.web.id` |
| 20 Sep | **Pindah domain #2**: `fabriku.web.id` → `fabriku.id` | Data GSC `fabriku.id` baru mulai ~19 Sep |
| 13 Okt | Fix title ganda, event GA `sign_up`, 7 landing `/aplikasi/*`, 7 post baru; post jadi 26 | |
| 15 Okt | OG image | |
| 27 Okt | 2 klik, 155 impresi/28 hari, 34 terindeks, authority 4, 3 referring domain | |

## Kenapa belum ada hasil (urut dampak)

1. **Domain pindah 2 kali dalam 2 bulan.** Setiap pindah, Google memproses ulang seluruh situs, dan sinyal domain lama hanya sebagian yang diteruskan. Perbaikan teknis 5–10 Sep dikerjakan di `fabriku.web.id`, lalu 10 hari kemudian domainnya diganti. Efektif, `fabriku.id` baru berumur ~5 minggu di mata Google.
   - Properti GSC `fabriku.web.id` sudah tidak bisa diakses akun ini, jadi alat **Change of Address hampir pasti tidak pernah dipakai**.
   - `fabriku.my.id` me-redirect semua URL ke homepage `fabriku.id/`, bukan ke halaman yang setara. Link lama ke artikel kehilangan nilainya.
   - `www.fabriku.my.id` menjawab `200` dengan halaman "Tidak Ditemukan" (soft 404).
2. **Topik post dipilih tanpa data volume.** Sampai 13 Okt, semua target keyword adalah tebakan (diakui di `seo-keyword-strategy.md`: "isn't backed by a keyword-volume tool"). Contoh yang dicek 27 Okt dengan volume ~0/bulan: `cara mengelola stok bahan baku`, `maklun konveksi`, `piutang pelanggan`, `hpp konveksi`, `harga jasa servis`. Post-post itu bisa ranking, tapi tidak ada yang mencari.
3. **Backlink tidak pernah dikerjakan.** Direkomendasikan di setiap audit (5 Sep, 9 Sep, 13 Okt). Selalu ditandai "manual, di luar kode", dan tidak pernah dijalankan. Masih 3 referring domain.
4. **Ukuran keberhasilan tidak terpasang.** GA4 belum pernah menerima `sign_up`. Pendaftaran lewat Google kemungkinan tidak tercatat, karena gtag tidak dimuat di `/dashboard`. Akibatnya tidak ada yang tahu apakah SEO menghasilkan pendaftar.
5. **Fokus berulang di perbaikan teknis.** Perbaikan teknis memang perlu dan sudah beres: SSR, title, canonical, sitemap, robots, OG. Tapi audit berikutnya terus menemukan hal teknis kecil, sementara faktor 1–3 tidak tersentuh.

## Yang sudah benar (jangan dikerjakan ulang)

SSR + meta per halaman, 1 `<title>`/halaman, canonical, sitemap (37 URL), robots.txt route, noindex filter blog, JSON-LD Article/FAQ/Breadcrumb, OG image stabil, internal link antar post, 7 landing kategori, `fabriku.web.id` → `fabriku.id` 301 per path.

## Aturan supaya plan tidak sia-sia lagi

1. **Jangan pindah domain lagi.** `fabriku.id` final. Setiap migrasi mengulang hitungan dari nol.
2. **Setiap post baru wajib punya keyword target dengan volume terukur** (RankMySEO Keyword planner) dan minimal 3 kata. Tanpa angka, jangan ditulis.
3. **Backlink adalah item P0, bukan "manual nanti".** Setiap plan harus punya target backlink mingguan dengan nama pemiliknya.
4. **Ukur pendaftar, bukan cuma impresi.** Pastikan `sign_up` tercatat untuk semua jalur (password + Google) sebelum menilai hasil.
5. **Tidak ada audit teknis baru** sebelum ada bukti regresi. Cukup cek cepat dengan `title-count.sh` + curl.
6. **Beri waktu 8–12 minggu** setelah perubahan konten sebelum menilai. Domain baru memang lambat.
