<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { AlertTriangle, Archive, Box, ChevronDown, ChevronUp, LayoutGrid, Package } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface InventoryItem {
    id: number;
    sku: string;
    name: string;
    quantity: number;
    reserved: number;
    is_low_stock: boolean;
    image_url?: string;
}

interface Location {
    id: number;
    name: string;
    code: string;
    type: string;
    capacity: number | null;
    used_capacity: number;
    percentage: number;
    is_unlimited: boolean;
    items: InventoryItem[];
}

interface Props {
    locations: Location[];
    stats: {
        total_locations: number;
        total_items: number;
        total_capacity: number;
        total_used: number;
    };
}

const props = defineProps<Props>();

const expandedLocations = ref<number[]>([]);

const toggleLocation = (locationId: number) => {
    if (expandedLocations.value.includes(locationId)) {
        expandedLocations.value = expandedLocations.value.filter((id) => id !== locationId);
    } else {
        expandedLocations.value.push(locationId);
    }
};

const getCapacityColor = (percentage: number) => {
    if (percentage >= 90) return 'text-red-600 dark:text-red-400';
    if (percentage >= 70) return 'text-yellow-600 dark:text-yellow-400';
    return 'text-green-600 dark:text-green-400';
};

const getProgressBarColor = (percentage: number) => {
    if (percentage >= 90) return 'bg-red-500';
    if (percentage >= 70) return 'bg-yellow-500';
    return 'bg-green-500';
};
</script>

<template>
    <AppLayout>
        <Head title="Visualisasi Inventory" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <PageHeader
                    title="Visualisasi Inventory"
                    description="Gambaran visual lokasi inventory dan penempatan barang"
                >
                    <template #actions>
                        <Link
                            href="/inventory/locations"
                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Kelola Lokasi
                        </Link>
                    </template>
                </PageHeader>

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                                <LayoutGrid :size="20" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Lokasi</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_locations }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-green-50 p-2 text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                <Package :size="20" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Item Fisik</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_items }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                <Archive :size="20" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Kapasitas Terpakai</p>
                                <p class="text-xl font-bold text-gray-900 dark:text-white">{{ stats.total_used }} units</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locations Grid -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="location in locations"
                        :key="location.id"
                        class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                    >
                        <!-- Card Header -->
                        <div
                            class="flex cursor-pointer items-start justify-between border-b border-gray-100 bg-gray-50/50 p-4 dark:border-gray-700 dark:bg-gray-800/50"
                            @click="toggleLocation(location.id)"
                        >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ location.name }}</h3>
                                    <span class="rounded bg-gray-200 px-1.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ location.code }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span v-if="location.is_unlimited">Kapasitas Unlimited</span>
                                    <span v-else>
                                        {{ location.used_capacity }} / {{ location.capacity }} items
                                        <span :class="['ml-1 font-medium', getCapacityColor(location.percentage)]">
                                            ({{ location.percentage }}%)
                                        </span>
                                    </span>
                                </div>
                            </div>
                            <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <ChevronUp v-if="expandedLocations.includes(location.id)" :size="20" />
                                <ChevronDown v-else :size="20" />
                            </button>
                        </div>

                        <!-- Capacity Bar (if limited) -->
                        <div v-if="!location.is_unlimited" class="h-1.5 w-full bg-gray-100 dark:bg-gray-700">
                            <div
                                class="h-full transition-all duration-500"
                                :class="getProgressBarColor(location.percentage)"
                                :style="{ width: `${Math.min(location.percentage, 100)}%` }"
                            ></div>
                        </div>

                        <!-- Items List -->
                        <div
                            class="flex-1 overflow-hidden transition-all duration-300 ease-in-out"
                            :class="expandedLocations.includes(location.id) ? 'max-h-[500px] overflow-y-auto' : 'max-h-[180px]'"
                        >
                            <div v-if="location.items.length > 0" class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                <Link
                                    v-for="item in location.items"
                                    :key="item.id"
                                    :href="`/inventory/items/${item.id}`"
                                    class="group flex items-center gap-3 p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/30"
                                >
                                    <!-- Product Image -->
                                    <div class="shrink-0">
                                        <img
                                            v-if="item.image_url"
                                            :src="item.image_url"
                                            :alt="item.name"
                                            class="h-12 w-12 rounded-lg border border-gray-200 object-cover shadow-sm dark:border-gray-700"
                                        />
                                        <div
                                            v-else
                                            class="flex h-12 w-12 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
                                        >
                                            <Package :size="20" class="text-gray-400" />
                                        </div>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400">
                                                {{ item.name }}
                                            </p>
                                            <AlertTriangle v-if="item.is_low_stock" :size="14" class="text-red-500" title="Low Stock" />
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.sku }}</p>
                                    </div>
                                    <div class="shrink-0 text-right text-sm">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ item.quantity }}</p>
                                        <p v-if="item.reserved > 0" class="text-xs text-yellow-600 dark:text-yellow-400">
                                            {{ item.reserved }} resv
                                        </p>
                                    </div>
                                </Link>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="flex h-32 flex-col items-center justify-center p-4 text-center text-gray-400 dark:text-gray-500">
                                <Box class="mb-2 h-8 w-8 opacity-20" />
                                <p class="text-sm">Lokasi kosong</p>
                            </div>
                        </div>

                        <!-- Footer / Expand hint -->
                        <div
                            v-if="!expandedLocations.includes(location.id) && location.items.length > 3"
                            class="cursor-pointer border-t border-gray-100 bg-gray-50/50 p-2 text-center text-xs font-medium text-gray-500 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                            @click="toggleLocation(location.id)"
                        >
                            + {{ location.items.length - 3 }} item lainnya
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
