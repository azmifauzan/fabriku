<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { AlertTriangle, Info, Mail } from 'lucide-vue-next';
import { computed } from 'vue';

interface Role {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface Staff {
    id?: number;
    code: string;
    name: string;
    role_id: number | null;
    phone: string | null;
    email: string | null;
    is_active: boolean;
    role?: Role | null;
    user?: { id: number; email: string } | null;
}

const props = defineProps<{
    staff?: Staff;
    roles: Role[];
    maxStaff?: number;
    currentStaffCount?: number;
}>();

const form = useForm({
    code: props.staff?.code || '',
    name: props.staff?.name || '',
    role_id: props.staff?.role_id || props.staff?.role?.id || '',
    phone: props.staff?.phone || '',
    email: props.staff?.email || '',
    is_active: props.staff?.is_active ?? true,
});

const submit = () => {
    if (props.staff?.id) {
        form.put(`/staff/${props.staff.id}`, {
            preserveScroll: true,
        });
    } else {
        form.post('/staff', {
            preserveScroll: true,
        });
    }
};

const isEditing = !!props.staff?.id;

const roleOptions = computed(() =>
    props.roles.map((role) => ({
        value: role.id,
        label: role.name,
    })),
);

const selectedRoleDescription = computed(() => {
    const selectedRole = props.roles.find((r) => r.id === Number(form.role_id));
    return selectedRole?.description || null;
});

const isAtLimit = computed(() => {
    if (!props.maxStaff || !props.currentStaffCount) return false;
    return props.currentStaffCount >= props.maxStaff;
});
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Edit Staff' : 'Tambah Staff'" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? 'Edit Staff' : 'Tambah Staff Baru' }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{ isEditing ? 'Perbarui informasi staff' : 'Tambahkan staff / karyawan baru' }}
                        </p>
                    </div>
                    <Link
                        href="/staff"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <!-- Info Banner for new staff -->
                <div
                    v-if="!isEditing"
                    class="mb-6 flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20"
                >
                    <Info :size="20" class="mt-0.5 flex-shrink-0 text-blue-600 dark:text-blue-400" />
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                            Akun otomatis akan dibuat
                        </p>
                        <p class="mt-1 text-sm text-blue-700 dark:text-blue-400">
                            Saat menambah staff baru, sistem akan otomatis membuat akun login dan mengirim email dengan detail login (email &amp; password) ke staff tersebut.
                        </p>
                    </div>
                </div>

                <!-- Staff limit warning -->
                <div
                    v-if="!isEditing && maxStaff && currentStaffCount"
                    class="mb-6 flex items-start gap-3 rounded-xl border p-4"
                    :class="
                        isAtLimit
                            ? 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20'
                            : 'border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20'
                    "
                >
                    <AlertTriangle
                        :size="20"
                        class="mt-0.5 flex-shrink-0"
                        :class="isAtLimit ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400'"
                    />
                    <div>
                        <p
                            class="text-sm font-medium"
                            :class="isAtLimit ? 'text-red-800 dark:text-red-300' : 'text-amber-800 dark:text-amber-300'"
                        >
                            Kuota Staff: {{ currentStaffCount }} / {{ maxStaff }}
                        </p>
                        <p
                            v-if="isAtLimit"
                            class="mt-1 text-sm text-red-700 dark:text-red-400"
                        >
                            Batas maksimal staff tercapai. Hubungi admin platform untuk menambah kuota.
                        </p>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <FormSection title="Informasi Dasar" description="Data identifikasi staff">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField
                                v-model="form.code"
                                label="Kode Staff"
                                type="text"
                                placeholder="Contoh: STF001"
                                :required="true"
                                :error="form.errors.code"
                                hint="Kode unik untuk staff"
                            />

                            <FormField
                                v-model="form.name"
                                label="Nama Lengkap"
                                type="text"
                                placeholder="Nama lengkap staff"
                                :required="true"
                                :error="form.errors.name"
                            />

                            <div class="md:col-span-2">
                                <FormField
                                    v-model="form.role_id"
                                    label="Role"
                                    type="select"
                                    :required="true"
                                    :error="form.errors.role_id"
                                    :options="roleOptions"
                                    hint="Pilih role yang menentukan akses menu staff"
                                >
                                    <template #options>
                                        <option value="">-- Pilih Role --</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">
                                            {{ role.name }}
                                        </option>
                                    </template>
                                </FormField>
                                <p
                                    v-if="selectedRoleDescription"
                                    class="mt-2 flex items-start gap-1.5 text-sm text-indigo-600 dark:text-indigo-400"
                                >
                                    <Info :size="14" class="mt-0.5 flex-shrink-0" />
                                    {{ selectedRoleDescription }}
                                </p>
                            </div>
                        </div>
                    </FormSection>

                    <!-- Contact Information -->
                    <FormSection title="Informasi Kontak" description="Data kontak staff (email wajib untuk login)">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField v-model="form.phone" label="Nomor Telepon" type="tel" placeholder="08xxxxxxxxxx" :error="form.errors.phone" />

                            <div>
                                <FormField
                                    v-model="form.email"
                                    label="Email"
                                    type="email"
                                    placeholder="email@example.com"
                                    :required="true"
                                    :error="form.errors.email"
                                />
                                <p v-if="!isEditing" class="mt-2 flex items-start gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    <Mail :size="14" class="mt-0.5 flex-shrink-0" />
                                    Password login akan dikirim ke email ini
                                </p>
                            </div>
                        </div>
                    </FormSection>

                    <!-- Status -->
                    <FormSection title="Status" description="Status aktif staff">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <FormField v-model="form.is_active" label="Aktif (dapat digunakan)" type="checkbox" />
                        </div>
                    </FormSection>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            href="/staff"
                            class="rounded-lg border border-gray-300 px-6 py-2.5 font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || (!isEditing && isAtLimit)"
                            class="rounded-lg bg-indigo-600 px-6 py-2.5 font-medium text-white transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                        >
                            {{ form.processing ? 'Menyimpan...' : isEditing ? 'Update' : 'Simpan & Kirim Email' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
