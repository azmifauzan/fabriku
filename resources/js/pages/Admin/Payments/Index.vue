<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ref, computed } from 'vue'
import { Search, Clock, CheckCircle, XCircle, Eye } from 'lucide-vue-next'
import { useSweetAlert } from '@/composables/useSweetAlert'
import Swal from 'sweetalert2'

const props = defineProps({
    payments: Object,
    filters: Object,
    stats: Object,
})

const { confirm, showSuccess, showError } = useSweetAlert()

// Filters
const search = ref(props.filters?.search || '')
const status = ref(props.filters?.status || '')
const planType = ref(props.filters?.plan_type || '')

const applyFilters = () => {
    router.get('/admin/payments', {
        search: search.value,
        status: status.value,
        plan_type: planType.value,
    }, {
        preserveState: true,
        replace: true,
    })
}

const clearFilters = () => {
    search.value = ''
    status.value = ''
    planType.value = ''
    applyFilters()
}

// Actions
const approving = ref(null)
const rejecting = ref(null)
const rejectionReason = ref('')
const showRejectModal = ref(false)
const selectedPayment = ref(null)
const showProofModal = ref(false)
const proofUrl = ref('')

const approve = async (payment) => {
    const result = await confirm(
        'Approve Pembayaran',
        `Approve pembayaran dari ${payment.tenant.name} sebesar ${formatCurrency(payment.amount)}?`,
        'Ya, Approve',
        'question',
        '#10b981'
    )

    if (!result.isConfirmed) return

    approving.value = payment.id
    const form = useForm({})
    
    form.post(route('admin.payments.approve', payment.id), {
        onSuccess: () => {
            showSuccess('Berhasil!', 'Pembayaran berhasil diapprove')
        },
        onError: () => {
            showError('Gagal!', 'Terjadi kesalahan saat approve pembayaran')
        },
        onFinish: () => approving.value = null
    })
}

const openRejectModal = async (payment) => {
    const result = await Swal.fire({
        title: 'Reject Pembayaran',
        text: `Reject pembayaran dari ${payment.tenant.name}?`,
        input: 'textarea',
        inputLabel: 'Alasan Penolakan',
        inputPlaceholder: 'Masukkan alasan penolakan...',
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan penolakan harus diisi!'
            }
        },
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Reject',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    })

    if (result.isConfirmed) {
        rejecting.value = payment.id
        const form = useForm({
            reason: result.value
        })
        
        form.post(route('admin.payments.reject', payment.id), {
            onSuccess: () => {
                showSuccess('Berhasil!', 'Pembayaran berhasil direject')
            },
            onError: () => {
                showError('Gagal!', 'Terjadi kesalahan saat reject pembayaran')
            },
            onFinish: () => rejecting.value = null
        })
    }
}

const reject = () => {
    if (!selectedPayment.value || !rejectionReason.value) return

    rejecting.value = selectedPayment.value.id
    const form = useForm({
        reason: rejectionReason.value
    })
    
    form.post(route('admin.payments.reject', selectedPayment.value.id), {
        onSuccess: () => {
            showRejectModal.value = false
            selectedPayment.value = null
        },
        onFinish: () => rejecting.value = null
    })
}

const viewProof = (payment) => {
    proofUrl.value = payment.proof_url
    showProofModal.value = true
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(amount)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
    }
    return colors[status] || colors.pending
}
</script>

<template>
    <Head title="Subscription Payments" />

    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Requests</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Kelola pembayaran subscription dan membership tenant
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ stats?.pending_count || 0 }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                        <Clock class="w-6 h-6 text-yellow-600" />
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ formatCurrency(stats?.pending_amount || 0) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Approved (Bulan Ini)</p>
                        <p class="text-2xl font-bold text-green-600">{{ stats?.approved_this_month || 0 }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <CheckCircle class="w-6 h-6 text-green-600" />
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">{{ formatCurrency(stats?.approved_amount_this_month || 0) }}</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Cari nama tenant..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                        />
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
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
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-purple-500"
                    >
                        <option value="">Semua Paket</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
            </div>

            <div v-if="search || status || planType" class="mt-3 flex justify-end">
                <button
                    @click="clearFilters"
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Paket</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nominal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bukti</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Admin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="payment in payments.data" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(payment.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ payment.tenant?.name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400 capitalize">
                                    {{ payment.plan_type }} ({{ payment.duration_months }} bln)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(payment.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button 
                                    v-if="payment.proof_url"
                                    @click="viewProof(payment)"
                                    class="inline-flex items-center text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    <Eye class="w-4 h-4 mr-1" />
                                    Lihat
                                </button>
                                <span v-else class="text-gray-400 text-sm">-</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                                    :class="getStatusColor(payment.status)"
                                >
                                    {{ payment.status }}
                                </span>
                                <p v-if="payment.rejection_reason" class="text-xs text-red-500 mt-1 max-w-32 truncate" :title="payment.rejection_reason">
                                    {{ payment.rejection_reason }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ payment.admin?.name || '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div v-if="payment.status === 'pending'" class="flex gap-2">
                                    <button 
                                        @click="approve(payment)"
                                        :disabled="approving === payment.id"
                                        class="px-3 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700 disabled:opacity-50"
                                    >
                                        {{ approving === payment.id ? '...' : 'Setujui' }}
                                    </button>
                                    <button 
                                        @click="openRejectModal(payment)"
                                        class="px-3 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700"
                                    >
                                        Tolak
                                    </button>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                        </tr>
                        <tr v-if="payments.data.length === 0">
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data pembayaran.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="payments.last_page > 1" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Menampilkan {{ payments.from }} - {{ payments.to }} dari {{ payments.total }} data
                    </p>
                    <div class="flex gap-2">
                        <button
                            v-for="link in payments.links"
                            :key="link.label"
                            @click="link.url && router.get(link.url)"
                            :disabled="!link.url"
                            :class="[
                                'px-3 py-1 text-sm rounded',
                                link.active
                                    ? 'bg-purple-600 text-white'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <XCircle class="h-6 w-6 text-red-600" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    Tolak Pembayaran
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Berikan alasan penolakan pembayaran dari <strong>{{ selectedPayment?.tenant?.name }}</strong>.
                                    </p>
                                    <textarea 
                                        v-model="rejectionReason"
                                        class="mt-3 w-full border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-indigo-500 focus:border-indigo-500"
                                        rows="3"
                                        placeholder="Contoh: Bukti transfer tidak valid, nominal tidak sesuai..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            @click="reject"
                            :disabled="!rejectionReason || rejecting === selectedPayment?.id"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            {{ rejecting === selectedPayment?.id ? 'Memproses...' : 'Tolak Pembayaran' }}
                        </button>
                        <button 
                            type="button" 
                            @click="showRejectModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Proof Modal -->
        <div v-if="showProofModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="proof-modal" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showProofModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Bukti Pembayaran</h3>
                            <button @click="showProofModal = false" class="text-gray-400 hover:text-gray-500">
                                <XCircle class="w-6 h-6" />
                            </button>
                        </div>
                        <div class="flex justify-center">
                            <img :src="proofUrl" alt="Bukti Pembayaran" class="max-w-full max-h-96 object-contain rounded-lg" />
                        </div>
                        <div class="mt-4 flex justify-center">
                            <a 
                                :href="proofUrl" 
                                target="_blank" 
                                class="text-indigo-600 hover:text-indigo-500 text-sm"
                            >
                                Buka di tab baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
