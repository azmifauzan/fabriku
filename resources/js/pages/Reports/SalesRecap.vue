<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { FileBarChart, Filter, Search, TrendingUp, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface RecapItem {
    customer_id: number;
    customer_name: string;
    total_orders: number;
    total_revenue: number;
    total_paid: number;
    outstanding: number;
}

interface Summary {
    total_customers: number;
    total_revenue: number;
    total_paid: number;
    total_outstanding: number;
}

interface DefaultDates {
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    recap: RecapItem[];
    summary: Summary;
    filters: {
        search?: string;
        start_date?: string;
        end_date?: string;
    };
    defaultDates: DefaultDates;
}>();

const search = ref(props.filters.search || '');
const startDate = ref(props.filters.start_date || props.defaultDates.start_date);
const endDate = ref(props.filters.end_date || props.defaultDates.end_date);

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const applyFilter = () => {
    router.get(
        '/reports/sales-recap',
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
</script>

<template>
    <AppLayout>
        <Head title="Rekapitulasi Penjualan" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <Users :size="24" class="text-indigo-500" />
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Rekapitulasi Penjualan per Customer</h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ringkasan transaksi per pelanggan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customer</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ summary.total_customers }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(summary.total_revenue) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Terbayar</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">
                            {{ formatCurrency(summary.total_paid) }}
                        </dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Outstanding</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">
                            {{ formatCurrency(summary.total_outstanding) }}
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
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search :size="16" class="text-gray-400" />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Nama customer..."
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
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Order
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Revenue
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total Terbayar
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Outstanding
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="recap.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data penjualan</td>
                                </tr>
                                <tr v-for="item in recap" :key="item.customer_id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ item.customer_name }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ item.total_orders }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(item.total_revenue) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-green-600 dark:text-green-400">
                                        {{ formatCurrency(item.total_paid) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap text-red-600 dark:text-red-400">
                                        {{ formatCurrency(item.outstanding) }}
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
