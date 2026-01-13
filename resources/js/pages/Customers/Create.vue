<template>
    <AppLayout>
        <Head title="Tambah Customer" />
        
        <div class="py-6 px-6">
            <div class="max-w-4xl mx-auto">
                <CustomerForm />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CustomerForm from './Form.vue';
</script>
