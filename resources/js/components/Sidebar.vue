<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'
import {
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  Home,
  Package,
  Scissors,
  ShirtIcon,
  Users,
  FileBox,
  BookOpen,
  ClipboardList,
  ChefHat,
  Warehouse,
  X,
} from 'lucide-vue-next'

const props = defineProps<{
  isOpen: boolean
  isMobile: boolean
  currentRoute: string
}>()

const emit = defineEmits<{
  (e: 'toggle'): void
  (e: 'close'): void
}>()

const page = usePage()

// Track expanded menu items
const expandedMenus = ref<string[]>([])

const businessCategory = computed(() => (page.props.tenant as any)?.business_category as string | undefined)
const terminology = computed(() => ((page.props.tenant as any)?.terminology ?? {}) as Record<string, string>)

const menuItems = computed(() => {
  const isFood = businessCategory.value === 'food'

  return [
    {
      name: 'Dashboard',
      href: '/dashboard',
      icon: Home,
    },
    {
      name: terminology.value.material || 'Bahan Baku',
      href: '/materials',
      icon: Package,
    },
    {
      name: terminology.value.pattern || 'Pattern',
      href: '/patterns',
      icon: isFood ? BookOpen : ShirtIcon,
    },
    {
      name: terminology.value.preparation_order || 'Cutting Order',
      href: '/cutting-orders',
      icon: isFood ? ClipboardList : Scissors,
    },
    {
      name: terminology.value.contractor || 'Kontraktor',
      href: '/contractors',
      icon: Users,
    },
    {
      name: terminology.value.production_order || 'Production Order',
      href: '/production-orders',
      icon: isFood ? ChefHat : FileBox,
    },
    {
      name: 'Inventory',
      href: '/inventory',
      icon: Warehouse,
      children: [
        {
          name: 'Lokasi',
          href: '/inventory/locations',
        },
        {
          name: 'Items',
          href: '/inventory/items',
        },
      ],
    },
  ]
})

const isActive = (href: string) => {
  return props.currentRoute.startsWith(href)
}

// Auto-expand parent menu if child is active
const checkAndExpandActiveMenu = () => {
  menuItems.value.forEach((item) => {
    if (item.children) {
      const hasActiveChild = item.children.some((child) => isActive(child.href))
      if (hasActiveChild && !expandedMenus.value.includes(item.href)) {
        expandedMenus.value.push(item.href)
      }
    }
  })
}

// Run on mount and when route changes
onMounted(() => {
  checkAndExpandActiveMenu()
})

watch(
  () => props.currentRoute,
  () => {
    checkAndExpandActiveMenu()
  },
)

const isMenuExpanded = (href: string) => {
  return expandedMenus.value.includes(href)
}

const toggleMenu = (href: string) => {
  const index = expandedMenus.value.indexOf(href)
  if (index > -1) {
    expandedMenus.value.splice(index, 1)
  } else {
    expandedMenus.value.push(href)
  }
}

const handleLinkClick = () => {
  if (props.isMobile) {
    emit('close')
  }
}
</script>

<template>
  <aside
    :class="[
      'fixed top-16 h-[calc(100vh-4rem)] bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transition-all duration-300 z-40',
      // Mobile: slide from left, Desktop: always visible
      isMobile
        ? [
            'left-0 w-64',
            isOpen ? 'translate-x-0' : '-translate-x-full',
          ]
        : [
            'left-0',
            isOpen ? 'w-64' : 'w-16',
          ],
    ]"
  >
    <!-- Mobile Close Button -->
    <button
      v-if="isMobile"
      @click="emit('close')"
      class="absolute right-2 top-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 md:hidden"
      aria-label="Close menu"
    >
      <X :size="20" class="text-gray-600 dark:text-gray-300" />
    </button>

    <!-- Desktop Toggle Button -->
    <button
      v-if="!isMobile"
      @click="emit('toggle')"
      class="hidden md:block absolute -right-3 top-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-full p-1 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
    >
      <ChevronLeft v-if="isOpen" :size="16" class="text-gray-600 dark:text-gray-300" />
      <ChevronRight v-else :size="16" class="text-gray-600 dark:text-gray-300" />
    </button>

    <!-- Menu Items -->
    <nav class="px-2 py-4 space-y-1 overflow-y-auto h-full">
      <template v-for="item in menuItems" :key="item.href">
        <!-- Parent Menu Item -->
        <Link
          v-if="!item.children"
          :href="item.href"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors group',
            isActive(item.href)
              ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400'
              : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
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
                : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-300',
            ]"
          />
          <span
            v-if="isMobile || isOpen"
            class="text-sm font-medium whitespace-nowrap"
          >
            {{ item.name }}
          </span>
        </Link>

        <!-- Menu with Children -->
        <div v-else>
          <button
            type="button"
            :class="[
              'w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg transition-colors cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700',
              isActive(item.href)
                ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400'
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
                  isActive(item.href)
                    ? 'text-indigo-600 dark:text-indigo-400'
                    : 'text-gray-500 dark:text-gray-400',
                ]"
              />
              <span
                v-if="isMobile || isOpen"
                class="text-sm font-medium whitespace-nowrap"
              >
                {{ item.name }}
              </span>
            </div>
            <ChevronDown
              v-if="isMobile || isOpen"
              :size="16"
              :class="[
                'flex-shrink-0 transition-transform duration-200',
                isMenuExpanded(item.href) ? 'rotate-180' : '',
                isActive(item.href)
                  ? 'text-indigo-600 dark:text-indigo-400'
                  : 'text-gray-400 dark:text-gray-500',
              ]"
            />
          </button>

          <!-- Submenu Items -->
          <div
            v-if="(isMobile || isOpen) && isMenuExpanded(item.href)"
            class="ml-8 mt-1 space-y-1"
          >
            <Link
              v-for="child in item.children"
              :key="child.href"
              :href="child.href"
              :class="[
                'block px-3 py-2 rounded-lg text-sm transition-colors',
                isActive(child.href)
                  ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 font-medium'
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
              ]"
              @click="handleLinkClick"
            >
              {{ child.name }}
            </Link>
          </div>
        </div>
      </template>
    </nav>
  </aside>
</template>
