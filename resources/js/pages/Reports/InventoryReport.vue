<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { AlertTriangle, FileBarChart, Filter, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface InventoryItem {
    id: number;
    sku: string;
    name: string;
    category: string;
    quantity: number;
    reserved_quantity: number;
    available_quantity: number;
    minimum_stock: number;
    unit_price: number;
    total_value: number;
    status: string;
    location: string | null;
    pattern: string | null;
    is_low_stock: boolean;
    production_date: string | null;
    expired_date: string | null;
}

interface Summary {
    total_items: number;
    total_quantity: number;
    total_value: number;
    low_stock_items: number;
    out_of_stock_items: number;
}

interface Filters {
    status?: string;
    category?: string;
    search?: string;
}

const props = defineProps<{
    items: InventoryItem[];
    summary: Summary;
    filters: Filters;
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const category = ref(props.filters.category || '');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const applyFilter = () => {
    router.get(
        '/reports/inventory',
        {
            search: search.value,
            status: status.value,
            category: category.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const resetFilter = () => {
    search.value = '';
    status.value = '';
    category.value = '';
    applyFilter();
};

const getStatusClass = (itemStatus: string) => {
    const classes: Record<string, string> = {
        available: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        low_stock: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        out_of_stock: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[itemStatus] || classes.available;
};
</script>

<template>
    <AppLayout>
        <Head title="Laporan Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <FileBarChart :size="24" class="text-indigo-500" />
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Inventory</h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ringkasan stok dan nilai inventory</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ summary.total_items }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Quantity</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ summary.total_quantity }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(summary.total_value) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Low Stock</dt>
                        <dd class="mt-1 text-2xl font-semibold text-yellow-600 dark:text-yellow-400">
                            {{ summary.low_stock_items }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-red-600 dark:text-red-400">Out of Stock</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">
                            {{ summary.out_of_stock_items }}
                        </dd>
                    </div>
                </div>

                <!-- Filters -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-4 flex items-center gap-2">
                            <Filter :size="20" class="text-gray-500" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search :size="16" class="text-gray-400" />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Nama atau SKU..."
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        @keyup.enter="applyFilter"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Status </label>
                                <select
                                    v-model="status"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="available">Available</option>
                                    <option value="low_stock">Low Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Kategori </label>
                                <select
                                    v-model="category"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option value="garment">Garment</option>
                                    <option value="food">Makanan</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div class="flex items-end gap-2">
                                <button
                                    type="button"
                                    @click="applyFilter"
                                    class="flex-1 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Terapkan
                                </button>
                                <button
                                    type="button"
                                    @click="resetFilter"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        SKU
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama Item
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Lokasi
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Qty
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Reserved
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Available
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Unit Price
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Value
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="items.length === 0">
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data inventory</td>
                                </tr>
                                <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ item.sku }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">{{ item.name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ item.pattern || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ item.location || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ item.reserved_quantity }}
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                        :class="item.is_low_stock ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
                                    >
                                        {{ item.available_quantity }}
                                        <span v-if="item.is_low_stock" class="ml-1">
                                            <AlertTriangle :size="14" class="inline" />
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(item.unit_price) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(item.total_value) }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            :class="getStatusClass(item.status)"
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
