<template>
    <AppLayout>
        <Head :title="`Data ${contractorLabel}`" />

        <div class="py-6 px-6">
            <div class="mx-auto max-w-7xl">
                <PageHeader
                    :title="contractorLabel"
                    :description="`Kelola data ${contractorLabelLower} untuk produksi eksternal`"
                    create-link="/contractors/create"
                    :create-text="`Tambah ${contractorLabel}`"
                />

                <!-- Filters and Search -->
                <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="p-5">
                        <form @submit.prevent="search" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Pencarian
                                </label>
                                <input
                                    v-model="form.search"
                                    type="text"
                                    placeholder="Nama, kontak, email, telepon..."
                                    class="mt-1 block w-full px-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 transition-all"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Tipe
                                </label>
                                <select
                                    v-model="form.type"
                                    class="mt-1 block w-full px-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 transition-all"
                                >
                                    <option value="">Semua Tipe</option>
                                    <option value="individual">Individual</option>
                                    <option value="company">Perusahaan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Spesialisasi
                                </label>
                                <select
                                    v-model="form.specialty"
                                    class="mt-1 block w-full px-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 transition-all"
                                >
                                    <option value="">Semua Spesialisasi</option>
                                    <option value="sewing">Penjahit</option>
                                    <option value="baking">Tukang Kue</option>
                                    <option value="crafting">Perajin</option>
                                    <option value="other">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Status
                                </label>
                                <select
                                    v-model="form.status"
                                    class="mt-1 block w-full px-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 transition-all"
                                >
                                    <option value="">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Non-Aktif</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contractors Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-xl border border-gray-200 dark:border-gray-700">
                    <!-- Table Info -->
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan <span class="font-semibold">{{ contractors.data.length }}</span> dari <span class="font-semibold">{{ contractors.total }}</span> {{ contractorLabelLower }}
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700/50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        {{ contractorLabel }}
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        Kontak
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        Spesialisasi
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        Tarif
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="contractor in contractors.data"
                                    :key="contractor.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
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
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                              :class="getSpecialtyBadgeClass(contractor.specialty)">
                                            {{ getSpecialtyLabel(contractor.specialty) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div v-if="contractor.rate_per_piece">
                                            Rp {{ Number(contractor.rate_per_piece).toLocaleString() }}/pcs
                                        </div>
                                        <div v-if="contractor.rate_per_hour">
                                            Rp {{ Number(contractor.rate_per_hour).toLocaleString() }}/jam
                                        </div>
                                        <div v-if="!contractor.rate_per_piece && !contractor.rate_per_hour">
                                            -
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                            :class="contractor.status === 'active' 
                                                ? 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                                                : 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400'"
                                        >
                                            {{ contractor.status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link
                                                :href="show.url(contractor.id)"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Lihat Detail"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </Link>
                                            <Link
                                                :href="edit.url(contractor.id)"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                title="Edit"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </Link>
                                            <button
                                                @click="deleteContractor(contractor)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Hapus"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="contractors.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                Menampilkan {{ contractors.from }} sampai {{ contractors.to }} dari {{ contractors.total }} hasil
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in contractors.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-md',
                                        link.active
                                            ? 'bg-blue-500 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                >
                                    {{ link.label.replace(/&laquo;|&raquo;/g, '') }}
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
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { index, show, edit, destroy } from '@/actions/App/Http/Controllers/ContractorController'
import { useBusinessContext } from '@/composables/useBusinessContext'

interface Contractor {
    id: number
    name: string
    type: string
    contact_person?: string
    phone?: string
    email?: string
    specialty: string
    status: string
    rate_per_piece?: number
    rate_per_hour?: number
}

interface ContractorData {
    data: Contractor[]
    total: number
    links: any[]
    from: number
    to: number
}

interface Filters {
    search?: string
    type?: string
    specialty?: string
    status?: string
}

const props = defineProps<{
    contractors: ContractorData
    filters: Filters
}>()

const { term, termLower } = useBusinessContext()

const contractorLabel = computed(() => term('contractor', 'Kontraktor'))
const contractorLabelLower = computed(() => termLower('contractor', 'kontraktor'))

const form = reactive({
    search: props.filters?.search || '',
    type: props.filters?.type || '',
    specialty: props.filters?.specialty || '',
    status: props.filters?.status || '',
})

const search = () => {
    router.get(index.url(), form, {
        preserveState: true,
        replace: true,
    })
}

const getSpecialtyLabel = (specialty: string) => {
    const labels: Record<string, string> = {
        sewing: 'Penjahit',
        baking: 'Tukang Kue',
        crafting: 'Perajin',
        other: 'Lainnya',
    }
    return labels[specialty] || specialty
}

const getSpecialtyBadgeClass = (specialty: string) => {
    const classes: Record<string, string> = {
        sewing: 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400',
        baking: 'bg-orange-100 text-orange-800 dark:bg-orange-800/20 dark:text-orange-400',
        crafting: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400',
        other: 'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-400',
    }
    return classes[specialty] || classes.other
}

const deleteContractor = (contractor: Contractor) => {
    if (confirm(`Apakah Anda yakin ingin menghapus ${contractorLabelLower.value} ${contractor.name}?`)) {
        router.delete(destroy.url(contractor.id))
    }
}
</script>