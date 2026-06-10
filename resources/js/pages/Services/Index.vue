<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, SquarePen, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface Service {
    id: number;
    code: string;
    name: string;
    category: string | null;
    description: string | null;
    price: number;
    duration_minutes: number | null;
    is_active: boolean;
    created_at: string;
}

interface PaginatedServices {
    data: Service[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    services: PaginatedServices;
    filters: {
        search?: string;
        status?: boolean | string;
    };
}

const props = defineProps<Props>();

const { confirmDelete, showSuccess } = useSweetAlert();

const search = ref(props.filters.search || '');
const isActiveFilter = ref(props.filters.status ?? '');

const searchServices = () => {
    router.get(
        '/services',
        {
            search: search.value,
            status: isActiveFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const deleteService = async (service: Service) => {
    const result = await confirmDelete('Hapus Layanan', `Apakah Anda yakin ingin menghapus layanan "${service.name}"?`);

    if (result.isConfirmed) {
        router.delete(`/services/${service.id}`, {
            onSuccess: () => {
                showSuccess('Berhasil!', 'Layanan berhasil dihapus');
            },
        });
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <AppLayout>
        <Head title="Layanan" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader title="Layanan" description="Kelola katalog jasa/layanan" create-link="/services/create" create-text="Tambah Layanan" />

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
                                    placeholder="Kode, nama, kategori..."
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    @keyup.enter="searchServices"
                                />
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                v-model="isActiveFilter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Status</option>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button
                                type="button"
                                @click="searchServices"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md"
                            >
                                <Search :size="16" />
                                Filter
                            </button>
                            <button
                                v-if="search || isActiveFilter"
                                type="button"
                                @click="
                                    search = '';
                                    isActiveFilter = '';
                                    searchServices();
                                "
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
                                Menampilkan <span class="font-semibold">{{ services.from }}</span> -
                                <span class="font-semibold">{{ services.to }}</span> dari
                                <span class="font-semibold">{{ services.total }}</span> layanan
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Kode
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Nama
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Kategori
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Harga Dasar
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
                                    v-for="service in services.data"
                                    :key="service.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ service.code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ service.name }}
                                        </span>
                                        <div v-if="service.duration_minutes" class="text-xs text-gray-500">
                                            Durasi: {{ service.duration_minutes }} menit
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ service.category || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ formatCurrency(service.price) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs leading-5 font-semibold"
                                            :class="
                                                service.is_active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
                                                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
                                            "
                                        >
                                            {{ service.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/services/${service.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Edit layanan"
                                            >
                                                <SquarePen :size="18" />
                                            </Link>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                @click="deleteService(service)"
                                                title="Hapus layanan"
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="services.data.length === 0">
                                    <td colspan="6" class="px-6 py-16 text-center">
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
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                                            />
                                        </svg>
                                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data layanan</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan layanan pertama Anda</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="services.data.length > 0" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ services.from }}</span> - <span class="font-medium">{{ services.to }}</span> dari
                                <span class="font-medium">{{ services.total }}</span> data
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="page in services.last_page"
                                    :key="page"
                                    :href="`/services?page=${page}`"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        page === services.current_page
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
