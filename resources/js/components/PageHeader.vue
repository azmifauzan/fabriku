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
  <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex-1">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">{{ title }}</h1>
      <p v-if="description || subtitle" class="mt-2 text-sm sm:text-base text-gray-600 dark:text-gray-400">
        {{ description || subtitle }}
      </p>
    </div>
    <div v-if="$slots.actions" class="flex gap-2">
      <slot name="actions" />
    </div>
    <Link
      v-else-if="createLink"
      :href="createLink"
      class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow-md transition-all duration-200"
    >
      <Plus :size="20" />
      <span class="whitespace-nowrap">{{ createText || 'Tambah' }}</span>
    </Link>
  </div>
</template>
