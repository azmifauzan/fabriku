<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50">
        <Head title="Fabriku - Platform Manajemen Produksi Multi-Kategori untuk UMKM" />

        <!-- Navigation -->
        <nav class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center">
                        <h1 class="text-2xl font-bold text-indigo-600">Fabriku</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <Link href="/login" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600"> Masuk </Link>
                        <Link href="/login" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                            Coba Demo
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl">
                <div class="relative z-10 pb-8 sm:pb-16 md:pb-20 lg:w-full lg:pb-28 xl:pb-32">
                    <main class="mx-auto mt-10 max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                        <div class="text-center lg:text-left">
                            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                                <span class="block">Kelola Produksi Bisnis UMKM</span>
                                <span class="block text-indigo-600">Multi-Kategori, Satu Platform</span>
                            </h1>
                            <p class="mt-3 text-base text-gray-500 sm:mx-auto sm:mt-5 sm:max-w-xl sm:text-lg md:mt-5 md:text-xl lg:mx-0">
                                Platform SaaS untuk UMKM dalam mengelola produksi berbagai kategori bisnis (Garment, Kue, Kerajinan, dan lainnya) dari
                                bahan baku hingga penjualan dalam satu sistem terintegrasi.
                            </p>

                            <!-- Category Badges -->
                            <div class="mt-6 flex flex-wrap justify-center gap-2 lg:justify-start">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-800">
                                    🧵 Garment & Konveksi
                                </span>
                                <span class="inline-flex items-center rounded-full bg-pink-100 px-3 py-1 text-sm font-medium text-pink-800">
                                    🍰 Makanan & Kue
                                </span>
                                <span class="inline-flex items-center rounded-full bg-purple-100 px-3 py-1 text-sm font-medium text-purple-800">
                                    🎨 Kerajinan (Coming Soon)
                                </span>
                                <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                                    💄 Kosmetik (Coming Soon)
                                </span>
                            </div>

                            <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                                <div class="rounded-md shadow">
                                    <Link
                                        href="/login"
                                        class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-8 py-3 text-base font-medium text-white hover:bg-indigo-700 md:px-10 md:py-4 md:text-lg"
                                    >
                                        Mulai Sekarang
                                    </Link>
                                </div>
                                <div class="mt-3 sm:mt-0 sm:ml-3">
                                    <a
                                        href="#features"
                                        class="flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-100 px-8 py-3 text-base font-medium text-indigo-700 hover:bg-indigo-200 md:px-10 md:py-4 md:text-lg"
                                    >
                                        Pelajari Lebih Lanjut
                                    </a>
                                </div>
                            </div>

                            <!-- Demo Credentials -->
                            <div class="mt-8 rounded-lg border border-indigo-200 bg-gradient-to-r from-indigo-50 to-purple-50 p-4">
                                <p class="mb-3 font-semibold text-gray-900">🎉 Demo Gratis - Pilih Kategori Bisnis Anda:</p>

                                <div class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                    <!-- Garment Demo -->
                                    <div class="rounded-md border border-gray-200 bg-white p-3 shadow-sm">
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="text-lg">🧵</span>
                                            <span class="font-semibold text-gray-900">Konveksi Fabriku</span>
                                        </div>
                                        <div class="space-y-1 text-gray-600">
                                            <p>Email: <span class="font-mono text-indigo-600">admin@konveksi.com</span></p>
                                            <p>Password: <span class="font-mono text-indigo-600">password</span></p>
                                            <p class="mt-2 text-xs text-gray-500">Garment & Konveksi (Mukena, Daster, Gamis)</p>
                                        </div>
                                    </div>

                                    <!-- Food Demo -->
                                    <div class="rounded-md border border-gray-200 bg-white p-3 shadow-sm">
                                        <div class="mb-2 flex items-center gap-2">
                                            <span class="text-lg">🍰</span>
                                            <span class="font-semibold text-gray-900">Kue Mama Homemade</span>
                                        </div>
                                        <div class="space-y-1 text-gray-600">
                                            <p>Email: <span class="font-mono text-pink-600">admin@kuemama.com</span></p>
                                            <p>Password: <span class="font-mono text-pink-600">password</span></p>
                                            <p class="mt-2 text-xs text-gray-500">Makanan & Kue (Cake, Brownies, Cookies)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="bg-white py-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-base font-semibold tracking-wide text-indigo-600 uppercase">Fitur Lengkap</h2>
                    <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">Semua yang Anda Butuhkan</p>
                    <p class="mx-auto mt-4 max-w-2xl text-xl text-gray-500">
                        Dari bahan baku hingga penjualan, kelola seluruh proses bisnis Anda dengan terminologi yang disesuaikan per kategori
                    </p>
                </div>

                <div class="mt-10">
                    <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Feature 1 -->
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-indigo-500 p-3 shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Manajemen Bahan Baku</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Catat penerimaan bahan (kain, bahan makanan, dll) dari supplier dengan atribut dinamis, tracking batch/expired
                                        date, dan monitor stok real-time.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 2 -->
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-green-500 p-3 shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Pattern/Resep & Persiapan</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Kelola pattern/resep dengan BOM (Bill of Materials), proses persiapan (cutting/mixing), hitung efisiensi, dan
                                        minimalisir waste.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 3 -->
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-purple-500 p-3 shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Manajemen Produksi</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Kelola produksi internal maupun outsourcing (penjahit/dapur sharing) dengan quality control dan batch tracking
                                        terintegrasi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 4 -->
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-yellow-500 p-3 shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Inventory Gudang</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Kelola produk jadi dengan lokasi rak, FIFO/FEFO tracking, expired date monitoring (makanan), dan alert untuk
                                        low stock.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 5 -->
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-red-500 p-3 shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Penjualan Multi-Channel</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Catat penjualan dari berbagai channel (offline, online, reseller) dengan payment tracking.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 6 -->
                        <div class="pt-6">
                            <div class="flow-root rounded-lg bg-gray-50 px-6 pb-8">
                                <div class="-mt-6">
                                    <div>
                                        <span class="inline-flex items-center justify-center rounded-md bg-pink-500 p-3 shadow-lg">
                                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                                                />
                                            </svg>
                                        </span>
                                    </div>
                                    <h3 class="mt-8 text-lg font-medium tracking-tight text-gray-900">Laporan & Analytics</h3>
                                    <p class="mt-5 text-base text-gray-500">
                                        Dashboard KPI, laporan produksi, inventory, sales, hingga profit & loss statement.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits Section -->
        <div class="bg-indigo-700">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:flex lg:items-center lg:justify-between lg:px-8 lg:py-16">
                <h2 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    <span class="block">Siap untuk meningkatkan bisnis Anda?</span>
                    <span class="block text-indigo-200">Mulai trial gratis 30 hari hari ini.</span>
                </h2>
                <div class="mt-8 flex lg:mt-0 lg:flex-shrink-0">
                    <div class="inline-flex rounded-md shadow">
                        <Link
                            href="/login"
                            class="inline-flex items-center justify-center rounded-md border border-transparent bg-white px-5 py-3 text-base font-medium text-indigo-600 hover:bg-indigo-50"
                        >
                            Coba Sekarang
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="text-center">
                    <p class="text-base text-gray-400">&copy; 2026 Fabriku. Made with ❤️ for Indonesian UMKM.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
