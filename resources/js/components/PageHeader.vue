<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'

defineProps<{
  title: string
  description?: string
  subtitle?: string
  createLink?: string
  createText?: string
}>()
</script>

<template>
  <div class="mb-4 sm:mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
    <div class="flex-1">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">{{ title }}</h2>
      <p v-if="description || subtitle" class="mt-1 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
        {{ description || subtitle }}
      </p>
    </div>
    <Link
      v-if="createLink"
      :href="createLink"
      class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors"
    >
      <Plus :size="18" />
      <span class="whitespace-nowrap">{{ createText || 'Tambah' }}</span>
    </Link>
  </div>
</template>
