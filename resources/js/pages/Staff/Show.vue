<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface PreparationOrder {
    id: number;
    code: string;
    status: string;
    created_at: string;
}

interface StaffData {
    id: number;
    code: string;
    name: string;
    position: string | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
    created_at: string;
    preparationOrders_count: number;
}

interface Props {
    staff: StaffData;
    recentOrders: PreparationOrder[];
    stats: {
        total_preparations: number;
    };
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head :title="`Detail Staff: ${staff.name}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">Detail Staff: {{ staff.name }}</h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">Informasi lengkap staff {{ staff.code }}</p>
                    </div>
                    <Link
                        href="/staff"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Staff Information -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Staff</h3>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/staff/${staff.id}/edit`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 dark:focus:ring-offset-gray-800"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Staff</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ staff.code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-gray-100">{{ staff.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Posisi</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ staff.position || '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                :class="
                                                    staff.is_active
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
                                                "
                                            >
                                                {{ staff.is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="staff.phone">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ staff.phone }}</dd>
                                    </div>
                                    <div v-if="staff.email">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ staff.email }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat pada</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{
                                                new Date(staff.created_at).toLocaleDateString('id-ID', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit',
                                                })
                                            }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Recent Preparation Orders -->
                        <div class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Order Persiapan Terakhir</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table v-if="recentOrders.length" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                            >
                                                Kode Order
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                            >
                                                Status
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                            >
                                                Tanggal
                                            </th>
                                            <th
                                                class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400"
                                            >
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                        <tr
                                            v-for="order in recentOrders"
                                            :key="order.id"
                                            class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                        >
                                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                                {{ order.code }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                                    :class="{
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300': order.status === 'draft',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300':
                                                            order.status === 'pending',
                                                        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300':
                                                            order.status === 'in_progress',
                                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300':
                                                            order.status === 'completed',
                                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300': order.status === 'cancelled',
                                                    }"
                                                >
                                                    {{
                                                        order.status === 'draft'
                                                            ? 'Draft'
                                                            : order.status === 'pending'
                                                              ? 'Menunggu'
                                                              : order.status === 'in_progress'
                                                                ? 'Sedang Berlangsung'
                                                                : order.status === 'completed'
                                                                  ? 'Selesai'
                                                                  : 'Dibatalkan'
                                                    }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                                {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                                            </td>
                                            <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                                <Link
                                                    :href="`/preparation-orders/${order.id}`"
                                                    class="text-indigo-600 transition-colors hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                                >
                                                    Lihat Detail
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="p-6 text-center text-gray-500 dark:text-gray-400">Belum ada order persiapan untuk staff ini</div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Sidebar -->
                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Statistik</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Order Persiapan</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_preparations }}</dd>
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
