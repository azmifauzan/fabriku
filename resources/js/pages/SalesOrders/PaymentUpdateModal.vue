<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Update Pembayaran</h3>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">Rp {{ Number(totalAmount).toLocaleString('id-ID') }}</dd>
                </div>

                <div>
                    <label for="paid_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jumlah Dibayar <span class="text-red-600">*</span>
                    </label>
                    <input
                        id="paid_amount"
                        v-model.number="form.paid_amount"
                        type="number"
                        min="0"
                        :max="totalAmount"
                        step="0.01"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.paid_amount }"
                    />
                    <p v-if="form.errors.paid_amount" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ form.errors.paid_amount }}</p>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Sisa Pembayaran</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">Rp {{ remainingAmount.toLocaleString('id-ID') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Pembayaran</dt>
                    <dd class="mt-1">
                        <span
                            class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                            :class="{
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': previewStatus === 'unpaid',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': previewStatus === 'partial',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': previewStatus === 'paid',
                            }"
                        >
                            {{ previewStatus === 'unpaid' ? 'Belum Dibayar' : previewStatus === 'partial' ? 'Dibayar Sebagian' : 'Lunas' }}
                        </span>
                    </dd>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        @click="close"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { updatePayment } from '@/actions/App/Http/Controllers/SalesOrderController';
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    salesOrderId: number;
    totalAmount: number;
    paidAmount: number;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    paid_amount: props.paidAmount,
});

const remainingAmount = computed(() => Math.max(0, props.totalAmount - (form.paid_amount || 0)));

const previewStatus = computed(() => {
    if (!form.paid_amount || form.paid_amount <= 0) {
        return 'unpaid';
    }

    return form.paid_amount >= props.totalAmount ? 'paid' : 'partial';
});

const close = () => {
    form.clearErrors();
    form.paid_amount = props.paidAmount;
    emit('close');
};

const submit = () => {
    form.patch(updatePayment.url({ sales_order: props.salesOrderId }), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>
