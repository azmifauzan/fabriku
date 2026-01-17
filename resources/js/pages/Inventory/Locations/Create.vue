<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import LocationForm from './Form.vue';

interface Props {
    zones: string[];
    statuses: Record<string, string>;
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Tambah Lokasi Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <LocationForm :zones="zones" :statuses="statuses" />
            </div>
        </div>
    </AppLayout>
</template>
