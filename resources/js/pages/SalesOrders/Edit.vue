<template>
    <AppLayout>
        <Head :title="`Edit Pesanan: ${salesOrder.order_number}`" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="`Edit Pesanan: ${salesOrder.order_number}`"
                    :description="`Update pesanan ${salesOrder.order_number}`"
                    :back-link="{ href: `/sales-orders/${salesOrder.id}`, text: 'Kembali ke Detail' }"
                />

                <SalesOrderForm 
                    :sales-order="salesOrder" 
                    :customers="customers" 
                    :inventory-items="inventoryItems" 
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SalesOrderForm from './Form.vue';

defineProps({
    salesOrder: Object,
    customers: Array,
    inventoryItems: Array
});
</script>
