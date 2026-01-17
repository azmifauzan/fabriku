<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';

defineProps<{
    title: string;
    description?: string;
    subtitle?: string;
    createLink?: string;
    createText?: string;
}>();
</script>

<template>
    <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex-1">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">{{ title }}</h1>
            <p v-if="description || subtitle" class="mt-2 text-sm text-gray-600 sm:text-base dark:text-gray-400">
                {{ description || subtitle }}
            </p>
        </div>
        <div v-if="$slots.actions" class="flex gap-2">
            <slot name="actions" />
        </div>
        <Link
            v-else-if="createLink"
            :href="createLink"
            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-indigo-700 hover:shadow-md sm:w-auto"
        >
            <Plus :size="20" />
            <span class="whitespace-nowrap">{{ createText || 'Tambah' }}</span>
        </Link>
    </div>
</template>
