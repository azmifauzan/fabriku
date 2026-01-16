<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  label: string
  modelValue?: string | number | boolean
  type?: 'text' | 'email' | 'tel' | 'number' | 'date' | 'textarea' | 'select' | 'checkbox'
  placeholder?: string
  required?: boolean
  error?: string
  hint?: string
  options?: Array<{ value: string | number; label: string }>
  disabled?: boolean
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  required: false,
  disabled: false,
  rows: 4,
})

const emit = defineEmits<{
  'update:modelValue': [value: string | number | boolean]
}>()

const inputValue = computed({
  get: () => props.modelValue,
  set: (value) => {
    if (value !== undefined) {
      emit('update:modelValue', value)
    }
  },
})

const inputClasses = computed(() => [
  'block w-full px-4 py-3 rounded-lg border transition-all duration-200',
  'text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500',
  'focus:ring-2 focus:ring-offset-0',
  props.error
    ? 'border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-200 dark:focus:ring-red-800'
    : 'border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-200 dark:focus:ring-indigo-800',
  'bg-white dark:bg-gray-700',
  'disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:cursor-not-allowed',
])

const labelClasses = 'block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2'
</script>

<template>
  <div class="space-y-2">
    <label v-if="label && type !== 'checkbox'" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>

    <div v-if="type === 'checkbox'" :class="labelClasses">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </div>

    <!-- Text/Email/Tel/Number/Date Input -->
    <input
      v-if="['text', 'email', 'tel', 'number', 'date'].includes(type)"
      v-model="inputValue as string | number"
      :type="type"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :class="inputClasses"
    />

    <!-- Textarea -->
    <textarea
      v-else-if="type === 'textarea'"
      v-model="inputValue as string"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :rows="rows"
      :class="inputClasses"
    />

    <!-- Select -->
    <select
      v-else-if="type === 'select'"
      v-model="inputValue as string | number"
      :required="required"
      :disabled="disabled"
      :class="inputClasses"
    >
      <slot name="options">
        <option v-for="option in options" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </slot>
    </select>

    <!-- Checkbox -->
    <div v-else-if="type === 'checkbox'">
      <label class="flex items-center cursor-pointer gap-3">
        <input
          v-model="inputValue as boolean"
          type="checkbox"
          :disabled="disabled"
          class="w-5 h-5 text-indigo-600 border-gray-300 dark:border-gray-600 rounded focus:ring-indigo-500 focus:ring-2 dark:bg-gray-700 disabled:cursor-not-allowed flex-shrink-0"
        />
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
          {{ label }}
          <span v-if="required" class="text-red-500 ml-1">*</span>
        </span>
      </label>
    </div>

    <!-- Error Message -->
    <p v-if="error" class="text-sm text-red-600 dark:text-red-400 flex items-start gap-1">
      <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
      <span>{{ error }}</span>
    </p>

    <!-- Hint -->
    <p v-if="hint && !error" class="text-sm text-gray-500 dark:text-gray-400">
      {{ hint }}
    </p>
  </div>
</template>
