<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Material {
    material_id: number;
    material_name: string;
    quantity: number;
    unit: string;
}

interface Pattern {
    id: number;
    code: string;
    name: string;
    category: string;
}

interface Staff {
    id: number;
    code: string;
    name: string;
    position: string | null;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    status: string;
    type: string;
    quantity_requested: number;
}

interface PreparationOrder {
    id: number;
    order_number: string;
    pattern: Pattern | null;
    prepared_by_staff: Staff | null;
    preparation_date: string;
    output_quantity: number;
    output_unit: string;
    materials_used: Material[];
    notes: string | null;
    status: string;
    production_orders: ProductionOrder[];
    can_be_edited: boolean;
    can_be_deleted: boolean;
}

const props = defineProps<{
    order: PreparationOrder;
}>();

const { confirmDelete, showSuccess } = useSweetAlert();

const statusBadge = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300',
        in_progress: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
        completed: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
        cancelled: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
    };

    return colors[status] || 'bg-gray-100 text-gray-800';
};

const statusLabel = (status: string) => {
    const labels: Record<string, string> = {
        draft: 'Draft',
        in_progress: 'In Progress',
        completed: 'Completed',
        cancelled: 'Cancelled',
    };

    return labels[status] || status;
};

const handleDelete = async () => {
    const result = await confirmDelete('Hapus Preparation Order', `Apakah Anda yakin ingin menghapus preparation order ${props.order.order_number}?`);

    if (result.isConfirmed) {
        router.delete(`/preparation-orders/${props.order.id}`, {
            onSuccess: () => {
                showSuccess('Berhasil!', 'Preparation order berhasil dihapus');
            },
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Detail Preparation Order ${order.order_number}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <!-- Header -->
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            Preparation Order {{ order.order_number }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            <template v-if="order.pattern"> Pattern: {{ order.pattern.name }} ({{ order.pattern.code }}) </template>
                            <template v-else> Custom Preparation </template>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            href="/preparation-orders"
                            class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                        >
                            ← Kembali
                        </Link>
                        <Link
                            v-if="order.can_be_edited"
                            :href="`/preparation-orders/${order.id}/edit`"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                            Edit
                        </Link>
                        <button
                            v-if="order.can_be_deleted"
                            @click="handleDelete"
                            class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-red-700"
                        >
                            Hapus
                        </button>
                    </div>
                </div>

                <!-- Main Info Card -->
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-6">
                        <div class="mb-6 flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Detail</h3>
                            <span :class="statusBadge(order.status)" class="inline-flex rounded-full px-3 py-1 text-sm leading-5 font-semibold">
                                {{ statusLabel(order.status) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Tanggal Order</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{
                                        order.preparation_date
                                            ? new Date(order.preparation_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                                            : '-'
                                    }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                <p class="mb-1 text-xs text-gray-500 dark:text-gray-400">Penanggung Jawab</p>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ order.prepared_by_staff?.name || '-' }}
                                </p>
                                <p v-if="order.prepared_by_staff?.position" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                    {{ order.prepared_by_staff.position }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/30">
                                <p class="mb-1 text-xs text-blue-600 dark:text-blue-400">Output Hasil</p>
                                <p class="text-lg font-bold text-blue-700 dark:text-blue-300">{{ order.output_quantity }} {{ order.output_unit }}</p>
                            </div>
                        </div>

                        <div v-if="order.notes" class="mt-4 rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                            <p class="mb-1 text-xs font-medium text-amber-600 dark:text-amber-400">Catatan</p>
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ order.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Materials Used -->
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Material yang Digunakan</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Material
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Jumlah
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Satuan
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-if="order.materials_used.length === 0">
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                            Tidak ada material tercatat
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(material, idx) in order.materials_used"
                                        :key="idx"
                                        class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ material.material_name }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-sm text-gray-700 dark:text-gray-300">
                                            {{ material.quantity }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ material.unit }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Related Production Orders -->
                <div
                    v-if="order.production_orders && order.production_orders.length > 0"
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Production Orders Terkait</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Order Number
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Tipe
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Target Qty
                                        </th>
                                        <th
                                            class="px-4 py-3 text-right text-xs font-semibold tracking-wider text-gray-600 uppercase dark:text-gray-300"
                                        >
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr
                                        v-for="po in order.production_orders"
                                        :key="po.id"
                                        class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                    >
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                            {{ po.order_number }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 capitalize dark:text-gray-300">
                                            {{ po.type }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                :class="statusBadge(po.status)"
                                                class="inline-flex rounded-full px-2 py-1 text-xs leading-5 font-semibold"
                                            >
                                                {{ statusLabel(po.status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono text-sm text-gray-700 dark:text-gray-300">
                                            {{ po.quantity_requested }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <Link
                                                :href="`/production-orders/${po.id}`"
                                                class="text-sm font-medium text-indigo-600 transition-colors hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                            >
                                                Lihat Detail
                                            </Link>
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
