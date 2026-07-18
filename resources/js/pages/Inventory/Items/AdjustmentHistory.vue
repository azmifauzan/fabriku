<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { formatNumber } from '@/lib/utils';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Clock, FileText, RefreshCw, TrendingDown, TrendingUp, User } from 'lucide-vue-next';

interface AdminUser {
    id: number;
    name: string;
}

interface Adjustment {
    id: number;
    adjustment_type: string;
    quantity_before: number;
    quantity_after: number;
    adjustment_quantity: number;
    reason: string;
    notes: string | null;
    adjusted_by_user?: AdminUser;
    approved_by_user?: AdminUser;
    created_at: string;
    adjustment_label: string;
}

interface Location {
    id: number;
    name: string;
    code: string;
}

interface ProductionOrder {
    order_number: string;
}

interface Item {
    id: number;
    sku: string;
    name: string;
    current_stock: number;
    source_label: string;
    inventory_location?: Location;
    production_order?: ProductionOrder;
}

interface AdjustmentTypes {
    [key: string]: string;
}

const props = defineProps<{
    item: Item;
    adjustments: Adjustment[];
    adjustmentTypes: AdjustmentTypes;
}>();

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getAdjustmentTypeColor = (type: string) => {
    switch (type) {
        case 'opening_balance':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'correction':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        case 'damage':
            return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 'loss':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
        case 'found':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'return':
            return 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400';
    }
};

const getAdjustmentIcon = (quantity: number) => {
    if (quantity > 0) return TrendingUp;
    if (quantity < 0) return TrendingDown;
    return RefreshCw;
};
</script>

<template>
    <AppLayout>
        <Head :title="`Riwayat Adjustment - ${item.name}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="`/inventory/items/${item.id}`"
                        class="mb-4 inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Detail Item
                    </Link>

                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Riwayat Adjustment Stock</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">{{ item.name }} ({{ item.sku }})</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Stock Saat Ini</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ formatNumber(item.current_stock) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Item Info Card -->
                <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sumber</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ item.source_label }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Lokasi</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                {{ item.inventory_location?.name || '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Adjustment</p>
                            <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ adjustments.length }}</p>
                        </div>
                    </div>
                </div>

                <!-- Adjustment History -->
                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <h2 class="font-semibold text-gray-900 dark:text-white">Riwayat Perubahan Stock</h2>
                    </div>

                    <div v-if="adjustments.length === 0" class="px-6 py-12 text-center">
                        <FileText class="mx-auto h-12 w-12 text-gray-400" />
                        <p class="mt-4 text-gray-500 dark:text-gray-400">Belum ada riwayat adjustment</p>
                    </div>

                    <div v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="adjustment in adjustments" :key="adjustment.id" class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-4">
                                    <!-- Icon -->
                                    <div
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full"
                                        :class="
                                            adjustment.adjustment_quantity > 0
                                                ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400'
                                                : adjustment.adjustment_quantity < 0
                                                  ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
                                                  : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                        "
                                    >
                                        <component :is="getAdjustmentIcon(adjustment.adjustment_quantity)" class="h-5 w-5" />
                                    </div>

                                    <!-- Details -->
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                                :class="getAdjustmentTypeColor(adjustment.adjustment_type)"
                                            >
                                                {{ adjustmentTypes[adjustment.adjustment_type] || adjustment.adjustment_type }}
                                            </span>
                                        </div>
                                        <p class="mt-1 font-medium text-gray-900 dark:text-white">
                                            {{ adjustment.reason }}
                                        </p>
                                        <p v-if="adjustment.notes" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ adjustment.notes }}
                                        </p>
                                        <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <Clock class="h-3 w-3" />
                                                {{ formatDate(adjustment.created_at) }}
                                            </span>
                                            <span v-if="adjustment.adjusted_by_user" class="flex items-center gap-1">
                                                <User class="h-3 w-3" />
                                                {{ adjustment.adjusted_by_user.name }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quantity Change -->
                                <div class="text-right">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatNumber(adjustment.quantity_before) }}
                                        </span>
                                        <span class="text-gray-400">→</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">
                                            {{ formatNumber(adjustment.quantity_after) }}
                                        </span>
                                    </div>
                                    <p
                                        class="mt-1 text-sm font-medium"
                                        :class="
                                            adjustment.adjustment_quantity > 0
                                                ? 'text-green-600 dark:text-green-400'
                                                : adjustment.adjustment_quantity < 0
                                                  ? 'text-red-600 dark:text-red-400'
                                                  : 'text-gray-500'
                                        "
                                    >
                                        {{ adjustment.adjustment_quantity > 0 ? '+' : '' }}{{ formatNumber(adjustment.adjustment_quantity) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
