<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface MaterialType {
    id?: number;
    code: string;
    name: string;
    unit: string;
    description: string | null;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{
    materialType?: MaterialType;
}>();

const form = useForm({
    code: props.materialType?.code || '',
    name: props.materialType?.name || '',
    unit: props.materialType?.unit || '',
    description: props.materialType?.description || '',
    sort_order: props.materialType?.sort_order || 0,
    is_active: props.materialType?.is_active ?? true,
});

const submit = () => {
    if (props.materialType?.id) {
        form.put(`/material-types/${props.materialType.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/material-types', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.materialType?.id;
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Edit Jenis Bahan' : 'Tambah Jenis Bahan'" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? 'Edit Jenis Bahan' : 'Tambah Jenis Bahan' }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{ isEditing ? 'Perbarui informasi jenis bahan' : 'Tambahkan jenis bahan baru untuk kategorisasi' }}
                        </p>
                    </div>
                    <Link
                        href="/material-types"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <FormSection title="Informasi Dasar" description="Data identifikasi jenis bahan">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.code"
                                label="Kode Jenis Bahan"
                                type="text"
                                placeholder="Contoh: kain"
                                :required="true"
                                :error="form.errors.code"
                                hint="Kode unik untuk jenis bahan (huruf kecil, tanpa spasi)"
                            />

                            <FormField
                                v-model="form.name"
                                label="Nama"
                                type="text"
                                placeholder="Contoh: Kain"
                                :required="true"
                                :error="form.errors.name"
                            />

                            <FormField
                                v-model="form.unit"
                                label="Satuan"
                                type="text"
                                placeholder="Contoh: meter, kg, pcs"
                                :required="true"
                                :error="form.errors.unit"
                                hint="Satuan default untuk jenis bahan ini"
                            />

                            <div class="md:col-span-2">
                                <FormField
                                    v-model="form.description"
                                    label="Deskripsi"
                                    type="textarea"
                                    placeholder="Deskripsi singkat jenis bahan..."
                                    :error="form.errors.description"
                                    :rows="3"
                                />
                            </div>

                            <FormField
                                v-model="form.sort_order"
                                label="Urutan Tampilan"
                                type="number"
                                placeholder="0"
                                :error="form.errors.sort_order"
                                hint="Angka lebih kecil akan ditampilkan lebih dulu"
                            />

                            <FormField v-model="form.is_active" label="Aktif (dapat digunakan)" type="checkbox" />
                        </div>
                    </FormSection>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            href="/material-types"
                            class="rounded-lg border border-gray-300 px-6 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-indigo-600 px-6 py-2.5 font-medium text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                        >
                            {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
