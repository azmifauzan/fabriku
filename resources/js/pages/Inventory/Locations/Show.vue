<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface InventoryItem {
    id: number;
    sku: string;
    name: string;
    current_stock: number;
    quality_grade: string;
    status: string;
}

interface Location {
    id: number;
    name: string;
    zone: string;
    rack: string;
    description?: string;
    capacity?: number;
    current_capacity?: number;
    available_capacity?: number;
    temperature_min?: number;
    temperature_max?: number;
    status: string;
    notes?: string;
    items?: InventoryItem[];
}

interface Props {
    location: Location;
}

const props = defineProps<Props>();

const capacityPercentage = () => {
    if (!props.location.capacity) return 0;
    const used = props.location.current_capacity || 0;
    return Math.round((used / props.location.capacity) * 100);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Detail Lokasi: ${location.name}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            Detail Lokasi: {{ location.name }}
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                            {{ location.description || '' }}
                        </p>
                    </div>
                    <Link
                        href="/inventory/locations"
                        class="text-sm font-medium text-gray-600 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
                    >
                        ← Kembali
                    </Link>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <!-- Location Details -->
                    <div class="lg:col-span-2">
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Lokasi</h3>
                                    <div class="flex gap-2">
                                        <Link
                                            :href="`/inventory/locations/${location.id}/edit`"
                                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 dark:focus:ring-offset-gray-800"
                                        >
                                            Edit
                                        </Link>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div v-if="location.capacity" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kapasitas</dt>
                                        <dd class="mt-2">
                                            <div class="mb-2 flex items-center justify-between text-sm text-gray-900 dark:text-gray-100">
                                                <span>{{ location.current_capacity || 0 }} / {{ location.capacity }} items</span>
                                                <span class="font-medium">{{ capacityPercentage() }}%</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div
                                                    :class="
                                                        capacityPercentage() >= 90
                                                            ? 'bg-red-600'
                                                            : capacityPercentage() >= 70
                                                              ? 'bg-yellow-600'
                                                              : 'bg-green-600'
                                                    "
                                                    :style="{ width: `${capacityPercentage()}%` }"
                                                    class="h-full transition-all duration-300"
                                                ></div>
                                            </div>
                                        </dd>
                                    </div>
                                    <div v-if="location.temperature_min || location.temperature_max" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Suhu Penyimpanan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {{ location.temperature_min || '-' }}°C ~ {{ location.temperature_max || '-' }}°C
                                        </dd>
                                    </div>
                                    <div v-if="location.notes" class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                                        <dd class="mt-1 text-sm whitespace-pre-wrap text-gray-900 dark:text-gray-100">{{ location.notes }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Sidebar -->
                    <div class="space-y-6">
                        <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                            <div class="p-6">
                                <h3 class="mb-4 text-lg font-medium text-gray-900 dark:text-gray-100">Statistik</h3>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                        <dd class="mt-1">
                                            <span
                                                :class="
                                                    location.status === 'active'
                                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200'
                                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-100'
                                                "
                                                class="inline-flex rounded-full px-3 py-1 text-sm leading-5 font-semibold"
                                            >
                                                {{ location.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Items</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ location.items?.length || 0 }}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kapasitas Tersedia</dt>
                                        <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ location.available_capacity || '-' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Items in this location -->
                <div v-if="location.items && location.items.length > 0" class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                    <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Items di Lokasi Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        SKU
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Stok
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Grade
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="item in location.items"
                                    :key="item.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ item.sku }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                        {{ item.name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ item.current_stock }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ item.quality_grade }}
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-600 dark:text-gray-400">
                                        {{ item.status }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <Link
                                            :href="`/inventory/items/${item.id}`"
                                            class="text-indigo-600 transition-colors hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            Lihat Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
