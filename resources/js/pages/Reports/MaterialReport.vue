<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { AlertTriangle, FileBarChart, Filter, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface Material {
    id: number;
    code: string;
    name: string;
    type: string;
    current_stock: number;
    min_stock: number;
    unit: string;
    price_per_unit: number;
    total_received: number;
    total_used: number;
    total_cost: number;
    average_price: number;
    receipts_count: number;
    is_low_stock: boolean;
}

interface Filters {
    material_type?: string;
    search?: string;
    start_date?: string;
    end_date?: string;
}

interface Summary {
    total_items: number;
    total_stock_value: number;
    total_received: number;
    total_used: number;
    low_stock_count: number;
}

const props = defineProps<{
    materials: Material[];
    summary: Summary;
    filters: Filters;
}>();

const search = ref(props.filters.search || '');
const materialType = ref(props.filters.material_type || '');
const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');



const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatNumber = (value: number, decimals: number = 2) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: decimals,
    }).format(value);
};

const applyFilter = () => {
    router.get(
        '/reports/material',
        {
            search: search.value,
            material_type: materialType.value,
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
    materialType.value = '';
    startDate.value = '';
    endDate.value = '';
    applyFilter();
};
</script>

<template>
    <AppLayout>
        <Head title="Laporan Material" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <FileBarChart :size="24" class="text-indigo-500" />
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Material</h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ringkasan penerimaan dan penggunaan material</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Material</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ summary.total_items }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nilai Stock Saat Ini</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(summary.total_stock_value) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Diterima</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
                            {{ formatNumber(summary.total_received) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Digunakan</dt>
                        <dd class="mt-1 text-2xl font-semibold text-orange-600 dark:text-orange-400">
                            {{ formatNumber(summary.total_used) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
                            <AlertTriangle v-if="summary.low_stock_count > 0" :size="16" class="text-red-500" />
                            Low Stock
                        </dt>
                        <dd
                            class="mt-1 text-2xl font-semibold"
                            :class="summary.low_stock_count > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
                        >
                            {{ summary.low_stock_count }}
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
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search :size="16" class="text-gray-400" />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Nama atau kode..."
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        @keyup.enter="applyFilter"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Tipe Material </label>
                                <select
                                    v-model="materialType"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Tipe</option>
                                    <option value="fabric">Kain</option>
                                    <option value="thread">Benang</option>
                                    <option value="accessory">Aksesoris</option>
                                    <option value="other">Lainnya</option>
                                </select>
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
                                        Kode
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama Material
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Tipe
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Stok Saat Ini
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Diterima
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Digunakan
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Biaya
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Harga Rata-rata
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="materials.length === 0">
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data material</td>
                                </tr>
                                <tr
                                    v-for="material in materials"
                                    :key="material.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                    :class="{ 'bg-red-50 dark:bg-red-900/20': material.is_low_stock }"
                                >
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ material.code }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-900 dark:text-white">{{ material.name }}</span>
                                            <span
                                                v-if="material.is_low_stock"
                                                class="inline-flex items-center rounded bg-red-100 px-1.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900 dark:text-red-200"
                                            >
                                                Low
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                                        >
                                            {{ material.type || '-' }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap"
                                        :class="material.is_low_stock ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
                                    >
                                        {{ formatNumber(material.current_stock) }} {{ material.unit }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-green-600 dark:text-green-400">
                                        +{{ formatNumber(material.total_received) }} {{ material.unit }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-orange-600 dark:text-orange-400">
                                        -{{ formatNumber(material.total_used) }} {{ material.unit }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(material.total_cost) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(material.average_price) }}/{{ material.unit }}
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
