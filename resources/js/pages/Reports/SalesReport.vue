<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { FileBarChart, Search, Filter, TrendingUp } from 'lucide-vue-next'
import { ref } from 'vue'

interface Order {
  id: number
  order_number: string
  order_date: string
  customer_name: string
  customer_type: string
  total_items: number
  subtotal: number
  discount: number
  total_amount: number
  payment_status: string
  status: string
}

interface Summary {
  total_orders: number
  total_revenue: number
  total_discount: number
  total_items_sold: number
  completed_orders: number
  pending_orders: number
}

interface RevenueByType {
  [key: string]: {
    count: number
    total: number
  }
}

interface Filters {
  status?: string
  customer_type?: string
  search?: string
  start_date?: string
  end_date?: string
}

interface DefaultDates {
  start_date: string
  end_date: string
}

const props = defineProps<{
  orders: Order[]
  summary: Summary
  revenueByType: RevenueByType
  filters: Filters
  defaultDates: DefaultDates
}>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const customerType = ref(props.filters.customer_type || '')
const startDate = ref(props.filters.start_date || props.defaultDates.start_date)
const endDate = ref(props.filters.end_date || props.defaultDates.end_date)

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const applyFilter = () => {
  router.get(
    '/reports/sales',
    {
      search: search.value,
      status: status.value,
      customer_type: customerType.value,
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
  status.value = ''
  customerType.value = ''
  startDate.value = props.defaultDates.start_date
  endDate.value = props.defaultDates.end_date
  applyFilter()
}

const getStatusClass = (orderStatus: string) => {
  const classes: Record<string, string> = {
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
  }
  return classes[orderStatus] || classes.draft
}

const getPaymentStatusClass = (paymentStatus: string) => {
  const classes: Record<string, string> = {
    paid: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    partial: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    unpaid: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
  }
  return classes[paymentStatus] || classes.unpaid
}
</script>

<template>
  <AppLayout>
    <Head title="Laporan Penjualan" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center gap-3">
              <FileBarChart :size="24" class="text-indigo-500" />
              <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                  Laporan Penjualan
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  Ringkasan penjualan dan revenue
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Orders
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ summary.total_orders }}
            </dd>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ summary.completed_orders }} completed, {{ summary.pending_orders }} pending
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Revenue
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ formatCurrency(summary.total_revenue) }}
            </dd>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              Discount: {{ formatCurrency(summary.total_discount) }}
            </p>
          </div>
          <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
              Total Items Sold
            </dt>
            <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
              {{ summary.total_items_sold }}
            </dd>
          </div>
        </div>

        <!-- Revenue by Customer Type -->
        <div
          v-if="Object.keys(revenueByType).length > 0"
          class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
        >
          <div class="p-6">
            <div class="flex items-center gap-2 mb-4">
              <TrendingUp :size="20" class="text-indigo-500" />
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Revenue by Customer Type
              </h3>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div
                v-for="(data, type) in revenueByType"
                :key="type"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4"
              >
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 capitalize">
                  {{ type }}
                </dt>
                <dd class="text-xl font-semibold text-gray-900 dark:text-white mt-1">
                  {{ formatCurrency(data.total) }}
                </dd>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                  {{ data.count }} orders
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg">
          <div class="p-6">
            <div class="flex items-center gap-2 mb-4">
              <Filter :size="20" class="text-gray-500" />
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filter</h3>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
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
                    placeholder="Order # atau customer..."
                    class="pl-10 w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                    @keyup.enter="applyFilter"
                  />
                </div>
              </div>
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
                  <option value="pending">Pending</option>
                  <option value="cancelled">Cancelled</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Tipe Customer
                </label>
                <select
                  v-model="customerType"
                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                >
                  <option value="">Semua Tipe</option>
                  <option value="retail">Retail</option>
                  <option value="reseller">Reseller</option>
                  <option value="wholesale">Wholesale</option>
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
                    Tanggal
                  </th>
                  <th
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Customer
                  </th>
                  <th
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Items
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Subtotal
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Discount
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Total
                  </th>
                  <th
                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                  >
                    Payment
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
                  <td colspan="9" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    Tidak ada data penjualan
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
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400"
                  >
                    {{ order.order_date }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      {{ order.customer_name }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                      {{ order.customer_type }}
                    </div>
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-white"
                  >
                    {{ order.total_items }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white"
                  >
                    {{ formatCurrency(order.subtotal) }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500 dark:text-gray-400"
                  >
                    {{ formatCurrency(order.discount) }}
                  </td>
                  <td
                    class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900 dark:text-white"
                  >
                    {{ formatCurrency(order.total_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span
                      :class="getPaymentStatusClass(order.payment_status)"
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    >
                      {{ order.payment_status }}
                    </span>
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
