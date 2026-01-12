<template>
    <AppLayout>
        <Head :title="`Edit Customer: ${customer.name}`" />
        <CustomerForm :customer="customer" />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomerForm from './Form.vue';

defineProps({
    customer: Object
});
</script>
