<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    payments: Array,
})

const approving = ref(null)
const rejecting = ref(null)
const rejectionReason = ref('')
const showRejectModal = ref(false)
const selectedPayment = ref(null)

const approve = (payment) => {
    if (!confirm('Are you sure you want to approve this payment?')) return

    approving.value = payment.id
    const form = useForm({})
    
    form.post(route('admin.payments.approve', payment.id), {
        onFinish: () => approving.value = null
    })
}

const openRejectModal = (payment) => {
    selectedPayment.value = payment
    rejectionReason.value = ''
    showRejectModal.value = true
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
</script>

<template>
    <Head title="Subscription Payments" />

    <AdminLayout>
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Payment Requests</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage subscription payments and approvals</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Proof</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ formatDate(payment.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ payment.tenant?.name }}</div>
                                <div class="text-xs text-gray-500">{{ payment.tenant?.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 capitalize">
                                {{ payment.plan_type }} ({{ payment.duration_months }} mo)
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ formatCurrency(payment.amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a 
                                    :href="`/storage/${payment.proof_path}`" 
                                    target="_blank"
                                    class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                >
                                    View Proof
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': payment.status === 'pending',
                                        'bg-green-100 text-green-800': payment.status === 'approved',
                                        'bg-red-100 text-red-800': payment.status === 'rejected'
                                    }"
                                >
                                    {{ payment.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div v-if="payment.status === 'pending'" class="flex gap-2">
                                    <button 
                                        @click="approve(payment)"
                                        :disabled="approving === payment.id"
                                        class="text-green-600 hover:text-green-900"
                                    >
                                        {{ approving === payment.id ? '...' : 'Approve' }}
                                    </button>
                                    <button 
                                        @click="openRejectModal(payment)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Reject
                                    </button>
                                </div>
                                <span v-else class="text-gray-400">
                                    -
                                </span>
                            </td>
                        </tr>
                        <tr v-if="payments.length === 0">
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                No payment requests found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="showRejectModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRejectModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Reject Payment
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Please provide a reason for rejecting this payment.
                                    </p>
                                    <textarea 
                                        v-model="rejectionReason"
                                        class="mt-2 w-full border border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        rows="3"
                                        placeholder="Reason for rejection..."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            @click="reject"
                            :disabled="!rejectionReason || rejecting === selectedPayment?.id"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            {{ rejecting === selectedPayment?.id ? 'Rejecting...' : 'Reject Payment' }}
                        </button>
                        <button 
                            type="button" 
                            @click="showRejectModal = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
