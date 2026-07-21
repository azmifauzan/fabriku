<script setup lang="ts">
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, CreditCard, Edit, Power, PowerOff } from 'lucide-vue-next';

const props = defineProps({
    tenant: Object,
    stats: Object,
    payments: Array,
});

const suspend = () => {
    if (confirm('Are you sure you want to suspend this tenant?')) {
        router.post(`/admin/tenants/${props.tenant.id}/suspend`);
    }
};

const activate = () => {
    router.post(`/admin/tenants/${props.tenant.id}/activate`);
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[status] || colors.pending;
};

const isExpired = () => {
    if (!props.tenant.subscription_expires_at) return false;
    return new Date(props.tenant.subscription_expires_at) < new Date();
};
</script>

<template>
    <Head :title="`${tenant.name}`" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a
                href="/admin/tenants"
                class="mb-4 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to Tenants
            </a>
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ tenant.name }}</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Tenant Details</p>
                </div>
                <div class="flex space-x-3">
                    <Link
                        :href="`/admin/tenants/${tenant.id}/edit`"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <Edit class="mr-2 h-4 w-4" />
                        Edit
                    </Link>
                    <button
                        v-if="tenant.is_active"
                        @click="suspend"
                        class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-white transition hover:bg-red-700"
                    >
                        <PowerOff class="mr-2 h-4 w-4" />
                        Suspend
                    </button>
                    <button
                        v-else
                        @click="activate"
                        class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-white transition hover:bg-green-700"
                    >
                        <Power class="mr-2 h-4 w-4" />
                        Activate
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3 lg:grid-cols-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Users</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.users_count || 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Materials</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.materials_count || 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Patterns</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.patterns_count || 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Preparation</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.preparation_orders_count || 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Production</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.production_orders_count || 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="text-sm text-gray-600 dark:text-gray-400">Sales</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ stats.sales_orders_count || 0 }}</p>
            </div>
        </div>

        <!-- Tenant Information -->
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Tenant Information</h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Business Category</dt>
                        <dd class="mt-1 text-sm text-gray-900 capitalize dark:text-white">{{ tenant.business_category }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription Plan</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 capitalize dark:bg-purple-900/30 dark:text-purple-200"
                            >
                                {{ tenant.subscription_plan }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <span
                                :class="[
                                    'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                    tenant.is_active
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200',
                                ]"
                            >
                                {{ tenant.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Subscription Expires</dt>
                        <dd class="mt-1 text-sm" :class="isExpired() ? 'font-bold text-red-600' : 'text-gray-900 dark:text-white'">
                            {{ tenant.subscription_expires_at ? formatDate(tenant.subscription_expires_at) : 'N/A' }}
                            <span v-if="isExpired()" class="text-xs">(Expired)</span>
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
        <div
            v-if="tenant.users && tenant.users.length > 0"
            class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800"
        >
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="user in tenant.users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ user.email }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 capitalize dark:text-white">{{ user.role }}</td>
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
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment History -->
        <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <CreditCard class="h-5 w-5 text-purple-600" />
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Payment History</h2>
                </div>
                <Link href="/admin/payments" class="text-sm text-purple-600 hover:text-purple-700"> View All Payments </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Paket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Nominal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase dark:text-gray-400">Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ formatDate(payment.created_at) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 capitalize dark:text-white">
                                {{ payment.plan_type }} ({{ payment.duration_months }} bln)
                            </td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(payment.amount) }}
                            </td>
                            <td class="px-6 py-4">
                                <span :class="['rounded-full px-2 py-1 text-xs font-medium capitalize', getStatusColor(payment.status)]">
                                    {{ payment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ payment.admin?.name || '-' }}
                            </td>
                        </tr>
                        <tr v-if="!payments || payments.length === 0">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada riwayat pembayaran</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
