<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

interface Pattern {
  id: number
  code: string
  name: string
}

interface User {
  id: number
  name: string
}

interface CuttingOrder {
  id: number
  order_number: string
  pattern: Pattern
  order_date: string
  target_date: string | null
  target_quantity: number
  status: string
  cutter: User | null
}

interface PaginatedOrders {
  data: CuttingOrder[]
  current_page: number
  last_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  cuttingOrders: PaginatedOrders
  filters: {
    search?: string
    status?: string
  }
}>()

const { term, termLower } = useBusinessContext()

const preparationOrderLabel = computed(() => term('preparation_order', 'Cutting Order'))
const preparationLabel = computed(() => term('preparation', 'Cutting/Pemotongan'))
const patternLabel = computed(() => term('pattern', 'Pattern'))

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')

const applyFilters = () => {
  router.get('/cutting-orders', {
    search: search.value || undefined,
    status: statusFilter.value || undefined,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  search.value = ''
  statusFilter.value = ''
  applyFilters()
}

const deleteCuttingOrder = (order: CuttingOrder) => {
  if (order.status !== 'draft') {
    alert('Hanya order dengan status draft yang bisa dihapus')
    return
  }

  if (!confirm(`Hapus ${termLower('preparation_order', 'cutting order')} ${order.order_number}?`)) return

  router.delete(`/cutting-orders/${order.id}`, {
    preserveScroll: true,
  })
}

const getStatusBadge = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300',
  }
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300'
}

const getStatusLabel = (status: string) => {
  const labels: Record<string, string> = {
    draft: 'Draft',
    in_progress: 'Proses',
    completed: 'Selesai',
    cancelled: 'Batal',
  }
  return labels[status] || status
}

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="`Data ${preparationOrderLabel}`" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          :title="`Data ${preparationOrderLabel}`"
          :description="`Kelola ${preparationOrderLabel.toLowerCase()} untuk proses ${preparationLabel.toLowerCase()}`"
          create-link="/cutting-orders/create"
          create-text="Tambah Order"
        />

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
              <input
                v-model="search"
                type="text"
                :placeholder="`Nomor order atau ${patternLabel.toLowerCase()}...`"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
              <select
                v-model="statusFilter"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              >
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="in_progress">Proses</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Batal</option>
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
                v-if="search || statusFilter"
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
                  No. Order
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  {{ patternLabel }}
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  Target Qty
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  Pelaksana
                </th>
                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="cuttingOrders.data.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                  <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data {{ termLower('preparation_order', 'cutting order') }}</p>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan {{ termLower('preparation_order', 'cutting order') }} pertama Anda</p>
                </td>
              </tr>
              <tr v-for="order in cuttingOrders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ order.order_number }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ order.pattern.code }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ order.pattern.name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(order.order_date) }}</div>
                  <div v-if="order.target_date" class="text-xs text-gray-500 dark:text-gray-400">
                    Target: {{ formatDate(order.target_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ order.target_quantity }} pcs</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getStatusBadge(order.status)"
                  >
                    {{ getStatusLabel(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ order.cutter?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2">
                    <Link
                      v-if="order.status === 'draft' || order.status === 'in_progress'"
                      :href="`/cutting-orders/${order.id}/edit`"
                      class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300"
                    >
                      Edit
                    </Link>
                    <button
                      type="button"
                      @click="deleteCuttingOrder(order)"
                      class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300"
                      :disabled="order.status !== 'draft'"
                      :class="{ 'opacity-50 cursor-not-allowed': order.status !== 'draft' }"
                      :title="order.status !== 'draft' ? 'Hanya draft yang bisa dihapus' : 'Hapus'"
                    >
                      Hapus
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div
            v-if="cuttingOrders.data.length > 0"
            class="px-6 py-4 border-t border-gray-200 dark:border-gray-700"
          >
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                <span class="font-medium">{{ cuttingOrders.from }}</span> - <span class="font-medium">{{ cuttingOrders.to }}</span> dari <span class="font-medium">{{ cuttingOrders.total }}</span> data
              </div>
              <div class="flex flex-wrap gap-2">
                <Link
                  v-for="page in cuttingOrders.last_page"
                  :key="page"
                  :href="`/cutting-orders?page=${page}`"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                    page === cuttingOrders.current_page
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
