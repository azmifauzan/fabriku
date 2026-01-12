<template>
    <AppLayout>
        <Head title="Tambah Customer" />
        <CustomerForm />
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomerForm from './Form.vue';
</script>
