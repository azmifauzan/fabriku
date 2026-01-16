<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { create } from '@/actions/App/Http/Controllers/ProductionOrderController'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { useBusinessContext } from '@/composables/useBusinessContext'
import { useSweetAlert } from '@/composables/useSweetAlert'
import { Eye, Edit, Trash2 } from 'lucide-vue-next'

interface Pattern {
  id: number
  code: string
  name: string
}

interface PreparationOrder {
  id: number
  order_number: string
  pattern: Pattern
}

interface Contractor {
  id: number
  name: string
}

interface ProductionOrder {
  id: number
  order_number: string
  preparation_order: PreparationOrder
  contractor: Contractor | null
  estimated_completion_date: string | null
  type: string
  status: string
  priority: string
  created_at: string
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
const { confirmDelete, showSuccess, showWarning } = useSweetAlert()

const productionOrderLabel = computed(() => 'Production Order')
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

const deleteOrder = async (order: ProductionOrder) => {
  if (order.status !== 'draft') {
    showWarning('Tidak Bisa Dihapus', 'Hanya order dengan status draft yang bisa dihapus')
    return
  }

  const result = await confirmDelete(
    `Hapus ${productionOrderLabel.value}`,
    `Apakah Anda yakin ingin menghapus ${termLower('production_order', 'production order')} ${order.order_number}?`
  )

  if (result.isConfirmed) {
    router.delete(`/production-orders/${order.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        showSuccess('Berhasil!', `${productionOrderLabel.value} berhasil dihapus`)
      }
    })
  }
}

const getStatusBadge = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-300',
    sent: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-800/20 dark:text-indigo-300',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-300',
    completed: 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-300',
  }
  return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800/40 dark:text-gray-300'
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

const markComplete = async (order: ProductionOrder) => {
  const result = await useSweetAlert().confirmAction(
    'Tandai Selesai?',
    `Tandai ${productionOrderLabel.value} ${order.order_number} sebagai selesai?`
  )

  if (result.isConfirmed) {
    router.post(`/production-orders/${order.id}/mark-complete`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        showSuccess('Berhasil!', `${productionOrderLabel.value} ditandai selesai`)
      }
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head :title="`Data ${productionOrderLabel}`" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          :title="productionOrderLabel"
          description="Kelola order produksi internal dan eksternal"
          :create-link="create.url()"
          create-text="Buat Order"
        />

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Pencarian
              </label>
              <input
                v-model="search"
                @input="applyFilters"
                type="text"
                :placeholder="`Order number, ${contractorLabelLower}...`"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Status
              </label>
              <select
                v-model="statusFilter"
                @change="applyFilters"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
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
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Tipe
              </label>
              <select
                v-model="typeFilter"
                @change="applyFilters"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              >
                <option value="">Semua Tipe</option>
                <option value="internal">Internal</option>
                <option value="external">Eksternal</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                {{ contractorLabel }}
              </label>
              <select
                v-model="contractorFilter"
                @change="applyFilters"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              >
                <option :value="''">Semua {{ contractorLabel }}</option>
                <option v-for="contractor in contractors" :key="contractor.id" :value="contractor.id">
                  {{ contractor.name }}
                </option>
              </select>
            </div>

            <div class="flex items-end gap-2">
              <button
                @click="applyFilters"
                class="px-5 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm"
              >
                Filter
              </button>
              <button
                v-if="search || statusFilter || typeFilter || contractorFilter"
                @click="clearFilters"
                class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
              >
                <span class="text-base">✕</span>
                Clear
              </button>
            </div>
          </div>
        </div>

        <!-- Orders Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Order Number
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    {{ patternLabel }}
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Status
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
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="orders.data.length === 0">
                  <td colspan="8" class="px-6 py-16 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data {{ termLower('production_order', 'production order') }}</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Buat order produksi pertama Anda.</p>
                  </td>
                </tr>
                <tr v-for="order in orders.data" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ order.order_number }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">
                      {{ order.preparation_order.pattern?.name || 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ order.preparation_order.pattern?.code || '-' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="[
                        'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                        getStatusBadge(order.status)
                      ]"
                    >
                      {{ getStatusLabel(order.status) }}
                    </span>
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
                    {{ order.preparation_order.output_quantity }} pcs
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ order.estimated_completion_date ? new Date(order.estimated_completion_date).toLocaleDateString('id-ID') : '-' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="`/production-orders/${order.id}`"
                        class="inline-flex items-center justify-center p-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-colors"
                        title="Lihat detail production order"
                      >
                        <Eye :size="18" />
                      </Link>
                      <button
                        v-if="['sent', 'in_progress'].includes(order.status)"
                        type="button"
                        @click="markComplete(order)"
                        class="inline-flex items-center justify-center p-2 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-lg transition-colors"
                        title="Tandai selesai"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" :width="18" :height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"/>
                        </svg>
                      </button>
                      <Link
                        v-if="order.status === 'draft'"
                        :href="`/production-orders/${order.id}/edit`"
                        class="inline-flex items-center justify-center p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                        title="Edit production order"
                      >
                        <Edit :size="18" />
                      </Link>
                      <button
                        v-if="order.status === 'draft'"
                        type="button"
                        @click="deleteOrder(order)"
                        class="inline-flex items-center justify-center p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                        title="Hapus production order"
                      >
                        <Trash2 :size="18" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="orders.last_page > 1" class="bg-white dark:bg-gray-800 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan {{ orders.from }} - {{ orders.to }} dari {{ orders.total }} data
              </div>
              <div class="flex items-center gap-2">
                <Link
                  v-if="orders.current_page > 1"
                  :href="`/production-orders?page=${orders.current_page - 1}`"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all"
                >
                  ← Prev
                </Link>
                <template v-for="page in orders.last_page" :key="page">
                  <Link
                    v-if="Math.abs(page - orders.current_page) <= 2 || page === 1 || page === orders.last_page"
                    :href="`/production-orders?page=${page}`"
                    :class="[
                      'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                      page === orders.current_page
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600'
                    ]"
                  >
                    {{ page }}
                  </Link>
                  <span
                    v-else-if="Math.abs(page - orders.current_page) === 3"
                    class="px-2 text-gray-500 dark:text-gray-400"
                  >
                    ...
                  </span>
                </template>
                <Link
                  v-if="orders.current_page < orders.last_page"
                  :href="`/production-orders?page=${orders.current_page + 1}`"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-all"
                >
                  Next →
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
