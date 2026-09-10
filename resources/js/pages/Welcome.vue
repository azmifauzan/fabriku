<script setup lang="ts">
import FAQ from '@/components/Landing/FAQ.vue';
import SeoHead from '@/components/SeoHead.vue';
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

const description = 'Fabriku membantu UMKM mengelola bahan baku, produksi, stok, penjualan, dan laporan dalam satu aplikasi berbasis web.';

const ogImage = computed(() => new URL('/images/fabriku-word.png', props.canonical ?? 'https://fabriku.web.id').toString());

const jsonLd = computed(() => {
    const origin = props.canonical ?? 'https://fabriku.web.id';

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

const modules = [
    {
        title: 'Bahan baku',
        copy: 'Catat penerimaan supplier, batch, tanggal kedaluwarsa, dan posisi stok sejak barang datang.',
    },
    {
        title: 'Produksi',
        copy: 'Kelola resep atau BOM, persiapan, produksi internal maupun outsourcing, dan quality control.',
    },
    {
        title: 'Stok & penjualan',
        copy: 'Pantau barang jadi, lokasi penyimpanan, perpindahan stok, pesanan, dan pembayaran.',
    },
    {
        title: 'Laporan',
        copy: 'Lihat ringkasan operasional dan unduh laporan Excel atau PDF dari data yang sama.',
    },
];

const includedFeatures = ['Dashboard & analytics', 'Bahan baku & inventory', 'Produksi & outsourcing', 'Penjualan & sales order', 'Export Excel/PDF'];
</script>

<template>
    <SeoHead
        title="Fabriku | Aplikasi Produksi dan Stok untuk UMKM"
        :description="description"
        :canonical="canonical"
        :og-image="ogImage"
        :json-ld="jsonLd"
    />

    <div class="min-h-screen overflow-x-clip bg-slate-50 font-sans text-slate-900 selection:bg-indigo-600 selection:text-white">
        <a
            href="#main-content"
            class="sr-only z-[100] rounded-md bg-white px-4 py-3 font-bold text-slate-900 focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:outline-2 focus:outline-offset-2 focus:outline-indigo-600"
        >
            Lewati ke konten utama
        </a>

        <nav class="border-b border-slate-200 bg-white" aria-label="Navigasi utama">
            <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-4 px-5 sm:px-8 lg:px-10">
                <a
                    href="#top"
                    aria-label="Fabriku, kembali ke atas"
                    class="flex min-h-11 shrink-0 items-center gap-2 rounded-md focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                >
                    <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-9 w-14 shrink-0 object-contain" />
                    <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 w-[92px] shrink-0 object-contain object-left" />
                </a>

                <div class="hidden items-center gap-7 text-sm font-semibold lg:flex">
                    <a href="#cara-kerja" class="rounded-sm px-1 py-3 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                        >Cara kerja</a
                    >
                    <a href="#fitur" class="rounded-sm px-1 py-3 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                        >Fitur</a
                    >
                    <a href="#harga" class="rounded-sm px-1 py-3 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                        >Harga</a
                    >
                    <a href="#faq" class="rounded-sm px-1 py-3 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600">FAQ</a>
                    <Link href="/blog" class="rounded-sm px-1 py-3 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                        >Blog</Link
                    >
                </div>

                <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                    <Link
                        href="/login"
                        class="inline-flex min-h-11 items-center rounded-md px-3 text-sm font-semibold hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:px-4"
                    >
                        Masuk
                    </Link>
                    <Link
                        href="/register"
                        class="inline-flex min-h-11 items-center rounded-md bg-indigo-600 px-4 text-sm font-bold text-white hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:px-5"
                    >
                        Coba gratis
                    </Link>
                </div>
            </div>
        </nav>

        <main id="main-content">
            <section id="top" class="border-b border-slate-200 bg-white">
                <div
                    class="mx-auto grid max-w-7xl gap-12 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[1.08fr_0.92fr] lg:items-center lg:gap-20 lg:px-10 lg:py-28"
                >
                    <div>
                        <p class="mb-5 text-sm font-bold text-indigo-700">Aplikasi operasional untuk UMKM Indonesia</p>
                        <h1 class="max-w-3xl text-[clamp(2.8rem,7vw,5.6rem)] leading-[0.98] font-black tracking-[-0.055em] text-[#163761]">
                            Kelola produksi, stok, dan penjualan dalam <span class="text-indigo-600">satu alur.</span>
                        </h1>
                        <p class="mt-7 max-w-2xl text-lg leading-8 text-slate-600 sm:text-xl">
                            Fabriku menyatukan pekerjaan sejak bahan datang sampai laporan selesai, sehingga pemilik dan tim bekerja dari data yang
                            sama.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row sm:items-center">
                            <Link
                                href="/register"
                                class="inline-flex min-h-12 items-center justify-center rounded-md bg-indigo-600 px-6 font-bold text-white hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            >
                                Coba Fabriku 30 hari
                            </Link>
                            <a
                                href="#fitur"
                                class="inline-flex min-h-12 items-center justify-center rounded-md px-6 font-bold text-[#163761] hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                            >
                                Lihat fitur utama
                            </a>
                        </div>

                        <p class="mt-7 max-w-2xl text-sm leading-6 text-slate-500">
                            Cocok untuk retail, garment, makanan, kerajinan, kosmetik, produksi rumahan, dan jasa.
                        </p>
                    </div>

                    <figure
                        id="cara-kerja"
                        class="relative scroll-mt-6 rounded-xl border border-slate-200 bg-slate-50 p-5 shadow-[8px_8px_0_#4f46e5] sm:p-7"
                        aria-labelledby="workflow-title"
                    >
                        <div class="mb-6 flex items-center justify-between gap-4 border-b border-slate-200 pb-5">
                            <div>
                                <p class="text-sm font-semibold text-indigo-700">Alur kerja Fabriku</p>
                                <h2 id="workflow-title" class="mt-1 text-xl font-black text-[#163761]">Satu data, diteruskan ke proses berikutnya</h2>
                            </div>
                            <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-10 w-14 object-contain" />
                        </div>

                        <ol class="space-y-2">
                            <li
                                v-for="(module, index) in modules"
                                :key="module.title"
                                class="grid grid-cols-[2rem_1fr] items-center gap-3 rounded-lg bg-white px-4 py-4"
                            >
                                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-xs font-black text-indigo-700">
                                    {{ String(index + 1).padStart(2, '0') }}
                                </span>
                                <span class="font-bold text-[#163761]">{{ module.title }}</span>
                            </li>
                        </ol>
                    </figure>
                </div>
            </section>

            <section id="fitur" class="scroll-mt-6 border-b border-slate-200 bg-slate-50">
                <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24 lg:px-10">
                    <div class="grid gap-6 lg:grid-cols-[0.75fr_1.25fr] lg:gap-20">
                        <div>
                            <p class="text-sm font-bold text-indigo-700">Fitur utama</p>
                            <h2 class="mt-3 max-w-md text-4xl leading-tight font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">
                                Yang dibutuhkan untuk menjalankan operasional harian.
                            </h2>
                        </div>

                        <div class="border-t border-slate-300">
                            <article
                                v-for="(module, index) in modules"
                                :key="module.title"
                                class="grid gap-3 border-b border-slate-300 py-7 sm:grid-cols-[3rem_12rem_1fr] sm:gap-5"
                            >
                                <span class="text-sm font-bold text-indigo-700">{{ String(index + 1).padStart(2, '0') }}</span>
                                <h3 class="text-xl font-black text-[#163761]">{{ module.title }}</h3>
                                <p class="max-w-xl leading-7 text-slate-600">{{ module.copy }}</p>
                            </article>
                        </div>
                    </div>
                </div>
            </section>

            <section id="harga" class="scroll-mt-6 bg-[#102b50] text-white">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[0.9fr_1.1fr] lg:items-center lg:gap-20 lg:px-10">
                    <div>
                        <p class="text-sm font-bold text-cyan-200">Harga sederhana</p>
                        <h2 class="mt-3 max-w-xl text-4xl leading-tight font-black tracking-[-0.04em] sm:text-5xl">
                            Gunakan semua fitur selama 30 hari.
                        </h2>
                        <p class="mt-5 max-w-xl text-lg leading-8 text-slate-200">
                            Tidak perlu kartu kredit. Setelah masa coba selesai, data tetap bisa dibaca sampai Anda memperpanjang akses.
                        </p>
                    </div>

                    <div class="rounded-xl bg-white p-6 text-slate-900 sm:p-8">
                        <p class="text-sm font-bold text-indigo-700">Full member</p>
                        <div class="mt-3 flex flex-wrap items-end gap-x-3 gap-y-1">
                            <p class="text-4xl font-black tracking-[-0.04em] text-[#163761] sm:text-5xl">
                                {{ formatCurrency(props.settings.membership_price_monthly) }}
                            </p>
                            <span class="pb-1 text-slate-500">per bulan</span>
                        </div>
                        <p class="mt-3 text-sm text-slate-500">Paket tahunan {{ formatCurrency(props.settings.membership_price_yearly) }}.</p>

                        <ul class="my-7 grid gap-3 border-y border-slate-200 py-6 sm:grid-cols-2">
                            <li v-for="feature in includedFeatures" :key="feature" class="flex gap-3 text-sm font-semibold">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-sm bg-indigo-600" aria-hidden="true"></span>
                                {{ feature }}
                            </li>
                        </ul>

                        <Link
                            href="/register"
                            class="inline-flex min-h-12 w-full items-center justify-center rounded-md bg-indigo-600 px-6 font-bold text-white hover:bg-indigo-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Buat akun dan coba gratis
                        </Link>
                    </div>
                </div>
            </section>

            <FAQ />
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto max-w-7xl px-5 py-10 sm:px-8 lg:px-10">
                <div class="flex flex-col gap-7 md:flex-row md:items-end md:justify-between">
                    <div>
                        <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-6 w-[120px] object-contain object-left" />
                        <p class="mt-4 max-w-sm text-sm leading-6 text-slate-500">Aplikasi produksi, stok, dan penjualan untuk operasional UMKM.</p>
                    </div>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm font-semibold">
                        <a href="#fitur" class="rounded-sm py-2 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                            >Fitur</a
                        >
                        <a href="#harga" class="rounded-sm py-2 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                            >Harga</a
                        >
                        <a href="#faq" class="rounded-sm py-2 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600">FAQ</a>
                        <Link href="/blog" class="rounded-sm py-2 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                            >Blog</Link
                        >
                        <Link href="/privasi" class="rounded-sm py-2 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                            >Privasi</Link
                        >
                        <Link
                            href="/syarat-ketentuan"
                            class="rounded-sm py-2 hover:text-indigo-700 focus-visible:outline-2 focus-visible:outline-indigo-600"
                            >Syarat & Ketentuan</Link
                        >
                    </div>
                </div>

                <div class="mt-8 flex flex-col gap-2 border-t border-slate-200 pt-6 text-xs text-slate-500 sm:flex-row sm:justify-between">
                    <p>© 2026 Fabriku. Dibuat untuk UMKM Indonesia.</p>
                    <p>
                        Design by
                        <a
                            href="https://satsetui.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold hover:text-indigo-700 hover:underline"
                            >SatsetUI</a
                        >
                        · Managed by
                        <a
                            href="https://satsetops.com"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-semibold hover:text-indigo-700 hover:underline"
                            >SatsetOps</a
                        >
                    </p>
                </div>
            </div>
        </footer>
    </div>
</template>
