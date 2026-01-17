<template>
    <AppLayout>
        <Head :title="`Detail Kontraktor: ${contractor.name}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            Detail Kontraktor: {{ contractor.name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">Informasi lengkap kontraktor {{ contractor.code }}</p>
                    </div>
                    <Link
                        href="/contractors"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Contractor Information -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Kontraktor</h3>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/contractors/${contractor.id}/edit`"
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
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Kontraktor</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contractor.code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contractor.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Spesialisasi</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contractor.specialty || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="
                                                    contractor.is_active
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                                "
                                            >
                                                {{ contractor.is_active ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contractor.phone || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ contractor.email || '-' }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Alamat</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ contractor.address || '-' }}
                                        </dd>
                                    </div>
                                    <div v-if="contractor.notes" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                                        <dd class="mt-1 text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ contractor.notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Sidebar -->
                    <div class="lg:col-span-1">
                        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Statistik</h3>
                            </div>
                            <div class="p-6">
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Produksi</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ stats.total_productions }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Item Diproduksi</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ stats.total_items_produced }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Production Orders -->
                <div class="mt-6">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Riwayat Produksi Terbaru</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Kode Order
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Pattern
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Jumlah
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-300">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                    <tr v-if="!recentOrders || recentOrders.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Belum ada riwayat produksi
                                        </td>
                                    </tr>
                                    <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            {{ order.order_number }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                            {{ order.start_date }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            {{ order.pattern?.name || '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-gray-100">
                                            {{ order.quantity_ordered }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="{
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200':
                                                        order.status === 'pending',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.status === 'in_progress',
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.status === 'completed',
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.status === 'cancelled',
                                                }"
                                            >
                                                {{
                                                    order.status === 'pending'
                                                        ? 'Pending'
                                                        : order.status === 'in_progress'
                                                          ? 'In Progress'
                                                          : order.status === 'completed'
                                                            ? 'Selesai'
                                                            : 'Dibatalkan'
                                                }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
import { computed } from 'vue';

interface Pattern {
    id: number;
    name: string;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    start_date: string;
    quantity_ordered: number;
    status: string;
    pattern?: Pattern;
}

interface Contractor {
    id: number;
    code: string;
    name: string;
    specialty: string | null;
    phone: string | null;
    email: string | null;
    address: string | null;
    notes: string | null;
    is_active: boolean;
}

interface Stats {
    total_productions: number;
    total_items_produced: number;
}

const props = defineProps<{
    contractor: Contractor;
    recentOrders?: ProductionOrder[];
    stats?: Stats;
}>();

const defaultStats: Stats = {
    total_productions: 0,
    total_items_produced: 0,
};

const stats = computed(() => props.stats ?? defaultStats);
</script>
