<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

interface AdjustedBy {
    id: number;
    name: string;
}

interface Summary {
    batch_id: string;
    supplier_name: string;
    purchase_invoice: string | null;
    notes: string | null;
    created_at: string;
    adjusted_by: AdjustedBy | null;
    total_cost: number;
    total_qty: number;
}

interface Line {
    id: number;
    inventory_item_id: number;
    adjustment_quantity: number;
    unit_cost: number;
    quantity_before: number;
    quantity_after: number;
    inventory_item: { id: number; sku: string; product_name: string } | null;
}

defineProps<{
    summary: Summary;
    lines: Line[];
}>();

const formatRupiah = (value: number) =>
    'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
</script>

<template>
    <AppLayout>
        <Head title="Detail Pembelian" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <div class="mb-4">
                    <Link href="/purchase-receipts" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                        <ArrowLeft :size="14" />
                        Kembali ke Daftar Pembelian
                    </Link>
                </div>

                <!-- Summary -->
                <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h1 class="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">Detail Pembelian</h1>
                    <dl class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Supplier</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ summary.supplier_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">No. Invoice</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ summary.purchase_invoice ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Tanggal</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">
                                {{ new Date(summary.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">Dicatat oleh</dt>
                            <dd class="font-medium text-gray-900 dark:text-gray-100">{{ summary.adjusted_by?.name ?? '-' }}</dd>
                        </div>
                        <div v-if="summary.notes" class="col-span-2 sm:col-span-4">
                            <dt class="text-gray-500 dark:text-gray-400">Catatan</dt>
                            <dd class="text-gray-700 dark:text-gray-300">{{ summary.notes }}</dd>
                        </div>
                    </dl>
                    <div class="mt-4 flex gap-6 border-t border-gray-100 pt-4 dark:border-gray-700">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Item</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ lines.length }} produk</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Qty</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ summary.total_qty }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Nilai</p>
                            <p class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ formatRupiah(summary.total_cost) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Lines table -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-medium uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-right">Stok Sebelum</th>
                                <th class="px-4 py-3 text-right">Qty Beli</th>
                                <th class="px-4 py-3 text-right">Stok Sesudah</th>
                                <th class="px-4 py-3 text-right">Harga Modal</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="line in lines" :key="line.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ line.inventory_item?.product_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ line.inventory_item?.sku }}</p>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ line.quantity_before }}</td>
                                <td class="px-4 py-3 text-right font-medium text-green-600 dark:text-green-400">+{{ line.adjustment_quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ line.quantity_after }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">{{ formatRupiah(line.unit_cost ?? 0) }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                    {{ formatRupiah(line.adjustment_quantity * (line.unit_cost ?? 0)) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
