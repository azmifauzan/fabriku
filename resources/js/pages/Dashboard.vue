<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Box, ClipboardList, Factory, Package, ShoppingCart, TrendingUp } from 'lucide-vue-next';
import { computed } from 'vue';

const { term } = useBusinessContext();

const materialLabel = computed(() => term('material', 'Bahan Baku'));

interface Activity {
    type: 'sale' | 'production';
    description: string;
    amount?: number;
    quantity?: number;
    status: string;
    date: string;
}

interface Stats {
    total_materials: number;
    low_stock_materials: number;
    total_inventory: number;
    low_stock_inventory: number;
    total_sales_month: number;
    total_sales_count: number;
    pending_production: number;
    pending_preparation: number;
}

interface TopProduct {
    sku: string;
    name: string;
    total_sold: number;
    total_revenue: number;
}

interface Material {
    id: number;
    code: string;
    name: string;
    current_stock: number;
    unit: string;
    reorder_point: number;
}

interface InventoryItem {
    id: number;
    sku: string;
    name: string;
    current_stock: number;
    reserved_stock: number;
    minimum_stock: number;
}

defineProps<{
    stats: Stats;
    salesTrend?: Array<{ date: string; total: number; count: number }>;
    topProducts?: TopProduct[];
    recentActivities?: Activity[];
    lowStockMaterials?: Material[];
    lowStockInventory?: InventoryItem[];
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getStatusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return classes[status] || classes.draft;
};
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Welcome Message -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Fabriku</h2>
                        <p class="mt-1 text-gray-600 dark:text-gray-400">Ringkasan bisnis dan aktivitas terkini</p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Total Materials -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 rounded-lg bg-indigo-500 p-3">
                                    <Package :size="24" class="text-white" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total {{ materialLabel }}</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ stats.total_materials }}
                                    </dd>
                                    <p v-if="stats.low_stock_materials > 0" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ stats.low_stock_materials }} low stock
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Inventory -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 rounded-lg bg-green-500 p-3">
                                    <Box :size="24" class="text-white" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Inventory</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ stats.total_inventory }}
                                    </dd>
                                    <p v-if="stats.low_stock_inventory > 0" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                        {{ stats.low_stock_inventory }} low stock
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales This Month -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 rounded-lg bg-blue-500 p-3">
                                    <ShoppingCart :size="24" class="text-white" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Penjualan Bulan Ini</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(stats.total_sales_month) }}
                                    </dd>
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ stats.total_sales_count }} orders</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Production -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center gap-4">
                                <div class="flex-shrink-0 rounded-lg bg-yellow-500 p-3">
                                    <Factory :size="24" class="text-white" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Produksi Aktif</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                        {{ stats.pending_production }}
                                    </dd>
                                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ stats.pending_preparation }} preparation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Top Products -->
                    <div v-if="topProducts && topProducts.length > 0" class="rounded-lg bg-white shadow-sm lg:col-span-2 dark:bg-gray-800">
                        <div class="p-6">
                            <div class="mb-4 flex items-center gap-2">
                                <TrendingUp :size="20" class="text-indigo-500" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Produk Terlaris (30 Hari Terakhir)</h3>
                            </div>
                            <div class="space-y-3">
                                <div
                                    v-for="product in topProducts"
                                    :key="product.sku"
                                    class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                            {{ product.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.sku }}</p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ product.total_sold }} pcs</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatCurrency(product.total_revenue) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div v-if="recentActivities && recentActivities.length > 0" class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="mb-4 flex items-center gap-2">
                                <ClipboardList :size="20" class="text-indigo-500" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Aktivitas Terkini</h3>
                            </div>
                            <div class="space-y-3">
                                <div
                                    v-for="(activity, idx) in recentActivities"
                                    :key="idx"
                                    class="border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                                >
                                    <div class="flex items-start gap-2">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm text-gray-900 dark:text-white">
                                                {{ activity.description }}
                                            </p>
                                            <div class="mt-1 flex items-center gap-2">
                                                <span
                                                    :class="getStatusBadgeClass(activity.status)"
                                                    class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium"
                                                >
                                                    {{ activity.status }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ formatDate(activity.date) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div
                    v-if="(lowStockMaterials && lowStockMaterials.length > 0) || (lowStockInventory && lowStockInventory.length > 0)"
                    class="grid grid-cols-1 gap-6 lg:grid-cols-2"
                >
                    <!-- Low Stock Materials -->
                    <div v-if="lowStockMaterials && lowStockMaterials.length > 0" class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="mb-4 flex items-center gap-2">
                                <AlertTriangle :size="20" class="text-red-500" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ materialLabel }} Low Stock</h3>
                            </div>
                            <div class="space-y-3">
                                <div
                                    v-for="material in lowStockMaterials"
                                    :key="material.id"
                                    class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                            {{ material.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ material.code }}</p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                                            {{ material.current_stock }} {{ material.unit }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Min: {{ material.reorder_point }} {{ material.unit }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link href="/materials" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                    Lihat Semua {{ materialLabel }} →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Inventory -->
                    <div v-if="lowStockInventory && lowStockInventory.length > 0" class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="mb-4 flex items-center gap-2">
                                <AlertTriangle :size="20" class="text-red-500" />
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Inventory Low Stock</h3>
                            </div>
                            <div class="space-y-3">
                                <div
                                    v-for="item in lowStockInventory"
                                    :key="item.id"
                                    class="flex items-center justify-between border-b border-gray-200 pb-3 last:border-0 dark:border-gray-700"
                                >
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                            {{ item.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.sku }}</p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                                            {{ item.current_stock - item.reserved_stock }} pcs
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Min: {{ item.minimum_stock }} pcs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Link href="/inventory/items" class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                    Lihat Semua Inventory →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
