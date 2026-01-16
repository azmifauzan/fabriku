<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Search, Plus, SquarePen, Trash2 } from 'lucide-vue-next'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useSweetAlert } from '@/composables/useSweetAlert'

interface MaterialType {
  id: number
  code: string
  name: string
  description: string | null
  sort_order: number
  is_active: boolean
  created_at: string
}

interface Props {
  materialTypes: {
    data: MaterialType[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    search?: string
    is_active?: boolean | string
  }
}

const props = defineProps<Props>()

const { confirmDelete, showSuccess } = useSweetAlert()

const search = ref(props.filters.search || '')
const isActiveFilter = ref(props.filters.is_active ?? '')

const searchMaterialTypes = () => {
  router.get(
    '/material-types',
    {
      search: search.value,
      is_active: isActiveFilter.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  )
}

const deleteMaterialType = async (materialType: MaterialType) => {
  const result = await confirmDelete(
    'Hapus Jenis Bahan',
    `Apakah Anda yakin ingin menghapus jenis bahan "${materialType.name}"?`
  )

  if (result.isConfirmed) {
    router.delete(`/material-types/${materialType.id}`, {
      onSuccess: () => {
        showSuccess('Berhasil!', 'Jenis bahan berhasil dihapus')
      }
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Jenis Bahan" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          title="Jenis Bahan"
          description="Kelola jenis/kategori bahan baku"
          create-link="/material-types/create"
          create-text="Tambah Jenis Bahan"
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
                placeholder="Cari kode atau nama..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
                @keyup.enter="searchMaterialTypes"
              />
            </div>
          </div>

          <div class="flex gap-4">
            <select
              v-model="isActiveFilter"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
              @change="searchMaterialTypes"
            >
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>

            <button
              type="button"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors font-medium"
              @click="searchMaterialTypes"
            >
              Filter
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
                    Kode
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Nama
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Deskripsi
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Urutan
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
                  v-for="materialType in materialTypes.data"
                  :key="materialType.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ materialType.code }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-gray-900 dark:text-white font-medium">
                      {{ materialType.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                      {{ materialType.description || '-' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-gray-900 dark:text-white">
                      {{ materialType.sort_order }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="materialType.is_active 
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'"
                    >
                      {{ materialType.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="`/material-types/${materialType.id}/edit`"
                        class="inline-flex items-center justify-center p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                        title="Edit tipe bahan baku"
                      >
                        <SquarePen :size="18" />
                      </Link>
                      <button
                        type="button"
                        class="inline-flex items-center justify-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                        @click="deleteMaterialType(materialType)"
                        title="Hapus tipe bahan baku"
                      >
                        <Trash2 :size="18" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="materialTypes.data.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                      <p class="text-lg font-medium mb-1">Tidak ada data</p>
                      <p class="text-sm">Belum ada jenis bahan yang ditambahkan</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div
            v-if="materialTypes.last_page > 1"
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
          >
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Menampilkan {{ materialTypes.data.length }} dari {{ materialTypes.total }} data
            </div>
            <div class="flex gap-2">
              <Link
                v-for="page in materialTypes.last_page"
                :key="page"
                :href="`/material-types?page=${page}&search=${search}&is_active=${isActiveFilter}`"
                class="px-3 py-1 rounded border transition-colors"
                :class="page === materialTypes.current_page
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
