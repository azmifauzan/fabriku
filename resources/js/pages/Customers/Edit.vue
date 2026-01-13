<template>
    <AppLayout>
        <Head :title="`Edit Customer: ${customer.name}`" />
        
        <div class="py-6 px-6">
            <div class="max-w-4xl mx-auto">
                <CustomerForm :customer="customer" />
            </div>
        </div>
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
