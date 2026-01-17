<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';

interface MaterialAttribute {
    id: number;
    attribute_key: string;
    attribute_value: string;
}

interface MaterialReceipt {
    id: number;
    receipt_number: string;
    supplier_name: string;
    quantity: string;
    unit: string;
    price_per_unit: string;
    total_cost: string;
    receipt_date: string;
    batch_number: string | null;
    expired_date: string | null;
    notes: string | null;
}

interface Material {
    id: number;
    code: string;
    name: string;
    material_type_id: number;
    unit: string;
    price_per_unit: string;
    stock_quantity: string;
    min_stock: string;
    supplier_name: string | null;
    description: string | null;
    created_at: string;
    updated_at: string;
    receipts?: MaterialReceipt[];
    materialAttributes?: MaterialAttribute[];
    materialType?: {
        id: number;
        name: string;
        code: string;
    };
}

const props = defineProps<{
    material: Material;
}>();

const formatCurrency = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(typeof value === 'string' ? parseFloat(value) : value);
};

const formatNumber = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(typeof value === 'string' ? parseFloat(value) : value);
};

const isLowStock = () => {
    return parseFloat(props.material.stock_quantity) <= parseFloat(props.material.min_stock);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="`Detail Bahan: ${material.name}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ material.name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            Kode: <span class="font-semibold">{{ material.code }}</span>
                        </p>
                    </div>
                    <Link
                        href="/materials"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Material Information -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Informasi Bahan</h3>
                                    <Link
                                        :href="`/materials/${material.id}/edit`"
                                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 dark:focus:ring-offset-gray-800"
                                    >
                                        Edit
                                    </Link>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Bahan</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ material.code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ material.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Bahan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ material.materialType?.name || '-' }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Satuan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ material.unit }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Harga Per Unit</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ formatCurrency(material.price_per_unit) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ material.supplier_name || '-' }}
                                        </dd>
                                    </div>
                                    <div v-if="material.description" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</dt>
                                        <dd class="mt-1 text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">
                                            {{ material.description }}
                                        </dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat pada</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ formatDate(material.created_at) }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Attributes -->
                        <div
                            v-if="material.materialAttributes && material.materialAttributes.length > 0"
                            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Atribut Tambahan</h3>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div v-for="attr in material.materialAttributes" :key="attr.id">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ attr.attribute_key }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ attr.attribute_value }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Recent Receipts -->
                        <div v-if="material.receipts && material.receipts.length > 0" class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Penerimaan Terbaru</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                Nomor
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                Supplier
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                Qty
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                Total
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                                Tanggal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr v-for="receipt in material.receipts" :key="receipt.id" class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                                {{ receipt.receipt_number }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                {{ receipt.supplier_name }}
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                                {{ formatNumber(receipt.quantity) }} {{ receipt.unit }}
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                                {{ formatCurrency(receipt.total_cost) }}
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                                {{ formatDate(receipt.receipt_date) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Stock Status -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Status Stok</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Stok Saat Ini</dt>
                                        <dd class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                            {{ formatNumber(material.stock_quantity) }} {{ material.unit }}
                                        </dd>
                                    </div>
                                </div>

                                <div v-if="isLowStock()" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-900/30 dark:bg-yellow-900/20">
                                    <div class="flex gap-3">
                                        <AlertTriangle class="h-5 w-5 text-yellow-600 dark:text-yellow-500 flex-shrink-0" />
                                        <div>
                                            <p class="text-sm font-semibold text-yellow-800 dark:text-yellow-200">Stok Rendah</p>
                                            <p class="mt-1 text-xs text-yellow-700 dark:text-yellow-300">
                                                Stok berada di bawah batas minimum ({{ formatNumber(material.reorder_point) }})
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Minimum Stok</span>
                                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ formatNumber(material.min_stock) }} {{ material.unit }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
