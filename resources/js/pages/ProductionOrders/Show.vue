<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ProductionBatchForm from './ProductionBatchForm.vue';

interface Pattern {
    id: number;
    code: string;
    name: string;
}

interface PreparationOrder {
    id: number;
    order_number: string;
    pattern: Pattern;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    preparation_order: PreparationOrder;
    contractor: Contractor | null;
    type: string;
    status: string;
    quantity_produced: number;
    quantity_good: number;
    quantity_reject: number;
    estimated_completion_date: string | null;
    sent_date: string | null;
    completed_date: string | null;
    notes: string | null;
    batches: ProductionBatch[];
}

const props = defineProps<{
    order: ProductionOrder;
}>();

const { term, termLower } = useBusinessContext();
const { confirmDelete, showSuccess } = useSweetAlert();

const productionOrderLabel = computed(() => 'Production Order');
const contractorLabel = computed(() => term('contractor', 'Kontraktor'));

const showReceive = ref(false);

const canSend = computed(() => {
    if (props.order.type === 'external') {
        return ['draft', 'pending'].includes(props.order.status);
    }

    return ['draft', 'pending'].includes(props.order.status);
});

const canReceive = computed(() => {
    return ['sent', 'in_progress'].includes(props.order.status);
});

const sendOrder = async () => {
    const result = await confirmDelete(
        `Kirim ${productionOrderLabel.value}`,
        `Apakah Anda yakin ingin mengirim ${termLower('production_order', 'production order')} ${props.order.order_number}?`,
    );

    if (result.isConfirmed) {
        router.post(
            `/production-orders/${props.order.id}/send`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Berhasil!', `${productionOrderLabel.value} berhasil dikirim`);
                },
            },
        );
    }
};

const statusBadge = (status: string) => {
    const colors: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        sent: 'bg-indigo-100 text-indigo-800',
        in_progress: 'bg-blue-100 text-blue-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const statusLabel = (status: string) => {
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
</script>

<template>
    <AppLayout>
        <Head :title="`${productionOrderLabel} ${order.order_number}`" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ productionOrderLabel }} {{ order.order_number }}</h2>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ order.preparation_order.order_number }} — {{ order.preparation_order.pattern?.name || 'No Pattern' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link href="/production-orders" class="text-sm text-gray-600 hover:text-gray-900"> ← Kembali </Link>
                        <button
                            v-if="canSend"
                            @click="sendOrder"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-indigo-700"
                        >
                            Kirim / Mulai
                        </button>
                        <button
                            v-if="canReceive"
                            @click="showReceive = !showReceive"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase hover:bg-gray-50"
                        >
                            {{ showReceive ? 'Tutup Form' : 'Terima Batch' }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg lg:col-span-2">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Detail</h3>
                                <span :class="statusBadge(order.status)" class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold">
                                    {{ statusLabel(order.status) }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-xs text-gray-500">Tipe</p>
                                    <p class="text-sm font-medium text-gray-900">{{ order.type === 'internal' ? 'Internal' : 'Eksternal' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-xs text-gray-500">{{ contractorLabel }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ order.contractor?.name || '-' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-xs text-gray-500">Tanggal Estimasi Selesai</p>
                                    <p class="text-sm font-medium text-gray-900">
                                        {{
                                            order.estimated_completion_date
                                                ? new Date(order.estimated_completion_date).toLocaleDateString('id-ID')
                                                : '-'
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div class="rounded-lg bg-green-50 p-4">
                                    <p class="text-xs text-gray-600">Diterima (total)</p>
                                    <p class="text-lg font-bold text-green-700">{{ order.quantity_produced }} pcs</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <p class="text-xs text-gray-600">Good / Reject</p>
                                    <p class="text-lg font-bold text-gray-900">{{ order.quantity_good }} / {{ order.quantity_reject }}</p>
                                </div>
                            </div>

                            <div v-if="order.notes" class="mt-4">
                                <p class="text-xs text-gray-500">Catatan</p>
                                <p class="text-sm text-gray-900">{{ order.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900">Terima Batch</h3>
                            <p class="mt-1 text-sm text-gray-600">Input hasil produksi dan QC.</p>

                            <div v-if="!canReceive" class="mt-4 text-sm text-gray-500">Form tersedia setelah order dikirim / dimulai.</div>

                            <div v-else class="mt-4" v-show="showReceive">
                                <ProductionBatchForm :order="order" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900">Batch</h3>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Grade</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-if="order.batches.length === 0">
                                        <td colspan="5" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada batch.</td>
                                    </tr>
                                    <tr v-for="b in order.batches" :key="b.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ b.batch_number }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ new Date(b.received_date).toLocaleDateString('id-ID') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ b.quantity_received }} (good {{ b.quantity_good }}, defect {{ b.quantity_defect }}, reject
                                            {{ b.quantity_reject }})
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ b.grade }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ b.expiry_date ? new Date(b.expiry_date).toLocaleDateString('id-ID') : '-' }}
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
