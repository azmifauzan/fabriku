<template>
    <AppLayout>
        <Head :title="`Detail Pesanan: ${salesOrder.order_number}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="`Detail Pesanan: ${salesOrder.order_number}`"
                    :description="`Informasi lengkap pesanan dari ${salesOrder.customer.name}`"
                    :back-link="{ href: '/sales-orders', text: 'Kembali ke Daftar Pesanan' }"
                />

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Order Details -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Order Information -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Pesanan</h3>
                                    <div class="flex gap-2">
                                        <Link
                                            v-if="salesOrder.status === 'draft' || salesOrder.status === 'confirmed'"
                                            :href="`/sales-orders/${salesOrder.id}/edit`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900"
                                        >
                                            Edit
                                        </Link>
                                        <a
                                            :href="`/sales-orders/${salesOrder.id}/print`"
                                            target="_blank"
                                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                        >
                                            Print
                                        </a>
                                        <a
                                            :href="`/sales-orders/${salesOrder.id}/export`"
                                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-25 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                        >
                                            Export
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Order</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ salesOrder.order_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{
                                                new Date(salesOrder.order_date).toLocaleDateString('id-ID', {
                                                    weekday: 'long',
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                })
                                            }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Invoice</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ salesOrder.invoice_number || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">No. Resi</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ salesOrder.resi_number || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</dt>
                                        <dd class="mt-1">
                                            <Link
                                                :href="`/customers/${salesOrder.customer.id}`"
                                                class="text-sm text-blue-600 hover:underline dark:text-blue-400"
                                            >
                                                {{ salesOrder.customer.name }}
                                            </Link>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ salesOrder.customer.code }}</div>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Channel</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="{
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': salesOrder.channel === 'offline',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': salesOrder.channel === 'online',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
                                                        salesOrder.channel === 'reseller',
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                        salesOrder.channel === 'marketplace',
                                                }"
                                            >
                                                {{
                                                    salesOrder.channel === 'offline'
                                                        ? 'Offline'
                                                        : salesOrder.channel === 'online'
                                                          ? 'Online'
                                                          : salesOrder.channel === 'reseller'
                                                            ? 'Reseller'
                                                            : 'Marketplace'
                                                }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="{
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': salesOrder.status === 'draft',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                        salesOrder.status === 'confirmed',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                        salesOrder.status === 'processing',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200':
                                                        salesOrder.status === 'shipped',
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                        salesOrder.status === 'completed',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': salesOrder.status === 'cancelled',
                                                }"
                                            >
                                                {{
                                                    salesOrder.status === 'draft'
                                                        ? 'Draft'
                                                        : salesOrder.status === 'confirmed'
                                                          ? 'Dikonfirmasi'
                                                          : salesOrder.status === 'processing'
                                                            ? 'Proses'
                                                            : salesOrder.status === 'shipped'
                                                              ? 'Dikirim'
                                                              : salesOrder.status === 'completed'
                                                                ? 'Selesai'
                                                                : 'Dibatalkan'
                                                }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Metode Pembayaran</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{
                                                salesOrder.payment_method === 'cash'
                                                    ? 'Cash'
                                                    : salesOrder.payment_method === 'transfer'
                                                      ? 'Transfer'
                                                      : salesOrder.payment_method === 'credit_card'
                                                        ? 'Kartu Kredit'
                                                        : salesOrder.payment_method === 'qris'
                                                          ? 'QRIS'
                                                          : 'COD'
                                            }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Pembayaran</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="{
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200':
                                                        salesOrder.payment_status === 'unpaid',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                        salesOrder.payment_status === 'partial',
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200':
                                                        salesOrder.payment_status === 'paid',
                                                }"
                                            >
                                                {{
                                                    salesOrder.payment_status === 'unpaid'
                                                        ? 'Belum Dibayar'
                                                        : salesOrder.payment_status === 'partial'
                                                          ? 'Dibayar Sebagian'
                                                          : 'Lunas'
                                                }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Dibayar</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            Rp {{ Number(salesOrder.paid_amount).toLocaleString('id-ID') }}
                                        </dd>
                                    </div>
                                    <div v-if="salesOrder.shipping_address" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat Pengiriman</dt>
                                        <dd class="mt-1 text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">
                                            {{ salesOrder.shipping_address }}
                                        </dd>
                                    </div>
                                    <div v-if="salesOrder.notes" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                                        <dd class="mt-1 text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ salesOrder.notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Item Pesanan</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Produk
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Jumlah
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Harga
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Diskon
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300"
                                            >
                                                Subtotal
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr v-for="item in salesOrder.items" :key="item.id">
                                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                                <div class="font-medium">{{ item.inventory_item.pattern.name }}</div>
                                                <div class="text-gray-500 dark:text-gray-400">
                                                    SKU: {{ item.inventory_item.sku }}, Lokasi: {{ item.inventory_item.inventory_location.name }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ item.quantity }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(item.unit_price).toLocaleString('id-ID') }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(item.discount_amount).toLocaleString('id-ID') }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Subtotal
                                            </td>
                                            <td class="px-6 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(salesOrder.subtotal).toLocaleString('id-ID') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Diskon
                                            </td>
                                            <td class="px-6 py-3 text-right text-sm font-medium text-red-600 dark:text-red-400">
                                                - Rp {{ Number(salesOrder.discount_amount).toLocaleString('id-ID') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Pajak
                                            </td>
                                            <td class="px-6 py-3 text-right text-sm font-medium text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(salesOrder.tax_amount).toLocaleString('id-ID') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="px-6 py-3 text-right text-base font-bold text-gray-900 dark:text-gray-100">
                                                Total
                                            </td>
                                            <td class="px-6 py-3 text-right text-base font-bold text-gray-900 dark:text-gray-100">
                                                Rp {{ Number(salesOrder.total_amount).toLocaleString('id-ID') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Ringkasan</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Item</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ salesOrder.items.length }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Quantity</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ salesOrder.items.reduce((sum, item) => sum + item.quantity, 0) }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sisa Pembayaran</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            Rp {{ Number(salesOrder.total_amount - salesOrder.paid_amount).toLocaleString('id-ID') }}
                                        </dd>
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
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    salesOrder: Object,
});
</script>
