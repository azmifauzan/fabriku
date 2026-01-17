<script setup lang="ts">
import { useDarkMode } from '@/composables/useDarkMode';
import { Link } from '@inertiajs/vue3';
import { LogOut, Menu, Moon, Sun, User } from 'lucide-vue-next';

defineProps<{
    user: {
        name: string;
        role: string;
    } | null;
    isMobile: boolean;
}>();

const emit = defineEmits<{
    (e: 'toggleSidebar'): void;
}>();

const { isDark, toggleDark } = useDarkMode();
</script>

<template>
    <nav class="fixed top-0 right-0 left-0 z-50 h-16 border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <div class="flex h-full items-center justify-between px-4">
            <!-- Left Section -->
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Button -->
                <button
                    v-if="isMobile"
                    @click="emit('toggleSidebar')"
                    class="rounded-lg p-2 transition-colors hover:bg-gray-100 md:hidden dark:hover:bg-gray-700"
                    aria-label="Toggle menu"
                >
                    <Menu :size="20" class="text-gray-600 dark:text-gray-300" />
                </button>

                <!-- Logo -->
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-600">
                        <span class="text-sm font-bold text-white">F</span>
                    </div>
                    <h1 class="text-lg font-bold text-indigo-600 sm:text-xl dark:text-indigo-400">Fabriku</h1>
                </div>
            </div>

            <!-- Right Section -->
            <div class="flex items-center gap-2 sm:gap-4">
                <!-- Theme Toggle -->
                <button
                    @click="toggleDark()"
                    class="rounded-lg p-2 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                    aria-label="Toggle theme"
                >
                    <Sun v-if="isDark" :size="20" class="text-gray-600 dark:text-gray-300" />
                    <Moon v-else :size="20" class="text-gray-600 dark:text-gray-300" />
                </button>

                <!-- User Info - Desktop -->
                <div v-if="user" class="hidden items-center gap-3 sm:flex">
                    <div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-1.5 dark:bg-gray-700">
                        <User :size="16" class="text-gray-500 dark:text-gray-400" />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ user.name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ user.role }}</span>
                        </div>
                    </div>

                    <!-- Logout -->
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                        aria-label="Logout"
                    >
                        <LogOut :size="20" />
                    </Link>
                </div>

                <!-- User Info - Mobile (Icon Only) -->
                <div v-if="user" class="flex items-center gap-2 sm:hidden">
                    <button class="rounded-lg bg-gray-50 p-2 dark:bg-gray-700" aria-label="User menu">
                        <User :size="18" class="text-gray-600 dark:text-gray-300" />
                    </button>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                        aria-label="Logout"
                    >
                        <LogOut :size="18" />
                    </Link>
                </div>
            </div>
        </div>
    </nav>
</template>
