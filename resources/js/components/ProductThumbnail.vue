<script setup lang="ts">
defineProps<{
    imageUrl?: string | null;
}>();
</script>

<template>
    <div
        class="flex items-center justify-center overflow-hidden rounded-md border border-gray-100 bg-gray-100 dark:border-gray-700 dark:bg-gray-700"
    >
        <img v-if="imageUrl" :src="imageUrl" alt="" class="h-full w-full object-cover" />
        <svg v-else class="h-1/2 w-1/2 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
            />
        </svg>
    </div>
</template>
