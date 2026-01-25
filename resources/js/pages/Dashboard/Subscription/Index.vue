<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    tenant: any;
    settings: any;
    pendingPayment: any;
    history: any[];
    server_time: string;
}>();

const form = useForm({
    plan_type: 'monthly', // monthly, yearly
    amount: props.settings.membership_price_monthly || 25000,
    proof: null as File | null,
});

const isTrial = computed(() => props.tenant.subscription_plan === 'trial');
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
    form.post(route('subscription.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset('proof'),
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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Membership & Subscription</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Status Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status Membership</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500">Tipe Member</p>
                            <p class="text-xl font-bold capitalize text-primary-600">
                                {{ tenant.subscription_plan === 'trial' ? 'Free Trial' : 'Full Member' }}
                            </p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500">Status</p>
                            <span 
                                class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                            >
                                {{ isActive ? 'Aktif' : 'Tidak Aktif / Expired' }}
                            </span>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-500">Berlaku Sampai</p>
                            <p class="text-xl font-bold">
                                {{ tenant.subscription_expires_at ? formatDate(tenant.subscription_expires_at) : '-' }}
                            </p>
                            <p v-if="isExpired" class="text-xs text-red-600 mt-1">
                                Masa berlaku habis. Segera perpanjang!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Upgrade / Renewal Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ pendingPayment ? 'Menunggu Konfirmasi' : 'Perpanjang / Upgrade Membership' }}
                    </h3>

                    <div v-if="pendingPayment" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h4 class="font-bold text-yellow-800">Pembayaran Sedang Diproses</h4>
                                <p class="text-sm text-yellow-700">
                                    Kami sedang memverifikasi bukti pembayaran Anda senilai 
                                    <strong>{{ formatCurrency(pendingPayment.amount) }}</strong>. 
                                    Mohon tunggu maksimal 1x24 jam.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Info Rekening -->
                        <div>
                            <h4 class="font-medium text-gray-700 mb-3">Info Pembayaran</h4>
                            <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                                <p class="text-sm text-gray-500 mb-1">Bank Transfer</p>
                                <p class="text-xl font-bold text-indigo-900">{{ settings.bank_name }}</p>
                                <p class="text-2xl font-mono my-2 select-all">{{ settings.account_number }}</p>
                                <p class="text-sm text-gray-600">a.n {{ settings.account_holder }}</p>
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
                                        >
                                        <div class="p-3 border rounded-lg peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-colors">
                                            <div class="font-medium">Bulanan</div>
                                            <div class="text-sm text-gray-500">{{ formatCurrency(settings.membership_price_monthly || 25000) }}</div>
                                        </div>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input 
                                            type="radio" 
                                            v-model="form.plan_type" 
                                            value="yearly" 
                                            class="peer sr-only"
                                            @change="handlePlanChange"
                                        >
                                        <div class="p-3 border rounded-lg peer-checked:border-indigo-500 peer-checked:bg-indigo-50 transition-colors">
                                            <div class="font-medium">Tahunan</div>
                                            <div class="text-sm text-gray-500">{{ formatCurrency(settings.membership_price_yearly || 250000) }}</div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Form -->
                        <div>
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Transfer</label>
                                    <input 
                                        type="text" 
                                        :value="formatCurrency(form.amount)"
                                        disabled
                                        class="w-full bg-gray-100 border-gray-300 rounded-md shadow-sm"
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Bukti Transfer</label>
                                    <input 
                                        type="file" 
                                        @input="form.proof = ($event.target as HTMLInputElement).files?.[0] || null"
                                        accept="image/*"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                    >
                                    <p v-if="form.errors.proof" class="text-red-500 text-xs mt-1">{{ form.errors.proof }}</p>
                                </div>

                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Mengupload...' : 'Konfirmasi Pembayaran' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Pembayaran</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paket</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="item in history" :key="item.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(item.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(item.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                        {{ item.plan_type }} ({{ item.duration_months }} bln)
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize"
                                            :class="{
                                                'bg-yellow-100 text-yellow-800': item.status === 'pending',
                                                'bg-green-100 text-green-800': item.status === 'approved',
                                                'bg-red-100 text-red-800': item.status === 'rejected'
                                            }"
                                        >
                                            {{ item.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.rejection_reason || '-' }}
                                    </td>
                                </tr>
                                <tr v-if="history.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 text-sm">
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
