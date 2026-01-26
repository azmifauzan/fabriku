<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import FormField from '@/components/FormField.vue';
import { watch } from 'vue';

const props = defineProps<{
    show: boolean;
    materialId: number;
    supplierName?: string | null;
    currentPrice?: string | number;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    material_id: props.materialId,
    supplier_name: props.supplierName || '',
    quantity: '',
    unit_price: props.currentPrice || '',
    receipt_date: new Date().toISOString().split('T')[0],
    batch_number: '',
    notes: '',
});

watch(() => props.show, (val) => {
    if (val) {
        form.material_id = props.materialId;
        form.supplier_name = props.supplierName || '';
        form.unit_price = props.currentPrice || '';
        form.batch_number = ''; // Reset batch number on open
        form.quantity = '';
    }
});

const submit = () => {
    form.post(route('material-receipts.store'), {
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const close = () => {
    emit('close');
    form.reset();
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="lg">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                Restock Bahan
            </h2>

            <div class="mt-6 space-y-4">
                <FormField
                    v-model="form.supplier_name"
                    label="Supplier"
                    placeholder="Nama Supplier"
                    :error="form.errors.supplier_name"
                />

                <FormField
                    v-model="form.batch_number"
                    label="Nomor Batch (Opsional)"
                    placeholder="Contoh: BATCH-001"
                    :error="form.errors.batch_number"
                />

                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        v-model="form.quantity"
                        label="Jumlah"
                        type="number"
                        placeholder="0"
                        :error="form.errors.quantity"
                    />

                    <FormField
                        v-model="form.unit_price"
                        label="Harga Satuan"
                        type="number"
                        placeholder="0"
                        :error="form.errors.unit_price"
                    />
                </div>

                <FormField
                    v-model="form.receipt_date"
                    label="Tanggal Penerimaan"
                    type="date"
                    :error="form.errors.receipt_date"
                />

                <FormField
                    v-model="form.notes"
                    label="Catatan"
                    placeholder="Catatan tambahan..."
                    :error="form.errors.notes"
                />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button
                    type="button"
                    @click="close"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:focus:ring-offset-gray-800"
                >
                    <span v-if="form.processing">Menyimpan...</span>
                    <span v-else>Simpan Stok</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
