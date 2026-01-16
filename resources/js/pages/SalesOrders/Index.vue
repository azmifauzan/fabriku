<template>
    <AppLayout>
        <Head title="Data Sales Order" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Sales Order"
                    description="Kelola pesanan penjualan produk"
                    create-link="/sales-orders/create"
                    create-text="Tambah Pesanan"
                />

                <!-- Statistics Dashboard -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        Rp {{ Number(stats.total_revenue).toLocaleString('id-ID') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.pending_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Selesai</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.completed_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
                <form @submit.prevent="search" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Pencarian
                            </label>
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="No. order, customer..."
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Status
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            >
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="confirmed">Dikonfirmasi</option>
                                <option value="processing">Proses</option>
                                <option value="shipped">Dikirim</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Pembayaran
                            </label>
                            <select
                                v-model="form.payment_status"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            >
                                <option value="">Semua</option>
                                <option value="unpaid">Belum Dibayar</option>
                                <option value="partial">Dibayar Sebagian</option>
                                <option value="paid">Lunas</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                                Channel
                            </label>
                            <select
                                v-model="form.channel"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            >
                                <option value="">Semua Channel</option>
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                                <option value="reseller">Reseller</option>
                                <option value="marketplace">Marketplace</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="submit"
                                class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
                            >
                                Filter
                            </button>
                            <button
                                v-if="form.search || form.status || form.payment_status || form.channel"
                                type="button"
                                @click="clearFilters"
                                class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                            >
                                <span class="text-base">✕</span>
                                Clear
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sales Orders Table -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        No. Order
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Customer
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Channel
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Pembayaran
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ order.order_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ order.customer.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ order.customer.code }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ new Date(order.order_date).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.channel === 'offline',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.channel === 'online',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': order.channel === 'reseller',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.channel === 'marketplace'
                                            }"
                                        >
                                            {{ order.channel === 'offline' ? 'Offline' : order.channel === 'online' ? 'Online' : order.channel === 'reseller' ? 'Reseller' : 'Marketplace' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.status === 'draft',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.status === 'confirmed',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': order.status === 'processing',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': order.status === 'shipped',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.status === 'completed',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.status === 'cancelled'
                                            }"
                                        >
                                            {{ order.status === 'draft' ? 'Draft' : order.status === 'confirmed' ? 'Dikonfirmasi' : order.status === 'processing' ? 'Proses' : order.status === 'shipped' ? 'Dikirim' : order.status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.payment_status === 'unpaid',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': order.payment_status === 'partial',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.payment_status === 'paid'
                                            }"
                                        >
                                            {{ order.payment_status === 'unpaid' ? 'Belum Dibayar' : order.payment_status === 'partial' ? 'Sebagian' : 'Lunas' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/sales-orders/${order.id}`"
                                                class="inline-flex items-center justify-center p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                                                title="Lihat detail sales order"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" :width="18" :height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </Link>
                                            <Link
                                                v-if="order.status === 'draft' || order.status === 'confirmed'"
                                                :href="`/sales-orders/${order.id}/edit`"
                                                class="inline-flex items-center justify-center p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                                                title="Edit sales order"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" :width="18" :height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                            </Link>
                                            <button
                                                v-if="order.status === 'draft'"
                                                @click="deleteOrder(order)"
                                                class="inline-flex items-center justify-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                                title="Hapus sales order"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" :width="18" :height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan {{ orders.from }} sampai {{ orders.to }} dari {{ orders.total }} data
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="orders.current_page > 1"
                                    :href="`/sales-orders?page=${orders.current_page - 1}`"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all"
                                >
                                    ← Prev
                                </Link>
                                <template v-for="page in orders.last_page" :key="page">
                                    <Link
                                        v-if="Math.abs(page - orders.current_page) <= 2 || page === 1 || page === orders.last_page"
                                        :href="`/sales-orders?page=${page}`"
                                        :class="[
                                            'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                                            page === orders.current_page
                                                ? 'bg-indigo-600 text-white shadow-sm'
                                                : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                                        ]"
                                    >
                                        {{ page }}
                                    </Link>
                                    <span
                                        v-else-if="Math.abs(page - orders.current_page) === 3"
                                        class="px-2 text-gray-500 dark:text-gray-400"
                                    >
                                        ...
                                    </span>
                                </template>
                                <Link
                                    v-if="orders.current_page < orders.last_page"
                                    :href="`/sales-orders?page=${orders.current_page + 1}`"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all"
                                >
                                    Next →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { router, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';

const props = defineProps({
    orders: Object,
    stats: Object,
    filters: Object
});

const { confirmDelete, showSuccess } = useSweetAlert();

const form = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    payment_status: props.filters.payment_status || '',
    channel: props.filters.channel || ''
});

watch(form, () => {
    search();
}, { deep: true });

function search() {
    router.get('/sales-orders', form.value, {
        preserveState: true,
        replace: true
    });
}

function clearFilters() {
    form.value = {
        search: '',
        status: '',
        payment_status: '',
        channel: ''
    };
    router.get('/sales-orders');
}

async function deleteOrder(order) {
    const result = await confirmDelete(
        'Hapus Sales Order',
        `Apakah Anda yakin ingin menghapus pesanan ${order.order_number}?`
    );

    if (result.isConfirmed) {
        router.delete(`/sales-orders/${order.id}`, {
            onSuccess: () => {
                showSuccess('Berhasil!', 'Sales order berhasil dihapus');
            }
        });
    }
}
</script>
