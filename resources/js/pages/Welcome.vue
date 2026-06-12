<script setup lang="ts">
import FAQ from '@/components/Landing/FAQ.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Check, ChefHat, Home, Palette, Scissors, Sparkles, Store, Wrench, X } from 'lucide-vue-next';

const props = defineProps({
    canLogin: {
        type: Boolean,
        required: false,
    },
    settings: {
        type: Object,
        default: () => ({
            membership_price_monthly: 25000,
            membership_price_yearly: 250000,
        }),
    },
});

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const features = [
    { name: 'Dashboard & Analytics', free: true, full: true },
    { name: 'Manajemen Bahan Baku', free: true, full: true },
    { name: 'Produksi Internal & Outsourcing', free: true, full: true },
    { name: 'Inventory Gudang', free: true, full: true },
    { name: 'Penjualan & Sales Order', free: true, full: true },
    { name: 'Export Laporan (Excel/PDF)', free: true, full: true },
    { name: 'Laporan Lengkap & Analytics', free: true, full: true },
    { name: 'Masa Aktif Full Access', free: '30 Hari', full: 'Sesuai Paket' },
    { name: 'Setelah Expired', free: 'Read-Only', full: 'Perpanjang' },
];
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <Head title="Fabriku - Platform Manajemen Produksi Multi-Kategori untuk UMKM" />

        <!-- Navigation -->
        <nav class="sticky top-0 z-50 border-b border-gray-100 bg-white/80 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center gap-2">
                        <img src="/images/fabriku-logo-only.png" alt="Fabriku Logo" class="h-8 w-8 object-contain" />
                        <img src="/images/fabriku-word.png" alt="Fabriku" class="h-5 object-contain" />
                    </div>
                    <div class="hidden items-center gap-6 md:flex">
                        <a href="#features" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"> Fitur </a>
                        <a href="#pricing" class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"> Harga </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="px-4 py-2 text-sm font-medium text-gray-600 transition-colors hover:text-gray-900"> Masuk </Link>
                        <Link
                            href="/register"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
                        >
                            Coba Gratis
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="pt-16 pb-24 lg:pt-24 lg:pb-32">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-4xl text-center">
                    <!-- Badge -->
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-indigo-100 px-4 py-2 text-sm font-medium text-indigo-700">
                        <span class="h-2 w-2 rounded-full bg-green-500"></span>
                        Gratis 30 Hari Trial
                    </div>

                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                        Kelola Bisnis UMKM
                        <span class="block text-indigo-600">Multi-Kategori, Satu Platform</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-gray-600 sm:text-xl">
                        Platform SaaS untuk UMKM Indonesia — dari toko sederhana yang butuh stok & kasir, hingga bisnis produksi lengkap dari bahan
                        baku hingga penjualan.
                    </p>

                    <!-- Category Badges -->
                    <div class="mt-8 flex flex-wrap justify-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <Store :size="14" class="text-gray-400" /> Toko / Retail
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <Scissors :size="14" class="text-gray-400" /> Garment
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <ChefHat :size="14" class="text-gray-400" /> Makanan & Kue
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <Palette :size="14" class="text-gray-400" /> Kerajinan & Craft
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <Sparkles :size="14" class="text-gray-400" /> Kosmetik
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <Home :size="14" class="text-gray-400" /> Produksi Rumahan
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 shadow-sm"
                        >
                            <Wrench :size="14" class="text-gray-400" /> Jasa & Layanan
                        </span>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="mt-10 flex flex-col justify-center gap-4 sm:flex-row">
                        <Link
                            href="/register"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-8 py-4 text-base font-semibold text-white transition-colors hover:bg-indigo-700"
                        >
                            Mulai Gratis 30 Hari
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                        <a
                            href="#pricing"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gray-100 px-8 py-4 text-base font-semibold text-gray-700 transition-colors hover:bg-gray-200"
                        >
                            Lihat Harga
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Tanpa kartu kredit</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Full akses 30 hari</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setup 5 menit</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <span class="text-sm font-semibold tracking-wide text-indigo-600 uppercase">Fitur Lengkap</span>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Semua yang Anda Butuhkan</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                        Dari bahan baku hingga penjualan, kelola seluruh proses bisnis dengan terminologi yang disesuaikan per kategori
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="rounded-2xl bg-gray-50 p-6 transition-colors hover:bg-gray-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-500">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Manajemen Bahan Baku</h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Catat penerimaan bahan dari supplier dengan atribut dinamis, tracking batch/expired date, dan monitor stok real-time.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="rounded-2xl bg-gray-50 p-6 transition-colors hover:bg-gray-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-green-500">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Pattern/Resep & Persiapan</h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Kelola pattern/resep dengan BOM, proses persiapan, hitung efisiensi, dan minimalisir waste produksi.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="rounded-2xl bg-gray-50 p-6 transition-colors hover:bg-gray-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Manajemen Produksi</h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Kelola produksi internal maupun outsourcing dengan quality control dan batch tracking terintegrasi.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="rounded-2xl bg-gray-50 p-6 transition-colors hover:bg-gray-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-500">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Inventory Gudang</h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Kelola produk jadi dengan lokasi rak, FIFO/FEFO tracking, expired date monitoring, dan alert low stock.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="rounded-2xl bg-gray-50 p-6 transition-colors hover:bg-gray-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-red-500">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Penjualan Multi-Channel</h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Catat penjualan dari berbagai channel (offline, online, reseller) dengan payment tracking terintegrasi.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="rounded-2xl bg-gray-50 p-6 transition-colors hover:bg-gray-100">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-pink-500">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900">Laporan & Analytics</h3>
                        <p class="text-sm leading-relaxed text-gray-600">
                            Dashboard KPI, laporan produksi, inventory, sales, hingga profit & loss statement lengkap.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="bg-gradient-to-b from-white to-indigo-50 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <span class="text-sm font-semibold tracking-wide text-indigo-600 uppercase">Harga</span>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Pilih Paket yang Sesuai</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                        Mulai dengan Free Trial 30 hari dengan full akses semua fitur, atau langsung upgrade ke Full Member untuk akses tanpa batas
                        waktu
                    </p>
                </div>

                <div class="mx-auto grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Free Trial -->
                    <div class="relative rounded-2xl border border-gray-200 bg-white p-6 shadow-lg">
                        <div class="mb-6 text-center">
                            <h3 class="mb-2 text-xl font-bold text-gray-900">Free Trial</h3>
                            <div class="text-3xl font-extrabold text-gray-900">
                                Rp 0
                                <span class="text-base font-normal text-gray-500">/30 hari</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Full akses semua fitur</p>
                            <p class="mt-1 text-xs font-medium text-amber-600">Setelah expired: Read-only access</p>
                        </div>

                        <ul class="mb-6 space-y-2.5">
                            <li v-for="feature in features" :key="feature.name" class="flex items-start gap-2.5">
                                <component
                                    :is="feature.free === true ? Check : feature.free ? Check : X"
                                    :class="feature.free === true ? 'text-green-500' : feature.free ? 'text-green-500' : 'text-gray-300'"
                                    class="mt-0.5 h-4 w-4 flex-shrink-0"
                                />
                                <span :class="feature.free ? 'text-sm text-gray-700' : 'text-sm text-gray-400'">
                                    {{ feature.name }}
                                    <span v-if="typeof feature.free === 'string'" class="font-medium text-indigo-600">({{ feature.free }})</span>
                                </span>
                            </li>
                        </ul>

                        <Link
                            href="/register"
                            class="block w-full rounded-xl bg-indigo-50 px-4 py-2.5 text-center font-semibold text-indigo-600 transition-colors hover:bg-indigo-100"
                        >
                            Coba Gratis
                        </Link>
                    </div>

                    <!-- Full Member -->
                    <div class="relative rounded-2xl border-2 border-indigo-200 bg-white p-6 shadow-lg">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700"> POPULER </span>
                        </div>

                        <div class="mb-6 text-center">
                            <h3 class="mb-2 text-xl font-bold text-gray-900">Full Member</h3>
                            <div class="text-3xl font-extrabold text-gray-900">
                                {{ formatCurrency(settings.membership_price_monthly) }}
                                <span class="text-base font-normal text-gray-500">/bulan</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                atau {{ formatCurrency(settings.membership_price_yearly) }}/tahun
                                <span class="block text-xs font-medium text-green-600">
                                    (Hemat {{ formatCurrency(settings.membership_price_monthly * 12 - settings.membership_price_yearly) }})
                                </span>
                            </p>
                        </div>

                        <ul class="mb-6 space-y-2.5">
                            <li v-for="feature in features" :key="feature.name" class="flex items-start gap-2.5">
                                <Check class="mt-0.5 h-4 w-4 flex-shrink-0 text-green-500" />
                                <span class="text-sm text-gray-700">
                                    {{ feature.name }}
                                    <span v-if="typeof feature.full === 'string'" class="font-medium text-indigo-600">({{ feature.full }})</span>
                                </span>
                            </li>
                        </ul>

                        <Link
                            href="/register"
                            class="block w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-center font-semibold text-white transition-colors hover:bg-indigo-700"
                        >
                            Mulai Sekarang
                        </Link>
                    </div>
                </div>

                <!-- Feature Comparison Table (Mobile Hidden) -->
                <div class="mt-16 hidden lg:block">
                    <h3 class="mb-8 text-center text-xl font-bold text-gray-900">Perbandingan Fitur Lengkap</h3>
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Fitur</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Free Trial</th>
                                    <th class="px-6 py-4 text-center text-sm font-semibold text-indigo-600">Full Member</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="feature in features" :key="feature.name" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ feature.name }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <component
                                            :is="feature.free === true ? Check : feature.free ? Check : X"
                                            :class="feature.free === true ? 'text-green-500' : feature.free ? 'text-green-500' : 'text-gray-300'"
                                            class="inline-block h-5 w-5"
                                        />
                                        <span v-if="typeof feature.free === 'string'" class="ml-1 text-sm text-gray-600">{{ feature.free }}</span>
                                    </td>
                                    <td class="bg-indigo-50/50 px-6 py-4 text-center">
                                        <Check class="inline-block h-5 w-5 text-indigo-600" />
                                        <span v-if="typeof feature.full === 'string'" class="ml-1 text-sm text-indigo-600">{{ feature.full }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <FAQ />

        <!-- CTA Section -->
        <section class="py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl bg-indigo-600 px-6 py-16 text-center sm:px-12 sm:py-20">
                    <h2 class="mb-4 text-3xl font-bold text-white sm:text-4xl">Siap untuk meningkatkan bisnis Anda?</h2>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-indigo-100">
                        Mulai trial gratis 30 hari sekarang. Tanpa kartu kredit, akses penuh semua fitur.
                    </p>
                    <Link
                        href="/register"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-8 py-4 text-base font-semibold text-indigo-600 transition-colors hover:bg-indigo-50"
                    >
                        Mulai Gratis Sekarang
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                    <div class="flex items-center gap-2">
                        <img src="/images/fabriku-logo-only.png" alt="Fabriku Logo" class="h-6 w-6 object-contain" />
                        <img src="/images/fabriku-word.png" alt="Fabriku" class="h-4 object-contain" />
                    </div>
                    <div class="flex items-center gap-6">
                        <a
                            href="https://github.com/azmifauzan/fabriku"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 text-gray-500 transition-colors hover:text-gray-900"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    fill-rule="evenodd"
                                    d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                            <span class="text-sm font-medium">GitHub</span>
                        </a>
                        <p class="text-sm text-gray-500">© 2026 Fabriku. Made with ❤️ for Indonesian UMKM.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
