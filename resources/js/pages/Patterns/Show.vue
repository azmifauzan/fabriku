<template>
    <AppLayout>
        <Head :title="`Detail ${patternLabel}: ${pattern.name}`" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="`Detail ${patternLabel}: ${pattern.name}`"
                    :description="`Informasi lengkap ${patternLabel.toLowerCase()} ${pattern.code}`"
                    :back-link="{ href: '/patterns', text: `Kembali ke Daftar ${patternLabel}` }"
                />

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Pattern Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi {{ patternLabel }}</h3>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/patterns/${pattern.id}/edit`"
                                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode {{ patternLabel }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ pattern.code }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ pattern.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tipe Produk</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ pattern.product_type }}</dd>
                                    </div>
                                    <div v-if="pattern.size">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ukuran</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ pattern.size }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="mt-1">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="pattern.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                            >
                                                {{ pattern.is_active ? 'Aktif' : 'Non-Aktif' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="pattern.estimated_cost">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Estimasi Biaya</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            Rp {{ Number(pattern.estimated_cost).toLocaleString('id-ID') }}
                                        </dd>
                                    </div>
                                    <div v-if="pattern.description" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ pattern.description }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Recent Preparation Orders -->
                        <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Riwayat Order Persiapan Terakhir</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table v-if="recentOrders && recentOrders.length" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                No. Order
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Tanggal
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="order in recentOrders" :key="order.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 dark:text-blue-400">
                                                <Link :href="`/preparation-orders/${order.id}`">{{ order.order_number }}</Link>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ new Date(order.order_date).toLocaleDateString('id-ID') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ order.quantity }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                    :class="{
                                                        'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200': order.status === 'draft',
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': order.status === 'in_progress',
                                                        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': order.status === 'completed',
                                                        'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': order.status === 'cancelled'
                                                    }"
                                                >
                                                    {{ order.status === 'draft' ? 'Draft' : order.status === 'in_progress' ? 'Proses' : order.status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else class="p-6 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada order persiapan
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Sidebar -->
                    <div class="space-y-6">
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Statistik</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Order</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_orders || 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Diproduksi</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ stats.total_produced || 0 }}
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
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

interface Pattern {
    id: number
    code: string
    name: string
    product_type: string
    size?: string
    description?: string
    is_active: boolean
    estimated_cost?: number
}

interface PreparationOrder {
    id: number
    order_number: string
    order_date: string
    quantity: number
    status: string
}

interface Stats {
    total_orders: number
    total_produced: number
}

interface Props {
    pattern: Pattern
    recentOrders?: PreparationOrder[]
    stats: Stats
}

defineProps<Props>()

const { term } = useBusinessContext()
const patternLabel = computed(() => term('pattern', 'Pattern'))
</script>
