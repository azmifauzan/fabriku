<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Factory,
    Home,
    Package,
    Settings,
    ShoppingCart,
    Warehouse,
    X,
    CreditCard,
} from 'lucide-vue-next';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    isMobile: boolean;
    currentRoute: string;
}>();

const emit = defineEmits<{
    (e: 'toggle'): void;
    (e: 'close'): void;
}>();

const page = usePage();

// Track expanded menu items
const expandedMenus = ref<string[]>([]);

const businessCategory = computed(() => (page.props.tenant as any)?.business_category as string | undefined);
const terminology = computed(() => ((page.props.tenant as any)?.terminology ?? {}) as Record<string, string>);

// Get user permissions from shared data
const userPermissions = computed(() => {
    const user = (page.props.auth as any)?.user;
    return user?.permissions ?? [];
});

const isAdmin = computed(() => {
    const user = (page.props.auth as any)?.user;
    return user?.role === 'admin';
});

// Check if user has a specific permission
const hasPermission = (permissionSlug: string): boolean => {
    if (isAdmin.value) return true;
    if (userPermissions.value.includes('*')) return true;
    return userPermissions.value.includes(permissionSlug);
};

// Check if user has any of the given permissions
const hasAnyPermission = (slugs: string[]): boolean => {
    return slugs.some((slug) => hasPermission(slug));
};

const allMenuItems = computed(() => {
    return [
        {
            name: 'Dashboard',
            href: '/dashboard',
            icon: Home,
            permission: null, // always visible
        },
        {
            name: 'Master Data',
            href: '/master-data',
            icon: Settings,
            permission: null, // filtered by children
            children: [
                {
                    name: 'Jenis Bahan',
                    href: '/material-types',
                    permission: 'material.view',
                },
                {
                    name: 'Staff',
                    href: '/staff',
                    permission: null, // admin-only, handled separately
                    adminOnly: true,
                },
                {
                    name: 'Lokasi Inventory',
                    href: '/inventory/locations',
                    permission: 'inventory.view',
                },
                {
                    name: 'Customer',
                    href: '/customers',
                    permission: 'sales.view',
                },
                {
                    name: terminology.value.pattern || 'Pattern',
                    href: '/patterns',
                    permission: 'pattern.view',
                },
                {
                    name: terminology.value.contractor || 'Kontraktor',
                    href: '/contractors',
                    permission: 'production.view',
                },
            ],
        },
        {
            name: terminology.value.material || 'Bahan Baku',
            href: '/materials',
            icon: Package,
            permission: 'material.view',
        },
        {
            name: 'Preparation',
            href: '/preparation-orders',
            icon: CheckCircle2,
            permission: 'preparation.view',
        },
        {
            name: 'Production Order',
            href: '/production-orders',
            icon: Factory,
            permission: 'production.view',
        },
        {
            name: 'Inventory',
            href: '/inventory',
            icon: Warehouse,
            permission: 'inventory.view',
            children: [
                {
                    name: 'Items',
                    href: '/inventory/items',
                    permission: 'inventory.view',
                },
                {
                    name: 'Visualisasi',
                    href: '/inventory/visualization',
                    permission: 'inventory.view',
                },
            ],
        },
        {
            name: 'Sales Order',
            href: '/sales-orders',
            icon: ShoppingCart,
            permission: 'sales.view',
            children: [
                {
                    name: 'List',
                    href: '/sales-orders',
                    permission: 'sales.view',
                },
                {
                    name: 'Settings',
                    href: '/settings',
                    permission: 'settings.view',
                },
            ],
        },
        {
            name: 'Reports',
            href: '/reports',
            icon: BarChart3,
            permission: 'report.view',
            children: [
                {
                    name: 'Material',
                    href: '/reports/material',
                    permission: 'report.view',
                },
                {
                    name: 'Inventory',
                    href: '/reports/inventory',
                    permission: 'report.view',
                },
                {
                    name: 'Penjualan',
                    href: '/reports/sales',
                    permission: 'report.view',
                },
                {
                    name: 'Produksi',
                    href: '/reports/production',
                    permission: 'report.view',
                },
            ],
        },
    ];
});

// Filter menu items based on permissions
const menuItems = computed(() => {
    return allMenuItems.value
        .filter((item) => {
            // If item has children, filter children first
            if (item.children) {
                const visibleChildren = item.children.filter((child: any) => {
                    if (child.adminOnly) return isAdmin.value;
                    if (!child.permission) return true;
                    return hasPermission(child.permission);
                });
                return visibleChildren.length > 0;
            }

            // Top-level item without children
            if (!item.permission) return true;
            return hasPermission(item.permission);
        })
        .map((item) => {
            if (item.children) {
                return {
                    ...item,
                    children: item.children.filter((child: any) => {
                        if (child.adminOnly) return isAdmin.value;
                        if (!child.permission) return true;
                        return hasPermission(child.permission);
                    }),
                };
            }
            return item;
        });
});

const isActive = (href: string) => {
    // Exact match for /settings to avoid matching /settings/telegram
    if (href === '/settings') {
        return props.currentRoute === '/settings';
    }
    return props.currentRoute.startsWith(href);
};

// Auto-expand parent menu if child is active
const checkAndExpandActiveMenu = () => {
    menuItems.value.forEach((item) => {
        if (item.children) {
            const hasActiveChild = item.children.some((child: any) => isActive(child.href));
            if (hasActiveChild && !expandedMenus.value.includes(item.href)) {
                expandedMenus.value.push(item.href);
            }
        }
    });
};

// Run on mount and when route changes
onMounted(() => {
    checkAndExpandActiveMenu();
});

watch(
    () => props.currentRoute,
    () => {
        checkAndExpandActiveMenu();
    },
);

const isMenuExpanded = (href: string) => {
    return expandedMenus.value.includes(href);
};

const toggleMenu = (href: string) => {
    const index = expandedMenus.value.indexOf(href);
    if (index > -1) {
        expandedMenus.value.splice(index, 1);
    } else {
        expandedMenus.value.push(href);
    }
};

const handleLinkClick = () => {
    if (props.isMobile) {
        emit('close');
    }
};
</script>

<template>
    <aside
        :class="[
            'fixed top-16 z-40 h-[calc(100vh-4rem)] flex flex-col border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-700 dark:bg-gray-800',
            // Mobile: slide from left, Desktop: always visible
            isMobile ? ['left-0 w-64', isOpen ? 'translate-x-0' : '-translate-x-full'] : ['left-0', isOpen ? 'w-64' : 'w-16'],
        ]"
    >
        <!-- Mobile Close Button -->
        <button
            v-if="isMobile"
            @click="emit('close')"
            class="absolute top-2 right-2 rounded-lg p-2 hover:bg-gray-100 md:hidden dark:hover:bg-gray-700"
            aria-label="Close menu"
        >
            <X :size="20" class="text-gray-600 dark:text-gray-300" />
        </button>

        <!-- Desktop Toggle Button -->
        <button
            v-if="!isMobile"
            @click="emit('toggle')"
            class="absolute top-4 -right-3 hidden rounded-full border border-gray-200 bg-white p-1 transition-colors hover:bg-gray-50 md:block dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700"
        >
            <ChevronLeft v-if="isOpen" :size="16" class="text-gray-600 dark:text-gray-300" />
            <ChevronRight v-else :size="16" class="text-gray-600 dark:text-gray-300" />
        </button>

        <!-- Menu Items -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-4">
            <template v-for="item in menuItems" :key="item.href">
                <!-- Parent Menu Item -->
                <Link
                    v-if="!item.children"
                    :href="item.href"
                    :class="[
                        'group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors',
                        isActive(item.href)
                            ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
                            : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700',
                    ]"
                    @click="handleLinkClick"
                >
                    <component
                        :is="item.icon"
                        :size="20"
                        :class="[
                            'flex-shrink-0',
                            isActive(item.href)
                                ? 'text-indigo-600 dark:text-indigo-400'
                                : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300',
                        ]"
                    />
                    <span v-if="isMobile || isOpen" class="text-sm font-medium whitespace-nowrap">
                        {{ item.name }}
                    </span>
                </Link>

                <!-- Menu with Children -->
                <div v-else>
                    <button
                        type="button"
                        :class="[
                            'flex w-full cursor-pointer items-center justify-between gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700',
                            isActive(item.href)
                                ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
                                : 'text-gray-700 dark:text-gray-300',
                        ]"
                        @click="toggleMenu(item.href)"
                    >
                        <div class="flex items-center gap-3">
                            <component
                                :is="item.icon"
                                :size="20"
                                :class="[
                                    'flex-shrink-0',
                                    isActive(item.href) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400',
                                ]"
                            />
                            <span v-if="isMobile || isOpen" class="text-sm font-medium whitespace-nowrap">
                                {{ item.name }}
                            </span>
                        </div>
                        <ChevronDown
                            v-if="isMobile || isOpen"
                            :size="16"
                            :class="[
                                'flex-shrink-0 transition-transform duration-200',
                                isMenuExpanded(item.href) ? 'rotate-180' : '',
                                isActive(item.href) ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500',
                            ]"
                        />
                    </button>

                    <!-- Submenu Items -->
                    <div v-if="(isMobile || isOpen) && isMenuExpanded(item.href)" class="mt-1 ml-8 space-y-1">
                        <Link
                            v-for="child in item.children"
                            :key="child.href"
                            :href="child.href"
                            :class="[
                                'block rounded-lg px-3 py-2 text-sm transition-colors',
                                isActive(child.href)
                                    ? 'bg-indigo-50 font-medium text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
                                    : 'text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700',
                            ]"
                            @click="handleLinkClick"
                        >
                            {{ child.name }}
                        </Link>
                    </div>
                </div>
            </template>
        </nav>

        <!-- Sidebar Footer (Membership) -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-2">
            <Link
                href="/subscription"
                :class="[
                    'group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors',
                    isActive('/subscription')
                        ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400'
                        : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700',
                ]"
                @click="handleLinkClick"
            >
                <CreditCard
                    :size="20"
                    :class="[
                        'flex-shrink-0',
                        isActive('/subscription')
                            ? 'text-indigo-600 dark:text-indigo-400'
                            : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-300',
                    ]"
                />
                <span v-if="isMobile || isOpen" class="text-sm font-medium whitespace-nowrap">
                    Membership
                </span>
            </Link>
        </div>
    </aside>
</template>
