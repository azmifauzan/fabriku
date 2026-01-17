<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useSweetAlert } from '@/composables/useSweetAlert'
import { useBusinessContext } from '@/composables/useBusinessContext'
import { Edit, Trash2, Eye } from 'lucide-vue-next'

interface PatternMaterial {
  id: number
  name: string
  code: string
  unit: string
  pivot: {
    quantity_needed: string
  }
}

interface Pattern {
  id: number
  code: string
  name: string
  product_type: string
  is_active: boolean
  preparation_orders_count: number
  materials: PatternMaterial[]
}

interface PaginatedPatterns {
  data: Pattern[]
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

const { productTypes, term, termLower } = useBusinessContext()
const { confirmDelete, showSuccess } = useSweetAlert()

const patternLabel = computed(() => term('pattern', 'Pattern'))
const patternLabelLower = computed(() => termLower('pattern', 'pattern'))
const materialLabel = computed(() => term('material', 'Bahan Baku'))

const props = defineProps<{
  patterns: PaginatedPatterns
  filters: {
    search?: string
    product_type?: string
    is_active?: string
  }
}>()

const search = ref(props.filters.search || '')
const productTypeFilter = ref(props.filters.product_type || '')
const statusFilter = ref(props.filters.is_active || '')

const applyFilters = () => {
  router.get('/patterns', {
    search: search.value || undefined,
    product_type: productTypeFilter.value || undefined,
    is_active: statusFilter.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  productTypeFilter.value = ''
  statusFilter.value = ''
  applyFilters()
}

const deletePattern = async (pattern: Pattern) => {
  const result = await confirmDelete(
    `Hapus ${patternLabel.value}`,
    `Apakah Anda yakin ingin menghapus ${termLower('pattern', 'pattern')} "${pattern.name}"?`
  )

  if (result.isConfirmed) {
    router.delete(`/patterns/${pattern.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        showSuccess('Berhasil!', `${patternLabel.value} berhasil dihapus`)
      }
    })
  }
}

const getProductTypeLabel = (type: string) => {
  return productTypes.value[type] || type
}

const getProductTypeBadge = (type: string) => {
  if (!type) {
    return 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300'
  }

  const palette = [
    'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-300',
    'bg-pink-100 text-pink-800 dark:bg-pink-800/20 dark:text-pink-300',
    'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300',
    'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300',
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-300',
    'bg-indigo-100 text-indigo-800 dark:bg-indigo-800/20 dark:text-indigo-300',
  ]

  let hash = 0
  for (let i = 0; i < type.length; i += 1) {
    hash = (hash * 31 + type.charCodeAt(i)) % 2147483647
  }

  return palette[hash % palette.length] || 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300'
}
</script>

<template>
  <AppLayout>
    <Head :title="`Data ${patternLabel}`" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          :title="patternLabel"
          :description="`Template produk dengan kebutuhan ${materialLabel.toLowerCase()} (BOM)`"
          create-link="/patterns/create"
          :create-text="`Tambah ${patternLabel}`"
        />

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
              <input
                v-model="search"
                type="text"
                placeholder="Nama atau kode..."
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Jenis Produk</label>
              <select
                v-model="productTypeFilter"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              >
                <option value="">Semua Jenis</option>
                <option v-for="(label, value) in productTypes" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
              <select
                v-model="statusFilter"
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
                @click="applyFilters"
                class="flex-1 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md"
              >
                Filter
              </button>
              <button
                v-if="search || productTypeFilter || statusFilter"
                type="button"
                @click="clearFilters"
                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold rounded-lg transition-all shadow-sm"
                title="Clear filters"
              >
                ✕
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700/50">
              <tr>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  {{ patternLabel }}
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  Jenis & Ukuran
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  {{ materialLabel }} (BOM)
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
              <tr v-if="patterns.data.length === 0">
                <td colspan="5" class="px-6 py-16 text-center">
                  <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data {{ termLower('pattern', 'pattern') }}</p>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan {{ termLower('pattern', 'pattern') }} produk pertama Anda</p>
                </td>
              </tr>
              <tr v-for="pattern in patterns.data" :key="pattern.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ pattern.code }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ pattern.name }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getProductTypeBadge(pattern.product_type)"
                  >
                    {{ getProductTypeLabel(pattern.product_type) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    <div v-if="pattern.materials && pattern.materials.length > 0" class="space-y-1">
                      <div
                        v-for="material in pattern.materials"
                        :key="material.id"
                        class="text-xs"
                      >
                        • {{ material.code }}: {{ material.pivot.quantity_needed }} {{ material.unit }}
                      </div>
                    </div>
                    <span v-else class="text-gray-400">Belum ada BOM</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="pattern.is_active"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300"
                  >
                    Aktif
                  </span>
                  <span
                    v-else
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300"
                  >
                    Nonaktif
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                  <div class="flex justify-end gap-2">
                    <Link
                      :href="`/patterns/${pattern.id}`"
                      class="inline-flex items-center justify-center p-2 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                      :title="`Lihat ${patternLabelLower}`"
                    >
                      <Eye :size="18" />
                    </Link>
                    <Link
                      :href="`/patterns/${pattern.id}/edit`"
                      class="inline-flex items-center justify-center p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                      :title="`Edit ${patternLabelLower}`"
                    >
                      <Edit :size="18" />
                    </Link>
                    <button
                      type="button"
                      @click="deletePattern(pattern)"
                      class="inline-flex items-center justify-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="pattern.preparation_orders_count > 0"
                      :title="pattern.preparation_orders_count > 0 ? 'Tidak bisa dihapus, sudah ada preparation order' : `Hapus ${patternLabelLower}`"
                    >
                      <Trash2 :size="18" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div
            v-if="patterns.data.length > 0"
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
          >
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                <span class="font-medium">{{ patterns.from }}</span> - <span class="font-medium">{{ patterns.to }}</span> dari <span class="font-medium">{{ patterns.total }}</span> data
              </div>
              <div class="flex flex-wrap gap-2">
                <Link
                  v-for="page in patterns.last_page"
                  :key="page"
                  :href="`/patterns?page=${page}`"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                    page === patterns.current_page
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
