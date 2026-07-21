<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/Admin/AdminSettingController';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';

const props = defineProps({
    settings: Object,
});

const { showSuccess, showError } = useSweetAlert();

// Initialize bank accounts from settings or with one empty account
const initBankAccounts = () => {
    if (props.settings.bank_accounts && props.settings.bank_accounts.length > 0) {
        return props.settings.bank_accounts;
    }
    // Migrate from old format if exists
    if (props.settings.bank_name) {
        return [
            {
                bank_name: props.settings.bank_name,
                account_number: props.settings.account_number || '',
                account_holder: props.settings.account_holder || '',
            },
        ];
    }
    return [{ bank_name: '', account_number: '', account_holder: '' }];
};

const form = useForm({
    bank_accounts: initBankAccounts(),
    membership_price_monthly: props.settings.membership_price_monthly || 25000,
    membership_price_yearly: props.settings.membership_price_yearly || 250000,
    pro_price_monthly: props.settings.pro_price_monthly || 35000,
    pro_price_yearly: props.settings.pro_price_yearly || 350000,
    max_staff_per_tenant: props.settings.max_staff_per_tenant || 5,
});

const addBankAccount = () => {
    form.bank_accounts.push({ bank_name: '', account_number: '', account_holder: '' });
};

const removeBankAccount = (index) => {
    if (form.bank_accounts.length > 1) {
        form.bank_accounts.splice(index, 1);
    }
};

const submit = () => {
    form.post(update.url(), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccess('Berhasil!', 'Pengaturan berhasil disimpan');
        },
        onError: () => {
            showError('Gagal!', 'Terjadi kesalahan saat menyimpan pengaturan');
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
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
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Rekening Bank</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tambahkan rekening untuk menerima pembayaran membership</p>
                        </div>
                        <button
                            type="button"
                            @click="addBankAccount"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-purple-50 px-3 py-1.5 text-sm font-medium text-purple-600 hover:bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400 dark:hover:bg-purple-900/50"
                        >
                            <Plus class="h-4 w-4" />
                            Tambah Rekening
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div
                            v-for="(account, index) in form.bank_accounts"
                            :key="index"
                            class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/50"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Rekening {{ index + 1 }}</span>
                                <button
                                    v-if="form.bank_accounts.length > 1"
                                    type="button"
                                    @click="removeBankAccount(index)"
                                    class="rounded p-1 text-red-500 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/30"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </button>
                            </div>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Bank</label>
                                    <input
                                        v-model="account.bank_name"
                                        type="text"
                                        placeholder="BCA, Mandiri, dll"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Rekening</label>
                                    <input
                                        v-model="account.account_number"
                                        type="text"
                                        placeholder="1234567890"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Atas Nama</label>
                                    <input
                                        v-model="account.account_holder"
                                        type="text"
                                        placeholder="Nama pemilik rekening"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-if="form.errors.bank_accounts" class="mt-2 text-sm text-red-600">{{ form.errors.bank_accounts }}</div>
                </div>

                <!-- Membership Pricing -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Harga Full Member</h2>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Paket dasar dengan kuota AI Assistant 200 pesan/hari</p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Bulanan (IDR)</label>
                            <input
                                v-model="form.membership_price_monthly"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(form.membership_price_monthly) }}</p>
                            <div v-if="form.errors.membership_price_monthly" class="mt-1 text-sm text-red-600">
                                {{ form.errors.membership_price_monthly }}
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Tahunan (IDR)</label>
                            <input
                                v-model="form.membership_price_yearly"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ formatCurrency(form.membership_price_yearly) }}
                                <span class="text-green-600 dark:text-green-400"
                                    >(Hemat {{ formatCurrency(form.membership_price_monthly * 12 - form.membership_price_yearly) }})</span
                                >
                            </p>
                            <div v-if="form.errors.membership_price_yearly" class="mt-1 text-sm text-red-600">
                                {{ form.errors.membership_price_yearly }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pro Membership Pricing -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Harga Pro Member</h2>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                        Paket premium dengan kuota AI Assistant 500 pesan/hari (2.5x lebih banyak)
                    </p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Bulanan (IDR)</label>
                            <input
                                v-model="form.pro_price_monthly"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ formatCurrency(form.pro_price_monthly) }}</p>
                            <div v-if="form.errors.pro_price_monthly" class="mt-1 text-sm text-red-600">{{ form.errors.pro_price_monthly }}</div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Tahunan (IDR)</label>
                            <input
                                v-model="form.pro_price_yearly"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ formatCurrency(form.pro_price_yearly) }}
                                <span class="text-green-600 dark:text-green-400"
                                    >(Hemat {{ formatCurrency(form.pro_price_monthly * 12 - form.pro_price_yearly) }})</span
                                >
                            </p>
                            <div v-if="form.errors.pro_price_yearly" class="mt-1 text-sm text-red-600">{{ form.errors.pro_price_yearly }}</div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Limits -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">Batas Tenant</h2>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Pengaturan kuota dan batas untuk setiap tenant</p>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Maksimal Staff per Tenant</label>
                            <input
                                v-model="form.max_staff_per_tenant"
                                type="number"
                                min="1"
                                max="100"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Jumlah maksimal staff yang dapat dibuat oleh setiap tenant</p>
                            <div v-if="form.errors.max_staff_per_tenant" class="mt-1 text-sm text-red-600">
                                {{ form.errors.max_staff_per_tenant }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-lg bg-purple-600 px-6 py-2 font-medium text-white transition-colors hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-purple-600 dark:hover:bg-purple-700"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
