<script setup lang="ts">
import { ref } from 'vue'
import { Search, X } from 'lucide-vue-next'

interface FilterField {
  name: string
  label: string
  type: 'text' | 'select'
  placeholder?: string
  options?: Array<{ value: string; label: string }>
  value?: string
}

const props = defineProps<{
  fields: FilterField[]
  searchPlaceholder?: string
  onFilter?: (filters: Record<string, string>) => void
}>()

const filters = ref<Record<string, string>>({})

// Initialize filters
props.fields.forEach((field) => {
  if (field.value) {
    filters.value[field.name] = field.value
  }
})

const applyFilters = () => {
  if (props.onFilter) {
    props.onFilter(filters.value)
  }
}

const clearFilters = () => {
  filters.value = {}
  applyFilters()
}

const hasActiveFilters = () => {
  return Object.values(filters.value).some((value) => value !== '' && value !== undefined)
}
</script>

<template>
  <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 border border-gray-200 dark:border-gray-700">
    <form @submit.prevent="applyFilters" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="field in fields" :key="field.name">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ field.label }}
          </label>

          <!-- Text Input -->
          <div v-if="field.type === 'text'" class="relative">
            <Search
              :size="18"
              class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
            />
            <input
              v-model="filters[field.name]"
              type="text"
              :placeholder="field.placeholder"
              class="w-full pl-10 pr-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-sm transition-all"
              @keyup.enter="applyFilters"
            />
          </div>

          <!-- Select Input -->
          <select
            v-else-if="field.type === 'select'"
            v-model="filters[field.name]"
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-700 dark:text-white text-sm transition-all"
          >
            <option value="">{{ field.placeholder || 'Semua' }}</option>
            <option
              v-for="option in field.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-2">
        <button
          v-if="hasActiveFilters()"
          type="button"
          @click="clearFilters"
          class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
        >
          <X :size="16" />
          Reset
        </button>
        <button
          type="submit"
          class="inline-flex items-center gap-2 px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow-md transition-all"
        >
          <Search :size="16" />
          Cari
        </button>
      </div>
    </form>
  </div>
</template>
