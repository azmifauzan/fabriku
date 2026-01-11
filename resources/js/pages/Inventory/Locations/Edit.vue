<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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

        <div class="py-6">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <LocationForm
                    :location="location"
                    :zones="zones"
                    :statuses="statuses"
                />
            </div>
        </div>
    </AppLayout>
</template>
