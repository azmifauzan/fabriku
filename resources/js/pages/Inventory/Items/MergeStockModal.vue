<script setup lang="ts">
import { formatNumber } from '@/lib/utils';
import { useForm } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import { watch } from 'vue';

interface MergeCandidate {
    id: number;
    sku: string;
    product_name: string;
    current_quantity: number;
}

interface Item {
    id: number;
    sku: string;
    name: string;
}

const props = defineProps<{
    show: boolean;
    item: Item;
    candidates?: MergeCandidate[];
}>();

const emit = defineEmits<{
    close: [];
}>();

const form = useForm({
    destination_item_id: null as number | null,
    reason: '',
    notes: '',
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
    form.post(`/inventory/items/${props.item.id}/merge`, {
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
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gabung Item</h3>
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

                            <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                                Seluruh stok item ini akan dipindah ke item tujuan, lalu item ini dihapus.
                            </p>

                            <form v-if="candidates && candidates.length > 0" @submit.prevent="submit" class="space-y-5">
                                <div>
                                    <label for="destination_item_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Gabung ke Item <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        id="destination_item_id"
                                        v-model="form.destination_item_id"
                                        required
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-300': form.errors.destination_item_id }"
                                    >
                                        <option :value="null" disabled>Pilih item tujuan</option>
                                        <option v-for="candidate in candidates" :key="candidate.id" :value="candidate.id">
                                            {{ candidate.sku }} — stok saat ini: {{ formatNumber(candidate.current_quantity) }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.destination_item_id" class="mt-1 text-xs text-red-600">
                                        {{ form.errors.destination_item_id }}
                                    </p>
                                </div>

                                <div>
                                    <label for="merge_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Alasan <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="merge_reason"
                                        v-model="form.reason"
                                        type="text"
                                        required
                                        placeholder="Contoh: Konsolidasi stok di rak yang sama"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        :class="{ 'border-red-300': form.errors.reason }"
                                    />
                                    <p v-if="form.errors.reason" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.reason }}
                                    </p>
                                </div>

                                <div>
                                    <label for="merge_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Catatan Tambahan
                                    </label>
                                    <textarea
                                        id="merge_notes"
                                        v-model="form.notes"
                                        rows="2"
                                        placeholder="Catatan tambahan (opsional)"
                                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

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
                                        :disabled="form.processing || !form.destination_item_id"
                                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {{ form.processing ? 'Menggabungkan...' : 'Gabung Item' }}
                                    </button>
                                </div>
                            </form>

                            <div v-else class="space-y-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Tidak ada item lain di rak yang sama dengan produk, harga, grade, dan status yang sama untuk digabungkan.
                                </p>
                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        @click="close"
                                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                                    >
                                        Tutup
                                    </button>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
