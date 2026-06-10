<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    users: Object,
    tenants: Array,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const tenant_id = ref(props.filters?.tenant_id || '');
const role = ref(props.filters?.role || '');
const status = ref(props.filters?.status || '');

const applyFilters = () => {
    router.get(
        '/admin/users',
        {
            search: search.value,
            tenant_id: tenant_id.value,
            role: role.value,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    tenant_id.value = '';
    role.value = '';
    status.value = '';
    applyFilters();
};
</script>

<template>
    <Head title="Users" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage all users across tenants</p>
            </div>
            <Link
                href="/admin/users/create"
                class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 font-medium text-white transition hover:bg-purple-700"
            >
                <Plus class="mr-2 h-5 w-5" />
                Create User
            </Link>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Search -->
                <div>
                    <div class="relative">
                        <Search class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search users..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-4 pl-10 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Tenant Filter -->
                <div>
                    <select
                        v-model="tenant_id"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">All Tenants</option>
                        <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">{{ tenant.name }}</option>
                    </select>
                </div>

                <!-- Role Filter -->
                <div>
                    <select
                        v-model="role"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="mt-3 flex justify-end">
                <button @click="clearFilters" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Users Table -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.tenant?.name }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full bg-indigo-100 px-2 py-1 text-xs font-medium text-indigo-800 capitalize dark:bg-indigo-900/30 dark:text-indigo-200"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    :class="[
                                        'rounded-full px-2 py-1 text-xs font-medium',
                                        user.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                    ]"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <Link
                                    :href="`/admin/users/${user.id}`"
                                    class="text-sm font-medium text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                >
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                    </div>
                    <div class="flex space-x-1">
                        <template v-for="(link, index) in users.links" :key="index">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                :class="[
                                    'rounded px-3 py-1 text-sm',
                                    link.active
                                        ? 'bg-purple-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                :class="[
                                    'cursor-not-allowed rounded bg-gray-100 px-3 py-1 text-sm text-gray-400 dark:bg-gray-800 dark:text-gray-600',
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
