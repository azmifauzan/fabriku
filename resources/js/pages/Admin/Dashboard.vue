<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Building2, Package, ShoppingCart, Users } from 'lucide-vue-next';

defineProps({
    stats: Object,
    recentTenants: Array,
    tenantGrowth: Array,
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Welcome to Fabriku Admin Panel</p>
        </div>

        <!-- Stats Grid -->
        <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Tenants -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Tenants</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_tenants }}</p>
                        <p class="mt-1 text-sm text-green-600">{{ stats.active_tenants }} active</p>
                    </div>
                    <div class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900/30">
                        <Building2 class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</p>
                        <p class="mt-1 text-sm text-gray-500">Across all tenants</p>
                    </div>
                    <div class="rounded-lg bg-indigo-100 p-3 dark:bg-indigo-900/30">
                        <Users class="h-8 w-8 text-indigo-600 dark:text-indigo-400" />
                    </div>
                </div>
            </div>

            <!-- Preparation Orders -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Preparation Orders</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_preparation_orders }}</p>
                        <p class="mt-1 text-sm text-gray-500">All time</p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30">
                        <Package class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Sales Orders -->
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Sales Orders</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_sales_orders }}</p>
                        <p class="mt-1 text-sm text-gray-500">All time</p>
                    </div>
                    <div class="rounded-lg bg-green-100 p-3 dark:bg-green-900/30">
                        <ShoppingCart class="h-8 w-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Tenants -->
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Tenants</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="tenant in recentTenants" :key="tenant.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                                >
                                    {{ tenant.business_category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900/30 dark:text-purple-200"
                                >
                                    {{ tenant.subscription_plan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs font-medium',
                                        tenant.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                    ]"
                                >
                                    {{ tenant.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ tenant.created_at }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
