<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Item {
    id: number;
    sku: string;
    name: string;
    description?: string;
    category: string;
    current_stock: number;
    reserved_stock: number;
    minimum_stock: number;
    unit_cost: number;
    selling_price: number;
    quality_grade: string;
    status: string;
    attributes?: Record<string, any>;
    batch_number?: string;
    production_date?: string;
    expiry_date?: string;
    best_before_date?: string;
    notes?: string;
    inventory_location?: {
        id: number;
        name: string;
        zone: string;
        rack: string;
    };
    pattern?: {
        id: number;
        name: string;
        code: string;
    };
}

interface Props {
    item: Item;
}

const props = defineProps<Props>();

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

const isLowStock = () => {
    return props.item.current_stock <= props.item.minimum_stock;
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('id-ID');
};
</script>

<template>
    <AppLayout>
        <Head :title="`Item: ${item.sku}`" />

        <PageHeader :title="`${item.name} (${item.sku})`" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <Link href="/inventory/items" class="text-sm font-medium text-gray-600 hover:text-gray-900"> ← Kembali ke Inventory </Link>
                    <Link
                        :href="`/inventory/items/${item.id}/edit`"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Edit Item
                    </Link>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Item Details -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm lg:col-span-2">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Informasi Item</h3>
                                <span :class="statusBadgeClass(item.status)" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                    {{ item.status }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">SKU</dt>
                                    <dd class="mt-1 font-mono text-sm text-gray-900">{{ item.sku }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                                    <dd class="mt-1 text-sm text-gray-900 capitalize">{{ item.category }}</dd>
                                </div>
                                <div v-if="item.pattern" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Pattern/Resep</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ item.pattern.name }} ({{ item.pattern.code }})</dd>
                                </div>
                                <div v-if="item.description" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ item.description }}</dd>
                                </div>
                                <div v-if="item.attributes && Object.keys(item.attributes).length > 0" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Atribut</dt>
                                    <dd class="mt-1">
                                        <dl class="grid grid-cols-2 gap-2">
                                            <div v-for="(value, key) in item.attributes" :key="key">
                                                <dt class="text-xs text-gray-500 capitalize">{{ key }}</dt>
                                                <dd class="text-sm text-gray-900">{{ value }}</dd>
                                            </div>
                                        </dl>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Quality Grade</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ item.quality_grade }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Batch Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ item.batch_number || '-' }}</dd>
                                </div>
                                <div v-if="item.inventory_location" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Lokasi</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ item.inventory_location.name }} (Zona {{ item.inventory_location.zone }}, Rak
                                        {{ item.inventory_location.rack }})
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tanggal Produksi</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(item.production_date) }}</dd>
                                </div>
                                <div v-if="item.category === 'food'">
                                    <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(item.expiry_date) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Harga Modal</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Rp {{ item.unit_cost.toLocaleString('id-ID') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Harga Jual</dt>
                                    <dd class="mt-1 text-sm text-gray-900">Rp {{ item.selling_price.toLocaleString('id-ID') }}</dd>
                                </div>
                                <div v-if="item.notes" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ item.notes }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Stock Info -->
                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                            <div class="border-b border-gray-200 px-6 py-4">
                                <h3 class="text-lg font-medium text-gray-900">Stok</h3>
                            </div>
                            <div class="p-6">
                                <dl class="space-y-4">
                                    <div class="rounded-lg bg-blue-50 p-4">
                                        <dt class="text-sm font-medium text-blue-600">Current Stock</dt>
                                        <dd class="mt-1 flex items-baseline">
                                            <span class="text-2xl font-bold text-blue-900">{{ item.current_stock }}</span>
                                            <span v-if="isLowStock()" class="ml-2 text-xs text-red-600">⚠ Low Stock</span>
                                        </dd>
                                    </div>
                                    <div class="rounded-lg bg-yellow-50 p-4">
                                        <dt class="text-sm font-medium text-yellow-600">Reserved</dt>
                                        <dd class="mt-1 text-2xl font-bold text-yellow-900">{{ item.reserved_stock }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-green-50 p-4">
                                        <dt class="text-sm font-medium text-green-600">Available</dt>
                                        <dd class="mt-1 text-2xl font-bold text-green-900">{{ availableStock() }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-4">
                                        <dt class="text-sm font-medium text-gray-600">Minimum Stock</dt>
                                        <dd class="mt-1 text-2xl font-bold text-gray-900">{{ item.minimum_stock }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
