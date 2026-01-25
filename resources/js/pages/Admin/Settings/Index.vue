<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

const props = defineProps({
    settings: Object,
})

const form = useForm({
    bank_name: props.settings.bank_name || '',
    account_number: props.settings.account_number || '',
    account_holder: props.settings.account_holder || '',
    membership_price_monthly: props.settings.membership_price_monthly || 25000,
    membership_price_yearly: props.settings.membership_price_yearly || 250000,
})

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <Head title="Admin Settings" />

    <AdminLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage global system settings</p>
        </div>

        <div class="max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Bank Information -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Bank Information</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Name</label>
                            <input 
                                v-model="form.bank_name"
                                type="text" 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <div v-if="form.errors.bank_name" class="mt-1 text-sm text-red-600">{{ form.errors.bank_name }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Number</label>
                            <input 
                                v-model="form.account_number"
                                type="text" 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <div v-if="form.errors.account_number" class="mt-1 text-sm text-red-600">{{ form.errors.account_number }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Holder</label>
                            <input 
                                v-model="form.account_holder"
                                type="text" 
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <div v-if="form.errors.account_holder" class="mt-1 text-sm text-red-600">{{ form.errors.account_holder }}</div>
                        </div>
                    </div>
                </div>

                <!-- Membership Pricing -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Membership Pricing</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Monthly Price (IDR)</label>
                            <input 
                                v-model="form.membership_price_monthly"
                                type="number" 
                                min="0"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <div v-if="form.errors.membership_price_monthly" class="mt-1 text-sm text-red-600">{{ form.errors.membership_price_monthly }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Yearly Price (IDR)</label>
                            <input 
                                v-model="form.membership_price_yearly"
                                type="number" 
                                min="0"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                            >
                            <div v-if="form.errors.membership_price_yearly" class="mt-1 text-sm text-red-600">{{ form.errors.membership_price_yearly }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors disabled:opacity-50"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Settings' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
