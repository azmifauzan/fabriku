<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft } from 'lucide-vue-next'

const form = useForm({
    name: '',
    business_category: 'garment',
    subscription_plan: 'trial',
    subscription_days: 30,
    admin_name: '',
    admin_email: '',
    admin_password: '',
})

const submit = () => {
    form.post('/admin/tenants', {
        onSuccess: () => form.reset(),
    })
}
</script>

<template>
    <Head title="Create Tenant" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a href="/admin/tenants" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Tenants
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Tenant</h1>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Tenant Information -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tenant Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tenant Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tenant Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                placeholder="e.g., Konveksi ABC"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Business Category -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Business Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.business_category"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Subscription Plan <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.subscription_plan"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
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
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Subscription Duration (days) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model.number="form.subscription_days"
                                type="number"
                                required
                                min="1"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            />
                            <p v-if="form.errors.subscription_days" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_days }}</p>
                        </div>
                    </div>
                </div>

                <!-- Admin User Information -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Admin User</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Admin Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Admin Name <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.admin_name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            />
                            <p v-if="form.errors.admin_name" class="mt-1 text-sm text-red-600">{{ form.errors.admin_name }}</p>
                        </div>

                        <!-- Admin Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Admin Email <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.admin_email"
                                type="email"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            />
                            <p v-if="form.errors.admin_email" class="mt-1 text-sm text-red-600">{{ form.errors.admin_email }}</p>
                        </div>

                        <!-- Admin Password -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Admin Password <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.admin_password"
                                type="password"
                                required
                                minlength="8"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            />
                            <p v-if="form.errors.admin_password" class="mt-1 text-sm text-red-600">{{ form.errors.admin_password }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a
                        href="/admin/tenants"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Tenant' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
