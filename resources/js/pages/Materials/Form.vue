<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface MaterialAttribute {
    id?: number;
    attribute_name: string;
    attribute_value: string;
}

interface MaterialType {
    code: string;
    name: string;
}

interface Material {
    id?: number;
    code: string;
    name: string;
    type: string;
    unit: string;
    standard_price: string;
    reorder_point: string;
    is_active: boolean;
    current_stock?: string;
    attributes?: MaterialAttribute[];
}

const props = defineProps<{
    material?: Material;
    materialTypes: MaterialType[];
}>();

const form = useForm({
    code: props.material?.code || '',
    name: props.material?.name || '',
    type: props.material?.type || props.materialTypes[0]?.code || 'kain',
    quantity: props.material?.current_stock || '0',
    unit: props.material?.unit || 'meter',
    standard_price: props.material?.standard_price || '0',
    reorder_point: props.material?.reorder_point || '0',
    is_active: props.material?.is_active ?? true,
    attributes:
        props.material?.attributes
            ?.filter((attr): attr is MaterialAttribute => attr != null)
            .map((attr) => ({
                name: attr.attribute_name || '',
                value: attr.attribute_value || '',
            })) || [],
});

const addAttribute = () => {
    form.attributes.push({ name: '', value: '' });
};

const removeAttribute = (index: number) => {
    form.attributes.splice(index, 1);
};

const submit = () => {
    if (props.material?.id) {
        form.put(`/materials/${props.material.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/materials', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.material?.id;
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Edit Bahan Baku' : 'Tambah Bahan Baku'" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? 'Edit Bahan Baku' : 'Tambah Bahan Baku Baru' }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{ isEditing ? 'Perbarui informasi bahan baku' : 'Tambahkan bahan baku baru ke inventory' }}
                        </p>
                    </div>
                    <Link
                        href="/materials"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <FormSection title="Informasi Dasar" description="Data identifikasi bahan baku">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.code"
                                label="Kode Bahan"
                                type="text"
                                placeholder="Contoh: KA-001"
                                :required="true"
                                :error="form.errors.code"
                                hint="Kode unik untuk identifikasi bahan baku"
                            />

                            <FormField
                                v-model="form.name"
                                label="Nama Bahan"
                                type="text"
                                placeholder="Contoh: Kain Katun Rayon Premium"
                                :required="true"
                                :error="form.errors.name"
                            />

                            <FormField
                                v-model="form.type"
                                label="Jenis Bahan"
                                type="select"
                                :required="true"
                                :error="form.errors.type"
                                :options="
                                    materialTypes.map((type) => ({
                                        value: type.code,
                                        label: type.name,
                                    }))
                                "
                            />

                            <FormField
                                v-model="form.unit"
                                label="Satuan"
                                type="select"
                                :required="true"
                                :error="form.errors.unit"
                                :options="[
                                    { value: 'meter', label: 'Meter' },
                                    { value: 'yard', label: 'Yard' },
                                    { value: 'kg', label: 'Kilogram (kg)' },
                                    { value: 'gram', label: 'Gram' },
                                    { value: 'roll', label: 'Roll' },
                                    { value: 'pcs', label: 'Pieces (pcs)' },
                                    { value: 'lusin', label: 'Lusin' },
                                    { value: 'pack', label: 'Pack' },
                                ]"
                            />
                        </div>
                    </FormSection>

                    <!-- Stock and Pricing -->
                    <FormSection title="Stok dan Harga" description="Informasi stok dan pricing">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.quantity"
                                label="Jumlah Stok"
                                type="number"
                                placeholder="0"
                                :error="form.errors.quantity"
                                hint="Stok bahan baku saat ini"
                            />

                            <FormField
                                v-model="form.standard_price"
                                label="Harga (Rp)"
                                type="number"
                                placeholder="0"
                                :error="form.errors.standard_price"
                                hint="Harga per satuan (opsional)"
                            />

                            <FormField
                                v-model="form.reorder_point"
                                label="Minimum Stok"
                                type="number"
                                placeholder="0"
                                :error="form.errors.reorder_point"
                                hint="Peringatan stok rendah (opsional)"
                            />

                            <div class="space-y-2">
                                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    Bahan baku aktif (dapat digunakan untuk produksi)
                                </div>
                                <FormField v-model="form.is_active" label="Aktif" type="checkbox" />
                            </div>
                        </div>
                    </FormSection>

                    <!-- Dynamic Attributes -->
                    <FormSection title="Atribut Tambahan" description="Informasi dinamis seperti corak, gramasi, lebar, dll">
                        <div class="space-y-4">
                            <div class="flex justify-end">
                                <button
                                    type="button"
                                    @click="addAttribute"
                                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-600 transition-all hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 dark:hover:bg-indigo-900/50"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Atribut
                                </button>
                            </div>

                            <div v-if="form.attributes.length > 0" class="space-y-3">
                                <div
                                    v-for="(attr, index) in form.attributes.filter((a) => a)"
                                    :key="index"
                                    class="flex items-start gap-3 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50"
                                >
                                    <div class="flex-1">
                                        <input
                                            v-model="attr.name"
                                            type="text"
                                            placeholder="Nama atribut (misal: Corak)"
                                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>
                                    <div class="flex-1">
                                        <input
                                            v-model="attr.value"
                                            type="text"
                                            placeholder="Nilai atribut (misal: Polos)"
                                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeAttribute(index)"
                                        class="rounded-lg p-2.5 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                        title="Hapus atribut"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div v-else class="rounded-lg bg-gray-50 py-8 text-center text-sm text-gray-500 dark:bg-gray-700/50 dark:text-gray-400">
                                Belum ada atribut. Klik "Tambah Atribut" untuk menambahkan.
                            </div>
                        </div>
                    </FormSection>

                    <!-- Submit Button -->
                    <div class="flex flex-col items-stretch justify-end gap-3 pt-4 sm:flex-row sm:items-center">
                        <Link
                            href="/materials"
                            class="order-2 rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-center text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 sm:order-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="order-1 inline-flex items-center justify-center gap-2 rounded-lg border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 sm:order-2"
                        >
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            {{ form.processing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Tambah Bahan Baku' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
