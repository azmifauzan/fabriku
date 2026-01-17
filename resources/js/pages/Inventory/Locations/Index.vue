<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

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
        is_active?: string;
    };
}

const props = defineProps<Props>();

const { confirmDelete, showSuccess } = useSweetAlert();

const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.is_active || '');

const applyFilters = () => {
    const isActiveValue = statusFilter.value ? (statusFilter.value === 'true' ? 'true' : 'false') : undefined;
    router.get(
        '/inventory/locations',
        {
            search: search.value || undefined,
            is_active: isActiveValue,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    router.get('/inventory/locations');
};

const deleteLocation = async (location: Location) => {
    const result = await confirmDelete('Hapus Lokasi Inventory', `Apakah Anda yakin ingin menghapus lokasi "${location.name}"?`);

    if (result.isConfirmed) {
        router.delete(`/inventory/locations/${location.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Berhasil!', 'Lokasi inventory berhasil dihapus');
            },
        });
    }
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

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Lokasi Inventory"
                    description="Kelola lokasi penyimpanan inventory di warehouse"
                    create-link="/inventory/locations/create"
                    create-text="Tambah Lokasi"
                />

                <!-- Filters -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                            <div class="relative">
                                <Search :size="18" class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Nama atau rak..."
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="statusFilter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="true">Aktif</option>
                                <option value="false">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md"
                            >
                                <Search :size="16" />
                                Filter
                            </button>
                            <button
                                v-if="search || statusFilter"
                                type="button"
                                @click="clearFilters"
                                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition-all hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                                title="Clear filters"
                            >
                                <X :size="18" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <!-- Table Info -->
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan <span class="font-semibold">{{ locations.from }}</span> -
                                <span class="font-semibold">{{ locations.to }}</span> dari
                                <span class="font-semibold">{{ locations.total }}</span> lokasi
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Nama
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Kapasitas
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr
                                    v-for="location in locations.data"
                                    :key="location.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ location.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="location.capacity" class="w-32">
                                            <div class="mb-1 flex items-center justify-between text-xs text-gray-600 dark:text-gray-400">
                                                <span>{{ location.current_capacity || 0 }}/{{ location.capacity }}</span>
                                                <span>{{ capacityPercentage(location) }}%</span>
                                            </div>
                                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div
                                                    :class="capacityBarClass(capacityPercentage(location))"
                                                    :style="{ width: `${capacityPercentage(location)}%` }"
                                                    class="h-full transition-all duration-300"
                                                ></div>
                                            </div>
                                        </div>
                                        <span v-else class="text-sm text-gray-600 dark:text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="statusBadgeClass(location.status)"
                                            class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                        >
                                            {{ location.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/inventory/locations/${location.id}`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700"
                                                title="Lihat detail lokasi"
                                            >
                                                <Eye :size="18" />
                                            </Link>
                                            <Link
                                                :href="`/inventory/locations/${location.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Edit lokasi"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                @click="deleteLocation(location)"
                                                title="Hapus lokasi"
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="locations.data.length === 0">
                                    <td colspan="4" class="px-6 py-16 text-center">
                                        <svg
                                            class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                            />
                                        </svg>
                                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data lokasi</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan lokasi inventory pertama Anda</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="locations.data.length > 0" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ locations.from }}</span> - <span class="font-medium">{{ locations.to }}</span> dari
                                <span class="font-medium">{{ locations.total }}</span> data
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="page in locations.last_page"
                                    :key="page"
                                    :href="`/inventory/locations?page=${page}`"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        page === locations.current_page
                                            ? 'bg-indigo-600 text-white shadow-sm'
                                            : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                    ]"
                                    preserve-state
                                    preserve-scroll
                                >
                                    {{ page }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
