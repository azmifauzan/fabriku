<template>
    <AppLayout>
        <Head title="Tambah Pesanan Baru" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Tambah Pesanan Baru"
                    description="Buat pesanan penjualan baru"
                    :back-link="{ href: '/sales-orders', text: 'Kembali ke Daftar Pesanan' }"
                />

                <SalesOrderForm :customers="customers" :inventory-items="inventoryItems" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import SalesOrderForm from './Form.vue';

defineProps({
    customers: Array,
    inventoryItems: Array,
});
</script>
