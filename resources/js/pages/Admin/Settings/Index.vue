<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { Plus, Trash2 } from 'lucide-vue-next'
import { update } from '@/actions/App/Http/Controllers/Admin/AdminSettingController'
import { useSweetAlert } from '@/composables/useSweetAlert'

const props = defineProps({
    settings: Object,
})

const { showSuccess, showError } = useSweetAlert()

// Initialize bank accounts from settings or with one empty account
const initBankAccounts = () => {
    if (props.settings.bank_accounts && props.settings.bank_accounts.length > 0) {
        return props.settings.bank_accounts
    }
    // Migrate from old format if exists
    if (props.settings.bank_name) {
        return [{
            bank_name: props.settings.bank_name,
            account_number: props.settings.account_number || '',
            account_holder: props.settings.account_holder || '',
        }]
    }
    return [{ bank_name: '', account_number: '', account_holder: '' }]
}

const form = useForm({
    bank_accounts: initBankAccounts(),
    membership_price_monthly: props.settings.membership_price_monthly || 25000,
    membership_price_yearly: props.settings.membership_price_yearly || 250000,
    pro_price_monthly: props.settings.pro_price_monthly || 35000,
    pro_price_yearly: props.settings.pro_price_yearly || 350000,
    max_staff_per_tenant: props.settings.max_staff_per_tenant || 5,
})

const addBankAccount = () => {
    form.bank_accounts.push({ bank_name: '', account_number: '', account_holder: '' })
}

const removeBankAccount = (index) => {
    if (form.bank_accounts.length > 1) {
        form.bank_accounts.splice(index, 1)
    }
}

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess('Berhasil!', 'Pengaturan berhasil disimpan')
        },
        onError: () => {
            showError('Gagal!', 'Terjadi kesalahan saat menyimpan pengaturan')
        },
    })
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value)
}
</script>

<template>
    <Head title="Admin Settings" />

    <AdminLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Settings</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Kelola pengaturan sistem dan membership</p>
        </div>

        <div class="max-w-4xl">
            <form @submit.prevent="submit" class="space-y-6">
                
                <!-- Bank Accounts -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Rekening Bank</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tambahkan rekening untuk menerima pembayaran membership</p>
                        </div>
                        <button
                            type="button"
                            @click="addBankAccount"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-purple-600 bg-purple-50 rounded-lg hover:bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50"
                        >
                            <Plus class="w-4 h-4" />
                            Tambah Rekening
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div 
                            v-for="(account, index) in form.bank_accounts" 
                            :key="index"
                            class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg bg-gray-50 dark:bg-gray-900/50"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Rekening {{ index + 1 }}</span>
                                <button
                                    v-if="form.bank_accounts.length > 1"
                                    type="button"
                                    @click="removeBankAccount(index)"
                                    class="p-1 text-red-500 hover:text-red-700 hover:bg-red-50 rounded dark:hover:bg-red-900/30"
                                >
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Bank</label>
                                    <input 
                                        v-model="account.bank_name"
                                        type="text"
                                        placeholder="BCA, Mandiri, dll"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Rekening</label>
                                    <input 
                                        v-model="account.account_number"
                                        type="text"
                                        placeholder="1234567890"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Atas Nama</label>
                                    <input 
                                        v-model="account.account_holder"
                                        type="text"
                                        placeholder="Nama pemilik rekening"
                                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="form.errors.bank_accounts" class="mt-2 text-sm text-red-600">{{ form.errors.bank_accounts }}</div>
                </div>

                <!-- Membership Pricing -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Harga Full Member</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Paket dasar dengan kuota AI Assistant 200 pesan/hari</p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Bulanan (IDR)</label>
                            <input 
                                v-model="form.membership_price_monthly"
                                type="number" 
                                min="0"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(form.membership_price_monthly) }}</p>
                            <div v-if="form.errors.membership_price_monthly" class="mt-1 text-sm text-red-600">{{ form.errors.membership_price_monthly }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Tahunan (IDR)</label>
                            <input 
                                v-model="form.membership_price_yearly"
                                type="number" 
                                min="0"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(form.membership_price_yearly) }} <span class="text-green-600 dark:text-green-400">(Hemat {{ formatCurrency((form.membership_price_monthly * 12) - form.membership_price_yearly) }})</span></p>
                            <div v-if="form.errors.membership_price_yearly" class="mt-1 text-sm text-red-600">{{ form.errors.membership_price_yearly }}</div>
                        </div>
                    </div>
                </div>

                <!-- Pro Membership Pricing -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Harga Pro Member</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Paket premium dengan kuota AI Assistant 500 pesan/hari (2.5x lebih banyak)</p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Bulanan (IDR)</label>
                            <input 
                                v-model="form.pro_price_monthly"
                                type="number" 
                                min="0"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(form.pro_price_monthly) }}</p>
                            <div v-if="form.errors.pro_price_monthly" class="mt-1 text-sm text-red-600">{{ form.errors.pro_price_monthly }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Tahunan (IDR)</label>
                            <input 
                                v-model="form.pro_price_yearly"
                                type="number" 
                                min="0"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(form.pro_price_yearly) }} <span class="text-green-600 dark:text-green-400">(Hemat {{ formatCurrency((form.pro_price_monthly * 12) - form.pro_price_yearly) }})</span></p>
                            <div v-if="form.errors.pro_price_yearly" class="mt-1 text-sm text-red-600">{{ form.errors.pro_price_yearly }}</div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Limits -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Batas Tenant</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Pengaturan kuota dan batas untuk setiap tenant</p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maksimal Staff per Tenant</label>
                            <input 
                                v-model="form.max_staff_per_tenant"
                                type="number" 
                                min="1"
                                max="100"
                                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jumlah maksimal staff yang dapat dibuat oleh setiap tenant</p>
                            <div v-if="form.errors.max_staff_per_tenant" class="mt-1 text-sm text-red-600">{{ form.errors.max_staff_per_tenant }}</div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-6 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 dark:bg-purple-600 dark:hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
