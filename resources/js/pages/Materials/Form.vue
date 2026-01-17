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
    id: number;
    code: string;
    name: string;
}

interface Material {
    id?: number;
    code: string;
    name: string;
    material_type_id: number;
    unit: string;
    price_per_unit: string;
    stock_quantity?: string;
    min_stock?: string;
    supplier_name?: string;
    description?: string;
    attributes?: MaterialAttribute[];
}

const props = defineProps<{
    material?: Material;
    materialTypes: MaterialType[];
}>();

const form = useForm({
    code: props.material?.code || '',
    name: props.material?.name || '',
    material_type_id: props.material?.material_type_id || (props.materialTypes[0]?.id || ''),
    stock_quantity: props.material?.stock_quantity || '0',
    unit: props.material?.unit || 'meter',
    price_per_unit: props.material?.price_per_unit || '0',
    min_stock: props.material?.min_stock || '0',
    supplier_name: props.material?.supplier_name || '',
    description: props.material?.description || '',
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
                                v-model="form.material_type_id"
                                label="Jenis Bahan"
                                type="select"
                                :required="true"
                                :error="form.errors.material_type_id"
                                :options="
                                    props.materialTypes.map((type) => ({
                                        value: type.id,
                                        label: type.name,
                                    }))
                                "
                            />

                            <FormField
                                v-model="form.unit"
                                label="Satuan"
                                type="text"
                                placeholder="meter, kg, pcs, etc"
                                :required="true"
                                :error="form.errors.unit"
                            />
                        </div>
                    </FormSection>

                    <!-- Stock and Pricing -->
                    <FormSection title="Stok dan Harga" description="Informasi stok dan pricing">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.stock_quantity"
                                label="Jumlah Stok"
                                type="number"
                                placeholder="0"
                                :error="form.errors.stock_quantity"
                                hint="Stok bahan baku saat ini"
                            />

                            <FormField
                                v-model="form.price_per_unit"
                                label="Harga (Rp)"
                                type="number"
                                placeholder="0"
                                :error="form.errors.price_per_unit"
                                hint="Harga per satuan (opsional)"
                            />

                            <FormField
                                v-model="form.min_stock"
                                label="Minimum Stok"
                                type="number"
                                placeholder="0"
                                :error="form.errors.min_stock"
                                hint="Peringatan stok rendah (opsional)"
                            />

                            <FormField
                                v-model="form.supplier_name"
                                label="Nama Supplier"
                                type="text"
                                placeholder="Nama supplier (opsional)"
                                :error="form.errors.supplier_name"
                            />
                        </div>
                    </FormSection>

                    <!-- Description -->
                    <FormSection title="Deskripsi" description="Informasi tambahan tentang bahan baku">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300">Deskripsi</label>
                            <textarea
                                v-model="form.description"
                                placeholder="Deskripsi bahan baku (opsional)"
                                rows="4"
                                class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
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
