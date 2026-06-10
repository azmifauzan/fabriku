<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Download, FileBarChart, FileSpreadsheet, Search } from 'lucide-vue-next';
import { ref } from 'vue';

interface ServiceRow {
    code: string;
    name: string;
    category: string | null;
    total_sold: number;
    total_revenue: number;
}

interface StaffRow {
    staff_name: string;
    total_services: number;
    total_revenue: number;
}

interface Summary {
    total_service_types: number;
    total_services_sold: number;
    total_revenue: number;
}

interface Filters {
    start_date?: string;
    end_date?: string;
}

interface DefaultDates {
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    services: ServiceRow[];
    staffRecap: StaffRow[];
    summary: Summary;
    filters: Filters;
    defaultDates: DefaultDates;
}>();

const startDate = ref(props.filters.start_date || props.defaultDates.start_date);
const endDate = ref(props.filters.end_date || props.defaultDates.end_date);
const showExportMenu = ref(false);

const formatCurrency = (amount: number) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);

const applyFilter = () => {
    router.get('/reports/service', { start_date: startDate.value, end_date: endDate.value }, { preserveState: true, preserveScroll: true });
};

const resetFilter = () => {
    startDate.value = props.defaultDates.start_date;
    endDate.value = props.defaultDates.end_date;
    applyFilter();
};

const exportReport = (format: 'pdf' | 'excel') => {
    const params = new URLSearchParams({
        format,
        ...(startDate.value && { start_date: startDate.value }),
        ...(endDate.value && { end_date: endDate.value }),
    });
    window.location.href = `/reports/service/export?${params.toString()}`;
    showExportMenu.value = false;
};
</script>

<template>
    <AppLayout>
        <Head title="Laporan Layanan" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <FileBarChart :size="24" class="text-indigo-500" />
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Layanan</h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Rekap penjualan jasa/layanan per periode</p>
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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Layanan Terjual</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ summary.total_service_types }}</dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Layanan Terjual</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ summary.total_services_sold }}</dd>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">{{ formatCurrency(summary.total_revenue) }}</dd>
                    </div>
                </div>

                <!-- Filter -->
                <div class="rounded-lg bg-white p-4 shadow-sm dark:bg-gray-800">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Dari Tanggal</label>
                            <input
                                v-model="startDate"
                                type="date"
                                class="mt-1 rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Sampai Tanggal</label>
                            <input
                                v-model="endDate"
                                type="date"
                                class="mt-1 rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <button
                            type="button"
                            @click="applyFilter"
                            class="flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            <Search :size="16" />
                            Terapkan
                        </button>
                        <button
                            type="button"
                            @click="resetFilter"
                            class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Services Table -->
                <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Kode
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Layanan
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Kategori
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Terjual
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Pendapatan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="service in services" :key="service.code">
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ service.code }}</td>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ service.name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ service.category || '-' }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-white">{{ service.total_sold }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(service.total_revenue) }}
                                </td>
                            </tr>
                            <tr v-if="services.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-400">Tidak ada data layanan pada periode ini</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Staff Recap -->
                <div v-if="staffRecap.length > 0" class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Rekap per Staff</h3>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Staff
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Jumlah Layanan
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Pendapatan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="staff in staffRecap" :key="staff.staff_name">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ staff.staff_name }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-white">{{ staff.total_services }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(staff.total_revenue) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
