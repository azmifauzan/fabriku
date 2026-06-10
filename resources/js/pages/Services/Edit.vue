<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import Form from './Form.vue';

defineProps<{
    service: any;
    inventoryItems?: any[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Edit Layanan" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">Edit Layanan</h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">Perbarui informasi data layanan/jasa</p>
                    </div>
                    <Link
                        href="/services"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <Form :service="service" :inventory-items="inventoryItems" />
            </div>
        </div>
    </AppLayout>
</template>
