<script setup lang="ts">
import { X } from 'lucide-vue-next';

interface Props {
    show: boolean;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    closeable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    show: false,
    maxWidth: '2xl',
    closeable: true,
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const maxWidthClass = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    '2xl': 'max-w-2xl',
}[props.maxWidth];
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
            >
                <!-- Backdrop click handler -->
                <div class="fixed inset-0" @click="close"></div>

                <Transition
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 scale-95"
                    enter-to-class="opacity-100 scale-100"
                    leave-active-class="transition-all duration-200"
                    leave-from-class="opacity-100 scale-100"
                    leave-to-class="opacity-0 scale-95"
                >
                    <div
                        v-if="show"
                        class="relative w-full overflow-hidden rounded-xl bg-white shadow-2xl dark:bg-gray-800"
                        :class="maxWidthClass"
                    >
                        <!-- Close button -->
                        <div v-if="closeable" class="absolute top-4 right-4 z-10">
                            <button
                                type="button"
                                @click="close"
                                class="text-gray-400 transition-colors hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                <X :size="20" />
                            </button>
                        </div>
                        
                        <slot />
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
