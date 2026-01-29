<template>
    <div class="bg-white p-8 max-w-4xl mx-auto print:p-0">
        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-8 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">INVOICE</h1>
                <p class="text-gray-500" v-if="salesOrder.invoice_number">Invoice #: {{ salesOrder.invoice_number }}</p>
                <p class="text-gray-500" v-else>Order #: {{ salesOrder.order_number }}</p>
                <p class="text-gray-500">Date: {{ new Date(salesOrder.order_date).toLocaleDateString('id-ID') }}</p>
            </div>
            <div class="text-right">
                <img v-if="settings?.company_logo" :src="settings.company_logo" alt="Company Logo" class="h-16 mb-4 ml-auto object-contain">
                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ settings?.company_name || 'FABRIKU' }}</h2>
                <p class="text-gray-500 text-sm max-w-xs">
                    {{ settings?.company_address }}<br>
                    <span v-if="settings?.company_phone">{{ settings?.company_phone }}<br></span>
                    <span v-if="settings?.company_email">{{ settings?.company_email }}</span>
                </p>
            </div>
        </div>

        <!-- Addresses -->
        <div class="flex justify-between mb-8">
            <div>
                <h3 class="text-gray-600 uppercase text-xs font-semibold mb-2">Bill To:</h3>
                <p class="font-bold text-gray-900">{{ salesOrder.customer.name }}</p>
                <p class="text-gray-600 text-sm">{{ salesOrder.customer.address || '-' }}</p>
                <p class="text-gray-600 text-sm">{{ salesOrder.customer.phone || '-' }}</p>
            </div>
            <div class="text-right" v-if="salesOrder.shipping_address">
                <h3 class="text-gray-600 uppercase text-xs font-semibold mb-2">Ship To:</h3>
                <p class="text-gray-600 text-sm whitespace-pre-wrap max-w-xs ml-auto">{{ salesOrder.shipping_address }}</p>
                <p class="text-gray-600 text-sm mt-2" v-if="salesOrder.resi_number">Resi: {{ salesOrder.resi_number }}</p>
            </div>
        </div>

        <!-- Table -->
        <table class="w-full mb-8">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="text-left py-3 text-sm font-bold text-gray-700 uppercase">Item</th>
                    <th class="text-right py-3 text-sm font-bold text-gray-700 uppercase">Qty</th>
                    <th class="text-right py-3 text-sm font-bold text-gray-700 uppercase">Price</th>
                    <th class="text-right py-3 text-sm font-bold text-gray-700 uppercase">Disc</th>
                    <th class="text-right py-3 text-sm font-bold text-gray-700 uppercase">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in salesOrder.items" :key="item.id" class="border-b border-gray-200">
                    <td class="py-4 text-sm text-gray-900">
                        <p class="font-medium">{{ item.inventory_item?.product_name || item.inventory_item?.pattern?.name }}</p>
                        <p class="text-gray-500 text-xs">{{ item.inventory_item?.sku }}</p>
                    </td>
                    <td class="py-4 text-right text-sm text-gray-900">{{ item.quantity }}</td>
                    <td class="py-4 text-right text-sm text-gray-900">Rp {{ Number(item.unit_price).toLocaleString('id-ID') }}</td>
                    <td class="py-4 text-right text-sm text-gray-900">Rp {{ Number(item.discount_amount).toLocaleString('id-ID') }}</td>
                    <td class="py-4 text-right text-sm text-gray-900 font-medium">Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end mb-8">
            <div class="w-1/2">
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium">Rp {{ Number(salesOrder.subtotal).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100" v-if="salesOrder.discount_amount > 0">
                    <span class="text-gray-600">Discount ({{ salesOrder.discount_percentage }}%)</span>
                    <span class="font-medium text-red-600">- Rp {{ Number(salesOrder.discount_amount).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-100" v-if="salesOrder.tax_amount > 0">
                    <span class="text-gray-600">Tax</span>
                    <span class="font-medium">Rp {{ Number(salesOrder.tax_amount).toLocaleString('id-ID') }}</span>
                </div>
                <div class="flex justify-between py-4 border-t-2 border-gray-300">
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
        <div class="fixed bottom-8 right-8 print:hidden">
            <button 
                @click="print" 
                class="bg-indigo-600 text-white px-6 py-3 rounded-full shadow-lg hover:bg-indigo-700 transition-colors flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Invoice
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';

const props = defineProps({
    salesOrder: Object,
    settings: Object
});

function print() {
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
