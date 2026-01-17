<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import LocationForm from './Form.vue';

interface Location {
    id: number;
    name: string;
    zone: string;
    rack: string;
    description?: string;
    capacity?: number;
    status: string;
}

interface Props {
    location: Location;
    zones: string[];
    statuses: Record<string, string>;
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Edit Lokasi Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <LocationForm :location="location" :zones="zones" :statuses="statuses" />
            </div>
        </div>
    </AppLayout>
</template>
