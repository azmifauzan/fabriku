<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft, Edit, Power, PowerOff } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    tenant: Object,
    stats: Object,
})

const suspend = () => {
    if (confirm('Are you sure you want to suspend this tenant?')) {
        router.post(`/admin/tenants/${props.tenant.id}/suspend`)
    }
}

const activate = () => {
    router.post(`/admin/tenants/${props.tenant.id}/activate`)
}
</script>

<template>
    <Head :title="`${tenant.name}`" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a href="/admin/tenants" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Tenants
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ tenant.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Tenant Details</p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="`/admin/tenants/${tenant.id}/edit`"
                        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        <Edit class="w-4 h-4 mr-2" />
                        Edit
                    </Link>
                    <button
                        v-if="tenant.is_active"
                        @click="suspend"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition"
                    >
                        <PowerOff class="w-4 h-4 mr-2" />
                        Suspend
                    </button>
                    <button
                        v-else
                        @click="activate"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition"
                    >
                        <Power class="w-4 h-4 mr-2" />
                        Activate
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.users_count || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Materials</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.materials_count || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Patterns</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.patterns_count || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Preparation</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.preparation_orders_count || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Production</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.production_orders_count || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">Sales</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ stats.sales_orders_count || 0 }}</p>
            </div>
        </div>

        <!-- Tenant Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tenant Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Business Category</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white capitalize">{{ tenant.business_category }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription Plan</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 capitalize">
                                {{ tenant.subscription_plan }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span
                                :class="[
                                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                                    tenant.is_active
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                                        : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                                ]"
                            >
                                {{ tenant.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription Expires</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ tenant.subscription_expires_at ? new Date(tenant.subscription_expires_at).toLocaleDateString() : 'N/A' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ new Date(tenant.created_at).toLocaleDateString() }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ new Date(tenant.updated_at).toLocaleDateString() }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Users List -->
        <div v-if="tenant.users && tenant.users.length > 0" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in tenant.users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white capitalize">{{ user.role }}</td>
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
