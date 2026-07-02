<template>
    <div class="mx-auto max-w-4xl bg-white p-4 sm:p-8 print:p-0">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 border-b pb-8 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="mb-2 text-3xl font-bold text-gray-900">INVOICE</h1>
                <p class="text-gray-500" v-if="salesOrder.invoice_number">Invoice #: {{ salesOrder.invoice_number }}</p>
                <p class="text-gray-500" v-else>Order #: {{ salesOrder.order_number }}</p>
                <p class="text-gray-500">Date: {{ new Date(salesOrder.order_date).toLocaleDateString('id-ID') }}</p>
                <p class="text-gray-500" v-if="salesOrder.payment_due_date">
                    Due Date: {{ new Date(salesOrder.payment_due_date).toLocaleDateString('id-ID') }}
                </p>
            </div>
            <div class="sm:text-right">
                <img v-if="settings?.company_logo" :src="settings.company_logo" alt="Company Logo" class="mb-4 h-16 object-contain sm:ml-auto" />
                <h2 class="mb-2 text-xl font-bold text-gray-900">{{ settings?.company_name || 'FABRIKU' }}</h2>
                <p class="max-w-xs text-sm text-gray-500 sm:ml-auto">
                    {{ settings?.company_address }}<br />
                    <span v-if="settings?.company_phone">{{ settings?.company_phone }}<br /></span>
                    <span v-if="settings?.company_email">{{ settings?.company_email }}</span>
                </p>
            </div>
        </div>

        <!-- Addresses -->
        <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:justify-between">
            <div>
                <h3 class="mb-2 text-xs font-semibold text-gray-600 uppercase">Bill To:</h3>
                <p class="font-bold text-gray-900">{{ salesOrder.customer.name }}</p>
                <p class="text-sm text-gray-600">{{ salesOrder.customer.address || '-' }}</p>
                <p class="text-sm text-gray-600">{{ salesOrder.customer.phone || '-' }}</p>
            </div>
            <div class="sm:text-right" v-if="salesOrder.shipping_address">
                <h3 class="mb-2 text-xs font-semibold text-gray-600 uppercase">Ship To:</h3>
                <p class="max-w-xs text-sm whitespace-pre-wrap text-gray-600 sm:ml-auto">{{ salesOrder.shipping_address }}</p>
                <p class="mt-2 text-sm text-gray-600" v-if="salesOrder.resi_number">Resi: {{ salesOrder.resi_number }}</p>
            </div>
        </div>

        <!-- Mobile: item cards -->
        <div class="mb-8 space-y-3 sm:hidden">
            <div class="border-b-2 border-gray-300 pb-2 text-xs font-bold text-gray-700 uppercase">Item Pesanan</div>
            <div v-for="item in salesOrder.items" :key="item.id" class="border-b border-gray-200 pb-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900">
                            {{ item.inventory_item?.product_name || item.inventory_item?.pattern?.name || item.service?.name }}
                        </p>
                        <p class="text-xs text-gray-500">{{ item.inventory_item?.sku || item.service?.code }}</p>
                    </div>
                    <p class="shrink-0 font-semibold text-gray-900">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</p>
                </div>
                <div class="mt-1 flex gap-4 text-xs text-gray-500">
                    <span>Qty: {{ item.quantity }}</span>
                    <span>Harga: Rp {{ Number(item.unit_price).toLocaleString('id-ID') }}</span>
                    <span v-if="item.discount_amount > 0">Disc: Rp {{ Number(item.discount_amount).toLocaleString('id-ID') }}</span>
                </div>
            </div>
        </div>

        <!-- Desktop: table -->
        <table class="mb-8 hidden w-full sm:table">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="py-3 text-left text-sm font-bold text-gray-700 uppercase">Item</th>
                    <th class="py-3 text-right text-sm font-bold text-gray-700 uppercase">Qty</th>
                    <th class="py-3 text-right text-sm font-bold text-gray-700 uppercase">Price</th>
                    <th class="py-3 text-right text-sm font-bold text-gray-700 uppercase">Disc</th>
                    <th class="py-3 text-right text-sm font-bold text-gray-700 uppercase">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in salesOrder.items" :key="item.id" class="border-b border-gray-200">
                    <td class="py-4 text-sm text-gray-900">
                        <p class="font-medium">{{ item.inventory_item?.product_name || item.inventory_item?.pattern?.name || item.service?.name }}</p>
                        <p class="text-xs text-gray-500">{{ item.inventory_item?.sku || item.service?.code }}</p>
                    </td>
                    <td class="py-4 text-right text-sm text-gray-900">{{ item.quantity }}</td>
                    <td class="py-4 text-right text-sm text-gray-900">Rp {{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                    <td class="py-4 text-right text-sm text-gray-900">Rp {{ Number(item.discount_amount).toLocaleString('id-ID') }}</td>
                    <td class="py-4 text-right text-sm font-medium text-gray-900">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="mb-8 flex justify-end">
            <div class="w-full sm:w-1/2">
                <div class="flex justify-between border-b border-gray-100 py-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium">Rp {{ Number(salesOrder.subtotal).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 py-2" v-if="salesOrder.discount_amount > 0">
                    <span class="text-gray-600">Discount ({{ salesOrder.discount_percentage }}%)</span>
                    <span class="font-medium text-red-600">- Rp {{ Number(salesOrder.discount_amount).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 py-2" v-if="salesOrder.tax_amount > 0">
                    <span class="text-gray-600">Tax</span>
                    <span class="font-medium">Rp {{ Number(salesOrder.tax_amount).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-100 py-2" v-if="salesOrder.shipping_cost > 0">
                    <span class="text-gray-600">Shipping Cost</span>
                    <span class="font-medium">Rp {{ Number(salesOrder.shipping_cost).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between border-t-2 border-gray-300 py-4">
                    <span class="text-lg font-bold text-gray-900">Total</span>
                    <span class="text-lg font-bold text-gray-900">Rp {{ Number(salesOrder.total_amount).toLocaleString('id-ID') }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
            <p class="mb-2 whitespace-pre-wrap">{{ settings?.invoice_footer_text || 'Thank you for your business!' }}</p>
            <p v-if="salesOrder.notes" class="italic">Note: {{ salesOrder.notes }}</p>
        </div>

        <!-- Print Button (Hidden when printing) -->
        <div class="fixed right-4 bottom-4 sm:right-8 sm:bottom-8 print:hidden">
            <button
                @click="printInvoice"
                class="flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-3 text-sm text-white shadow-lg transition-colors hover:bg-indigo-700 sm:px-6"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Print Invoice
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';

const props = defineProps({
    salesOrder: Object,
    settings: Object,
});

function printInvoice() {
    window.print();
}

onMounted(() => {
    // Optional: auto-print on load
    // window.print();
});
</script>

<style>
@media print {
    body {
        background-color: white;
    }
    @page {
        margin: 0;
    }
    .print\:hidden {
        display: none;
    }
    .print\:p-0 {
        padding: 0;
    }
}
</style>
