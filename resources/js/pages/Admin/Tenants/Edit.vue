<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ArrowLeft } from 'lucide-vue-next'

const props = defineProps({
    tenant: Object,
})

const form = useForm({
    name: props.tenant.name,
    business_category: props.tenant.business_category,
    subscription_plan: props.tenant.subscription_plan,
    subscription_expires_at: props.tenant.subscription_expires_at?.split('T')[0],
    is_active: props.tenant.is_active,
})

const submit = () => {
    form.put(`/admin/tenants/${props.tenant.id}`)
}
</script>

<template>
    <Head title="Edit Tenant" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <a :href="`/admin/tenants/${tenant.id}`" class="inline-flex items-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-4">
                <ArrowLeft class="w-4 h-4 mr-2" />
                Back to Tenant
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Tenant</h1>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tenant Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
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

                    <!-- Subscription Expires At -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Subscription Expires <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.subscription_expires_at"
                            type="date"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                        />
                        <p v-if="form.errors.subscription_expires_at" class="mt-1 text-sm text-red-600">{{ form.errors.subscription_expires_at }}</p>
                    </div>

                    <!-- Active Status -->
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                            />
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                        </label>
                        <p v-if="form.errors.is_active" class="mt-1 text-sm text-red-600">{{ form.errors.is_active }}</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a
                        :href="`/admin/tenants/${tenant.id}`"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Updating...' : 'Update Tenant' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
