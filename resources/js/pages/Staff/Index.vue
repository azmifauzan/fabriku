<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Search, SquarePen, Trash2 } from 'lucide-vue-next'
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

interface Props {
  staff: {
    data: Staff[]
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
                placeholder="Cari nama, kode, atau posisi..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
                @keyup.enter="searchStaff"
              />
            </div>
          </div>

          <div class="flex gap-4">
            <select
              v-model="isActiveFilter"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent transition-colors"
              @change="searchStaff"
            >
              <option value="">Semua Status</option>
              <option value="1">Aktif</option>
              <option value="0">Nonaktif</option>
            </select>

            <button
              type="button"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white rounded-lg transition-colors font-medium"
              @click="searchStaff"
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
                    Posisi
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Kontak
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
                  <td colspan="6" class="px-6 py-12 text-center">
                    <div class="text-gray-500 dark:text-gray-400">
                      <p class="text-lg font-medium mb-1">Tidak ada data</p>
                      <p class="text-sm">Belum ada staff yang ditambahkan</p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div
            v-if="staff.last_page > 1"
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between"
          >
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Menampilkan {{ staff.data.length }} dari {{ staff.total }} data
            </div>
            <div class="flex gap-2">
              <Link
                v-for="page in staff.last_page"
                :key="page"
                :href="`/staff?page=${page}&search=${search}&is_active=${isActiveFilter}`"
                class="px-3 py-1 rounded border transition-colors"
                :class="page === staff.current_page
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

