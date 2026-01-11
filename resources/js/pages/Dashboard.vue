<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import {
  Package,
  Box,
  DollarSign,
  ClipboardList,
  AlertTriangle,
  TrendingUp,
  ShoppingCart,
  Factory,
} from 'lucide-vue-next'
import { computed } from 'vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

const { term, termLower } = useBusinessContext()

const materialLabel = computed(() => term('material', 'Bahan Baku'))

interface Activity {
  type: 'sale' | 'production'
  description: string
  amount?: number
  quantity?: number
  status: string
  date: string
}

interface Stats {
  total_materials: number
  low_stock_materials: number
  total_inventory: number
  low_stock_inventory: number
  total_sales_month: number
  total_sales_count: number
  pending_production: number
  pending_cutting: number
}

interface TopProduct {
  sku: string
  name: string
  total_sold: number
  total_revenue: number
}

interface Material {
  id: number
  code: string
  name: string
  quantity: number
  unit: string
  minimum_stock: number
}

interface InventoryItem {
  id: number
  sku: string
  name: string
  quantity: number
  reserved_quantity: number
  minimum_stock: number
}

defineProps<{
  stats: Stats
  salesTrend?: Array<{ date: string; total: number; count: number }>
  topProducts?: TopProduct[]
  recentActivities?: Activity[]
  lowStockMaterials?: Material[]
  lowStockInventory?: InventoryItem[]
}>()

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const getStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    draft: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
  }
  return classes[status] || classes.draft
}
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto space-y-6">
        <!-- Welcome Message -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              Dashboard Fabriku
            </h2>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
              Ringkasan bisnis dan aktivitas terkini
            </p>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <!-- Total Materials -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-indigo-500 rounded-lg p-3">
                  <Package :size="24" class="text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Total {{ materialLabel }}
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ stats.total_materials }}
                  </dd>
                  <p
                    v-if="stats.low_stock_materials > 0"
                    class="text-xs text-red-600 dark:text-red-400 mt-1"
                  >
                    {{ stats.low_stock_materials }} low stock
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Total Inventory -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                  <Box :size="24" class="text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Total Inventory
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ stats.total_inventory }}
                  </dd>
                  <p
                    v-if="stats.low_stock_inventory > 0"
                    class="text-xs text-red-600 dark:text-red-400 mt-1"
                  >
                    {{ stats.low_stock_inventory }} low stock
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sales This Month -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-blue-500 rounded-lg p-3">
                  <ShoppingCart :size="24" class="text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Penjualan Bulan Ini
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ formatCurrency(stats.total_sales_month) }}
                  </dd>
                  <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    {{ stats.total_sales_count }} orders
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Pending Production -->
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                  <Factory :size="24" class="text-white" />
                </div>
                <div class="flex-1 min-w-0">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Produksi Aktif
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ stats.pending_production }}
                  </dd>
                  <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    {{ stats.pending_cutting }} cutting
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Top Products -->
          <div
            v-if="topProducts && topProducts.length > 0"
            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg lg:col-span-2"
          >
            <div class="p-6">
              <div class="flex items-center gap-2 mb-4">
                <TrendingUp :size="20" class="text-indigo-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Produk Terlaris (30 Hari Terakhir)
                </h3>
              </div>
              <div class="space-y-3">
                <div
                  v-for="product in topProducts"
                  :key="product.sku"
                  class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0"
                >
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ product.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.sku }}</p>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                      {{ product.total_sold }} pcs
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      {{ formatCurrency(product.total_revenue) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activities -->
          <div
            v-if="recentActivities && recentActivities.length > 0"
            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
          >
            <div class="p-6">
              <div class="flex items-center gap-2 mb-4">
                <ClipboardList :size="20" class="text-indigo-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Aktivitas Terkini
                </h3>
              </div>
              <div class="space-y-3">
                <div
                  v-for="(activity, idx) in recentActivities"
                  :key="idx"
                  class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0"
                >
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-900 dark:text-white">
                        {{ activity.description }}
                      </p>
                      <div class="flex items-center gap-2 mt-1">
                        <span
                          :class="getStatusBadgeClass(activity.status)"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                        >
                          {{ activity.status }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                          {{ formatDate(activity.date) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Low Stock Alerts -->
        <div
          v-if="
            (lowStockMaterials && lowStockMaterials.length > 0) ||
            (lowStockInventory && lowStockInventory.length > 0)
          "
          class="grid grid-cols-1 lg:grid-cols-2 gap-6"
        >
          <!-- Low Stock Materials -->
          <div
            v-if="lowStockMaterials && lowStockMaterials.length > 0"
            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
          >
            <div class="p-6">
              <div class="flex items-center gap-2 mb-4">
                <AlertTriangle :size="20" class="text-red-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  {{ materialLabel }} Low Stock
                </h3>
              </div>
              <div class="space-y-3">
                <div
                  v-for="material in lowStockMaterials"
                  :key="material.id"
                  class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0"
                >
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ material.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ material.code }}</p>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                      {{ material.current_stock }} {{ material.unit }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Min: {{ material.reorder_point }} {{ material.unit }}
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <Link
                  href="/materials"
                  class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                >
                  Lihat Semua {{ materialLabel }} →
                </Link>
              </div>
            </div>
          </div>

          <!-- Low Stock Inventory -->
          <div
            v-if="lowStockInventory && lowStockInventory.length > 0"
            class="bg-white dark:bg-gray-800 shadow-sm rounded-lg"
          >
            <div class="p-6">
              <div class="flex items-center gap-2 mb-4">
                <AlertTriangle :size="20" class="text-red-500" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Inventory Low Stock
                </h3>
              </div>
              <div class="space-y-3">
                <div
                  v-for="item in lowStockInventory"
                  :key="item.id"
                  class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0"
                >
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                      {{ item.name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ item.sku }}</p>
                  </div>
                  <div class="text-right ml-4">
                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                      {{ item.current_stock - item.reserved_stock }} pcs
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      Min: {{ item.minimum_stock }} pcs
                    </p>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <Link
                  href="/inventory/items"
                  class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                >
                  Lihat Semua Inventory →
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
