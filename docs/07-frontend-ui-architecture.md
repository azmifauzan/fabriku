# Frontend UI Architecture - Fabriku

## Overview

Fabriku menggunakan modern frontend stack dengan Vue 3, Inertia.js v2, dan Tailwind CSS v4, dioptimalkan untuk pengalaman mobile-first yang responsif dengan dukungan dark mode.

## Tech Stack

### Core Technologies
- **Vue 3.5.13** - Progressive JavaScript framework
- **Inertia.js v2** - Modern monolithic SPA framework
- **TypeScript 5.2.2** - Type safety dan developer experience
- **Tailwind CSS v4** - Utility-first CSS framework
- **Vite 7** - Lightning-fast build tool
- **Laravel Wayfinder v0.1.3** - Type-safe routing

### Icon Library
- **lucide-vue-next** - Modern, beautiful icon set dengan 1000+ icons

### State Management & Utilities
- **@vueuse/core 12.8.2** - Collection of Vue composition utilities
- **class-variance-authority** - Type-safe variant styling
- **clsx & tailwind-merge** - Conditional class management

---

## Layout System Architecture

### 1. AppLayout (Main Layout)

**File**: `resources/js/layouts/AppLayout.vue`

Layout utama yang membungkus semua halaman aplikasi dengan struktur:
- Fixed navbar di atas
- Collapsible/toggle sidebar di kiri
- Main content area yang responsif
- Footer di bawah

#### Features:
- ✅ **Mobile-First Responsive**
  - Mobile (< 768px): Sidebar sebagai drawer overlay
  - Tablet/Desktop (≥ 768px): Sidebar tetap terlihat
  
- ✅ **Adaptive Behavior**
  - Auto-detect screen size dengan `window.innerWidth`
  - Auto-hide sidebar saat resize ke mobile
  - Smooth transitions (300ms)

- ✅ **Dark Mode Support**
  - Semua komponen mendukung dark variant
  - Smooth color transitions

#### Props & State:
```typescript
// Internal state
const isSidebarOpen = ref(false)  // Sidebar state
const isMobile = ref(false)        // Mobile detection

// Computed from Inertia page props
const user = computed(() => page.props.auth?.user || null)
const currentRoute = computed(() => page.url)
```

#### Mobile vs Desktop Behavior:

| Feature | Mobile (< 768px) | Desktop (≥ 768px) |
|---------|------------------|-------------------|
| Sidebar Type | Drawer overlay | Fixed panel |
| Sidebar Width | 256px (w-64) | 256px expanded, 64px collapsed |
| Overlay | Black backdrop (bg-black/50) | None |
| Auto-hide on navigate | Yes | No |
| Toggle button location | Navbar (hamburger menu) | Sidebar edge |
| Main content margin | ml-0 | ml-16 or ml-64 |

---

### 2. Navbar Component

**File**: `resources/js/components/Navbar.vue`

Fixed top navigation bar dengan height 64px (h-16).

#### Features:
- ✅ Logo branding (Fabriku)
- ✅ Hamburger menu button (mobile only)
- ✅ Dark/Light theme toggle dengan Sun/Moon icons
- ✅ User info display (responsive)
- ✅ Logout button

#### Responsive Design:

**Mobile** (< 640px):
- Hamburger menu icon visible
- User info: icon only (no text)
- Compact spacing (gap-2)
- Logo: text-lg

**Desktop** (≥ 640px):
- No hamburger menu
- Full user info with name + role
- Normal spacing (gap-4)
- Logo: text-xl

#### Props:
```typescript
interface Props {
  user: { name: string; role: string } | null
  isMobile: boolean
}

// Emits
emit('toggleSidebar') // Trigger sidebar toggle pada mobile
```

#### Icons Used:
- `Menu` - Hamburger menu (mobile)
- `Sun` - Light mode indicator
- `Moon` - Dark mode indicator
- `User` - User profile icon
- `LogOut` - Logout action

---

### 3. Sidebar Component

**File**: `resources/js/components/Sidebar.vue`

Navigasi menu utama aplikasi dengan adaptive behavior.

#### Features:
- ✅ **Mobile Drawer** - Slide dari kiri dengan overlay
- ✅ **Desktop Panel** - Fixed dengan collapse/expand
- ✅ **Active Route Highlighting** - Indigo color untuk route aktif
- ✅ **Icon + Text Navigation** - 6 menu items
- ✅ **Auto-close on Navigate** (mobile only)
- ✅ **Smooth Animations** - Translate dan width transitions

#### Menu Items:
1. **Dashboard** - `/dashboard` (Home icon)
2. **Bahan Baku** - `/materials` (Package icon)
3. **Pattern** - `/patterns` (ShirtIcon)
4. **Cutting Order** - `/cutting-orders` (Scissors icon)
5. **Kontraktor** - `/contractors` (Users icon)
6. **Production Order** - `/production-orders` (FileBox icon)

#### Responsive Behavior:

**Mobile** (< 768px):
```vue
// Sidebar sebagai drawer
- Position: fixed
- Width: 256px (w-64)
- Transform: translate-x based on isOpen
- Z-index: 40 (below navbar)
- Close button (X icon) visible di corner
- Auto-close saat klik link navigasi
```

**Desktop** (≥ 768px):
```vue
// Sidebar sebagai collapsible panel
- Position: fixed
- Width: 64px collapsed / 256px expanded
- No transform animation
- Toggle button (chevron) di tepi kanan
- Text labels: hidden saat collapsed
- Icons: always visible
```

#### Props:
```typescript
interface Props {
  isOpen: boolean          // Sidebar open/closed state
  isMobile: boolean        // Mobile detection flag
  currentRoute: string     // Current URL for active highlighting
}

// Emits
emit('toggle')  // Toggle sidebar state
emit('close')   // Close sidebar (mobile navigation)
```

#### Active Route Logic:
```typescript
const isActive = (href: string) => {
  return props.currentRoute.startsWith(href)
}
```

Menggunakan `startsWith()` agar parent routes tetap aktif untuk child routes. Contoh: `/materials` aktif untuk `/materials/create` dan `/materials/123/edit`.

---

### 4. Footer Component

**File**: `resources/js/components/Footer.vue`

Simple footer dengan copyright dan tagline.

#### Features:
- Copyright year (dynamic dengan `new Date().getFullYear()`)
- Aplikasi tagline
- Dark mode support
- Responsive text sizing (text-xs sm:text-sm)

---

### 5. PageHeader Component

**File**: `resources/js/components/PageHeader.vue`

Reusable header untuk halaman dengan title, description, dan action button.

#### Features:
- ✅ **Responsive Layout** - Stack di mobile, row di desktop
- ✅ **Optional Create Button** - Dengan Plus icon
- ✅ **Flexible Content** - Title + description props
- ✅ **Full Width Button on Mobile** - Better touch targets

#### Props:
```typescript
interface Props {
  title: string           // Page title (required)
  description?: string    // Optional subtitle
  createLink?: string     // Optional create action URL
  createText?: string     // Button text (default: "Tambah")
}
```

#### Usage Example:
```vue
<PageHeader
  title="Data Bahan Baku"
  description="Kelola bahan baku untuk produksi garment"
  create-link="/materials/create"
  create-text="Tambah Bahan Baku"
/>
```

#### Responsive:
- **Mobile**: Stack vertical, full-width button, text-xl title
- **Desktop**: Flex row, auto-width button, text-2xl title

---

## Theme System

### Dark Mode Implementation

**File**: `resources/js/composables/useDarkMode.ts`

Custom implementation untuk dark mode management dengan **explicit default light theme**.

```typescript
import { ref, watch, onMounted } from 'vue'

export function useDarkMode() {
  const STORAGE_KEY = 'fabriku-theme'
  const isDark = ref(false)

  // Initialize theme
  const initTheme = () => {
    const savedTheme = localStorage.getItem(STORAGE_KEY)
    
    if (savedTheme === 'dark') {
      isDark.value = true
      document.documentElement.classList.add('dark')
    } else {
      // Default to light - explicitly set
      isDark.value = false
      document.documentElement.classList.remove('dark')
      localStorage.setItem(STORAGE_KEY, 'light')
    }
  }

  // Toggle function
  const toggleDark = () => {
    isDark.value = !isDark.value
    
    if (isDark.value) {
      document.documentElement.classList.add('dark')
      localStorage.setItem(STORAGE_KEY, 'dark')
    } else {
      document.documentElement.classList.remove('dark')
      localStorage.setItem(STORAGE_KEY, 'light')
    }
  }

  return { isDark, toggleDark }
}
```

**Important**: Tidak lagi menggunakan VueUse `useDark` karena issue dengan default value. Custom implementation memberikan kontrol penuh.

#### Features:
- ✅ **Default Light Theme** - Explicitly set ke light mode
- ✅ Persistent di localStorage (`fabriku-theme`)
- ✅ Instant toggle tanpa page reload
- ✅ Direct DOM manipulation untuk reliability
- ✅ Apply dark class ke `<html>` element
- ✅ Toggle via Sun/Moon icon di Navbar
- ✅ Auto-initialize on mount dan immediate execution

#### Troubleshooting:
Jika theme stuck di dark mode:
1. Buka: `http://localhost:8000/reset-theme.html`
2. Klik "Reset to Light Mode"
3. Atau run di browser console:
   ```javascript
   localStorage.setItem('fabriku-theme', 'light');
   document.documentElement.classList.remove('dark');
   location.reload();
   ```

#### Usage in Components:
```vue
<script setup>
import { useDarkMode } from '@/composables/useDarkMode'

const { isDark, toggleDark } = useDarkMode()
</script>

<template>
  <button @click="toggleDark()">
    <Sun v-if="isDark" />
    <Moon v-else />
  </button>
</template>
```

### Dark Mode Classes

Tailwind CSS v4 dark mode menggunakan class strategy:

```html
<!-- Background colors -->
<div class="bg-white dark:bg-gray-800"></div>

<!-- Text colors -->
<p class="text-gray-900 dark:text-white"></p>

<!-- Borders -->
<div class="border-gray-200 dark:border-gray-700"></div>

<!-- Hover states -->
<button class="hover:bg-gray-50 dark:hover:bg-gray-700"></button>
```

#### Color Palette Guidelines:

| Element | Light Mode | Dark Mode |
|---------|-----------|-----------|
| Background (primary) | white | gray-800 |
| Background (secondary) | gray-50 | gray-900 |
| Text (primary) | gray-900 | white |
| Text (secondary) | gray-600 | gray-400 |
| Border | gray-200 | gray-700 |
| Accent (primary) | indigo-600 | indigo-400 |
| Accent (hover) | indigo-700 | indigo-300 |

---

## Icon System (Lucide Vue Next)

### Installation
```bash
npm install lucide-vue-next
```

### Usage Pattern
```vue
<script setup>
import { Home, Package, Edit, Trash2 } from 'lucide-vue-next'
</script>

<template>
  <!-- Basic usage -->
  <Home :size="20" />
  
  <!-- With color class -->
  <Edit :size="16" class="text-indigo-600" />
  
  <!-- Responsive size -->
  <Menu :size="18" class="sm:hidden" />
</template>
```

### Icons Used in App:

#### Navigation Icons:
- `Home` - Dashboard
- `Package` - Materials (Bahan Baku)
- `ShirtIcon` - Patterns
- `Scissors` - Cutting Orders
- `Users` - Contractors
- `FileBox` - Production Orders

#### UI Icons:
- `Menu` - Mobile hamburger menu
- `X` - Close/dismiss buttons
- `ChevronLeft` / `ChevronRight` - Sidebar toggle
- `Sun` / `Moon` - Theme toggle
- `User` - User profile
- `LogOut` - Logout action
- `Plus` - Create actions
- `Edit` - Edit actions
- `Trash2` - Delete actions
- `Search` - Search/filter
- `AlertTriangle` - Warnings/alerts
- `DollarSign` - Currency/sales
- `ClipboardList` - Orders/lists
- `Box` - Inventory/stock

### Icon Size Guidelines:
- **Small actions**: 14-16px
- **Default**: 18-20px
- **Large/prominent**: 24px
- **Dashboard stats**: 24px

---

## Responsive Design Strategy

### Mobile-First Approach

Fabriku dibangun dengan **mobile-first** philosophy:

1. **Base styles untuk mobile** (default, no breakpoint)
2. **Progressive enhancement** untuk tablet dan desktop
3. **Touch-friendly** - Minimum 44x44px touch targets
4. **Performance** - Lazy loading, code splitting

### Breakpoints (Tailwind Default)

| Breakpoint | Min Width | Device |
|------------|-----------|--------|
| (default) | 0px | Mobile (phones) |
| `sm:` | 640px | Large phones / small tablets |
| `md:` | 768px | Tablets |
| `lg:` | 1024px | Laptops / desktops |
| `xl:` | 1280px | Large desktops |
| `2xl:` | 1536px | XL desktops |

### Common Responsive Patterns

#### 1. Hide/Show Elements:
```vue
<!-- Show on mobile only -->
<button class="md:hidden">Menu</button>

<!-- Hide on mobile -->
<div class="hidden sm:flex">Desktop Menu</div>
```

#### 2. Responsive Spacing:
```vue
<div class="px-4 sm:px-6 lg:px-8">
  <!-- 16px mobile, 24px tablet, 32px desktop -->
</div>
```

#### 3. Responsive Grid:
```vue
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
  <!-- 1 column mobile, 2 tablet, 4 desktop -->
</div>
```

#### 4. Responsive Typography:
```vue
<h1 class="text-xl sm:text-2xl lg:text-3xl">
  <!-- 20px mobile, 24px tablet, 30px desktop -->
</h1>
```

#### 5. Responsive Flex Direction:
```vue
<div class="flex flex-col sm:flex-row gap-4">
  <!-- Stack on mobile, row on desktop -->
</div>
```

### Mobile-Specific Considerations:

#### Touch Targets:
- Minimum 44x44px untuk buttons/links
- Adequate spacing antara interactive elements (min 8px)

#### Performance:
- Lazy load images dengan `loading="lazy"`
- Code splitting per route via Vite
- Minimal bundle size dengan tree shaking

#### Navigation:
- Hamburger menu untuk mobile
- Drawer/overlay sidebar (tidak permanent)
- Auto-close setelah navigation

#### Forms:
- Full-width inputs di mobile
- Stack form fields vertically
- Larger touch-friendly buttons

---

## Page Structure Pattern

### Standard Page Template:

```vue
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { Edit, Trash2, Search, X } from 'lucide-vue-next'

// Props, state, methods...
</script>

<template>
  <AppLayout>
    <Head title="Page Title" />

    <div class="py-4 sm:py-6 px-4 sm:px-6">
      <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <PageHeader
          title="Page Title"
          description="Page description"
          create-link="/resource/create"
          create-text="Tambah Data"
        />

        <!-- Filters (Optional) -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 mb-4 sm:mb-6">
          <!-- Filter content -->
        </div>

        <!-- Main Content (Table, Cards, etc) -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <!-- Content -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>
```

### Key Patterns:

1. **Always wrap with AppLayout**
2. **Use Head for page title** (affects browser tab)
3. **Responsive padding**: `py-4 sm:py-6 px-4 sm:px-6`
4. **Max-width container**: `max-w-7xl mx-auto`
5. **Consistent spacing**: `mb-4 sm:mb-6` antara sections
6. **Dark mode support**: Semua background colors

---

## Component Library Guidelines

### Button Variants:

```vue
<!-- Primary Action -->
<button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
  Primary Button
</button>

<!-- Secondary Action -->
<button class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm font-medium rounded-lg transition-colors">
  Secondary Button
</button>

<!-- Destructive Action -->
<button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
  Delete
</button>

<!-- Icon Button -->
<button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
  <Edit :size="18" />
</button>
```

### Card/Panel:
```vue
<div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
  <div class="p-4 sm:p-6">
    <!-- Content -->
  </div>
</div>
```

### Table:
```vue
<div class="overflow-x-auto">
  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
    <thead class="bg-gray-50 dark:bg-gray-700">
      <tr>
        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
          Header
        </th>
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      <!-- Rows -->
    </tbody>
  </table>
</div>
```

### Form Input:
```vue
<div>
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
    Label
  </label>
  <input
    type="text"
    class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
  />
</div>
```

### Badge:
```vue
<!-- Status Badge -->
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
  Active
</span>

<!-- Type Badge -->
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
  Kain
</span>
```

---

## Best Practices

### 1. Mobile-First Development
✅ **DO**:
- Start dengan mobile layout (no breakpoint)
- Add complexity progressively dengan breakpoints
- Test di mobile browser/device emulator
- Use relative units (rem, em) instead of px when appropriate

❌ **DON'T**:
- Start dengan desktop dan "shrink down"
- Hardcode pixel values tanpa responsive consideration
- Forget touch target sizes (min 44x44px)

### 2. Dark Mode
✅ **DO**:
- Test every component di both themes
- Use semantic color naming (primary, secondary, muted)
- Add `dark:` variant untuk semua color classes
- Use smooth transitions: `transition-colors`

❌ **DON'T**:
- Leave any component without dark mode support
- Use absolute colors (like `#ffffff`) - use Tailwind classes
- Forget hover/focus states dalam dark mode

### 3. Performance
✅ **DO**:
- Lazy load components dengan `defineAsyncComponent`
- Use `v-show` untuk toggle yang frequent
- Use `v-if` untuk conditional rendering yang rare
- Optimize images (WebP, lazy loading)

❌ **DON'T**:
- Load all components eagerly
- Render large lists tanpa pagination/virtual scrolling
- Import icons individually di banyak tempat (use centralized imports)

### 4. Accessibility
✅ **DO**:
- Use semantic HTML (`<nav>`, `<main>`, `<footer>`)
- Add `aria-label` untuk icon-only buttons
- Maintain proper heading hierarchy (h1 → h2 → h3)
- Test keyboard navigation (Tab, Enter, Escape)

❌ **DON'T**:
- Rely solely pada color untuk convey information
- Skip alt text untuk images
- Create inaccessible custom form controls

### 5. TypeScript
✅ **DO**:
- Define interfaces untuk props
- Use type-safe Inertia props
- Leverage Laravel Wayfinder untuk type-safe routing

❌ **DON'T**:
- Use `any` type
- Skip prop type definitions
- Ignore TypeScript errors

---

## File Organization

```
resources/js/
├── actions/                    # Laravel Wayfinder generated
│   └── App/Http/Controllers/   # Type-safe controller methods
├── components/                 # Reusable components
│   ├── Navbar.vue
│   ├── Sidebar.vue
│   ├── Footer.vue
│   └── PageHeader.vue
├── composables/                # Vue composables
│   └── useDarkMode.ts
├── layouts/                    # Layout components
│   └── AppLayout.vue
├── pages/                      # Page components (Inertia)
│   ├── Auth/
│   │   └── Login.vue
│   ├── Dashboard.vue
│   ├── Materials/
│   │   ├── Index.vue
│   │   └── Form.vue
│   ├── Patterns/
│   ├── CuttingOrders/
│   ├── Contractors/
│   └── ProductionOrders/
├── types/                      # TypeScript types
├── routes/                     # Laravel Wayfinder routes
├── wayfinder/                  # Wayfinder config
├── app.ts                      # Main app entry
└── ssr.ts                      # SSR entry (if enabled)
```

---

## Debugging Tips

### 1. Responsive Issues:
```bash
# Use browser DevTools device emulator
# Test common breakpoints: 375px, 640px, 768px, 1024px
```

### 2. Dark Mode Issues:
```javascript
// Check localStorage
localStorage.getItem('fabriku-theme') // Should return 'dark' or 'light'

// Force toggle in console
const { toggleDark } = useDarkMode()
toggleDark()
```

### 3. Inertia Page Props:
```vue
<script setup>
import { usePage } from '@inertiajs/vue3'

// Debug props
console.log('Page props:', usePage().props)
console.log('Auth user:', usePage().props.auth?.user)
</script>
```

### 4. Hot Reload Issues:
```bash
# Clear Vite cache
rm -rf node_modules/.vite

# Rebuild
npm run dev
```

---

## Future Enhancements

### Planned Features:
1. **Component Library** - Extract common components ke dedicated library
2. **Storybook** - Component documentation dan testing
3. **Animation Library** - Consistent animations dengan Framer Motion atau Vue transitions
4. **Form Builder** - Reusable form components dengan validation
5. **Data Tables** - Advanced table component dengan sort, filter, pagination
6. **Charts** - Dashboard charts dengan Chart.js atau Recharts
7. **SSR Support** - Server-side rendering untuk SEO
8. **PWA** - Progressive Web App capabilities (offline, install)

### Performance Optimizations:
1. Image optimization dengan Laravel image processing
2. CDN integration untuk static assets
3. Route-based code splitting optimization
4. Bundle size analysis dan reduction
5. Service worker untuk caching

---

## References

### Documentation:
- **Vue 3**: https://vuejs.org/
- **Inertia.js v2**: https://v2.inertiajs.com/
- **Tailwind CSS v4**: https://tailwindcss.com/
- **Laravel Wayfinder**: https://github.com/laravel/wayfinder
- **Lucide Icons**: https://lucide.dev/
- **VueUse**: https://vueuse.org/

### Internal Docs:
- [Business Requirements](./01-business-requirements.md)
- [System Architecture](./02-system-architecture.md)
- [Database Schema](./03-database-schema.md)
- [API Endpoints](./04-api-endpoints.md)
- [User Flows](./05-user-flows.md)

---

**Last Updated**: 2026-01-11
**Version**: 1.0.0
**Maintainer**: Fabriku Development Team
