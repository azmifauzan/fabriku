<template>
    <AppLayout>
        <Head :title="`Data ${contractorLabel}`" />

        <div class="px-6 py-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="contractorLabel"
                    :description="`Kelola data ${contractorLabelLower} untuk produksi eksternal`"
                    create-link="/contractors/create"
                    :create-text="`Tambah ${contractorLabel}`"
                />

                <!-- Filters and Search -->
                <div class="mb-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="p-5">
                        <form @submit.prevent="search" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Pencarian </label>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Nama, kontak, email, telepon..."
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Tipe </label>
                                <select
                                    v-model="form.type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Tipe</option>
                                    <option value="individual">Individual</option>
                                    <option value="company">Perusahaan</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Spesialisasi </label>
                                <select
                                    v-model="form.specialty"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Spesialisasi</option>
                                    <option value="sewing">Penjahit</option>
                                    <option value="baking">Tukang Kue</option>
                                    <option value="crafting">Perajin</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Status </label>
                                <select
                                    v-model="form.status"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Non-Aktif</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button
                                    type="submit"
                                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600"
                                >
                                    Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contractors Table -->
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <!-- Table Info -->
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan <span class="font-semibold">{{ contractors.data.length }}</span> dari
                                <span class="font-semibold">{{ contractors.total }}</span> {{ contractorLabelLower }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        {{ contractorLabel }}
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Kontak
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold tracking-wider text-gray-700 uppercase dark:text-gray-200">
                                        Spesialisasi
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
                                    v-for="contractor in contractors.data"
                                    :key="contractor.id"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300 dark:bg-gray-600">
                                                    <svg
                                                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                                        />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ contractor.name }}
                                                </div>
                                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ contractor.type === 'individual' ? 'Individual' : 'Perusahaan' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ contractor.contact_person || '-' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ contractor.phone || contractor.email || '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="getSpecialtyBadgeClass(contractor.specialty)"
                                        >
                                            {{ getSpecialtyLabel(contractor.specialty) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            :class="
                                                contractor.is_active
                                                    ? 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                                                    : 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                                            "
                                        >
                                            {{ contractor.is_active ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm whitespace-nowrap">
                                        <div class="flex justify-end gap-2">
                                            <Link
                                                :href="show.url(contractor.id)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/30"
                                                :title="`Lihat detail ${contractorLabelLower}`"
                                            >
                                                <Eye :size="18" />
                                            </Link>
                                            <Link
                                                :href="edit.url(contractor.id)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                                :title="`Edit ${contractorLabelLower}`"
                                            >
                                                <Edit :size="18" />
                                            </Link>
                                            <button
                                                type="button"
                                                @click="deleteContractor(contractor)"
                                                class="inline-flex items-center justify-center rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
                                                :title="`Hapus ${contractorLabelLower}`"
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
                    <div v-if="contractors.data.length > 0" class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                        <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                <span class="font-medium">{{ contractors.from }}</span> - <span class="font-medium">{{ contractors.to }}</span> dari
                                <span class="font-medium">{{ contractors.total }}</span> data
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-for="page in contractors.last_page"
                                    :key="page"
                                    :href="`/contractors?page=${page}`"
                                    :class="[
                                        'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        page === contractors.current_page
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

<script setup lang="ts">
import { destroy, edit, index, show } from '@/actions/App/Http/Controllers/ContractorController';
import PageHeader from '@/components/PageHeader.vue';
import { useSweetAlert } from '@/composables/useSweetAlert';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Trash2 } from 'lucide-vue-next';
import { reactive } from 'vue';

interface Contractor {
    id: number;
    name: string;
    type: string;
    contact_person?: string;
    phone?: string;
    email?: string;
    specialty: string;
    is_active: boolean;
    rate_per_piece?: number;
    rate_per_hour?: number;
}

interface ContractorData {
    data: Contractor[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Filters {
    search?: string;
    type?: string;
    specialty?: string;
    status?: string;
}

const props = defineProps<{
    contractors: ContractorData;
    filters: Filters;
}>();

const { confirmDelete, showSuccess } = useSweetAlert();

const contractorLabel = 'Kontraktor';
const contractorLabelLower = 'kontraktor';

const form = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    specialty: props.filters?.specialty || '',
    status: props.filters?.status || '',
});

const search = () => {
    router.get(index.url(), form, {
        preserveState: true,
        replace: true,
    });
};

const getSpecialtyLabel = (specialty: string) => {
    const labels: Record<string, string> = {
        sewing: 'Penjahit',
        baking: 'Tukang Kue',
        crafting: 'Perajin',
        other: 'Lainnya',
    };
    return labels[specialty] || specialty;
};

const getSpecialtyBadgeClass = (specialty: string) => {
    const classes: Record<string, string> = {
        sewing: 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400',
        baking: 'bg-orange-100 text-orange-800 dark:bg-orange-800/20 dark:text-orange-400',
        crafting: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400',
        other: 'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-400',
    };
    return classes[specialty] || classes.other;
};

const deleteContractor = async (contractor: Contractor) => {
    const result = await confirmDelete(`Hapus ${contractorLabel}`, `Apakah Anda yakin ingin menghapus ${contractorLabelLower} "${contractor.name}"?`);

    if (result.isConfirmed) {
        router.delete(destroy.url(contractor.id), {
            onSuccess: () => {
                showSuccess('Berhasil!', `${contractorLabel} berhasil dihapus`);
            },
        });
    }
};
</script>
