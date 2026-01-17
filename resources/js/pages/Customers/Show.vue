<template>
    <AppLayout>
        <Head :title="`Detail Customer: ${customer.name}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            Detail Customer: {{ customer.name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">Informasi lengkap customer {{ customer.code }}</p>
                    </div>
                    <Link
                        href="/customers"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Customer Information -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Customer</h3>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/customers/${customer.id}/edit`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Customer</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ customer.code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ customer.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ customer.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="
                                                    customer.is_active
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                "
                                            >
                                                {{ customer.is_active ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ customer.phone || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ customer.email || '-' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ customer.address || '-' }}<br />
                                            <span v-if="customer.city || customer.province">
                                                {{ customer.city }}{{ customer.city && customer.province ? ', ' : '' }}{{ customer.province }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="customer.notes" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                                        <dd class="mt-1 text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ customer.notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Recent Sales Orders -->
                        <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Riwayat Pesanan Terakhir</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table v-if="recentOrders.length" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                No. Order
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Tanggal
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Total
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr v-for="order in recentOrders" :key="order.id">
                                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-blue-600 dark:text-blue-400">
                                                <Link :href="`/sales-orders/${order.id}`">{{ order.order_number }}</Link>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                                {{ new Date(order.order_date).toLocaleDateString('id-ID') }}
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(order.total).toLocaleString('id-ID') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                    :class="{
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.status === 'draft',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.status === 'confirmed',
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                            order.status === 'processing',
                                                        'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
                                                            order.status === 'shipped',
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                            order.status === 'completed',
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
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="p-6 text-center text-gray-500 dark:text-gray-400">Belum ada pesanan</div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Sidebar -->
                    <div class="space-y-6">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Statistik</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_orders }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            Rp {{ Number(stats.total_revenue).toLocaleString('id-ID') }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pesanan Pending</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.pending_orders }}</dd>
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

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Order {
    id: number;
    order_number: string;
    order_date: string;
    total: number;
    status: string;
}

interface Stats {
    total_orders: number;
    total_revenue: number;
    pending_orders: number;
}

interface Customer {
    id: number;
    code: string;
    name: string;
    phone?: string;
    email?: string;
    address?: string;
    city?: string;
    province?: string;
    is_active: boolean;
    notes?: string;
}

defineProps<{
    customer: Customer;
    recentOrders: Order[];
    stats: Stats;
}>();
</script>
