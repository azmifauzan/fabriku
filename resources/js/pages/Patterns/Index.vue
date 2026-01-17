<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { useBusinessContext } from '@/composables/useBusinessContext';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Search, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface PatternMaterial {
    id: number;
    name: string;
    code: string;
    unit: string;
    pivot: {
        quantity_needed: string;
    };
}

interface Pattern {
    id: number;
    code: string;
    name: string;
    preparation_orders_count: number;
    materials: PatternMaterial[];
    is_active: boolean;
}

interface PaginatedPatterns {
    data: Pattern[];
    current_page: number;
    last_page: number;
    total: number;
    from: number;
    to: number;
}

const { term, termLower } = useBusinessContext();
const { confirmDelete, showSuccess } = useSweetAlert();

const patternLabel = computed(() => term('pattern', 'Pattern'));
const patternLabelLower = computed(() => termLower('pattern', 'pattern'));
const materialLabel = computed(() => term('material', 'Bahan Baku'));

const props = defineProps<{
    patterns: PaginatedPatterns;
    filters: {
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');

const applyFilters = () => {
    router.get(
        '/patterns',
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const deletePattern = async (pattern: Pattern) => {
    const result = await confirmDelete(
        `Hapus ${patternLabel.value}`,
        `Apakah Anda yakin ingin menghapus ${termLower('pattern', 'pattern')} "${pattern.name}"?`,
    );

    if (result.isConfirmed) {
        router.delete(`/patterns/${pattern.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                showSuccess('Berhasil!', `${patternLabel.value} berhasil dihapus`);
            },
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Data ${patternLabel}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="patternLabel"
                    :description="`Template produk dengan kebutuhan ${materialLabel.toLowerCase()} (BOM)`"
                    create-link="/patterns/create"
                    :create-text="`Tambah ${patternLabel}`"
                />

                <!-- Filters -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
                            <div class="relative">
                                <Search :size="18" class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-400" />
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Kode atau nama..."
                                    class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    @keyup.enter="applyFilters"
                                />
                            </div>
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
                                v-if="search"
                                type="button"
                                @click="
                                    search = '';
                                    applyFilters();
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
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                    {{ patternLabel }}
                                </th>
                                <th class="px-6 py-4 text-center text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                    Status
                                </th>
                                <th class="px-6 py-4 text-right text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr v-if="patterns.data.length === 0">
                                <td colspan="3" class="px-6 py-16 text-center">
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
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                    <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">
                                        Tidak ada data {{ termLower('pattern', 'pattern') }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Tambahkan {{ termLower('pattern', 'pattern') }} produk pertama Anda
                                    </p>
                                </td>
                            </tr>
                            <tr
                                v-for="pattern in patterns.data"
                                :key="pattern.id"
                                class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ pattern.code }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ pattern.name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                            pattern.is_active
                                                ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400',
                                        ]"
                                    >
                                        {{ pattern.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="`/patterns/${pattern.id}`"
                                            class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 transition-colors hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700"
                                            :title="`Lihat ${patternLabelLower}`"
                                        >
                                            <Eye :size="18" />
                                        </Link>
                                        <Link
                                            :href="`/patterns/${pattern.id}/edit`"
                                            class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                            :title="`Edit ${patternLabelLower}`"
                                        >
                                            <Edit :size="18" />
                                        </Link>
                                        <button
                                            type="button"
                                            @click="deletePattern(pattern)"
                                            class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                            :disabled="pattern.preparation_orders_count > 0"
                                            :title="
                                                pattern.preparation_orders_count > 0
                                                    ? 'Tidak bisa dihapus, sudah ada preparation order'
                                                    : `Hapus ${patternLabelLower}`
                                            "
                                        >
                                            <Trash2 :size="18" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="patterns.data.length > 0" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ patterns.from }}</span> - <span class="font-medium">{{ patterns.to }}</span> dari
                                <span class="font-medium">{{ patterns.total }}</span> data
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="page in patterns.last_page"
                                    :key="page"
                                    :href="`/patterns?page=${page}`"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        page === patterns.current_page
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
