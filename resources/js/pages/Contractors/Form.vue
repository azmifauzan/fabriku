<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import { useBusinessContext } from '@/composables/useBusinessContext';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Contractor {
    id?: number;
    code?: string;
    name: string;
    type: string;
    specialty: string;
    contact_person: string;
    email: string;
    phone: string;
    address: string;
    is_active: boolean;
    notes: string;
}

const props = defineProps<{
    contractor?: Contractor;
}>();

const { term, termLower } = useBusinessContext();

const contractorLabel = computed(() => term('contractor', 'Kontraktor'));
const contractorLabelLower = computed(() => termLower('contractor', 'kontraktor'));

const form = useForm({
    code: props.contractor?.code || '',
    name: props.contractor?.name || '',
    type: props.contractor?.type || 'individual',
    specialty: props.contractor?.specialty || '',
    contact_person: props.contractor?.contact_person || '',
    email: props.contractor?.email || '',
    phone: props.contractor?.phone || '',
    address: props.contractor?.address || '',
    is_active: props.contractor?.is_active ?? true,
    notes: props.contractor?.notes || '',
});

const submit = () => {
    if (props.contractor?.id) {
        form.put(`/contractors/${props.contractor.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/contractors', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.contractor?.id;
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? `Edit ${contractorLabel}` : `Tambah ${contractorLabel}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? `Edit ${contractorLabel}` : `Tambah ${contractorLabel} Baru` }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{
                                isEditing
                                    ? `Perbarui informasi ${contractorLabelLower}`
                                    : `Tambahkan ${contractorLabelLower} untuk produksi eksternal`
                            }}
                        </p>
                    </div>
                    <Link
                        href="/contractors"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <FormSection title="Informasi Dasar" description="Data identitas kontraktor">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.code"
                                label="Kode"
                                type="text"
                                placeholder="Otomatis jika kosong"
                                :error="form.errors.code"
                                hint="Biarkan kosong untuk generate otomatis"
                            />

                            <FormField
                                v-model="form.name"
                                :label="`Nama ${contractorLabel}`"
                                type="text"
                                placeholder="Nama lengkap kontraktor"
                                :required="true"
                                :error="form.errors.name"
                            />

                            <FormField
                                v-model="form.type"
                                label="Tipe"
                                type="select"
                                :required="true"
                                :error="form.errors.type"
                                :options="[
                                    { value: 'individual', label: 'Individual' },
                                    { value: 'company', label: 'Perusahaan' },
                                ]"
                            />

                            <FormField
                                v-model="form.specialty"
                                label="Spesialisasi"
                                type="text"
                                placeholder="Contoh: Penjahit mukena dan gamis"
                                :error="form.errors.specialty"
                                hint="Deskripsi spesialisasi kontraktor"
                            />

                            <div class="md:col-span-2">
                                <label class="flex items-center space-x-3">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700"
                                    />
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300"> Kontraktor Aktif </span>
                                </label>
                                <p class="mt-1 ml-7 text-xs text-gray-500 dark:text-gray-400">
                                    Hanya kontraktor aktif yang dapat dipilih untuk production order
                                </p>
                            </div>
                        </div>
                    </FormSection>

                    <!-- Contact Information -->
                    <FormSection title="Informasi Kontak" description="Detail kontak dan alamat">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.contact_person"
                                label="Nama Kontak"
                                type="text"
                                placeholder="Nama person in charge"
                                :error="form.errors.contact_person"
                            />

                            <FormField v-model="form.phone" label="Telepon" type="tel" placeholder="08xxxxxxxxxx" :error="form.errors.phone" />

                            <FormField v-model="form.email" label="Email" type="email" placeholder="email@example.com" :error="form.errors.email" />

                            <FormField
                                v-model="form.address"
                                label="Alamat"
                                type="textarea"
                                placeholder="Alamat lengkap"
                                :rows="3"
                                :error="form.errors.address"
                            />
                        </div>
                    </FormSection>

                    <!-- Notes -->
                    <FormSection title="Catatan" description="Informasi tambahan (opsional)">
                        <FormField
                            v-model="form.notes"
                            label="Catatan"
                            type="textarea"
                            placeholder="Catatan tambahan tentang kontraktor..."
                            :rows="3"
                            :error="form.errors.notes"
                        />
                    </FormSection>

                    <!-- Submit Button -->
                    <div class="flex flex-col items-stretch justify-end gap-3 pt-4 sm:flex-row sm:items-center">
                        <Link
                            href="/contractors"
                            class="order-2 rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-center text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 sm:order-1 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="order-1 rounded-lg border border-transparent bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-50 sm:order-2"
                        >
                            {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
