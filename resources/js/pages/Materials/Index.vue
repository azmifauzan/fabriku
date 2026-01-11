<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { Edit, Trash2, Search, X, AlertTriangle } from 'lucide-vue-next'

interface MaterialAttribute {
  id: number
  attribute_name: string
  attribute_value: string
}

interface Material {
  id: number
  code: string
  name: string
  type: string
  unit: string
  standard_price: string
  current_stock: string
  reorder_point: string
  is_active: boolean
  receipts_count: number
  attributes?: MaterialAttribute[]
  created_at: string
}

interface PaginatedMaterials {
  data: Material[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  materials: PaginatedMaterials
  filters: {
    search?: string
    type?: string
    is_active?: string
  }
}>()

const search = ref(props.filters.search || '')
const typeFilter = ref(props.filters.type || '')
const statusFilter = ref(props.filters.is_active || '')

const applyFilters = () => {
  router.get('/materials', {
    search: search.value || undefined,
    type: typeFilter.value || undefined,
    is_active: statusFilter.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  typeFilter.value = ''
  statusFilter.value = ''
  applyFilters()
}

const deleteMaterial = (material: Material) => {
  if (!confirm(`Hapus material ${material.name}?`)) return

  router.delete(`/materials/${material.id}`, {
    preserveScroll: true,
  })
}

const formatCurrency = (value: string) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(parseFloat(value))
}

const formatNumber = (value: string) => {
  return new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(parseFloat(value))
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    kain: 'Kain',
    benang: 'Benang',
    aksesoris: 'Aksesoris',
    kemasan: 'Kemasan',
    lainnya: 'Lainnya',
  }
  return labels[type] || type
}

const getTypeBadgeColor = (type: string) => {
  const colors: Record<string, string> = {
    kain: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    benang: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    aksesoris: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
    kemasan: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
    lainnya: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300',
  }
  return colors[type] || 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'
}

const isLowStock = (material: Material) => {
  return parseFloat(material.current_stock) <= parseFloat(material.reorder_point)
}
</script>

<template>
  <AppLayout>
    <Head title="Data Bahan Baku" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          title="Data Bahan Baku"
          description="Kelola bahan baku untuk produksi garment"
          create-link="/materials/create"
          create-text="Tambah Bahan Baku"
        />

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari</label>
              <div class="relative">
                <Search :size="18" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Nama atau kode..."
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                  @keyup.enter="applyFilters"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis</label>
              <select
                v-model="typeFilter"
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
              >
                <option value="">Semua Jenis</option>
                <option value="kain">Kain</option>
                <option value="benang">Benang</option>
                <option value="aksesoris">Aksesoris</option>
                <option value="kemasan">Kemasan</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select
                v-model="statusFilter"
                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
                class="flex-1 inline-flex justify-center items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
              >
                <Search :size="16" />
                Filter
              </button>
              <button
                type="button"
                @click="clearFilters"
                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm font-medium rounded-lg transition-colors"
              >
                <X :size="16" />
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Kode / Nama
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jenis
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stok
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Harga Standar
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="materials.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="mt-2 font-medium">Tidak ada data bahan baku</p>
                  <p class="text-xs">Tambahkan bahan baku pertama Anda</p>
                </td>
              </tr>
              <tr v-for="material in materials.data" :key="material.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ material.code }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ material.name }}</div>
                    <div v-if="material.attributes && material.attributes.length > 0" class="mt-1 flex flex-wrap gap-1">
                      <span
                        v-for="attr in material.attributes"
                        :key="attr.id"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300"
                      >
                        <span class="font-medium">{{ attr.attribute_name }}:</span>
                        <span class="ml-1">{{ attr.attribute_value }}</span>
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getTypeBadgeColor(material.type)"
                  >
                    {{ getTypeLabel(material.type) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col">
                    <div class="text-sm text-gray-900 dark:text-white">
                      <span :class="{ 'text-red-600 dark:text-red-400 font-semibold': isLowStock(material) }">
                        {{ formatNumber(material.current_stock) }} {{ material.unit }}
                      </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      Min: {{ formatNumber(material.reorder_point) }}
                      <span v-if="isLowStock(material)" class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 font-medium ml-1">
                        <AlertTriangle :size="12" />
                        Rendah
                      </span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                  {{ formatCurrency(material.standard_price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="material.is_active"
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300"
                  >
                    Aktif
                  </span>
                  <span
                    v-else
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300"
                  >
                    Nonaktif
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-3">
                    <Link
                      :href="`/materials/${material.id}/edit`"
                      class="inline-flex items-center gap-1 text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                    >
                      <Edit :size="16" />
                      <span>Edit</span>
                    </Link>
                    <button
                      type="button"
                      @click="deleteMaterial(material)"
                      class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                      :disabled="material.receipts_count > 0"
                      :class="{ 'opacity-50 cursor-not-allowed': material.receipts_count > 0 }"
                      :title="material.receipts_count > 0 ? 'Tidak bisa dihapus karena ada transaksi penerimaan' : 'Hapus'"
                    >
                      <Trash2 :size="16" />
                      <span>Hapus</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="materials.data.length > 0" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan
                <span class="font-medium">{{ materials.from }}</span>
                -
                <span class="font-medium">{{ materials.to }}</span>
                dari
                <span class="font-medium">{{ materials.total }}</span>
                data
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="page in materials.last_page"
                  :key="page"
                  :href="`/materials?page=${page}`"
                  :class="[
                    'px-3 py-1 text-sm rounded',
                    page === materials.current_page
                      ? 'bg-indigo-600 text-white'
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600'
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
