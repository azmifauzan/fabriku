<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';

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

const statusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        maintenance: 'bg-yellow-100 text-yellow-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const capacityPercentage = () => {
    if (!props.location.capacity) return 0;
    const used = props.location.current_capacity || 0;
    return Math.round((used / props.location.capacity) * 100);
};
</script>

<template>
    <AppLayout>
        <Head :title="`Lokasi: ${location.name}`" />

        <PageHeader :title="location.name" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6 flex items-center justify-between">
                    <Link href="/inventory/locations" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                        ← Kembali ke Daftar Lokasi
                    </Link>
                    <Link
                        :href="`/inventory/locations/${location.id}/edit`"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        Edit Lokasi
                    </Link>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Location Details -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm lg:col-span-2">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Informasi Lokasi</h3>
                                <span
                                    :class="statusBadgeClass(location.status)"
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ location.status === 'active' ? 'Aktif' : location.status === 'inactive' ? 'Tidak Aktif' : 'Maintenance' }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Zona</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ location.zone }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Rak</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ location.rack }}</dd>
                                </div>
                                <div v-if="location.capacity" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Kapasitas</dt>
                                    <dd class="mt-2">
                                        <div class="flex items-center justify-between text-sm text-gray-900">
                                            <span>{{ location.current_capacity || 0 }} / {{ location.capacity }} items</span>
                                            <span class="font-medium">{{ capacityPercentage() }}%</span>
                                        </div>
                                        <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                                            <div
                                                :class="capacityPercentage() >= 90 ? 'bg-red-600' : capacityPercentage() >= 70 ? 'bg-yellow-600' : 'bg-green-600'"
                                                :style="{ width: `${capacityPercentage()}%` }"
                                                class="h-full transition-all duration-300"
                                            ></div>
                                        </div>
                                    </dd>
                                </div>
                                <div v-if="location.temperature_min || location.temperature_max" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Suhu Penyimpanan</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ location.temperature_min || '-' }}°C ~ {{ location.temperature_max || '-' }}°C
                                    </dd>
                                </div>
                                <div v-if="location.description" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Deskripsi</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ location.description }}</dd>
                                </div>
                                <div v-if="location.notes" class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ location.notes }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-medium text-gray-900">Statistik</h3>
                        </div>
                        <div class="p-6">
                            <dl class="space-y-4">
                                <div class="rounded-lg bg-indigo-50 p-4">
                                    <dt class="text-sm font-medium text-indigo-600">Total Items</dt>
                                    <dd class="mt-1 text-2xl font-bold text-indigo-900">{{ location.items?.length || 0 }}</dd>
                                </div>
                                <div class="rounded-lg bg-green-50 p-4">
                                    <dt class="text-sm font-medium text-green-600">Available Capacity</dt>
                                    <dd class="mt-1 text-2xl font-bold text-green-900">{{ location.available_capacity || '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Items in this location -->
                <div v-if="location.items && location.items.length > 0" class="mt-6 overflow-hidden rounded-lg bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Items di Lokasi Ini</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Nama</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Grade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="item in location.items" :key="item.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ item.sku }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ item.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ item.current_stock }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ item.quality_grade }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ item.status }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <Link
                                            :href="`/inventory/items/${item.id}`"
                                            class="font-medium text-indigo-600 hover:text-indigo-500"
                                        >
                                            Lihat
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
