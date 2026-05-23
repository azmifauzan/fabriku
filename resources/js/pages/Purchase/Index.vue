<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Eye, Search, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface ReceiptLine {
    batch_id: string;
    supplier_name: string;
    purchase_invoice: string | null;
    notes: string | null;
    total_qty: number;
    total_cost: number;
    item_count: number;
    created_at: string;
    adjusted_by: { id: number; name: string } | null;
}

interface PaginatedReceipts {
    data: ReceiptLine[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    receipts: PaginatedReceipts;
    filters: { search?: string };
}>();

const search = ref(props.filters.search ?? '');

const applySearch = () => {
    router.get('/purchase-receipts', { search: search.value }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    search.value = '';
    router.get('/purchase-receipts', {}, { preserveState: true, replace: true });
};

const formatRupiah = (value: number) =>
    'Rp ' + Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 });
</script>

<template>
    <AppLayout>
        <Head title="Pembelian" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Pembelian Barang"
                    description="Catat barang masuk dari supplier"
                    create-link="/purchase-receipts/create"
                    create-text="Catat Pembelian"
                />

                <!-- Search -->
                <div class="mb-4 flex items-center gap-2">
                    <div class="relative flex-1 max-w-sm">
                        <Search :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari supplier / nomor invoice..."
                            class="w-full rounded-lg border border-gray-300 py-2 pl-9 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            @keydown.enter="applySearch"
                        />
                        <button v-if="search" @click="clearSearch" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <X :size="14" />
                        </button>
                    </div>
                    <button
                        @click="applySearch"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        Cari
                    </button>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs font-medium uppercase text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Supplier</th>
                                <th class="px-4 py-3 text-left">No. Invoice</th>
                                <th class="px-4 py-3 text-right">Jml Item</th>
                                <th class="px-4 py-3 text-right">Total Qty</th>
                                <th class="px-4 py-3 text-right">Total Nilai</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-if="receipts.data.length === 0">
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                    Belum ada catatan pembelian
                                </td>
                            </tr>
                            <tr
                                v-for="receipt in receipts.data"
                                :key="receipt.batch_id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ new Date(receipt.created_at).toLocaleDateString('id-ID') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                    {{ receipt.supplier_name }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                    {{ receipt.purchase_invoice ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                    {{ receipt.item_count }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-gray-400">
                                    {{ receipt.total_qty }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                                    {{ formatRupiah(receipt.total_cost) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Link
                                        :href="`/purchase-receipts/batch/${receipt.batch_id}`"
                                        class="inline-flex items-center gap-1 rounded px-2 py-1 text-xs text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20"
                                    >
                                        <Eye :size="14" />
                                        Detail
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="receipts.last_page > 1" class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ receipts.from }}–{{ receipts.to }} dari {{ receipts.total }} transaksi
                        </p>
                        <div class="flex gap-1">
                            <Link
                                v-for="page in receipts.last_page"
                                :key="page"
                                :href="`/purchase-receipts?page=${page}`"
                                :class="[
                                    'rounded px-2.5 py-1 text-sm',
                                    page === receipts.current_page
                                        ? 'bg-indigo-600 text-white'
                                        : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700',
                                ]"
                            >
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
