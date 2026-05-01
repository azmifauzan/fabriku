<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { History, RefreshCw } from 'lucide-vue-next';
import AdjustStockModal from './AdjustStockModal.vue';

interface Pattern {
    id: number;
    name: string;
    code: string;
}

interface PreparationOrder {
    id: number;
    pattern: Pattern;
    output_quantity: number;
    output_unit: string;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    preparation_order?: PreparationOrder;
    labor_cost: string;
    completed_date?: string;
    estimated_completion_date?: string;
    status: string;
}

interface InventoryLocation {
    id: number;
    name: string;
    code: string;
}

interface ItemCategory {
    id: number;
    name: string;
}

interface Item {
    id: number;
    sku: string;
    name: string;
    description?: string;
    category?: ItemCategory;
    source_type?: string;
    source_label?: string;
    current_stock: number;
    reserved_stock: number;
    target_quantity: number;
    unit_cost: number;
    selling_price: number;
    status: string;
    expiry_date?: string;
    notes?: string;
    image_url?: string;
    inventory_location?: InventoryLocation;
    production_order?: ProductionOrder;
}

interface Props {
    item: Item;
    adjustmentTypes?: Record<string, string>;
}

const props = defineProps<Props>();

// Default adjustment types if not passed from server
const adjustmentTypes = props.adjustmentTypes || {
    opening_balance: 'Stock Awal',
    correction: 'Koreksi',
    damage: 'Rusak',
    loss: 'Hilang',
    found: 'Ditemukan',
    return: 'Retur',
};

const showAdjustModal = ref(false);

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

const availableStock = () => {
    return props.item.current_stock - props.item.reserved_stock;
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    });
};

const productionUnit = () => {
    return props.item.production_order?.preparation_order?.output_unit || 'pcs';
};
</script>

<template>
    <AppLayout>
        <Head :title="`${item.name} (${item.sku})`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <Link href="/inventory/items" class="mb-2 inline-flex items-center text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                            ← Kembali ke Inventory
                        </Link>
                        <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl dark:text-white">{{ item.name }}</h1>
                        <p class="mt-1 font-mono text-sm text-gray-500 dark:text-gray-400">{{ item.sku }}</p>
                        <p v-if="item.source_label" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Sumber: {{ item.source_label }}
                        </p>
                    </div>
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <Link
                            :href="`/inventory/items/${item.id}/qrcode/print`"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 sm:px-4 sm:py-2.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            Print QR Code
                        </Link>
                        <Link
                            :href="`/inventory/items/${item.id}/adjustments`"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 sm:px-4 sm:py-2.5 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            <History class="h-4 w-4" />
                            Riwayat
                        </Link>
                        <button
                            @click="showAdjustModal = true"
                            class="inline-flex items-center gap-2 rounded-lg border border-indigo-300 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 shadow-sm transition-all hover:bg-indigo-100 sm:px-4 sm:py-2.5 dark:border-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                        >
                            <RefreshCw class="h-4 w-4" />
                            Adjust Stock
                        </button>
                        <Link
                            :href="`/inventory/items/${item.id}/edit`"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-500 sm:px-4 sm:py-2.5"
                        >
                            Edit Item
                        </Link>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Item Details -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Production Order Info -->
                        <div v-if="item.production_order" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 bg-indigo-50 px-6 py-4 dark:border-gray-700 dark:bg-indigo-900/20">
                                <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-300">Informasi Production Order</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Nomor Order</p>
                                        <p class="mt-1 font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ item.production_order.order_number }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Produk</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ item.production_order.preparation_order?.pattern?.name || '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                                        <span class="mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" 
                                              :class="item.production_order.status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'">
                                            {{ item.production_order.status === 'completed' ? 'Selesai' : 'Terkirim' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tanggal Selesai</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatDate(item.production_order.completed_date || item.production_order.estimated_completion_date) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Target Produksi</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ item.target_quantity }} {{ productionUnit() }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Biaya Tenaga Kerja</p>
                                        <p class="mt-1 text-sm font-medium text-gray-900 dark:text-white">
                                            Rp {{ parseFloat(item.production_order.labor_cost || '0').toLocaleString('id-ID') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Info -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Item</h3>
                                    <span :class="statusBadgeClass(item.status)" class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold">
                                        {{ item.status }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <!-- Product Image -->
                                    <div v-if="item.image_url" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Foto Produk</dt>
                                        <dd class="mt-2">
                                            <img :src="item.image_url" :alt="item.name" class="h-48 w-full rounded-lg border border-gray-200 object-cover shadow-sm dark:border-gray-700" />
                                        </dd>
                                    </div>
                                    
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SKU</dt>
                                        <dd class="mt-1 font-mono text-sm font-semibold text-gray-900 dark:text-white">{{ item.sku }}</dd>
                                    </div>
                                    <div v-if="item.category">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kategori</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ item.category.name }}</dd>
                                    </div>
                                    <div v-if="item.inventory_location">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Lokasi Penyimpanan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                                            {{ item.inventory_location.name }} ({{ item.inventory_location.code }})
                                        </dd>
                                    </div>
                                    <div v-if="item.expiry_date">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Kadaluarsa</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(item.expiry_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Harga Modal</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">Rp {{ item.unit_cost.toLocaleString('id-ID') }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Harga Jual</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">Rp {{ item.selling_price.toLocaleString('id-ID') }}</dd>
                                    </div>
                                    <div v-if="item.description" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ item.description }}</dd>
                                    </div>
                                    <div v-if="item.notes" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ item.notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Stock Info -->
                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Stok</h3>
                            </div>
                            <div class="p-6">
                                <dl class="space-y-4">
                                    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                        <dt class="text-sm font-medium text-blue-700 dark:text-blue-400">Current Stock</dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <span class="text-3xl font-bold text-blue-900 dark:text-blue-200">{{ item.current_stock }}</span>
                                            <span class="ml-2 text-sm text-blue-700 dark:text-blue-400">{{ productionUnit() }}</span>
                                        </dd>
                                    </div>
                                    <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/20">
                                        <dt class="text-sm font-medium text-yellow-700 dark:text-yellow-400">Reserved</dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <span class="text-3xl font-bold text-yellow-900 dark:text-yellow-200">{{ item.reserved_stock }}</span>
                                            <span class="ml-2 text-sm text-yellow-700 dark:text-yellow-400">{{ productionUnit() }}</span>
                                        </dd>
                                    </div>
                                    <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                                        <dt class="text-sm font-medium text-green-700 dark:text-green-400">Available</dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <span class="text-3xl font-bold text-green-900 dark:text-green-200">{{ availableStock() }}</span>
                                            <span class="ml-2 text-sm text-green-700 dark:text-green-400">{{ productionUnit() }}</span>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adjust Stock Modal -->
        <AdjustStockModal
            :show="showAdjustModal"
            :item="item"
            :adjustment-types="adjustmentTypes"
            @close="showAdjustModal = false"
        />
    </AppLayout>
</template>
