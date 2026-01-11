<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

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
    kain: 'bg-blue-100 text-blue-800',
    benang: 'bg-green-100 text-green-800',
    aksesoris: 'bg-purple-100 text-purple-800',
    kemasan: 'bg-yellow-100 text-yellow-800',
    lainnya: 'bg-gray-100 text-gray-800',
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}

const isLowStock = (material: Material) => {
  return parseFloat(material.current_stock) <= parseFloat(material.reorder_point)
}
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <Head title="Data Bahan Baku" />

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-indigo-600">Fabriku</h1>
            </div>
            <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
              <Link
                href="/dashboard"
                class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium"
              >
                Dashboard
              </Link>
              <Link
                href="/materials"
                class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Bahan Baku
              </Link>
              <Link
                href="/patterns"
                class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium"
              >
                Pattern
              </Link>
              <Link
                href="/cutting-orders"
                class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium"
              >
                Cutting Order
              </Link>
            </div>
          </div>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <span class="text-sm text-gray-700">{{ $page.props.auth.user?.name || 'User' }}</span>
              <span class="text-xs text-gray-500 ml-2">({{ $page.props.auth.user?.role || 'staff' }})</span>
            </div>
            <div class="ml-4">
              <Link
                href="/logout"
                method="post"
                as="button"
                class="text-sm text-red-600 hover:text-red-800"
              >
                Logout
              </Link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
          <div>
            <h2 class="text-2xl font-bold text-gray-900">Data Bahan Baku</h2>
            <p class="mt-1 text-sm text-gray-600">
              Kelola bahan baku untuk produksi garment
            </p>
          </div>
          <Link
            href="/materials/create"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Bahan Baku
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
              <input
                v-model="search"
                type="text"
                placeholder="Nama atau kode..."
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
              <select
                v-model="typeFilter"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="statusFilter"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Filter
              </button>
              <button
                type="button"
                @click="clearFilters"
                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Reset
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
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
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="materials.data.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">
                  <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <p class="mt-2 font-medium">Tidak ada data bahan baku</p>
                  <p class="text-xs">Tambahkan bahan baku pertama Anda</p>
                </td>
              </tr>
              <tr v-for="material in materials.data" :key="material.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex flex-col">
                    <div class="text-sm font-medium text-gray-900">{{ material.code }}</div>
                    <div class="text-sm text-gray-500">{{ material.name }}</div>
                    <div v-if="material.attributes && material.attributes.length > 0" class="mt-1 flex flex-wrap gap-1">
                      <span
                        v-for="attr in material.attributes"
                        :key="attr.id"
                        class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-700"
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
                    <div class="text-sm text-gray-900">
                      <span :class="{ 'text-red-600 font-semibold': isLowStock(material) }">
                        {{ formatNumber(material.current_stock) }} {{ material.unit }}
                      </span>
                    </div>
                    <div class="text-xs text-gray-500">
                      Min: {{ formatNumber(material.reorder_point) }}
                      <span v-if="isLowStock(material)" class="text-red-600 font-medium ml-1">⚠ Rendah</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(material.standard_price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="material.is_active"
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
                      :href="`/materials/${material.id}/edit`"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Edit
                    </Link>
                    <button
                      type="button"
                      @click="deleteMaterial(material)"
                      class="text-red-600 hover:text-red-900"
                      :disabled="material.receipts_count > 0"
                      :class="{ 'opacity-50 cursor-not-allowed': material.receipts_count > 0 }"
                      :title="material.receipts_count > 0 ? 'Tidak bisa dihapus karena ada transaksi penerimaan' : 'Hapus'"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="materials.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
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
  </div>
</template>
