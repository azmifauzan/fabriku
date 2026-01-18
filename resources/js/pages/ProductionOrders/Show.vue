<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

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
    type: string;
    status: string;
    estimated_completion_date: string | null;
    sent_date: string | null;
    completed_date: string | null;
    priority: string;
    labor_cost: number;
    notes: string | null;
}

const props = defineProps<{
    order: ProductionOrder;
}>();

const { term, termLower } = useBusinessContext();
const { confirm, confirmDelete, showSuccess } = useSweetAlert();

const productionOrderLabel = computed(() => 'Production Order');
const patternLabel = computed(() => term('pattern', 'Pattern'));
const contractorLabel = computed(() => term('contractor', 'Kontraktor'));

const canSend = computed(() => {
    return props.order.status === 'draft';
});

const canMarkComplete = computed(() => {
    return ['sent', 'in_progress'].includes(props.order.status);
});

const sendOrder = async () => {
    const result = await confirm(
        `Kirim ${productionOrderLabel.value}`,
        `Apakah Anda yakin ingin mengirim ${productionOrderLabel.value.toLowerCase()} ${props.order.order_number}?`,
        'Ya, Kirim',
        'question',
        '#4f46e5',
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

const markComplete = async () => {
    const result = await confirm(
        `Tandai Selesai`,
        `Apakah Anda yakin ingin menandai ${productionOrderLabel.value.toLowerCase()} ${props.order.order_number} sebagai selesai?`,
        'Ya, Tandai Selesai',
        'question',
        '#10b981',
    );

    if (result.isConfirmed) {
        router.post(
            `/production-orders/${props.order.id}/mark-complete`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    showSuccess('Berhasil!', `${productionOrderLabel.value} berhasil ditandai selesai`);
                },
            },
        );
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`${productionOrderLabel} #${order.order_number}`" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-800">{{ productionOrderLabel }} #{{ order.order_number }}</h2>
                                <p class="text-sm text-gray-600">{{ order.preparation_order.order_number }}</p>
                            </div>
                            <div class="flex gap-2">
                                <button
                                    v-if="canSend"
                                    @click="sendOrder"
                                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-indigo-700"
                                >
                                    {{ order.type === 'external' ? 'Kirim ke Kontraktor' : 'Mulai Produksi' }}
                                </button>
                                <button
                                    v-if="canMarkComplete"
                                    @click="markComplete"
                                    class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-green-700"
                                >
                                    Tandai Selesai
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <h3 class="text-lg font-medium text-gray-900">Detail Order</h3>
                                <span :class="statusBadge(order.status)" class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold">
                                    {{ statusLabel(order.status) }}
                                </span>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="`/production-orders/${order.id}/edit`"
                                    v-if="order.status === 'draft' || order.status === 'sent'"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase hover:bg-gray-50"
                                >
                                    Edit
                                </Link>
                                <Link href="/production-orders" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase hover:bg-gray-50"> ← Kembali </Link>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-xs text-gray-500">{{ patternLabel }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ order.preparation_order.pattern?.name || '-' }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ order.preparation_order.pattern?.code || '' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-xs text-gray-500">Quantity</p>
                                <p class="text-sm font-medium text-gray-900">{{ order.preparation_order.output_quantity }} pcs</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-xs text-gray-500">Tipe</p>
                                <p class="text-sm font-medium text-gray-900">{{ order.type === 'internal' ? 'Internal' : 'Eksternal' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-xs text-gray-500">{{ contractorLabel }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ order.contractor?.name || '-' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-xs text-gray-500">Prioritas</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ order.priority === 'urgent' ? 'Urgent' : order.priority === 'high' ? 'Tinggi' : order.priority === 'low' ? 'Rendah' : 'Normal' }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-4">
                                <p class="text-xs text-gray-500">Biaya Tenaga Kerja</p>
                                <p class="text-sm font-medium text-gray-900">Rp {{ order.labor_cost?.toLocaleString('id-ID') || 0 }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="rounded-lg bg-blue-50 p-4">
                                <p class="text-xs text-gray-600">Tanggal Estimasi Selesai</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{
                                        order.estimated_completion_date
                                            ? new Date(order.estimated_completion_date).toLocaleDateString('id-ID')
                                            : '-'
                                    }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-indigo-50 p-4">
                                <p class="text-xs text-gray-600">Tanggal Kirim/Mulai</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ order.sent_date ? new Date(order.sent_date).toLocaleDateString('id-ID') : '-' }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-green-50 p-4">
                                <p class="text-xs text-gray-600">Tanggal Selesai</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ order.completed_date ? new Date(order.completed_date).toLocaleDateString('id-ID') : '-' }}
                                </p>
                            </div>
                        </div>

                        <div v-if="order.notes" class="mt-4 rounded-lg bg-yellow-50 p-4">
                            <p class="text-xs text-gray-600 font-medium">Catatan</p>
                            <p class="text-sm text-gray-900 mt-1">{{ order.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
