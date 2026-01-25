<script setup lang="ts">
import { create } from '@/actions/App/Http/Controllers/ProductionOrderController';
import PageHeader from '@/components/PageHeader.vue';
import { useBusinessContext } from '@/composables/useBusinessContext';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Check, Edit, Eye, Play, Send, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Pattern {
    id: number;
    code: string;
    name: string;
}

interface PreparationOrder {
    id: number;
    order_number: string;
    output_quantity: number;
    pattern: Pattern;
}

interface Contractor {
    id: number;
    name: string;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    preparation_order: PreparationOrder;
    contractor: Contractor | null;
    estimated_completion_date: string | null;
    type: string;
    status: string;
    priority: string;
    created_at: string;
}

interface PaginatedOrders {
    data: ProductionOrder[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    orders: PaginatedOrders;
    contractors: Contractor[];
    filters: {
        search?: string;
        status?: string;
        type?: string;
        contractor_id?: string;
    };
    stats: {
        total_orders: number;
        draft_orders: number;
        in_progress_orders: number;
        completed_orders: number;
    };
}>();

const { term, termLower } = useBusinessContext();
const { confirmDelete, showSuccess, showWarning } = useSweetAlert();

const productionOrderLabel = computed(() => 'Production Order');
const contractorLabel = computed(() => term('contractor', 'Kontraktor'));
const contractorLabelLower = computed(() => termLower('contractor', 'kontraktor'));
const patternLabel = computed(() => term('pattern', 'Pattern'));

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const typeFilter = ref(props.filters.type || '');
const contractorFilter = ref(props.filters.contractor_id || '');

const applyFilters = () => {
    router.get(
        '/production-orders',
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
            type: typeFilter.value || undefined,
            contractor_id: contractorFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    typeFilter.value = '';
    contractorFilter.value = '';
    applyFilters();
};

const deleteOrder = async (order: ProductionOrder) => {
    if (order.status !== 'draft') {
        showWarning('Tidak Bisa Dihapus', 'Hanya order dengan status draft yang bisa dihapus');
        return;
    }

    const result = await confirmDelete(
        `Hapus ${productionOrderLabel.value}`,
        `Apakah Anda yakin ingin menghapus ${termLower('production_order', 'production order')} ${order.order_number}?`,
    );

    if (result.isConfirmed) {
        router.delete(`/production-orders/${order.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Berhasil!', `${productionOrderLabel.value} berhasil dihapus`);
            },
        });
    }
};

const getStatusBadge = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300',
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-300',
        sent: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800/20 dark:text-indigo-300',
        in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300',
        completed: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300';
};

const getStatusLabel = (status: string) => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        pending: 'Pending',
        sent: 'Dikirim',
        in_progress: 'Dalam Proses',
        completed: 'Selesai',
        cancelled: 'Dibatalkan',
    };
    return labels[status] || status;
};

const getTypeLabel = (type: string) => {
    return type === 'internal' ? 'Internal' : 'Eksternal';
};

const startProduction = async (order: ProductionOrder) => {
    const result = await useSweetAlert().confirm(
        'Mulai Produksi?',
        `Mulai produksi untuk ${productionOrderLabel.value} ${order.order_number}?`,
        'Ya, Mulai Produksi',
        'question',
    );

    if (result.isConfirmed) {
        router.post(
            `/production-orders/${order.id}/start`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Berhasil!', `${productionOrderLabel.value} dimulai produksinya`);
                },
            },
        );
    }
};

const sendToContractor = async (order: ProductionOrder) => {
    const result = await useSweetAlert().confirm(
        'Kirim ke Kontraktor?',
        `Kirim ${productionOrderLabel.value} ${order.order_number} ke kontraktor?`,
        'Ya, Kirim',
        'question',
    );

    if (result.isConfirmed) {
        router.post(
            `/production-orders/${order.id}/send`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Berhasil!', `${productionOrderLabel.value} berhasil dikirim ke kontraktor`);
                },
            },
        );
    }
};

const markComplete = async (order: ProductionOrder) => {
    const result = await useSweetAlert().confirm(
        'Tandai Selesai?',
        `Tandai ${productionOrderLabel.value} ${order.order_number} sebagai selesai?`,
        'Ya, Tandai Selesai',
        'question',
    );

    if (result.isConfirmed) {
        router.post(
            `/production-orders/${order.id}/mark-complete`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Berhasil!', `${productionOrderLabel.value} ditandai selesai`);
                },
            },
        );
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Data ${productionOrderLabel}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="productionOrderLabel"
                    description="Kelola order produksi internal dan eksternal"
                    :create-link="create.url()"
                    create-text="Buat Order"
                />

                <!-- Statistics -->
                <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Order</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-gray-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Draft</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.draft_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-yellow-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.in_progress_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.completed_orders }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:gap-4">
                        <div class="flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                            <input
                                v-model="search"
                                @input="applyFilters"
                                type="text"
                                :placeholder="`Order number, ${contractorLabelLower}...`"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <div class="flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"> Status </label>
                            <select
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="sent">Dikirim</option>
                                <option value="in_progress">Dalam Proses</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option>
                            </select>
                        </div>

                        <div class="flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300"> Tipe </label>
                            <select
                                v-model="typeFilter"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Tipe</option>
                                <option value="internal">Internal</option>
                                <option value="external">Eksternal</option>
                            </select>
                        </div>

                        <div class="flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ contractorLabel }}
                            </label>
                            <select
                                v-model="contractorFilter"
                                @change="applyFilters"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option :value="''">Semua {{ contractorLabel }}</option>
                                <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                                    {{ contractor.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex flex-shrink-0 items-end gap-2">
                            <button
                                @click="applyFilters"
                                class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700"
                            >
                                Filter
                            </button>
                            <button
                                v-if="search || statusFilter || typeFilter || contractorFilter"
                                @click="clearFilters"
                                class="flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                <span class="text-base">✕</span>
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Order Number
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        {{ patternLabel }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Tipe
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        {{ contractorLabel }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Target Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <svg
                                            class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-gray-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                            />
                                        </svg>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            Tidak ada data {{ termLower('production_order', 'production order') }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat order produksi pertama Anda.</p>
                                    </td>
                                </tr>
                                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ order.order_number }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ order.preparation_order.pattern?.name || 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ order.preparation_order.pattern?.code || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex rounded-full px-3 py-1 text-xs leading-5 font-semibold',
                                                getStatusBadge(order.status),
                                            ]"
                                        >
                                            {{ getStatusLabel(order.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ getTypeLabel(order.type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ order.contractor?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                        {{ order.preparation_order.output_quantity }} pcs
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                        {{
                                            order.estimated_completion_date
                                                ? new Date(order.estimated_completion_date).toLocaleDateString('id-ID')
                                                : '-'
                                        }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/production-orders/${order.id}`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Lihat detail production order"
                                            >
                                                <Eye :size="18" />
                                            </Link>
                                            <!-- Mulai Produksi (Internal) -->
                                            <button
                                                v-if="order.type === 'internal' && order.status === 'draft'"
                                                type="button"
                                                @click="startProduction(order)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-orange-600 transition-colors hover:bg-orange-50 dark:text-orange-400 dark:hover:bg-orange-900/30"
                                                title="Mulai produksi"
                                            >
                                                <Play :size="18" />
                                            </button>
                                            <!-- Kirim ke Kontraktor (External) -->
                                            <button
                                                v-if="order.type === 'external' && order.status === 'draft'"
                                                type="button"
                                                @click="sendToContractor(order)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-purple-600 transition-colors hover:bg-purple-50 dark:text-purple-400 dark:hover:bg-purple-900/30"
                                                title="Kirim ke kontraktor"
                                            >
                                                <Send :size="18" />
                                            </button>
                                            <!-- Tandai Selesai -->
                                            <button
                                                v-if="['in_progress', 'sent'].includes(order.status)"
                                                type="button"
                                                @click="markComplete(order)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-green-600 transition-colors hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/30"
                                                title="Tandai selesai"
                                            >
                                                <Check :size="18" />
                                            </button>
                                            <Link
                                                v-if="order.status === 'draft'"
                                                :href="`/production-orders/${order.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                title="Edit production order"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button
                                                v-if="order.status === 'draft'"
                                                type="button"
                                                @click="deleteOrder(order)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                title="Hapus production order"
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.last_page > 1" class="border-t border-gray-200 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan {{ orders.from }} - {{ orders.to }} dari {{ orders.total }} data
                            </div>
                            <div class="flex items-center gap-2">
                                <Link
                                    v-if="orders.current_page > 1"
                                    :href="`/production-orders?page=${orders.current_page - 1}`"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    ← Prev
                                </Link>
                                <template v-for="page in orders.last_page" :key="page">
                                    <Link
                                        v-if="Math.abs(page - orders.current_page) <= 2 || page === 1 || page === orders.last_page"
                                        :href="`/production-orders?page=${page}`"
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
                                    :href="`/production-orders?page=${orders.current_page + 1}`"
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
