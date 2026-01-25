<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft, Edit, Shield } from 'lucide-vue-next'

defineProps({
    user: Object,
})
</script>

<template>
    <Head :title="`${user.name}`" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a href="/admin/users" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Users
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                </div>
                <Link
                    :href="`/admin/users/${user.id}/edit`"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition"
                >
                    <Edit class="w-4 h-4 mr-2" />
                    Edit
                </Link>
            </div>
        </div>

        <!-- User Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">User Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ user.tenant?.name || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Basic Role</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-200 capitalize">
                                {{ user.role }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ user.phone || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span
                                :class="[
                                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                                    user.is_active
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                                        : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                                ]"
                            >
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verified</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ user.email_verified_at ? new Date(user.email_verified_at).toLocaleDateString() : 'Not verified' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ new Date(user.created_at).toLocaleDateString() }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Assigned Roles -->
        <div v-if="user.roles && user.roles.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <Shield class="w-5 h-5 text-gray-500 dark:text-gray-400 mr-2" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned Roles</h2>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="role in user.roles"
                        :key="role.id"
                        class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
                    >
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ role.name }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ role.description || 'No description' }}</p>
                        <div class="mt-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ role.permissions?.length || 0 }} permissions
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
