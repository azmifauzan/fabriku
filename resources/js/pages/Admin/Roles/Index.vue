<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps({
    roles: Array,
});
</script>

<template>
    <Head title="Roles" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roles</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage roles and permissions</p>
            </div>
            <Link
                href="/admin/roles/create"
                class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 font-medium text-white transition hover:bg-purple-700"
            >
                <Plus class="mr-2 h-5 w-5" />
                Create Role
            </Link>
        </div>

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="role in roles"
                :key="role.id"
                class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
            >
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ role.name }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ role.slug }}</p>
                    </div>
                    <span
                        v-if="role.is_system_role"
                        class="rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-200"
                    >
                        System
                    </span>
                </div>

                <p v-if="role.description" class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ role.description }}
                </p>

                <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ role.permissions_count || 0 }}</span> permissions
                        <span class="mx-2">•</span>
                        <span class="font-medium">{{ role.users_count || 0 }}</span> users
                    </div>
                    <Link
                        :href="`/admin/roles/${role.id}`"
                        class="text-sm font-medium text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                    >
                        View
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
