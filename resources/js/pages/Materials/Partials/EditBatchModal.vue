<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import FormField from '@/components/FormField.vue';
import { watch, ref } from 'vue';
import { Camera, Upload, X } from 'lucide-vue-next';
import CameraCaptureModal from '@/components/CameraCaptureModal.vue';
import { update } from '@/actions/App/Http/Controllers/MaterialReceiptController';

interface Batch {
    id: number;
    receipt_number: string;
    batch_number: string | null;
    supplier_name: string;
    quantity: string;
    remaining_quantity?: string;
    price_per_unit: string;
    receipt_date: string;
    notes?: string | null;
    image_url?: string | null;
}

const props = defineProps<{
    show: boolean;
    batch: Batch | null;
    unit: string;
}>();

const emit = defineEmits(['close']);

const form = useForm({
    supplier_name: '',
    quantity: '',
    price_per_unit: '',
    receipt_date: '',
    batch_number: '',
    notes: '',
    image: null as File | null,
});

const showCameraModal = ref(false);
const previewImage = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

// Calculate used quantity (original - remaining)
const usedQuantity = () => {
    if (!props.batch) return 0;
    const original = parseFloat(props.batch.quantity) || 0;
    const remaining = parseFloat(props.batch.remaining_quantity || props.batch.quantity) || 0;
    return original - remaining;
};

// Minimum quantity is the used quantity
const minQuantity = () => usedQuantity();

watch(() => props.show, (val) => {
    if (val && props.batch) {
        form.supplier_name = props.batch.supplier_name || '';
        form.quantity = props.batch.quantity || '';
        form.price_per_unit = props.batch.price_per_unit || '';
        form.receipt_date = props.batch.receipt_date || '';
        form.batch_number = props.batch.batch_number || '';
        form.notes = props.batch.notes || '';
        form.image = null;
        previewImage.value = props.batch.image_url || null;
        if (fileInput.value) fileInput.value.value = '';
    }
});

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        processFile(target.files[0]);
    }
};

const handleCameraCapture = (file: File) => {
    processFile(file);
};

const processFile = (file: File) => {
    form.image = file;
    
    // Create preview
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImage.value = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const clearImage = () => {
    form.image = null;
    previewImage.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    if (!props.batch) return;
    
    form.post(update(props.batch.id), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const close = () => {
    emit('close');
    form.reset();
    clearImage();
};

const formatNumber = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(typeof value === 'string' ? parseFloat(value) : value);
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="lg">
        <div class="max-h-[85vh] overflow-y-auto p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">
                Edit Data Batch
            </h2>
            <p v-if="batch" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ batch.receipt_number }} {{ batch.batch_number ? `(${batch.batch_number})` : '' }}
            </p>

            <div v-if="batch" class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-700/50">
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Qty Awal:</span>
                        <span class="ml-2 font-semibold text-gray-900 dark:text-white">{{ formatNumber(batch.quantity) }} {{ unit }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Terpakai:</span>
                        <span class="ml-2 font-semibold text-orange-600 dark:text-orange-400">{{ formatNumber(usedQuantity()) }} {{ unit }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400">Sisa:</span>
                        <span class="ml-2 font-semibold" :class="parseFloat(batch.remaining_quantity || '0') > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500'">
                            {{ formatNumber(batch.remaining_quantity || batch.quantity) }} {{ unit }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        v-model="form.quantity"
                        label="Jumlah Qty"
                        type="number"
                        :placeholder="`Min: ${formatNumber(minQuantity())}`"
                        :error="form.errors.quantity"
                        :hint="usedQuantity() > 0 ? `Minimal ${formatNumber(minQuantity())} ${unit} (sudah terpakai)` : undefined"
                    />

                    <FormField
                        v-model="form.supplier_name"
                        label="Supplier"
                        placeholder="Nama Supplier"
                        :error="form.errors.supplier_name"
                    />
                </div>

                <FormField
                    v-model="form.batch_number"
                    label="Nomor Batch (Opsional)"
                    placeholder="Contoh: BATCH-001"
                    :error="form.errors.batch_number"
                />

                <div class="grid grid-cols-2 gap-4">
                    <FormField
                        v-model="form.price_per_unit"
                        label="Harga Satuan"
                        type="number"
                        placeholder="0"
                        :error="form.errors.price_per_unit"
                    />

                    <FormField
                        v-model="form.receipt_date"
                        label="Tanggal Penerimaan"
                        type="date"
                        :error="form.errors.receipt_date"
                    />
                </div>

                <FormField
                    v-model="form.notes"
                    label="Catatan"
                    placeholder="Catatan tambahan..."
                    :error="form.errors.notes"
                />

                <div class="mt-4">
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">Foto Batch (Opsional)</label>
                    <div class="flex items-center gap-4">
                        <div v-if="previewImage" class="relative h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg border border-gray-300 bg-gray-100 dark:border-gray-600 dark:bg-gray-700">
                            <img :src="previewImage" alt="Preview" class="h-full w-full object-cover" />
                            <button 
                                type="button" 
                                @click="clearImage"
                                class="absolute top-1 right-1 rounded-full bg-red-600 p-1 text-white shadow-sm hover:bg-red-700"
                            >
                                <X class="h-3 w-3" />
                            </button>
                        </div>
                        <div class="flex-1 space-y-2">
                             <div class="flex flex-wrap gap-2">
                                <label class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                                    <Upload class="h-4 w-4" />
                                    <span>Upload File</span>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/*"
                                        @change="handleFileChange"
                                        class="hidden"
                                    />
                                </label>

                                <button 
                                    type="button"
                                    @click="showCameraModal = true"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-600 transition-all hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                                >
                                    <Camera class="h-4 w-4" />
                                    <span>Ambil Foto</span>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Bukti fisik penerimaan barang/surat jalan.
                            </p>
                            <p v-if="form.errors.image" class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.image }}
                            </p>
                        </div>
                    </div>
                </div>
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
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </Modal>

    <CameraCaptureModal
        :show="showCameraModal"
        @close="showCameraModal = false"
        @capture="handleCameraCapture"
    />
</template>
