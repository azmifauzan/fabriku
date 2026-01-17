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

interface Item {
    id: number;
    sku: string;
    name: string;
    category: string;
    inventory_location_id: number;
    pattern_id?: number;
    current_stock: number;
    reserved_stock: number;
    minimum_stock: number;
    unit_cost: string;
    selling_price: string;
    quality_grade: string;
    batch_number?: string;
    production_date?: string;
    expiry_date?: string;
    storage_requirements?: string;
    status: string;
    notes?: string;
}

interface Props {
    item: Item;
    locations: Location[];
    patterns: Pattern[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Edit Item Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <ItemForm :item="item" :locations="locations" :patterns="patterns" />
            </div>
        </div>
    </AppLayout>
</template>
