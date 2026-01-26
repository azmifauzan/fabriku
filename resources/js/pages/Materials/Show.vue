<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Plus, QrCode } from 'lucide-vue-next';
import RestockModal from './Partials/RestockModal.vue';
import { ref } from 'vue';

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
    remaining_quantity?: string;
    status?: string;
    barcode?: string | null;
    usages?: Array<{
        id: number;
        preparation_order_number: string;
        quantity: string;
        date: string;
    }>;
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
    receipts_count?: number;
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

const showRestockModal = ref(false);
const activeTab = ref<'batches' | 'usages'>('batches');
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
                                    <!-- Removed Price, Supplier, Created At as per user request -->
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

                        <!-- Stock History Tabs -->
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                                <div class="flex items-center gap-4">
                                    <button
                                        @click="activeTab = 'batches'"
                                        class="text-sm font-semibold transition-colors"
                                        :class="activeTab === 'batches' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                                    >
                                        Daftar Batch
                                    </button>
                                    <div class="h-4 w-px bg-gray-300 dark:bg-gray-600"></div>
                                    <button
                                        @click="activeTab = 'usages'"
                                        class="text-sm font-semibold transition-colors"
                                        :class="activeTab === 'usages' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200'"
                                    >
                                        Riwayat Penggunaan
                                    </button>
                                </div>
                            </div>

                            <!-- Batches Table -->
                            <div v-if="activeTab === 'batches'" class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Batch Info</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Supplier & Harga</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Qty Awal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Sisa</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Barcode</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr v-for="receipt in material.receipts" :key="receipt.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ receipt.receipt_number }}</div>
                                                <div v-if="receipt.batch_number" class="text-xs text-gray-500">{{ receipt.batch_number }}</div>
                                                <div class="text-xs text-gray-500">{{ formatDate(receipt.receipt_date) }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                 <div class="text-sm font-medium text-gray-900 dark:text-white">{{ receipt.supplier_name }}</div>
                                                 <div class="text-xs text-gray-500">{{ formatCurrency(receipt.price_per_unit) }}</div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                {{ formatNumber(receipt.quantity) }} {{ material.unit }}
                                            </td>
                                            <td class="px-6 py-4 text-sm font-semibold" :class="parseFloat(receipt.remaining_quantity || '0') > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500'">
                                                {{ formatNumber(receipt.remaining_quantity ?? receipt.quantity) }} {{ material.unit }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <span 
                                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                                    :class="receipt.status === 'active' && parseFloat(receipt.remaining_quantity || '0') > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'"
                                                >
                                                    {{ parseFloat(receipt.remaining_quantity || '0') > 0 ? 'Aktif' : 'Habis' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                <div v-if="receipt.barcode" class="flex items-center gap-2">
                                                    <QrCode class="h-4 w-4" />
                                                    <span class="font-mono text-xs">{{ receipt.barcode }}</span>
                                                </div>
                                                <span v-else>-</span>
                                            </td>
                                        </tr>
                                        <tr v-if="!material.receipts?.length">
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                                Belum ada data batch.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Usages Table -->
                            <div v-else class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Kegiatan/Order</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Batch Asal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Qty Digunakan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <!-- Flatten usages from receipts -->
                                        <template v-for="receipt in material.receipts" :key="receipt.id">
                                            <tr v-for="usage in receipt.usages" :key="usage.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ usage.date }}
                                                </td>
                                                <td class="px-6 py-4">
                                                     <Link :href="`/preparation-orders`" class="text-indigo-600 hover:underline dark:text-indigo-400">
                                                        {{ usage.preparation_order_number }}
                                                     </Link>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                                    {{ receipt.batch_number || receipt.receipt_number }}
                                                </td>
                                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                                    {{ formatNumber(usage.quantity) }} {{ material.unit }}
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="!material.receipts?.some(r => r.usages?.length)">
                                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                                Belum ada riwayat penggunaan.
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
                                                Stok berada di bawah batas minimum ({{ formatNumber(material.min_stock) }})
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
                                
                                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                                    <button 
                                        @click="showRestockModal = true"
                                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition-all hover:bg-indigo-700 active:bg-indigo-800"
                                    >
                                        <Plus class="h-4 w-4" />
                                        Restock Barang
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <RestockModal
            :show="showRestockModal"
            :material-id="material.id"
            :supplier-name="material.supplier_name"
            :current-price="material.price_per_unit"
            @close="showRestockModal = false"
        />
    </AppLayout>
</template>
