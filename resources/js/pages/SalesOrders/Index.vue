<template>
    <AppLayout>
        <Head title="Data Sales Order" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Sales Order"
                    description="Kelola pesanan penjualan produk"
                    create-link="/sales-orders/create"
                    create-text="Tambah Pesanan"
                />

                <!-- Statistics Dashboard -->
                <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-blue-500 p-2">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pesanan</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-green-500 p-2">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Revenue (Lunas)</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ Number(stats.realized_revenue).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-red-500 p-2">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Piutang (Outstanding)</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ Number(stats.outstanding_revenue).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-yellow-500 p-2">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pending</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ stats.pending_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-purple-500 p-2">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Selesai</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ stats.completed_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <form @submit.prevent="search" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6 items-end">
                        <div class="lg:col-span-2">
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="No. order, customer..."
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300"> Status </label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
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
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300"> Pembayaran </label>
                            <select
                                v-model="form.payment_status"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua</option>
                                <option value="unpaid">Belum Dibayar</option>
                                <option value="partial">Dibayar Sebagian</option>
                                <option value="paid">Lunas</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-700 dark:text-gray-300"> Channel </label>
                            <select
                                v-model="form.channel"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Channel</option>
                                <option value="offline">Offline</option>
                                <option value="online">Online</option>
                                <option value="reseller">Reseller</option>
                                <option value="marketplace">Marketplace</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                            >
                                Filter
                            </button>
                            <button
                                v-if="form.search || form.status || form.payment_status || form.channel"
                                type="button"
                                @click="clearFilters"
                                class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                ✕
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sales Orders Table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        No. Order
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Customer
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Tanggal
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        No. Invoice
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Channel
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Total
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Status
                                    </th>
                                    <th class="px-4 py-2 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Pembayaran
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-2 text-xs font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {{ order.order_number }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                            {{ order.customer.name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ order.customer.code }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-2 text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ new Date(order.order_date).toLocaleDateString('id-ID') }}
                                    </td>
                                    <td class="px-4 py-2 text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{ order.invoice_number || '-' }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-[10px] leading-5 font-semibold"
                                            :class="{
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.channel === 'offline',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.channel === 'online',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': order.channel === 'reseller',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.channel === 'marketplace',
                                            }"
                                        >
                                            {{
                                                order.channel === 'offline'
                                                    ? 'Offline'
                                                    : order.channel === 'online'
                                                      ? 'Online'
                                                      : order.channel === 'reseller'
                                                        ? 'Reseller'
                                                        : 'Marketplace'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-xs font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        Rp {{ Number(order.total_amount).toLocaleString('id-ID') }}
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-[10px] leading-5 font-semibold"
                                            :class="{
                                                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.status === 'draft',
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.status === 'confirmed',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                    order.status === 'processing',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200': order.status === 'shipped',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.status === 'completed',
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.status === 'cancelled',
                                            }"
                                        >
                                            {{
                                                order.status === 'draft'
                                                    ? 'Draft'
                                                    : order.status === 'confirmed'
                                                      ? 'Dikonfirmasi'
                                                      : order.status === 'processing'
                                                        ? 'Proses'
                                                        : order.status === 'shipped'
                                                          ? 'Dikirim'
                                                          : order.status === 'completed'
                                                            ? 'Selesai'
                                                            : 'Dibatalkan'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-[10px] leading-5 font-semibold"
                                            :class="{
                                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.payment_status === 'unpaid',
                                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                    order.payment_status === 'partial',
                                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.payment_status === 'paid',
                                            }"
                                        >
                                            {{
                                                order.payment_status === 'unpaid'
                                                    ? 'Belum Dibayar'
                                                    : order.payment_status === 'partial'
                                                      ? 'Sebagian'
                                                      : 'Lunas'
                                            }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right text-xs font-medium whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/sales-orders/${order.id}`"
                                                class="inline-flex items-center justify-center rounded-lg p-1.5 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                title="Lihat detail sales order"
                                            >
                                                <Eye :size="16" />
                                            </Link>
                                            <a
                                                :href="`/sales-orders/${order.id}/print`"
                                                target="_blank"
                                                class="inline-flex items-center justify-center rounded-lg p-1.5 text-gray-600 transition-colors hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-900/30"
                                                title="Print Invoice"
                                            >
                                                <Printer :size="16" />
                                            </a>
                                            <Link
                                                v-if="order.status === 'draft' || order.status === 'confirmed'"
                                                :href="`/sales-orders/${order.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-1.5 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Edit sales order"
                                            >
                                                <Edit :size="16" />
                                            </Link>
                                            <button
                                                v-if="order.status === 'draft'"
                                                @click="deleteOrder(order)"
                                                class="inline-flex items-center justify-center rounded-lg p-1.5 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                title="Hapus sales order"
                                            >
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links.length > 3" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan {{ orders.from }} sampai {{ orders.to }} dari {{ orders.total }} data
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="orders.current_page > 1"
                                    :href="`/sales-orders?page=${orders.current_page - 1}`"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    ← Prev
                                </Link>
                                <template v-for="page in orders.last_page" :key="page">
                                    <Link
                                        v-if="Math.abs(page - orders.current_page) <= 2 || page === 1 || page === orders.last_page"
                                        :href="`/sales-orders?page=${page}`"
                                        :class="[
                                            'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                            page === orders.current_page
                                                ? 'bg-indigo-600 text-white shadow-sm'
                                                : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                                        ]"
                                    >
                                        {{ page }}
                                    </Link>
                                    <span v-else-if="Math.abs(page - orders.current_page) === 3" class="px-2 text-gray-500 dark:text-gray-400">
                                        ...
                                    </span>
                                </template>
                                <Link
                                    v-if="orders.current_page < orders.last_page"
                                    :href="`/sales-orders?page=${orders.current_page + 1}`"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
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
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash2, Printer } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    orders: Object,
    stats: Object,
    filters: Object,
});

const { confirmDelete, showSuccess } = useSweetAlert();

const form = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
    payment_status: props.filters.payment_status || '',
    channel: props.filters.channel || '',
});

watch(
    form,
    () => {
        search();
    },
    { deep: true },
);

function search() {
    router.get('/sales-orders', form.value, {
        preserveState: true,
        replace: true,
    });
}

function clearFilters() {
    form.value = {
        search: '',
        status: '',
        payment_status: '',
        channel: '',
    };
    router.get('/sales-orders');
}

async function deleteOrder(order) {
    const result = await confirmDelete('Hapus Sales Order', `Apakah Anda yakin ingin menghapus pesanan ${order.order_number}?`);

    if (result.isConfirmed) {
        router.delete(`/sales-orders/${order.id}`, {
            onSuccess: () => {
                showSuccess('Berhasil!', 'Sales order berhasil dihapus');
            },
        });
    }
}
</script>
