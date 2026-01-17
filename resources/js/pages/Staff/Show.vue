<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'

interface PreparationOrder {
  id: number
  code: string
  status: string
  created_at: string
}

interface StaffData {
  id: number
  code: string
  name: string
  position: string | null
  phone: string | null
  email: string | null
  is_active: boolean
  created_at: string
  preparationOrders_count: number
}

interface Props {
  staff: StaffData
  recentOrders: PreparationOrder[]
  stats: {
    total_preparations: number
  }
}

defineProps<Props>()
</script>

<template>
  <AppLayout>
    <Head :title="`Detail Staff: ${staff.name}`" />

    <div class="py-6 px-6">
      <div class="mx-auto max-w-7xl">
        <PageHeader
          :title="`Detail Staff: ${staff.name}`"
          :description="`Informasi lengkap staff ${staff.code}`"
          :back-link="{ href: '/staff', text: 'Kembali ke Daftar Staff' }"
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Staff Information -->
          <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Staff</h3>
                  <div class="flex gap-2">
                    <Link
                      href="/staff"
                      class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 dark:border-gray-700 rounded-md text-sm text-gray-700 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                      Kembali ke Daftar
                    </Link>

                    <Link
                      :href="`/staff/${staff.id}/edit`"
                      class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                      Edit
                    </Link>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Staff</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ staff.code }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ staff.name }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Posisi</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ staff.position || '-' }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="staff.is_active 
                          ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                          : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'"
                      >
                        {{ staff.is_active ? 'Aktif' : 'Nonaktif' }}
                      </span>
                    </dd>
                  </div>
                  <div v-if="staff.phone">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ staff.phone }}</dd>
                  </div>
                  <div v-if="staff.email">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ staff.email }}</dd>
                  </div>
                  <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat pada</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ new Date(staff.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Recent Preparation Orders -->
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Order Persiapan Terakhir</h3>
              </div>
              <div class="overflow-x-auto">
                <table v-if="recentOrders.length" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Kode Order
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Status
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Tanggal
                      </th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Aksi
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ order.code }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="{
                            'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300': order.status === 'draft',
                            'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300': order.status === 'pending',
                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': order.status === 'in_progress',
                            'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': order.status === 'completed',
                            'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300': order.status === 'cancelled'
                          }"
                        >
                          {{ order.status === 'draft' ? 'Draft' : order.status === 'pending' ? 'Menunggu' : order.status === 'in_progress' ? 'Sedang Berlangsung' : order.status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {{ new Date(order.created_at).toLocaleDateString('id-ID') }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <Link
                          :href="`/preparation-orders/${order.id}`"
                          class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors"
                        >
                          Lihat Detail
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div v-else class="p-6 text-center text-gray-500 dark:text-gray-400">
                  Belum ada order persiapan untuk staff ini
                </div>
              </div>
            </div>
          </div>

          <!-- Statistics Sidebar -->
          <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Statistik</h3>
                <dl class="space-y-4">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Order Persiapan</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_preparations }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
