<script setup lang="ts">
import FAQ from '@/components/Landing/FAQ.vue';
import SeoHead from '@/components/SeoHead.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { faqs } from '@/data/faq';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        settings?: {
            membership_price_monthly: number;
            membership_price_yearly: number;
        };
        canonical?: string;
    }>(),
    {
        settings: () => ({
            membership_price_monthly: 25000,
            membership_price_yearly: 250000,
        }),
        canonical: undefined,
    },
);

const description =
    'Kelola produksi, stok, dan penjualan di Fabriku. Buat website usaha untuk menampilkan produk atau layanan, menerima pesanan, dan mengelola permintaan pelanggan.';

const ogImage = computed(() => new URL('/images/fabriku-word.png', props.canonical ?? 'https://fabriku.id').toString());

const jsonLd = computed(() => {
    const origin = props.canonical ?? 'https://fabriku.id';

    return [
        {
            '@context': 'https://schema.org',
            '@type': 'Organization',
            name: 'Fabriku',
            url: origin,
            logo: new URL('/images/fabriku-logo-only.png', origin).toString(),
        },
        {
            '@context': 'https://schema.org',
            '@type': 'SoftwareApplication',
            name: 'Fabriku',
            applicationCategory: 'BusinessApplication',
            operatingSystem: 'Web',
            description,
            offers: {
                '@type': 'Offer',
                price: String(props.settings.membership_price_monthly),
                priceCurrency: 'IDR',
            },
        },
        {
            '@context': 'https://schema.org',
            '@type': 'FAQPage',
            mainEntity: faqs.map((faq) => ({
                '@type': 'Question',
                name: faq.question,
                acceptedAnswer: { '@type': 'Answer', text: faq.answer },
            })),
        },
    ];
});

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

const includedFeatures = [
    'Bahan baku dan inventaris',
    'Produksi internal dan outsourcing',
    'Penjualan dan sales order',
    'Website usaha dan katalog produk',
    'Pesanan produk dan permintaan jasa',
    'Export laporan Excel dan PDF',
];
</script>

<template>
    <SeoHead
        title="Fabriku | Produksi, Stok, dan Website Usaha untuk UMKM"
        :description="description"
        :canonical="canonical"
        :og-image="ogImage"
        :json-ld="jsonLd"
    />

    <PublicLayout>
        <div>
            <section id="top" class="border-b border-slate-200 bg-white">
                <div
                    class="mx-auto grid max-w-7xl gap-12 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1fr_0.92fr] lg:items-center lg:gap-16 lg:px-10 lg:py-24"
                >
                    <div>
                        <p class="text-sm font-bold text-indigo-800">Operasional dan website usaha untuk UMKM</p>
                        <h1 class="mt-5 max-w-3xl text-[clamp(2.65rem,6.5vw,5.1rem)] leading-[0.99] font-black tracking-[-0.055em] text-[#163761]">
                            Kelola usaha. Tampilkan produk dan layanan secara online.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-700">
                            Catat produksi, stok, dan penjualan di Fabriku. Buat website usaha untuk menerima pesanan produk atau permintaan jasa,
                            lalu tindak lanjuti bersama staf.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Link
                                href="/register"
                                class="inline-flex min-h-12 items-center justify-center rounded-md bg-indigo-700 px-6 text-sm font-bold text-white hover:bg-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                            >
                                Coba Fabriku 30 hari
                            </Link>
                            <a
                                href="#cara-kerja"
                                class="inline-flex min-h-12 items-center justify-center rounded-md px-5 text-sm font-bold text-[#163761] hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                            >
                                Lihat alur website
                            </a>
                        </div>

                        <p class="mt-6 text-sm font-semibold text-slate-600">Untuk usaha produk, jasa, maupun gabungan.</p>
                    </div>

                    <figure class="relative rounded-2xl bg-[#163761] p-5 text-white sm:p-7" aria-labelledby="website-flow-title">
                        <figcaption class="border-b border-white/20 pb-5">
                            <p class="text-sm font-semibold text-cyan-200">Alur website usaha</p>
                            <h2 id="website-flow-title" class="mt-2 max-w-md text-2xl leading-tight font-bold sm:text-3xl">
                                Data kerja tetap bertemu dengan pelanggan.
                            </h2>
                        </figcaption>

                        <div class="border-b border-white/20 py-5">
                            <p class="text-xs font-bold tracking-wide text-cyan-200">PRODUK</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-3 sm:gap-3">
                                <div class="rounded-md bg-white px-3 py-3 text-sm font-bold text-[#163761]">Stok di Fabriku</div>
                                <div class="rounded-md bg-white px-3 py-3 text-sm font-bold text-[#163761]">Katalog website</div>
                                <div class="rounded-md bg-white px-3 py-3 text-sm font-bold text-[#163761]">Pesanan ditindaklanjuti staf</div>
                            </div>
                            <p class="mt-3 max-w-lg text-sm leading-6 text-slate-100">
                                Pelanggan mengirim pesanan. Staf menghubungi mereka untuk memastikan detail dan pembayaran.
                            </p>
                        </div>

                        <div class="pt-5">
                            <p class="text-xs font-bold tracking-wide text-cyan-200">JASA</p>
                            <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-2 text-sm font-semibold">
                                <span>Layanan ditampilkan</span>
                                <span class="h-px w-8 bg-cyan-200" aria-hidden="true"></span>
                                <span>Permintaan masuk</span>
                                <span class="h-px w-8 bg-cyan-200" aria-hidden="true"></span>
                                <span>Staf menindaklanjuti</span>
                            </div>
                        </div>
                    </figure>
                </div>
            </section>

            <section id="cara-kerja" class="scroll-mt-6 border-b border-slate-200 bg-[#eef2f7]">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20 lg:px-10">
                    <div>
                        <p class="text-sm font-bold text-indigo-800">Satu alur kerja</p>
                        <h2 class="mt-3 max-w-lg text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">
                            Website memakai data usaha yang sudah Anda kelola.
                        </h2>
                        <p class="mt-5 max-w-lg leading-7 text-slate-700">
                            Produk yang ditampilkan memakai katalog dan stok Fabriku. Untuk usaha jasa, pelanggan mengirim permintaan melalui halaman
                            layanan.
                        </p>
                    </div>

                    <ol class="divide-y divide-slate-300 border-y border-slate-300">
                        <li class="grid gap-2 py-5 sm:grid-cols-[3.5rem_1fr] sm:gap-5">
                            <span class="text-sm font-bold text-indigo-800">01</span>
                            <div>
                                <h3 class="text-lg font-bold text-[#163761]">Pilih yang ingin ditampilkan</h3>
                                <p class="mt-1 leading-7 text-slate-700">Tampilkan produk, layanan, atau keduanya di website usaha Anda.</p>
                            </div>
                        </li>
                        <li class="grid gap-2 py-5 sm:grid-cols-[3.5rem_1fr] sm:gap-5">
                            <span class="text-sm font-bold text-indigo-800">02</span>
                            <div>
                                <h3 class="text-lg font-bold text-[#163761]">Terima pesanan atau permintaan</h3>
                                <p class="mt-1 leading-7 text-slate-700">
                                    Fabriku meneruskan pesanan website dan formulir calon pelanggan ke tim Anda.
                                </p>
                            </div>
                        </li>
                        <li class="grid gap-2 py-5 sm:grid-cols-[3.5rem_1fr] sm:gap-5">
                            <span class="text-sm font-bold text-indigo-800">03</span>
                            <div>
                                <h3 class="text-lg font-bold text-[#163761]">Staf menindaklanjuti</h3>
                                <p class="mt-1 leading-7 text-slate-700">
                                    Konfirmasi pesanan dan pembayaran secara langsung, atau lanjutkan permintaan jasa menjadi sales order.
                                </p>
                            </div>
                        </li>
                    </ol>
                </div>
            </section>

            <section id="fitur" class="scroll-mt-6 border-b border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-8 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[0.72fr_1.28fr] lg:gap-20 lg:px-10">
                    <div>
                        <p class="text-sm font-bold text-indigo-800">Kerja harian</p>
                        <h2 class="mt-3 max-w-md text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">
                            Bahan, produksi, dan penjualan tercatat di tempat yang sama.
                        </h2>
                    </div>

                    <div class="border-t border-slate-300">
                        <article class="grid gap-2 border-b border-slate-300 py-5 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <h3 class="font-bold text-[#163761]">Bahan baku</h3>
                            <p class="leading-7 text-slate-700">Catat bahan masuk, pemakaian, batch, dan lokasi penyimpanan.</p>
                        </article>
                        <article class="grid gap-2 border-b border-slate-300 py-5 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <h3 class="font-bold text-[#163761]">Produksi</h3>
                            <p class="leading-7 text-slate-700">Atur kebutuhan produksi internal maupun pengerjaan oleh mitra.</p>
                        </article>
                        <article class="grid gap-2 border-b border-slate-300 py-5 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <h3 class="font-bold text-[#163761]">Penjualan</h3>
                            <p class="leading-7 text-slate-700">Kelola sales order dan tindak lanjut pesanan dari website.</p>
                        </article>
                        <article class="grid gap-2 border-b border-slate-300 py-5 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <h3 class="font-bold text-[#163761]">Laporan</h3>
                            <p class="leading-7 text-slate-700">Baca ringkasan usaha dan unduh laporan Excel atau PDF.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="website" class="scroll-mt-6 border-b border-slate-200 bg-[#f5f7fa]">
                <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20 lg:px-10">
                    <div class="grid gap-5 lg:grid-cols-[0.85fr_1.15fr] lg:items-end lg:gap-16">
                        <div>
                            <p class="text-sm font-bold text-indigo-800">Website usaha</p>
                            <h2 class="mt-3 max-w-xl text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">
                                Satu website, dua cara pelanggan mulai bertransaksi.
                            </h2>
                        </div>
                        <p class="max-w-2xl leading-7 text-slate-700">
                            Toko produk perlu katalog dan stok. Usaha jasa perlu penjelasan layanan dan formulir permintaan. Fabriku mendukung kedua
                            kebutuhan itu.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-4 lg:grid-cols-[1.15fr_0.85fr]">
                        <article class="rounded-2xl bg-[#163761] p-6 text-white sm:p-8">
                            <p class="text-sm font-bold text-cyan-200">Untuk usaha produk</p>
                            <h3 class="mt-3 max-w-xl text-2xl leading-tight font-black sm:text-3xl">Tampilkan katalog yang terhubung ke stok.</h3>
                            <p class="mt-4 max-w-2xl leading-7 text-slate-100">
                                Pembeli mengirim pesanan lewat website. Tim Anda menerima informasi pesanan dan mengonfirmasi jumlah, ongkir, serta
                                pembayaran sebelum transaksi diselesaikan.
                            </p>
                            <p class="mt-7 border-t border-white/20 pt-5 text-sm font-semibold text-cyan-100">
                                Cocok untuk retail, garment, makanan, kerajinan, kosmetik, dan produksi rumahan.
                            </p>
                        </article>

                        <article class="flex flex-col justify-between rounded-2xl border border-slate-300 bg-white p-6 sm:p-8">
                            <div>
                                <p class="text-sm font-bold text-indigo-800">Untuk usaha jasa</p>
                                <h3 class="mt-3 text-2xl leading-tight font-black text-[#163761]">
                                    Terangkan layanan sebelum pelanggan menghubungi Anda.
                                </h3>
                                <p class="mt-4 leading-7 text-slate-700">
                                    Tampilkan layanan, cara kerja, dan informasi usaha. Permintaan pelanggan masuk ke Fabriku agar staf bisa
                                    menindaklanjuti dan mencatat penjualannya.
                                </p>
                            </div>
                            <a
                                href="#desain"
                                class="mt-7 inline-flex min-h-11 w-fit items-center rounded-md px-1 text-sm font-bold text-indigo-800 underline decoration-indigo-300 underline-offset-4 hover:text-indigo-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                            >
                                Lihat pilihan desain website
                            </a>
                        </article>
                    </div>
                </div>
            </section>

            <section id="desain" class="scroll-mt-6 bg-[#102b50] text-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[0.85fr_1.15fr] lg:gap-20 lg:px-10">
                    <div>
                        <p class="text-sm font-bold text-cyan-200">Mulai dari template atau brief</p>
                        <h2 class="mt-3 max-w-lg text-3xl leading-tight font-black tracking-[-0.04em] sm:text-4xl">
                            Buat tampilan website tanpa memulai dari halaman kosong.
                        </h2>
                        <p class="mt-5 max-w-xl leading-7 text-slate-100">
                            Pilih template katalog, atau jelaskan desain yang Anda inginkan melalui wizard khusus Fabriku. Hasilnya dapat dibawa ke
                            Fabriku sebagai draf untuk dilanjutkan sebelum diterbitkan.
                        </p>
                    </div>

                    <div class="space-y-6 border-t border-white/25 pt-5">
                        <div class="grid gap-2 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <p class="font-bold text-cyan-100">Halaman utama</p>
                            <p class="leading-7 text-slate-100">Tampilkan identitas usaha, katalog produk, layanan, dan cara menghubungi Anda.</p>
                        </div>
                        <div class="grid gap-2 border-t border-white/20 pt-5 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <p class="font-bold text-cyan-100">Halaman tambahan</p>
                            <p class="leading-7 text-slate-100">
                                Buat halaman berisi informasi lain dan tampilkan tautannya di footer website, misalnya kebijakan pengiriman atau
                                panduan pemesanan.
                            </p>
                        </div>
                        <div class="grid gap-2 border-t border-white/20 pt-5 sm:grid-cols-[10rem_1fr] sm:gap-6">
                            <p class="font-bold text-cyan-100">Sebelum diterbitkan</p>
                            <p class="leading-7 text-slate-100">
                                Periksa judul, ringkasan Google, dan isi halaman di Fabriku. Kredit desain kustom terpisah dari langganan Fabriku.
                            </p>
                        </div>
                        <p class="border-t border-white/20 pt-5 text-sm leading-6 text-slate-200">
                            Mulai wizard desain dari area Website di Fabriku, lalu kirim hasilnya kembali sebagai draf. Powered by
                            <span class="font-bold text-white">SatsetUI</span>.
                        </p>
                    </div>
                </div>
            </section>

            <section id="harga" class="scroll-mt-6 border-t border-slate-200 bg-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:gap-20 lg:px-10">
                    <div>
                        <p class="text-sm font-bold text-indigo-800">Langganan Fabriku</p>
                        <h2 class="mt-3 max-w-xl text-3xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-4xl">
                            Operasional dan website usaha dalam satu akun.
                        </h2>
                        <p class="mt-5 max-w-xl leading-7 text-slate-700">
                            Mulai dengan masa uji coba. Setelahnya, pilih periode langganan yang sesuai dengan usaha Anda.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-[#f5f7fa] p-6 sm:p-8">
                        <p class="text-sm font-bold text-indigo-800">Fabriku Core</p>
                        <div class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-1">
                            <p class="text-4xl font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">
                                {{ formatCurrency(props.settings.membership_price_monthly) }}
                            </p>
                            <span class="pb-1 text-sm text-slate-700">per bulan</span>
                        </div>
                        <p class="mt-2 text-sm text-slate-700">Pilihan tahunan {{ formatCurrency(props.settings.membership_price_yearly) }}.</p>

                        <ul class="my-6 grid gap-3 border-y border-slate-300 py-5 sm:grid-cols-2">
                            <li v-for="feature in includedFeatures" :key="feature" class="text-sm leading-6 font-semibold text-[#163761]">
                                {{ feature }}
                            </li>
                        </ul>

                        <p class="mb-5 text-sm leading-6 text-slate-700">
                            Uji coba berlangsung 30 hari. Kredit untuk desain kustom tidak termasuk dalam langganan.
                        </p>
                        <Link
                            href="/register"
                            class="inline-flex min-h-12 w-full items-center justify-center rounded-md bg-indigo-700 px-6 text-sm font-bold text-white hover:bg-indigo-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-700"
                        >
                            Buat akun Fabriku
                        </Link>
                    </div>
                </div>
            </section>

            <FAQ />
        </div>
    </PublicLayout>
</template>
