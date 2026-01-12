<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { useBusinessContext } from '@/composables/useBusinessContext';

interface Material {
    id: number;
    name: string;
    code: string;
    unit: string;
    current_stock: number;
}

interface Pattern {
    id: number;
    code: string;
    name: string;
    product_type: string;
    size?: string;
    estimated_cost: number;
    materials: Array<{
        material_id: number;
        quantity_needed: number;
        material: Material;
    }>;
}

interface CuttingOrder {
    id?: number;
    order_number: string;
    pattern_id: number;
    planned_quantity: number;
    actual_quantity: number;
    status: string;
    priority: string;
    notes?: string;
    scheduled_date?: string;
    completed_date?: string;
}

interface Props {
    cuttingOrder?: CuttingOrder;
    patterns: Pattern[];
    materials: Material[];
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const form = useForm({
    pattern_id: props.cuttingOrder?.pattern_id || 0,
    planned_quantity: props.cuttingOrder?.planned_quantity || 1,
    actual_quantity: props.cuttingOrder?.actual_quantity || 0,
    status: props.cuttingOrder?.status || 'draft',
    priority: props.cuttingOrder?.priority || 'normal',
    notes: props.cuttingOrder?.notes || '',
    scheduled_date: props.cuttingOrder?.scheduled_date || '',
    completed_date: props.cuttingOrder?.completed_date || '',
});

const selectedPattern = computed(() => {
    if (form.pattern_id === 0) return null;
    return props.patterns.find(p => p.id === form.pattern_id) || null;
});

const requiredMaterials = computed(() => {
    if (!selectedPattern.value) return [];
    
    return selectedPattern.value.materials.map(pm => ({
        material: pm.material,
        quantity_needed: pm.quantity_needed,
        total_needed: pm.quantity_needed * form.planned_quantity,
        available: pm.material.current_stock,
        sufficient: pm.material.current_stock >= (pm.quantity_needed * form.planned_quantity),
    }));
});

const allMaterialsSufficient = computed(() => {
    return requiredMaterials.value.every(rm => rm.sufficient);
});

const estimatedCost = computed(() => {
    if (!selectedPattern.value) return 0;
    return selectedPattern.value.estimated_cost * form.planned_quantity;
});

const statusOptions = [
    { value: 'draft', label: 'Draft' },
    { value: 'in_progress', label: 'Dalam Proses' },
    { value: 'completed', label: 'Selesai' },
    { value: 'cancelled', label: 'Dibatalkan' },
];

const priorityOptions = [
    { value: 'low', label: 'Rendah' },
    { value: 'normal', label: 'Normal' },
    { value: 'high', label: 'Tinggi' },
    { value: 'urgent', label: 'Urgent' },
];

const canEditStatus = computed(() => {
    if (!props.isEdit) return true;
    const currentStatus = props.cuttingOrder?.status || 'draft';
    return currentStatus === 'draft' || currentStatus === 'in_progress';
});

const submit = () => {
    if (props.isEdit && props.cuttingOrder?.id) {
        form.put(`/cutting-orders/${props.cuttingOrder.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/cutting-orders', {
            preserveScroll: true,
        });
    }
};

const goBack = () => {
    router.visit('/cutting-orders');
};

const { term } = useBusinessContext();

const preparationOrderLabel = computed(() => term('preparation_order', 'Cutting Order'));
const patternLabel = computed(() => term('pattern', 'Pattern'));

// Auto-set completed date when status is completed
watch(() => form.status, (newStatus) => {
    if (newStatus === 'completed' && !form.completed_date) {
        const today = new Date();
        form.completed_date = today.toISOString().split('T')[0];
    } else if (newStatus !== 'completed') {
        form.completed_date = '';
    }
});
</script>

<template>
    <AppLayout>
        <Head :title="isEdit ? `Edit ${preparationOrderLabel}` : `Buat ${preparationOrderLabel} Baru`" />

        <!-- Page Content -->
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ isEdit ? `Edit ${preparationOrderLabel}` : `Buat ${preparationOrderLabel} Baru` }}
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ isEdit ? `Ubah informasi ${preparationOrderLabel.toLowerCase()}` : `Tambahkan ${preparationOrderLabel.toLowerCase()} baru` }}
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="goBack"
                                class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300"
                            >
                                ← Kembali
                            </button>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                        <!-- Order Information -->
                        <div class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Informasi {{ preparationOrderLabel }}
                            </h3>

                            <div v-if="isEdit" class="rounded-lg bg-gray-50 dark:bg-gray-700 p-4">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Order Number: <span class="font-bold">{{ cuttingOrder?.order_number }}</span>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Pattern Selection -->
                                <div class="sm:col-span-2">
                                    <label for="pattern_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ patternLabel }} <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="pattern_id"
                                        v-model="form.pattern_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :disabled="isEdit"
                                        required
                                    >
                                        <option :value="0">-- Pilih {{ patternLabel }} --</option>
                                        <option v-for="pattern in patterns" :key="pattern.id" :value="pattern.id">
                                            {{ pattern.name }} ({{ pattern.code }}) - {{ pattern.product_type }} {{ pattern.size || '' }}
                                        </option>
                                    </select>
                                    <p v-if="isEdit" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        {{ patternLabel }} tidak bisa diubah setelah order dibuat
                                    </p>
                                    <p v-if="form.errors.pattern_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.pattern_id }}
                                    </p>
                                </div>

                                <!-- Planned Quantity -->
                                <div>
                                    <label for="planned_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Jumlah Target <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="planned_quantity"
                                        v-model.number="form.planned_quantity"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Jumlah produk yang direncanakan
                                    </p>
                                    <p v-if="form.errors.planned_quantity" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.planned_quantity }}
                                    </p>
                                </div>

                                <!-- Actual Quantity -->
                                <div>
                                    <label for="actual_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Jumlah Aktual
                                    </label>
                                    <input
                                        id="actual_quantity"
                                        v-model.number="form.actual_quantity"
                                        type="number"
                                        min="0"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Jumlah produk yang berhasil diproses
                                    </p>
                                    <p v-if="form.errors.actual_quantity" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.actual_quantity }}
                                    </p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :disabled="!canEditStatus"
                                        required
                                    >
                                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <p v-if="!canEditStatus" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Status tidak bisa diubah untuk order yang sudah selesai/dibatalkan
                                    </p>
                                    <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.status }}
                                    </p>
                                </div>

                                <!-- Priority -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Prioritas <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="priority"
                                        v-model="form.priority"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required
                                    >
                                        <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.priority }}
                                    </p>
                                </div>

                                <!-- Scheduled Date -->
                                <div>
                                    <label for="scheduled_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Jadwal
                                    </label>
                                    <input
                                        id="scheduled_date"
                                        v-model="form.scheduled_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.scheduled_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.scheduled_date }}
                                    </p>
                                </div>

                                <!-- Completed Date -->
                                <div>
                                    <label for="completed_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Selesai
                                    </label>
                                    <input
                                        id="completed_date"
                                        v-model="form.completed_date"
                                        type="date"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :disabled="form.status !== 'completed'"
                                    />
                                    <p v-if="form.errors.completed_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.completed_date }}
                                    </p>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Catatan
                                </label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </div>

                        <!-- Material Requirements -->
                        <div v-if="selectedPattern" class="mt-8 space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Kebutuhan Bahan
                            </h3>

                            <div v-if="!allMaterialsSufficient" class="rounded-md bg-yellow-50 dark:bg-yellow-900/20 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            Stok bahan tidak mencukupi
                                        </h3>
                                        <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                            <p>Beberapa bahan tidak memiliki stok yang cukup untuk order ini.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600">
                                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Bahan
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Per Unit
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Total Butuh
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Stok Tersedia
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                        <tr v-for="rm in requiredMaterials" :key="rm.material.id">
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-white">
                                                {{ rm.material.name }}
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ rm.material.code }}</div>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ rm.quantity_needed }} {{ rm.material.unit }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                                {{ rm.total_needed }} {{ rm.material.unit }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {{ rm.available }} {{ rm.material.unit }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                                <span
                                                    :class="[
                                                        rm.sufficient
                                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                                                            : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                                        'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
                                                    ]"
                                                >
                                                    {{ rm.sufficient ? 'Cukup' : 'Kurang' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="flex justify-end rounded-lg bg-gray-50 dark:bg-gray-700 p-4">
                                <div class="text-right">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Estimasi Biaya Total</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                        Rp {{ estimatedCost.toLocaleString('id-ID') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                            <button
                                type="button"
                                @click="goBack"
                                class="order-2 sm:order-1 rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="order-1 sm:order-2 inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                            </button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
