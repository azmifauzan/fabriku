<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import QrScanner from '@/components/QrScanner.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber } from '@/lib/utils';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, History, RefreshCw } from 'lucide-vue-next';
import { ref } from 'vue';
import AdjustStockModal from './AdjustStockModal.vue';

interface Item {
    id: number;
    sku: string;
    name: string;
    category?: {
        id: number;
        name: string;
    };
    current_stock: number;
    reserved_stock: number;
    target_quantity?: number;
    unit_cost: number;
    selling_price: number;
    status: string;
    inventory_location?: {
        id: number;
        name: string;
    };
    production_order?: {
        id: number;
        order_number: string;
    };
    expiry_date?: string;
    source_type?: string;
}

interface Category {
    id: number;
    name: string;
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
        category_id?: number | string;
        location_id?: number;
    };
    categories: Category[];
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const categoryFilter = ref(props.filters.category_id || '');

const applyFilters = () => {
    router.get(
        '/inventory/items',
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            category_id: categoryFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
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
    return item.current_stock <= 0;
};

const showAdjustModal = ref(false);
const selectedItem = ref<Item | null>(null);

const openAdjustModal = (item: Item) => {
    selectedItem.value = item;
    showAdjustModal.value = true;
};

const adjustmentTypes = {
    opening_balance: 'Stock Awal',
    correction: 'Koreksi',
    damage: 'Rusak',
    loss: 'Hilang',
    found: 'Ditemukan',
    return: 'Retur',
};
</script>

<template>
    <AppLayout>
        <Head title="Inventory Items" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Inventory Items"
                    description="Kelola barang jadi hasil produksi"
                    create-link="/inventory/items/create"
                    create-text="Tambah Item"
                >
                    <template #actions>
                        <QrScanner v-slot="{ startScanning }">
                            <button
                                @click="startScanning"
                                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                    />
                                </svg>
                                Scan QR Code
                            </button>
                        </QrScanner>
                        <Link
                            href="/inventory/items/create"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow-md"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="whitespace-nowrap">Tambah Item</span>
                        </Link>
                    </template>
                </PageHeader>

                <!-- Filters -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari SKU atau nama..."
                                @keyup.enter="applyFilters"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="statusFilter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="available">Available</option>
                                <option value="reserved">Reserved</option>
                                <option value="sold">Sold</option>
                                <option value="damaged">Damaged</option>
                                <option value="expired">Expired</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                            <select
                                v-model="categoryFilter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                @click="applyFilters"
                                class="flex-1 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                            >
                                Filter
                            </button>
                            <button
                                v-if="search || statusFilter || categoryFilter"
                                @click="clearFilters"
                                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                title="Clear filters"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div
                    v-if="items.data.length > 0"
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Nama Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Lokasi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Stock</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="item in items.data" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {{ item.sku }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        <div>
                                            {{ item.name }}
                                            <span
                                                v-if="isLowStock(item)"
                                                class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/30 dark:text-red-300"
                                            >
                                                Low Stock
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {{ item.inventory_location?.name || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ formatNumber(item.current_stock) }}</span>
                                            <span v-if="item.reserved_stock > 0" class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatNumber(item.reserved_stock) }} reserved
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="statusBadgeClass(item.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <Link
                                                :href="`/inventory/items/${item.id}`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Lihat detail item"
                                            >
                                                <Eye :size="18" />
                                            </Link>
                                            <Link
                                                :href="`/inventory/items/${item.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Edit Item"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button
                                                type="button"
                                                @click="openAdjustModal(item)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Adjust Stock"
                                            >
                                                <RefreshCw :size="18" />
                                            </button>
                                            <Link
                                                :href="`/inventory/items/${item.id}/adjustments`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700"
                                                title="Riwayat"
                                            >
                                                <History :size="18" />
                                            </Link>
                                            <Link
                                                :href="`/inventory/items/${item.id}/qrcode/print`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700"
                                                title="Print QR Code"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"
                                                    />
                                                </svg>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="px-6 py-16 text-center">
                        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                            />
                        </svg>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada inventory items</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan item pertama.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adjust Stock Modal -->
        <AdjustStockModal
            v-if="selectedItem"
            :show="showAdjustModal"
            :item="selectedItem"
            :adjustment-types="adjustmentTypes"
            @close="showAdjustModal = false"
        />
    </AppLayout>
</template>
