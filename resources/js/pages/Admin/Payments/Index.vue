<script setup>
import { approve as approveAction, reject as rejectAction } from '@/actions/App/Http/Controllers/Admin/AdminPaymentController';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CheckCircle, Clock, Eye, Search, XCircle } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { ref } from 'vue';

const props = defineProps({
    payments: Object,
    filters: Object,
    stats: Object,
});

const { confirm, showSuccess, showError } = useSweetAlert();

// Filters
const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const planType = ref(props.filters?.plan_type || '');

const applyFilters = () => {
    router.get(
        '/admin/payments',
        {
            search: search.value,
            status: status.value,
            plan_type: planType.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    planType.value = '';
    applyFilters();
};

// Actions
const approving = ref(null);
const rejecting = ref(null);
const rejectionReason = ref('');
const showRejectModal = ref(false);
const selectedPayment = ref(null);
const showProofModal = ref(false);
const proofUrl = ref('');

const approve = async (payment) => {
    const result = await confirm(
        'Approve Pembayaran',
        `Approve pembayaran dari ${payment.tenant.name} sebesar ${formatCurrency(payment.amount)}?`,
        'Ya, Approve',
        'question',
        '#10b981',
    );

    if (!result.isConfirmed) return;

    approving.value = payment.id;
    const form = useForm({});

    form.post(approveAction(payment.id), {
        onSuccess: () => {
            showSuccess('Berhasil!', 'Pembayaran berhasil diapprove');
        },
        onError: () => {
            showError('Gagal!', 'Terjadi kesalahan saat approve pembayaran');
        },
        onFinish: () => (approving.value = null),
    });
};

const openRejectModal = async (payment) => {
    const result = await Swal.fire({
        title: 'Reject Pembayaran',
        text: `Reject pembayaran dari ${payment.tenant.name}?`,
        input: 'textarea',
        inputLabel: 'Alasan Penolakan',
        inputPlaceholder: 'Masukkan alasan penolakan...',
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan penolakan harus diisi!';
            }
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Reject',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    });

    if (result.isConfirmed) {
        rejecting.value = payment.id;
        const form = useForm({
            reason: result.value,
        });

        form.post(rejectAction(payment.id), {
            onSuccess: () => {
                showSuccess('Berhasil!', 'Pembayaran berhasil direject');
            },
            onError: () => {
                showError('Gagal!', 'Terjadi kesalahan saat reject pembayaran');
            },
            onFinish: () => (rejecting.value = null),
        });
    }
};

const reject = () => {
    if (!selectedPayment.value || !rejectionReason.value) return;

    rejecting.value = selectedPayment.value.id;
    const form = useForm({
        reason: rejectionReason.value,
    });

    form.post(rejectAction(selectedPayment.value.id), {
        onSuccess: () => {
            showRejectModal.value = false;
            selectedPayment.value = null;
        },
        onFinish: () => (rejecting.value = null),
    });
};

const viewProof = (payment) => {
    proofUrl.value = payment.proof_url;
    showProofModal.value = true;
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
        hour: '2-digit',
        minute: '2-digit',
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
</script>

<template>
    <Head title="Subscription Payments" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Requests</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola pembayaran subscription dan membership tenant</p>
        </div>

        <!-- Stats Cards -->
        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ stats?.pending_count || 0 }}</p>
                    </div>
                    <div class="rounded-full bg-yellow-100 p-3 dark:bg-yellow-900/30">
                        <Clock class="h-6 w-6 text-yellow-600" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">{{ formatCurrency(stats?.pending_amount || 0) }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Approved (Bulan Ini)</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats?.approved_this_month || 0 }}</p>
                    </div>
                    <div class="rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                        <CheckCircle class="h-6 w-6 text-green-600" />
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">{{ formatCurrency(stats?.approved_amount_this_month || 0) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <Search class="absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 transform text-gray-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Cari nama tenant..."
                            class="w-full rounded-lg border border-gray-300 bg-white py-2 pr-4 pl-10 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <!-- Plan Type Filter -->
                <div>
                    <select
                        v-model="planType"
                        @change="applyFilters"
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 focus:ring-2 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">Semua Paket</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
            </div>

            <div v-if="search || status || planType" class="mt-3 flex justify-end">
                <button @click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Paket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Nominal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Bukti</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ formatDate(payment.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ payment.tenant?.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 capitalize dark:bg-purple-900/30 dark:text-purple-400"
                                >
                                    {{ payment.plan_type }} ({{ payment.duration_months }} bln)
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                {{ formatCurrency(payment.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    v-if="payment.proof_url"
                                    @click="viewProof(payment)"
                                    class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-900"
                                >
                                    <Eye class="mr-1 h-4 w-4" />
                                    Lihat
                                </button>
                                <span v-else class="text-sm text-gray-400">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="rounded-full px-2 py-1 text-xs font-medium capitalize" :class="getStatusColor(payment.status)">
                                    {{ payment.status }}
                                </span>
                                <p
                                    v-if="payment.rejection_reason"
                                    class="mt-1 max-w-32 truncate text-xs text-red-500"
                                    :title="payment.rejection_reason"
                                >
                                    {{ payment.rejection_reason }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ payment.admin?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                <div v-if="payment.status === 'pending'" class="flex gap-2">
                                    <button
                                        @click="approve(payment)"
                                        :disabled="approving === payment.id"
                                        class="rounded bg-green-600 px-3 py-1 text-xs font-medium text-white hover:bg-green-700 disabled:opacity-50"
                                    >
                                        {{ approving === payment.id ? '...' : 'Setujui' }}
                                    </button>
                                    <button
                                        @click="openRejectModal(payment)"
                                        class="rounded bg-red-600 px-3 py-1 text-xs font-medium text-white hover:bg-red-700"
                                    >
                                        Tolak
                                    </button>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">Tidak ada data pembayaran.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">Menampilkan {{ payments.from }} - {{ payments.to }} dari {{ payments.total }} data</p>
                    <div class="flex gap-2">
                        <button
                            v-for="link in payments.links"
                            :key="link.label"
                            @click="link.url && router.get(link.url)"
                            :disabled="!link.url"
                            :class="[
                                'rounded px-3 py-1 text-sm',
                                link.active
                                    ? 'bg-purple-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300',
                                !link.url && 'cursor-not-allowed opacity-50',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity" @click="showRejectModal = false"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
                            >
                                <XCircle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 w-full text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">Tolak Pembayaran</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Berikan alasan penolakan pembayaran dari <strong>{{ selectedPayment?.tenant?.name }}</strong
                                        >.
                                    </p>
                                    <textarea
                                        v-model="rejectionReason"
                                        class="mt-3 w-full rounded-md border border-gray-300 bg-white p-2 text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                        rows="3"
                                        placeholder="Contoh: Bukti transfer tidak valid, nominal tidak sesuai..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-700">
                        <button
                            type="button"
                            @click="reject"
                            :disabled="!rejectionReason || rejecting === selectedPayment?.id"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:outline-none disabled:opacity-50 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ rejecting === selectedPayment?.id ? 'Memproses...' : 'Tolak Pembayaran' }}
                        </button>
                        <button
                            type="button"
                            @click="showRejectModal = false"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Modal -->
        <div v-if="showProofModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="proof-modal" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="bg-opacity-75 fixed inset-0 bg-gray-500 transition-opacity" @click="showProofModal = false"></div>

                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle dark:bg-gray-800"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 dark:bg-gray-800">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bukti Pembayaran</h3>
                            <button @click="showProofModal = false" class="text-gray-400 hover:text-gray-500">
                                <XCircle class="h-6 w-6" />
                            </button>
                        </div>
                        <div class="flex justify-center">
                            <img :src="proofUrl" alt="Bukti Pembayaran" class="max-h-96 max-w-full rounded-lg object-contain" />
                        </div>
                        <div class="mt-4 flex justify-center">
                            <a :href="proofUrl" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-500"> Buka di tab baru </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
