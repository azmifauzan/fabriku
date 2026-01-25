<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ref } from 'vue'
import { Plus, Search, Filter } from 'lucide-vue-next'

const props = defineProps({
    tenants: Object,
    filters: Object,
})

const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const plan = ref(props.filters?.plan || '')

const applyFilters = () => {
    router.get('/admin/tenants', {
        search: search.value,
        status: status.value,
        plan: plan.value,
    }, {
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    search.value = ''
    status.value = ''
    plan.value = ''
    applyFilters()
}
</script>

<template>
    <Head title="Tenants" />

    <AdminLayout>
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tenants</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage all tenants in the platform</p>
            </div>
            <Link
                href="/admin/tenants/create"
                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition"
            >
                <Plus class="w-5 h-5 mr-2" />
                Create Tenant
            </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search tenants..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <!-- Plan Filter -->
                <div>
                    <select
                        v-model="plan"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">All Plans</option>
                        <option value="trial">Trial</option>
                        <option value="basic">Basic</option>
                        <option value="premium">Premium</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button
                    @click="clearFilters"
                    class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white"
                >
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Tenants Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="tenant in tenants.data" :key="tenant.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ tenant.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                    {{ tenant.business_category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200">
                                    {{ tenant.subscription_plan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                {{ tenant.users_count || 0 }}
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        tenant.is_active
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200'
                                            : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200'
                                    ]"
                                >
                                    {{ tenant.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="`/admin/tenants/${tenant.id}`"
                                    class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 text-sm font-medium"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="tenants.links" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ tenants.from }} to {{ tenants.to }} of {{ tenants.total }} results
                    </div>
                    <div class="flex space-x-1">
                        <template v-for="(link, index) in tenants.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded',
                                    link.active
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'px-3 py-1 text-sm rounded bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
