<template>
    <AppLayout>
        <Head title="Tambah Pesanan Baru" />

        <div class="py-6 px-6">
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
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SalesOrderForm from './Form.vue';

defineProps({
    customers: Array,
    inventoryItems: Array
});
</script>
