<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Package, ShoppingBag, ShoppingCart, TrendingUp } from 'lucide-vue-next';

interface Stats {
    sales_today: number;
    sales_today_count: number;
    sales_month: number;
    sales_month_count: number;
    total_inventory_items: number;
    low_stock_count: number;
    out_of_stock_count: number;
    inventory_value: number;
    outstanding_receivables: number;
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

interface RecentPurchase {
    batch_id: string;
    supplier_name: string;
    created_at: string;
    total_cost: number;
}

defineProps<{
    stats: Stats;
    salesTrend: SalesTrendItem[];
    topProducts: TopProduct[];
    lowStockItems: LowStockItem[];
    recentPurchases: RecentPurchase[];
}>();

const fmt = (value: number) => 'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan toko hari ini</p>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Penjualan Hari Ini</p>
                            <TrendingUp :size="16" class="text-green-500" />
                        </div>
                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(stats.sales_today) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ stats.sales_today_count }} transaksi</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Penjualan Bulan Ini</p>
                            <ShoppingCart :size="16" class="text-indigo-500" />
                        </div>
                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(stats.sales_month) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ stats.sales_month_count }} transaksi</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nilai Stok</p>
                            <Package :size="16" class="text-blue-500" />
                        </div>
                        <p class="mt-2 text-xl font-bold text-gray-900 dark:text-gray-100">{{ fmt(stats.inventory_value) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ stats.total_inventory_items }} produk</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Stok Hampir Habis</p>
                            <AlertTriangle :size="16" :class="stats.low_stock_count > 0 ? 'text-orange-500' : 'text-gray-400'" />
                        </div>
                        <p class="mt-2 text-xl font-bold" :class="stats.low_stock_count > 0 ? 'text-orange-500' : 'text-gray-900 dark:text-gray-100'">
                            {{ stats.low_stock_count }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ stats.out_of_stock_count }} habis total</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Sales Trend -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Penjualan 7 Hari Terakhir</h2>
                        <div v-if="salesTrend.length === 0" class="py-8 text-center text-sm text-gray-400">Belum ada data penjualan</div>
                        <div v-else class="space-y-2">
                            <div v-for="day in salesTrend" :key="day.date" class="flex items-center gap-3">
                                <span class="w-20 text-xs text-gray-500 dark:text-gray-400">
                                    {{ new Date(day.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}
                                </span>
                                <div class="flex-1 rounded-full bg-gray-100 dark:bg-gray-700" style="height: 6px">
                                    <div
                                        class="h-full rounded-full bg-indigo-500"
                                        :style="{
                                            width: `${Math.min(100, (day.total / (Math.max(...salesTrend.map((d) => d.total)) || 1)) * 100)}%`,
                                        }"
                                    ></div>
                                </div>
                                <span class="w-24 text-right text-xs font-medium text-gray-700 dark:text-gray-300">{{ fmt(day.total) }}</span>
                                <span class="w-12 text-right text-xs text-gray-400">{{ day.count }}x</span>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <h2 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300">Produk Terlaris (30 Hari)</h2>
                        <div v-if="topProducts.length === 0" class="py-8 text-center text-sm text-gray-400">Belum ada data penjualan</div>
                        <div v-else class="space-y-3">
                            <div v-for="(prod, idx) in topProducts" :key="prod.sku" class="flex items-center gap-3">
                                <span
                                    class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400"
                                >
                                    {{ idx + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ prod.name }}</p>
                                    <p class="text-xs text-gray-400">{{ prod.sku }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ fmt(prod.total_revenue) }}</p>
                                    <p class="text-xs text-gray-400">{{ prod.total_sold }} terjual</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Stok Hampir Habis</h2>
                            <Link href="/inventory/items" class="text-xs text-indigo-600 hover:underline dark:text-indigo-400">Lihat semua</Link>
                        </div>
                        <div v-if="lowStockItems.length === 0" class="py-8 text-center text-sm text-gray-400">Semua stok aman</div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="item in lowStockItems"
                                :key="item.id"
                                class="flex items-center justify-between gap-2 rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-900/10"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ item.product_name }}</p>
                                    <p class="text-xs text-gray-400">{{ item.sku }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-sm font-bold text-orange-600 dark:text-orange-400">
                                        {{ item.current_quantity - item.reserved_quantity }}
                                    </p>
                                    <p class="text-xs text-gray-400">min. {{ item.minimum_stock }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="lowStockItems.length === 0" class="mt-3">
                            <Link
                                href="/purchase-receipts/create"
                                class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                            >
                                <ShoppingBag :size="12" />
                                Tambah Stok
                            </Link>
                        </div>
                    </div>

                    <!-- Recent Purchases -->
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pembelian Terakhir</h2>
                            <Link href="/purchase-receipts" class="text-xs text-indigo-600 hover:underline dark:text-indigo-400">Lihat semua</Link>
                        </div>
                        <div v-if="recentPurchases.length === 0" class="py-8 text-center text-sm text-gray-400">
                            Belum ada catatan pembelian.
                            <br />
                            <Link href="/purchase-receipts/create" class="mt-2 inline-block text-indigo-600 hover:underline dark:text-indigo-400">
                                Catat pembelian pertama →
                            </Link>
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="purchase in recentPurchases"
                                :key="purchase.batch_id"
                                class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 px-3 py-2 dark:border-gray-700"
                            >
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">{{ purchase.supplier_name }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{
                                            new Date(purchase.created_at).toLocaleDateString('id-ID', {
                                                day: '2-digit',
                                                month: 'short',
                                                year: 'numeric',
                                            })
                                        }}
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-300">{{ fmt(purchase.total_cost) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
