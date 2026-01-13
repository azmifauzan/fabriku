<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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

        <div class="py-6 px-6">
            <div class="max-w-4xl mx-auto">
                <LocationForm
                    :zones="zones"
                    :statuses="statuses"
                />
            </div>
        </div>
    </AppLayout>
</template>
