<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { Activity, Building2, ChevronDown, CreditCard, FileText, LayoutDashboard, LogOut, Menu, Settings, Shield, Users, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const admin = computed(() => page.props.auth?.admin);

const sidebarOpen = ref(true);
const userMenuOpen = ref(false);

const navigation = [
    { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { name: 'Tenants', href: '/admin/tenants', icon: Building2 },
    { name: 'Payments', href: '/admin/payments', icon: CreditCard },
    { name: 'Users', href: '/admin/users', icon: Users },
    { name: 'Roles', href: '/admin/roles', icon: Shield },
    { name: 'Settings', href: '/admin/settings', icon: Settings },
    { name: 'Monitoring', href: '/admin/monitoring', icon: Activity },
    { name: 'Audit Logs', href: '/admin/audit-logs', icon: FileText },
];

const isActive = (href) => {
    return window.location.pathname.startsWith(href);
};

const logout = () => {
    router.post('/admin/logout');
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 transform bg-gradient-to-b from-purple-900 to-indigo-900 transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center justify-between bg-black/20 px-6">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white/10 p-1">
                        <img src="/images/fabriku-logo-only.png?v=2" alt="Fabriku Logo" class="h-full w-full object-contain" />
                    </div>
                    <div>
                        <img src="/images/fabriku-word.png?v=2" alt="Fabriku" class="h-5 object-contain" />
                        <p class="text-xs text-purple-200 mt-0.5">Admin Panel</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="text-white lg:hidden">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 space-y-1 px-3">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
                        isActive(item.href) ? 'bg-white/20 text-white shadow-lg' : 'text-purple-100 hover:bg-white/10 hover:text-white',
                    ]"
                >
                    <component :is="item.icon" class="mr-3 h-5 w-5" />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User Menu (Bottom) -->
            <div class="absolute right-0 bottom-0 left-0 bg-black/20 p-4">
                <div class="relative">
                    <button
                        @click="userMenuOpen = !userMenuOpen"
                        class="flex w-full items-center rounded-lg px-3 py-2 text-sm text-white transition hover:bg-white/10"
                    >
                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-purple-500">
                            <span class="text-sm font-semibold text-white">
                                {{ admin?.name?.charAt(0) || 'A' }}
                            </span>
                        </div>
                        <div class="ml-3 flex-1 text-left">
                            <p class="text-sm font-medium">{{ admin?.name || 'Admin' }}</p>
                            <p class="text-xs text-purple-200">{{ admin?.email }}</p>
                        </div>
                        <ChevronDown class="h-4 w-4" />
                    </button>

                    <!-- Dropdown -->
                    <div v-show="userMenuOpen" class="absolute right-0 bottom-full left-0 mb-2 rounded-lg bg-white py-1 shadow-lg dark:bg-gray-800">
                        <button
                            @click="logout"
                            class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <LogOut class="mr-2 h-4 w-4" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div :class="['transition-all duration-300', sidebarOpen ? 'lg:ml-64' : 'ml-0']">
            <!-- Top Bar -->
            <div class="sticky top-0 z-40 border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <Menu class="h-6 w-6" />
                    </button>

                    <div class="flex items-center space-x-4">
                        <!-- Notifications, etc can go here -->
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
