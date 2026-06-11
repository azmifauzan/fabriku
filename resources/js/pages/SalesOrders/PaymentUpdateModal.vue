<template>
    <Modal :show="show" @close="close" max-width="md">
        <div class="px-6 py-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tambah Pembayaran</h3>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div class="flex justify-between rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pesanan</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">Rp {{ Number(totalAmount).toLocaleString('id-ID') }}</dd>
                    </div>
                    <div class="text-right">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Sisa Pembayaran</dt>
                        <dd class="mt-1 text-sm font-semibold text-red-600 dark:text-red-400">Rp {{ remainingAmount.toLocaleString('id-ID') }}</dd>
                    </div>
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Jumlah Dibayar <span class="text-red-600">*</span>
                    </label>
                    <input
                        id="amount"
                        v-model.number="form.amount"
                        type="number"
                        min="0.01"
                        step="0.01"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.amount }"
                    />
                    <p v-if="form.errors.amount" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.amount }}</p>
                </div>

                <div>
                    <label for="method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Metode Pembayaran <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="method"
                        v-model="form.method"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.method }"
                    >
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="credit_card">Kartu Kredit</option>
                        <option value="qris">QRIS</option>
                        <option value="cod">COD</option>
                    </select>
                    <p v-if="form.errors.method" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.method }}</p>
                </div>

                <div>
                    <label for="paid_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tanggal Bayar <span class="text-red-600">*</span>
                    </label>
                    <input
                        id="paid_at"
                        v-model="form.paid_at"
                        type="date"
                        required
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        :class="{ 'border-red-500': form.errors.paid_at }"
                    />
                    <p v-if="form.errors.paid_at" class="mt-1 text-xs text-red-600 dark:text-red-400">{{ form.errors.paid_at }}</p>
                </div>

                <div>
                    <label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                    <textarea
                        id="note"
                        v-model="form.note"
                        rows="2"
                        class="mt-1 w-full rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    ></textarea>
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

const remainingAmount = computed(() => Math.max(0, props.totalAmount - props.paidAmount));

const getTodayDate = () => {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
};

const form = useForm({
    amount: remainingAmount.value || 0,
    method: 'cash',
    paid_at: getTodayDate(),
    note: '',
});

const close = () => {
    form.clearErrors();
    form.amount = remainingAmount.value || 0;
    form.method = 'cash';
    form.paid_at = getTodayDate();
    form.note = '';
    emit('close');
};

const submit = () => {
    form.patch(updatePayment.url({ sales_order: props.salesOrderId }), {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
};
</script>
