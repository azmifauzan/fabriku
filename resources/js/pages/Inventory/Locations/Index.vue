<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import { Eye, Edit, Trash2, Search } from 'lucide-vue-next';

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
    router.get('/inventory/locations', {
        search: search.value || undefined,
        is_active: isActiveValue,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    search.value = '';
    statusFilter.value = '';
    router.get('/inventory/locations');
};

const deleteLocation = async (location: Location) => {
    const result = await confirmDelete(
        'Hapus Lokasi Inventory',
        `Apakah Anda yakin ingin menghapus lokasi "${location.name}"?`
    );

    if (result.isConfirmed) {
        router.delete(`/inventory/locations/${location.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Berhasil!', 'Lokasi inventory berhasil dihapus');
            }
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

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Lokasi Inventory"
                    description="Kelola lokasi penyimpanan inventory di warehouse"
                    create-link="/inventory/locations/create"
                    create-text="Tambah Lokasi"
                />

                <!-- Filters -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <Search
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"
                                :size="20"
                            />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari nama atau rak..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <select
                            v-model="statusFilter"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
                            @change="applyFilters"
                        >
                            <option value="">Semua Status</option>
                            <option value="true">Aktif</option>
                            <option value="false">Tidak Aktif</option>
                        </select>

                        <button
                            v-if="search || statusFilter"
                            type="button"
                            @click="clearFilters"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold rounded-lg transition-all shadow-sm"
                            title="Clear filters"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Kapasitas
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="location in locations.data"
                                    :key="location.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ location.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div v-if="location.capacity" class="w-32">
                                            <div class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
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
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ location.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/inventory/locations/${location.id}`"
                                                class="inline-flex items-center justify-center p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                                                title="Lihat detail lokasi"
                                            >
                                                <Eye :size="18" />
                                            </Link>
                                            <Link
                                                :href="`/inventory/locations/${location.id}/edit`"
                                                class="inline-flex items-center justify-center p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                                                title="Edit lokasi"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                                @click="deleteLocation(location)"
                                                title="Hapus lokasi"
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="locations.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <div class="text-gray-500 dark:text-gray-400">
                                            <p class="text-lg font-medium mb-1">Tidak ada data</p>
                                            <p class="text-sm">Belum ada lokasi yang ditambahkan</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="locations.last_page > 1"
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
                    >
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan {{ locations.data.length }} dari {{ locations.total }} data
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="page in locations.last_page"
                                :key="page"
                                :href="`/inventory/locations?page=${page}&search=${search}&status=${statusFilter}`"
                                class="px-3 py-1 rounded border transition-colors"
                                :class="page === locations.current_page
                                    ? 'bg-indigo-600 text-white border-indigo-600'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                            >
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
