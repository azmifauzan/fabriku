<script setup lang="ts">
import FormField from '@/components/FormField.vue';
import FormSection from '@/components/FormSection.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { CheckSquare, Info, Shield } from 'lucide-vue-next';
import { computed } from 'vue';

interface Permission {
    id: number;
    name: string;
    slug: string;
    module: string;
}

interface Role {
    id: number;
    name: string;
    description: string | null;
    permissions?: Permission[];
}

const props = defineProps<{
    role?: Role;
    permissions: Record<string, Permission[]>;
}>();

const isEditing = !!props.role?.id;

const form = useForm({
    name: props.role?.name || '',
    description: props.role?.description || '',
    permission_ids: props.role?.permissions?.map((p) => p.id) || ([] as number[]),
});

const submit = () => {
    if (props.role?.id) {
        form.put(`/roles/${props.role.id}`, { preserveScroll: true });
    } else {
        form.post('/roles', { preserveScroll: true });
    }
};

const moduleLabels: Record<string, string> = {
    material: 'Bahan Baku',
    pattern: 'Pattern / Resep',
    preparation: 'Preparation',
    production: 'Produksi',
    inventory: 'Inventory',
    sales: 'Penjualan',
    purchase: 'Pembelian',
    simple_production: 'Catatan Produksi',
    service: 'Layanan',
    report: 'Laporan',
    settings: 'Pengaturan',
    audit: 'Audit Log',
    role: 'Role & Izin',
    user: 'Pengguna',
};

const getModuleLabel = (module: string) => moduleLabels[module] || module;

const selectedCount = computed(() => form.permission_ids.length);
const totalPermissions = computed(() => Object.values(props.permissions).flat().length);

const toggleModule = (modulePermissions: Permission[]) => {
    const ids = modulePermissions.map((p) => p.id);
    const allSelected = ids.every((id) => form.permission_ids.includes(id));
    if (allSelected) {
        form.permission_ids = form.permission_ids.filter((id) => !ids.includes(id));
    } else {
        const newIds = ids.filter((id) => !form.permission_ids.includes(id));
        form.permission_ids = [...form.permission_ids, ...newIds];
    }
};

const isModuleFullySelected = (modulePermissions: Permission[]) => {
    return modulePermissions.every((p) => form.permission_ids.includes(p.id));
};

const isModulePartiallySelected = (modulePermissions: Permission[]) => {
    return (
        modulePermissions.some((p) => form.permission_ids.includes(p.id)) &&
        !isModuleFullySelected(modulePermissions)
    );
};
</script>

<template>
    <AppLayout>
        <Head :title="isEditing ? 'Edit Role' : 'Tambah Role'" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ isEditing ? 'Edit Role' : 'Tambah Role Baru' }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{
                                isEditing
                                    ? 'Perbarui nama, deskripsi, dan izin role'
                                    : 'Buat role khusus dengan izin yang Anda tentukan'
                            }}
                        </p>
                    </div>
                    <Link
                        href="/roles"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <FormSection title="Informasi Dasar" description="Nama dan deskripsi role">
                        <div class="grid grid-cols-1 gap-6">
                            <FormField
                                v-model="form.name"
                                label="Nama Role"
                                type="text"
                                placeholder="Contoh: Kasir Gudang"
                                :required="true"
                                :error="form.errors.name"
                                hint="Nama yang mudah dikenali untuk role ini"
                            />
                            <FormField
                                v-model="form.description"
                                label="Deskripsi"
                                type="textarea"
                                placeholder="Deskripsi singkat kegunaan role ini"
                                :error="form.errors.description"
                            />
                        </div>
                    </FormSection>

                    <!-- Permissions -->
                    <FormSection title="Izin Akses" description="Pilih menu dan aksi yang bisa diakses oleh role ini">
                        <!-- Permission count summary -->
                        <div class="mb-4 flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3 dark:border-indigo-800 dark:bg-indigo-900/20">
                            <Shield :size="16" class="flex-shrink-0 text-indigo-600 dark:text-indigo-400" />
                            <span class="text-sm text-indigo-700 dark:text-indigo-300">
                                <span class="font-semibold">{{ selectedCount }}</span> dari
                                <span class="font-semibold">{{ totalPermissions }}</span> izin dipilih
                            </span>
                        </div>

                        <!-- Error for permission_ids -->
                        <p v-if="form.errors.permission_ids" class="mb-4 text-sm text-red-600 dark:text-red-400">
                            {{ form.errors.permission_ids }}
                        </p>

                        <div v-if="!Object.keys(permissions).length" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                            <Info :size="24" class="mx-auto mb-2" />
                            Tidak ada izin yang tersedia.
                        </div>

                        <div class="space-y-6">
                            <div v-for="(modulePermissions, moduleName) in permissions" :key="moduleName">
                                <!-- Module header with toggle all -->
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                        {{ getModuleLabel(String(moduleName)) }}
                                    </h4>
                                    <button
                                        type="button"
                                        @click="toggleModule(modulePermissions)"
                                        class="flex items-center gap-1 text-xs font-medium transition-colors"
                                        :class="
                                            isModuleFullySelected(modulePermissions)
                                                ? 'text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-200'
                                                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
                                        "
                                    >
                                        <CheckSquare :size="12" />
                                        {{ isModuleFullySelected(modulePermissions) ? 'Hapus Semua' : 'Pilih Semua' }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <label
                                        v-for="permission in modulePermissions"
                                        :key="permission.id"
                                        class="flex cursor-pointer items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors"
                                        :class="
                                            form.permission_ids.includes(permission.id)
                                                ? 'border-indigo-300 bg-indigo-50 dark:border-indigo-700 dark:bg-indigo-900/20'
                                                : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-gray-600 dark:hover:bg-gray-700/50'
                                        "
                                    >
                                        <input
                                            v-model="form.permission_ids"
                                            type="checkbox"
                                            :value="permission.id"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600"
                                        />
                                        <span
                                            class="font-medium"
                                            :class="
                                                form.permission_ids.includes(permission.id)
                                                    ? 'text-indigo-800 dark:text-indigo-200'
                                                    : 'text-gray-700 dark:text-gray-200'
                                            "
                                        >
                                            {{ permission.name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </FormSection>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                        <Link
                            href="/roles"
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
