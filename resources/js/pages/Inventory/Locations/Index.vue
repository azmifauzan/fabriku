<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';

interface Location {
    id: number;
    name: string;
    zone: string;
    rack: string;
    description?: string;
    capacity?: number;
    status: string;
    current_capacity?: number;
    available_capacity?: number;
}

interface PaginatedLocations {
    data: Location[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    locations: PaginatedLocations;
    filters: {
        search?: string;
        status?: string;
        zone?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const zoneFilter = ref(props.filters.zone || '');

const applyFilters = () => {
    router.get('/inventory/locations', {
        search: search.value || undefined,
        status: statusFilter.value || undefined,
        zone: zoneFilter.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    zoneFilter.value = '';
    router.get('/inventory/locations');
};

const deleteLocation = (location: Location) => {
    if (!confirm(`Hapus lokasi ${location.name}?`)) return;
    
    router.delete(`/inventory/locations/${location.id}`, {
        preserveScroll: true,
    });
};

const statusBadgeClass = (status: string) => {
    const classes: Record<string, string> = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-gray-100 text-gray-800',
        maintenance: 'bg-yellow-100 text-yellow-800',
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

const capacityPercentage = (location: Location) => {
    if (!location.capacity) return 0;
    const used = location.current_capacity || 0;
    return Math.round((used / location.capacity) * 100);
};

const capacityBarClass = (percentage: number) => {
    if (percentage >= 90) return 'bg-red-600';
    if (percentage >= 70) return 'bg-yellow-600';
    return 'bg-green-600';
};
</script>

<template>
    <AppLayout>
        <Head title="Lokasi Inventory" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader title="Lokasi Inventory" />

                <!-- Filters & Actions -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-1 flex-col gap-4 sm:flex-row">
                        <!-- Search -->
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari nama, zona, atau rak..."
                            @keyup.enter="applyFilters"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                        />

                        <!-- Status Filter -->
                        <select
                            v-model="statusFilter"
                            @change="applyFilters"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-auto"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="maintenance">Maintenance</option>
                        </select>

                        <!-- Zone Filter -->
                        <input
                            v-model="zoneFilter"
                            type="text"
                            placeholder="Filter zona..."
                            @keyup.enter="applyFilters"
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-32"
                        />

                        <button
                            v-if="search || statusFilter || zoneFilter"
                            @click="clearFilters"
                            class="rounded-md bg-gray-100 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Reset
                        </button>
                    </div>

                    <Link
                        href="/inventory/locations/create"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    >
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Lokasi
                    </Link>
                </div>

                <!-- Locations Grid -->
                <div v-if="locations.data.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="location in locations.data"
                        :key="location.id"
                        class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-shadow"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ location.name }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Zona {{ location.zone }} • Rak {{ location.rack }}
                                    </p>
                                </div>
                                <span
                                    :class="statusBadgeClass(location.status)"
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                >
                                    {{ location.status === 'active' ? 'Aktif' : location.status === 'inactive' ? 'Tidak Aktif' : 'Maintenance' }}
                                </span>
                            </div>

                            <p v-if="location.description" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ location.description }}
                            </p>

                            <!-- Capacity Bar -->
                            <div v-if="location.capacity" class="mt-4">
                                <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                    <span>Kapasitas</span>
                                    <span class="font-medium">
                                        {{ location.current_capacity || 0 }} / {{ location.capacity }}
                                    </span>
                                </div>
                                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div
                                        :class="capacityBarClass(capacityPercentage(location))"
                                        :style="{ width: `${capacityPercentage(location)}%` }"
                                        class="h-full transition-all duration-300"
                                    ></div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    {{ capacityPercentage(location) }}% terpakai
                                </p>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 flex items-center gap-2">
                                <Link
                                    :href="`/inventory/locations/${location.id}`"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500"
                                >
                                    Detail
                                </Link>
                                <Link
                                    :href="`/inventory/locations/${location.id}/edit`"
                                    class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-500"
                                >
                                    Edit
                                </Link>
                                <button
                                    @click="deleteLocation(location)"
                                    class="text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-500"
                                >
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="rounded-lg bg-white dark:bg-gray-800 p-12 text-center shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Belum ada lokasi</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan lokasi inventory pertama.</p>
                    <div class="mt-6">
                        <Link
                            href="/inventory/locations/create"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Lokasi
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="locations.last_page > 1" class="mt-6 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-3 sm:px-6">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <Link
                            v-if="locations.current_page > 1"
                            :href="`/inventory/locations?page=${locations.current_page - 1}`"
                            class="relative inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="locations.current_page < locations.last_page"
                            :href="`/inventory/locations?page=${locations.current_page + 1}`"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                        >
                            Next
                        </Link>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan
                                <span class="font-medium">{{ (locations.current_page - 1) * locations.per_page + 1 }}</span>
                                hingga
                                <span class="font-medium">{{ Math.min(locations.current_page * locations.per_page, locations.total) }}</span>
                                dari
                                <span class="font-medium">{{ locations.total }}</span>
                                lokasi
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
