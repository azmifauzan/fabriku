<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';
import { ref } from 'vue';

interface FilterField {
    name: string;
    label: string;
    type: 'text' | 'select';
    placeholder?: string;
    options?: Array<{ value: string; label: string }>;
    value?: string;
}

const props = defineProps<{
    fields: FilterField[];
    searchPlaceholder?: string;
    onFilter?: (filters: Record<string, string>) => void;
}>();

const filters = ref<Record<string, string>>({});

// Initialize filters
props.fields.forEach((field) => {
    if (field.value) {
        filters.value[field.name] = field.value;
    }
});

const applyFilters = () => {
    if (props.onFilter) {
        props.onFilter(filters.value);
    }
};

const clearFilters = () => {
    filters.value = {};
    applyFilters();
};

const hasActiveFilters = () => {
    return Object.values(filters.value).some((value) => value !== '' && value !== undefined);
};
</script>

<template>
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <form @submit.prevent="applyFilters" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="field in fields" :key="field.name">
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ field.label }}
                    </label>

                    <!-- Text Input -->
                    <div v-if="field.type === 'text'" class="relative">
                        <Search :size="18" class="absolute top-1/2 left-3 -translate-y-1/2 transform text-gray-400" />
                        <input
                            v-model="filters[field.name]"
                            type="text"
                            :placeholder="field.placeholder"
                            class="w-full rounded-lg border border-gray-300 py-2.5 pr-3 pl-10 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Select Input -->
                    <select
                        v-else-if="field.type === 'select'"
                        v-model="filters[field.name]"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">{{ field.placeholder || 'Semua' }}</option>
                        <option v-for="option in field.options" :key="option.value" :value="option.value">
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
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                >
                    <X :size="16" />
                    Reset
                </button>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md"
                >
                    <Search :size="16" />
                    Cari
                </button>
            </div>
        </form>
    </div>
</template>
