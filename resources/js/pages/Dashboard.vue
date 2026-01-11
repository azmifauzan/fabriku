<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Package, Box, DollarSign, ClipboardList } from 'lucide-vue-next'
import { computed } from 'vue'
import { useBusinessContext } from '@/composables/useBusinessContext'

const { term, termLower } = useBusinessContext()

const materialLabel = computed(() => term('material', 'Bahan Baku'))
const patternLabel = computed(() => term('pattern', 'Pattern'))
const preparationLabel = computed(() => term('preparation', 'Persiapan'))
const productionLabel = computed(() => term('production', 'Produksi'))

defineProps<{
  stats: {
    total_materials: number
    total_inventory: number
    total_sales_month: number
    pending_orders: number
  }
}>()
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="py-6 px-6">
      <div class="max-w-7xl mx-auto">
        <!-- Welcome Message -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
          <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              Selamat Datang di Fabriku!
            </h2>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Aplikasi manajemen produksi dan penjualan untuk UMKM
            </p>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-indigo-500 rounded-lg p-3">
                  <Package :size="24" class="text-white" />
                </div>
                <div class="flex-1">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Total {{ materialLabel }}
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ stats.total_materials }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-green-500 rounded-lg p-3">
                  <Box :size="24" class="text-white" />
                </div>
                <div class="flex-1">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Inventory
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ stats.total_inventory }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-yellow-500 rounded-lg p-3">
                  <DollarSign :size="24" class="text-white" />
                </div>
                <div class="flex-1">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Penjualan Bulan Ini
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    Rp {{ stats.total_sales_month.toLocaleString('id-ID') }}
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
            <div class="p-6">
              <div class="flex items-center gap-4">
                <div class="flex-shrink-0 bg-red-500 rounded-lg p-3">
                  <ClipboardList :size="24" class="text-white" />
                </div>
                <div class="flex-1">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                    Order Pending
                  </dt>
                  <dd class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">
                    {{ stats.pending_orders }}
                  </dd>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Coming Soon Section -->
        <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
              Modul yang Akan Dikembangkan
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white">📦 {{ materialLabel }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Manajemen material dan penerimaan</p>
              </div>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white">✂️ {{ preparationLabel }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                  {{ termLower('preparation_order', 'cutting order') }} dan hasil
                </p>
              </div>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white">🧵 {{ productionLabel }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ termLower('production_order', 'production order') }} internal & eksternal</p>
              </div>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white">📊 Inventory</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Gudang dan lokasi penyimpanan</p>
              </div>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white">💰 Penjualan</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Order dan payment tracking</p>
              </div>
              <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 dark:text-white">📈 Laporan</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Analytics dan reporting</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
