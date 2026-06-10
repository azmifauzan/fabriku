<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const form = useForm({
    name: '',
    business_category: 'garment',
    subscription_plan: 'trial',
    subscription_days: 30,
    admin_name: '',
    admin_email: '',
    admin_password: '',
});

const submit = () => {
    form.post('/admin/tenants', {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Tenant" />

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
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Tenant</h1>
        </div>

        <!-- Form -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Tenant Information -->
                <div class="border-b border-gray-200 pb-6 dark:border-gray-700">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Tenant Information</h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Tenant Name -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Tenant Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                placeholder="e.g., Konveksi ABC"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Business Category -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Business Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.business_category"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="garment">Garment</option>
                                <option value="food">Food</option>
                                <option value="craft">Craft</option>
                                <option value="cosmetic">Cosmetic</option>
                                <option value="other">Other</option>
                            </select>
                            <p v-if="form.errors.business_category" class="mt-1 text-sm text-red-600">{{ form.errors.business_category }}</p>
                        </div>

                        <!-- Subscription Plan -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subscription Plan <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.subscription_plan"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="trial">Trial</option>
                                <option value="basic">Basic</option>
                                <option value="premium">Premium</option>
                                <option value="enterprise">Enterprise</option>
                            </select>
                            <p v-if="form.errors.subscription_plan" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_plan }}</p>
                        </div>

                        <!-- Subscription Duration -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Subscription Duration (days) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="form.subscription_days"
                                type="number"
                                required
                                min="1"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p v-if="form.errors.subscription_days" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_days }}</p>
                        </div>
                    </div>
                </div>

                <!-- Admin User Information -->
                <div>
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Admin User</h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Admin Name -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Admin Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.admin_name"
                                type="text"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p v-if="form.errors.admin_name" class="mt-1 text-sm text-red-600">{{ form.errors.admin_name }}</p>
                        </div>

                        <!-- Admin Email -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Admin Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.admin_email"
                                type="email"
                                required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</p>
                        </div>

                        <!-- Admin Password -->
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Admin Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.admin_password"
                                type="password"
                                required
                                minlength="8"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p v-if="form.errors.admin_password" class="mt-1 text-sm text-red-600">{{ form.errors.admin_password }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-700">
                    <a
                        href="/admin/tenants"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-purple-600 px-4 py-2 text-white transition hover:bg-purple-700 disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Tenant' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
