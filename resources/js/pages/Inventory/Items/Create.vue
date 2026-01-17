<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ItemForm from './Form.vue';

interface Location {
    id: number;
    name: string;
    zone: string;
    rack: string;
}

interface Pattern {
    id: number;
    name: string;
    code: string;
    product_type: string;
}

interface Props {
    locations: Location[];
    patterns: Pattern[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Tambah Item Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <ItemForm :locations="locations" :patterns="patterns" />
            </div>
        </div>
    </AppLayout>
</template>
