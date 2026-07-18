<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber } from '@/lib/utils';
import { Head, router } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Download, FileBarChart, FileSpreadsheet, Filter, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface PurchaseItem {
    sku: string;
    name: string;
    quantity: number;
    unit_cost: number;
    total_cost: number;
}

interface Batch {
    batch_id: string;
    date: string;
    supplier_name: string;
    purchase_invoice: string | null;
    notes: string | null;
    adjusted_by: string;
    item_count: number;
    total_qty: number;
    total_cost: number;
    items: PurchaseItem[];
}

interface Summary {
    total_transactions: number;
    total_items_purchased: number;
    total_spend: number;
}

interface Filters {
    search?: string;
    start_date?: string;
    end_date?: string;
}

interface DefaultDates {
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    batches: Batch[];
    summary: Summary;
    filters: Filters;
    defaultDates: DefaultDates;
}>();

const search = ref(props.filters.search || '');
const startDate = ref(props.filters.start_date || props.defaultDates.start_date);
const endDate = ref(props.filters.end_date || props.defaultDates.end_date);
const showExportMenu = ref(false);
const expandedBatches = ref<Record<string, boolean>>({});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const applyFilter = () => {
    router.get(
        '/reports/purchase',
        {
            search: search.value,
            start_date: startDate.value,
            end_date: endDate.value,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const resetFilter = () => {
    search.value = '';
    startDate.value = props.defaultDates.start_date;
    endDate.value = props.defaultDates.end_date;
    applyFilter();
};

const exportReport = (format: 'pdf' | 'excel') => {
    const params = new URLSearchParams({
        format,
        ...(search.value && { search: search.value }),
        ...(startDate.value && { start_date: startDate.value }),
        ...(endDate.value && { end_date: endDate.value }),
    });
    window.location.href = `/reports/purchase/export?${params.toString()}`;
    showExportMenu.value = false;
};

const toggleBatch = (batchId: string) => {
    expandedBatches.value[batchId] = !expandedBatches.value[batchId];
};
</script>

<template>
    <AppLayout>
        <Head title="Laporan Pembelian" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <FileBarChart :size="24" class="text-indigo-500" />
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Pembelian</h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ringkasan pembelian barang jadi dari supplier</p>
                                </div>
                            </div>
                            <div class="relative">
                                <button
                                    type="button"
                                    @click="showExportMenu = !showExportMenu"
                                    class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    <Download :size="16" />
                                    Export
                                </button>
                                <div
                                    v-if="showExportMenu"
                                    class="ring-opacity-5 absolute right-0 z-10 mt-2 w-48 rounded-md bg-white py-1 shadow-lg ring-1 ring-black dark:bg-gray-800"
                                >
                                    <button
                                        type="button"
                                        @click="exportReport('pdf')"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        <Download :size="16" class="text-red-500" />
                                        Export PDF
                                    </button>
                                    <button
                                        type="button"
                                        @click="exportReport('excel')"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        <FileSpreadsheet :size="16" class="text-green-500" />
                                        Export Excel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transaksi</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatNumber(summary.total_transactions) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Qty Dibeli</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatNumber(summary.total_items_purchased) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai Pembelian</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(summary.total_spend) }}
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
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                            <div class="lg:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search :size="16" class="text-gray-400" />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Cari Supplier, Invoice, SKU, atau Nama Produk..."
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        @keyup.enter="applyFilter"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Dari Tanggal </label>
                                <input
                                    v-model="startDate"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Sampai Tanggal </label>
                                <input
                                    v-model="endDate"
                                    type="date"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div class="flex items-end gap-2">
                                <button
                                    type="button"
                                    @click="applyFilter"
                                    class="flex-1 cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Terapkan
                                </button>
                                <button
                                    type="button"
                                    @click="resetFilter"
                                    class="cursor-pointer rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
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
                                    <th class="w-10 px-6 py-3"></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        No. Invoice
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Supplier
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Item Unik
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Qty
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Nilai
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Pencatat
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="batches.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data pembelian</td>
                                </tr>
                                <template v-for="batch in batches" :key="batch.batch_id">
                                    <!-- Main Row -->
                                    <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-center whitespace-nowrap">
                                            <button
                                                type="button"
                                                @click="toggleBatch(batch.batch_id)"
                                                class="cursor-pointer text-gray-500 hover:text-indigo-600 focus:outline-none"
                                            >
                                                <ChevronUp v-if="expandedBatches[batch.batch_id]" :size="18" />
                                                <ChevronDown v-else :size="18" />
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ batch.date }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ batch.purchase_invoice || '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ batch.supplier_name }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ batch.item_count }}
                                        </td>
                                        <td class="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ batch.total_qty }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ formatCurrency(batch.total_cost) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                            {{ batch.adjusted_by }}
                                        </td>
                                    </tr>

                                    <!-- Collapsible Detail Row -->
                                    <tr v-if="expandedBatches[batch.batch_id]">
                                        <td colspan="8" class="bg-gray-50/50 p-4 dark:bg-gray-900/40">
                                            <div
                                                class="rounded-lg border border-gray-200 bg-white p-4 shadow-inner dark:border-gray-700 dark:bg-gray-800"
                                            >
                                                <h4 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Detail Pembelian Barang</h4>

                                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                                                        <tr>
                                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                                SKU
                                                            </th>
                                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                                Nama Produk
                                                            </th>
                                                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                                Quantity
                                                            </th>
                                                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                                Harga Beli
                                                            </th>
                                                            <th class="px-4 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                                Total
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                        <tr
                                                            v-for="item in batch.items"
                                                            :key="item.sku"
                                                            class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50"
                                                        >
                                                            <td class="px-4 py-2 font-mono text-sm text-gray-700 dark:text-gray-300">
                                                                {{ item.sku }}
                                                            </td>
                                                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-white">
                                                                {{ item.name }}
                                                            </td>
                                                            <td class="px-4 py-2 text-center text-sm text-gray-700 dark:text-gray-300">
                                                                {{ formatNumber(item.quantity) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-right text-sm text-gray-700 dark:text-gray-300">
                                                                {{ formatCurrency(item.unit_cost) }}
                                                            </td>
                                                            <td class="px-4 py-2 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                                {{ formatCurrency(item.total_cost) }}
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <div
                                                    v-if="batch.notes"
                                                    class="mt-3 rounded border border-gray-100 bg-gray-50 p-2.5 text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
                                                >
                                                    <strong>Catatan:</strong> {{ batch.notes }}
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
