<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { FileBarChart, Search, Filter } from 'lucide-vue-next'
import { ref, computed } from 'vue'

interface Material {
  id: number
  code: string
  name: string
  type: string
  current_stock: number
  unit: string
  total_received: number
  total_cost: number
  average_price: number
  receipts_count: number
}

interface Filters {
  material_type?: string
  search?: string
  start_date?: string
  end_date?: string
}

const props = defineProps<{
  materials: Material[]
  filters: Filters
}>()

const search = ref(props.filters.search || '')
const materialType = ref(props.filters.material_type || '')
const startDate = ref(props.filters.start_date || '')
const endDate = ref(props.filters.end_date || '')

const totalValue = computed(() => {
  return props.materials.reduce((sum, m) => sum + m.total_cost, 0)
})

const totalReceived = computed(() => {
  return props.materials.reduce((sum, m) => sum + m.total_received, 0)
})

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const applyFilter = () => {
  router.get(
    '/reports/material',
    {
      search: search.value,
      material_type: materialType.value,
      start_date: startDate.value,
      end_date: endDate.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  )
}

const resetFilter = () => {
  search.value = ''
  materialType.value = ''
  startDate.value = ''
  endDate.value = ''
  applyFilter()
}
</script>

<template>
  <AppLayout>
    <Head title="Laporan Material" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center gap-3">
              <FileBarChart :size="24" class="text-indigo-500" />
              <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Laporan Material</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Ringkasan penerimaan dan penggunaan material
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Material
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ materials.length }}
            </dd>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Penerimaan
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ totalReceived.toFixed(2) }}
            </dd>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Nilai</dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ formatCurrency(totalValue) }}
            </dd>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center gap-2 mb-4">
              <Filter :size="20" class="text-gray-500" />
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Pencarian
                </label>
                <div class="relative">
                  <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                  >
                    <Search :size="16" class="text-gray-400" />
                  </div>
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Nama atau kode..."
                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                    @keyup.enter="applyFilter"
                  />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tipe Material
                </label>
                <select
                  v-model="materialType"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                >
                  <option value="">Semua Tipe</option>
                  <option value="fabric">Kain</option>
                  <option value="thread">Benang</option>
                  <option value="accessory">Aksesoris</option>
                  <option value="other">Lainnya</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Dari Tanggal
                </label>
                <input
                  v-model="startDate"
                  type="date"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Sampai Tanggal
                </label>
                <input
                  v-model="endDate"
                  type="date"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                />
              </div>
              <div class="flex items-end gap-2">
                <button
                  type="button"
                  @click="applyFilter"
                  class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium"
                >
                  Terapkan
                </button>
                <button
                  type="button"
                  @click="resetFilter"
                  class="bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 px-4 rounded-md text-sm font-medium"
                >
                  Reset
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Kode
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Nama Material
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Tipe
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Stok Saat Ini
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Total Diterima
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Total Biaya
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Harga Rata-rata
                  </th>
                  <th
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Jumlah Penerimaan
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="materials.length === 0">
                  <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data material
                  </td>
                </tr>
                <tr
                  v-for="material in materials"
                  :key="material.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                  >
                    {{ material.code }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                    {{ material.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                    >
                      {{ material.type }}
                    </span>
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white"
                  >
                    {{ material.current_stock }} {{ material.unit }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white"
                  >
                    {{ material.total_received }} {{ material.unit }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white"
                  >
                    {{ formatCurrency(material.total_cost) }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400"
                  >
                    {{ formatCurrency(material.average_price) }}/{{ material.unit }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400"
                  >
                    {{ material.receipts_count }}x
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
