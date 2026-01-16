<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useSweetAlert } from '@/composables/useSweetAlert'

interface Material {
  material_id: number
  material_name: string
  quantity: number
  unit: string
}

interface Pattern {
  id: number
  code: string
  name: string
  category: string
}

interface Staff {
  id: number
  code: string
  name: string
  position: string | null
}

interface ProductionOrder {
  id: number
  order_number: string
  status: string
  type: string
  quantity_requested: number
}

interface PreparationOrder {
  id: number
  order_number: string
  pattern: Pattern | null
  prepared_by_staff: Staff | null
  order_date: string
  output_quantity: number
  output_unit: string
  materials_used: Material[]
  notes: string | null
  status: string
  production_orders: ProductionOrder[]
  can_be_edited: boolean
  can_be_deleted: boolean
}

const props = defineProps<{
  order: PreparationOrder
}>()

const { confirmDelete, showSuccess } = useSweetAlert()

const statusBadge = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300',
    in_progress: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300',
    completed: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
    cancelled: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
  }

  return colors[status] || 'bg-gray-100 text-gray-800'
}

const statusLabel = (status: string) => {
  const labels: Record<string, string> = {
    draft: 'Draft',
    in_progress: 'In Progress',
    completed: 'Completed',
    cancelled: 'Cancelled',
  }

  return labels[status] || status
}

const handleDelete = async () => {
  const result = await confirmDelete(
    'Hapus Preparation Order',
    `Apakah Anda yakin ingin menghapus preparation order ${props.order.order_number}?`
  )

  if (result.isConfirmed) {
    router.delete(`/preparation-orders/${props.order.id}`, {
      onSuccess: () => {
        showSuccess('Berhasil!', 'Preparation order berhasil dihapus')
      }
    })
  }
}
</script>

<template>
  <AppLayout>
    <Head :title="`Detail Preparation Order ${order.order_number}`" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6 flex items-start justify-between gap-4">
          <div>
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              Preparation Order {{ order.order_number }}
            </h2>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              <template v-if="order.pattern">
                Pattern: {{ order.pattern.name }} ({{ order.pattern.code }})
              </template>
              <template v-else>
                Custom Preparation
              </template>
            </p>
          </div>
          <div class="flex items-center gap-3">
            <Link 
              href="/preparation-orders" 
              class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
            >
              ← Kembali
            </Link>
            <Link
              v-if="order.can_be_edited"
              :href="`/preparation-orders/${order.id}/edit`"
              class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-semibold text-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-all shadow-sm"
            >
              Edit
            </Link>
            <button
              v-if="order.can_be_deleted"
              @click="handleDelete"
              class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-semibold text-sm hover:bg-red-700 transition-all shadow-sm"
            >
              Hapus
            </button>
          </div>
        </div>

        <!-- Main Info Card -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 mb-6">
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Detail</h3>
              <span :class="statusBadge(order.status)" class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full">
                {{ statusLabel(order.status) }}
              </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Tanggal Order</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ new Date(order.order_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Penanggung Jawab</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ order.prepared_by_staff?.name || '-' }}
                </p>
                <p v-if="order.prepared_by_staff?.position" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                  {{ order.prepared_by_staff.position }}
                </p>
              </div>

              <div class="bg-blue-50 dark:bg-blue-900/30 rounded-lg p-4">
                <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Output Hasil</p>
                <p class="text-lg font-bold text-blue-700 dark:text-blue-300">
                  {{ order.output_quantity }} {{ order.output_unit }}
                </p>
              </div>
            </div>

            <div v-if="order.notes" class="mt-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-4">
              <p class="text-xs text-amber-600 dark:text-amber-400 mb-1 font-medium">Catatan</p>
              <p class="text-sm text-gray-900 dark:text-gray-100">{{ order.notes }}</p>
            </div>
          </div>
        </div>

        <!-- Materials Used -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 mb-6">
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Material yang Digunakan</h3>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Material
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Jumlah
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Satuan
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr v-if="order.materials_used.length === 0">
                    <td colspan="3" class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400 text-center">
                      Tidak ada material tercatat
                    </td>
                  </tr>
                  <tr 
                    v-for="(material, idx) in order.materials_used" 
                    :key="idx" 
                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                  >
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                      {{ material.material_name }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 text-right font-mono">
                      {{ material.quantity }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                      {{ material.unit }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Related Production Orders -->
        <div 
          v-if="order.production_orders && order.production_orders.length > 0" 
          class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700"
        >
          <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Production Orders Terkait</h3>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Order Number
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Tipe
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Status
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Target Qty
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                      Aksi
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr 
                    v-for="po in order.production_orders" 
                    :key="po.id" 
                    class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
                  >
                    <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                      {{ po.order_number }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 capitalize">
                      {{ po.type }}
                    </td>
                    <td class="px-4 py-3">
                      <span :class="statusBadge(po.status)" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                        {{ statusLabel(po.status) }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300 text-right font-mono">
                      {{ po.quantity_requested }}
                    </td>
                    <td class="px-4 py-3 text-right">
                      <Link 
                        :href="`/production-orders/${po.id}`" 
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 text-sm font-medium transition-colors"
                      >
                        Lihat Detail
                      </Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
