<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ItemForm from './Form.vue';

interface Location {
    id: number;
    name: string;
    code: string;
    capacity: number;
}

interface Pattern {
    id: number;
    name: string;
    code: string;
    output_quantity: number;
}

interface PreparationOrder {
    pattern: Pattern;
}

interface ProductionOrder {
    id: number;
    order_number: string;
    preparation_order_id: number;
    labor_cost: string;
    completed_date: string;
    estimated_completion_date: string;
    status: string;
    preparation_order?: PreparationOrder;
}

interface Item {
    id: number;
    sku: string;
    name: string;
    production_order_id?: number;
    inventory_location_id: number;
    target_quantity: number;
    current_stock: number;
    unit_cost: string;
    selling_price: string;
    status: string;
    notes?: string;
}

interface Props {
    item: Item;
    locations: Location[];
    productionOrders: ProductionOrder[];
}

defineProps<Props>();
</script>

<template>
    <AppLayout>
        <Head title="Edit Item Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <ItemForm :item="item" :locations="locations" :production-orders="productionOrders" />
            </div>
        </div>
    </AppLayout>
</template>
