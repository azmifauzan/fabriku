<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Search, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    orders: any;
    filters: any;
}>();

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');

const applyFilters = () => {
    router.get(
        '/preparation-orders',
        {
            search: search.value || undefined,
            status: statusFilter.value || undefined,
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
    applyFilters();
};

const statusColors: Record<string, string> = {
    draft: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300',
    in_progress: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    completed: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    cancelled: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
};

const statusLabels: Record<string, string> = {
    draft: 'Draft',
    in_progress: 'In Progress',
    completed: 'Completed',
    cancelled: 'Cancelled',
};
</script>

<template>
    <AppLayout>
        <Head title="Data Preparation Order" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Data Preparation Order"
                    description="Kelola preparation order untuk proses produksi"
                    create-link="/preparation-orders/create"
                    create-text="Tambah Preparation"
                />

                <!-- Filters -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                            <div class="relative">
                                <Search :size="18" class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Nomor order..."
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="statusFilter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md"
                            >
                                <Search :size="16" />
                                Filter
                            </button>
                            <button
                                v-if="search || statusFilter"
                                type="button"
                                @click="clearFilters"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                title="Clear filters"
                            >
                                <X :size="18" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <!-- Table Info -->
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan
                                <span class="font-medium">{{ orders.from || 0 }}</span>
                                sampai
                                <span class="font-medium">{{ orders.to || 0 }}</span>
                                dari
                                <span class="font-medium">{{ orders.total || 0 }}</span>
                                preparation order
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Nomor Order
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Pattern
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Output
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="orders.data.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Tidak ada data preparation order.
                                    </td>
                                </tr>
                                <tr v-for="order in orders.data" :key="order.id" class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ order.order_number }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">{{ order.pattern?.name || '-' }}</div>
                                        <div v-if="order.pattern" class="text-xs text-gray-500 dark:text-gray-400">{{ order.pattern?.code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ order.output_quantity }} {{ order.output_unit }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{
                                                new Date(order.order_date).toLocaleDateString('id-ID', {
                                                    day: 'numeric',
                                                    month: 'short',
                                                    year: 'numeric',
                                                })
                                            }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                statusColors[order.status],
                                                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold',
                                            ]"
                                        >
                                            {{ statusLabels[order.status] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/preparation-orders/${order.id}`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Lihat detail preparation order"
                                            >
                                                <Eye :size="18" />
                                            </Link>
                                            <Link
                                                v-if="['draft', 'in_progress'].includes(order.status)"
                                                :href="`/preparation-orders/${order.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                title="Edit preparation order"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="orders.links && orders.last_page > 1" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-1 justify-center gap-1">
                                <component
                                    v-for="(link, index) in orders.links"
                                    :key="index"
                                    :is="link.url ? Link : 'span'"
                                    :href="link.url"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        link.active
                                            ? 'bg-indigo-600 text-white shadow-sm'
                                            : link.url
                                              ? 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
                                              : 'cursor-not-allowed border border-gray-200 bg-gray-100 text-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-600',
                                    ]"
                                    :preserve-scroll="true"
                                >
                                    <span v-html="link.label" />
                                </component>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
