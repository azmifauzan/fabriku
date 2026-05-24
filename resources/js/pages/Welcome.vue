
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import FAQ from '@/components/Landing/FAQ.vue';
import { Check, ChefHat, Home, Palette, Scissors, Sparkles, Store, X } from 'lucide-vue-next';

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
        <nav class="bg-white/80 backdrop-blur-sm border-b border-gray-100 sticky top-0 z-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-lg">F</span>
                        </div>
                        <h1 class="text-xl font-bold text-gray-900">Fabriku</h1>
                    </div>
                    <div class="hidden md:flex items-center gap-6">
                        <a href="#features" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                            Fitur
                        </a>
                        <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                            Harga
                        </a>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link href="/login" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                            Masuk
                        </Link>
                        <Link 
                            href="/register" 
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors"
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
                <div class="text-center max-w-4xl mx-auto">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-100 text-indigo-700 text-sm font-medium mb-6">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Gratis 30 Hari Trial
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900">
                        Kelola Bisnis UMKM
                        <span class="block text-indigo-600">Multi-Kategori, Satu Platform</span>
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto leading-relaxed">
                        Platform SaaS untuk UMKM Indonesia — dari toko sederhana yang butuh stok & kasir, hingga bisnis produksi lengkap dari bahan baku hingga penjualan.
                    </p>

                    <!-- Category Badges -->
                    <div class="mt-8 flex flex-wrap justify-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium shadow-sm">
                            <Store :size="14" class="text-gray-400" /> Toko / Retail
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium shadow-sm">
                            <Scissors :size="14" class="text-gray-400" /> Garment
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium shadow-sm">
                            <ChefHat :size="14" class="text-gray-400" /> Makanan & Kue
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium shadow-sm">
                            <Palette :size="14" class="text-gray-400" /> Kerajinan & Craft
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium shadow-sm">
                            <Sparkles :size="14" class="text-gray-400" /> Kosmetik
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-gray-200 bg-white text-gray-600 text-sm font-medium shadow-sm">
                            <Home :size="14" class="text-gray-400" /> Produksi Rumahan
                        </span>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
                        <Link
                            href="/register"
                            class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                        >
                            Mulai Gratis 30 Hari
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>
                        <a
                            href="#pricing"
                            class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors"
                        >
                            Lihat Harga
                        </a>
                    </div>

                    <!-- Trust Indicators -->
                    <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Tanpa kartu kredit</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Full akses 30 hari</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Setup 5 menit</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-24 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-indigo-600 font-semibold text-sm uppercase tracking-wide">Fitur Lengkap</span>
                    <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">
                        Semua yang Anda Butuhkan
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                        Dari bahan baku hingga penjualan, kelola seluruh proses bisnis dengan terminologi yang disesuaikan per kategori
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-6 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Bahan Baku</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Catat penerimaan bahan dari supplier dengan atribut dinamis, tracking batch/expired date, dan monitor stok real-time.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Pattern/Resep & Persiapan</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Kelola pattern/resep dengan BOM, proses persiapan, hitung efisiensi, dan minimalisir waste produksi.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Manajemen Produksi</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Kelola produksi internal maupun outsourcing dengan quality control dan batch tracking terintegrasi.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Inventory Gudang</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Kelola produk jadi dengan lokasi rak, FIFO/FEFO tracking, expired date monitoring, dan alert low stock.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-6 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Penjualan Multi-Channel</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Catat penjualan dari berbagai channel (offline, online, reseller) dengan payment tracking terintegrasi.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-6 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                        <div class="w-12 h-12 bg-pink-500 rounded-xl flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Laporan & Analytics</h3>
                        <p class="text-gray-600 text-sm leading-relaxed">
                            Dashboard KPI, laporan produksi, inventory, sales, hingga profit & loss statement lengkap.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="py-24 bg-gradient-to-b from-white to-indigo-50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <span class="text-indigo-600 font-semibold text-sm uppercase tracking-wide">Harga</span>
                    <h2 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">
                        Pilih Paket yang Sesuai
                    </h2>
                    <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">
                        Mulai dengan Free Trial 30 hari dengan full akses semua fitur, atau langsung upgrade ke Full Member untuk akses tanpa batas waktu
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                    <!-- Free Trial -->
                    <div class="relative bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Free Trial</h3>
                            <div class="text-3xl font-extrabold text-gray-900">
                                Rp 0
                                <span class="text-base font-normal text-gray-500">/30 hari</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Full akses semua fitur</p>
                            <p class="mt-1 text-xs text-amber-600 font-medium">Setelah expired: Read-only access</p>
                        </div>

                        <ul class="space-y-2.5 mb-6">
                            <li v-for="feature in features" :key="feature.name" class="flex items-start gap-2.5">
                                <component 
                                    :is="feature.free === true ? Check : feature.free ? Check : X"
                                    :class="feature.free === true ? 'text-green-500' : feature.free ? 'text-green-500' : 'text-gray-300'"
                                    class="w-4 h-4 flex-shrink-0 mt-0.5"
                                />
                                <span :class="feature.free ? 'text-gray-700 text-sm' : 'text-gray-400 text-sm'">
                                    {{ feature.name }}
                                    <span v-if="typeof feature.free === 'string'" class="font-medium text-indigo-600">({{ feature.free }})</span>
                                </span>
                            </li>
                        </ul>

                        <Link
                            href="/register"
                            class="block w-full py-2.5 px-4 text-center font-semibold text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition-colors"
                        >
                            Coba Gratis
                        </Link>
                    </div>

                    <!-- Full Member -->
                    <div class="relative bg-white rounded-2xl shadow-lg border-2 border-indigo-200 p-6">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-xs font-bold rounded-full">
                                POPULER
                            </span>
                        </div>

                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Full Member</h3>
                            <div class="text-3xl font-extrabold text-gray-900">
                                {{ formatCurrency(settings.membership_price_monthly) }}
                                <span class="text-base font-normal text-gray-500">/bulan</span>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                atau {{ formatCurrency(settings.membership_price_yearly) }}/tahun
                                <span class="block text-green-600 font-medium text-xs">
                                    (Hemat {{ formatCurrency((settings.membership_price_monthly * 12) - settings.membership_price_yearly) }})
                                </span>
                            </p>
                        </div>

                        <ul class="space-y-2.5 mb-6">
                            <li v-for="feature in features" :key="feature.name" class="flex items-start gap-2.5">
                                <Check class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" />
                                <span class="text-gray-700 text-sm">
                                    {{ feature.name }}
                                    <span v-if="typeof feature.full === 'string'" class="font-medium text-indigo-600">({{ feature.full }})</span>
                                </span>
                            </li>
                        </ul>

                        <Link
                            href="/register"
                            class="block w-full py-2.5 px-4 text-center font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-colors"
                        >
                            Mulai Sekarang
                        </Link>
                    </div>


                </div>

                <!-- Feature Comparison Table (Mobile Hidden) -->
                <div class="mt-16 hidden lg:block">
                    <h3 class="text-xl font-bold text-gray-900 text-center mb-8">Perbandingan Fitur Lengkap</h3>
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
                                            class="w-5 h-5 inline-block"
                                        />
                                        <span v-if="typeof feature.free === 'string'" class="text-sm text-gray-600 ml-1">{{ feature.free }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center bg-indigo-50/50">
                                        <Check class="w-5 h-5 text-indigo-600 inline-block" />
                                        <span v-if="typeof feature.full === 'string'" class="text-sm text-indigo-600 ml-1">{{ feature.full }}</span>
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
                <div class="rounded-3xl bg-indigo-600 px-6 py-16 sm:px-12 sm:py-20 text-center">
                    <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
                        Siap untuk meningkatkan bisnis Anda?
                    </h2>
                    <p class="text-lg text-indigo-100 mb-8 max-w-2xl mx-auto">
                        Mulai trial gratis 30 hari sekarang. Tanpa kartu kredit, akses penuh semua fitur.
                    </p>
                    <Link
                        href="/register"
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-indigo-600 bg-white rounded-xl hover:bg-indigo-50 transition-colors"
                    >
                        Mulai Gratis Sekarang
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-gray-200 py-12 bg-white">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">F</span>
                        </div>
                        <span class="text-gray-900 font-semibold">Fabriku</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <a 
                            href="https://github.com/azmifauzan/fabriku" 
                            target="_blank" 
                            rel="noopener noreferrer"
                            class="flex items-center gap-2 text-gray-500 hover:text-gray-900 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-sm font-medium">GitHub</span>
                        </a>
                        <p class="text-sm text-gray-500">
                            © 2026 Fabriku. Made with ❤️ for Indonesian UMKM.
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
