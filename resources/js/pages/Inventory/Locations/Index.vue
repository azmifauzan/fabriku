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
                <PageHeader
                    title="Lokasi Inventory"
                    description="Kelola lokasi penyimpanan inventory di warehouse"
                    create-link="/inventory/locations/create"
                    create-text="Tambah Lokasi"
                />

                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari nama, zona, atau rak..."
                                @keyup.enter="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select
                                v-model="statusFilter"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak Aktif</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Zona</label>
                            <input
                                v-model="zoneFilter"
                                type="text"
                                placeholder="Filter zona..."
                                @keyup.enter="applyFilters"
                                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                            />
                        </div>

                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="flex-1 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md"
                            >
                                Filter
                            </button>
                            <button
                                v-if="search || statusFilter || zoneFilter"
                                type="button"
                                @click="clearFilters"
                                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold rounded-lg transition-all shadow-sm"
                                title="Clear filters"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
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
                <div v-else class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
                    <div class="px-6 py-16 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Belum ada lokasi</p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulai dengan menambahkan lokasi inventory pertama Anda</p>
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
