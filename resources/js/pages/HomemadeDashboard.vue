<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Factory, Package, ShoppingCart, Sparkles, TrendingUp } from 'lucide-vue-next';

interface Stats {
    sales_today: number;
    sales_today_count: number;
    sales_month: number;
    sales_month_count: number;
    total_inventory_items: number;
    low_stock_count: number;
    out_of_stock_count: number;
    inventory_value: number;
    production_today: number;
    low_stock_materials_count: number;
}

interface SalesTrendItem {
    date: string;
    total: number;
    count: number;
}

interface TopProduct {
    sku: string;
    name: string;
    total_sold: number;
    total_revenue: number;
}

interface LowStockItem {
    id: number;
    sku: string;
    product_name: string;
    current_quantity: number;
    reserved_quantity: number;
    minimum_stock: number;
}

interface LowStockMaterial {
    id: number;
    code: string;
    name: string;
    stock_quantity: number;
    unit: string;
    min_stock: number;
}

interface RecentProduction {
    batch_id: string;
    inventory_item?: {
        product_name: string;
    };
    created_at: string;
    total_qty: number;
}

defineProps<{
    stats: Stats;
    salesTrend: SalesTrendItem[];
    topProducts: TopProduct[];
    lowStockItems: LowStockItem[];
    lowStockMaterials: LowStockMaterial[];
    recentProductions: RecentProduction[];
}>();

const fmt = (value: number) => 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
</script>

<template>
    <AppLayout>
        <Head title="Dashboard Produksi Rumahan" />

        <div class="min-h-screen bg-gray-50/50 px-6 py-6 dark:bg-gray-900/50">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="flex items-center gap-2 text-2xl font-bold text-gray-900 dark:text-gray-100">
                            <span>Dashboard Produksi Rumahan</span>
                            <Sparkles :size="20" class="animate-pulse text-indigo-500" />
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan produksi dan penjualan Anda hari ini</p>
                    </div>
                    <div>
                        <Link
                            href="/simple-production/create"
                            class="inline-flex transform items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-all hover:-translate-y-0.5 hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                        >
                            <Factory :size="16" />
                            Catat Produksi Baru
                        </Link>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <!-- Revenue Today -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Penjualan Hari Ini</p>
                            <div class="rounded-xl bg-green-50 p-2 dark:bg-green-950/30">
                                <TrendingUp :size="16" class="text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(stats.sales_today) }}</p>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ stats.sales_today_count }} transaksi</p>
                    </div>

                    <!-- Revenue Month -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Penjualan Bulan Ini</p>
                            <div class="rounded-xl bg-indigo-50 p-2 dark:bg-indigo-950/30">
                                <ShoppingCart :size="16" class="text-indigo-600 dark:text-indigo-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(stats.sales_month) }}</p>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ stats.sales_month_count }} transaksi</p>
                    </div>

                    <!-- Finished Goods Inventory Value -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Nilai Produk Jadi</p>
                            <div class="rounded-xl bg-blue-50 p-2 dark:bg-blue-950/30">
                                <Package :size="16" class="text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(stats.inventory_value) }}</p>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">{{ stats.total_inventory_items }} SKU produk jadi</p>
                    </div>

                    <!-- Production Today -->
                    <div
                        class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-shadow hover:shadow-md dark:border-gray-800 dark:bg-gray-800"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Produksi Hari Ini</p>
                            <div class="rounded-xl bg-purple-50 p-2 dark:bg-purple-950/30">
                                <Factory :size="16" class="text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                        <p class="mt-3 text-xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.production_today }} <span class="text-xs font-normal text-gray-400">unit</span>
                        </p>
                        <p class="mt-1 text-xs font-medium text-gray-500 dark:text-gray-400">Dari catatan produksi harian</p>
                    </div>
                </div>

                <!-- Main Section -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Sales Trend -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="mb-5 text-sm font-bold text-gray-800 dark:text-gray-200">Tren Penjualan 7 Hari Terakhir</h2>
                        <div v-if="salesTrend.length === 0" class="py-12 text-center text-sm text-gray-400">Belum ada data penjualan</div>
                        <div v-else class="space-y-3">
                            <div v-for="day in salesTrend" :key="day.date" class="flex items-center gap-3">
                                <span class="w-20 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                    {{ new Date(day.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}
                                </span>
                                <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-700" style="height: 8px">
                                    <div
                                        class="h-full rounded-full bg-indigo-500"
                                        :style="{
                                            width: `${Math.min(100, (day.total / (Math.max(...salesTrend.map((d) => d.total)) || 1)) * 100)}%`,
                                        }"
                                    ></div>
                                </div>
                                <span class="w-24 text-right text-xs font-bold text-gray-800 dark:text-gray-200">{{ fmt(day.total) }}</span>
                                <span class="w-12 text-right text-xs font-medium text-gray-400">{{ day.count }}x</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <h2 class="mb-5 text-sm font-bold text-gray-800 dark:text-gray-200">Produk Jadi Terlaris (30 Hari)</h2>
                        <div v-if="topProducts.length === 0" class="py-12 text-center text-sm text-gray-400">Belum ada data penjualan</div>
                        <div v-else class="space-y-4">
                            <div v-for="(prod, idx) in topProducts" :key="prod.sku" class="flex items-center gap-3">
                                <span
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-xs font-bold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                                >
                                    {{ idx + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-bold text-gray-800 dark:text-gray-200">{{ prod.name }}</p>
                                    <p class="text-xs font-semibold text-gray-400">{{ prod.sku }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ fmt(prod.total_revenue) }}</p>
                                    <p class="text-xs font-medium text-gray-400">{{ prod.total_sold }} unit terjual</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Materials Warning -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-200">
                                <span>Bahan Baku Hampir Habis</span>
                                <span
                                    v-if="stats.low_stock_materials_count > 0"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-[10px] font-bold text-red-600 dark:bg-red-950/30 dark:text-red-400"
                                >
                                    {{ stats.low_stock_materials_count }}
                                </span>
                            </h2>
                            <Link href="/materials" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                >Lihat semua</Link
                            >
                        </div>
                        <div v-if="lowStockMaterials.length === 0" class="py-12 text-center text-sm text-gray-400">Semua stok bahan baku aman</div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="mat in lowStockMaterials"
                                :key="mat.id"
                                class="flex items-center justify-between gap-3 rounded-xl border border-red-50 bg-red-50/20 px-4 py-3 dark:border-red-950/20 dark:bg-red-950/5"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-800 dark:text-gray-200">{{ mat.name }}</p>
                                    <p class="text-xs font-semibold text-gray-400">{{ mat.code }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-red-600 dark:text-red-400">{{ mat.stock_quantity }} {{ mat.unit }}</p>
                                    <p class="text-xs font-medium text-gray-400">min. {{ mat.min_stock }} {{ mat.unit }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Finished Products Warning -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-800">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-200">
                                <span>Produk Jadi Hampir Habis</span>
                                <span
                                    v-if="stats.low_stock_count > 0"
                                    class="flex h-5 w-5 items-center justify-center rounded-full bg-orange-100 text-[10px] font-bold text-orange-600 dark:bg-orange-950/30 dark:text-orange-400"
                                >
                                    {{ stats.low_stock_count }}
                                </span>
                            </h2>
                            <Link href="/inventory/items" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                >Lihat semua</Link
                            >
                        </div>
                        <div v-if="lowStockItems.length === 0" class="py-12 text-center text-sm text-gray-400">Semua stok produk jadi aman</div>
                        <div v-else class="space-y-3">
                            <div
                                v-for="item in lowStockItems"
                                :key="item.id"
                                class="flex items-center justify-between gap-3 rounded-xl border border-orange-50 bg-orange-50/20 px-4 py-3 dark:border-orange-950/20 dark:bg-orange-950/5"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-800 dark:text-gray-200">{{ item.product_name }}</p>
                                    <p class="text-xs font-semibold text-gray-400">{{ item.sku }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-orange-600 dark:text-orange-400">
                                        {{ item.current_quantity - item.reserved_quantity }}
                                    </p>
                                    <p class="text-xs font-medium text-gray-400">min. {{ item.minimum_stock }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Productions -->
                    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2 dark:border-gray-800 dark:bg-gray-800">
                        <div class="mb-5 flex items-center justify-between">
                            <h2 class="text-sm font-bold text-gray-800 dark:text-gray-200">Riwayat Produksi Terbaru</h2>
                            <Link href="/simple-production" class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400"
                                >Lihat semua</Link
                            >
                        </div>
                        <div v-if="recentProductions.length === 0" class="py-12 text-center text-sm text-gray-400">
                            Belum ada riwayat produksi dicatat
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full border-collapse text-left">
                                <thead>
                                    <tr
                                        class="border-b border-gray-100 text-xs font-bold tracking-wider text-gray-400 uppercase dark:border-gray-700"
                                    >
                                        <th class="pr-4 pb-3">Tanggal</th>
                                        <th class="pr-4 pb-3">Batch ID</th>
                                        <th class="pr-4 pb-3">Produk</th>
                                        <th class="pb-3 text-right">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="prod in recentProductions"
                                        :key="prod.batch_id"
                                        class="dark:border-gray-750 border-b border-gray-50/50 text-sm text-gray-700 hover:bg-gray-50/20 dark:text-gray-300 dark:hover:bg-gray-800/10"
                                    >
                                        <td class="py-3.5 pr-4 font-medium">
                                            {{
                                                new Date(prod.created_at).toLocaleDateString('id-ID', {
                                                    day: '2-digit',
                                                    month: 'short',
                                                    year: 'numeric',
                                                })
                                            }}
                                        </td>
                                        <td class="py-3.5 pr-4 font-mono text-xs text-gray-400">{{ prod.batch_id.substring(0, 8) }}...</td>
                                        <td class="py-3.5 pr-4 font-bold text-gray-800 dark:text-gray-200">
                                            {{ prod.inventory_item?.product_name || 'Generic Production' }}
                                        </td>
                                        <td class="py-3.5 text-right font-bold text-indigo-600 dark:text-indigo-400">+{{ prod.total_qty }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
