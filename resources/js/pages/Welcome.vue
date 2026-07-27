<script setup lang="ts">
import FAQ from '@/components/Landing/FAQ.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowDown, ArrowRight, BarChart3, Boxes, Check, CircleDollarSign, Factory, PackageCheck, ScanLine, Truck } from 'lucide-vue-next';

type PlanFeature = {
    name: string;
    free: boolean | string;
    full: boolean | string;
};

const props = withDefaults(
    defineProps<{
        settings?: {
            membership_price_monthly: number;
            membership_price_yearly: number;
        };
    }>(),
    {
        settings: () => ({
            membership_price_monthly: 25000,
            membership_price_yearly: 250000,
        }),
    },
);

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

const modules = [
    {
        number: '01',
        title: 'Bahan masuk, langsung tercatat.',
        copy: 'Penerimaan supplier, batch, tanggal kedaluwarsa, dan posisi stok—rapi sejak pintu gudang.',
        icon: Boxes,
        tone: 'bg-indigo-100',
    },
    {
        number: '02',
        title: 'Produksi bergerak, tim tahu.',
        copy: 'Resep atau BOM, persiapan, produksi internal maupun outsourcing, sampai quality control.',
        icon: Factory,
        tone: 'bg-cyan-100',
    },
    {
        number: '03',
        title: 'Barang jadi tidak menghilang.',
        copy: 'Lokasi rak, FIFO/FEFO, transfer stok, dan peringatan stok rendah dalam satu pandangan.',
        icon: PackageCheck,
        tone: 'bg-emerald-100',
    },
    {
        number: '04',
        title: 'Angka bicara apa adanya.',
        copy: 'Penjualan multi-channel, pembayaran, dashboard KPI, dan laporan yang siap diunduh.',
        icon: BarChart3,
        tone: 'bg-purple-100',
    },
];

const features: PlanFeature[] = [
    { name: 'Dashboard & analytics', free: true, full: true },
    { name: 'Bahan baku & inventory', free: true, full: true },
    { name: 'Produksi & outsourcing', free: true, full: true },
    { name: 'Penjualan & sales order', free: true, full: true },
    { name: 'Export Excel/PDF', free: true, full: true },
    { name: 'Masa aktif', free: '30 hari', full: 'Sesuai paket' },
    { name: 'Setelah masa aktif', free: 'Read-only', full: 'Perpanjang' },
];

const industries = ['Retail', 'Garment', 'Makanan', 'Kerajinan', 'Kosmetik', 'Produksi rumahan', 'Jasa'];
</script>

<template>
    <Head title="Fabriku — Operasional UMKM dalam satu alur" />

    <div class="landing min-h-screen overflow-hidden bg-slate-50 text-gray-800 selection:bg-indigo-600 selection:text-white">
        <nav class="relative z-50 border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-[74px] max-w-[1440px] items-center justify-between px-5 sm:px-8 lg:px-12">
                <a href="#top" aria-label="Fabriku, kembali ke atas" class="flex shrink-0 items-center gap-2 overflow-visible">
                    <img src="/images/fabriku-logo-only.png?v=2" alt="" class="h-9 w-14 shrink-0 object-contain" />
                    <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 w-[92px] shrink-0 object-contain object-left" />
                </a>

                <div class="hidden items-center gap-8 text-[13px] font-bold tracking-[0.12em] uppercase md:flex">
                    <a href="#cara-kerja" class="underline-offset-8 hover:underline">Cara kerja</a>
                    <a href="#fitur" class="underline-offset-8 hover:underline">Modul</a>
                    <a href="#harga" class="underline-offset-8 hover:underline">Harga</a>
                    <a href="#faq" class="underline-offset-8 hover:underline">FAQ</a>
                </div>

                <div class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <Link href="/login" class="hidden px-2 py-2 text-sm font-bold min-[420px]:inline-flex sm:px-4">Masuk</Link>
                    <Link
                        href="/register"
                        class="group inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-bold whitespace-nowrap text-white shadow-sm transition-colors hover:bg-indigo-700 sm:px-5"
                    >
                        Coba gratis
                        <ArrowRight :size="16" class="transition-transform group-hover:translate-x-1" />
                    </Link>
                </div>
            </div>
        </nav>

        <main id="top">
            <section class="relative border-b border-gray-200">
                <div class="blueprint absolute inset-0 opacity-35" aria-hidden="true"></div>
                <div class="relative mx-auto grid max-w-[1440px] lg:min-h-[720px] lg:grid-cols-[1.08fr_0.92fr]">
                    <div class="flex flex-col justify-between border-gray-200 px-5 py-14 sm:px-8 sm:py-20 lg:border-r lg:px-12 lg:py-16">
                        <div>
                            <div class="mb-9 flex items-center gap-3 text-xs font-black tracking-[0.16em] uppercase">
                                <span class="inline-block h-3 w-3 animate-pulse rounded-full bg-indigo-600"></span>
                                Sistem kerja UMKM Indonesia
                            </div>

                            <h1 class="max-w-[820px] text-[clamp(3.8rem,8.4vw,8.2rem)] leading-[0.9] font-black tracking-[-0.06em] uppercase">
                                Bukan cuma
                                <span class="relative inline-block text-indigo-600">
                                    catat.
                                    <svg class="absolute -right-3 -bottom-5 w-[103%]" viewBox="0 0 390 22" fill="none" aria-hidden="true">
                                        <path d="M3 14.5C104 4 239 5 387 11" stroke="currentColor" stroke-width="6" stroke-linecap="round" />
                                    </svg>
                                </span>
                                <br />Bikin alur.
                            </h1>

                            <p class="mt-11 max-w-xl text-lg leading-relaxed font-medium sm:text-xl">
                                Fabriku menyambungkan bahan, produksi, stok, penjualan, dan laporan. Jadi Anda tahu apa yang sedang dikerjakan—dan apa
                                yang perlu dibereskan berikutnya.
                            </p>
                        </div>

                        <div class="mt-12 flex flex-col gap-7 sm:flex-row sm:items-center lg:mt-16">
                            <Link
                                href="/register"
                                class="group inline-flex w-fit items-center gap-4 rounded-lg bg-indigo-600 px-6 py-4 text-base font-black text-white shadow-md transition-colors hover:bg-indigo-700"
                            >
                                Mulai 30 hari gratis
                                <ArrowRight :size="20" class="transition-transform group-hover:translate-x-1" />
                            </Link>
                            <a href="#cara-kerja" class="flex w-fit items-center gap-2 text-sm font-black tracking-wider uppercase">
                                Lihat cara kerjanya
                                <ArrowDown :size="16" />
                            </a>
                        </div>
                    </div>

                    <div class="relative flex items-center justify-center bg-gray-900 p-5 sm:p-10 lg:p-12">
                        <div class="absolute top-5 right-5 font-mono text-[10px] tracking-[0.2em] text-white/40 uppercase sm:top-8 sm:right-8">
                            Live floor / 08:42
                        </div>

                        <div class="relative w-full max-w-[560px] rotate-[-1.5deg] border border-gray-300 bg-white shadow-[12px_12px_0_#4f46e5]">
                            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 sm:px-6">
                                <div>
                                    <p class="text-[10px] font-black tracking-[0.18em] text-slate-500 uppercase">Hari ini di workshop</p>
                                    <p class="mt-1 text-xl font-black tracking-tight">Ringkasan Operasional</p>
                                </div>
                                <ScanLine :size="28" stroke-width="1.5" />
                            </div>

                            <div class="grid grid-cols-2 border-b border-gray-200 sm:grid-cols-3">
                                <div class="border-r border-gray-200 p-4 sm:p-5">
                                    <p class="text-[10px] font-bold tracking-widest uppercase">Antrean</p>
                                    <p class="mt-3 text-4xl font-black">12</p>
                                    <p class="mt-1 text-xs text-slate-500">pesanan</p>
                                </div>
                                <div class="border-gray-200 p-4 sm:border-r sm:p-5">
                                    <p class="text-[10px] font-bold tracking-widest uppercase">Produksi</p>
                                    <p class="mt-3 text-4xl font-black">08</p>
                                    <p class="mt-1 text-xs text-slate-500">sedang jalan</p>
                                </div>
                                <div class="col-span-2 border-t border-gray-200 bg-indigo-50 p-4 sm:col-span-1 sm:border-t-0 sm:p-5">
                                    <p class="text-[10px] font-bold tracking-widest uppercase">Selesai</p>
                                    <p class="mt-3 text-4xl font-black">31</p>
                                    <p class="mt-1 text-xs text-slate-500">unit hari ini</p>
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                <div class="mb-4 flex items-center justify-between">
                                    <p class="text-xs font-black tracking-widest uppercase">Alur aktif</p>
                                    <span class="rounded-full bg-indigo-600 px-3 py-1 text-[9px] font-black tracking-widest text-white uppercase"
                                        >Live</span
                                    >
                                </div>
                                <div class="space-y-3 font-mono text-[11px] sm:text-xs">
                                    <div class="grid grid-cols-[70px_1fr_auto] items-center gap-3 border border-gray-200 bg-white px-3 py-3">
                                        <span>#PRD-221</span>
                                        <span class="font-sans font-bold">Kemeja Linen / 24 pcs</span>
                                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span>
                                    </div>
                                    <div class="grid grid-cols-[70px_1fr_auto] items-center gap-3 border border-gray-200 bg-white px-3 py-3">
                                        <span>#PRD-218</span>
                                        <span class="font-sans font-bold">Tote Bag / QC</span>
                                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-400 ring-1 ring-emerald-600"></span>
                                    </div>
                                    <div class="grid grid-cols-[70px_1fr_auto] items-center gap-3 border border-gray-200 bg-white px-3 py-3">
                                        <span>#SO-1094</span>
                                        <span class="font-sans font-bold">Siap dikirim</span>
                                        <Truck :size="17" />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="absolute -top-5 -left-4 rotate-[-5deg] border border-indigo-200 bg-indigo-100 px-3 py-2 text-[10px] font-black tracking-widest text-indigo-700 uppercase"
                            >
                                Semua tercatat
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="ticker border-b border-indigo-100 bg-indigo-50 py-3 text-indigo-700" aria-label="Kategori bisnis yang didukung">
                <div class="ticker-track flex w-max items-center gap-7 font-black tracking-[0.14em] whitespace-nowrap uppercase">
                    <template v-for="repeat in 2" :key="repeat">
                        <template v-for="industry in industries" :key="`${repeat}-${industry}`">
                            <span>{{ industry }}</span
                            ><span aria-hidden="true">✦</span>
                        </template>
                    </template>
                </div>
            </div>

            <section id="cara-kerja" class="border-b border-gray-200 bg-white">
                <div class="mx-auto grid max-w-[1440px] lg:grid-cols-[0.65fr_1.35fr]">
                    <div class="border-gray-200 bg-indigo-50 px-5 py-16 sm:px-8 lg:border-r lg:px-12 lg:py-24">
                        <p class="mb-5 text-xs font-black tracking-[0.18em] uppercase">Satu jalur, bukan lima aplikasi</p>
                        <h2 class="text-5xl leading-[0.92] font-black tracking-[-0.055em] uppercase sm:text-7xl">
                            Dari bahan datang sampai uang pulang.
                        </h2>
                    </div>

                    <div class="grid sm:grid-cols-2">
                        <div class="border-b border-gray-200 p-6 sm:border-r sm:p-8">
                            <span class="font-mono text-xs">LANGKAH 01</span>
                            <Boxes class="my-8" :size="42" stroke-width="1.5" />
                            <h3 class="text-2xl font-black uppercase">Catat bahan</h3>
                            <p class="mt-3 max-w-sm font-medium">Stok awal jelas. Batch dan supplier tidak lagi tersebar di chat atau buku.</p>
                        </div>
                        <div class="border-b border-gray-200 p-6 sm:p-8">
                            <span class="font-mono text-xs">LANGKAH 02</span>
                            <Factory class="my-8" :size="42" stroke-width="1.5" />
                            <h3 class="text-2xl font-black uppercase">Jalankan produksi</h3>
                            <p class="mt-3 max-w-sm font-medium">Bahan terpakai, progres, dan hasil produksi bergerak dalam alur yang sama.</p>
                        </div>
                        <div class="border-b border-gray-200 p-6 sm:border-r sm:border-b-0 sm:p-8">
                            <span class="font-mono text-xs">LANGKAH 03</span>
                            <PackageCheck class="my-8" :size="42" stroke-width="1.5" />
                            <h3 class="text-2xl font-black uppercase">Kelola barang jadi</h3>
                            <p class="mt-3 max-w-sm font-medium">Tahu jumlah dan lokasinya tanpa perlu hitung ulang setiap kali ada pesanan.</p>
                        </div>
                        <div class="p-6 sm:p-8">
                            <span class="font-mono text-xs">LANGKAH 04</span>
                            <CircleDollarSign class="my-8" :size="42" stroke-width="1.5" />
                            <h3 class="text-2xl font-black uppercase">Jual & evaluasi</h3>
                            <p class="mt-3 max-w-sm font-medium">Pesanan dan pembayaran masuk; laporan memberi arah untuk keputusan berikutnya.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="fitur" class="border-b border-gray-200 bg-slate-50 py-20 sm:py-28">
                <div class="mx-auto max-w-[1440px] px-5 sm:px-8 lg:px-12">
                    <div class="mb-14 grid gap-8 lg:grid-cols-[1fr_0.7fr] lg:items-end">
                        <div>
                            <p class="mb-5 text-xs font-black tracking-[0.18em] text-indigo-600 uppercase">Empat modul inti</p>
                            <h2 class="max-w-4xl text-5xl leading-[0.93] font-black tracking-[-0.055em] uppercase sm:text-7xl">
                                Kerja harian,<br />tanpa drama data.
                            </h2>
                        </div>
                        <p class="max-w-xl text-lg leading-relaxed font-medium lg:justify-self-end">
                            Bukan fitur yang dipajang lalu dilupakan. Setiap modul mengikuti urutan kerja bisnis—supaya data masuk sekali dan berguna
                            sampai laporan.
                        </p>
                    </div>

                    <div class="grid border-t border-l border-gray-200 md:grid-cols-2">
                        <article
                            v-for="module in modules"
                            :key="module.number"
                            class="group relative min-h-[360px] overflow-hidden border-r border-b border-gray-200 bg-white p-6 transition-colors hover:bg-indigo-50/40 sm:p-9"
                        >
                            <div class="flex items-start justify-between">
                                <span class="font-mono text-sm font-bold">MOD / {{ module.number }}</span>
                                <span :class="module.tone" class="flex h-14 w-14 items-center justify-center rounded-full border border-gray-300">
                                    <component :is="module.icon" :size="27" stroke-width="1.7" />
                                </span>
                            </div>
                            <div class="mt-20 max-w-xl sm:mt-24">
                                <h3 class="text-3xl leading-tight font-black tracking-[-0.035em] uppercase sm:text-4xl">{{ module.title }}</h3>
                                <p class="mt-4 max-w-lg leading-relaxed font-medium text-slate-600">{{ module.copy }}</p>
                            </div>
                            <span
                                class="absolute -right-2 -bottom-10 text-[9rem] leading-none font-black text-gray-800/[0.035] transition-colors group-hover:text-indigo-600/[0.07]"
                                aria-hidden="true"
                            >
                                {{ module.number }}
                            </span>
                        </article>
                    </div>
                </div>
            </section>

            <section id="harga" class="border-b border-gray-800 bg-gray-900 py-20 text-white sm:py-28">
                <div class="mx-auto max-w-[1200px] px-5 sm:px-8">
                    <div class="mb-14 text-center">
                        <p class="mb-5 text-xs font-black tracking-[0.18em] text-indigo-300 uppercase">Harga terang, tidak pakai teka-teki</p>
                        <h2 class="text-5xl leading-none font-black tracking-[-0.055em] uppercase sm:text-7xl">Coba dulu. Bayar kalau cocok.</h2>
                        <p class="mx-auto mt-6 max-w-2xl text-lg text-white/65">Semua fitur terbuka selama 30 hari. Tanpa kartu kredit.</p>
                    </div>

                    <div class="grid gap-5 lg:grid-cols-2">
                        <article class="flex flex-col rounded-xl border border-white/20 bg-slate-800 p-6 sm:p-9">
                            <div class="flex items-start justify-between gap-4 border-b border-white/20 pb-7">
                                <div>
                                    <p class="text-xs font-black tracking-[0.18em] text-white/50 uppercase">Untuk kenalan</p>
                                    <h3 class="mt-2 text-3xl font-black uppercase">Trial penuh</h3>
                                </div>
                                <span class="border border-white/30 px-3 py-1 font-mono text-xs">30 HARI</span>
                            </div>
                            <p class="mt-8 text-6xl font-black tracking-[-0.06em]">Rp0</p>
                            <p class="mt-2 text-white/55">Akses penuh, lalu otomatis read-only.</p>
                            <ul class="my-9 flex-1 space-y-3">
                                <li v-for="feature in features" :key="feature.name" class="flex items-center gap-3 text-sm">
                                    <Check :size="17" class="text-indigo-300" />
                                    <span>{{ feature.name }}</span>
                                    <span v-if="typeof feature.free === 'string'" class="ml-auto font-mono text-xs text-white/50">{{
                                        feature.free
                                    }}</span>
                                </li>
                            </ul>
                            <Link
                                href="/register"
                                class="flex items-center justify-between rounded-lg border border-white px-5 py-4 font-black uppercase hover:bg-white hover:text-gray-900"
                            >
                                Mulai gratis <ArrowRight :size="20" />
                            </Link>
                        </article>

                        <article
                            class="relative flex flex-col rounded-xl border border-indigo-200 bg-indigo-100 p-6 text-gray-800 shadow-[8px_8px_0_#4f46e5] sm:p-9"
                        >
                            <span
                                class="absolute -top-4 right-5 rounded-full bg-indigo-600 px-4 py-1.5 text-xs font-black tracking-widest text-white uppercase"
                                >Paling masuk akal</span
                            >
                            <div class="flex items-start justify-between gap-4 border-b border-indigo-300 pb-7">
                                <div>
                                    <p class="text-xs font-black tracking-[0.18em] uppercase opacity-60">Untuk jalan terus</p>
                                    <h3 class="mt-2 text-3xl font-black uppercase">Full member</h3>
                                </div>
                                <span class="rounded border border-indigo-300 px-3 py-1 font-mono text-xs">BULANAN</span>
                            </div>
                            <div class="mt-8 flex flex-wrap items-end gap-x-3">
                                <p class="text-5xl font-black tracking-[-0.06em] sm:text-6xl">
                                    {{ formatCurrency(props.settings.membership_price_monthly) }}
                                </p>
                                <span class="mb-2 font-bold opacity-60">/ bulan</span>
                            </div>
                            <p class="mt-2 font-medium">
                                Tahunan {{ formatCurrency(props.settings.membership_price_yearly) }} · hemat
                                {{ formatCurrency(props.settings.membership_price_monthly * 12 - props.settings.membership_price_yearly) }}
                            </p>
                            <ul class="my-9 flex-1 space-y-3">
                                <li v-for="feature in features" :key="feature.name" class="flex items-center gap-3 text-sm font-medium">
                                    <Check :size="17" />
                                    <span>{{ feature.name }}</span>
                                    <span v-if="typeof feature.full === 'string'" class="ml-auto font-mono text-xs opacity-60">{{
                                        feature.full
                                    }}</span>
                                </li>
                            </ul>
                            <Link
                                href="/register"
                                class="flex items-center justify-between rounded-lg bg-indigo-600 px-5 py-4 font-black text-white uppercase hover:bg-indigo-700"
                            >
                                Gunakan Fabriku <ArrowRight :size="20" />
                            </Link>
                        </article>
                    </div>
                </div>
            </section>

            <FAQ />

            <section class="relative border-t border-b border-indigo-100 bg-indigo-50 py-20 sm:py-28">
                <div class="blueprint absolute inset-0 opacity-40" aria-hidden="true"></div>
                <div class="relative mx-auto max-w-[1100px] px-5 text-center sm:px-8">
                    <p class="mb-5 text-xs font-black tracking-[0.18em] uppercase">Mulai dari proses yang paling berantakan</p>
                    <h2 class="text-5xl leading-[0.9] font-black tracking-[-0.06em] uppercase sm:text-8xl">Besok pagi,<br />kerja lebih jelas.</h2>
                    <p class="mx-auto mt-7 max-w-xl text-lg font-medium">
                        Siapkan lima menit. Coba semua fiturnya selama 30 hari. Data tetap bisa dibaca jika Anda belum lanjut.
                    </p>
                    <Link
                        href="/register"
                        class="mt-10 inline-flex items-center gap-4 rounded-lg bg-indigo-600 px-7 py-4 font-black text-white shadow-md transition-colors hover:bg-indigo-700"
                    >
                        Buka workshop digital Anda
                        <ArrowRight :size="20" />
                    </Link>
                </div>
            </section>
        </main>

        <footer class="bg-white">
            <div class="mx-auto grid max-w-[1440px] gap-x-8 gap-y-3 px-5 py-10 sm:px-8 md:grid-cols-3 md:items-end lg:px-12">
                <div>
                    <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-6 w-[120px] object-contain object-left" />
                    <p class="mt-4 max-w-xs text-sm font-medium text-slate-500">Operasional UMKM dalam satu alur yang bisa dipahami seluruh tim.</p>
                </div>
                <div class="flex flex-wrap gap-5 text-sm font-bold md:justify-center">
                    <a href="#fitur" class="hover:underline">Modul</a>
                    <a href="#harga" class="hover:underline">Harga</a>
                    <a href="#faq" class="hover:underline">FAQ</a>
                    <a href="https://github.com/azmifauzan/fabriku" target="_blank" rel="noopener noreferrer" class="hover:underline">GitHub</a>
                </div>
                <p class="text-sm font-medium text-slate-500 md:text-right">© 2026 Fabriku · Dibuat untuk UMKM Indonesia.</p>

                <div class="hidden md:block" aria-hidden="true"></div>
                <div class="flex flex-wrap gap-5 text-sm font-bold md:justify-center">
                    <Link href="/privasi" class="hover:underline">Privasi</Link>
                    <Link href="/syarat-ketentuan" class="hover:underline">Syarat & Ketentuan</Link>
                </div>
                <p class="text-xs font-medium text-slate-400 md:text-right">
                    Design by
                    <a
                        href="https://satsetui.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-slate-500 hover:text-indigo-600 hover:underline"
                        >SatsetUI</a
                    >
                    and Managed by
                    <a
                        href="https://satsetops.com"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-semibold text-slate-500 hover:text-indigo-600 hover:underline"
                        >SatsetOps</a
                    >
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.landing {
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}

.blueprint {
    background-image: linear-gradient(rgba(23, 33, 29, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(23, 33, 29, 0.1) 1px, transparent 1px);
    background-size: 32px 32px;
}

.ticker-track {
    animation: ticker 28s linear infinite;
}

@keyframes ticker {
    to {
        transform: translateX(-50%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ticker-track {
        animation: none;
    }
}
</style>
