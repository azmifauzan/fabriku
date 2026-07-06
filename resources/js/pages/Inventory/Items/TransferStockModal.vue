<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Plus, X, Trash2 } from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface InventoryLocation {
    id: number;
    name: string;
    code: string;
    capacity?: number | null;
    available_capacity?: number;
}

interface Item {
    id: number;
    sku: string;
    name: string;
    current_stock: number;
    reserved_stock: number;
    inventory_location?: {
        id: number;
    };
}

const props = defineProps<{
    show: boolean;
    item: Item;
    locations?: InventoryLocation[];
}>();

const emit = defineEmits<{
    close: [];
}>();

const availableStock = computed(() => {
    return Math.max(0, props.item.current_stock - props.item.reserved_stock);
});

const form = useForm({
    splits: [
        { location_id: null as number | null, quantity: 1 }
    ],
    reason: '',
    notes: '',
});

const totalTransferred = computed(() => {
    return form.splits.reduce((sum, split) => sum + (Number(split.quantity) || 0), 0);
});

const isExceedingStock = computed(() => {
    return totalTransferred.value > availableStock.value;
});

const addSplit = () => {
    form.splits.push({ location_id: null, quantity: 1 });
};

const removeSplit = (index: number) => {
    form.splits.splice(index, 1);
    if (form.splits.length === 0) {
        addSplit();
    }
};

const availableLocationsFor = (index: number) => {
    if (!props.locations) return [];
    
    // Exclude locations already selected in other splits, and the item's current location
    const selectedLocationIds = form.splits
        .filter((_, i) => i !== index)
        .map(s => s.location_id)
        .filter(id => id !== null);

    return props.locations.filter(loc => {
        return loc.id !== props.item.inventory_location?.id && !selectedLocationIds.includes(loc.id);
    });
};

const capacityErrorFor = (index: number) => {
    const split = form.splits[index];
    if (!split.location_id || !split.quantity || !props.locations) return null;

    const location = props.locations.find(l => l.id === split.location_id);
    if (!location || location.capacity === null || location.available_capacity === undefined) return null;

    if (split.quantity > location.available_capacity) {
        return `Kapasitas tidak cukup (sisa: ${location.available_capacity})`;
    }
    return null;
};

const hasCapacityErrors = computed(() => {
    return form.splits.some((_, index) => capacityErrorFor(index) !== null);
});

watch(
    () => props.show,
    (newValue) => {
        if (newValue) {
            form.reset();
        }
    },
);

const submit = () => {
    if (isExceedingStock.value || hasCapacityErrors.value) return;

    form.post(`/inventory/items/${props.item.id}/transfer`, {
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
                <div class="fixed inset-0 bg-black/50" @click="close"></div>

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
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pindah/Split Stock</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ item.name }} ({{ item.sku }})</p>
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
                                <!-- Available Stock Info -->
                                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-blue-700 dark:text-blue-400">Stock Tersedia</span>
                                        <span class="text-2xl font-bold text-blue-900 dark:text-blue-200">{{ availableStock }}</span>
                                    </div>
                                </div>

                                <!-- Splits -->
                                <div class="space-y-3">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Lokasi Tujuan <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <div v-for="(split, index) in form.splits" :key="index" class="flex flex-col gap-2 rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                        <div class="flex gap-2">
                                            <div class="flex-1">
                                                <select
                                                    v-model="split.location_id"
                                                    :name="`splits[${index}][location_id]`"
                                                    required
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                    :class="{ 'border-red-300': form.errors[`splits.${index}.location_id`] }"
                                                >
                                                    <option :value="null" disabled>Pilih Rak</option>
                                                    <option v-for="loc in availableLocationsFor(index)" :key="loc.id" :value="loc.id">
                                                        {{ loc.name }} ({{ loc.code }}) 
                                                        <template v-if="loc.capacity !== null">- Sisa: {{ loc.available_capacity }}</template>
                                                    </option>
                                                </select>
                                                <p v-if="form.errors[`splits.${index}.location_id`]" class="mt-1 text-xs text-red-600">
                                                    {{ form.errors[`splits.${index}.location_id`] }}
                                                </p>
                                            </div>
                                            <div class="w-24 shrink-0">
                                                <input
                                                    v-model.number="split.quantity"
                                                    :name="`splits[${index}][quantity]`"
                                                    type="number"
                                                    min="1"
                                                    required
                                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                    :class="{ 'border-red-300': form.errors[`splits.${index}.quantity`] || capacityErrorFor(index) }"
                                                />
                                            </div>
                                            <button
                                                v-if="form.splits.length > 1"
                                                type="button"
                                                @click="removeSplit(index)"
                                                class="shrink-0 rounded-lg p-2 text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20"
                                                title="Hapus Rak"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <p v-if="capacityErrorFor(index)" class="text-xs text-red-600">
                                            {{ capacityErrorFor(index) }}
                                        </p>
                                        <p v-if="form.errors[`splits.${index}.quantity`]" class="text-xs text-red-600">
                                            {{ form.errors[`splits.${index}.quantity`] }}
                                        </p>
                                    </div>
                                    
                                    <button
                                        type="button"
                                        @click="addSplit"
                                        class="inline-flex w-full items-center justify-center gap-1 rounded-lg border border-dashed border-gray-300 py-2 text-sm font-medium text-gray-600 hover:border-gray-400 hover:bg-gray-50 hover:text-gray-900 dark:border-gray-600 dark:text-gray-400 dark:hover:border-gray-500 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                                    >
                                        <Plus class="h-4 w-4" />
                                        Tambah Rak
                                    </button>
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
                                        placeholder="Contoh: Pemindahan stok ke rak depan"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-300': form.errors.reason }"
                                    />
                                    <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.reason }}
                                    </p>
                                </div>

                                <!-- Notes -->
                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300"> Catatan Tambahan </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="2"
                                        placeholder="Catatan tambahan (opsional)"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-col gap-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Dipindah:</span>
                                        <span 
                                            class="text-lg font-bold"
                                            :class="isExceedingStock ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'"
                                        >
                                            {{ totalTransferred }}
                                        </span>
                                    </div>
                                    <p v-if="isExceedingStock" class="text-sm text-red-600">
                                        Total yang dipindah melebihi stock tersedia.
                                    </p>
                                    <p v-if="form.errors.splits" class="text-sm text-red-600">
                                        {{ form.errors.splits }}
                                    </p>

                                    <div class="flex justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="close"
                                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="form.processing || isExceedingStock || hasCapacityErrors || totalTransferred <= 0"
                                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            {{ form.processing ? 'Menyimpan...' : 'Pindah Stock' }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
