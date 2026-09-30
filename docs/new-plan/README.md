# Rencana Website Usaha dan Pusat Sosial Fabriku

Status: master plan awal 23 September 2026, dengan pembaruan alur Satsetui 30 September 2026. Jadwal dan harga lama belum otomatis menjadi komitmen rilis atau harga berlaku.

Status implementasi terakhir diaudit 29 September 2026: MVP Website Usaha di repo dan produksi Fabriku sudah memiliki pengelola situs, tiga preset desain lokal, katalog produk/jasa, checkout pesanan draft, prospek jasa, notifikasi staf, dan alur domain mandiri. Image `290926-2` sehat, fallback origin Cloudflare aktif, dan smoke test publik lulus. Integrasi desain AI/API Satsetui belum ada. Pusat Sosial dan perubahan harga sengaja ditunda. Lihat [status implementasi](master-plan.md) dan [checklist rilis storefront](storefront-release-checklist.md).

**Mulai dari [Master Plan](master-plan.md).** Untuk pekerjaan storefront berikutnya, baca juga [Integrasi Satsetui–Storefront](satsetui-storefront-integration-plan.md). Itu adalah keputusan terbaru: akun Satsetui baru dari email Fabriku terverifikasi dibuat dan langsung verified dengan welcome credit sesuai aturan Satsetui; akun lama ditautkan setelah verifikasi sekali. Setelah tertaut, Fabriku langsung membuka wizard privat Satsetui. Kredit/top-up dan editor tetap di Satsetui, Fabriku menerima hasil final. Bagian sosial media serta harga paket masih rencana lama dan ditunda.

| Dokumen | Baca untuk |
|---|---|
| [Master Plan](master-plan.md) | Keputusan produk dan arsitektur; jadwal/harga awal diberi status historis sampai diputuskan ulang |
| [Tinjauan dan rekomendasi](review-dan-rekomendasi.md) | Riset Repliz, eksis, Postiz, Satsetui, kode Fabriku, harga pembanding |
| [MVP Website Usaha](commerce-mvp-plan.md) | Detail alur pesanan/prospek dan batas data (rancangan awal) |
| [Paket, charge, dan pelanggan lama](website-usaha-pricing-plan.md) | Analisis billing awal dan pelanggan lama |
| [Integrasi Satsetui–Storefront](satsetui-storefront-integration-plan.md) | Fokus sekarang: penautan akun, login langsung ke wizard privat, kredit/editor Satsetui, impor hasil final ke Fabriku, halaman SEO, dan gerbang penerimaan |
| [API Satsetui](satsetui-api-plan.md) | Rancangan API umum untuk integrator lain; tidak menggantikan alur login/ekspor merchant Fabriku |
| [Branding dan SEO](brand-seo-expansion-plan.md) | Posisi merek, pembanding Indonesia, SEO situs Fabriku dan merchant |
| [Checklist rilis storefront](storefront-release-checklist.md) | Kondisi Cloudflare/origin, urutan deploy, smoke test, dan batas fitur yang belum rilis |
