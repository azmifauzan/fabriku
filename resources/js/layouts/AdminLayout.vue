<script setup>
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
    LayoutDashboard,
    Building2,
    Users,
    Shield,
    FileText,
    LogOut,
    Menu,
    X,
    ChevronDown
} from 'lucide-vue-next'

const page = usePage()
const admin = computed(() => page.props.auth?.admin)

const sidebarOpen = ref(true)
const userMenuOpen = ref(false)

const navigation = [
    { name: 'Dashboard', href: '/admin', icon: LayoutDashboard },
    { name: 'Tenants', href: '/admin/tenants', icon: Building2 },
    { name: 'Users', href: '/admin/users', icon: Users },
    { name: 'Roles', href: '/admin/roles', icon: Shield },
    { name: 'Audit Logs', href: '/admin/audit-logs', icon: FileText },
]

const isActive = (href) => {
    return window.location.pathname.startsWith(href)
}

const logout = () => {
    router.post('/admin/logout')
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <div
            :class="[
                'fixed inset-y-0 left-0 z-50 w-64 bg-gradient-to-b from-purple-900 to-indigo-900 transform transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-6 bg-black/20">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/10 p-2 rounded-lg">
                        <svg class="w-6 h-6 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-white font-bold text-lg">Fabriku</h1>
                        <p class="text-purple-200 text-xs">Admin Panel</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden text-white">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 px-3 space-y-1">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-all duration-200',
                        isActive(item.href)
                            ? 'bg-white/20 text-white shadow-lg'
                            : 'text-purple-100 hover:bg-white/10 hover:text-white'
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5 mr-3" />
                    {{ item.name }}
                </Link>
            </nav>

            <!-- User Menu (Bottom) -->
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-black/20">
                <div class="relative">
                    <button
                        @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center w-full px-3 py-2 text-sm text-white hover:bg-white/10 rounded-lg transition"
                    >
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ admin?.name?.charAt(0) || 'A' }}
                            </span>
                        </div>
                        <div class="ml-3 text-left flex-1">
                            <p class="text-sm font-medium">{{ admin?.name || 'Admin' }}</p>
                            <p class="text-xs text-purple-200">{{ admin?.email }}</p>
                        </div>
                        <ChevronDown class="w-4 h-4" />
                    </button>

                    <!-- Dropdown -->
                    <div
                        v-show="userMenuOpen"
                        class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-1"
                    >
                        <button
                            @click="logout"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <LogOut class="w-4 h-4 mr-2" />
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div :class="['transition-all duration-300', sidebarOpen ? 'lg:ml-64' : 'ml-0']">
            <!-- Top Bar -->
            <div class="sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <Menu class="w-6 h-6" />
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
