<template>
    <div class="mx-auto max-w-4xl bg-white p-4 sm:p-8 print:p-0">
        <!-- Header -->
        <div class="mb-8 flex flex-col gap-4 border-b pb-8 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="mb-1 text-3xl font-bold text-gray-900">SURAT JALAN</h1>
                <p class="text-gray-500">No. Order: {{ salesOrder.order_number }}</p>
                <p class="text-gray-500" v-if="salesOrder.invoice_number">No. Invoice: {{ salesOrder.invoice_number }}</p>
                <p class="text-gray-500">Tanggal: {{ new Date(salesOrder.order_date).toLocaleDateString('id-ID') }}</p>
                <p class="text-gray-500" v-if="salesOrder.resi_number">No. Resi: {{ salesOrder.resi_number }}</p>
            </div>
            <div class="sm:text-right">
                <img v-if="settings?.company_logo" :src="settings.company_logo" alt="Company Logo" class="mb-4 h-16 object-contain sm:ml-auto">
                <h2 class="mb-2 text-xl font-bold text-gray-900">{{ settings?.company_name || 'FABRIKU' }}</h2>
                <p class="max-w-xs text-sm text-gray-500 sm:ml-auto">
                    {{ settings?.company_address }}<br>
                    <span v-if="settings?.company_phone">{{ settings?.company_phone }}<br></span>
                    <span v-if="settings?.company_email">{{ settings?.company_email }}</span>
                </p>
            </div>
        </div>

        <!-- Addresses -->
        <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:justify-between">
            <div>
                <h3 class="mb-2 text-xs font-semibold uppercase text-gray-600">Kepada:</h3>
                <p class="font-bold text-gray-900">{{ salesOrder.customer.name }}</p>
                <p class="text-sm text-gray-600">{{ salesOrder.customer.address || '-' }}</p>
                <p class="text-sm text-gray-600">{{ salesOrder.customer.phone || '-' }}</p>
            </div>
            <div class="sm:text-right" v-if="salesOrder.shipping_address">
                <h3 class="mb-2 text-xs font-semibold uppercase text-gray-600">Alamat Pengiriman:</h3>
                <p class="max-w-xs whitespace-pre-wrap text-sm text-gray-600 sm:ml-auto">{{ salesOrder.shipping_address }}</p>
            </div>
        </div>

        <!-- Mobile: item cards -->
        <div class="mb-8 space-y-3 sm:hidden">
            <div class="border-b-2 border-gray-300 pb-2 text-xs font-bold uppercase text-gray-700">Daftar Barang</div>
            <div v-for="(item, index) in salesOrder.items" :key="item.id" class="border-b border-gray-200 pb-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900">{{ index + 1 }}. {{ item.inventory_item?.product_name || item.inventory_item?.pattern?.name }}</p>
                        <p class="text-xs text-gray-500">{{ item.inventory_item?.sku }}</p>
                    </div>
                    <p class="shrink-0 font-semibold text-gray-900">{{ item.quantity }} pcs</p>
                </div>
            </div>
        </div>

        <!-- Desktop: table -->
        <table class="mb-8 hidden w-full sm:table">
            <thead>
                <tr class="border-b-2 border-gray-300">
                    <th class="py-3 text-left text-sm font-bold uppercase text-gray-700">No</th>
                    <th class="py-3 text-left text-sm font-bold uppercase text-gray-700">Nama Barang</th>
                    <th class="py-3 text-left text-sm font-bold uppercase text-gray-700">SKU</th>
                    <th class="py-3 text-right text-sm font-bold uppercase text-gray-700">Qty</th>
                    <th class="py-3 pl-6 text-left text-sm font-bold uppercase text-gray-700">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in salesOrder.items" :key="item.id" class="border-b border-gray-200">
                    <td class="py-4 text-sm text-gray-900">{{ index + 1 }}</td>
                    <td class="py-4 text-sm text-gray-900">
                        <p class="font-medium">{{ item.inventory_item?.product_name || item.inventory_item?.pattern?.name }}</p>
                    </td>
                    <td class="py-4 text-sm text-gray-500">{{ item.inventory_item?.sku }}</td>
                    <td class="py-4 text-right text-sm font-medium text-gray-900">{{ item.quantity }} pcs</td>
                    <td class="py-4 pl-6 text-sm text-gray-500">{{ item.notes || '' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="mb-8 flex justify-end">
            <div class="w-full sm:w-1/3">
                <div class="flex justify-between border-t-2 border-gray-300 py-3">
                    <span class="font-bold text-gray-900">Total Qty</span>
                    <span class="font-bold text-gray-900">{{ salesOrder.items.reduce((sum, item) => sum + item.quantity, 0) }} pcs</span>
                </div>
            </div>
        </div>

        <!-- Signature Area -->
        <div class="mb-8 grid grid-cols-3 gap-8 text-center text-sm text-gray-600">
            <div>
                <p class="mb-16">Pengirim,</p>
                <div class="border-t border-gray-400 pt-2">
                    <p class="font-medium text-gray-900">{{ settings?.company_name || 'Pengirim' }}</p>
                </div>
            </div>
            <div>
                <p class="mb-16">Diterima oleh,</p>
                <div class="border-t border-gray-400 pt-2">
                    <p class="font-medium text-gray-900">{{ salesOrder.customer.name }}</p>
                </div>
            </div>
            <div>
                <p class="mb-16">Kurir / Ekspedisi,</p>
                <div class="border-t border-gray-400 pt-2">
                    <p class="text-gray-500">{{ salesOrder.resi_number || '________________' }}</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="border-t border-gray-200 pt-8 text-center text-sm text-gray-500">
            <p class="mb-2 whitespace-pre-wrap">{{ settings?.invoice_footer_text || 'Terima kasih atas kepercayaan Anda.' }}</p>
            <p v-if="salesOrder.notes" class="italic">Catatan: {{ salesOrder.notes }}</p>
        </div>

        <!-- Print Button (Hidden when printing) -->
        <div class="fixed right-4 bottom-4 sm:right-8 sm:bottom-8 print:hidden">
            <button
                @click="printPage"
                class="flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-3 text-sm text-white shadow-lg transition-colors hover:bg-indigo-700 sm:px-6"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Surat Jalan
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    salesOrder: Object,
    settings: Object,
});

function printPage() {
    window.print();
}
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
