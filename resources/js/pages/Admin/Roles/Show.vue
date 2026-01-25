<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft, Edit, Shield, Users } from 'lucide-vue-next'

defineProps({
    role: Object,
})
</script>

<template>
    <Head :title="`${role.name}`" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a href="/admin/roles" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Roles
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ role.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ role.slug }}</p>
                </div>
                <Link
                    v-if="!role.is_system_role"
                    :href="`/admin/roles/${role.id}/edit`"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
                >
                    <Edit class="w-4 h-4 mr-2" />
                    Edit
                </Link>
                <span
                    v-else
                    class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200"
                >
                    System Role
                </span>
            </div>
        </div>

        <!-- Role Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Role Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ role.description || 'No description' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Permissions</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ role.permissions?.length || 0 }} permissions</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ role.users?.length || 0 }} users</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ new Date(role.created_at).toLocaleDateString() }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Permissions -->
        <div v-if="role.permissions && role.permissions.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <Shield class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Permissions</h2>
                </div>
            </div>
            <div class="p-6">
                <div v-for="(perms, module) in groupPermissionsByModule(role.permissions)" :key="module" class="mb-6 last:mb-0">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3 capitalize">{{ module }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div
                            v-for="permission in perms"
                            :key="permission.id"
                            class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-sm text-gray-700 dark:text-gray-300"
                        >
                            {{ permission.name }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div v-if="role.users && role.users.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <Users class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users with this Role</h2>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in role.users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.tenant?.name || 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        user.is_active
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                                            : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                                    ]"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>

<script>
export default {
    methods: {
        groupPermissionsByModule(permissions) {
            return permissions.reduce((groups, permission) => {
                const module = permission.module || 'other'
                if (!groups[module]) {
                    groups[module] = []
                }
                groups[module].push(permission)
                return groups
            }, {})
        }
    }
}
</script>
