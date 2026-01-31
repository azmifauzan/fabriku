<script setup lang="ts">
import { useDarkMode } from '@/composables/useDarkMode';
import { Link, usePage } from '@inertiajs/vue3';
import { AlertCircle, ArrowUpCircle, Clock, LogOut, Menu, Moon, Sun, User } from 'lucide-vue-next';
import { computed } from 'vue';

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

const page = usePage();
const tenant = computed(() => page.props.tenant as any);

const daysRemaining = computed(() => {
    if (!tenant.value?.subscription_expires_at) return 0;
    const expiresAt = new Date(tenant.value.subscription_expires_at);
    const now = new Date();
    const diff = expiresAt.getTime() - now.getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
});

const isTrialActive = computed(() => {
    return tenant.value?.subscription_plan === 'trial' && !tenant.value?.is_expired;
});

const isExpired = computed(() => {
    return tenant.value?.is_expired === true;
});
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
                <!-- Membership Status Badge -->
                <div v-if="tenant">
                    <!-- Trial Active - Show Days Remaining -->
                    <Link 
                        v-if="isTrialActive"
                        href="/subscription"
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 transition-colors dark:bg-amber-900/20 dark:hover:bg-amber-900/30"
                    >
                        <Clock :size="16" class="text-amber-600 dark:text-amber-400" />
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-300">
                            Trial {{ daysRemaining }} hari lagi
                        </span>
                    </Link>
                    
                    <!-- Expired - Show Warning -->
                    <Link 
                        v-else-if="isExpired"
                        href="/subscription"
                        class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50 hover:bg-red-100 transition-colors dark:bg-red-900/20 dark:hover:bg-red-900/30"
                    >
                        <AlertCircle :size="16" class="text-red-600 dark:text-red-400" />
                        <span class="text-xs font-medium text-red-700 dark:text-red-300">
                            Expired - Read Only
                        </span>
                    </Link>
                    
                    <!-- Mobile versions -->
                    <Link 
                        v-if="isTrialActive"
                        href="/subscription"
                        class="sm:hidden rounded-lg p-2 bg-amber-50 hover:bg-amber-100 transition-colors dark:bg-amber-900/20"
                    >
                        <Clock :size="18" class="text-amber-600 dark:text-amber-400" />
                    </Link>
                    
                    <Link 
                        v-else-if="isExpired"
                        href="/subscription"
                        class="sm:hidden rounded-lg p-2 bg-red-50 hover:bg-red-100 transition-colors dark:bg-red-900/20"
                    >
                        <AlertCircle :size="18" class="text-red-600 dark:text-red-400" />
                    </Link>
                </div>

                <!-- Upgrade Button (Show for trial users) -->
                <Link
                    v-if="isTrialActive"
                    href="/subscription"
                    class="hidden md:flex items-center gap-2 px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium text-sm transition-all shadow-sm hover:shadow"
                >
                    <ArrowUpCircle :size="16" />
                    <span>Upgrade</span>
                </Link>

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
