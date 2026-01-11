<template>
    <AppLayout>
        <Head :title="`Edit Customer: ${customer.name}`" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-4xl">
                <PageHeader
                    :title="`Edit Customer: ${customer.name}`"
                    :description="`Update data customer ${customer.code}`"
                    :back-link="{ href: `/customers/${customer.id}`, text: 'Kembali ke Detail' }"
                />

                <CustomerForm :customer="customer" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import CustomerForm from './Form.vue';

defineProps({
    customer: Object
});
</script>
