<script setup lang="ts">
import { X } from 'lucide-vue-next';

interface Props {
    show: boolean;
    title?: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'info';
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi',
    confirmText: 'Ya, Lanjutkan',
    cancelText: 'Batal',
    variant: 'danger',
});

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();

const variantClasses = {
    danger: 'bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600',
    warning: 'bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-500 dark:hover:bg-yellow-600',
    info: 'bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600',
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
                @click.self="emit('cancel')"
            >
                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div v-if="show" class="w-full max-w-md overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-800">
                        <!-- Header -->
                        <div class="flex items-center justify-between border-b border-gray-200 p-6 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ title }}
                            </h3>
                            <button
                                type="button"
                                @click="emit('cancel')"
                                class="text-gray-400 transition-colors hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <X :size="20" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="p-6">
                            <p class="text-gray-700 dark:text-gray-300">
                                {{ message }}
                            </p>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-900/50"
                        >
                            <button
                                type="button"
                                @click="emit('cancel')"
                                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                {{ cancelText }}
                            </button>
                            <button
                                type="button"
                                @click="emit('confirm')"
                                :class="['rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors', variantClasses[variant]]"
                            >
                                {{ confirmText }}
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
