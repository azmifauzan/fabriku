<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Shield } from 'lucide-vue-next';

defineProps({
    user: Object,
});
</script>

<template>
    <Head :title="`${user.name}`" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a
                href="/admin/users"
                class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Users
            </a>
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                </div>
                <Link
                    :href="`/admin/users/${user.id}/edit`"
                    class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700"
                >
                    <Edit class="mr-2 h-4 w-4" />
                    Edit
                </Link>
            </div>
        </div>

        <!-- User Information -->
        <div class="mb-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">User Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tenant</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ user.tenant?.name || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Basic Role</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800 capitalize dark:bg-indigo-900/30 dark:text-indigo-200"
                            >
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
                                    'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                    user.is_active
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
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
        <div
            v-if="user.roles && user.roles.length > 0"
            class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center">
                    <Shield class="mr-2 h-5 w-5 text-gray-500 dark:text-gray-400" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Assigned Roles</h2>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="role in user.roles" :key="role.id" class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <h3 class="font-medium text-gray-900 dark:text-white">{{ role.name }}</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ role.description || 'No description' }}</p>
                        <div class="mt-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400"> {{ role.permissions?.length || 0 }} permissions </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
