<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

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
  <div class="min-h-screen bg-gray-100">
    <Head title="Dashboard" />

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
                class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
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
                class="text-gray-500 hover:text-gray-900 inline-flex items-center px-1 pt-1 text-sm font-medium"
              >
                Cutting Order
              </Link>
            </div>
          </div>
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <span class="text-sm text-gray-700">{{ $page.props.auth.user?.name || 'User' }}</span>
              <span class="text-xs text-gray-500 ml-2">({{ $page.props.auth.user?.role || 'staff' }})</span>
            </div>
            <div class="ml-4">
              <Link
                href="/logout"
                method="post"
                as="button"
                class="text-sm text-red-600 hover:text-red-800"
              >
                Logout
              </Link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Message -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
          <div class="p-6 bg-white border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">
              Selamat Datang di Fabriku!
            </h2>
            <p class="mt-2 text-gray-600">
              Aplikasi manajemen produksi dan penjualan garment untuk UMKM
            </p>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Total Bahan Baku
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.total_materials }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Inventory
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.total_inventory }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Penjualan Bulan Ini
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      Rp {{ stats.total_sales_month.toLocaleString('id-ID') }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
              <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                  <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">
                      Order Pending
                    </dt>
                    <dd class="text-2xl font-semibold text-gray-900">
                      {{ stats.pending_orders }}
                    </dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Coming Soon Section -->
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
              Modul yang Akan Dikembangkan
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">📦 Bahan Baku</h4>
                <p class="text-sm text-gray-600 mt-1">Manajemen material dan penerimaan</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">✂️ Pemotongan</h4>
                <p class="text-sm text-gray-600 mt-1">Cutting orders dan hasil</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">🧵 Produksi</h4>
                <p class="text-sm text-gray-600 mt-1">Jahit internal & eksternal</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">📊 Inventory</h4>
                <p class="text-sm text-gray-600 mt-1">Gudang dan lokasi penyimpanan</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">💰 Penjualan</h4>
                <p class="text-sm text-gray-600 mt-1">Order dan payment tracking</p>
              </div>
              <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-900">📈 Laporan</h4>
                <p class="text-sm text-gray-600 mt-1">Analytics dan reporting</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
