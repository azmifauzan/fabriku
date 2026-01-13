<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { FileBarChart, Filter } from 'lucide-vue-next'
import { ref } from 'vue'

interface ProductionOrder {
  id: number
  order_number: string
  pattern_name: string
  category: string
  type: string
  contractor_name: string
  quantity_target: number
  quantity_good: number
  quantity_defect: number
  quantity_reject: number
  efficiency_percentage: number
  production_cost: number
  status: string
  start_date: string | null
  target_date: string | null
  completion_date: string | null
}

interface Summary {
  total_orders: number
  total_target: number
  total_produced: number
  total_defect: number
  total_reject: number
  average_efficiency: number
  total_cost: number
  completed_orders: number
}

interface Filters {
  status?: string
  production_type?: string
  start_date?: string
  end_date?: string
}

const props = defineProps<{
  orders: ProductionOrder[]
  summary: Summary
  filters: Filters
}>()

const status = ref(props.filters.status || '')
const productionType = ref(props.filters.production_type || '')
const startDate = ref(props.filters.start_date || '')
const endDate = ref(props.filters.end_date || '')

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const applyFilter = () => {
  router.get(
    '/reports/production',
    {
      status: status.value,
      production_type: productionType.value,
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
  status.value = ''
  productionType.value = ''
  startDate.value = ''
  endDate.value = ''
  applyFilter()
}

const getStatusClass = (orderStatus: string) => {
  const classes: Record<string, string> = {
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
  }
  return classes[orderStatus] || classes.draft
}

const getEfficiencyClass = (efficiency: number) => {
  if (efficiency >= 90) return 'text-green-600 dark:text-green-400'
  if (efficiency >= 75) return 'text-yellow-600 dark:text-yellow-400'
  return 'text-red-600 dark:text-red-400'
}
</script>

<template>
  <AppLayout>
    <Head title="Laporan Produksi" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center gap-3">
              <FileBarChart :size="24" class="text-indigo-500" />
              <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  Laporan Produksi
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Ringkasan efisiensi dan biaya produksi
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Orders
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ summary.total_orders }}
            </dd>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ summary.completed_orders }} completed
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Produced
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ summary.total_produced }}
            </dd>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Target: {{ summary.total_target }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Average Efficiency
            </dt>
            <dd
              class="text-2xl font-semibold mt-1"
              :class="getEfficiencyClass(summary.average_efficiency ?? 0)"
            >
              {{ (summary.average_efficiency ?? 0).toFixed(1) }}%
            </dd>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Defect: {{ summary.total_defect }}, Reject: {{ summary.total_reject }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Cost
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ formatCurrency(summary.total_cost) }}
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
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Status
                </label>
                <select
                  v-model="status"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                >
                  <option value="">Semua Status</option>
                  <option value="completed">Completed</option>
                  <option value="in_progress">In Progress</option>
                  <option value="pending">Pending</option>
                  <option value="draft">Draft</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tipe Produksi
                </label>
                <select
                  v-model="productionType"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                >
                  <option value="">Semua Tipe</option>
                  <option value="internal">Internal</option>
                  <option value="external">External</option>
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
                    Order #
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Pattern
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Contractor
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Target
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Good
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Defect
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Reject
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Efficiency
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Cost
                  </th>
                  <th
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Status
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="orders.length === 0">
                  <td colspan="10" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data produksi
                  </td>
                </tr>
                <tr
                  v-for="order in orders"
                  :key="order.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"
                  >
                    {{ order.order_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ order.pattern_name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                      {{ order.category }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ order.contractor_name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                      {{ order.type }}
                    </div>
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white"
                  >
                    {{ order.quantity_target }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600 dark:text-green-400"
                  >
                    {{ order.quantity_good }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-yellow-600 dark:text-yellow-400"
                  >
                    {{ order.quantity_defect }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-600 dark:text-red-400"
                  >
                    {{ order.quantity_reject }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium"
                    :class="getEfficiencyClass(order.efficiency_percentage)"
                  >
                    {{ order.efficiency_percentage }}%
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white"
                  >
                    {{ formatCurrency(order.production_cost) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span
                      :class="getStatusClass(order.status)"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    >
                      {{ order.status }}
                    </span>
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
