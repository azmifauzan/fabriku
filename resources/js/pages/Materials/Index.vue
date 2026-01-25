<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { AlertTriangle, Edit, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface MaterialAttribute {
    id: number;
    attribute_key: string;
    attribute_value: string;
}

interface Material {
    id: number;
    code: string;
    name: string;
    material_type_id: number;
    unit: string;
    price_per_unit: string;
    stock_quantity: string;
    min_stock: string;
    reorder_point: string;
    supplier_name?: string;
    receipts_count: number;
    attributes?: MaterialAttribute[];
    materialType?: {
        id: number;
        name: string;
        code: string;
    };
    created_at: string;
}

interface PaginatedMaterials {
    data: Material[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface MaterialType {
    id: number;
    name: string;
}

const props = defineProps<{
    materials: PaginatedMaterials;
    types: MaterialType[];
    filters: {
        search?: string;
        material_type_id?: string;
    };
    stats: {
        total_materials: number;
        low_stock_materials: number;
        out_of_stock_materials: number;
        total_asset_value: number;
    };
}>();

const { confirmDelete, showSuccess } = useSweetAlert();

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.material_type_id || '');

const applyFilters = () => {
    router.get(
        '/materials',
        {
            search: search.value || undefined,
            material_type_id: typeFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    search.value = '';
    typeFilter.value = '';
    applyFilters();
};

const deleteMaterial = async (material: Material) => {
    const result = await confirmDelete('Hapus Bahan Baku', `Apakah Anda yakin ingin menghapus bahan baku "${material.name}"?`);

    if (result.isConfirmed) {
        router.delete(`/materials/${material.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Berhasil!', 'Bahan baku berhasil dihapus');
            },
        });
    }
};

const formatCurrency = (value: string | number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(typeof value === 'string' ? parseFloat(value) : value);
};

const formatNumber = (value: string) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(parseFloat(value));
};

const isLowStock = (material: Material) => {
    return parseFloat(material.stock_quantity) <= parseFloat(material.min_stock);
};
</script>

<template>
    <AppLayout>
        <Head title="Data Bahan Baku" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    title="Data Bahan Baku"
                    description="Kelola bahan baku untuk produksi garment"
                    create-link="/materials/create"
                    create-text="Tambah Bahan Baku"
                />

                <!-- Statistics -->
                <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Material</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_materials }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-yellow-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stok Menipis</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.low_stock_materials }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-red-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Stok Habis</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.out_of_stock_materials }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-5">
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Valuasi Aset</p>
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(stats.total_asset_value) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:gap-4">
                        <div class="flex-1">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                            <div class="relative">
                                <Search :size="18" class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Nama atau kode..."
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis</label>
                            <select
                                v-model="typeFilter"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <option value="">Semua Jenis</option>
                                <option v-for="type in types" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                @click="applyFilters"
                                class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md"
                            >
                                <Search :size="16" />
                                Filter
                            </button>
                            <button
                                v-if="search || typeFilter"
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
                                Menampilkan <span class="font-semibold">{{ materials.from }}</span> -
                                <span class="font-semibold">{{ materials.to }}</span> dari
                                <span class="font-semibold">{{ materials.total }}</span> bahan baku
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Kode / Nama
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Jenis
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Stok
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Harga
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr v-if="materials.data.length === 0">
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
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                            />
                                        </svg>
                                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data bahan baku</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan bahan baku pertama Anda</p>
                                    </td>
                                </tr>
                                <tr
                                    v-for="material in materials.data"
                                    :key="material.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ material.code }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ material.name }}</div>
                                            <div v-if="material.attributes && material.attributes.length > 0" class="mt-1 flex flex-wrap gap-1">
                                                <span
                                                    v-for="attr in material.attributes"
                                                    :key="attr.id"
                                                    class="inline-flex items-center rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                                >
                                                    <span class="font-medium">{{ attr.attribute_key }}:</span>
                                                    <span class="ml-1">{{ attr.attribute_value }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ material.materialType?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                <span :class="{ 'font-semibold text-red-600 dark:text-red-400': isLowStock(material) }">
                                                    {{ formatNumber(material.stock_quantity) }} {{ material.unit }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                Min: {{ formatNumber(material.min_stock) }}
                                                <span
                                                    v-if="isLowStock(material)"
                                                    class="ml-1 inline-flex items-center gap-1 font-medium text-red-600 dark:text-red-400"
                                                >
                                                    <AlertTriangle :size="12" />
                                                    Rendah
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm whitespace-nowrap text-gray-900 dark:text-white">
                                        {{ formatCurrency(material.price_per_unit) }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="`/materials/${material.id}`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                title="Lihat detail"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </Link>
                                            <Link
                                                :href="`/materials/${material.id}/edit`"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                title="Edit bahan baku"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button
                                                type="button"
                                                @click="deleteMaterial(material)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                :disabled="material.receipts_count > 0"
                                                :title="
                                                    material.receipts_count > 0
                                                        ? 'Tidak bisa dihapus karena ada transaksi penerimaan'
                                                        : 'Hapus bahan baku'
                                                "
                                            >
                                                <Trash2 :size="18" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="materials.data.length > 0" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ materials.from }}</span> - <span class="font-medium">{{ materials.to }}</span> dari
                                <span class="font-medium">{{ materials.total }}</span> data
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="page in materials.last_page"
                                    :key="page"
                                    :href="`/materials?page=${page}`"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        page === materials.current_page
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
