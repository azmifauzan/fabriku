<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { Edit, Trash2, Search, X, Eye } from 'lucide-vue-next'

interface Customer {
  id: number
  code: string
  name: string
  type: string
  phone: string
  email: string
  address: string
  city: string
  province: string
  discount_percentage: string
  payment_term: string
  is_active: boolean
  notes: string
  created_at: string
}

interface PaginatedCustomers {
  data: Customer[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const props = defineProps<{
  customers: PaginatedCustomers
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
  router.get('/customers', {
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

const deleteCustomer = (customer: Customer) => {
  if (!confirm(`Hapus customer ${customer.name}?`)) return

  router.delete(`/customers/${customer.id}`, {
    preserveScroll: true,
  })
}

const getTypeLabel = (type: string) => {
  const labels: Record<string, string> = {
    retail: 'Retail',
    reseller: 'Reseller',
    online: 'Online',
  }
  return labels[type] || type
}

const getTypeBadgeColor = (type: string) => {
  const colors: Record<string, string> = {
    retail: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    reseller: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300',
    online: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
  }
  return colors[type] || 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'
}

const getPaymentTermLabel = (term: string) => {
  const labels: Record<string, string> = {
    cash: 'Cash',
    credit_7: '7 Hari',
    credit_14: '14 Hari',
    credit_30: '30 Hari',
  }
  return labels[term] || term
}
</script>

<template>
  <AppLayout>
    <Head title="Data Customer" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <PageHeader
          title="Data Customer"
          description="Kelola data customer untuk penjualan produk"
          create-link="/customers/create"
          create-text="Tambah Customer"
        />

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cari</label>
              <div class="relative">
                <Search :size="18" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                <input
                  v-model="search"
                  type="text"
                  placeholder="Nama, kode, telepon..."
                  class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
                  @keyup.enter="applyFilters"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipe</label>
              <select
                v-model="typeFilter"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
              >
                <option value="">Semua Tipe</option>
                <option value="retail">Retail</option>
                <option value="reseller">Reseller</option>
                <option value="online">Online</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
              <select
                v-model="statusFilter"
                class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all"
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
                class="flex-1 inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition-all shadow-sm hover:shadow-md"
              >
                <Search :size="16" />
                Filter
              </button>
              <button
                v-if="search || typeFilter || statusFilter"
                type="button"
                @click="clearFilters"
                class="inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm font-semibold rounded-lg transition-all shadow-sm"
                title="Clear filters"
              >
                <X :size="18" />
              </button>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700">
          <!-- Table Info -->
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Menampilkan <span class="font-semibold">{{ customers.from }}</span> - <span class="font-semibold">{{ customers.to }}</span> dari <span class="font-semibold">{{ customers.total }}</span> customer
              </p>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Kode / Nama
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Tipe
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Kontak
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Diskon / Term
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 dark:text-gray-200 uppercase tracking-wider">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="customers.data.length === 0">
                  <td colspan="6" class="px-6 py-16 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="mt-4 text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada data customer</p>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tambahkan customer pertama Anda</p>
                  </td>
                </tr>
                <tr v-for="customer in customers.data" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex flex-col">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">{{ customer.code }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">{{ customer.name }}</div>
                      <div v-if="customer.city" class="text-xs text-gray-400 dark:text-gray-500">{{ customer.city }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="getTypeBadgeColor(customer.type)"
                    >
                      {{ getTypeLabel(customer.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-col gap-1">
                      <div v-if="customer.phone" class="text-sm text-gray-900 dark:text-white">{{ customer.phone }}</div>
                      <div v-if="customer.email" class="text-xs text-gray-500 dark:text-gray-400">{{ customer.email }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex flex-col gap-1">
                      <div class="text-sm text-gray-900 dark:text-white">Diskon: {{ customer.discount_percentage }}%</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ getPaymentTermLabel(customer.payment_term) }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      v-if="customer.is_active"
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
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <div class="flex justify-end gap-2">
                      <Link
                        :href="`/customers/${customer.id}`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-lg font-medium transition-colors"
                      >
                        <Eye :size="16" />
                        <span>Detail</span>
                      </Link>
                      <Link
                        :href="`/customers/${customer.id}/edit`"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg font-medium transition-colors"
                      >
                        <Edit :size="16" />
                        <span>Edit</span>
                      </Link>
                      <button
                        type="button"
                        @click="deleteCustomer(customer)"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg font-medium transition-colors"
                      >
                        <Trash2 :size="16" />
                        <span>Hapus</span>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="customers.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                <span class="font-medium">{{ customers.from }}</span> - <span class="font-medium">{{ customers.to }}</span> dari <span class="font-medium">{{ customers.total }}</span> data
              </div>
              <div class="flex flex-wrap gap-2">
                <Link
                  v-for="page in customers.last_page"
                  :key="page"
                  :href="`/customers?page=${page}`"
                  :class="[
                    'px-4 py-2 text-sm font-medium rounded-lg transition-all',
                    page === customers.current_page
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