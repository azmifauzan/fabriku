<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { X, Plus, Minus, RefreshCw } from 'lucide-vue-next';

interface AdjustmentTypes {
    [key: string]: string;
}

interface Item {
    id: number;
    sku: string;
    name: string;
    current_stock: number;
    source_type?: string;
}

const props = defineProps<{
    show: boolean;
    item: Item;
    adjustmentTypes: AdjustmentTypes;
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    type: 'set' as 'add' | 'subtract' | 'set',
    quantity: 0,
    adjustment_type: 'correction',
    reason: '',
    notes: '',
});

// Calculate preview of new stock
const previewStock = computed(() => {
    const current = props.item.current_stock;
    switch (form.type) {
        case 'add':
            return current + form.quantity;
        case 'subtract':
            return Math.max(0, current - form.quantity);
        case 'set':
            return form.quantity;
        default:
            return current;
    }
});

// Difference from current stock
const stockDifference = computed(() => {
    return previewStock.value - props.item.current_stock;
});

// Reset form when modal opens
watch(() => props.show, (newValue) => {
    if (newValue) {
        form.reset();
        form.quantity = props.item.current_stock;
        form.type = 'set';
    }
});

const submit = () => {
    form.post(`/inventory/items/${props.item.id}/adjust`, {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};

const close = () => {
    form.reset();
    emit('close');
};

// Filter adjustment types based on item source
const availableAdjustmentTypes = computed(() => {
    const types = { ...props.adjustmentTypes };
    // Opening balance only available for manual entry items
    if (props.item.source_type === 'production') {
        delete types.opening_balance;
    }
    return types;
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50" @click="close"></div>

                <!-- Modal -->
                <div class="flex min-h-full items-center justify-center p-4">
                    <Transition
                        enter-active-class="transition-all duration-200 ease-out"
                        enter-from-class="scale-95 opacity-0"
                        enter-to-class="scale-100 opacity-100"
                        leave-active-class="transition-all duration-150 ease-in"
                        leave-from-class="scale-100 opacity-100"
                        leave-to-class="scale-95 opacity-0"
                    >
                        <div v-if="show" class="relative w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-800">
                            <!-- Header -->
                            <div class="mb-6 flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        Adjust Stock
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ item.name }} ({{ item.sku }})
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="close"
                                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 dark:hover:bg-gray-700"
                                >
                                    <X class="h-5 w-5" />
                                </button>
                            </div>

                            <form @submit.prevent="submit" class="space-y-5">
                                <!-- Current Stock Info -->
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Stock Saat Ini</span>
                                        <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ item.current_stock }}</span>
                                    </div>
                                </div>

                                <!-- Adjustment Type Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Tipe Operasi
                                    </label>
                                    <div class="mt-2 grid grid-cols-3 gap-2">
                                        <label
                                            class="flex cursor-pointer flex-col items-center rounded-lg border p-3 transition-all"
                                            :class="form.type === 'add' 
                                                ? 'border-green-500 bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' 
                                                : 'border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700'"
                                        >
                                            <input type="radio" v-model="form.type" value="add" class="sr-only" />
                                            <Plus class="h-5 w-5" />
                                            <span class="mt-1 text-xs font-medium">Tambah</span>
                                        </label>
                                        <label
                                            class="flex cursor-pointer flex-col items-center rounded-lg border p-3 transition-all"
                                            :class="form.type === 'subtract' 
                                                ? 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' 
                                                : 'border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700'"
                                        >
                                            <input type="radio" v-model="form.type" value="subtract" class="sr-only" />
                                            <Minus class="h-5 w-5" />
                                            <span class="mt-1 text-xs font-medium">Kurangi</span>
                                        </label>
                                        <label
                                            class="flex cursor-pointer flex-col items-center rounded-lg border p-3 transition-all"
                                            :class="form.type === 'set' 
                                                ? 'border-blue-500 bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' 
                                                : 'border-gray-200 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700'"
                                        >
                                            <input type="radio" v-model="form.type" value="set" class="sr-only" />
                                            <RefreshCw class="h-5 w-5" />
                                            <span class="mt-1 text-xs font-medium">Set Nilai</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Quantity Input -->
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ form.type === 'set' ? 'Stock Baru' : 'Jumlah' }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="quantity"
                                        v-model.number="form.quantity"
                                        type="number"
                                        min="0"
                                        required
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-300': form.errors.quantity }"
                                    />
                                    <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.quantity }}
                                    </p>
                                </div>

                                <!-- Preview -->
                                <div class="rounded-lg border-2 p-4" 
                                     :class="stockDifference > 0 
                                        ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' 
                                        : stockDifference < 0 
                                            ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20'
                                            : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-700/50'">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium" 
                                              :class="stockDifference > 0 
                                                ? 'text-green-700 dark:text-green-400' 
                                                : stockDifference < 0 
                                                    ? 'text-red-700 dark:text-red-400'
                                                    : 'text-gray-700 dark:text-gray-300'">
                                            Stock Baru
                                        </span>
                                        <div class="text-right">
                                            <span class="text-2xl font-bold"
                                                  :class="stockDifference > 0 
                                                    ? 'text-green-700 dark:text-green-400' 
                                                    : stockDifference < 0 
                                                        ? 'text-red-700 dark:text-red-400'
                                                        : 'text-gray-900 dark:text-white'">
                                                {{ previewStock }}
                                            </span>
                                            <p v-if="stockDifference !== 0" class="text-xs"
                                               :class="stockDifference > 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ stockDifference > 0 ? '+' : '' }}{{ stockDifference }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Adjustment Type -->
                                <div>
                                    <label for="adjustment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Kategori Adjustment <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="adjustment_type"
                                        v-model="form.adjustment_type"
                                        required
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-300': form.errors.adjustment_type }"
                                    >
                                        <option v-for="(label, type) in availableAdjustmentTypes" :key="type" :value="type">
                                            {{ label }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.adjustment_type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.adjustment_type }}
                                    </p>
                                </div>

                                <!-- Reason -->
                                <div>
                                    <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Alasan <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="reason"
                                        v-model="form.reason"
                                        type="text"
                                        required
                                        placeholder="Contoh: Stock opname, barang rusak, dll"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-300': form.errors.reason }"
                                    />
                                    <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.reason }}
                                    </p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Catatan Tambahan
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="2"
                                        placeholder="Catatan tambahan (opsional)"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end gap-3 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <button
                                        type="button"
                                        @click="close"
                                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing || stockDifference === 0"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ form.processing ? 'Menyimpan...' : 'Simpan Adjustment' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
