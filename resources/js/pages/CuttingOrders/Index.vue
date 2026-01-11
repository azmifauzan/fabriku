<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'

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

  if (!confirm(`Hapus cutting order ${order.order_number}?`)) return

  router.delete(`/cutting-orders/${order.id}`, {
    preserveScroll: true,
  })
}

const getStatusBadge = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
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
  <div class="min-h-screen bg-gray-100">
    <Head title="Data Cutting Order" />

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
                class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium"
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
                class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
              >
                Cutting Order
              </Link>
            </div>
          </div>
          <div class="flex items-center">
            <span class="text-sm text-gray-700">{{ $page.props.auth.user?.name }}</span>
            <Link
              href="/logout"
              method="post"
              as="button"
              class="ml-4 text-sm text-red-600 hover:text-red-800"
            >
              Logout
            </Link>
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
            <h2 class="text-2xl font-bold text-gray-900">Data Cutting Order</h2>
            <p class="mt-1 text-sm text-gray-600">
              Order pemotongan kain sesuai pattern
            </p>
          </div>
          <Link
            href="/cutting-orders/create"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
          >
            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Order
          </Link>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
              <input
                v-model="search"
                type="text"
                placeholder="Nomor order atau pattern..."
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                @keyup.enter="applyFilters"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="statusFilter"
                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
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
                class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
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
                  No. Order
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Pattern
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Target Qty
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Cutter
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="cuttingOrders.data.length === 0">
                <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">
                  <p class="font-medium">Tidak ada data cutting order</p>
                  <p class="text-xs">Tambahkan cutting order pertama Anda</p>
                </td>
              </tr>
              <tr v-for="order in cuttingOrders.data" :key="order.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ order.order_number }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ order.pattern.code }}</div>
                  <div class="text-xs text-gray-500">{{ order.pattern.name }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900">{{ formatDate(order.order_date) }}</div>
                  <div v-if="order.target_date" class="text-xs text-gray-500">
                    Target: {{ formatDate(order.target_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ order.target_quantity }} pcs</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                    :class="getStatusBadge(order.status)"
                  >
                    {{ getStatusLabel(order.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ order.cutter?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end gap-2">
                    <Link
                      v-if="order.status === 'draft' || order.status === 'in_progress'"
                      :href="`/cutting-orders/${order.id}/edit`"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Edit
                    </Link>
                    <button
                      type="button"
                      @click="deleteCuttingOrder(order)"
                      class="text-red-600 hover:text-red-900"
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
          <div v-if="cuttingOrders.data.length > 0" class="bg-white px-4 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
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
