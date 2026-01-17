<template>
    <AppLayout>
        <Head title="Tambah Customer" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <CustomerForm />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import CustomerForm from './Form.vue';
</script>
