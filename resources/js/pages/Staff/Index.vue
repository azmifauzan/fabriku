<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Search, SquarePen, Trash2, Eye, X } from 'lucide-vue-next'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useSweetAlert } from '@/composables/useSweetAlert'

interface Staff {
  id: number
  code: string
  name: string
  position: string | null
  phone: string | null
  email: string | null
  is_active: boolean
  created_at: string
}

interface PaginatedStaff {
  data: Staff[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

interface Props {
  staff: PaginatedStaff
  filters: {
    search?: string
    is_active?: boolean | string
  }
}

const props = defineProps<Props>()

const { confirmDelete, showSuccess } = useSweetAlert()

const search = ref(props.filters.search || '')
const isActiveFilter = ref(props.filters.is_active ?? '')

const searchStaff = () => {
  router.get(
    '/staff',
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

const deleteStaff = async (staff: Staff) => {
  const result = await confirmDelete(
    'Hapus Staff',
    `Apakah Anda yakin ingin menghapus staff "${staff.name}"?`
  )

  if (result.isConfirmed) {
    router.delete(`/staff/${staff.id}`, {
      onSuccess: () => {
        showSuccess('Berhasil!', 'Staff berhasil dihapus')
      }
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head title="Staff" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          title="Staff"
          description="Kelola data staff / karyawan"
          create-link="/staff/create"
          create-text="Tambah Staff"
        />

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
              <div class="relative">
                <Search :size="18" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Nama, kode, atau posisi..."
                  class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                  @keyup.enter="searchStaff"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
              <select
                v-model="isActiveFilter"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              >
                <option value="">Semua Status</option>
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
            </div>
            <div class="flex items-end gap-2">
              <button
                type="button"
                @click="searchStaff"
                class="flex-1 inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md"
              >
                <Search :size="16" />
                Filter
              </button>
              <button
                v-if="search || isActiveFilter"
                type="button"
                @click="search = ''; isActiveFilter = ''; searchStaff()"
                class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold rounded-lg transition-all shadow-sm"
                title="Clear filters"
              >
                <X :size="18" />
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Table Info -->
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan <span class="font-semibold">{{ staff.from }}</span> - <span class="font-semibold">{{ staff.to }}</span> dari <span class="font-semibold">{{ staff.total }}</span> staff
              </p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Kode
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Nama
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Posisi
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Kontak
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
                  v-for="item in staff.data"
                  :key="item.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                      {{ item.code }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-gray-900 dark:text-white font-medium">
                      {{ item.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                      {{ item.position || '-' }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="item.phone || item.email" class="text-sm text-gray-600 dark:text-gray-400">
                      <div v-if="item.phone">{{ item.phone }}</div>
                      <div v-if="item.email" class="text-xs">{{ item.email }}</div>
                    </div>
                    <span v-else class="text-sm text-gray-600 dark:text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="item.is_active 
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                        : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'"
                    >
                      {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="`/staff/${item.id}`"
                        class="inline-flex items-center justify-center p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                        title="Lihat staff"
                      >
                        <Eye :size="18" />
                      </Link>
                      <Link
                        :href="`/staff/${item.id}/edit`"
                        class="inline-flex items-center justify-center p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                        title="Edit staff"
                      >
                        <SquarePen :size="18" />
                      </Link>
                      <button
                        type="button"
                        class="inline-flex items-center justify-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                        @click="deleteStaff(item)"
                        title="Hapus staff"
                      >
                        <Trash2 :size="18" />
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="staff.data.length === 0">
                  <td colspan="6" class="px-6 py-16 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data staff</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan staff pertama Anda</p>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="staff.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                <span class="font-medium">{{ staff.from }}</span> - <span class="font-medium">{{ staff.to }}</span> dari <span class="font-medium">{{ staff.total }}</span> data
              </div>
              <div class="flex flex-wrap gap-2">
                <Link
                  v-for="page in staff.last_page"
                  :key="page"
                  :href="`/staff?page=${page}`"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                    page === staff.current_page
                      ? 'bg-indigo-600 text-white shadow-sm'
                      : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-300 dark:border-gray-600'
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

