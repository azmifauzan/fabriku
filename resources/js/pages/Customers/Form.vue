<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Customer {
    id?: number;
    code: string;
    name: string;
    phone: string;
    email: string;
    address: string;
    city: string;
    province: string;
    is_active: boolean;
    notes: string;
}

const props = defineProps<{
    customer?: Customer;
}>();

const form = useForm({
    code: props.customer?.code || '',
    name: props.customer?.name || '',
    phone: props.customer?.phone || '',
    email: props.customer?.email || '',
    address: props.customer?.address || '',
    city: props.customer?.city || '',
    province: props.customer?.province || '',
    notes: props.customer?.notes || '',
    is_active: props.customer?.is_active ?? true,
});

const submit = () => {
    if (props.customer?.id) {
        form.put(`/customers/${props.customer.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/customers', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.customer?.id;
</script>

<template>
    <div>
        <Head :title="isEditing ? 'Edit Customer' : 'Tambah Customer'" />

        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                    {{ isEditing ? 'Edit Customer' : 'Tambah Customer Baru' }}
                </h1>
                <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                    {{ isEditing ? 'Perbarui informasi customer' : 'Tambahkan data customer untuk penjualan produk' }}
                </p>
            </div>
            <Link
                href="/customers"
                class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
            >
                ← Kembali
            </Link>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Basic Information -->
            <FormSection title="Informasi Dasar" description="Data identitas customer">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <FormField
                        v-model="form.code"
                        label="Kode Customer"
                        type="text"
                        placeholder="Contoh: CUST-001"
                        :required="true"
                        :error="form.errors.code"
                        hint="Kode unik untuk identifikasi customer"
                    />

                    <FormField
                        v-model="form.name"
                        label="Nama Customer"
                        type="text"
                        placeholder="Nama lengkap customer"
                        :required="true"
                        :error="form.errors.name"
                    />

                    <div class="flex items-center pt-8">
                        <FormField v-model="form.is_active" label="Customer aktif (dapat bertransaksi)" type="checkbox" />
                    </div>
                </div>
            </FormSection>

            <!-- Contact Information -->
            <FormSection title="Informasi Kontak" description="Detail kontak dan alamat">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <FormField v-model="form.phone" label="Telepon" type="text" placeholder="08123456789" :error="form.errors.phone" />

                    <FormField v-model="form.email" label="Email" type="email" placeholder="customer@example.com" :error="form.errors.email" />

                    <div class="md:col-span-2">
                        <FormField
                            v-model="form.address"
                            label="Alamat"
                            type="textarea"
                            placeholder="Alamat lengkap customer"
                            :rows="2"
                            :error="form.errors.address"
                        />
                    </div>

                    <FormField v-model="form.city" label="Kota" type="text" placeholder="Contoh: Jakarta" :error="form.errors.city" />

                    <FormField v-model="form.province" label="Provinsi" type="text" placeholder="Contoh: DKI Jakarta" :error="form.errors.province" />
                </div>
            </FormSection>

            <!-- Notes -->
            <FormSection title="Catatan" description="Informasi tambahan (opsional)">
                <FormField
                    v-model="form.notes"
                    label="Catatan"
                    type="textarea"
                    placeholder="Catatan atau informasi tambahan tentang customer"
                    :rows="3"
                    :error="form.errors.notes"
                />
            </FormSection>

            <!-- Submit Button -->
            <div class="flex flex-col items-stretch justify-end gap-3 pt-4 sm:flex-row sm:items-center">
                <Link
                    href="/customers"
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
                    {{ form.processing ? 'Menyimpan...' : isEditing ? 'Simpan Perubahan' : 'Tambah Customer' }}
                </button>
            </div>
        </form>
    </div>
</template>
