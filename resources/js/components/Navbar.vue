<script setup lang="ts">
import { useDarkMode } from '@/composables/useDarkMode';
import { Link, usePage } from '@inertiajs/vue3';
import { AlertCircle, ArrowUpCircle, ChevronDown, Clock, LogOut, Menu, MessageCircle, Moon, Sun, User } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

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
const userMenuOpen = ref(false);
const userMenuDesktopRef = ref<HTMLElement | null>(null);
const userMenuMobileRef = ref<HTMLElement | null>(null);

const handleClickOutsideUserMenu = (event: MouseEvent) => {
    const target = event.target as Node;
    if (userMenuDesktopRef.value?.contains(target) || userMenuMobileRef.value?.contains(target)) {
        return;
    }
    userMenuOpen.value = false;
};

onMounted(() => document.addEventListener('click', handleClickOutsideUserMenu));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutsideUserMenu));

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

const companyName = computed(() => {
    return tenant.value?.company_name || tenant.value?.name || 'Fabriku';
});

const companyLogo = computed(() => {
    return tenant.value?.logo || null;
});

const companyInitials = computed(() => {
    const name = companyName.value;
    const words = name.split(' ').filter((word) => word.length > 0);
    if (words.length === 0) return 'F';
    if (words.length === 1) return words[0].substring(0, 2).toUpperCase();
    return (words[0][0] + words[1][0]).toUpperCase();
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
                    <div v-if="companyLogo" class="flex h-6 w-6 flex-shrink-0 overflow-hidden rounded-lg sm:h-8 sm:w-8">
                        <img :src="companyLogo" :alt="companyName" class="h-full w-full object-contain" />
                    </div>
                    <div v-else class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-lg bg-indigo-600 sm:h-8 sm:w-8">
                        <span class="text-xs font-bold text-white sm:text-sm">{{ companyInitials }}</span>
                    </div>
                    <div class="flex flex-col">
                        <h1 class="text-lg leading-tight font-bold text-indigo-600 sm:text-xl dark:text-indigo-400">{{ companyName }}</h1>
                    </div>
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
                        class="hidden items-center gap-2 rounded-lg bg-amber-50 px-3 py-1.5 transition-colors hover:bg-amber-100 sm:flex dark:bg-amber-900/20 dark:hover:bg-amber-900/30"
                    >
                        <Clock :size="16" class="text-amber-600 dark:text-amber-400" />
                        <span class="text-xs font-medium text-amber-700 dark:text-amber-300"> Trial {{ daysRemaining }} hari lagi </span>
                    </Link>

                    <!-- Expired - Show Warning -->
                    <Link
                        v-else-if="isExpired"
                        href="/subscription"
                        class="hidden items-center gap-2 rounded-lg bg-red-50 px-3 py-1.5 transition-colors hover:bg-red-100 sm:flex dark:bg-red-900/20 dark:hover:bg-red-900/30"
                    >
                        <AlertCircle :size="16" class="text-red-600 dark:text-red-400" />
                        <span class="text-xs font-medium text-red-700 dark:text-red-300"> Expired - Read Only </span>
                    </Link>

                    <!-- Mobile versions -->
                    <Link
                        v-if="isTrialActive"
                        href="/subscription"
                        class="rounded-lg bg-amber-50 p-2 transition-colors hover:bg-amber-100 sm:hidden dark:bg-amber-900/20"
                    >
                        <Clock :size="18" class="text-amber-600 dark:text-amber-400" />
                    </Link>

                    <Link
                        v-else-if="isExpired"
                        href="/subscription"
                        class="rounded-lg bg-red-50 p-2 transition-colors hover:bg-red-100 sm:hidden dark:bg-red-900/20"
                    >
                        <AlertCircle :size="18" class="text-red-600 dark:text-red-400" />
                    </Link>
                </div>

                <!-- Upgrade Button (Show for trial users) -->
                <Link
                    v-if="isTrialActive"
                    href="/subscription"
                    class="hidden items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow md:flex"
                >
                    <ArrowUpCircle :size="16" />
                    <span>Upgrade</span>
                </Link>

                <!-- Theme Toggle -->
                <button
                    @click="toggleDark()"
                    class="rounded-lg p-2.5 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                    aria-label="Toggle theme"
                >
                    <Sun v-if="isDark" :size="20" class="text-gray-600 dark:text-gray-300" />
                    <Moon v-else :size="20" class="text-gray-600 dark:text-gray-300" />
                </button>

                <!-- User Info - Desktop -->
                <div v-if="user" ref="userMenuDesktopRef" class="relative hidden sm:flex">
                    <button
                        @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-1.5 transition-colors hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600"
                    >
                        <User :size="16" class="text-gray-500 dark:text-gray-400" />
                        <div class="flex flex-col text-left">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ user.name }}</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ user.role }}</span>
                        </div>
                        <ChevronDown :size="16" class="text-gray-400 transition-transform" :class="{ 'rotate-180': userMenuOpen }" />
                    </button>

                    <!-- Dropdown Menu -->
                    <div
                        v-show="userMenuOpen"
                        class="absolute top-full right-0 mt-2 w-56 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.role }}</p>
                        </div>

                        <Link
                            href="/settings/telegram"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                            @click="userMenuOpen = false"
                        >
                            <MessageCircle :size="16" class="text-indigo-500" />
                            <span>Hubungkan Telegram</span>
                        </Link>

                        <div class="border-t border-gray-200 dark:border-gray-700">
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                @click="userMenuOpen = false"
                            >
                                <LogOut :size="16" />
                                <span>Logout</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- User Info - Mobile (Icon Only) -->
                <div v-if="user" ref="userMenuMobileRef" class="relative flex items-center gap-2 sm:hidden">
                    <button @click="userMenuOpen = !userMenuOpen" class="rounded-lg bg-gray-50 p-2.5 dark:bg-gray-700" aria-label="User menu">
                        <User :size="18" class="text-gray-600 dark:text-gray-300" />
                    </button>

                    <!-- Mobile Dropdown Menu -->
                    <div
                        v-show="userMenuOpen"
                        class="absolute top-full right-0 mt-2 w-56 rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ user.role }}</p>
                        </div>

                        <Link
                            href="/settings/telegram"
                            class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700"
                            @click="userMenuOpen = false"
                        >
                            <MessageCircle :size="16" class="text-indigo-500" />
                            <span>Hubungkan Telegram</span>
                        </Link>

                        <div class="border-t border-gray-200 dark:border-gray-700">
                            <Link
                                href="/logout"
                                method="post"
                                as="button"
                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                @click="userMenuOpen = false"
                            >
                                <LogOut :size="16" />
                                <span>Logout</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
