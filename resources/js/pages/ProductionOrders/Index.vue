<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { create } from '@/actions/App/Http/Controllers/ProductionOrderController'
import AppLayout from '@/layouts/AppLayout.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

interface Pattern {
  id: number
  code: string
  name: string
}

interface CuttingOrder {
  id: number
  order_number: string
  pattern: Pattern
}

interface CuttingResult {
  id: number
  cutting_order: CuttingOrder
}

interface Contractor {
  id: number
  name: string
}

interface ProductionOrder {
  id: number
  order_number: string
  cutting_result: CuttingResult
  contractor: Contractor | null
  requested_date: string
  promised_date: string | null
  quantity_requested: number
  type: string
  status: string
}

interface PaginatedOrders {
  data: ProductionOrder[]
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  orders: PaginatedOrders
  contractors: Contractor[]
  filters: {
    search?: string
    status?: string
    type?: string
    contractor_id?: string
  }
}>()

const { term, termLower } = useBusinessContext()

const productionOrderLabel = computed(() => term('production_order', 'Production Order'))
const contractorLabel = computed(() => term('contractor', 'Kontraktor'))
const contractorLabelLower = computed(() => termLower('contractor', 'kontraktor'))
const patternLabel = computed(() => term('pattern', 'Pattern'))

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')
const typeFilter = ref(props.filters.type || '')
const contractorFilter = ref(props.filters.contractor_id || '')

const applyFilters = () => {
  router.get('/production-orders', {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
    type: typeFilter.value || undefined,
    contractor_id: contractorFilter.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  statusFilter.value = ''
  typeFilter.value = ''
  contractorFilter.value = ''
  applyFilters()
}

const deleteOrder = (order: ProductionOrder) => {
  if (order.status !== 'draft') {
    alert('Hanya order dengan status draft yang bisa dihapus')
    return
  }

  if (!confirm(`Hapus ${termLower('production_order', 'production order')} ${order.order_number}?`)) return

  router.delete(`/production-orders/${order.id}`, {
    preserveScroll: true,
  })
}

const getStatusBadge = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    pending: 'bg-yellow-100 text-yellow-800',
    sent: 'bg-indigo-100 text-indigo-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    draft: 'Draft',
    pending: 'Pending',
    sent: 'Dikirim',
    in_progress: 'Dalam Proses',
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  }
  return labels[status] || status
}

const getTypeLabel = (type: string) => {
  return type === 'internal' ? 'Internal' : 'Eksternal'
}
</script>

<template>
  <AppLayout>
    <Head :title="`Data ${productionOrderLabel}`" />

    <PageHeader 
      :title="productionOrderLabel"
      subtitle="Kelola order produksi internal dan eksternal"
    />

    <!-- Page Content -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Filters -->
        <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Pencarian
                </label>
                <input
                  v-model="search"
                  @input="applyFilters"
                  type="text"
                  :placeholder="`Order number, ${contractorLabelLower}...`"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Status
                </label>
                <select
                  v-model="statusFilter"
                  @change="applyFilters"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">Semua Status</option>
                  <option value="draft">Draft</option>
                  <option value="pending">Pending</option>
                  <option value="sent">Dikirim</option>
                  <option value="in_progress">Dalam Proses</option>
                  <option value="completed">Selesai</option>
                  <option value="cancelled">Dibatalkan</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Tipe
                </label>
                <select
                  v-model="typeFilter"
                  @change="applyFilters"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option value="">Semua Tipe</option>
                  <option value="internal">Internal</option>
                  <option value="external">Eksternal</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  {{ contractorLabel }}
                </label>
                <select
                  v-model="contractorFilter"
                  @change="applyFilters"
                  class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                  <option :value="''">Semua {{ contractorLabel }}</option>
                  <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                    {{ contractor.name }}
                  </option>
                </select>
              </div>

              <div class="flex items-end">
                <button
                  @click="clearFilters"
                  class="w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                  Reset Filter
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="mb-6 flex items-center justify-between">
          <div class="text-sm text-gray-500">
            Menampilkan {{ orders.from }} - {{ orders.to }} dari {{ orders.total }} orders
          </div>
          <Link
            :href="create.url()"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat {{ productionOrderLabel }}
          </Link>
        </div>

        <!-- Orders Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Order Number
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    {{ patternLabel }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Tipe
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    {{ contractorLabel }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Quantity
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Target Date
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="orders.data.length === 0">
                  <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                    Tidak ada data {{ termLower('production_order', 'production order') }}
                  </td>
                </tr>
                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ order.order_number }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ new Date(order.requested_date).toLocaleDateString('id-ID') }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                      {{ order.cutting_result.cutting_order.pattern.name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ order.cutting_result.cutting_order.pattern.code }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-gray-900 dark:text-gray-100">
                      {{ getTypeLabel(order.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="text-sm text-gray-900 dark:text-gray-100">
                      {{ order.contractor?.name || '-' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    {{ order.quantity_requested }} pcs
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ order.promised_date ? new Date(order.promised_date).toLocaleDateString('id-ID') : '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="getStatusBadge(order.status)"
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    >
                      {{ getStatusLabel(order.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="`/production-orders/${order.id}`"
                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      >
                        Detail
                      </Link>
                      <Link
                        v-if="order.status === 'draft'"
                        :href="`/production-orders/${order.id}/edit`"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                      >
                        Edit
                      </Link>
                      <button
                        v-if="order.status === 'draft'"
                        @click="deleteOrder(order)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      >
                        Hapus
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="orders.last_page > 1" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 flex justify-between sm:hidden">
                <Link
                  v-if="orders.current_page > 1"
                  :href="`/production-orders?page=${orders.current_page - 1}`"
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                  Previous
                </Link>
                <Link
                  v-if="orders.current_page < orders.last_page"
                  :href="`/production-orders?page=${orders.current_page + 1}`"
                  class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                >
                  Next
                </Link>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700 dark:text-gray-300">
                    Halaman {{ orders.current_page }} dari {{ orders.last_page }}
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <Link
                      v-if="orders.current_page > 1"
                      :href="`/production-orders?page=${orders.current_page - 1}`"
                      class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                      ←
                    </Link>
                    <Link
                      v-if="orders.current_page < orders.last_page"
                      :href="`/production-orders?page=${orders.current_page + 1}`"
                      class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                    >
                      →
                    </Link>
                  </nav>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
