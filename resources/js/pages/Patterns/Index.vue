<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

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
  size: string | null
  is_active: boolean
  cutting_orders_count: number
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

const patternLabel = computed(() => term('pattern', 'Pattern'))
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

const deletePattern = (pattern: Pattern) => {
  if (!confirm(`Hapus ${termLower('pattern', 'pattern')} ${pattern.name}?`)) return

  router.delete(`/patterns/${pattern.id}`, {
    preserveScroll: true,
  })
}

const getProductTypeLabel = (type: string) => {
  return productTypes.value[type] || type
}

const getProductTypeBadge = (type: string) => {
  const palette = [
    'bg-purple-100 text-purple-800',
    'bg-pink-100 text-pink-800',
    'bg-blue-100 text-blue-800',
    'bg-green-100 text-green-800',
    'bg-yellow-100 text-yellow-800',
    'bg-indigo-100 text-indigo-800',
  ]

  let hash = 0
  for (let i = 0; i < type.length; i += 1) {
    hash = (hash * 31 + type.charCodeAt(i)) % 2147483647
  }

  return palette[hash % palette.length] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <AppLayout>
    <Head :title="`Data ${patternLabel} Produk`" />

    <!-- Main Content -->
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Data {{ patternLabel }} Produk</h2>
            <p class="mt-1 text-sm text-gray-600">
              Template produk dengan kebutuhan {{ materialLabel.toLowerCase() }} (BOM)
            </p>
          </div>
          <Link
            href="/patterns/create"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah {{ patternLabel }}
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari</label>
              <input
                v-model="search"
                type="text"
                placeholder="Nama atau kode..."
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Produk</label>
              <select
                v-model="productTypeFilter"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
              >
                <option value="">Semua Jenis</option>
                <option v-for="(label, value) in productTypes" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select
                v-model="statusFilter"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
              >
                Filter
              </button>
              <button
                type="button"
                @click="clearFilters"
                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  {{ patternLabel }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Jenis & Ukuran
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  {{ materialLabel }} (BOM)
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="patterns.data.length === 0">
                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                  <p class="font-medium">Tidak ada data {{ termLower('pattern', 'pattern') }}</p>
                  <p class="text-xs">Tambahkan {{ termLower('pattern', 'pattern') }} produk pertama Anda</p>
                </td>
              </tr>
              <tr v-for="pattern in patterns.data" :key="pattern.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <div class="text-sm font-medium text-gray-900">{{ pattern.code }}</div>
                    <div class="text-sm text-gray-500">{{ pattern.name }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getProductTypeBadge(pattern.product_type)"
                    >
                      {{ getProductTypeLabel(pattern.product_type) }}
                    </span>
                    <span v-if="pattern.size" class="text-xs text-gray-500">
                      Size: {{ pattern.size }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">
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
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                  >
                    Aktif
                  </span>
                  <span
                    v-else
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                  >
                    Nonaktif
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2">
                    <Link
                      :href="`/patterns/${pattern.id}/edit`"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Edit
                    </Link>
                    <button
                      type="button"
                      @click="deletePattern(pattern)"
                      class="text-red-600 hover:text-red-900"
                      :disabled="pattern.cutting_orders_count > 0"
                      :class="{ 'opacity-50 cursor-not-allowed': pattern.cutting_orders_count > 0 }"
                      :title="pattern.cutting_orders_count > 0 ? 'Tidak bisa dihapus, sudah ada cutting order' : 'Hapus'"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="patterns.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Menampilkan {{ patterns.from }} - {{ patterns.to }} dari {{ patterns.total }} data
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="page in patterns.last_page"
                  :key="page"
                  :href="`/patterns?page=${page}`"
                  :class="[
                    'px-3 py-1 text-sm rounded',
                    page === patterns.current_page
                      ? 'bg-indigo-600 text-white'
                      : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
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
