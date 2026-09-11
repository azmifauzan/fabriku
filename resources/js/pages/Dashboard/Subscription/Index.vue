<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/SubscriptionController';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    tenant: any;
    settings: any;
    pendingPayment: any;
    history: any[];
    server_time: string;
}>();

const { showError } = useSweetAlert();

const form = useForm({
    plan_type: 'monthly', // monthly, yearly
    payment_method: 'sumopod',
    amount: props.settings.membership_price_monthly || 25000,
});

const isExpired = computed(() => {
    if (!props.tenant.subscription_expires_at) return false;
    return new Date(props.tenant.subscription_expires_at) < new Date(props.server_time);
});
const isActive = computed(() => props.tenant.is_active && !isExpired.value);

const handlePlanChange = () => {
    if (form.plan_type === 'monthly') {
        form.amount = props.settings.membership_price_monthly || 25000;
    } else {
        form.amount = props.settings.membership_price_yearly || 250000;
    }
};

const submit = () => {
    form.post(store(), {
        preserveScroll: true,
        onError: () => {
            showError('Gagal!', 'Pembayaran belum dapat diproses. Silakan coba lagi.');
        },
    });
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <Head title="Subscription" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl leading-tight font-semibold text-gray-800 dark:text-white">Membership & Subscription</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <!-- Status Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Status Membership</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tipe Member</p>
                            <p class="text-xl font-bold text-purple-600 capitalize dark:text-purple-400">
                                {{ tenant.subscription_plan === 'trial' ? 'Free Trial' : 'Full Member' }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                            <span
                                class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="
                                    isActive
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'
                                "
                            >
                                {{ isActive ? 'Aktif' : 'Tidak Aktif / Expired' }}
                            </span>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/50">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Berlaku Sampai</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ tenant.subscription_expires_at ? formatDate(tenant.subscription_expires_at) : '-' }}
                            </p>
                            <p v-if="isExpired" class="mt-1 text-xs text-red-600 dark:text-red-400">Masa berlaku habis. Segera perpanjang!</p>
                        </div>
                    </div>
                </div>

                <!-- Upgrade / Renewal Form -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">
                        {{ pendingPayment ? 'Menunggu Konfirmasi' : 'Perpanjang / Upgrade Membership' }}
                    </h3>

                    <div v-if="pendingPayment" class="rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            <div>
                                <h4 class="font-bold text-yellow-800">Pembayaran Sedang Diproses</h4>
                                <p class="text-sm text-yellow-700">
                                    Pembayaran QRIS senilai <strong>{{ formatCurrency(pendingPayment.amount) }}</strong> belum selesai.
                                </p>
                                <a
                                    v-if="pendingPayment.payment_url"
                                    :href="pendingPayment.payment_url"
                                    class="mt-3 inline-flex min-h-11 items-center rounded-lg bg-yellow-800 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-900 focus:ring-2 focus:ring-yellow-700 focus:ring-offset-2 focus:outline-none"
                                >
                                    Lanjutkan pembayaran QRIS
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div>
                            <h4 class="mb-3 font-medium text-gray-700">Info Pembayaran</h4>

                            <div class="rounded-xl border border-purple-200 bg-purple-50 p-4 text-purple-950">
                                <p class="font-semibold">QRIS via SumoPod</p>
                                <p class="mt-1 text-sm">Setelah melanjutkan, pindai QRIS dan status membership akan diperbarui otomatis.</p>
                            </div>

                            <div class="mt-4 space-y-2">
                                <p class="text-sm text-gray-600">Pilih Paket:</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            v-model="form.plan_type"
                                            value="monthly"
                                            class="peer sr-only"
                                            @change="handlePlanChange"
                                        />
                                        <div class="rounded-lg border p-3 transition-colors peer-checked:border-indigo-500 peer-checked:bg-indigo-50">
                                            <div class="font-medium">Bulanan</div>
                                            <div class="text-sm text-gray-500">{{ formatCurrency(settings.membership_price_monthly || 25000) }}</div>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" v-model="form.plan_type" value="yearly" class="peer sr-only" @change="handlePlanChange" />
                                        <div class="rounded-lg border p-3 transition-colors peer-checked:border-indigo-500 peer-checked:bg-indigo-50">
                                            <div class="font-medium">Tahunan</div>
                                            <div class="text-sm text-gray-500">{{ formatCurrency(settings.membership_price_yearly || 250000) }}</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Total Pembayaran</label>
                                    <input
                                        type="text"
                                        :value="formatCurrency(form.amount)"
                                        disabled
                                        class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    />
                                </div>

                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex min-h-11 w-full justify-center rounded-lg border border-transparent bg-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-purple-700 focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50 dark:bg-purple-600 dark:hover:bg-purple-700"
                                >
                                    {{ form.processing ? 'Memproses...' : 'Bayar dengan QRIS SumoPod' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History -->
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Riwayat Pembayaran</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nominal
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Paket
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Catatan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-for="item in history" :key="item.id">
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 capitalize dark:text-gray-400">
                                        {{ item.plan_type }} ({{ item.duration_months }} bln)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold capitalize"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': item.status === 'pending',
                                                'bg-green-100 text-green-800': item.status === 'approved',
                                                'bg-red-100 text-red-800': ['rejected', 'failed', 'expired'].includes(item.status),
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500">
                                        {{ item.rejection_reason || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="history.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                        Belum ada riwayat pembayaran
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
