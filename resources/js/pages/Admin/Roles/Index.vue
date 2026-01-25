<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Plus } from 'lucide-vue-next'

defineProps({
    roles: Array,
})
</script>

<template>
    <Head title="Roles" />

    <AdminLayout>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Roles</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage roles and permissions</p>
            </div>
            <Link
                href="/admin/roles/create"
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition"
            >
                <Plus class="w-5 h-5 mr-2" />
                Create Role
            </Link>
        </div>

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="role in roles"
                :key="role.id"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition"
            >
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ role.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ role.slug }}</p>
                    </div>
                    <span
                        v-if="role.is_system_role"
                        class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200"
                    >
                        System
                    </span>
                </div>

                <p v-if="role.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    {{ role.description }}
                </p>

                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ role.permissions_count || 0 }}</span> permissions
                        <span class="mx-2">•</span>
                        <span class="font-medium">{{ role.users_count || 0 }}</span> users
                    </div>
                    <Link
                        :href="`/admin/roles/${role.id}`"
                        class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium"
                    >
                        View
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
