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
                <PageHeader title="Inventory Items" />

                <!-- Filters & Actions -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-1 flex-col gap-4 sm:flex-row">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari SKU atau nama..."
                            @keyup.enter="applyFilters"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                        />

                        <select
                            v-model="statusFilter"
                            @change="applyFilters"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto"
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
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto"
                        >
                            <option value="">Semua Kategori</option>
                            <option value="garment">Garment</option>
                            <option value="food">Food</option>
                        </select>

                        <button
                            v-if="search || statusFilter || categoryFilter"
                            @click="clearFilters"
                            class="rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Reset
                        </button>
                    </div>

                    <Link
                        href="/inventory/items/create"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Tambah Item
                    </Link>
                </div>

                <!-- Items Table -->
                <div v-if="items.data.length > 0" class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
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
                <div v-else class="rounded-lg bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada inventory items</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan item pertama.</p>
                    <div class="mt-6">
                        <Link
                            href="/inventory/items/create"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                        >
                            Tambah Item
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
