<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { FileBarChart, Filter, Search, TrendingUp } from 'lucide-vue-next';
import { ref } from 'vue';

interface Order {
    id: number;
    order_number: string;
    order_date: string;
    customer_name: string;
    customer_type: string;
    total_items: number;
    subtotal: number;
    discount: number;
    total_amount: number;
    payment_status: string;
    status: string;
}

interface Summary {
    total_orders: number;
    total_revenue: number;
    total_discount: number;
    total_items_sold: number;
    completed_orders: number;
    pending_orders: number;
}

interface RevenueByType {
    [key: string]: {
        count: number;
        total: number;
    };
}

interface Filters {
    status?: string;
    customer_type?: string;
    search?: string;
    start_date?: string;
    end_date?: string;
}

interface DefaultDates {
    start_date: string;
    end_date: string;
}

const props = defineProps<{
    orders: Order[];
    summary: Summary;
    revenueByType: RevenueByType;
    filters: Filters;
    defaultDates: DefaultDates;
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const customerType = ref(props.filters.customer_type || '');
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
        '/reports/sales',
        {
            search: search.value,
            status: status.value,
            customer_type: customerType.value,
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
    status.value = '';
    customerType.value = '';
    startDate.value = props.defaultDates.start_date;
    endDate.value = props.defaultDates.end_date;
    applyFilter();
};

const getStatusClass = (orderStatus: string) => {
    const classes: Record<string, string> = {
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
    };
    return classes[orderStatus] || classes.draft;
};

const getPaymentStatusClass = (paymentStatus: string) => {
    const classes: Record<string, string> = {
        paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        unpaid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[paymentStatus] || classes.unpaid;
};
</script>

<template>
    <AppLayout>
        <Head title="Laporan Penjualan" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="flex items-center gap-3">
                            <FileBarChart :size="24" class="text-indigo-500" />
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Penjualan</h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ringkasan penjualan dan revenue</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ summary.total_orders }}
                        </dd>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ summary.completed_orders }} completed, {{ summary.pending_orders }} pending
                        </p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(summary.total_revenue) }}
                        </dd>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Discount: {{ formatCurrency(summary.total_discount) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items Sold</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ summary.total_items_sold }}
                        </dd>
                    </div>
                </div>

                <!-- Revenue by Customer Type -->
                <div v-if="Object.keys(revenueByType).length > 0" class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-4 flex items-center gap-2">
                            <TrendingUp :size="20" class="text-indigo-500" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Revenue by Customer Type</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div v-for="(data, type) in revenueByType" :key="type" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 capitalize dark:text-gray-400">
                                    {{ type }}
                                </dt>
                                <dd class="mt-1 text-xl font-semibold text-gray-900 dark:text-white">
                                    {{ formatCurrency(data.total) }}
                                </dd>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ data.count }} orders</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-4 flex items-center gap-2">
                            <Filter :size="20" class="text-gray-500" />
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter</h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <Search :size="16" class="text-gray-400" />
                                    </div>
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Order # atau customer..."
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        @keyup.enter="applyFilter"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Status </label>
                                <select
                                    v-model="status"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300"> Tipe Customer </label>
                                <select
                                    v-model="customerType"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Tipe</option>
                                    <option value="retail">Retail</option>
                                    <option value="reseller">Reseller</option>
                                    <option value="wholesale">Wholesale</option>
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
                                        Order #
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Items
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Subtotal
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Discount
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Payment
                                    </th>
                                    <th class="px-6 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="orders.length === 0">
                                    <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">Tidak ada data penjualan</td>
                                </tr>
                                <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ order.order_number }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ order.order_date }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ order.customer_name }}
                                        </div>
                                        <div class="text-xs text-gray-500 capitalize dark:text-gray-400">
                                            {{ order.customer_type }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ order.total_items }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(order.subtotal) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ formatCurrency(order.discount) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(order.total_amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            :class="getPaymentStatusClass(order.payment_status)"
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                        >
                                            {{ order.payment_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <span
                                            :class="getStatusClass(order.status)"
                                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                        >
                                            {{ order.status }}
                                        </span>
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
