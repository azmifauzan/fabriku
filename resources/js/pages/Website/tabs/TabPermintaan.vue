<script setup lang="ts">
import Modal from '@/components/Modal.vue';
import { formatCurrency, formatDate } from '@/lib/formatters';
import { Link, router, useForm } from '@inertiajs/vue3';
import { CheckCircle2, ChevronRight, Clock, MessageSquare, Phone, XCircle } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Order = {
    id: number;
    order_number: string;
    status: string;
    payment_status: string;
    total_amount: string | number;
    created_at: string;
    customer: { name: string; phone: string };
};

type Lead = {
    id: number;
    name: string;
    phone: string;
    message: string | null;
    status: string;
    closed_reason?: string | null;
    service_id: number | null;
    sales_order_id: number | null;
    created_at: string;
    service?: { name: string };
};

const props = defineProps<{
    orders: Order[];
    leads: Lead[];
}>();

const leadFilter = ref<'all' | 'new' | 'contacted' | 'converted' | 'closed'>('all');
const closingLead = ref<Lead | null>(null);
const closeForm = useForm({
    status: 'closed',
    closed_reason: '',
});

const filteredLeads = computed(() => {
    if (leadFilter.value === 'all') return props.leads;
    return props.leads.filter((l) => l.status === leadFilter.value);
});

const orderStatusMap: Record<string, { label: string; class: string }> = {
    draft: { label: 'Draf', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' },
    confirmed: { label: 'Dikonfirmasi', class: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' },
    processing: { label: 'Diproses', class: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300' },
    shipped: { label: 'Dikirim', class: 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' },
    completed: { label: 'Selesai', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' },
    cancelled: { label: 'Dibatalkan', class: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' },
};

const paymentStatusMap: Record<string, { label: string; class: string }> = {
    unpaid: { label: 'Belum Bayar', class: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' },
    pending: { label: 'Menunggu', class: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-300' },
    partial: { label: 'Sebagian', class: 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300' },
    paid: { label: 'Lunas', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' },
    refunded: { label: 'Dikembalikan', class: 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300' },
};

const leadStatusMap: Record<string, { label: string; class: string }> = {
    new: { label: 'Baru', class: 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' },
    contacted: { label: 'Dihubungi', class: 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300' },
    converted: { label: 'Dikonversi', class: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' },
    closed: { label: 'Ditutup', class: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' },
};

function whatsappLink(phone: string, message: string) {
    const cleanPhone = phone.replace(/\D/g, '').replace(/^0/, '62');
    return `https://wa.me/${cleanPhone}?text=${encodeURIComponent(message)}`;
}

function markContacted(lead: Lead) {
    router.post(`/website/leads/${lead.id}/status`, { status: 'contacted' }, { preserveScroll: true });
}

function convertLead(lead: Lead) {
    router.post(`/website/leads/${lead.id}/convert`, {}, { preserveScroll: true });
}

function openCloseModal(lead: Lead) {
    closingLead.value = lead;
    closeForm.closed_reason = '';
}

function submitCloseLead() {
    if (!closingLead.value) return;
    closeForm.post(`/website/leads/${closingLead.value.id}/status`, {
        preserveScroll: true,
        onSuccess: () => {
            closingLead.value = null;
            closeForm.reset();
        },
    });
}
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Permintaan Masuk</h2>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Pantau pesanan produk dan prospek layanan yang dikirim melalui website Anda.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Pesanan Produk -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-900 dark:text-white">Pesanan Produk</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Checkout langsung dari toko online</p>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        {{ orders.length }} pesanan
                    </span>
                </div>

                <div v-if="orders.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-slate-500">
                    <Clock class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" />
                    <p class="text-sm">Belum ada pesanan dari website.</p>
                </div>

                <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li v-for="order in orders" :key="order.id" class="flex flex-col gap-2 py-3.5 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <Link :href="`/sales-orders/${order.id}`" class="font-semibold text-teal-700 hover:underline dark:text-teal-400">
                                    {{ order.order_number }}
                                </Link>
                                <span :class="['rounded px-2 py-0.5 text-xs font-medium', orderStatusMap[order.status]?.class ?? 'bg-slate-100 text-slate-700']">
                                    {{ orderStatusMap[order.status]?.label ?? order.status }}
                                </span>
                                <span :class="['rounded px-2 py-0.5 text-xs font-medium', paymentStatusMap[order.payment_status]?.class ?? 'bg-slate-100 text-slate-700']">
                                    {{ paymentStatusMap[order.payment_status]?.label ?? order.payment_status }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 dark:text-slate-400">
                                {{ order.customer?.name }} · {{ order.customer?.phone }}
                            </p>
                            <p class="text-xs text-slate-400">
                                {{ formatDate(order.created_at) }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end">
                            <span class="font-semibold text-slate-900 dark:text-white">
                                {{ formatCurrency(order.total_amount) }}
                            </span>
                            <a
                                v-if="order.customer?.phone"
                                :href="whatsappLink(order.customer.phone, `Halo ${order.customer.name}, kami dari toko ingin mengonfirmasi pesanan ${order.order_number}.`)"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                <Phone class="h-3.5 w-3.5 text-emerald-600" />
                                WhatsApp
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Prospek Layanan -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="font-semibold text-slate-900 dark:text-white">Prospek Layanan</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pertanyaan dan permintaan konsultasi</p>
                    </div>

                    <!-- Filter Status -->
                    <div class="flex items-center gap-1 text-xs">
                        <button
                            v-for="f in [
                                { key: 'all', label: 'Semua' },
                                { key: 'new', label: 'Baru' },
                                { key: 'contacted', label: 'Dihubungi' },
                                { key: 'converted', label: 'Dikonversi' },
                                { key: 'closed', label: 'Ditutup' },
                            ]"
                            :key="f.key"
                            type="button"
                            :class="[
                                'rounded-full px-2.5 py-1 font-medium transition-colors',
                                leadFilter === f.key
                                    ? 'bg-teal-700 text-white dark:bg-teal-600'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                            ]"
                            @click="leadFilter = f.key as any"
                        >
                            {{ f.label }}
                        </button>
                    </div>
                </div>

                <div v-if="filteredLeads.length === 0" class="flex flex-col items-center justify-center py-12 text-center text-slate-500">
                    <MessageSquare class="mb-2 h-8 w-8 text-slate-300 dark:text-slate-600" />
                    <p class="text-sm">Tidak ada prospek dengan filter ini.</p>
                </div>

                <ul v-else class="divide-y divide-slate-100 dark:divide-slate-800">
                    <li v-for="lead in filteredLeads" :key="lead.id" class="space-y-2 py-4 first:pt-0 last:pb-0">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <strong class="font-semibold text-slate-900 dark:text-white">{{ lead.name }}</strong>
                                    <span :class="['rounded px-2 py-0.5 text-xs font-medium', leadStatusMap[lead.status]?.class ?? 'bg-slate-100 text-slate-700']">
                                        {{ leadStatusMap[lead.status]?.label ?? lead.status }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400">
                                    {{ lead.service?.name || 'Pertanyaan Umum' }} · {{ lead.phone }}
                                </p>
                            </div>
                            <span class="text-xs text-slate-400">{{ formatDate(lead.created_at) }}</span>
                        </div>

                        <div v-if="lead.message" class="rounded-lg bg-slate-50 p-2.5 text-xs text-slate-700 dark:bg-slate-800/60 dark:text-slate-300">
                            "{{ lead.message }}"
                        </div>

                        <div v-if="lead.status === 'closed' && lead.closed_reason" class="text-xs text-slate-500 italic dark:text-slate-400">
                            Alasan ditutup: {{ lead.closed_reason }}
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <a
                                :href="whatsappLink(lead.phone, `Halo ${lead.name}, kami ingin menindaklanjuti permintaan Anda mengenai ${lead.service?.name || 'layanan kami'}.`)"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                <Phone class="h-3.5 w-3.5 text-emerald-600" />
                                Hubungi via WhatsApp
                            </a>

                            <button
                                v-if="lead.status === 'new'"
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                @click="markContacted(lead)"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5 text-blue-600" />
                                Tandai Dihubungi
                            </button>

                            <button
                                v-if="lead.status !== 'converted' && lead.service_id"
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg bg-teal-800 px-3 py-1.5 text-xs font-semibold text-white shadow-2xs hover:bg-teal-700 dark:bg-teal-700 dark:hover:bg-teal-600"
                                @click="convertLead(lead)"
                            >
                                Ubah Jadi Pesanan
                                <ChevronRight class="h-3.5 w-3.5" />
                            </button>

                            <button
                                v-if="lead.status !== 'converted' && lead.status !== 'closed'"
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30"
                                @click="openCloseModal(lead)"
                            >
                                <XCircle class="h-3.5 w-3.5" />
                                Tutup
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Modal Alasan Menutup Prospek -->
        <Modal :show="closingLead !== null" max-width="md" @close="closingLead = null">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Tutup Prospek</h3>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                    Beri alasan mengapa prospek ini ditutup (misal: belum butuh, nomor tidak aktif, dsb).
                </p>

                <form class="mt-4 space-y-4" @submit.prevent="submitCloseLead">
                    <div>
                        <label for="closed_reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Alasan Menutup <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="closed_reason"
                            v-model="closeForm.closed_reason"
                            required
                            rows="3"
                            maxlength="500"
                            placeholder="Tulis alasan singkat..."
                            class="mt-1 block w-full rounded-lg border border-slate-300 p-2.5 text-sm text-slate-900 shadow-2xs focus:border-teal-600 focus:ring-teal-600 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                        <p v-if="closeForm.errors.closed_reason" class="mt-1 text-xs text-rose-600">
                            {{ closeForm.errors.closed_reason }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="closingLead = null"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="closeForm.processing || !closeForm.closed_reason.trim()"
                            class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-rose-700 disabled:opacity-50"
                        >
                            Tutup Prospek
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </div>
</template>
