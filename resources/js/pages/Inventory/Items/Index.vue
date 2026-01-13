<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';

interface Item {
    id: number;
    sku: string;
    name: string;
    category: string;
    current_stock: number;
    reserved_stock: number;
    minimum_stock: number;
    unit_cost: number;
    selling_price: number;
    quality_grade: string;
    status: string;
    inventory_location?: {
        id: number;
        name: string;
    };
    expiry_date?: string;
}

interface PaginatedItems {
    data: Item[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    items: PaginatedItems;
    filters: {
        search?: string;
        status?: string;
        category?: string;
        location_id?: number;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const categoryFilter = ref(props.filters.category || '');

const applyFilters = () => {
    router.get('/inventory/items', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        category: categoryFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    categoryFilter.value = '';
    router.get('/inventory/items');
};

const statusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        available: 'bg-green-100 text-green-800',
        reserved: 'bg-yellow-100 text-yellow-800',
        sold: 'bg-blue-100 text-blue-800',
        damaged: 'bg-red-100 text-red-800',
        expired: 'bg-gray-100 text-gray-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const isLowStock = (item: Item) => {
    return item.current_stock <= item.minimum_stock;
};
</script>

<template>
    <AppLayout>
        <Head title="Inventory Items" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader 
                    title="Inventory Items" 
                    create-link="/inventory/items/create"
                    create-text="Tambah Item"
                />

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari SKU atau nama..."
                            @keyup.enter="applyFilters"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                        />

                        <select
                            v-model="statusFilter"
                            @change="applyFilters"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                        >
                            <option value="">Semua Status</option>
                            <option value="available">Available</option>
                            <option value="reserved">Reserved</option>
                            <option value="sold">Sold</option>
                            <option value="damaged">Damaged</option>
                            <option value="expired">Expired</option>
                        </select>

                        <select
                            v-model="categoryFilter"
                            @change="applyFilters"
                            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                        >
                            <option value="">Semua Kategori</option>
                            <option value="garment">Garment</option>
                            <option value="food">Food</option>
                        </select>

                        <div class="flex items-center gap-2">
                            <button
                                @click="applyFilters"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                            >
                                Filter
                            </button>
                            <button
                                v-if="search || statusFilter || categoryFilter"
                                @click="clearFilters"
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <span class="text-base">✕</span>
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div v-if="items.data.length > 0" class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Lokasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ item.sku }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div>
                                            {{ item.name }}
                                            <span v-if="isLowStock(item)" class="ml-2 inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">
                                                Low Stock
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ item.inventory_location?.name || '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ item.current_stock }}
                                        <span v-if="item.reserved_stock > 0" class="text-gray-500 dark:text-gray-400">({{ item.reserved_stock }} reserved)</span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ item.quality_grade }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span :class="statusBadgeClass(item.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <Link
                                            :href="`/inventory/items/${item.id}`"
                                            class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500"
                                        >
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-16 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada inventory items</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan item pertama.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
