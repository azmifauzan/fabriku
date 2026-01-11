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
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cari</label>
              <input
                v-model="search"
                type="text"
                :placeholder="`Nomor order atau ${patternLabel.toLowerCase()}...`"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select
                v-model="statusFilter"
                class="w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
                  No. Order
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  {{ patternLabel }}
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Target Qty
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Pelaksana
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="cuttingOrders.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                  <p class="font-medium">Tidak ada data {{ termLower('preparation_order', 'cutting order') }}</p>
                  <p class="text-xs">Tambahkan {{ termLower('preparation_order', 'cutting order') }} pertama Anda</p>
                </td>
              </tr>
              <tr v-for="order in cuttingOrders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
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
            class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700"
          >
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan {{ cuttingOrders.from }} - {{ cuttingOrders.to }} dari {{ cuttingOrders.total }} data
              </div>
              <div class="flex gap-2">
                <Link
                  v-for="page in cuttingOrders.last_page"
                  :key="page"
                  :href="`/cutting-orders?page=${page}`"
                  :class="[
                    'px-3 py-1 text-sm rounded',
                    page === cuttingOrders.current_page
                      ? 'bg-indigo-600 text-white'
                      : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 border border-gray-300 dark:border-gray-600'
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
