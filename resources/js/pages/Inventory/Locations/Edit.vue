<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import LocationForm from './Form.vue';

interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number | null;
    is_active: boolean;
}

interface Props {
    location: Location;
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Edit Lokasi Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <LocationForm :location="location" />
            </div>
        </div>
    </AppLayout>
</template>
