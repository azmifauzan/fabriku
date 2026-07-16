<script setup lang="ts">
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Lock, Plus, Shield, Trash2 } from 'lucide-vue-next';

interface RoleRow {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_system_role: boolean;
    permissions_count: number;
    staff_members_count?: number;
}

defineProps<{
    systemRoles: RoleRow[];
    customRoles: RoleRow[];
}>();

const { confirm } = useSweetAlert();

const deleteRole = async (role: RoleRow) => {
    const result = await confirm(
        'Hapus Role',
        `Apakah Anda yakin ingin menghapus role "${role.name}"?`,
        'Ya, Hapus',
        'warning',
        '#dc2626',
    );

    if (result.isConfirmed) {
        router.delete(`/roles/${role.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Role & Izin" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-5xl">
                <!-- Page Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            Role &amp; Izin
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            Kelola role custom dan lihat izin tiap role yang tersedia
                        </p>
                    </div>
                    <Link
                        href="/roles/create"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                    >
                        <Plus :size="16" />
                        Tambah Role
                    </Link>
                </div>

                <!-- Custom Roles Section -->
                <div class="mb-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Role Custom Tenant</h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Dibuat khusus untuk bisnis Anda — dapat diedit dan dihapus
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300"
                        >
                            {{ customRoles.length }} role
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table v-if="customRoles.length > 0" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Nama Role
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Deskripsi
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Jumlah Izin
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Jumlah Staff
                                    </th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="role in customRoles"
                                    :key="role.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <Shield :size="14" class="flex-shrink-0 text-indigo-500 dark:text-indigo-400" />
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ role.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ role.description || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300"
                                        >
                                            {{ role.permissions_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ role.staff_members_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex items-center justify-end gap-1">
                                            <Link
                                                :href="`/roles/${role.id}/edit`"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-blue-600 dark:hover:bg-gray-700 dark:hover:text-blue-400"
                                                title="Edit"
                                            >
                                                <Edit :size="16" />
                                            </Link>
                                            <button
                                                @click="deleteRole(role)"
                                                class="rounded-lg p-2 text-gray-400 transition-colors hover:bg-gray-100 hover:text-red-600 dark:hover:bg-gray-700 dark:hover:text-red-400"
                                                title="Hapus"
                                            >
                                                <Trash2 :size="16" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty state -->
                        <div v-else class="px-6 py-12 text-center">
                            <Shield :size="40" class="mx-auto mb-3 text-gray-300 dark:text-gray-600" />
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada role custom</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Buat role baru untuk mengatur akses menu dan aksi staff secara spesifik.
                            </p>
                            <Link
                                href="/roles/create"
                                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                            >
                                <Plus :size="14" />
                                Tambah Role Pertama
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- System Roles Section -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div>
                            <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Role Sistem</h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                Role bawaan platform — tidak dapat diedit atau dihapus
                            </p>
                        </div>
                        <span
                            class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-400"
                        >
                            {{ systemRoles.length }} role
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Nama Role
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Deskripsi
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Jumlah Izin
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400"
                                    >
                                        Status
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="role in systemRoles"
                                    :key="role.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <Shield :size="14" class="flex-shrink-0 text-gray-400 dark:text-gray-500" />
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ role.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ role.description || '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                        >
                                            {{ role.permissions_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                        >
                                            <Lock :size="10" />
                                            Bawaan
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
