<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    order?: any;
    patterns: any[];
    materials: any[];
    staff: any[];
}>();

// Format date properly for date input field (YYYY-MM-DD)
const formatDateForInput = (date: string | null | undefined): string => {
    if (!date) {
        return new Date().toISOString().split('T')[0];
    }

    // If date is already in YYYY-MM-DD format, return as is
    if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date;
    }

    // Otherwise parse and format
    return new Date(date).toISOString().split('T')[0];
};

const form = useForm({
    pattern_id: props.order?.pattern_id || null,
    order_date: formatDateForInput(props.order?.order_date),
    prepared_by: props.order?.prepared_by || null,
    output_quantity: props.order?.output_quantity || 0,
    output_unit: props.order?.output_unit || 'pieces',
    materials_used: props.order?.materials_used || [],
    notes: props.order?.notes || '',
    status: props.order?.status || 'completed',
});

const addMaterial = () => {
    form.materials_used.push({
        material_id: null,
        batch_id: null,
        material_name: '',
        quantity: 0,
        unit: '',
    });
};

const removeMaterial = (index: number) => {
    form.materials_used.splice(index, 1);
};

const onMaterialSelect = (index: number) => {
    const selected = props.materials.find((m) => m.id === form.materials_used[index].material_id);
    if (selected) {
        form.materials_used[index].material_name = selected.name;
        form.materials_used[index].unit = selected.unit;
        form.materials_used[index].batch_id = null; // Reset batch when material changes
    }
};

const getBatchesForMaterial = (materialId: number) => {
    const material = props.materials.find(m => m.id === materialId);
    return material?.receipts || [];
};

const getSelectedMaterial = (materialId: number | null) => {
    if (!materialId) return null;
    return props.materials.find(m => m.id === materialId);
};

const getSelectedBatch = (materialId: number, batchId: number | null) => {
    if (!materialId || !batchId) return null;
    const batches = getBatchesForMaterial(materialId);
    return batches.find(b => b.id === batchId);
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatBatchLabel = (batch: any) => {
    return `${batch.batch_number || batch.receipt_number} - ${batch.supplier_name} - Sisa: ${batch.remaining_quantity} ${batch.unit}`;
};

const submit = () => {
    if (props.order) {
        form.put(`/preparation-orders/${props.order.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/preparation-orders', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.order?.id;
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Edit Preparation Order' : 'Tambah Preparation Order'" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? 'Edit Preparation Order' : 'Tambah Preparation Order Baru' }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{ isEditing ? 'Perbarui informasi preparation order' : 'Tambahkan preparation order baru ke sistem' }}
                        </p>
                    </div>
                    <Link
                        href="/preparation-orders"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <FormSection title="Informasi Dasar" description="Data identifikasi preparation order">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <FormField
                                    v-model="form.pattern_id"
                                    label="Pattern/Resep"
                                    type="select"
                                    :required="true"
                                    :error="form.errors.pattern_id"
                                    hint="Pilih pattern/resep sebagai acuan untuk preparation"
                                    :options="[
                                        { value: null, label: 'Pilih Pattern/Resep' },
                                        ...patterns.map((p) => ({ value: p.id, label: p.name })),
                                    ]"
                                />
                            </div>

                            <FormField v-model="form.order_date" label="Tanggal Order" type="date" :required="true" :error="form.errors.order_date" />

                            <FormField
                                v-model="form.prepared_by"
                                label="Penanggung Jawab"
                                type="select"
                                :error="form.errors.prepared_by"
                                hint="Staff yang melakukan persiapan"
                                :options="[
                                    { value: null, label: 'Pilih Staff' },
                                    ...staff.map((s) => ({
                                        value: s.id,
                                        label: s.position ? `${s.name} (${s.position})` : s.name,
                                    })),
                                ]"
                            />
                        </div>
                    </FormSection>

                    <!-- Materials Used -->
                    <FormSection title="Material yang Digunakan" description="Daftar material dan jumlah yang digunakan dalam prep">
                        <div class="space-y-4">
                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    @click="addMaterial"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-600 transition-all hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Material
                                </button>
                            </div>

                            <div v-if="form.materials_used.length > 0" class="space-y-3">
                                <div
                                    v-for="(material, idx) in form.materials_used"
                                    :key="idx"
                                    class="items-start gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800"
                                >
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-12 items-start">
                                        <!-- Material Selection (Full Width) -->
                                        <div class="md:col-span-12">
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Material
                                            </label>
                                            <select
                                                v-model="material.material_id"
                                                @change="onMaterialSelect(Number(idx))"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                required
                                            >
                                                <option :value="null">Pilih Material</option>
                                                <option v-for="mat in materials" :key="mat.id" :value="mat.id">
                                                    {{ mat.name }} (Total Stok: {{ mat.stock_quantity }} {{ mat.unit }})
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Batch Selection (Full Width) -->
                                        <div class="md:col-span-12">
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Batch / Supplier
                                            </label>
                                            <select
                                                v-model="material.batch_id"
                                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                :disabled="!material.material_id"
                                                required
                                            >
                                                <option :value="null" disabled>Pilih Batch</option>
                                                <option v-for="batch in getBatchesForMaterial(material.material_id)" :key="batch.id" :value="batch.id">
                                                    {{ formatBatchLabel(batch) }}
                                                </option>
                                            </select>
                                            
                                            <!-- Detailed Batch Information Panel -->
                                            <div v-if="getSelectedBatch(material.material_id, material.batch_id)" class="mt-3 rounded-lg border border-gray-100 bg-gray-50 p-3 text-sm dark:border-gray-700 dark:bg-gray-700/50">
                                                    <div class="col-span-2 sm:col-span-4 flex gap-4">
                                                        <div v-if="getSelectedBatch(material.material_id, material.batch_id)?.image_url" class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 dark:border-gray-600">
                                                            <img :src="getSelectedBatch(material.material_id, material.batch_id)?.image_url" alt="Material" class="h-full w-full object-cover" />
                                                        </div>
                                                        <div class="grid flex-1 grid-cols-2 gap-2 sm:grid-cols-4">
                                                            <div>
                                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Supplier</span>
                                                                <span class="font-medium text-gray-900 dark:text-gray-200">{{ getSelectedBatch(material.material_id, material.batch_id).supplier_name }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Harga Satuan</span>
                                                                <span class="font-medium text-gray-900 dark:text-gray-200">{{ formatCurrency(getSelectedBatch(material.material_id, material.batch_id).price_per_unit) }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Batch Info</span>
                                                                <span class="font-medium text-gray-900 dark:text-gray-200">{{ getSelectedBatch(material.material_id, material.batch_id).batch_number || '-' }}</span>
                                                            </div>
                                                            <div>
                                                                <span class="block text-xs text-gray-500 dark:text-gray-400">Sisa Stok Batch</span>
                                                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                                                    {{ getSelectedBatch(material.material_id, material.batch_id).remaining_quantity }} {{ material.unit }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>

                                        <!-- Quantity Input -->
                                        <div class="md:col-span-5">
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Jumlah Digunakan
                                            </label>
                                            <div class="relative">
                                                <input
                                                    v-model.number="material.quantity"
                                                    type="number"
                                                    step="0.01"
                                                    placeholder="0"
                                                    required
                                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                                />
                                            </div>
                                        </div>

                                        <!-- Unit Input -->
                                        <div class="md:col-span-4">
                                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                Satuan
                                            </label>
                                            <input
                                                v-model="material.unit"
                                                type="text"
                                                readonly
                                                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                                            />
                                        </div>

                                        <!-- Remove Button -->
                                        <div class="md:col-span-3 flex justify-end items-end h-full pt-6">
                                            <button
                                                type="button"
                                                @click="removeMaterial(Number(idx))"
                                                class="flex items-center gap-2 rounded-lg border border-transparent px-4 py-2.5 text-sm font-medium text-red-600 transition-all hover:bg-red-50 hover:text-red-700 active:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-else class="rounded-lg bg-gray-50 py-8 text-center text-sm text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">
                                Belum ada material. Klik "Tambah Material" untuk menambahkan.
                            </div>

                            <div v-if="form.errors.materials_used" class="text-sm font-medium text-red-600 dark:text-red-400">
                                {{ form.errors.materials_used }}
                            </div>
                        </div>
                    </FormSection>

                    <!-- Output Information -->
                    <FormSection title="Output Hasil Preparation" description="Informasi hasil output dari proses preparation">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model.number="form.output_quantity"
                                label="Jumlah Output"
                                type="number"
                                step="0.01"
                                placeholder="0"
                                :required="true"
                                :error="form.errors.output_quantity"
                                hint="Jumlah hasil output dari preparation"
                            />

                            <FormField
                                v-model="form.output_unit"
                                label="Satuan Output"
                                type="text"
                                placeholder="pieces, kg, batch, dll"
                                :required="true"
                                :error="form.errors.output_unit"
                                hint="Satuan hasil output (pieces, kg, batch, dll)"
                            />
                        </div>
                    </FormSection>

                    <!-- Status and Notes -->
                    <FormSection title="Status dan Catatan" description="Status order dan catatan tambahan">
                        <div class="space-y-6">
                            <FormField
                                v-model="form.status"
                                label="Status"
                                type="select"
                                :required="true"
                                :error="form.errors.status"
                                hint="Draft/In Progress: stok belum dipotong. Completed: stok otomatis dipotong!"
                                :options="[
                                    { value: 'draft', label: 'Draft' },
                                    { value: 'in_progress', label: 'In Progress' },
                                    { value: 'completed', label: 'Completed' },
                                    { value: 'cancelled', label: 'Cancelled' },
                                ]"
                            />

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Catatan </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="4"
                                    placeholder="Catatan tambahan..."
                                    class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1.5 text-sm text-red-600 dark:text-red-400">
                                    {{ form.errors.notes }}
                                </p>
                            </div>
                        </div>
                    </FormSection>

                    <!-- Submit Button -->
                    <div class="flex flex-col items-stretch justify-end gap-3 pt-4 sm:flex-row sm:items-center">
                        <Link
                            href="/preparation-orders"
                            class="inline-flex w-full items-center justify-center rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 sm:w-auto dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        >
                            <svg
                                v-if="form.processing"
                                class="mr-2 -ml-1 h-4 w-4 animate-spin text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
