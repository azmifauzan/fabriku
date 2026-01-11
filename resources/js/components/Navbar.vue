<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Sun, Moon, LogOut, User, Menu } from 'lucide-vue-next'
import { useDarkMode } from '@/composables/useDarkMode'

defineProps<{
  user: {
    name: string
    role: string
  } | null
  isMobile: boolean
}>()

const emit = defineEmits<{
  (e: 'toggleSidebar'): void
}>()

const { isDark, toggleDark } = useDarkMode()
</script>

<template>
  <nav class="fixed top-0 left-0 right-0 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 z-50">
    <div class="h-full px-4 flex items-center justify-between">
      <!-- Left Section -->
      <div class="flex items-center gap-3">
        <!-- Mobile Menu Button -->
        <button
          v-if="isMobile"
          @click="emit('toggleSidebar')"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors md:hidden"
          aria-label="Toggle menu"
        >
          <Menu :size="20" class="text-gray-600 dark:text-gray-300" />
        </button>

        <!-- Logo -->
        <div class="flex items-center gap-2">
          <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-lg flex-shrink-0">
            <span class="text-white font-bold text-sm">F</span>
          </div>
          <h1 class="text-lg sm:text-xl font-bold text-indigo-600 dark:text-indigo-400">Fabriku</h1>
        </div>
      </div>

      <!-- Right Section -->
      <div class="flex items-center gap-2 sm:gap-4">
        <!-- Theme Toggle -->
        <button
          @click="toggleDark()"
          class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
          aria-label="Toggle theme"
        >
          <Sun v-if="isDark" :size="20" class="text-gray-600 dark:text-gray-300" />
          <Moon v-else :size="20" class="text-gray-600 dark:text-gray-300" />
        </button>

        <!-- User Info - Desktop -->
        <div v-if="user" class="hidden sm:flex items-center gap-3">
          <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 dark:bg-gray-700 rounded-lg">
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
            class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors"
            aria-label="Logout"
          >
            <LogOut :size="20" />
          </Link>
        </div>

        <!-- User Info - Mobile (Icon Only) -->
        <div v-if="user" class="flex sm:hidden items-center gap-2">
          <button
            class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700"
            aria-label="User menu"
          >
            <User :size="18" class="text-gray-600 dark:text-gray-300" />
          </button>
          <Link
            href="/logout"
            method="post"
            as="button"
            class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors"
            aria-label="Logout"
          >
            <LogOut :size="18" />
          </Link>
        </div>
      </div>
    </div>
  </nav>
</template>
