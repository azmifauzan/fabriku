<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Material {
  id: number
  code: string
  name: string
  unit: string
  created_at: string
}

interface MaterialTypeData {
  id: number
  code: string
  name: string
  description: string | null
  sort_order: number
  is_active: boolean
  created_at: string
  materials_count: number
}

interface Props {
  materialType: MaterialTypeData
  recentMaterials: Material[]
  stats: {
    total_materials: number
  }
}

defineProps<Props>()
</script>

<template>
  <AppLayout>
    <Head :title="`Detail Jenis Bahan: ${materialType.name}`" />

    <div class="py-6 px-6">
      <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex items-center justify-between">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
              Detail Jenis Bahan: {{ materialType.name }}
            </h1>
            <p class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
              Informasi lengkap jenis bahan {{ materialType.code }}
            </p>
          </div>
          <Link
            href="/material-types"
            class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300 transition-colors"
          >
            ← Kembali
          </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Material Type Information -->
          <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Informasi Jenis Bahan</h3>
                  <div class="flex gap-2">
                    <Link
                      :href="`/material-types/${materialType.id}/edit`"
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
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Kode Jenis Bahan</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ materialType.code }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ materialType.name }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Urutan</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ materialType.sort_order }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="materialType.is_active 
                          ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300'
                          : 'bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-300'"
                      >
                        {{ materialType.is_active ? 'Aktif' : 'Nonaktif' }}
                      </span>
                    </dd>
                  </div>
                  <div v-if="materialType.description" class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Deskripsi</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ materialType.description }}</dd>
                  </div>
                  <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Dibuat pada</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                      {{ new Date(materialType.created_at).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Recent Materials -->
            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Bahan-Bahan Terbaru</h3>
              </div>
              <div class="overflow-x-auto">
                <table v-if="recentMaterials.length" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                  <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Kode
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Nama
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Unit
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
                    <tr v-for="material in recentMaterials" :key="material.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ material.code }}
                      </td>
                      <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                        {{ material.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {{ material.unit }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                        {{ new Date(material.created_at).toLocaleDateString('id-ID') }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <Link
                          :href="`/materials/${material.id}`"
                          class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors"
                        >
                          Lihat Detail
                        </Link>
                      </td>
                    </tr>
                  </tbody>
                </table>
                <div v-else class="p-6 text-center text-gray-500 dark:text-gray-400">
                  Belum ada bahan untuk jenis ini
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
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bahan</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ stats.total_materials }}</dd>
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
