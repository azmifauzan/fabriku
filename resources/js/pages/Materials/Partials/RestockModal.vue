<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import Modal from '@/components/Modal.vue';
import FormField from '@/components/FormField.vue';
import { watch, ref } from 'vue';
import { Camera, Upload, X } from 'lucide-vue-next';
import CameraCaptureModal from '@/Components/CameraCaptureModal.vue';

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
    image: null as File | null,
});

const showCameraModal = ref(false);
const previewImage = ref<string | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

watch(() => props.show, (val) => {
    if (val) {
        form.material_id = props.materialId;
        form.supplier_name = props.supplierName || '';
        form.unit_price = props.currentPrice || '';
        form.batch_number = ''; // Reset batch number on open
        form.quantity = '';
        form.image = null;
        previewImage.value = null;
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
    form.post(route('material-receipts.store'), {
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

                <div class="mt-4">
                    <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">Foto Penerimaan (Opsional)</label>
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
                    <span v-else>Simpan Stok</span>
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
