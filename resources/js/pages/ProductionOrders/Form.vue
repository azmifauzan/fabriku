<script setup lang="ts">
import { useBusinessContext } from '@/composables/useBusinessContext';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
    type: string;
    specialty: string;
    rate_per_piece: number;
    rate_per_hour: number;
}

interface ProductionOrder {
    id?: number;
    order_number: string;
    preparation_order_id: number;
    contractor_id: number | null;
    type: string;
    estimated_completion_date: string | null;
    status: string;
    notes: string;
    labor_cost: number | null;
    priority: string;
}

const props = defineProps<{
    preparationOrders: PreparationOrder[];
    contractors: Contractor[];
    productionOrder?: ProductionOrder;
}>();

const form = useForm({
    preparation_order_id: props.productionOrder?.preparation_order_id || 0,
    type: props.productionOrder?.type || 'internal',
    contractor_id: props.productionOrder?.contractor_id || null,
    estimated_completion_date: props.productionOrder?.estimated_completion_date || '',
    labor_cost: props.productionOrder?.labor_cost ?? 0,
    priority: props.productionOrder?.priority || 'normal',
    notes: props.productionOrder?.notes || '',
});

const selectedPreparationOrder = computed(() => {
    return props.preparationOrders.find((po) => po.id === form.preparation_order_id);
});

const selectedContractor = computed(() => {
    return props.contractors.find((c) => c.id === form.contractor_id);
});

const availableQuantity = computed(() => {
    if (!selectedPreparationOrder.value) return 0;
    return selectedPreparationOrder.value.output_quantity;
});

const { term, termLower } = useBusinessContext();

const productionOrderLabel = computed(() => 'Production Order');
const productionOrderLabelLower = computed(() => 'production order');
const preparationLabel = computed(() => term('preparation', 'Persiapan'));
const contractorLabel = computed(() => term('contractor', 'Kontraktor'));
const patternLabel = computed(() => term('pattern', 'Pattern'));

const submit = () => {
    if (props.productionOrder?.id) {
        form.put(`/production-orders/${props.productionOrder.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/production-orders', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.productionOrder?.id;
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? `Edit ${productionOrderLabel}` : `Buat ${productionOrderLabel}`" />

        <!-- Page Content -->
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ isEditing ? `Edit ${productionOrderLabel}` : `Buat ${productionOrderLabel} Baru` }}
                                </h2>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{
                                        isEditing
                                            ? `Perbarui ${productionOrderLabelLower}`
                                            : `Buat order produksi dari hasil ${preparationLabel.toLowerCase()}`
                                    }}
                                </p>
                            </div>
                            <Link
                                href="/production-orders"
                                class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                            >
                                ← Kembali
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Preparation Order Selection -->
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Hasil {{ preparationLabel }}</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Pilih Hasil {{ preparationLabel }} <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.preparation_order_id"
                                            required
                                            :disabled="isEditing"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.preparation_order_id, 'bg-gray-100 dark:bg-gray-600': isEditing }"
                                        >
                                            <option value="0" disabled>-- Pilih Hasil {{ preparationLabel }} --</option>
                                            <option v-for="po in preparationOrders" :key="po.id" :value="po.id">
                                                {{ po.order_number }} - {{ po.pattern?.name || 'N/A' }} ({{ po.output_quantity }} pcs)
                                            </option>
                                        </select>
                                        <p v-if="form.errors.preparation_order_id" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.preparation_order_id }}
                                        </p>
                                    </div>

                                    <!-- Preparation Order Info -->
                                    <div v-if="selectedPreparationOrder" class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ patternLabel }}</p>
                                                <p class="text-sm text-gray-900 dark:text-white">
                                                    {{ selectedPreparationOrder.pattern?.name || 'N/A' }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ selectedPreparationOrder.pattern?.code || '-' }}
                                                </p>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Output Quantity</p>
                                                <p class="text-lg font-bold text-green-600 dark:text-green-400">
                                                    {{ selectedPreparationOrder.output_quantity }} pcs
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Production Type & Details -->
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Detail Produksi</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Tipe Produksi <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.type"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.type }"
                                        >
                                            <option value="internal">Internal</option>
                                            <option value="external">Eksternal ({{ contractorLabel }})</option>
                                        </select>
                                        <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
                                    </div>

                                    <div v-if="form.type === 'external'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ contractorLabel }} <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.contractor_id"
                                            :required="form.type === 'external'"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.contractor_id }"
                                        >
                                            <option :value="null" disabled>-- Pilih {{ contractorLabel }} --</option>
                                            <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                                                {{ contractor.name }} - Rp {{ contractor.rate_per_piece.toLocaleString('id-ID') }}/pcs
                                            </option>
                                        </select>
                                        <p v-if="form.errors.contractor_id" class="mt-1 text-sm text-red-600">{{ form.errors.contractor_id }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Tanggal Estimasi Selesai </label>
                                        <input
                                            v-model="form.estimated_completion_date"
                                            type="date"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.estimated_completion_date }"
                                        />
                                        <p v-if="form.errors.estimated_completion_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.estimated_completion_date }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Priority & Labor Cost -->
                            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Prioritas & Biaya</h3>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Prioritas <span class="text-red-500">*</span>
                                        </label>
                                        <select
                                            v-model="form.priority"
                                            required
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.priority }"
                                        >
                                            <option value="low">Low</option>
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                        <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600">{{ form.errors.priority }}</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Biaya Tenaga Kerja (estimasi)
                                        </label>
                                        <input
                                            v-model.number="form.labor_cost"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                            :class="{ 'border-red-500': form.errors.labor_cost }"
                                        />
                                        <p v-if="form.errors.labor_cost" class="mt-1 text-sm text-red-600">{{ form.errors.labor_cost }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Catatan </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    :class="{ 'border-red-500': form.errors.notes }"
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex flex-col items-stretch justify-end gap-3 sm:flex-row sm:items-center">
                                <Link
                                    href="/production-orders"
                                    class="order-2 rounded-md border border-gray-300 bg-white px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 sm:order-1 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                                >
                                    Batal
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="order-1 rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 sm:order-2"
                                >
                                    {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Buat Order' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
