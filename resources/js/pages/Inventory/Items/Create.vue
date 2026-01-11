<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
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

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <ItemForm
                    :locations="locations"
                    :patterns="patterns"
                />
            </div>
        </div>
    </AppLayout>
</template>
