<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="px-6 py-4">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/50">
                    <ArrowRightLeft :size="20" class="text-indigo-600 dark:text-indigo-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Update Status Pesanan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Status saat ini:
                        <span class="font-medium" :class="currentStatusClass">{{ currentStatusLabel }}</span>
                    </p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Target Status -->
                <div>
                    <label for="status-select" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Status Baru <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="status-select"
                        v-model="form.status"
                        required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.status }"
                    >
                        <option value="">— Pilih status —</option>
                        <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.status }}</p>
                </div>

                <!-- Resi Number (conditional when shipped) -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition duration-150 ease-in"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-1"
                >
                    <div v-if="form.status === 'shipped'">
                        <label for="resi-number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            No. Resi Pengiriman
                        </label>
                        <input
                            id="resi-number"
                            v-model="form.resi_number"
                            type="text"
                            placeholder="Contoh: JNE123456789ID"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            :class="{ 'border-red-500': form.errors.resi_number }"
                        />
                        <p v-if="form.errors.resi_number" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.resi_number }}</p>
                    </div>
                </Transition>

                <!-- Cancel warning -->
                <div
                    v-if="form.status === 'cancelled'"
                    class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20"
                >
                    <AlertTriangle :size="16" class="mt-0.5 flex-shrink-0 text-red-600 dark:text-red-400" />
                    <p class="text-xs text-red-700 dark:text-red-300">
                        Membatalkan pesanan akan <strong>membebaskan stok</strong> yang sudah direservasi. Tindakan ini tidak dapat diurungkan.
                    </p>
                </div>

                <!-- Completed note -->
                <div
                    v-else-if="form.status === 'completed'"
                    class="flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/20"
                >
                    <CheckCircle2 :size="16" class="mt-0.5 flex-shrink-0 text-green-600 dark:text-green-400" />
                    <p class="text-xs text-green-700 dark:text-green-300">
                        Menyelesaikan pesanan akan <strong>mengurangi stok inventory</strong> secara permanen.
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 pt-2 dark:border-gray-700">
                    <button
                        type="button"
                        @click="close"
                        :disabled="form.processing"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.status"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors focus:ring-2 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                        :class="
                            form.status === 'cancelled'
                                ? 'bg-red-600 hover:bg-red-700 focus:ring-red-500'
                                : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'
                        "
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import { updateStatus } from '@/actions/App/Http/Controllers/SalesOrderController';
import Modal from '@/components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import { AlertTriangle, ArrowRightLeft, CheckCircle2 } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    salesOrderId: number;
    currentStatus: string;
    allowedTransitions: string[];
}>();

const emit = defineEmits(['close']);

interface StatusOption {
    value: string;
    label: string;
}

const STATUS_LABELS: Record<string, string> = {
    draft: 'Draft',
    confirmed: 'Dikonfirmasi',
    processing: 'Proses',
    shipped: 'Dikirim',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
};

const STATUS_COLORS: Record<string, string> = {
    draft: 'text-gray-600 dark:text-gray-300',
    confirmed: 'text-blue-600 dark:text-blue-400',
    processing: 'text-yellow-600 dark:text-yellow-400',
    shipped: 'text-purple-600 dark:text-purple-400',
    completed: 'text-green-600 dark:text-green-400',
    cancelled: 'text-red-600 dark:text-red-400',
};

const currentStatusLabel = computed(() => STATUS_LABELS[props.currentStatus] ?? props.currentStatus);
const currentStatusClass = computed(() => STATUS_COLORS[props.currentStatus] ?? '');

const statusOptions = computed<StatusOption[]>(() => props.allowedTransitions.map((v) => ({ value: v, label: STATUS_LABELS[v] ?? v })));

const form = useForm({
    status: '',
    resi_number: '',
});

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const submit = () => {
    form.patch(updateStatus.url({ sales_order: props.salesOrderId }), {
        preserveScroll: true,
        onSuccess: () => close(),
    });
};
</script>
